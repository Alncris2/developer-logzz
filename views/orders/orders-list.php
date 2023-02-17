<?php
// error_reporting(-1);            
// ini_set('display_errors', 1);  
require_once(dirname(__FILE__) . '/../../includes/config.php');

session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}

if ($_SESSION['UserPlan'] == 6) {
    header('Location: ' . SERVER_URI . '/pedidos/lista-operador');
    exit;
}

$page_title = "Pedidos | Logzz";
$sidebar_expanded = false;
$orders_page = $select_datatable_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

//Busca o nível do usuário com base no ID
$UserPlan = $_SESSION['UserPlan'];
$user__id = $_SESSION['UserID'];
$is_affi = false;

if($_SESSION['UserPlan'] == 5){
    $get_product_list = $conn->prepare('SELECT * FROM products WHERE product_trash = 0 AND status = 1');
    $get_product_list->execute();

    $query_afi = $conn->prepare("SELECT DISTINCT u.user__id, u.full_name FROM users u INNER JOIN memberships m ON u.user__id = m.membership_affiliate_id INNER JOIN orders o ON o.user__id = m.membership_affiliate_id AND m.membership_product_id = o.product_id ORDER BY `user__id`");
    $query_afi->execute();

    $query_prod = $conn->prepare("SELECT u.user__id, u.full_name FROM users u WHERE (SELECT COUNT(*) FROM products p WHERE p.user__id = u.user__id) > 0 ORDER BY full_name");
    $query_prod->execute(); 
    $data_prod = $query_prod->fetchAll(\PDO::FETCH_ASSOC);

}else{
    $get_product_list = $conn->prepare('SELECT * FROM products WHERE user__id = :user__id AND product_trash = 0 AND status = 1');
    $get_product_list->execute(array('user__id' => $user__id));

    $query_afi = $conn->prepare("SELECT DISTINCT u.user__id, u.full_name FROM users u INNER JOIN memberships m ON u.user__id = m.membership_affiliate_id INNER JOIN orders o ON o.user__id = m.membership_affiliate_id AND m.membership_product_id = o.product_id AND m.membership_producer_id = :user__id ORDER BY `user__id`");
    $query_afi->execute(['user__id' => $user__id]);
     
}

$stmt = $conn->prepare("SELECT * FROM orders INNER JOIN sales ON orders.sale_id = sales.sale_id AND user__id = :user__id");
$stmt->execute(array('user__id' => $user__id));
$num_filter_row = $stmt->rowCount();

$data_user = $query_afi->fetchAll(\PDO::FETCH_ASSOC);
?>

<style>
    #filtersList {
        overflow-y: scroll;
    }

    #filtersList::-webkit-scrollbar {
        background-color: #EEEEEE;
        width: 7px;
        height: 7px;
    }

    #filtersList::-webkit-scrollbar-track {
        border-radius: 40px;
        background-color: #F5F5F5;
    }

    #filtersList::-webkit-scrollbar-thumb {
        border-radius: 40px;
        background-color: #2fde91;
    }

    .btn.option {
        padding: 1rem 1rem;
        border-radius: 1.25rem;
        font-weight: 500;
        font-size: .8rem;
        display: flex;
        color: #495057;
        align-items: center;
        justify-content: center;
    }

    .input-group input {
        width: 47%; 
        border: 0;
    } 
    .input-group button {
        width: 53%;
    }  

    .chatbox {
        width: 430px;
    }

    .chatbox .chatbox-close {
        right: 430px;
    }
    
    @media only screen and (max-width: 576px) {
        .chatbox {
            width: 100vw;
        }
    }

</style>
<?php $newArr = [] ?>

<!-- TEMP -->
<input type="hidden" id="user_plan" value="<?= $_SESSION['UserPlan']; ?>">
<input type="hidden" id="user_id" value="<?= $_SESSION['UserID']; ?>">

