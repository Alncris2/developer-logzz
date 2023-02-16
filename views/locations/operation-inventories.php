<?php

require_once (dirname(__FILE__) . '/../../includes/config.php');
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

$page_title = "Meus Estoques | Logzz";
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

$user__id = $_SESSION['UserID'];

$get_operation_id = $conn->prepare("SELECT logistic_operator.local_operation, logistic_operator.operator_id FROM logistic_operator WHERE logistic_operator.user_id = :user__id");
$get_operation_id->execute(array("user__id" => $user__id));

$data = $get_operation_id->fetch();
$operation_id = $data["local_operation"];
$operator_id = $data["operator_id"];

#Lista as Localidades para as Tabs
$get_locale_list = $conn->prepare('SELECT * FROM local_operations WHERE operation_id = :operation_id');
$get_locale_list->execute(array("operation_id" => $operation_id));

?>
<div class="container-fluid">
  <!-- row -->
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">Estoques de sua Operação Local</h4>
        </div>
        <div class="card-body">
          <div class="basic-list-group">
            <div class="row">
              <div class="col-lg-12">
                <div class="tab-content" id="nav-tabContent">
                <?php
                    while($tab_list_iten = $get_locale_list->fetch()) {
                        $inventory_locale_id = $tab_list_iten['operation_id'];
                ?>
                  <div>
                    <h4 class="mb-4">Produtos em <?php echo $tab_list_iten['operation_name']; ?></h4>
                    <table id="inventory-list-<?php echo $tab_list_iten['operation_id'] ?>" class="table card-table display dataTablesCard" data-page-length='100' data-order='[[0, "desc"]]'>
                        <thead>
                            <tr>
                            <th class="col-md-4 text-left" style="padding: 10px 10px;padding-left: 15px">Produto</th>
                            <th class="col-md-3 text-left" style="padding: 10px 10px;">Estoque Atual</th>
                            <th class="col-md-3 text-left" style="padding: 10px 10px;">Último Envio</th>
                            <th class="col-md-2 text-left" style="padding: 10px 10px;">Quant. Últ. Envio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $get_inventory = $conn->prepare('SELECT * FROM inventories i INNER JOIN products p ON i.inventory_product_id = p.product_id WHERE i.inventory_locale_id = :inventory_locale_id AND i.ship_locale = 0 ORDER BY i.inventory_id DESC');
                                $get_inventory->execute(array('inventory_locale_id' => $inventory_locale_id));
                                
                                while($inventory_item = $get_inventory->fetch()) {
                                    $current_product_id = $inventory_item['product_id'];
                                    
                            ?>
                            <tr>
                              <td style="padding: 10px 10px;padding-left: 15px"><?php echo $inventory_item['product_name']?></td>

                            <?php
                              $quantity = $inventory_item['inventory_quantity'];
                              $inventory_last_ship = $inventory_item['inventory_last_ship'];
                              $inventory_last_ship_quant = $inventory_item['inventory_last_ship_quant'];
                            ?>
                              <td><?php echo $quantity; ?></td>
                              <td><?php echo date_format(date_create($inventory_last_ship), 'd/m/Y'); ?></td>
                              <td><?php echo $inventory_last_ship_quant; ?></td>                                
                            </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                  </div>
                  <?php
                   }
                    ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
    require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>
