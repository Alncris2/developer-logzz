<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}

$page_title = "Envios Realizados | Logzz";
$select_datatable_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

$ship_user_id = $_SESSION['UserID'];
$get_ship_list = $conn->prepare('SELECT * FROM shipments WHERE ship_user_id = :ship_user_id');
$get_ship_list->execute(array('ship_user_id' => $ship_user_id));

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

                                    $get_user = $conn->prepare('SELECT 	full_name FROM users WHERE user__id = :user__id');
                                    $get_user->execute(array('user__id' => $user__id));
                                    $user_name = $get_user->fetch();
                                    
                                    if($type_ship == 1){
                                        $get_name_center_locale = $conn->prepare("SELECT center_name FROM center_locales WHERE center_id = :center_id");
                                        $get_name_center_locale->execute(['center_id' => '1']);
                                        $name = $get_name_center_locale->fetch(\PDO::FETCH_ASSOC)['center_name'];
                                    }

                                ?>
                                    <tr>
                                        <td class="col-md-3">
                                            <?php if ($ship_iten['ship_invoice'] != null) : ?>
                                                <a href="<?php echo SERVER_URI . "/uploads/envios/notas/" . $ship_iten['ship_invoice']; ?>" target="_blank" title="Ver nota fiscal anexada"><i class="fa fa-eye mr-1"></i></a>
                                            <?php else : ?>
                                                <a href="#" title="Sem nota fiscal anexada"><i class="fas fa-eye-slash mr-1"></i></a>
                                            <?php endif; ?>

                                            <?= $user_name['0']; ?>
                                        </td>
                                        <td class="col-md-2">
                                            <?= @$product_name[0]; ?>
                                        </td>
                                        <td class="col-md-1">
                                            <?= $ship_iten['ship_quantity'];  ?>
                                        </td>
                                        <td class="col-md-2 text-center">
                                            <?php echo $type_ship == 1 ? $name : $locale_name[0]?>
                                        </td>
                                        <td class="col-md-3"><?= $type_ship == 0 ? 'Operação Local' : 'Centro de Distribuição' ?></td>
                                        <td data-order="<?php echo date_format(date_create($ship_iten['ship_date']), 'YmdHi'); ?>" class="col-md-2 text-center"><?php echo date_format(date_create($ship_iten['ship_date']), 'd/m/Y');  ?></td>
                                        <td data-search="<?php echo $status_string; ?>" class="here-update-badge" style="padding-left: 5px;padding-right: 5px;">
                                            <?php switch ($ship_iten['ship_status']) {
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

                                            <span class="badge badge-xs <?php echo $btn_classes; ?> mb-1"><i class="<?php echo $icon; ?>"></i> <?php echo $status_string .' - '.  $ship_iten['ship_status']?> </span>
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
