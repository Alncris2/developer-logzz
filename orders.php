<?php

require_once ('includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
}

$page_title = "Pedidos | Logzz";
require_once('includes/layout/default/default-header.php');

  //Busca o nível do usuário com base no ID
  $user__id = $_SESSION['UserID'];
  
  if ($_SESSION['UserPlan'] == 5){
    //Busca todos dos pedidos se o usuário for ADM.
    $stmt = $conn->prepare('SELECT * FROM orders INNER JOIN sales ON orders.sale_id = sales.sale_id ORDER BY orders.sale_id ASC');
    $stmt->execute();

    $get_product_list = $conn->prepare('SELECT * FROM products');
    $get_product_list->execute();
    

  } else {
    //Busca apenas os pedidos do usário, se ele não for ADM.
    $stmt = $conn->prepare('SELECT * FROM orders INNER JOIN sales ON orders.sale_id = sales.sale_id WHERE user__id = :user__id ORDER BY orders.sale_id ASC');
    $stmt->execute(array('user__id' => $user__id));

    $get_product_list = $conn->prepare('SELECT * FROM products WHERE user__id = :user__id');
    $get_product_list->execute(array('user__id' => $user__id));

  }

  //Verfica se os filtros estão ativos
  if(isset($_GET['filtro']) && $_GET['filtro'] == 'ativo'){
    
    $filter_result = array();

    //Filtro por DATA
    $filter_data_result = array();

    if(!(empty($_GET['data-inicio']))){
      $data_inicio_ = pickerDateFormate($_GET['data-inicio']);
      $data_inicio_ = explode(" ", $data_inicio_);
          $data_inicio = $data_inicio_[0] . " 00:00:00";
      } else {
            $data_inicio = '2010-01-01';
      }

      if(!(empty($_GET['data-final']))){
      $data_final_ = pickerDateFormate($_GET['data-final']);
      $data_final_ = explode(" ", $data_final_);
          $data_final = $data_final_[0] . " 23:59:59";
      } else {
            $data_final = date('Y-m-d') . " 23:59:59";
      }

      $data_ids = $conn->prepare('SELECT order_id FROM orders WHERE order_date BETWEEN :data_inicio AND :data_final');
      $data_ids->execute(array('data_inicio' => $data_inicio, 'data_final' => $data_final));

      while($data_id = $data_ids->fetch()) {
          if (!(in_array($data_id['order_id'], $filter_data_result))) { 
            array_push($filter_data_result, $data_id['order_id']);
          }
      }

      $filter_result = $filter_data_result;

      
      //Filtro por NOME DO CLIENTE
      if(!(empty($_GET['nome-cliente']))){
        $filter_name_result = array();

        $nome_cliente = '%' . addslashes($_GET['nome-cliente']) . '%';

        $nome_cliente_ids = $conn->prepare('SELECT order_id FROM orders WHERE client_name LIKE :nome_cliente');
        $nome_cliente_ids->execute(array('nome_cliente' => $nome_cliente));
  
        while($nome_cliente_id = $nome_cliente_ids->fetch()) {
              array_push($filter_name_result, $nome_cliente_id['order_id']);
        }

        $filter_result = array_intersect($filter_data_result, $filter_name_result);
      }


       //Filtro por NOME DO PRODUTO
       if(!(empty($_GET['produto']))){
        $filter_sale_result = array();

        $produto = $_GET['produto'];

        //$produto = '%' . addslashes($_GET['nome-oferta']) . '%';

        $produto_ids = $conn->prepare('SELECT order_id FROM orders AS o INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE s.product_id LIKE :produto');
        $produto_ids->execute(array('produto' => $produto));
  
        while($produto_id = $produto_ids->fetch()) {
              array_push($filter_sale_result, $produto_id['order_id']);
        }

        $filter_result = array_intersect($filter_data_result, $filter_sale_result);
      }

      //Filtro por STATUS
      if(!(empty($_GET['status']))){
        $filter_status_result = array();

        $status = addslashes($_GET['status']);

        $status_ids = $conn->prepare('SELECT order_id FROM orders WHERE order_status = :o_status');
        $status_ids->execute(array('o_status' => $status));
  
        while($status_id = $status_ids->fetch()) {
              array_push($filter_status_result, $status_id['order_id']);
        }

        $filter_result = array_intersect($filter_data_result, $filter_status_result);
      }

      //Filtro por WHATSAPP
      if(!(empty($_GET['numero-cliente-produto']))){
        $filter_number_result = array();

        $client_number = '%' . $_GET['numero-cliente-produto'] . '%';

        $numero_ids = $conn->prepare('SELECT order_id FROM orders WHERE client_number LIKE :client_number');
        $numero_ids->execute(array('client_number' => $client_number));
  
        while($numero_id = $numero_ids->fetch()) {
              array_push($filter_number_result, $numero_id['order_id']);
        }

        $filter_result = array_intersect($filter_data_result, $filter_number_result);
      }

      //Filtro por NOME DA OFERTA
      // if(!(empty($_GET['nome-oferta']))){
      //   $filter_sale_result = array();

      //   $nome_oferta = '%' . addslashes($_GET['nome-oferta']) . '%';

      //   $nome_oferta_ids = $conn->prepare('SELECT order_id FROM orders AS o INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE sale_name LIKE :nome_oferta');
      //   $nome_oferta_ids->execute(array('nome_oferta' => $nome_oferta));
  
      //   while($nome_oferta_id = $nome_oferta_ids->fetch()) {
      //         array_push($filter_sale_result, $nome_oferta_id['order_id']);
      //   }

      //   $filter_result = array_intersect($filter_data_result, $filter_sale_result);
      // }
  
  }


  //Armazena o número de pedidos encontrados.
  $num_orders = $stmt->rowCount();
        
