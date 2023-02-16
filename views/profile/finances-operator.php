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

// function str_contains(string $haystack, string $needle): bool {
//     return '' === $needle || false !== strpos($haystack, $needle);
// }

$user__id = $_SESSION['UserID'];

    $get_operator = $conn->prepare("SELECT * FROM logistic_operator WHERE user_id = :user_id");
    $get_operator->execute(array("user_id" => $_SESSION['UserID']));

    $operator_id = $get_operator->fetch()['operator_id'];

    # Valor para repasse
    $orders_operator = $conn->prepare('SELECT DISTINCT o.order_id, o.client_address, o.order_status, o.order_payment_method, o.order_final_price FROM local_operations_orders loo INNER JOIN orders o ON loo.order_id = o.order_id WHERE loo.responsible_id = :operator_id AND (o.order_status = 3 OR o.order_status = 4 OR o.order_status = 9)');
    $orders_operator->execute(array("operator_id" => $operator_id));

    $operator_delivery_taxes = $conn->prepare("SELECT * FROM operations_delivery_taxes od INNER JOIN operations_locales ol ON od.operation_locale = ol.id WHERE od.operator_id = :operator_id");
    $operator_delivery_taxes->execute(array('operator_id' => $operator_id));

    $total_saque = 0.0;
    $total_transfer = 0.0;
    $taxes = $operator_delivery_taxes->fetchAll();

    while($order = $orders_operator->fetch()) {
        foreach($taxes as $tax) {

            $address = $order["client_address"];
            $address = explode("<br>", $address);

            $street_number = explode(", ", $address[0]);
            $street = $street_number[0];
            $number = explode("nº ", $street_number[1])[1];

            $bairro = $address[1];
            $complement = $address[2];

            $city_state = explode(", ", $address[3]);
            $city = $city_state[0];

            if($city == $tax['city']) {
                if($order["order_status"] == 3 || $order["order_status"] == 9) {
                    $total_saque = $total_saque + floatval($tax['complete_delivery_tax']);
                } else {
                    $total_saque = $total_saque + floatval($tax['frustrated_delivery_tax']);
                }
            }
        }
        if($order["order_status"] == 3 || $order["order_status"] == 9) {
            $total_saque = $total_saque + floatval($tax['complete_delivery_tax']);
        } else {
            $total_saque = $total_saque + floatval($tax['frustrated_delivery_tax']);
        }
        if($order["order_payment_method"] == "money") {
            $total_transfer = $total_transfer + floatval($order["order_final_price"]);
        }
    }

    $already_has_balance = $conn->prepare('SELECT * FROM transactions_meta WHERE user__id = :user__id AND meta_key = "transfer_balance"');
    $already_has_balance->execute(array("user__id" => $_SESSION["UserID"]));

    $current_transfer_balance = $conn->prepare('SELECT SUM(meta_value) FROM transactions_meta WHERE user__id = :user__id AND (meta_key = "in_review_transfer" OR meta_key = "cashed_out_transfer")');
    $current_transfer_balance->execute(array("user__id" => $_SESSION["UserID"]));
    $transfer_balance = $current_transfer_balance->fetch();

    $total_transfer = $total_transfer - $transfer_balance[0];

    if($already_has_balance->fetch() ) {
        $update_transfer_balance = $conn->prepare('UPDATE transactions_meta SET meta_value = :meta_value WHERE user__id = :user__id AND meta_key = "transfer_balance"');
        $update_transfer_balance->execute(array("user__id" => $_SESSION["UserID"], "meta_value" => $total_transfer ));
    } else {
        $update_transfer_balance = $conn->prepare('INSERT INTO transactions_meta (meta_id, user__id, meta_key, meta_value) VALUES (0, :user__id, :meta_key, :meta_value)');
        $update_transfer_balance->execute(array("user__id" => $_SESSION["UserID"], "meta_key" => "transfer_balance", "meta_value" => $total_transfer));
    }

    $already_has_balance = $conn->prepare('SELECT * FROM transactions_meta WHERE user__id = :user__id AND meta_key = "commission_balance"');
    $already_has_balance->execute(array("user__id" => $_SESSION["UserID"]));

    $current_saque_balance = $conn->prepare('SELECT SUM(meta_value) FROM transactions_meta WHERE user__id = :user__id AND (meta_key = "in_review_balance" OR meta_key = "cashed_out_balance")');
    $current_saque_balance->execute(array("user__id" => $_SESSION["UserID"]));
    $saque_balance = $current_saque_balance->fetch();

    $total_saque = $total_saque - $saque_balance[0];

    if($already_has_balance->fetch()) {
        $update_balance = $conn->prepare('UPDATE transactions_meta SET meta_value = :meta_value WHERE user__id = :user__id AND meta_key = "commission_balance"');
        $update_balance->execute(array("user__id" => $_SESSION["UserID"], "meta_value" => $total_saque));
    } else {
        $update_balance = $conn->prepare('INSERT INTO transactions_meta (meta_id, user__id, meta_key, meta_value) VALUES (0, :user__id, :meta_key, :meta_value)');
        $update_balance->execute(array("user__id" => $_SESSION["UserID"], "meta_key" => "commission_balance", "meta_value" => $total_saque));
    }

    # Saque em Análise
    $analise = $conn->prepare('SELECT SUM(meta_value) FROM transactions_meta WHERE meta_key = "in_review_balance" AND user__id = :user__id');
    $analise->execute(array('user__id' => $user__id));

    $analise = $analise->fetch();
    if (@$analise['0'] == null){
        
      $analise = 0;
      
    } else {

      $analise = $analise['0'];

    }

    # Total já sacado
    $sacado = $conn->prepare('SELECT SUM(meta_value) AS S FROM transactions_meta WHERE meta_key = "cashed_out_balance" AND user__id = :user__id');
    $sacado->execute(array('user__id' => $user__id));

    $sacado = $sacado->fetch();
    if (@$sacado['S'] == null){
        
      $sacado = 0;
      
    } else {

      $sacado = $sacado['S'];

    }

    #total repassado em analise
    $repasse_analise = $conn->prepare('SELECT SUM(billing_value) FROM billings WHERE user__id = :user__id AND billing_request IS NOT NULL AND billing_released IS NULL AND billing_type = "REPASSE"');
    $repasse_analise->execute(array("user__id" => $user__id));

    $repasse_analise = $repasse_analise->fetch();
    if (@$repasse_analise[0] == null){
        
      $repasse_analise = 0;
      
    } else {

      $repasse_analise = $repasse_analise[0];

    }

    #total repasse
    $repasse = $conn->prepare('SELECT SUM(billing_value) FROM billings WHERE user__id = :user__id AND billing_request IS NOT NULL AND billing_released IS NOT NULL AND billing_type = "REPASSE" AND billing_status != 1');
    $repasse->execute(array("user__id" => $user__id));

    $repasse = $repasse->fetch();
    if (@$repasse[0] == null){
        
      $repasse = 0;
      
    } else {

      $repasse = $repasse[0];

    }




