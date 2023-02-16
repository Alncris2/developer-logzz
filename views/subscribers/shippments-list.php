<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID'])) || $_SESSION['UserPlan'] < 5) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}

$page_title = "Envios Realizados | Logzz"; 
$select_datatable_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

if ($_SESSION['UserPlan'] == 5) {
    # code...
    $get_ship_list = $conn->prepare('SELECT * FROM shipments ORDER BY ship_id DESC');
    $get_ship_list->execute();
} else {
    $get_ship_list = $conn->prepare('SELECT local_operation, operator_id FROM `logistic_operator` WHERE user_id = :user__id');
    $get_ship_list->execute(["user__id" => $_SESSION['UserID']]);
    if(!$dataOperator = $get_ship_list->fetch()){
        header('Location: ' . SERVER_URI . '/login');
        exit;
    }

    $get_ship_list = $conn->prepare('SELECT * FROM shipments WHERE ship_locale_id = :user__id ORDER BY ship_id DESC');
    $get_ship_list->execute(["user__id" => $dataOperator['local_operation']]);
}


?>
 
<div class="container-fluid">
    <!-- row -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Todos os Seus Envios</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="shippments-list" class="table card-table display dataTablesCard table-sm" data-page-length='10' data-order='[[5, "desc"]]'>
                            <thead>
                                <tr>
                                    <!-- <th># ID</th> -->
                                    <th class="col-md-2">Usuário</th>
                                    <th class="col-md-1">Produto</th>
                                    <th class="col-md-1">Quant.</th>
                                    <th class="col-md-1">Valor</th>
                                    <th class="col-md-2 text-center">Local</th>
                                    <th class="col-md-3">Categoria</th>
                                    <th class="col-md-1 text-center">Data</th>
                                    <th class="col-md-2">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                while ($ship_iten = $get_ship_list->fetch()) {
                                    $user__id = intval($ship_iten['ship_user_id']);
                                    $product_id = intval($ship_iten['ship_product_id']);
                                    $locale_id = intval($ship_iten['ship_locale_id']);
                                    $ship_id = intval($ship_iten['ship_id']);
                                    $type_ship = $ship_iten['type_shipments'];

                                    $get_product = $conn->prepare('SELECT product_name, product_price FROM products WHERE product_id = :product_id');
                                    $get_product->execute(array('product_id' => $product_id));
                                    while($product = $get_product->fetch()){
                                        $product_name = $product['product_name'];
                                        $product_value_total = $product['product_price'] * $ship_iten['ship_quantity'];
                                    }

                                    $get_locale = $conn->prepare('SELECT operation_name FROM local_operations WHERE operation_id = :operation_id');
                                    $get_locale->execute(array('operation_id' => $locale_id));
                                    $locale_name = $get_locale->fetch();

                                    $get_user = $conn->prepare('SELECT full_name FROM users WHERE user__id = :user__id');
                                    $get_user->execute(array('user__id' => $user__id));
                                    $user_name = $get_user->fetch();

                                    if ($type_ship == 1) {
                                        $get_name_center_locale = $conn->prepare("SELECT center_name FROM center_locales WHERE center_id = :center_id");
                                        $get_name_center_locale->execute(['center_id' => '1']);
                                        $name = $get_name_center_locale->fetch(\PDO::FETCH_ASSOC)['center_name'];
                                    }


                                ?>
                                    <tr>
                                        <td class="col-md-3">
                                            <?php
                                            if ($ship_iten['ship_invoice'] != null) {
                                            ?>
                                                <a href="<?php echo SERVER_URI . "/uploads/envios/notas/" . $ship_iten['ship_invoice']; ?>" target="_blank" title="Ver nota fiscal anexada"><i class="fa fa-eye mr-1"></i></a>
                                            <?php
                                            } else {
                                            ?>
                                                <a href="#" title="Sem nota fiscal anexada"><i class="fas fa-eye-slash mr-1"></i></a>
                                            <?php
                                            }
                                            echo @$user_name['0'];
                                            ?>
                                        </td>
                                        <td class="col-md-2"><?php echo @$product_name; ?></td>    
                                        <td class="col-md-1"><?php echo $ship_iten['ship_quantity'];  ?></td>
                                        <td class="col-md-2"><?php echo 'R$ '. number_format($product_value_total, 2, ',', '.');  ?></td>
                                        <td class="col-md-2 text-center"><?php echo $type_ship == 1 ? @$name : @$locale_name[0] ?></td>
                                        <td class="col-md-3"><?= $type_ship == 0 ? 'Operação Local' : 'Centro de Distribuição' ?></td>
                                        <td data-order="<?php echo date_format(date_create($ship_iten['ship_date']), 'YmdHi'); ?>" class="col-md-2 text-center"><?php echo date_format(date_create($ship_iten['ship_date']), 'd/m');  ?></td>
 
                                        <?php
                                        switch ($ship_iten['ship_status']) {
                                            case 0:
                                                $btn_classes = "btn-warning";
                                                $icon = "far fa-clock";
                                                $status_string = "Enviado";
                                                break;
                                            case 2:
                                                $btn_classes = "light btn-success";
                                                $icon = "far fa-check-circle";
                                                $status_string = "Recebido";
                                                break;
                                            case 3:
                                                $btn_classes = "btn-danger";
                                                $icon = "fas fa-exclamation-circle";
                                                $status_string = "Problema";
                                                break;
                                            case 4:
                                                $btn_classes = "btn-danger";
                                                $icon = "fa-times-circle fa";
                                                $status_string = "Esgotado"; 
                                                break;
                                            default:
                                                $btn_classes = "light btn-dark";
                                                $icon = "far fa-clock";
                                                $status_string = "A Enviar";
                                                break;
                                        } ?>
                                        <td data-search="<?php echo $status_string; ?>" class="here-update-badge" style="padding-left: 5px;padding-right: 5px;">
                                            <?php
                                            if ($_SESSION['UserPlan'] == 5) { ?>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-xs <?php echo $btn_classes; ?> dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="<?php echo $icon; ?>"></i>&nbsp;<?php echo $status_string; ?><i style="	border: 0; vertical-align: middle; margin-left: 0.25em; line-height: 1;" class="fas fa-caret-down"></i></button>
                                                    <div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 42px, 0px);">
                                                        <button class="dropdown-item update-shipment-status-link2" data-status="1" data-id="<?php echo $ship_id; ?>" data-produto="<?php echo $product_id; ?>" data-local="<?php echo $locale_id; ?>" data-quantidade="<?php echo $ship_iten['ship_quantity']; ?>" data-type="<?= $type_ship ?>">A Enviar</button>
                                                        <button class="dropdown-item update-shipment-status-link2" data-status="0" data-id="<?php echo $ship_id; ?>" data-produto="<?php echo $product_id; ?>" data-local="<?php echo $locale_id; ?>" data-quantidade="<?php echo $ship_iten['ship_quantity']; ?>" data-type="<?= $type_ship ?>">Enviado</button>
                                                        <button class="dropdown-item update-shipment-status-link2" data-status="2" data-id="<?php echo $ship_id; ?>" data-produto="<?php echo $product_id; ?>" data-local="<?php echo $locale_id; ?>" data-quantidade="<?php echo $ship_iten['ship_quantity']; ?>" data-type="<?= $type_ship ?>">Recebido</button>
                                                        <button class="dropdown-item update-shipment-status-link2" data-status="3" data-id="<?php echo $ship_id; ?>" data-produto="<?php echo $product_id; ?>" data-local="<?php echo $locale_id; ?>" data-quantidade="<?php echo $ship_iten['ship_quantity']; ?>" data-type="<?= $type_ship ?>">Problema</button>
                                                        <button class="dropdown-item update-shipment-status-link2" data-status="4" data-id="<?php echo $ship_id; ?>" data-produto="<?php echo $product_id; ?>" data-local="<?php echo $locale_id; ?>" data-quantidade="<?php echo $ship_iten['ship_quantity']; ?>" data-type="<?= $type_ship ?>">Esgotado</button> 
                                                    </div>
                                                </div>
                                            <?php
                                            } else { ?>
                                                <span class="badge badge-xs <?php echo $btn_classes; ?> mb-1"><i class="<?php echo $icon; ?>"></i> <?php echo $status_string; ?></span>
                                            <?php
                                            } ?>
                                        </td>
                                    </tr>
                                <?php
                                } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer"></div>
            </div>
        </div>
    </div>