?>

<div class="container-fluid" style="overflow-x: scroll;">
  <div class="d-flex flex-wrap mb-2 align-items-center justify-content-between">
    <div class="mb-3 mr-3">
      <?php
        if(isset($_GET['filtro']) && $_GET['filtro'] == 'ativo'){
      ?>
      <h6 class="fs-16 text-black font-w600 mb-0">Exibindo <?php echo count($filter_result ); ?> pedidos, de acordo com seus filtros.</h6>
      <?php
        } else {
      ?>
      <h6 class="fs-16 text-black font-w600 mb-0"><?php echo $num_orders;  ?> Pedidos no total</h6>
      <?php
        }
        if ($_SESSION['UserPlan'] == 5){
      ?>
      <span class="fs-14">Usuário <b>Administrador</b></span>
      <?php
        } else {
        ?>
      <span class="fs-14">Usuário <b><?php echo $_SESSION['UserPlanString']; ?></b> | 100 entregas/mês.</span>
      <?php
        }
        ?>
    </div>
    <div class="event-tabs mb-3 mr-3">
    </div>
    <div class="d-flex mb-3">
          <div class="btn-group" role="group">
            <button type="button" class="btn btn-rounded btn-outline-success mr-2" data-toggle="dropdown" aria-expanded="true"><i class="fas fa-download scale2 mr-2"></i> Exportar</button>
            <div class="dropdown-menu">
            <a class="dropdown-item" href="#" id="export-to-csv"><i class="fas fa-file-csv scale2 mr-2"></i></i> CSV</a>
              <a class="dropdown-item" href="#" id="export-to-xlsx"><i class="fas fa-file-excel scale2 mr-2"></i> Excel</a>
              <a class="dropdown-item" href="#" id="export-to-pdf"><i class="fas fa-file-pdf scale2 mr-2"></i> PDF</a>
            </div>
          </div>
      <button type="button" id="modalFiltrosBtn" data-target="#modalFiltros" class="btn btn-rounded btn-success" class="btn btn-success text-nowrap"><i  class="fas fa-sliders-h scale2 mr-2" aria-hidden="true"></i>Filtros</button>
    </div>
  </div>

  <div class="row d-none" id="modalFiltrosDiv">
    <div class="col-xl-12">
    <div class="card">
        <div class="card-header">
          <h4 class="card-title">Filtros</h4>
        </div>
        <div class="card-body">
          <form method="GET">
            <div class="row">
              <div class="col-xl-4 mb-3">
                <div class="example">
              <p class="mb-1">por Data</p>
              <input type="hidden" class="form-control mb-2" name="filtro" value="ativo">
              <input name="data-inicio" placeholder="Do dia ..." class="datepicker-default form-control picker__input mb-2" id="datepicker" readonly="" aria-haspopup="true" aria-expanded="false" aria-readonly="false" aria-owns="datepicker_root">
              <input name="data-final" placeholder=".. ao dia" class="datepicker-default form-control picker__input" id="datepicker" readonly="" aria-haspopup="true" aria-expanded="false" aria-readonly="false" aria-owns="datepicker_root">
              <div class="picker" id="datepicker_root" aria-hidden="true"><div class="picker__holder" tabindex="-1"><div class="picker__frame"><div class="picker__wrap"><div class="picker__box"><div class="picker__header"><div class="picker__month">Setembro</div><div class="picker__year">2021</div><div class="picker__nav--prev" data-nav="-1" role="button" aria-controls="datepicker_table" title="Previous month"> </div><div class="picker__nav--next" data-nav="1" role="button" aria-controls="datepicker_table" title="Next month"> </div></div><table class="picker__table" id="datepicker_table" role="grid" aria-controls="datepicker" aria-readonly="true"><thead><tr><th class="picker__weekday" scope="col" title="Sunday">Sun</th><th class="picker__weekday" scope="col" title="Monday">Mon</th><th class="picker__weekday" scope="col" title="Tuesday">Tue</th><th class="picker__weekday" scope="col" title="Wednesday">Wed</th><th class="picker__weekday" scope="col" title="Thursday">Thu</th><th class="picker__weekday" scope="col" title="Friday">Fri</th><th class="picker__weekday" scope="col" title="Saturday">Sat</th></tr></thead><tbody><tr><td role="presentation"><div class="picker__day picker__day--outfocus" data-pick="1630206000000" role="gridcell" aria-label="29 August, 2021">29</div></td><td role="presentation"><div class="picker__day picker__day--outfocus" data-pick="1630292400000" role="gridcell" aria-label="30 August, 2021">30</div></td><td role="presentation"><div class="picker__day picker__day--outfocus" data-pick="1630378800000" role="gridcell" aria-label="31 August, 2021">31</div></td><td role="presentation"><div class="picker__day picker__day--infocus" data-pick="1630465200000" role="gridcell" aria-label="1 Setembro, 2021">1</div></td><td role="presentation"><div class="picker__day picker__day--infocus picker__day--today picker__day--highlighted" data-pick="1630551600000" role="gridcell" aria-label="2 Setembro, 2021" aria-activedescendant="true">2</div></td><td role="presentation"><div class="picker__day picker__day--infocus" data-pick="1630638000000" role="gridcell" aria-label="3 Setembro, 2021">3</div></td><td role="presentation"><div class="picker__day picker__day--infocus" data-pick="1630724400000" role="gridcell" aria-label="4 Setembro, 2021">4</div></td></tr><tr><td role="presentation"><div class="picker__day picker__day--infocus" data-pick="1630810800000" role="gridcell" aria-label="5 Setembro, 2021">5</div></td><td role="presentation"><div class="picker__day picker__day--infocus" data-pick="1630897200000" role="gridcell" aria-label="6 Setembro, 2021">6</div></td><td role="presentation"><div class="picker__day picker__day--infocus" data-pick="1630983600000" role="gridcell" aria-label="7 Setembro, 2021">7</div></td><td role="presentation"><div class="picker__day picker__day--infocus" data-pick="1631070000000" role="gridcell" aria-label="8 Setembro, 2021">8</div></td><td role="presentation"><div class="picker__day picker__day--infocus" data-pick="1631156400000" role="gridcell" aria-label="9 Setembro, 2021">9</div></td><td role="presentation"><div class="picker__day picker__day--infocus" data-pick="1631242800000" role="gridcell" aria-label="10 Setembro, 2021">10</div></td><td role="presentation"><div class="picker__day picker__day--infocus" data-pick="1631329200000" role="gridcell" aria-label="11 Setembro, 2021">11</div></td></tr><tr><td role="presentation"><div class="picker__day picker__day--infocus" data-pick="1631415600000" role="gridcell" aria-label="12 Setembro, 2021">12</div></td><td role="presentation"><div class="picker__day picker__day--infocus" data-pick="1631502000000" role="gridcell" aria-label="13 Setembro, 2021">13</div></td><td role="presentation"><div class="picker__day picker__day--infocus" data-pick="1631588400000" role="gridcell" aria-label="14 Setembro, 2021">14</div></td><td role="presentation"><div class="picker__day picker__day--infocus" data-pick="1631674800000" role="gridcell" aria-label="15 Setembro, 2021">15</div></td><td role="presentation"><div class="picker__day picker__day--infocus" data-pick="1631761200000" role="gridcell" aria-label="16 Setembro, 2021">16</div></td><td role="presentation"><div class="picker__day picker__day--infocus" data-pick="1631847600000" role="gridcell" aria-label="17 Setembro, 2021">17</div></td><td role="presentation"><div class="picker__day picker__day--infocus" data-pick="1631934000000" role="gridcell" aria-label="18 Setembro, 2021">18</div></td></tr><tr><td role="presentation"><div class="picker__day picker__day--infocus" data-pick="1632020400000" role="gridcell" aria-label="19 Setembro, 2021">19</div></td><td role="presentation"><div class="picker__day picker__day--infocus" data-pick="1632106800000" role="gridcell" aria-label="20 Setembro, 2021">20</div></td><td role="presentation"><div class="picker__day picker__day--infocus" data-pick="1632193200000" role="gridcell" aria-label="21 Setembro, 2021">21</div></td><td role="presentation"><div class="picker__day picker__day--infocus" data-pick="1632279600000" role="gridcell" aria-label="22 Setembro, 2021">22</div></td><td role="presentation"><div class="picker__day picker__day--infocus" data-pick="1632366000000" role="gridcell" aria-label="23 Setembro, 2021">23</div></td><td role="presentation"><div class="picker__day picker__day--infocus" data-pick="1632452400000" role="gridcell" aria-label="24 Setembro, 2021">24</div></td><td role="presentation"><div class="picker__day picker__day--infocus" data-pick="1632538800000" role="gridcell" aria-label="25 Setembro, 2021">25</div></td></tr><tr><td role="presentation"><div class="picker__day picker__day--infocus" data-pick="1632625200000" role="gridcell" aria-label="26 Setembro, 2021">26</div></td><td role="presentation"><div class="picker__day picker__day--infocus" data-pick="1632711600000" role="gridcell" aria-label="27 Setembro, 2021">27</div></td><td role="presentation"><div class="picker__day picker__day--infocus" data-pick="1632798000000" role="gridcell" aria-label="28 Setembro, 2021">28</div></td><td role="presentation"><div class="picker__day picker__day--infocus" data-pick="1632884400000" role="gridcell" aria-label="29 Setembro, 2021">29</div></td><td role="presentation"><div class="picker__day picker__day--infocus" data-pick="1632970800000" role="gridcell" aria-label="30 Setembro, 2021">30</div></td><td role="presentation"><div class="picker__day picker__day--outfocus" data-pick="1633057200000" role="gridcell" aria-label="1 Outubro, 2021">1</div></td><td role="presentation"><div class="picker__day picker__day--outfocus" data-pick="1633143600000" role="gridcell" aria-label="2 Outubro, 2021">2</div></td></tr><tr><td role="presentation"><div class="picker__day picker__day--outfocus" data-pick="1633230000000" role="gridcell" aria-label="3 Outubro, 2021">3</div></td><td role="presentation"><div class="picker__day picker__day--outfocus" data-pick="1633316400000" role="gridcell" aria-label="4 Outubro, 2021">4</div></td><td role="presentation"><div class="picker__day picker__day--outfocus" data-pick="1633402800000" role="gridcell" aria-label="5 Outubro, 2021">5</div></td><td role="presentation"><div class="picker__day picker__day--outfocus" data-pick="1633489200000" role="gridcell" aria-label="6 Outubro, 2021">6</div></td><td role="presentation"><div class="picker__day picker__day--outfocus" data-pick="1633575600000" role="gridcell" aria-label="7 Outubro, 2021">7</div></td><td role="presentation"><div class="picker__day picker__day--outfocus" data-pick="1633662000000" role="gridcell" aria-label="8 Outubro, 2021">8</div></td><td role="presentation"><div class="picker__day picker__day--outfocus" data-pick="1633748400000" role="gridcell" aria-label="9 Outubro, 2021">9</div></td></tr></tbody></table><div class="picker__footer"><button class="picker__button--today" type="button" data-pick="1630551600000" aria-controls="datepicker" disabled="disabled">Today</button><button class="picker__button--clear" type="button" data-clear="1" aria-controls="datepicker" disabled="disabled">Clear</button><button class="picker__button--close" type="button" data-close="true" aria-controls="datepicker" disabled="disabled">Close</button></div></div></div></div></div></div>
                </div>
                <button type="submit" class="btn btn-success text-nowrap mt-2"><i class="fas fa-sliders-h" aria-hidden="true"></i> Aplicar Filtros</button>
                <a href="<?php echo SERVER_URI; ?>/pedidos/" class="btn mt-2">Limpar Filtros</a>
              </div>
              <div class="col-xl-4 mb-3">
                <div class="example">
                  <p class="mb-1">por Cliente</p>
                  <input type="text" class="form-control mb-2" name="nome-cliente" value="<?php echo @addslashes($_GET['nome-cliente']); ?>" placeholder="Nome do Cliente">
                  <div class="form-group">
                      <label class="text-label">por Produto</label>
                      <select id="select-ship-product" class="d-block default-select" >
                      <option disabled selected>Nome do Produto</option>
                      <?php
                          while($prodcut = $get_product_list->fetch()) {
                      ?>
                      <option value="<?php echo $prodcut['product_id']; ?>"><?php if (strlen($prodcut['product_name']) > 30) { echo substr($prodcut['product_name'], 0, 30) . "..."; } else {echo $prodcut['product_name']; } ?></option>
                      <?php
                          }
                      ?>
                      </select>
                      <input type="hidden" id="text-ship-product" name="produto" value="" required>
                  </div>
                  <!-- <input type="text" class="form-control mb-2" name="nome-produto" value="" placeholder="Nome do Produto"> -->
                  <!-- <input type="text" class="form-control mb-2" name="nome-oferta" value="" placeholder="Nome do Oferta"> -->
                </div>
              </div>
              <div class="col-xl-4 mb-3">
                <div class="example">
                  <p class="mb-1">por Status</p>
                  <select class="form-control default-select" id="select-filter-status-id">
                    <option selected disabled>Todos</option>
                   <option value="0">Agendada</option>
                   <option value="2">Atrasada</option>
                   <option value="5">Cancelada</option>
                   <option value="4">Frustrada</option>
                   <option value="3">Completa</option>
                   <option value="1">Reagendada</option>
                   <option value="6">A Enviar</option>
                   <option value="7">Enviando</option>
                   <option value="8">Enviada</option>
                  </select>
                  <input type="hidden" id="text-filter-status-id" name="status" value="" required>
                  <p class="mb-1 mt-2">por WhatsApp</p>
                  <input type="text" class="form-control mb-2" name="numero-cliente-produto" value="<?php echo @addslashes($_GET['numero-cliente-produto']); ?>" placeholder="Número do Cliente">
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-xl-12" style="padding-left: 5px;">
      <div class="tab-content">
        <div id="All" class="tab-pane active fade show">
          <div class="table-responsive" style="overflow-x: visible;">
            <table id="example2" class="table card-table display dataTablesCard" data-page-length='100' data-order='[[0, "desc"]]'>
              <thead>
                <tr>
                  <th class="col-md-1">Pedido</th>
                  <th class="col-md-3">Cliente</th>
                  <th class="col-md-2">Produto</th>
                  <th class="col-md-1">Quan.</th>
                  <th class="col-md-1">Entrega</th>
                  <th class="col-md-1">Fatu. (R$)</th>
                  <th class="col-md-2">Cus. (R$)</th>
                  <th class="col-md-2">Tax. (R$)</th>
                  <th class="col-md-2">Ent. (R$)</th>
                  <th class="col-md-2">Vlr. Liq. (R$)</th>
                  <th class="col-md-1">Stat.</th>
                </tr>
              </thead>
              <tbody>
                <?php

              
                  while($row = $stmt->fetch()) {
                    
                    if(isset($_GET['filtro']) && $_GET['filtro'] == 'ativo'){
                        if(!(in_array($row['order_id'], $filter_result))){
                          continue;
                        }
                    }

                    if ($row['delivery_period'] == "manha"){
                      $period = "Manhã";
                    } else {
                      $period = "Tarde";
                    }
                    
                    if (isset($row['use_coupon']) && $row['use_coupon'] != 0 ) {
                      $final_price = $row['order_final_price'];
                    } else {
                      $final_price = $row['sale_price'];
                    }

                    $CUSTO    = $row['product_price'] * $row['sale_quantity'];
                    $LIQUIDO  = $final_price - ($row['sale_tax'] + $_SESSION['UserPlanShipTax']);
                ?>
                <tr>
                  <td data-order="<?php echo date_format(date_create($row['order_date']), 'YmdHi'); ?>"><?php echo date_format(date_create($row['order_date']), 'd/m \<\b\r\> H:i'); ?></td>
                  <td>
                      <a href="<?php echo SERVER_URI . "/meu-pedido/" . $row['order_number']; ?>" target="_blank" title="Ver detalhes do Pedido"><i class="fa fa-eye"></i></a>
                      <span class="text-nowrap"><?php echo $row['client_name']; ?><br><small><i class="fab fa-whatsapp"></i>&nbsp;<?php echo $row['client_number']; ?></small></span></td>
                  <td><?php echo $row['product_name']; ?></td>
                  <td class="text-center"><?php echo $row['sale_quantity']; ?></td>
                  <td><?php echo date_format(date_create($row['order_deadline']), 'd/m'); ?><br><?php echo $period; ?></td>
                  <td>R$ <?php echo number_format($final_price, 2, ',', '.'); ?></td>
                  <td>R$ <?php echo number_format($CUSTO, 2, ',', '.'); ?></td>
                  <td>R$ <?php echo number_format($row['sale_tax'], 2, ',', '.'); ?></td>
                  <td>R$ <?php echo number_format($_SESSION['UserPlanShipTax'], 2, ',', '.'); ?></td>
                  <td>R$ <?php echo number_format($LIQUIDO, 2, ',', '.');  ?></td>
                  <td class="here-update-badge" style="padding-left: 5px;padding-right: 5px;">
                    <?php
                        switch ($row['order_status']) {
                          case 1:
                              $btn_classes = "light badge-success";
                              $status_string = "Reag.";
                              break;
                          case 2:
                              $btn_classes = "light badge-warning";
                              $status_string = "Atra.";
                              break;
                          case 3:
                              $btn_classes = "badge-success";
                              $status_string = "Comp.";
                              break;
                          case 4:
                              $btn_classes = "light badge-dark";
                              $status_string = "Frust.";
                              break;
                          case 5:
                              $btn_classes = "light badge-danger";
                              $status_string = "Canc.";
                              break;
                          default:
                              $btn_classes = "light badge-success";
                              $status_string = "Agen.";
                              break;
                      }
                    ?>
                      <span class="badge badge-xs <?php echo $btn_classes; ?> mb-1"><?php echo $status_string; ?></span>
                      
                    <?php
                       if ($_SESSION['UserPlan'] == 5){
                    ?>
                      <div style="float: right;z-index: 999;margin-right: 20px;" class="dropdown text-sans-serif position-static"><button class="btn btn-success tp-btn-light sharp" type="button" id="order-dropdown-0" data-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="true"><span><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="18px" height="18px" viewBox="0 0 24 24" version="1.1"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect x="0" y="0" width="24" height="24"></rect><circle fill="#000000" cx="5" cy="12" r="2"></circle><circle fill="#000000" cx="12" cy="12" r="2"></circle><circle fill="#000000" cx="19" cy="12" r="2"></circle></g></svg></span></button>
                        <div class="dropdown-menu dropdown-menu-right border py-0" aria-labelledby="order-dropdown-0" x-placement="top-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(825px, 168px, 0px);">
                          <div class="py-2">
                            <a class="dropdown-item update-order-status" data-status="0" data-id="<?php echo $row['order_id']; ?>" href="#">Agendado</a>
                            <a class="dropdown-item update-order-status" data-status="2" data-id="<?php echo $row['order_id']; ?>" href="#">Atrasado</a>
                            <a class="dropdown-item update-order-status" data-status="5" data-id="<?php echo $row['order_id']; ?>" href="#">Cancelado</a>
                            <a class="dropdown-item" href="<?php echo SERVER_URI; ?>/pedido/frustrar/<?php echo $row['order_number']; ?>">Frustrado</a>
                            <a class="dropdown-item" href="<?php echo SERVER_URI; ?>/pedido/completar/<?php echo $row['order_id']; ?>">Completo</a>
                            <a class="dropdown-item" href="<?php echo SERVER_URI; ?>/pedido/reagendar/<?php echo $row['order_id']; ?>">Reagendado</a>
                            <!-- <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="#!">Delete</a> -->
                          </div>
                        </div>
                      </div>
                      <?php
                        }
                     ?>
                  </td>
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
<?php
    require_once('includes/layout/default/default-footer.php');
?>