<div class="container-fluid">
    <?php if($num_filter_row == 0): ?>
        <div id="no-sale" class="alert alert-success solid fade show mb-3 d-none">
            <i class="fa fa-info-circle" aria-hidden="true"></i><strong> Você ainda não fez a sua primeira venda.</strong> Assim que ela acontecer, todos os dados aparecerão aqui.
        </div>
    <?php endif; ?>
    <div class="d-flex flex-wrap mb-2 align-items-center justify-content-between">
        <div class="mb-3 mr-3">            
            <h6 id="result" class="fs-16 text-black font-w600 mb-0"></h6>            
            <?php
                if ($_SESSION['UserPlan'] == 5) {
                ?>
                    <span class="fs-14">Usuário <b>Administrador</b></span>
                <?php
                } else {
                ?>
                    <span class="fs-14">Plano: <b><?php echo $_SESSION['UserPlanString']; ?></b></span>
                <?php
                }
            ?>
        </div>
        <div class="event-tabs mb-3 mr-3">
        </div>
        <div class="d-flex mb-3">            
            <?php if($_SESSION['UserPlan'] == 5){ ?>
                <button type="button" class="btn btn-rounded btn-outline-success mr-2" data-toggle="dropdown" aria-expanded="true">
                    <i class="fa fa-truck-moving scale2 mr-2" aria-hidden="true"></i>&nbsp; Expedir  
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item export-orders-truck" href="<?= SERVER_URI .'/pedidos/expedicao-local-a/' ?>" target="_blanck">
                        A4
                    </a>
                    <a class="dropdown-item export-orders-truck" href="<?= SERVER_URI .'/pedidos/expedicao-local-t/' ?>" target="_blanck">
                        Térmica 
                    </a>
                </div>
            <?php } ?>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-rounded btn-outline-success mr-2" data-toggle="dropdown" aria-expanded="true"><i class="fas fa-download scale2 mr-2"></i> Exportar</button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="#" id="export-to-csv"><i class="fas fa-file-csv scale2 mr-2"></i></i> CSV</a>
                    <a class="dropdown-item" href="#" id="export-to-xlsx"><i class="fas fa-file-excel scale2 mr-2"></i> Excel</a>
                    <a class="dropdown-item" href="#" id="export-to-pdf"><i class="fas fa-file-pdf scale2 mr-2"></i> PDF</a>
                </div>
            </div>
            <button type="button" class="btn btn-rounded btn-success filter-btn" class="btn btn-success text-nowrap"><i class="fas fa-sliders-h scale2 mr-2" aria-hidden="true"></i>Filtros</button>
        </div>
    </div>    
    <div class="row ">
        <div class="col-xl-12" style="padding-left: 5px;">
            <div class="tab-content">
                <div id="All" class="tab-pane active fade show">
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table id="orders-list-ajax" class="table card-table display dataTablesCard" data-page-length='30' data-order='[[0, "desc"]]'>
                            <thead>
                                <?php if($_SESSION['UserPlan'] == 5): ?>
                                    <!-- <tr>
                                        <th class="col-md-1">Pedido</th>
                                        <th class="col-md-1">Cliente</th>
                                        <th class="col-md-2">Produto</th>
                                        <th class="col-md-1 d-none">Oferta</th>
                                        <th class="col-md-1">Qnt.</th>
                                        <th class="col-md-1">Entreg.</th>
                                        <th class="col-md-1">Fatur. (R$)</th>
                                        <th class="col-md-1">Despesas. (R$)</th>
                                        <th class="col-md-1">Lucro. (R$)</th>
                                        <th class="">Stat.</th>
                                    </tr> -->
                                <?php else: ?>
                                    <!-- <tr>
                                        <th class="col-md-1">Pedido</th>
                                        <th class="col-md-1">Cliente</th>
                                        <th class="col-md-2">Produto</th>
                                        <th class="col-md-1 d-none">Oferta</th>
                                        <th class="col-md-1">Qnt.</th>
                                        <th class="col-md-1">Entreg.</th>
                                        <th class="col-md-1">Fatur. (R$)</th>
                                        <th class="col-md-1">Taxa (R$)</th>
                                        <th class="col-md-1">Entreg. (R$)</th>
                                        <th class="col-md-1">Comis. (R$)</th>
                                        <th class="">Stat.</th>
                                    </tr> -->
                                <?php endif; ?>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="chatbox">
        <div class="chatbox-close"></div>
        <div class="col-xl-12" style="height: 100vh;">
            <div class="card">
                <div class="mt-4 center text-center">
                    <h4 class="card-title">Filtros</h4>
                </div>
                <div class="card-body" id="filtersList">
                    <div class="row">
                        <div class="col-lg-12 mb-2">
                            <form id="filter-form">
                                <div class="mb-3">
                                    <p class="mb-1">por Data</p>
                                    <input type="hidden" class="form-control mb-2" name="filtro" value="ativo">
                                    
                                    <div class="input-group form-control mb-2">
                                        <input name="data-inicio" value="<?php echo @addslashes($_GET['data-inicio']); ?>" placeholder="Do dia ..." class="datepicker-default picker__input" id="datepicker" readonly="" aria-haspopup="true" aria-expanded="false" aria-readonly="false" aria-owns="datepicker_root">
                                        <button type="button" disabled class="btn btn-outline-dark btn-sm modify-time px-1 h-100"">Apartir de 
                                            <input type="time" class="bg-transparent border-0" name="time-inicio" id="time-inicio" value="00:00">
                                        </button>
                                    </div>

                                    <div class="input-group form-control mb-2"> 
                                        <input name="data-final" value="<?php echo @addslashes($_GET['data-final']); ?>" placeholder=".. ao dia" class="datepicker-default picker__input" id="datepicker" readonly="" aria-haspopup="true" aria-expanded="false" aria-readonly="false" aria-owns="datepicker_root">
                                        <button type="button" disabled class="btn btn-outline-dark btn-sm modify-time px-1 h-100">Até de 
                                            <input type="time" class="bg-transparent border-0" name="time-final" id="time-final" value="23:59">
                                        </button>                                        
                                    </div>

                                    <div class="form-group">
                                        <select class="segment-select" name="reference-data" id="reference-data">
                                            <option value="agendamento">Pedido</option>
                                            <option value="entrega">Entrega</option>
                                            <option value="reembolso">Reemb.</option>
                                        </select>
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

                                    <p class="mb-1">por Cliente</p>
                                    <input type="text" class="form-control mb-2" name="nome-cliente" value="<?php echo @addslashes($_GET['nome-cliente']); ?>" placeholder="Nome do Cliente">

                                    <p class="mb-1">por Documento</p>
                                    <input type="text" class="form-control mb-2 documento" name="documento-cliente" value="<?php echo @addslashes($_GET['documento-cliente']); ?>" placeholder="Documento do Cliente">

                                    <div class="form-group">
                                        <label class="text-label">por Produto</label>
                                        <select id="select-ship-product" class="d-block default-select" multiple="multiple" data-live-search="true">
                                            <option value="" selected>Todos</option>
                                            <?php
                                            while ($prodcut = $get_product_list->fetch()) {
                                            ?>
                                                <option <?php if (!(empty(@$_GET['produto'])) && $_GET['produto'] == $prodcut['product_name']) echo "selected" ?> value="<?php echo $prodcut['product_name']; ?>">
                                                    <?php if (strlen($prodcut['product_name']) > 25) {
                                                        echo substr($prodcut['product_name'], 0, 25) . "...";
                                                    } else {
                                                        echo $prodcut['product_name'];
                                                    } ?>
                                                </option>
                                            <?php
                                            }
                                            ?>
                                        </select>
                                        <input type="hidden" id="text-ship-product" name="produto" value="<?php echo @addslashes($_GET['produto']); ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label class="mb-1">por Status</label> 
                                        <select class="form-control default-select" id="select-filter-status-id" multiple="multiple">
                                            <option value="" selected>Todos</option>
                                            <option value="1" <?php if (!(empty(@$_GET['status'])) && $_GET['status'] == 1) echo "selected" ?>>Agendada</option>
                                            <option value="3" <?php if (!(empty(@$_GET['status'])) && $_GET['status'] == 3) echo "selected" ?>>Atrasada</option>
                                            <option value="6" <?php if (!(empty(@$_GET['status'])) && $_GET['status'] == 6) echo "selected" ?>>Cancelada</option>
                                            <option value="5" <?php if (!(empty(@$_GET['status'])) && $_GET['status'] == 5) echo "selected" ?>>Frustrada</option>
                                            <option value="4" <?php if (!(empty(@$_GET['status'])) && $_GET['status'] == 4) echo "selected" ?>>Completa</option>
                                            <option value="2" <?php if (!(empty(@$_GET['status'])) && $_GET['status'] == 2) echo "selected" ?>>Reagendada</option>
                                            <option value="7" <?php if (!(empty(@$_GET['status'])) && $_GET['status'] == 7) echo "selected" ?>>Á Enviar</option>
                                            <option value="8" <?php if (!(empty(@$_GET['status'])) && $_GET['status'] == 8) echo "selected" ?>>Enviando</option>
                                            <option value="9" <?php if (!(empty(@$_GET['status'])) && $_GET['status'] == 9) echo "selected" ?>>Enviado</option>                                            
                                            <option value="10" <?php if (!(empty(@$_GET['status'])) && $_GET['status'] == 10) echo "selected" ?>>Reembolsado</option>
                                            <option value="11" <?php if (!(empty(@$_GET['status'])) && $_GET['status'] == 11) echo "selected" ?>>Confirmado</option>
                                            <option value="12" <?php if (!(empty(@$_GET['status'])) && $_GET['status'] == 12) echo "selected" ?>>Em Aberto</option>
                                            <option value="13" <?php if (!(empty(@$_GET['status'])) && $_GET['status'] == 13) echo "selected" ?>>Indisponível</option>
                                        </select>
                                    </div>

                                    <input type="hidden" id="text-filter-status-id" name="status" value="" required>
                                    <p class="mb-1 mt-2">por WhatsApp</p>
                                    <input type="text" class="form-control mb-2 phone" name="numero-cliente-produto" value="<?php echo @addslashes($_GET['numero-cliente-produto']); ?>" placeholder="Número do Cliente">

                                    <?php if($UserPlan == 5) { ?> 
                                        <div class="form-group afi-group">
                                            <label class="text-label"><small>por Produtor</small></label>
                                            <select id="select-producers" class="d-block default-select" data-live-search="true">
                                                <option id="all-producers-option" value="" selected>Todos</option>
                                                <?php foreach ($data_prod as $data) { ?> 
                                                    <option <?php if (!(empty(@$_GET['produtor'])) && $_GET['produtor'] == $data['user__id']) echo "selected" ?> value="<?php echo $data['user__id']; ?>">
                                                        <?php if (strlen($data['full_name']) > 25) {
                                                            echo substr($data['full_name'], 0, 25) . "...";
                                                        } else {
                                                            echo $data['full_name'];
                                                        } ?>
                                                    </option>
                                                <?php } ?>  
                                            </select>
                                            <input type="hidden" id="text-select-producers" name="produtor" value="">
                                        </div>
                                    <?php } ?>  

                                    <div class="form-group afi-group">
                                        <label class="text-label"><small>por Afiliado</small></label>
                                        <select id="select-affiliates" class="d-block default-select" data-live-search="true">
                                            <option id="all-affiliates-option" value="" selected>Todos</option>
                                            <?php

                                            $array_filter = [];

                                            foreach ($data_user as $data) {
                                                $arr = [
                                                    'id' => $data['user__id'],
                                                    'name' => ucwords(strtolower($data['full_name']))
                                                ];

                                                if (!in_array($arr, $array_filter)) {
                                                    array_push($array_filter, $arr);
                                                }
                                            }
                                            ?>

                                            <?php foreach ($array_filter as $data) : ?>
                                                <option <?php if (!(empty(@$_GET['produto'])) && $_GET['produto'] == $data['id']) echo "selected" ?> value="<?php echo $data['id']; ?>">
                                                    <?php if (strlen($data['name']) > 25) {
                                                        echo substr($data['name'], 0, 25) . "...";
                                                    } else {
                                                        echo $data['name'];
                                                    } ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="hidden" id="text-select-affiliates" name="afiliado" value="">
                                    </div>

                                    <div class="form-group">
                                        <label class="text-label"><small>por Operação Local</small></label>
                                        <select id="select-operations" class="d-block default-select" data-live-search="true">
                                            <option id="all-operations-option" value="" selected>Todas</option>
                                            <?php
                                            $get_local_operations = $conn->prepare("SELECT * FROM local_operations WHERE operation_active = 1");
                                            $get_local_operations->execute();

                                            while ($operation = $get_local_operations->fetch()) { ?>
                                                <option <?php if (!(empty(@$_GET['operacao'])) && $_GET['operacao'] == $operator["operation_id"]) {
                                                            echo "selected";
                                                        } ?>value="<?= $operation["operation_id"] ?>"><?= $operation["operation_name"] ?></option>
                                            <?php } ?>

                                            ?>
                                        </select>
                                        <input type="hidden" id="text-select-operations" name="operacao" value="">
                                    </div>

                                    <div class="form-group">
                                        <label class="text-label"><small>por Operador Logístico</small></label>
                                        <select id="select-operators" class="d-block default-select" data-live-search="true">
                                            <option id="all-operators-option" value="" selected>Todos</option>
                                            <option value="indefinido">Indefinido</option>
                                            <?php
                                                $get_logistic_operators = $conn->prepare("SELECT * FROM logistic_operator lo INNER JOIN users u ON u.user__id = lo.user_id AND u.active = 1");
                                                $get_logistic_operators->execute();

                                                while($operator = $get_logistic_operators->fetch()) {
                                                    echo "<option value='" . $operator["operator_id"] ."'>" . $operator["full_name"] . "</option>";
                                                }

                                            ?>
                                        </select>
                                        <input type="hidden" id="text-select-operators" name="operador" value="">
                                    </div>

                                </div>
                                <button type="submit" class="btn btn-block btn-success text-nowrap mt-2"><i class="fas fa-sliders-h" aria-hidden="true"></i> Aplicar Filtros</button>
                                <a href="<?php echo SERVER_URI; ?>/pedidos/" class="btn btn-block mt-2">Limpar Filtros</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="calcelOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="cancelationForm" class="modal-content">
            <div class="modal-header text-center">
                <h5 class="modal-title w-100 text-center" id="calcelOrderModalLabel">Justificativa de Cancelamento</h5>
            </div>

            <div class="modal-body">
                <input type="hidden" id="cancel_status" name="status">
                <input type="hidden" id="cancel_id" name="id">

                <div class="form-check mb-2">
                    <input class="form-check-input mt-2" type="radio" name="status_description" id="statusDescription1" value="Cliente disse que estava sem dinheiro" required>
                    <label class="form-check-label" for="statusDescription1">
                        Cliente disse que estava sem dinheiro
                    </label>
                </div>

                <div class="form-check mb-2">
                    <input class="form-check-input mt-2" type="radio" name="status_description" id="statusDescription2" value="Cliente disse que não poderia estar em casa no horário da rota">
                    <label class="form-check-label" for="statusDescription2">
                        Cliente disse que não poderia estar em casa no horário da rota
                    </label>
                </div>

                <div class="form-check mb-2">
                    <input class="form-check-input mt-2" type="radio" name="status_description" id="statusDescription3" value="Cliente disse que vai remarcar com o vendedor">
                    <label class="form-check-label" for="statusDescription3">
                        Cliente disse que vai remarcar com o vendedor
                    </label>
                </div>

                <div class="form-check mb-2">
                    <input class="form-check-input mt-2" type="radio" name="status_description" id="statusDescription4" value="Cliente disse que não queria mais o produto">
                    <label class="form-check-label" for="statusDescription4">
                        Cliente disse que não queria mais o produto
                    </label>
                </div>

                <div class="form-check mb-2">
                    <input class="form-check-input mt-2" type="radio" name="status_description" id="statusDescription5" value="Cliente disse que não havia confirmado a entrega">
                    <label class="form-check-label" for="statusDescription5">
                        Cliente disse que não havia confirmado a entrega
                    </label>
                </div>

                <div class="form-check mb-2">
                    <input class="form-check-input mt-2" type="radio" name="status_description" id="statusDescription6" value="0">
                    <label class="form-check-label" for="statusDescription6">
                        Outros
                    </label>
                </div>

                <div class="form-group">
                    <input type="text" id="otherDescription" class="form-control" name="other_description" maxlength="150" style="display: none;">
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                <button type="submit" class="btn btn-primary">Confirmar Cancelamento</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="unavailableOrderModal" tabindex="-1" aria-labelledby="unavailableOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="unavailableForm" class="modal-content">
            <div class="modal-header text-center">
                <h5 class="modal-title w-100 text-center" id="unavailableOrderModalLabel">Justificativa de Indisponibilidade</h5>
            </div>

            <div class="modal-body">
                <input type="hidden" id="unavailable_status" name="status">
                <input type="hidden" id="unavailable_id" name="id">

                <div class="form-check mb-2">
                    <input class="form-check-input mt-2" type="radio" name="status_description" id="statusDescription1" value="Entregador responsável pelo pedido não pôde realizar" required>
                    <label class="form-check-label" for="unavailableDescription1">
                        Entregador responsável pelo pedido não pôde realizar
                    </label>
                </div>

                <div class="form-check mb-2">
                    <input class="form-check-input mt-2" type="radio" name="status_description" id="statusDescription2" value="Atraso na reposição de estoque">
                    <label class="form-check-label" for="unavailableDescription2">
                        Atraso na reposição de estoque
                    </label>
                </div>

                <div class="form-check mb-2">
                    <input class="form-check-input mt-2" type="radio" name="status_description" id="statusDescription6" value="0">
                    <label class="form-check-label" for="unavailableDescription6">
                        Outro
                    </label>
                </div>

                <div class="form-group">
                    <input type="text" id="unavailableDescription" class="form-control" name="other_description" maxlength="150" style="display: none;">
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Voltar</button>
                <button type="submit" class="btn btn-primary">Confirmar</button>
            </div>
        </form>
    </div>
</div>

<?php require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php'); ?>

<script>
    $(document).ready(function() {

        $("#select-producers").change(function () {
            var multipleValues = $("#select-producers").val();
            $("#text-select-producers").val(multipleValues);
        }); 

        $('.modify-time').on('click', function() {
            $(this).next().click();
        });

    });

</script>