$stats = array(

  'disponivel_saque' => "R$ " .  number_format($total_saque, 2, ',', '.'),
  'a_liberar' => "R$ " .  number_format($total_transfer, 2, ',', '.'),
  'total_sacado' => "R$ " .  number_format($sacado, 2, ',', '.'),
  'em_analise' => "R$ " .  number_format($analise, 2, ',', '.'),
  'rep_em_analise' => "R$ " .  number_format($repasse_analise, 2, ',', '.'),
  'repasse' => "R$ " .  number_format($repasse, 2, ',', '.'),

);

$page_title = "Financeiro | Logzz";
$profile_page = true;
$select_datatable_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

?>

    <div class="container-fluid">
        <!-- row -->
        <div class="row" style="justify-content: space-between;">

            <div class="col-xl-6 col-lg-6 col-sm-4">
                <div class="widget-stat card" style="background-color: #cffeea;">
                    <div class="card-body p-4">
                    <div class="media">
                        <span class="mr-3" style="background-color: #2bc155;color: #cffeea;">
                        <i class="fas fa-dollar-sign"></i>
                        </span>
                        <div class="media-body text-right">
                        <label class="h1 mt-2" style="color: #00895f; "><?php echo $stats['disponivel_saque']; ?></label>
                        <p class="mb-1 font-weight-thin">Saldo Disponível</p>
                        <?php if (!(isset($saque_btn_disable))) { ?>
                            <button type="button" class="btn btn-success btn-xs float-right btn-billing-request" data-toggle="modal" data-target="#SolicitarSaqueModal">
                                <i class="fas fa-hand-holding-usd"></i>&nbsp;&nbsp;Sacar
                            </button>
                        <?php } ?>
                        </div>
                    </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6 col-lg-6 col-sm-4">
                <div class="widget-stat card" style="background-color: #cffeea;">
                    <div class="card-body p-4">
                    <div class="media">
                        <span class="mr-3" style="background-color: #2bc155;color: #cffeea;">
                        <i class="fas fa-hourglass-half"></i>
                        </span>
                        <div class="media-body text-right">
                        <label class="h1 mt-2" style="color: #00895f; "><?php echo $stats['a_liberar']; ?></label>
                        <p class="mb-1 font-weight-thin">Saldo a repassar</p>
                        <?php if (!(isset($antecipacao_btn_disable))) { ?>
                        <button type="button" class="btn btn-success btn-xs float-right" data-toggle="modal" data-target="#SolicitarAntecipacaoModal">
                            <i class="fas fa-hand-holding-usd"></i>&nbsp;&nbsp;Repassar
                        </button>
                        <?php } ?>
                        </div>
                    </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="row" style="justify-content: space-between;">

            <div class="col-xl-3 col-lg-3 col-sm-3">
                <div class="widget-stat card" style="background-color: #fff6db;">
                    <div class="card-body p-4">
                    <div class="media">
                        <span class="mr-1" style="background-color: #856404;color: #fff6db;height: 50px;width: 50px;min-width: 20px;font-size: 1.2em;">
                        <i class="far fa-clock"></i>
                        </span>
                        <div class="media-body text-right">
                        <label class="h1 mt-2" style="color: #856404; "><?php echo $stats['em_analise']; ?></label>
                        <p class="mb-1 font-weight-thin">Saque em Análise</p>
                        </div>
                    </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-3 col-sm-3">
                <div class="widget-stat card" style="background-color: #e1e1e1;">
                    <div class="card-body p-4">
                    <div class="media">
                        <span class="mr-1" style="background-color: #666c70;color: #e1e1e1;height: 50px;width: 50px;min-width: 20px;font-size: 1.2em;">
                        <i class="fas fa-history"></i>
                        </span>
                        <div class="media-body text-right">
                        <label class="h1 mt-2" style="color: #666c70; "><?php echo $stats['total_sacado']; ?></label>
                        <p class="mb-1 font-weight-thin">Total Já Sacado</p>
                        </div>
                    </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-3 col-sm-3">
                <div class="widget-stat card" style="background-color: #fff6db;">
                    <div class="card-body p-4">
                    <div class="media">
                        <span class="mr-1" style="background-color: #856404;color: #fff6db;height: 50px;width: 50px;min-width: 20px;font-size: 1.2em;">
                        <i class="far fa-clock"></i>
                        </span>
                        <div class="media-body text-right">
                        <label class="h1 mt-2" style="color: #856404; "><?php echo $stats['rep_em_analise']; ?></label>
                        <p class="mb-1 font-weight-thin">Repasse em Análise</p>
                        </div>
                    </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-3 col-sm-3">
                <div class="widget-stat card" style="background-color: #e1e1e1;">
                    <div class="card-body p-4">
                    <div class="media">
                        <span class="mr-1" style="background-color: #666c70; color: #e1e1e1;height: 50px;width: 50px;min-width: 20px;font-size: 1.2em;">
                        <i class="fas fa-history"></i>
                        </span>
                        <div class="media-body text-right">
                        <label class="h1 mt-2" style="color: #666c70; "><?php echo $stats['repasse']; ?></label>
                        <p class="mb-1 font-weight-thin">Total Já repassado</p>
                        <!-- Botão Modal/Total ja repassado -->
                        <button type="submit" class="btn btn-success btn-xs float-right ml-2" data-toggle="modal" data-target="#totRepassado">
                            <i class="fas fa-search ml-2"></i>&nbsp;&nbsp;Detalhes
                        </button>
                        <!-- POPUP -->
                        <div class="modal fade" id="totRepassado" tabindex="-1" role="dialog" aria-labelledby="totRepassado" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header center text-center d-block">
                                        <h5 class="modal-title" id="">Detalhes de Repasses Realizados</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                            <span aria-hidden="true"></span>   
                                        </button>
                                    </div>

                                    
                                                
                                    <div class="card-body">
                                        <?php
                                            $info_repasse = $conn->prepare('SELECT billing_request, billing_value, billing_released FROM billings WHERE user__id = :user__id AND billing_request IS NOT NULL AND billing_type = "REPASSE" ORDER BY billing_request DESC LIMIT 1');
                                            $info_repasse->execute(array('user__id' => $user__id));
                                            $info_repasse = $info_repasse->fetch();
                                        ?>
                                        <h4 class="fs-16 d-block font-w600" style="text-align: left;">Valor a ser Repassado Hoje <small class="fs-12 text-muted"></h4>
                                        
                                        <div class="row">
                                            <div class="col-lg-8 col-md-12 mb-3">
												<p class="fs-28 text-black font-w600 mb-1" style="text-align: left;"><?php echo $stats['a_liberar']; ?></p>
											</div>
										</div>

										<div class="row">
											<div class="col-lg-3 col-xs-6 col-xxl-6 mb-3">
												<div class="media bg-light p-3 rounded align-items-center">	
													<svg class="mr-2" width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
														<path d="M6.07438 25H7.95454V22.6464C11.8595 22.302 14 19.6039 14 16.8197C14 12.7727 10.8471 11.9977 7.95454 11.3088V5.10907C9.34297 5.4535 10.1529 6.5155 10.2686 7.66361H13.7975C13.5372 4.42021 11.281 2.61194 7.95454 2.32492V0H6.07438V2.35362C2.4876 2.66935 0 4.87945 0 8.09415C0 12.1412 3.18182 12.9449 6.07438 13.6625V19.977C4.45455 19.69 3.64463 18.628 3.52893 17.1929H0C0 20.4363 2.54545 22.3594 6.07438 22.6751V25ZM10.6736 16.992C10.6736 18.4845 9.69008 19.69 7.95454 19.977V14.1504C9.51653 14.6383 10.6736 15.3559 10.6736 16.992ZM3.35537 7.92193C3.35537 6.17107 4.48347 5.22388 6.07438 5.02296V10.8209C4.5124 10.333 3.35537 9.58668 3.35537 7.92193Z" fill="#FE634E"></path>
													</svg>
													<div class="media-body">
														<span class="fs-12 d-block mb-1">Último Repasse</span>
														<span class="fs-16 text-black"><?php if($info_repasse == true) {
                                                                                            echo "R$ ". number_format($info_repasse['billing_value'], 2, ",", ".");
                                                                                        } else {
                                                                                            echo "0";
                                                                                        } ?>
                                                        </span>
													</div>
												</div>
											</div>
											<div class="col-lg-4 col-md-6 col-xxl-6 mb-3">
												<div class="media bg-light p-3 rounded align-items-center">	
													<svg class="mr-4" width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
														<g clip-path="url(#clip0)">
														<path d="M21 3H20C20 2.20435 19.6839 1.44129 19.1213 0.87868C18.5587 0.31607 17.7956 0 17 0C16.2044 0 15.4413 0.31607 14.8787 0.87868C14.3161 1.44129 14 2.20435 14 3H10C10 2.20435 9.68393 1.44129 9.12132 0.87868C8.55871 0.316071 7.79565 4.47035e-08 7 4.47035e-08C6.20435 4.47035e-08 5.44129 0.316071 4.87868 0.87868C4.31607 1.44129 4 2.20435 4 3H3C2.20435 3 1.44129 3.31607 0.87868 3.87868C0.31607 4.44129 0 5.20435 0 6L0 21C0 21.7956 0.31607 22.5587 0.87868 23.1213C1.44129 23.6839 2.20435 24 3 24H21C21.7956 24 22.5587 23.6839 23.1213 23.1213C23.6839 22.5587 24 21.7956 24 21V6C24 5.20435 23.6839 4.44129 23.1213 3.87868C22.5587 3.31607 21.7956 3 21 3ZM3 5H4C4 5.79565 4.31607 6.55871 4.87868 7.12132C5.44129 7.68393 6.20435 8 7 8C7.26522 8 7.51957 7.89464 7.70711 7.70711C7.89464 7.51957 8 7.26522 8 7C8 6.73478 7.89464 6.48043 7.70711 6.29289C7.51957 6.10536 7.26522 6 7 6C6.73478 6 6.48043 5.89464 6.29289 5.70711C6.10536 5.51957 6 5.26522 6 5V3C6 2.73478 6.10536 2.48043 6.29289 2.29289C6.48043 2.10536 6.73478 2 7 2C7.26522 2 7.51957 2.10536 7.70711 2.29289C7.89464 2.48043 8 2.73478 8 3V4C8 4.26522 8.10536 4.51957 8.29289 4.70711C8.48043 4.89464 8.73478 5 9 5H14C14 5.79565 14.3161 6.55871 14.8787 7.12132C15.4413 7.68393 16.2044 8 17 8C17.2652 8 17.5196 7.89464 17.7071 7.70711C17.8946 7.51957 18 7.26522 18 7C18 6.73478 17.8946 6.48043 17.7071 6.29289C17.5196 6.10536 17.2652 6 17 6C16.7348 6 16.4804 5.89464 16.2929 5.70711C16.1054 5.51957 16 5.26522 16 5V3C16 2.73478 16.1054 2.48043 16.2929 2.29289C16.4804 2.10536 16.7348 2 17 2C17.2652 2 17.5196 2.10536 17.7071 2.29289C17.8946 2.48043 18 2.73478 18 3V4C18 4.26522 18.1054 4.51957 18.2929 4.70711C18.4804 4.89464 18.7348 5 19 5H21C21.2652 5 21.5196 5.10536 21.7071 5.29289C21.8946 5.48043 22 5.73478 22 6V10H2V6C2 5.73478 2.10536 5.48043 2.29289 5.29289C2.48043 5.10536 2.73478 5 3 5ZM21 22H3C2.73478 22 2.48043 21.8946 2.29289 21.7071C2.10536 21.5196 2 21.2652 2 21V12H22V21C22 21.2652 21.8946 21.5196 21.7071 21.7071C21.5196 21.8946 21.2652 22 21 22Z" fill="#FE634E"></path>
														<path d="M12 16C12.5523 16 13 15.5523 13 15C13 14.4477 12.5523 14 12 14C11.4477 14 11 14.4477 11 15C11 15.5523 11.4477 16 12 16Z" fill="#FE634E"></path>
														<path d="M18 16C18.5523 16 19 15.5523 19 15C19 14.4477 18.5523 14 18 14C17.4477 14 17 14.4477 17 15C17 15.5523 17.4477 16 18 16Z" fill="#FE634E"></path>
														<path d="M6 16C6.55228 16 7 15.5523 7 15C7 14.4477 6.55228 14 6 14C5.44771 14 5 14.4477 5 15C5 15.5523 5.44771 16 6 16Z" fill="#FE634E"></path>
														<path d="M12 20C12.5523 20 13 19.5523 13 19C13 18.4477 12.5523 18 12 18C11.4477 18 11 18.4477 11 19C11 19.5523 11.4477 20 12 20Z" fill="#FE634E"></path>
														<path d="M18 20C18.5523 20 19 19.5523 19 19C19 18.4477 18.5523 18 18 18C17.4477 18 17 18.4477 17 19C17 19.5523 17.4477 20 18 20Z" fill="#FE634E"></path>
														<path d="M6 20C6.55228 20 7 19.5523 7 19C7 18.4477 6.55228 18 6 18C5.44771 18 5 18.4477 5 19C5 19.5523 5.44771 20 6 20Z" fill="#FE634E"></path>
														</g>
														<defs>
														<clipPath id="clip0">
														<rect width="24" height="24" fill="white"></rect>
														</clipPath>
														</defs>
													</svg>
													<div class="media-body">
														<span class="fs-12 d-block mb-1">Data</span>
														<span class="fs-16 text-black"><?php if($info_repasse == true) {
                                                                                            echo date_format(date_create($info_repasse['billing_request']), 'd/m H:i');
                                                                                            } else {
                                                                                                echo "--/-- --:--";
                                                                                            } ?>
                                                </span>
													</div>
												</div>
											</div>
										</div>
									</div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-rounded btn-success mt-1" data-dismiss="modal">Fechar</button>
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

        <div class="row">
            <div class="col-xl-12 col-xxl-12">
                <div class="card">

                    <div class="card-header">
                        <small class="card-title">Histórico de Movimentação <i class="fa fa-info-circle" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="Histórico de Cobranças e Saques realizados"></i></small>
                    </div>
                
                    <div class="card-body">
                    <?php 
                        $get_transactions_list = $conn->prepare('SELECT * FROM billings WHERE user__id = :user__id ORDER BY billing_request DESC');
                        $get_transactions_list->execute(array('user__id' => $user__id));
                    
                        if ($get_transactions_list->rowCount() > 0){

                    ?>
                        <table class="table bank-accounts">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">Data</th>
                                    <th style="text-align: center;">Descrição</th>
                                    <th style="text-align: center;">Saldo</th>
                                    <th style="text-align: center;">Status</th>
                                    <th style="text-align: center;">Comprovante</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                while ($transactions_list = $get_transactions_list->fetch()){

                                    if ($transactions_list['billing_released'] == NULL){
                                        $status = "Pendente";
                                    } else {
                                        $status = "Bem Sucedido";
                                    }

                                ?>
                                <tr>
                                    <td style="text-align: center;"><?php echo date_format(date_create($transactions_list['billing_request']), 'd/m H:i'); ?></td>
                                    <td style="text-align: center;"><?php echo $transactions_list['billing_type']; ?></td>
                                    <td style="text-align: center; color: #2bc155; font-weight: bold;"><?php echo "+ R$ " . number_format($transactions_list['billing_value'], 2, ",", "."); ?></td>
                                    <td><?php if ($transactions_list['billing_released'] == null) { 
                                        ?>
                                            <span class="badge badge-sm d-block m-auto light badge-warning"><i class="far fa-clock"></i> PENDENTE</span>
                                        <?php
                                        } else if ($transactions_list['billing_status'] == 1) {
                                        ?>
                                            <span class="badge badge-sm d-block m-auto light badge-danger"><i class="far fa-check-circle"></i> RECUSADO</span>
                                        <?php
                                        } else {
                                        ?>
                                            <span class="badge badge-sm d-block m-auto light badge-primary"><i class="far fa-check-circle"></i> BEM SUCEDIDO</span>
                                        <?php
                                        }
                                        ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <a title="Ver comprovante de trasnferência" <?php
                                        if($transactions_list['billing_proof'] == NULL) {
                                            echo "href='#'";
                                        } else {
                                            if($transactions_list['billing_type'] == "SAQUE") {
                                                echo "href='" . SERVER_URI . "/uploads/saques/comprovantes/" . $transactions_list['billing_proof'] . "' target='_blank'"; 
                                            } else {
                                                echo "href='" . SERVER_URI . "/uploads/repasses/comprovantes/" . $transactions_list['billing_proof'] . "' target='_blank'"; 
                                            }
                                        }?>>
                                        <i class="fa fa-eye<?php if ($transactions_list['billing_proof'] == NULL) { echo '-slash';}?>"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php

                                }
                                ?>
                            </tbody>
                        </table>
                        <?php
                        } else {
                        ?>

                            <div class="alert alert-secondary alert-light alert-dismissible fade show">
                                <small>Nenhuma movimentação ainda.</small>
                            </div>

                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="SolicitarSaqueModal" style="display: none;" aria-hidden="true" >
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header center text-center d-block">
                    <h5 class="modal-title" id="">Solicitar Saque</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true"></span>   
                    </button>
                </div>
                <div class="card-body">
                    <form id="SaveCardForm" method="POST" action="">

                        <div class="row">
                            <div class="col-md-8 mb-3 m-auto">
                                <p class="mb-3 h4 font-weight-thin d-block text-center">Valor do Saque</p>
                                <input type="text" class="form-control text-center mb-2 money" name="valor-saque" id="valor-saque" placeholder="R$ XXXX,XX" required="" style=" font-size: 1.2em; letter-spacing: 1px; ">
                                <small class="text-muted"><p class="mb-4 font-weight-thin d-block text-center">Disponível p/ Saque: <strong><?php echo $stats['disponivel_saque']; ?></strong></p></small>
                                
                            </div>
                        </div>

                        <?php 
                            $get_bank_acc_list = $conn->prepare('SELECT account_id, account_user_id, account_bank, account_agency, account_number, account_type, account_pix_type, account_pix_key, account_status FROM bank_account_list WHERE account_user_id = :user__id AND account_status = 2');
                            $get_bank_acc_list->execute(array('user__id' => $user__id));
                        
                            if ($get_bank_acc_list->rowCount() > 0){
                                
                        ?>
                            <table class="table bank-accounts">
                                <thead>
                                    <tr>
                                        <th class="col-md-1" style="text-align: center; padding: 8px 12px"></th>
                                        <th class="col-md-2" style="text-align: center; padding: 8px 12px">Banco</th>
                                        <th class="col-md-2" style="text-align: center; padding: 8px 12px">Ag.</th>
                                        <th class="col-md-3" style="text-align: center; padding: 8px 12px">Conta</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    while ($bank_acc_list = $get_bank_acc_list->fetch()){

                                        $bank_name = bankName($bank_acc_list['account_bank']);
                                        $agency = $bank_acc_list['account_agency'];
                                        $number = $bank_acc_list['account_number'];                                        if ($bank_acc_list['account_type'] == 1){
                                            $type = "Corrente";
                                        } else {
                                            $type = "Poupança";
                                        }
                                    ?>
                                    <tr>
                                        <td style="text-align: center; padding: 0px">
                                            <div class="custom-control custom-checkbox checkbox-success check-lg ml-3">
                                                <input type="checkbox" name="bank-account-to-transfer" class="custom-control-input bank-checkbox-s" id="customCheckBoxS<?php echo $bank_acc_list['account_id']; ?>" value="<?php echo $bank_acc_list['account_id']; ?>" required="">
                                                <label class="custom-control-label text-center m-auto" for="customCheckBoxS<?php echo $bank_acc_list['account_id']; ?>" style="border-color: #2fde91"></label>
                                            </div>
                                        </td>
                                        <td style="text-align: center; padding: 8px 6px"><?php echo $bank_name; ?></td>
                                        <td style="text-align: center; padding: 8px 6px"><?php echo $agency; ?></td>
                                        <td style="text-align: center; padding: 8px 6px"><?php echo $number; ?></td>
                                    </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                            <?php
                            } else {
                                $disable_request_btn = true;
                            ?>

                               <div class="alert alert-danger fade show text-center" style=" padding: 0.6em; line-height: 1em; border-radius: 0.785em; ">
                                    <small>Você precisa ter pelo menos 1 conta cadastrada e aprovada antes de efeturar um saque. <a href="/perfil/contas-bancarias/" style="font-weight: bold; color: #a11313;">Ver Contas Bancárias</a></small>
                                </div>

                            <?php
                            }
                            ?>
                        <input type="hidden" name="conta-selecionada" value="0" id="text-bank-checkbox-s">
                            <?php
                                if (@$disable_request_btn != true){
                            ?>
                        <a class="btn btn-success btn-lg btn-block billing-request mt-4" data-action="billing-request"><i class="fas fa-hand-holding-usd"></i> Confirmar Saque</a>
                            <?php
                            } else {
                            ?>
                        <span class="btn btn-success btn-lg btn-block mt-4 disabled" ><i class="fas fa-hand-holding-usd"></i> Confirmar Saque</span>
                            <?php
                            }
                            ?>
                        <small class="text-muted fs-12 text-center  d-block mt-2">Prazo p/ Transferência: <strong>3 dia úteis.</strong></small>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="SolicitarAntecipacaoModal" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
            <div class="modal-header center text-center d-block">
                    <h5 class="modal-title" id="">Declarar Repasse</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true"></span>   
                    </button>
                </div>
                <div class="card-body">
                    <p>ENVIE O REPASSE PARA:<br>
                        <small>Nome da empresa:</small>
                        <br>
                        <strong>BBX NEGÓCIOS DIGITAIS</strong><br>
                        <small>Banco:</small>
                        <br>
                        <strong>290 - PagSeguro Internet S.A.</strong><br>
                        <small>Agência:</small>
                        <br>
                        <strong>0001</strong><br>
                        <small>Número da conta:</small>
                        <br>
                        <strong>10339404-5</strong><br>
                        <small>Tipo:</small>
                        <br>
                        <strong>Conta de pagamento</strong><br>
                        <small>CNPJ e Chave PIX:</small>
                        <br>
                        <strong>36.852.258/0001-63</strong><br>
                    </p>
                    <?php 
                        $get_bank_acc_list = $conn->prepare('SELECT account_id, account_user_id, account_bank, account_agency, account_number, account_type, account_pix_type, account_pix_key, account_status FROM bank_account_list WHERE account_user_id = :user__id AND account_status = 2');
                        $get_bank_acc_list->execute(array('user__id' => $user__id));
                    
                        if ($get_bank_acc_list->rowCount() == 0){
                            $disable_request_btn = true;
                        ?>

                           <div class="alert alert-danger fade show text-center" style=" padding: 0.6em; line-height: 1em; border-radius: 0.785em; ">
                                <small>Você precisa ter pelo menos 1 conta cadastrada e aprovada antes de efeturar um saque. <a href="/perfil/contas-bancarias/" style="font-weight: bold; color: #a11313;">Ver Contas Bancárias</a></small>
                            </div>

                        <?php
                        }
                        ?>

                    <form id="RepasseForm" method="POST" action="">

                        <div class="row">
                            <div class="col-md-8 mb-3 m-auto">
                                <p class="mb-3 h4 font-weight-thin d-block text-center">Valor do Repasse</p>
                                <input type="hidden" name="action" value="transfer-request">
                                <input type="text" class="form-control text-center mb-2 money" name="valor-repasse" id="valor-repasse" placeholder="R$ XXXX,XX" required="" style=" font-size: 1.2em; letter-spacing: 1px; ">
                                <small class="text-muted"><p class="mb-4 font-weight-thin d-block text-center">Disponível p/ Repasse: <strong><?php echo $stats['a_liberar']; ?></strong></p></small>
                                
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8 mb-5 m-auto">
                                <p class="mb-3 h4 font-weight-thin d-block text-center">Anexar Comprovante</label>
                                <div class="input-group mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-file-invoice-dollar"></i></span>
                                    </div>
                                    <div class="custom-file">
                                        <input type="file" id="comprovante-repasse" class="custom-file-input" name="comprovante"  accept=".png, .jpg, .pdf">
                                        <label class="custom-file-label">Selecionar arquivo...</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                                if (@$disable_request_btn != true){
                            ?>
                        <button class="btn btn-success btn-lg btn-block transfer-request" type="submit" data-action="transfer-request"><i class="fas fa-hand-holding-usd"></i> Confirmar Repasse</button>
                            <?php
                            } else {
                            ?>
                        <span class="btn btn-success btn-lg btn-block disabled" type="submit" data-action="transfer-request"><i class="fas fa-hand-holding-usd"></i> Confirmar Repasse</span>
                            <?php
                            }
                            ?>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>