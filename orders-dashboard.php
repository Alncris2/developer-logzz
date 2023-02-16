<?php

require_once ('includes/config.php');

session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
}

$user__id = $_SESSION['UserID'];

$page_title = "Dashboard | Logzz";
require_once('includes/layout/default/default-header.php');


//Verify Active Filters
if(isset($_GET['filtro']) && $_GET['filtro'] == "ativo"){

    if(isset($_GET['produto'])){
      $filter_by_product = addslashes($_GET['produto']);
    }

    if(isset($_GET['status'])){
      $filter_by_status = addslashes($_GET['status']);
    }

    if(isset($_GET['periodo'])){
      $filter_by_period = addslashes($_GET['periodo']);
    }
    
}

if($_SESSION['UserPlan'] == 5){
  require_once ('includes/dashboard-statistics-generator-admin.php');
} else {
  require_once ('includes/dashboard-statistics-generator-user.php');
}

    $dashboard_charts = array(
      'money-payments' => $a,
      'debit-payments' => $b,
      'credit-payments' => $c,
      'pix-payments' => $d,
      'agendada'   =>  $sales0['COUNT(order_status)'],
      'reagendada'   =>  $sales1['COUNT(order_status)'],
      'atrasada'   =>  $sales2['COUNT(order_status)'],
      'completa'   =>  $sales3['COUNT(order_status)'],
      'frustrada'    =>  $sales4['COUNT(order_status)'],
      'cancelada'    =>  $sales5['COUNT(order_status)'],
      'reembolsada'    =>  $sales6['COUNT(order_status)'],
      'day0' => $day0,
      'day1' => $day1,
      'day2' => $day2,
      'day3' => $day3,
      'day4' => $day4,
      'day5' => $day5,
      'day6' => $day6,
      'orders00' => $orders00[0],
      'orders01' => $orders01[0],
      'orders02' => $orders02[0],
      'orders03' => $orders03[0],
      'orders04' => $orders04[0],
      'orders05' => $orders05[0],
      'orders06' => $orders06[0],
      'billing00' => $billing_string00,
      'billing01' => $billing_string01,
      'billing02' => $billing_string02,
      'billing03' => $billing_string03,
      'billing04' => $billing_string04,
      'billing05' => $billing_string05,
      'billing06' => $billing_string06,
      'liquid00' => $comission_string00,
      'liquid01' => $comission_string01,
      'liquid02' => $comission_string02,
      'liquid03' => $comission_string03,
      'liquid04' => $comission_string04,
      'liquid05' => $comission_string05,
      'liquid06' => $comission_string06,

    );

    if (!(isset($_GET['periodo'])) || $_GET['periodo'] != 7){
      $dashboard_charts_7 = array (
        'liquid07' => $comission_string07,
        'liquid08' => $comission_string08,
        'liquid09' => $comission_string09,
        'liquid10' => $comission_string10,
        'liquid11' => $comission_string11,
        'liquid12' => $comission_string12,
        'liquid13' => $comission_string13,
        'liquid14' => $comission_string14,
        'billing07' => $billing_string07,
        'billing08' => $billing_string08,
        'billing09' => $billing_string09,
        'billing10' => $billing_string10,
        'billing11' => $billing_string11,
        'billing12' => $billing_string12,
        'billing13' => $billing_string13,
        'billing14' => $billing_string14,
        'orders07' => $orders07[0],
        'orders08' => $orders08[0],
        'orders09' => $orders09[0],
        'orders10' => $orders10[0],
        'orders11' => $orders11[0],
        'orders12' => $orders12[0],
        'orders13' => $orders13[0],
        'orders14' => $orders14[0],
        'day7' => $day7,
        'day8' => $day8,
        'day9' => $day9,
        'day10' => $day10,
        'day11' => $day11,
        'day12' => $day12,
        'day13' => $day13,
        'day14' => $day14
      );

      $dashboard_charts = array_merge($dashboard_charts, $dashboard_charts_7);
    }

    if (isset($_GET['periodo']) && $_GET['periodo'] == 30){
      $dashboard_charts_30 = array (
      'day15' => $day15,
      'day16' => $day16,
      'day17' => $day17,
      'day18' => $day18,
      'day19' => $day19,
      'day20' => $day20,
      'day21' => $day21,
      'day22' => $day22,
      'day23' => $day23,
      'day24' => $day24,
      'day25' => $day25,
      'day26' => $day26,
      'day27' => $day27,
      'day28' => $day28,
      'day29' => $day29,
      'orders15' => $orders15[0],
      'orders16' => $orders16[0],
      'orders17' => $orders17[0],
      'orders18' => $orders18[0],
      'orders19' => $orders19[0],
      'orders20' => $orders20[0],
      'orders21' => $orders21[0],
      'orders22' => $orders22[0],
      'orders23' => $orders23[0],
      'orders24' => $orders24[0],
      'orders25' => $orders25[0],
      'orders26' => $orders26[0],
      'orders27' => $orders27[0],
      'orders28' => $orders28[0],
      'orders29' => $orders29[0],
      'billing15' => $billing_string15,
      'billing16' => $billing_string16,
      'billing17' => $billing_string17,
      'billing18' => $billing_string18,
      'billing19' => $billing_string19,
      'billing20' => $billing_string20,
      'billing21' => $billing_string21,
      'billing22' => $billing_string22,
      'billing23' => $billing_string23,
      'billing24' => $billing_string24,
      'billing25' => $billing_string25,
      'billing26' => $billing_string26,
      'billing27' => $billing_string27,
      'billing28' => $billing_string28,
      'billing29' => $billing_string29,
      'liquid15' => $comission_string15,
      'liquid16' => $comission_string16,
      'liquid17' => $comission_string17,
      'liquid18' => $comission_string18,
      'liquid19' => $comission_string19,
      'liquid20' => $comission_string20,
      'liquid21' => $comission_string21,
      'liquid22' => $comission_string22,
      'liquid23' => $comission_string23,
      'liquid24' => $comission_string24,
      'liquid25' => $comission_string25,
      'liquid26' => $comission_string26,
      'liquid27' => $comission_string27,
      'liquid28' => $comission_string28,
      'liquid29' => $comission_string29
      );

      $dashboard_charts = array_merge($dashboard_charts, $dashboard_charts_30);
      
    }