</div> 
<?php
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>

<script>
    function updateShipmentStatusLink() {

        let u = location.protocol + "//" + window.location.hostname;
        event.preventDefault();

        element = $(this);
        console.log(element);
        return;

        var status = element.attr('data-status');
        var id = element.attr('data-id');
        var product = element.attr('data-produto');
        var locale = element.attr('data-local');
        var type = element.attr('data-type')
        var quant = element.attr('data-quantidade');

        var url = u + "/ajax/update-shipment-status.php";


        // Envia os parâmetro para o PHP via AJAX
        $.ajax({
            url: url,
            type: "GET",
            data: {
                status,
                id,
                product,
                locale,
                quant,
                type
            },
            dataType: 'json',
            processData: true,
            contentType: false,
            success: function(feedback) {
                Swal.fire({
                    title: "Sucesso!",
                    text: feedback.msg,
                    icon: 'success',
                }).then((value) => {
                    document.location.reload(true);
                });
            }
        });
        return false;
    };

    //Update shipment status from /pedidos in ADM account.
    $('.update-shipment-status-link2').click(function() {

        event.preventDefault();

        var status = this.getAttribute('data-status');
        var id = this.getAttribute('data-id');
        var product = this.getAttribute('data-produto');
        var locale = this.getAttribute('data-local');
        var type = this.getAttribute('data-type')
        var quant = this.getAttribute('data-quantidade');

        var url = u + "/ajax/update-shipment-status.php";


        // Envia os parâmetro para o PHP via AJAX
        $.ajax({
            url: url,
            type: "GET",
            data: {
                status,
                id,
                product,
                locale,
                quant,
                type
            },
            dataType: 'json',
            processData: true,
            contentType: false,
            success: function(feedback) {
                Swal.fire({
                    title: "Sucesso!",
                    text: feedback.msg,
                    icon: 'success',
                }).then((value) => {
                    document.location.reload(true);
                });
            }
        });

        return false;
    });
</script>