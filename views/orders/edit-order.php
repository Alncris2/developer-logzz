<?php
// error_reporting(-1);            
// ini_set('display_errors', 1);
require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

//Verifica privilégio de administrador
if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}

if ($_SESSION['UserPlan'] < 5 && $_GET['status'] != "reagendar") {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}

$operator_page = true;

if ($_GET['status'] == "reagendar") {
    $page_title = "Reagendar Pedido | Logzz";
} elseif ($_GET['status'] == "frustrar") {
    $page_title = "Frustrar Pedido | Logzz";
} else if ($_GET['status'] == "completar") {
    $page_title = "Completar Pedido | Logzz";
} else if ($_GET['status'] == "enviando") {
    $page_title = "Envio de Pedido | Logzz";
} else {
    exit;
}


require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');
?>
<?php
if ($_GET['status'] == "reagendar") {
    $data = [1, 2, 3, 4, 5, 6, 7];
    $order_id = $_GET['pedido'];
    $stmt = $conn->prepare("SELECT client_address, product_id FROM orders WHERE order_id = :order_id");
    $stmt->execute(array('order_id' => $order_id));

    if ($order = $stmt->fetch()) {
        $address = $order["client_address"];
        $city_state = explode("<br>", $address)[3];
        $city = explode(", ", $city_state)[0];
        $state = explode(", ", $city_state)[1];

        $local_operations = $conn->prepare('SELECT lop.operation_delivery_days, lop.operation_id  FROM local_operations lop INNER JOIN operations_locales ol ON lop.operation_id = ol.operation_id WHERE lop.uf = :uf AND ol.city like :city');
        $local_operations->execute(array("uf" => $state, "city" => '%' . $city . '%'));
        $data = null;
        if ($delivery_days = $local_operations->fetch()) {
            $data = $delivery_days['operation_delivery_days'];

            $invetoriesStmt = $conn->prepare('SELECT product_delivery_days FROM inventories WHERE inventory_locale_id = :operation_id AND inventory_product_id = :product_id');
            $invetoriesStmt->execute(array("operation_id" => $delivery_days['operation_id'], 'product_id' => $order["product_id"]));

            if ($dadosInventory = $invetoriesStmt->fetch()[0]) {
                $data = $dadosInventory;
            }
        }
    } ?>

    <div class="container-fluid">
        <!-- row -->
        <div class="row">
            <div class="col-xl-8 col-xxl-8">
                <div class="card">
                    <div class="card-header"> 
                        <h4 class="card-title">Informe Nova data e período para a entrega</h4>
                    </div>
                    <div class="card-body">
                        <form id="RescheduleOrderForm" action="reagendar" method="POST">
                            <p class="mb-1"></p>
                            <div class="row mt-1">
                                <div class="col-md-12 mb-3"> 
                                    <input type="hidden" id="ActionInput" name="order" value="<?php echo addslashes($_GET['pedido']); ?>">
                                    <input name="data-pedido" value="Data" class="datepicker-reagendar form-control picker__input" id="data-pedido" data-days="<?=  $data  ?>" readonly="" aria-haspopup="true" aria-expanded="false" aria-readonly="false" aria-owns="datepicker_root" placeholder="dia / mês / ano">
                                    <!-- <input type="text" class="form-control" id="hidden-date-field" name="hidden-date-field" placeholder="Apartament, Bloco, etc" value=""> -->
                                </div>
                                <div class="picker" id="datepicker_root" aria-hidden="true">
                                    <div class="picker__holder" tabindex="-1">
                                        <div class="picker__frame">
                                            <div class="picker__wrap">
                                                <div class="picker__box">
                                                    <div class="picker__header">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" id="SubmitButtonReag" class="btn btn-success mt-3 mb-3">Confirmar Reagendamento</button><a onclick="window.history.back();" class="btn btn-light ml-2 mt-3 mb-3"><i class="fas fa-sign-out-alt"></i> Cancelar</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
} else if ($_GET['status'] == "frustrar") {

    $order_number = addslashes($_GET['pedido']);

    $confirme_order_status = $conn->prepare('SELECT * FROM orders WHERE order_number = :order_number LIMIT 1');
    $confirme_order_status->execute(array('order_number' => $order_number));

    if($order_infos = $confirme_order_status->fetch()){
        $get_order_operation = $conn->prepare("SELECT * FROM local_operations_orders lo INNER JOIN local_operations loo ON lo.operation_id=loo.operation_id WHERE order_id=:order_id");
        $get_order_operation->execute(array("order_id" => $order_infos['order_id']));
        $local_operations = $get_order_operation->fetch();  
    }

    if ($local_operations) { ?> 
        <div class="container-fluid">
            <!-- row -->
            <div class="row">
                <div class="col-xl-12 col-xxl-12">
                    <div class="card"> 
                        <div class="card-header">
                            <h4 class="card-title">Anexe o Comprovante da Tentativa de Entrega</h4>
                        </div>
                        <div class="card-body">
                            <form id="FailOrderForm" action="frustrar" method="POST">
                                <div class="col-lg-12 mb-2">
                                    <?php
                                    

                                    if ($order_infos['order_status'] == 4 && $order_infos['fail_delivery_attemp'] != null) {
                                        $alert_class = "alert-warning show";
                                        $alert_icon = "fas fa-clock";
                                        $alert_title = "Pedido Frustrado!";
                                        $alert_msg = "Este pedido já foi definido como frustrado e já tem um comprovante anexado.";
                                        $alert_msg .= '<br><small><a href="' . SERVER_URI . '/uploads/pedidos/frustrados/' . $order_infos['fail_delivery_attemp'] . '" target="_blank">Ver Comprovante</a></small>';
                                        $disable_input = true; ?>

                                        <div class="alert <?php echo $alert_class; ?> fade">
                                            <i class="<?php echo $alert_icon; ?>"></i>
                                            <strong><?php echo $alert_title; ?></strong><br><?php echo $alert_msg; ?>
                                        </div>

                                        <?php
                                    } ?>
                                    <input type="hidden" name="action" value="entrega-frustrada">
                                    <input type="hidden" id="ActionInput" name="order" value="<?php echo addslashes($_GET['pedido']); ?>">
                                    <div class="mb-3">
                                        <div class="mb-3">
                                            <div class="form-group">
                                                <label class="text-label">Localidade:<i class="req-mark">*</i></label>
                                                <input type="text" disabled class="form-control" value="<?php echo $local_operations['operation_name'] ?>">
                                                <input id="operation-id" type="hidden" value="<?php echo $local_operations['operation_id'] ?>" name="localidade">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-group">
                                            <label class="text-label">Selecione o operador: <i class="req-mark">*</i></label>
                                            <select class="d-block default-select" id="select-ship-operator">
                                            </select>
                                        </div>
                                        <input type="hidden" id="text-ship-operator" name="operador" value="" required>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-group">
                                            <label class="text-label">Anexar Comprovante de tentativa de contato: <i class="req-mark">*</i></label>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="far fa-images"></i></span>
                                                </div>
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input" name="comprovante-tentativa-contato" accept="image/*" <?= @$disable_input ? 'disabled="disabled"': '' ?>>
                                                    <label class="custom-file-label">Selecionar imagem...</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-group">
                                            <label class="text-label">Anexar Comprovante de tentativa de entrega: <i class="req-mark">*</i></label>
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="far fa-images"></i></span>
                                                </div>
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input" name="comprovante-tentativa-entrega" accept="image/*" <?= @$disable_input ? 'disabled="disabled"': '' ?>>
                                                    <label class="custom-file-label">Selecionar imagem...</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-group">
                                            <label class="text-label">Cliente pagou pela tentativa ?</label>
                                            <div class="custom-control custom-switch mb-3">
                                                <input type="checkbox" class="custom-control-input" name="confirm-pay" id="confirm-pay">
                                                <label class="custom-control-label" id="confirm-pay-lbl" for="confirm-pay">&nbsp;Não</label>
                                            </div>
                                            <div id="components-pay" class="d-none"> 
                                                <div class="row"> 
                                                    <div class="form-group col-md-12">
                                                        <label for="datepicker">Informe o valor pago pelo cliente: </label>
                                                        <input type="text" class="form-control money" id="valor-pago" name="valor-pago" > 
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                        <button type="submit" id="SubmitButtonFrustr" class="btn btn-success mt-3 mb-3" <?= @$disable_input ? 'disabled="disabled"' : '' ?>>Confirmar Tentativa Frustrada</button>
                                        <a onclick="window.history.back();" class="btn btn-light ml-2 mt-3 mb-3"><i class="fas fa-sign-out-alt"></i> Voltar</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div> 
        </div>  
        </div>     
    <?PHP
    } else {
    ?>
        <div class="container-fluid">
            <div class="row col-12 d-flex align-center">
                <div class="alert alert-danger text-center w-100">Não existe estoque do produto para a cidade correspondente ao CEP inserido.</div>
            </div>
        </div>
    <?php
    }
} else if ($_GET['status'] == "completar") {
    $get_order_operation = $conn->prepare("SELECT * FROM local_operations_orders lo INNER JOIN local_operations loo ON lo.operation_id=loo.operation_id WHERE order_id=:order_id");
    $get_order_operation->execute(array("order_id" => $_GET['pedido']));
    $local_operations = $get_order_operation->fetch();
    if ($local_operations) { ?>

        <div class="container-fluid">
            <!-- row -->
            <div class="row">
                <div class="col-xl-8 col-xxl-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Detalhes da Entrega</h4>
                        </div>
                        <div class="card-body">
                            <form id="CompleteOrderForm" action="completar" method="POST">
                                <input type="hidden" name="action" value="pedido-completo">
                                <input type="hidden" id="ActionInput" name="order" value="<?php echo addslashes($_GET['pedido']); ?>">
                                <div class="mb-3">
                                    <div class="mb-3">
                                        <div class="form-group">
                                            <label class="text-label">Localidade:<i class="req-mark">*</i></label>
                                            <input type="text" disabled class="form-control" value="<?php echo $local_operations['operation_name'] ?>">
                                            <input id="operation-id" type="hidden" value="<?php echo $local_operations['operation_id'] ?>" name="localidade">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-group">
                                        <label class="text-label">Selecione o operador<i class="req-mark">*</i></label>
                                        <select class="d-block default-select" id="select-ship-operator">
                                        </select>
                                    </div>
                                    <input type="hidden" id="text-ship-operator" name="operador" value="" required>
                                </div>
                                <div class="mb-3 mt-3">
                                    <div class="form-group">
                                        <label class="text-label">Qual a Forma de Pagamento?<i class="req-mark">*</i></label>
                                        <select class="d-block default-select" id="select-ship-paymethod-op">
                                            <option disabled selected>Selecione...</option>
                                            <option value="money">Dinheiro</option>
                                            <option value="credit">Cartão Crédito</option>
                                            <option value="debit">Cartão Débito</option>
                                            <option value="pix">PIX</option>
                                        </select>
                                    </div>
                                    <input type="hidden" id="text-ship-paymethod-op" name="pagamento" value="" required>
                                </div>
                                <div id="op-credit-options" class="mb-3 d-none">
                                    <div class="form-group">
                                        <label class="text-label">Número de vezes:</label>
                                        <select class="d-block default-select" id="select-ship-credit-op">
                                            <option disabled selected>Selecione...</option>
                                            <option value="1">1x</option>
                                            <option value="2">2x</option>
                                            <option value="3">3x</option>
                                            <option value="4">4x</option>
                                            <option value="5">5x</option>
                                            <option value="6">6x</option>
                                            <option value="7">7x</option>
                                            <option value="8">8x</option>
                                            <option value="9">9x</option>
                                            <option value="10">10x</option>
                                            <option value="11">11x</option>
                                            <option value="12">12x</option>
                                        </select>
                                    </div>
                                </div>
                                <input type="hidden" id="text-ship-credit-op" name="v_credito" value="" required>

                                <div class="mb-3">
                                    <div class="form-group">
                                        <label class="text-label">CPF do cliente (opcional):</label>
                                        <input type="text" id="cpf-cliente" class="form-control cpf" name="cpf-cliente">
                                    </div>
                                </div>

                                <div class="mb-3" id="div-pix-display" style="display:none">
                                    <div class="form-group">
                                        <label class="text-label">Anexar Comprovante: <i class="req-mark">*</i></label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="far fa-images"></i></span>
                                            </div>
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" name="comprovante-pagamento" accept="image/*" <?= @$disable_input ? 'disabled="disabled"': '' ?>>
                                                <label class="custom-file-label">Selecionar imagem...</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="text-label">Deseja personalizar a data de conclusão ?</label>
                                    <div class="custom-control custom-switch mb-3">
                                        <input type="checkbox" class="custom-control-input" name="custom-date" id="custom-date">
                                        <label class="custom-control-label" id="custom-date-lbl" for="custom-date">&nbsp;Não</label>
                                    </div>
                                    <div id="components-date" class="d-none">
                                        <div class="row">
                                            <div class="form-group col-md-8">
                                                <label for="datepicker">Data da conclusão</label>
                                                <input name="data" placeholder=".. ao dia" require class="datepicker-default form-control picker__input mb-2" id="datepicker" readonly="" aria-haspopup="true" aria-expanded="false" aria-readonly="false" aria-owns="datepicker_root">
                                                <div class="picker" id="datepicker_root" aria-hidden="true">
                                                    <div class="picker__holder" tabindex="-1">
                                                        <div class="picker__frame">
                                                            <div class="picker__wrap">
                                                                <div class="picker__box">
                                                                    <div class="picker__header">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label for="datepicker">Hora da conclusão</label>
                                                <input type="time" name="hours" placeholder="horas" require class="form-control mb-2">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" id="SubmitButtonComplete" class="btn btn-success mt-3 mb-3">Confirmar Entrega</button>
                                <a onclick="window.history.back();" class="btn btn-light ml-2 mt-3 mb-3"><i class="fas fa-sign-out-alt"></i> Voltar</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
    } else {
    ?>
        <div class="container-fluid">
            <div class="row col-12 d-flex align-center">
                <div class="alert alert-danger text-center w-100">Não existe estoque do produto para a cidade correspondente ao CEP inserido.</div>
            </div>
        </div>

    <?php
    }
} else if ($_GET['status'] == "enviando") { ?>
    <div class="container-fluid">
        <!-- row -->
        <div class="row">
            <div class="col-xl-8 col-xxl-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Atualizar informações do pedido para enviado</h4>
                    </div>
                    <div class="card-body">
                        <form id="UpgrateOrderForm" action="enviar" method="POST">
                            <input type="hidden" name="action" value="enviar-order">
                            <input type="hidden" name="status" value="<?= $_GET['status'] ?>">
                            <input type="hidden" id="ActionInput" name="order" value="<?php echo addslashes($_GET['pedido']); ?>">
                            <div class="mb-3">
                                <div class="form-group">
                                    <label class="text-label">Código de rastreamento<i class="req-mark">*</i></label>
                                    <input type="text" class="form-control" name="cod-rastreio" placeholder="QL576505206BR" required>
                                </div>
                            </div>

                            <div class="mb-3 mt-3">
                                <div class="form-group">
                                    <label class="text-label">Transportadora<i class="req-mark">*</i></label>
                                    <select class="d-block default-select" name="transportadora" id="select-ship-tipo" required>
                                        <option disabled selected>Selecione...</option>
                                        <option value="Correios">Correios</option>
                                        <option value="Jadlog">Jadlog</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-group">
                                    <label class="text-label">Custo de Envio da Plataforma<i class="req-mark">*</i></label>
                                    <input type="text" class="form-control money" name="custo-envio" required>
                                </div>
                            </div>
                            <div class="mb-3 mt-3">
                                <div class="form-group">
                                    <label class="text-label">Centro de distribuição<i class="req-mark">*</i></label>
                                    <select class="d-block default-select" name="centro-distribuicao" id="select-ship-locale" required>
                                        <option disabled selected>Selecione...</option>
                                        <?php
                                        $get_locale_center_list = $conn->prepare('SELECT * FROM center_locales');
                                        $get_locale_center_list->execute();

                                        while ($tab_list_iten = $get_locale_center_list->fetch()) { ?>
                                            <option value="<?php echo $tab_list_iten['center_id']; ?>"><?= $tab_list_iten['center_name']; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" id="SubmitButtonComplete" class="btn btn-success mt-3 mb-3">Confirmar envio</button>
                            <a onclick="window.history.back();" class="btn btn-light ml-2 mt-3 mb-3"><i class="fas fa-sign-out-alt"></i> Voltar</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

<?php
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>

<script>
    $('#select-ship-paymethod-op').change(function(){
        if($(this).val() == "pix"){
            $("#div-pix-display").show();
        }else{
            $("#div-pix-display").hide();
        }
    })
</script>

<script>
    $('#UpgrateOrderForm').submit(function() {

        // Verificar código de rastreio
        const value_traking_code = $('[name="cod-rastreio"]').val();

        const regex_to_validate_tracking_code = /^[A-Z]{2}\d{9}[A-Z]{2}$/.test(value_traking_code);

        if (regex_to_validate_tracking_code) {
            if ($('[name="transportadora"]').val() == null) {
                Swal.fire({
                    title: 'Dados incompletos!',
                    text: 'Selecione a Trasportadora.',
                    icon: 'error',
                });

                return false;
            }

            if ($('[name="centro-distribuicao"]').val() == null) {
                Swal.fire({
                    title: 'Dados incompletos!',
                    text: 'Informe o centro de distribuição',
                    icon: 'error',
                });

                return false;
            }

            // Captura os dados do formulário
            const UpgrateOrderForm = document.getElementById('UpgrateOrderForm');

            // Instância o FormData passando como parâmetro o formulário
            const formData = new FormData(UpgrateOrderForm);

            // Envia O FormData através da requisição AJAX
            $.ajax({
                url: u + "/ajax/upgrade-order.php",
                type: "POST",
                data: formData,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function(feedback) {
                    Swal.fire({
                        title: feedback.title,
                        text: feedback.msg,
                        icon: feedback.type,
                    }).then((value) => {
                        if (feedback.type == 'success') {
                            window.location.assign("../../pedidos/");
                        }
                    });

                }
            });


            return false;
        }

        if (!regex_to_validate_tracking_code) {
            Swal.fire({
                title: 'Algo parece não estar certo!',
                text: 'Verifique o codigo de rastreio e tente novamente',
                icon: 'info',
            });

            return false;
        }
    });

    $(document).ready(function($) {
        //ATIVA INPUT COBRAR POR FRETE
        $('#custom-date-lbl').on('click', function() {
            if ($('#custom-date').is(":checked") === false) {
                $('#custom-date-lbl').html('&nbsp; Sim'); // trocar label do botão para "sim"
                $('#components-date').removeClass('d-none');
            } else {
                $('#custom-date-lbl').html('&nbsp; Não'); // trocar label do botão para "sim"
                $('#components-date').addClass('d-none');
            }
        });

        //ATIVA INPUT COBRAR POR FRETE
        $('#confirm-pay-lbl').on('click', function() {
            if ($('#confirm-pay').is(":checked") === false) {
                $('#confirm-pay-lbl').html('&nbsp; Sim'); // trocar label do botão para "sim"
                $('#valor-pago').prop('disabled', false).attr('required', 'req');
                $('#components-pay').removeClass('d-none');
            } else {
                $('#confirm-pay-lbl').html('&nbsp; Não'); // trocar label do botão para "sim"
                $('#valor-pago').prop('disabled', true).removeAttr('required');
                $('#components-pay').addClass('d-none');
            }
        });
    });
</script>