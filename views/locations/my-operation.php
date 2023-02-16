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

$page_title = "Minha Operação Local | Logzz";
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

$user__id = $_SESSION['UserID'];

$get_operation_id = $conn->prepare("SELECT logistic_operator.local_operation, logistic_operator.operator_id FROM logistic_operator WHERE logistic_operator.user_id = :user__id");
$get_operation_id->execute(array("user__id" => $user__id));

$data = $get_operation_id->fetch();
$operation_id = $data["local_operation"];
$operator_id = $data["operator_id"];

#Lista as Localidades para as Tabs
$get_local_operation = $conn->prepare('SELECT * FROM local_operations WHERE operation_id = :operation_id');
$get_local_operation->execute(array("operation_id" => $operation_id));

$get_operation_locales = $conn->prepare('SELECT * FROM operations_delivery_taxes WHERE operation_id=:operation_id AND operator_id=:operator_id');
$get_operation_locales->execute(array("operation_id" => $operation_id, "operator_id" => $operator_id));

while($tab_list_item = $get_local_operation->fetch()) {
?>
<div class="container-fluid">
  <!-- row -->
  <div class="row">
    <div class="col-lg-12">
    <?php if($tab_list_item['operation_active'] == 0): ?>
            <div class="alert  text-white bg-warning solid fade show mb-3" style="">
                <div class="d-flex align-items-center">
                    <svg version="1.1" id="Ebene_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                        width="45px" height="45px" viewBox="0 0 64 64" enable-background="new 0 0 64 64" xml:space="preserve">
                        <g>
                            <path d="M17.586,46.414C17.977,46.805,18.488,47,19,47s1.023-0.195,1.414-0.586L32,34.828l11.586,11.586
                                C43.977,46.805,44.488,47,45,47s1.023-0.195,1.414-0.586c0.781-0.781,0.781-2.047,0-2.828L34.828,32l11.586-11.586
                                c0.781-0.781,0.781-2.047,0-2.828c-0.781-0.781-2.047-0.781-2.828,0L32,29.172L20.414,17.586c-0.781-0.781-2.047-0.781-2.828,0
                                c-0.781,0.781-0.781,2.047,0,2.828L29.172,32L17.586,43.586C16.805,44.367,16.805,45.633,17.586,46.414z"/>
                            <path d="M32,64c8.547,0,16.583-3.329,22.626-9.373C60.671,48.583,64,40.547,64,32s-3.329-16.583-9.374-22.626
                                C48.583,3.329,40.547,0,32,0S15.417,3.329,9.374,9.373C3.329,15.417,0,23.453,0,32s3.329,16.583,9.374,22.626
                                C15.417,60.671,23.453,64,32,64z M12.202,12.202C17.49,6.913,24.521,4,32,4s14.51,2.913,19.798,8.202C57.087,17.49,60,24.521,60,32
                                s-2.913,14.51-8.202,19.798C46.51,57.087,39.479,60,32,60s-14.51-2.913-19.798-8.202C6.913,46.51,4,39.479,4,32
                                S6.913,17.49,12.202,12.202z"/>
                        </g>
                    </svg>
                    <div class="ml-3">
                        <strong> Sua operação local foi desativada!</strong> <br>
                        Você não poderá mais receber estoques nessa localidade.<br>
                    </div>
                </div>
            </div>
        <?php endif; ?>
      <div class="card">
        <div class="card-header">
          <h4 class="card-title"><?php echo $tab_list_item['operation_name']; ?></h4>
        </div>
        <div class="card-body">
          <div class="basic-list-group">
            <div class="row">
              <div class="col-lg-12">
                <div class="tab-content" id="nav-tabContent">
                  <div>
                    <div class="row">
                      <div class="col-4">
                        <?php
                         $full_address = explode("<br>", $tab_list_item['storage_address']);
                         $full_address[2] = $tab_list_item["telefone"] . "<br>" . $tab_list_item["destinatary_doc"];
                         $address_string = implode("<br>", $full_address);
                        ?>
                        <h6>Endereço de envio: </h6>
                        <p><?php echo $address_string ?></p>
                      </div>
                      <div class="col-8">
                        <h4 class="mb-4">Taxas por cidade</h4>
                        <table id="inventory-list-<?php echo $tab_list_iten['operation_id'] ?>" class="table card-table display dataTablesCard" data-page-length='100' data-order='[[0, "desc"]]'>
                          <thead>
                              <tr>
                              <th class="col-md-4 text-left" style="padding: 10px 10px;padding-left: 15px">Cidade</th>
                              <th class="col-md-3 text-left" style="padding: 10px 10px;">Taxa entr. compl.</th>
                              <th class="col-md-3 text-left" style="padding: 10px 10px;">Taxa entr. frustr.</th>
                              </tr>
                          </thead>
                          <tbody>
                            <?php
                            while($tab_list_item = $get_operation_locales->fetch()) {

                              $get_city = $conn->prepare("SELECT operations_locales.city FROM operations_locales WHERE id = :id");
                              $get_city->execute(array("id" => $tab_list_item["operation_locale"]));

                              $city_name = $get_city->fetch()["city"];
                              $tax_compl = $tab_list_item["complete_delivery_tax"];
                              $tax_frustr = $tab_list_item["frustrated_delivery_tax"];
                            ?>
                            <tr>
                              <td style="padding: 10px 10px;padding-left: 15px"><?php echo $city_name?></td>
                              <td>R$ <?php echo $tax_compl; ?></td>
                              <td>R$ <?php echo $tax_frustr; ?></td>
                            </tr>
                            <?php
                              }
                            ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
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
}
    require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>