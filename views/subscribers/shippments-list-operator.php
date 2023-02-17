
<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}

if ($_SESSION['UserPlan'] != 6) {
  header('Location: ' . SERVER_URI . '/pedidos/lista');
  exit;
}

$page_title = "Envios Realizados | Logzz";
$select_datatable_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

$get_operation_id = $conn->prepare('SELECT local_operation FROM logistic_operator WHERE user_id=:user_id');
$get_operation_id->execute(array('user_id' => $_SESSION['UserID']));

$get_ship_list = $conn->prepare('SELECT * FROM shipments WHERE ship_locale_id=:local_operation');
$get_ship_list->execute(array('local_operation' => $get_operation_id->fetch()['local_operation']));

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
                        <table id="shippments-list" class="table card-table display dataTablesCard table-sm" data-page-length='10' data-order='[[4, "desc"]]'>
                            <thead>
                                <tr>
                                    <!-- <th># ID</th> -->
                                    <th class="col-md-3">Assinante</th>
                                    <th class="col-md-2">Produto</th>
                                    <th class="col-md-1">Quant.</th>
                                    <th class="col-md-2 text-center">Local</th>
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

                                    $get_product = $conn->prepare('SELECT product_name FROM products WHERE product_id = :product_id');
                                    $get_product->execute(array('product_id' => $product_id));
                                    $product_name = $get_product->fetch();

                                    $get_locale = $conn->prepare('SELECT locale_name FROM locales WHERE locale_id = :locale_id AND locale_active = 1');
                                    $get_locale->execute(array('locale_id' => $locale_id));
                                    $locale_name = $get_locale->fetch();

                                    $get_user = $conn->prepare('SELECT 	full_name FROM users WHERE user__id = :user__id');
                                    $get_user->execute(array('user__id' => $user__id));
                                    $user_name = $get_user->fetch();

                                ?>
                                    <tr>
                                        <td class="col-md-4"><?php
                                                                if ($ship_iten['ship_invoice'] != null) {
                                                                ?>
                                                <a href="<?php echo SERVER_URI . "/uploads/envios/notas/" . $ship_iten['ship_invoice']; ?>" target="_blank" title="Ver nota fiscal anexada"><i class="fa fa-eye mr-1"></i></a>
                                            <?php
                                                                } else {
                                            ?>
                                                <a href="#" title="Sem nota fiscal anexada"><i class="fas fa-eye-slash mr-1"></i></a>
                                            <?php
                                                                }
                                                                echo $user_name['0'];
                                            ?>
                                        </td>
                                        <td class="col-md-3"><?php echo @$product_name['0']; ?></td>
                                        <td class="col-md-1"><?php echo $ship_iten['ship_quantity'];  ?></td>
                                        <td class="col-md-2 text-center"><?php echo utf8_encode(@$locale_name['0']);  ?></td>
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
                                            default:
                                                $btn_classes = "light btn-dark";
                                                $icon = "far fa-clock";
                                                $status_string = "A Enviar";
                                                break;
                                        }
                                        ?>
                                        <td data-search="<?php echo $status_string; ?>" class="here-update-badge" style="padding-left: 5px;padding-right: 5px;">
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-xs <?php echo $btn_classes; ?> dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="<?php echo $icon; ?>"></i>&nbsp;<?php echo $status_string; ?><i style="	border: 0; vertical-align: middle; margin-left: 0.25em; line-height: 1;" class="fas fa-caret-down"></i></button>
                                                    <div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 42px, 0px);">
                                                        <a class="dropdown-item update-shipment-status-link" data-status="1" data-id="<?php echo $ship_id; ?>" data-produto="<?php echo $product_id; ?>" data-local="<?php echo $locale_id; ?>" data-quantidade="<?php echo $ship_iten['ship_quantity']; ?>" href="estoque/atualizar-status">A Enviar</a>
                                                        <a class="dropdown-item update-shipment-status-link" data-status="0" data-id="<?php echo $ship_id; ?>" data-produto="<?php echo $product_id; ?>" data-local="<?php echo $locale_id; ?>" data-quantidade="<?php echo $ship_iten['ship_quantity']; ?>" href="estoque/atualizar-status">Enviado</a>
                                                        <a class="dropdown-item update-shipment-status-link" data-status="2" data-id="<?php echo $ship_id; ?>" data-produto="<?php echo $product_id; ?>" data-local="<?php echo $locale_id; ?>" data-quantidade="<?php echo $ship_iten['ship_quantity']; ?>" href="estoque/atualizar-status">Recebido</a>
                                                        <a class="dropdown-item update-shipment-status-link" data-status="3" data-id="<?php echo $ship_id; ?>" data-produto="<?php echo $product_id; ?>" data-local="<?php echo $locale_id; ?>" data-quantidade="<?php echo $ship_iten['ship_quantity']; ?>" href="estoque/atualizar-status">Problema</a>
                                                    </div>
                                                </div>
                    </div>
                </div>
            </div>

            </td>
            </tr>
        <?php
                                }
        ?>
        </tbody>
        </table>
        </div>
    </div>
    <div class="card-footer">

    </div>
</div>
</div>
</div>
</div>
<?php
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>