?>
<div class="container-fluid">
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
                  <input type="hidden" class="form-control mb-2" name="filtro" value="ativo">
                  <div class="example">
                  <p class="mb-1">por Período</p>
                  <select class="form-control default-select" id="select-filter-period-id">
                    <option selected disabled>Todo o Período</option>
                    <option value="7">Últimos 7 dias</option>
                   <option value="15">Últimos 15 dias</option>
                   <option value="30">Últimas 30 dias</option>
                  </select>
                  <input type="hidden" id="text-filter-period-id" name="periodo" value="" required>
                </div>
                </div>
                <button type="submit" class="btn btn-success text-nowrap mt-2"><i class="fas fa-sliders-h" aria-hidden="true"></i> Aplicar Filtros</button>
                <a href="<?php echo SERVER_URI; ?>/pedidos/dashboard/" class="btn mt-2">Limpar Filtros</a>
              </div>
              <div class="col-xl-4 mb-3">
                <div class="example">
                  <div class="form-group">
                      <label class="text-label">por Produto</label>
                      <select id="select-ship-product" class="d-block w-100 default-select" >
                      <option disabled selected>Todos os Produtos</option>
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
                </div>
              </div>
              <div class="col-xl-4 mb-3">
                <div class="example">
                  <p class="mb-1">por Status</p>
                  <select class="form-control default-select" id="select-filter-status-id">
                    <option selected disabled>Todos os status</option>
                   <option value="0">Agendada</option>
                   <option value="2">Atrasada</option>
                   <option value="5">Cancelada</option>
                   <option value="4">Frustrada</option>
                   <option value="3">Completa</option>
                   <option value="1">Reagendada</option>
                  </select>
                  <input type="hidden" id="text-filter-status-id" name="status" value="" required>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>  

  <div class="row">
    <div class="col-xl-12 col-xxl-12">
      <div class="row dashboard-numbers">
        <div class="col-xl-4 col-xxl-4 col-lg-4 col-sm-4 dashboard-number-col">
          <div class="card">
            <div class="card-body card-dashboard">
              <div class="d-flex justify-content-between">
                <div>
                  <i class="fas fa-money-bill-wave"></i>
                  <p class="fs-12 mb-1 d-inline">Faturamento</p>
                  <span class="fs-20 font-w700 d-block"><small>R$ </small> <?php echo number_format($faturamento[0], 2, ',', '.'); ?>
                  </span>
                </div>
                <div>
                  <i class="fas fa-donate"></i>
                  <p class="fs-12 mb-1 d-inline">Comissões</p>
                  <span class="fs-20 font-w700 d-block"><small>R$ </small><?php echo number_format($comissao[0], 2, ',', '.'); ?> <small>(<?php echo number_format($comissao_por100, 0, '', ''); ?>%)</small>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="col-xl-2 col-xxl-2 col-lg-2 col-sm-2 dashboard-number-col">
          <div class="card">
            <div class="card-body card-dashboard">
              <div class="d-flex align-items-end">
                <div>
                  <p class="fs-12 mb-1 d-block">Vendas</p>
                  <i class="fas fa-shopping-basket" style="font-size: 1.2em;"></i><span class="fs-20 font-w700"> <?php echo $vendas[0]; ?>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-2 col-xxl-2 col-lg-2 col-sm-2 dashboard-number-col">
          <div class="card">
            <div class="card-body card-dashboard">
              <div class="d-flex align-items-end">
                <div>
                  <p class="fs-12 mb-1 d-block">Produtos</p>
                  <i class="fas fa-tags" style="font-size: 1.2em;"></i><span class="fs-20 font-w700 d-inline"> <?php echo $produtos; ?>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-2 col-xxl-2 col-lg-2 col-sm-2 dashboard-number-col">
          <div class="card">
            <div class="card-body card-dashboard">
              <div class="d-flex align-items-end">
                <div>
                  <p class="fs-12 mb-1 d-block">Clientes Novos</p>
                  <i class="fas fa-users" style="font-size: 1.2em;"></i><span class="fs-20 font-w700 d-inline"> <?php echo $clientes; ?>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="col-xl-2 col-xxl-2 col-lg-2 col-sm-2 dashboard-number-col">
          <div class="card">
            <div class="card-body card-dashboard">
              <div class="d-flex align-items-end">
                <div>
                  <p class="fs-12 mb-1 d-block">Reembolsos</p>
                  <i class="fas fa-minus-circle" style="font-size: 1.2em;"></i><span class="fs-20 font-w700 d-inline"> <?php echo $reembolsos; ?>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>


        <div class="col-xl-4 col-xxl-12 col-lg-4">
          <div class="card">
            <div class="card-header align-items-start pb-0 border-0">	
              <div>
                <?php  //print_r($dashboard_charts); ?>
                <h4 class="fs-18 mb-0 text-black font-w600">Volume de Vendas
                           <?php
                              if (isset($_GET['periodo']) && $_GET['periodo'] == 30){ echo "(Últimos 30 dias)"; } else if (isset($_GET['periodo']) && $_GET['periodo'] == 7) { echo "(Últimos 7 dias)";}
                           ?>

                </h4>
                <span class="fs-12"></span>
              </div>
              <div class="d-flex mb-3">
                <button type="button" id="modalFiltrosBtn" data-target="#modalFiltros" class="btn btn-rounded btn-success" class="btn btn-success text-nowrap"><i  class="fas fa-sliders-h scale5 mr-3" aria-hidden="true"></i>Filtros</button>
              </div>
            </div>
            <div class="card-body pt-0">
              <canvas id="salesVolumeChart" height="120"></canvas>
            </div>
          </div>
        </div>

        <div class="col-xl-4 col-xxl-6 col-lg-4">
          <div class="card">
            <div class="card-header align-items-start pb-0 border-0">	
              <div>
                <h4 class="fs-18 mb-0 text-black font-w600">Vendas por Status</h4>
                <span class="fs-12"></span>
              </div>
            </div>
            <div class="card-body pt-0">
              <canvas id="salesStatusChart" height="250"></canvas>
            </div>
          </div>
        </div>

      <div class="col-xl-4 col-xxl-6 col-lg-4">
          <div class="card">
            <div class="card-header align-items-start pb-0 border-0">	
              <div>
                <h4 class="fs-18 mb-0 text-black font-w600">Vendas por Forma de Pagamento</h4>
                <span class="fs-12"></span>
              </div>
            </div>
            <div class="card-body pt-0">
              <canvas id="salesPayMethodChart" height="250"></canvas>
            </div>
          </div>
        </div>
    </div>
  </div>
</div>

<?php
    require_once('includes/layout/default/default-footer.php');
?>