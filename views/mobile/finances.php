<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
} else if ($_SESSION['UserPlan'] == 6) {
    header('Location: ' . SERVER_URI . '/perfil/financeiro-operador/');
}

$user__id = $_SESSION['UserID'];

$userPlan = $_SESSION['UserPlan'];



# Valor Disponível p/ Saque
$saque = $conn->prepare('SELECT meta_value FROM transactions_meta WHERE meta_key = "commission_balance" AND user__id = :user__id');
$saque->execute(array('user__id' => $user__id));

$saque = $saque->fetch();
if (@$saque['0'] == null) {
    $saque = 0;
    $saque_btn_disable = true;
} else {
    $saque = $saque['meta_value'];
}

# Valor Disponível p/ Antecipação
$antecipacao = $conn->prepare('SELECT meta_value FROM transactions_meta WHERE meta_key = "anticipation_balance" AND user__id = :user__id');
$antecipacao->execute(array('user__id' => $user__id));

$antecipacao = $antecipacao->fetch();
if (@$antecipacao['0'] == null || @$antecipacao['0'] < 0) {

    $antecipacao = 0;
    $antecipacao_btn_disable = true;
} else {

    $antecipacao = $antecipacao['meta_value'];
}

# Saque em Análise
$analise = $conn->prepare('SELECT SUM(meta_value) FROM transactions_meta WHERE meta_key = "in_review_balance" AND user__id = :user__id');
$analise->execute(array('user__id' => $user__id));

$analise = $analise->fetch();
if (@$analise['0'] == null) {

    $analise = 0;
} else {

    $analise = $analise['0'];
}

# Total já sacado
$sacado = $conn->prepare('SELECT SUM(billing_value) as S FROM billings WHERE user__id = :user__id AND billing_request IS NOT NULL AND billing_type = "SAQUE" ORDER BY billing_request DESC');
$sacado->execute(array('user__id' => $user__id));

$sacado = $sacado->fetch();
if (@$sacado['S'] == null) {

    $sacado = 0;
} else {

    $sacado = $sacado['S'];
}

# Total em Assinaturas
$get_total_signs = $conn->prepare('SELECT SUM(billing_value) AS total FROM billings WHERE (user__id = :user__id AND billing_released IS NOT NULL) AND (billing_type = "RECURRENCE" OR billing_type = "PLAN_UPGRADE") ORDER BY billing_id');
$get_total_signs->execute(array('user__id' => $user__id));

if ($get_total_signs->rowCount() > 0) {
    $get_total_signs = $get_total_signs->fetch();
    $assinaturas = $get_total_signs['total'];
} else {
    $assinaturas = 0;
}

# Total já cobrado
$get_total_charge = $conn->prepare('SELECT SUM(billing_value) AS total FROM billings WHERE user__id = :user__id AND (billing_type = "COBRANCA" AND billing_released IS NOT NULL) ORDER BY billing_id');
$get_total_charge->execute(array('user__id' => $user__id));

if ($get_total_charge->rowCount() > 0) {
    $get_total_charge = $get_total_charge->fetch();
    $cobrado = $get_total_charge['total'];
} else {
    $cobrado = 0;
}


$stats = array(

    'disponivel_saque' => "R$ " .  number_format($saque, 2, ',', '.'),
    'a_liberar' => "R$ " .  number_format($antecipacao, 2, ',', '.'),
    'total_sacado' => "R$ " .  number_format($sacado, 2, ',', '.'),
    'total_assinaturas' => "R$ " .  number_format($assinaturas, 2, ',', '.'),
    'em_analise' => "R$ " .  number_format($analise, 2, ',', '.'),
    'total_cobrado' => "R$ " .  number_format($cobrado, 2, ',', '.')

);


$page_title = "Financeiro | App Logzz";
$profile_page = true;
$select_datatable_page = true;

$iphone = strpos($_SERVER['HTTP_USER_AGENT'], "iPhone");
$ipad = strpos($_SERVER['HTTP_USER_AGENT'], "iPad");
$android = strpos($_SERVER['HTTP_USER_AGENT'], "Android");
$palmpre = strpos($_SERVER['HTTP_USER_AGENT'], "webOS");
$berry = strpos($_SERVER['HTTP_USER_AGENT'], "BlackBerry");
$ipod = strpos($_SERVER['HTTP_USER_AGENT'], "iPod");
$symbian =  strpos($_SERVER['HTTP_USER_AGENT'], "Symbian");
if ($iphone || $ipad || $android || $palmpre || $ipod || $berry || $symbian == true) {
    $mobile = 1;
    require_once(dirname(__FILE__) . '/../../includes/layout/fullwidth/fullwidth-header.php');
} else {
    $mobile = 0;
    require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');
}

?>

<style>
    .img-fluid {
        max-width: 40%;
        display: block;
        margin-left: auto;
        margin-right: auto;
        margin-bottom: 10px;
    }

    @media only screen and (max-width: 756px) {
        .widget-stat .media>span {
            height: 50px !important;
            width: 50px !important;
            min-width: 50px !important;
        }

        .font-size-value {
            font-size: 25px;
        }
    }
</style>

<input type="hidden" id="user-plan" value="<?= $userPlan ?>">

<div class="container-fluid h-100 mt-5">


    <div class="col-12 d-md-none d-block">
        <img class="img-fluid" src="/images/logo-full.png">
    </div>

    <!-- row -->
    <div class="row my-5" style="justify-content: space-between;">

        <div class="col-xl-6 col-lg-6 col-sm-6">
            <div class="widget-stat card" style="background-color: #cffeea;">
                <div class="card-body p-4">
                    <!-- Saldo Disponível -->
                    <div class="media">
                        <span class="mr-3" style="background-color: #2bc155;color: #cffeea;">
                            <i class="fas fa-dollar-sign"></i>
                        </span>
                        <div class="media-body text-right">
                            <label class="h1 mt-2 font-size-value" style="color: #00895f;"><?php echo $stats['disponivel_saque']; ?></label>
                            <p class="mb-1 font-weight-thin">Saldo Disponível</p>
                            <!-- Botão detalhe/modal -->
                            <button class="btn btn-success btn-xs float-right ml-2" data-toggle="modal" data-target="#balance-modal">
                                <i class="fas fa-search"></i> Detalhes
                            </button>
                            <!-- Modal Saldo Disponível -->
                            <div class="modal fade" id="balance-modal" tabindex="-1" role="dialog" aria-modal="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header center text-center d-block">
                                            <h5 class="modal-title" id="balance">Detalhes de Saldo</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                                <span aria-hidden="true"></span>
                                            </button>
                                        </div>

                                        <?php
                                        $info_last_transaction = $conn->prepare('SELECT billing_request, billing_value FROM billings WHERE user__id = :user__id AND billing_request IS NOT NULL AND billing_type = "SAQUE" ORDER BY billing_request DESC LIMIT 1');
                                        $info_last_transaction->execute(array('user__id' => $user__id));
                                        $info_last_transaction = $info_last_transaction->fetch();
                                        ?>

                                        <div class="card-body">
                                            <h4 class="fs-16 d-block font-w600" style="text-align: left;">Total Já Sacado</h4>

                                            <div class="row">
                                                <div class="col-lg-8 col-md-8 mb-3">
                                                    <p class="fs-28 text-black font-w600 mb-1" style="text-align: left;"><?php echo $stats['total_sacado']; ?></p>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-6 col-xs-6 col-xxl-6 mb-3">
                                                    <div class="media bg-light p-3 rounded align-items-center">
                                                        <svg class="mr-2" width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M6.07438 25H7.95454V22.6464C11.8595 22.302 14 19.6039 14 16.8197C14 12.7727 10.8471 11.9977 7.95454 11.3088V5.10907C9.34297 5.4535 10.1529 6.5155 10.2686 7.66361H13.7975C13.5372 4.42021 11.281 2.61194 7.95454 2.32492V0H6.07438V2.35362C2.4876 2.66935 0 4.87945 0 8.09415C0 12.1412 3.18182 12.9449 6.07438 13.6625V19.977C4.45455 19.69 3.64463 18.628 3.52893 17.1929H0C0 20.4363 2.54545 22.3594 6.07438 22.6751V25ZM10.6736 16.992C10.6736 18.4845 9.69008 19.69 7.95454 19.977V14.1504C9.51653 14.6383 10.6736 15.3559 10.6736 16.992ZM3.35537 7.92193C3.35537 6.17107 4.48347 5.22388 6.07438 5.02296V10.8209C4.5124 10.333 3.35537 9.58668 3.35537 7.92193Z" fill="#FE634E"></path>
                                                        </svg>
                                                        <div class="media-body">
                                                            <span class="fs-12 d-block mb-1">Valor do Último Saque</span>
                                                            <span class="fs-16 text-black"><?php if ($info_last_transaction == true) {
                                                                                                echo "R$ " . number_format($info_last_transaction['billing_value'], 2, ",", ".");
                                                                                            } else {
                                                                                                echo "--";
                                                                                            } ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6 col-xxl-6 mb-3">
                                                    <div class="media bg-light p-3 rounded align-items-center">
                                                        <svg class="mr-" width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
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
                                                            <span class="fs-12 d-block mb-1">Data do Último Saque</span>
                                                            <span class="fs-16 text-black"><?php if ($info_last_transaction == true) {
                                                                                                echo date_format(date_create($info_last_transaction['billing_request']), 'd/m H:i');
                                                                                            } else {
                                                                                                echo "--/-- --:--";
                                                                                            } ?></span>
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

                            <!-- Modal Detalhes Total Sacado -->
                            <div class="modal fade" id="cashedout-modal" tabindex="-1" role="dialog" aria-modal="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header center text-center d-block">
                                            <h5 class="modal-title" id="cashedout">Detalhes de Cobranças</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                                <span aria-hidden="true"></span>
                                            </button>
                                        </div>

                                        <?php
                                        $get_last_charge = $conn->prepare('SELECT billing_released, SUM(billing_value) AS total FROM billings WHERE user__id = :user__id AND (billing_type = "COBRANCA" AND billing_released IS NOT NULL) GROUP BY billing_released ORDER BY billing_id DESC LIMIT 1');
                                        $get_last_charge->execute(array('user__id' => $user__id));

                                        if ($get_last_charge->rowCount() > 0) {
                                            $get_last_charge = $get_last_charge->fetch();
                                            $last_charge_value = number_format($get_last_charge['total'], 2, ",", ".");
                                            $last_charge_date = date_format(date_create($get_last_charge['billing_released']), "d/m H:i");
                                        } else {
                                            $last_charge_date = "--/-- --:--";
                                            $last_charge_value = 0;
                                        }

                                        $get_next_charge = $conn->prepare('SELECT billing_released, SUM(billing_value) AS total FROM billings WHERE user__id = :user__id AND (billing_type = "COBRANCA" AND billing_released IS NULL) GROUP BY billing_released ORDER BY billing_id DESC LIMIT 1');
                                        $get_next_charge->execute(array('user__id' => $user__id));

                                        if ($get_next_charge->rowCount() > 0) {
                                            $get_next_charge = $get_next_charge->fetch();
                                            $next_charge_value = number_format($get_next_charge['total'], 2, ",", ".");
                                        } else {
                                            $next_charge_value = 0;
                                        }

                                        $get_max_charge_amount = $conn->prepare('SELECT max_charge_amount FROM users WHERE user__id = :user__id');
                                        $get_max_charge_amount->execute(array('user__id' => $user__id));

                                        if ($get_max_charge_amount->rowCount() > 0) {
                                            $get_max_charge_amount = $get_max_charge_amount->fetch();
                                            $max_charge_amount_value = number_format($get_max_charge_amount['max_charge_amount'], 2, ",", ".");
                                        } else {
                                            $max_charge_amount_value = 0;
                                        }

                                        ?>


                                        <div class="card-body">
                                            <h4 class="fs-16 d-block font-w600" style="text-align: left;">Saldo Pedente <small class="fs-12 text-muted"><i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Valor consumido em logística desde a última cobrança, será cobrado automaticamente quando atingir o limite atual da conta."></i></small></h4>

                                            <div class="row">
                                                <div class="col-lg-8 col-md-8 mb-3">
                                                    <p class="fs-28 text-black font-w600 mb-1" style="text-align: left;"><?php echo "R$ " . $next_charge_value; ?></p>
                                                    <small class="d-block text-left">Próx. Cobrança ao atingir <strong><?php echo "R$ " . $max_charge_amount_value; ?></strong></small>
                                                </div>
                                                <div class="col-lg-4 col-xs-4 mb-3">
                                                    <?php if ($next_charge_value != 0) { ?>
                                                        <button type="button" class="btn btn-rounded btn-success mt-1" id="make-charge-now">Pagar Agora</button>
                                                    <?php } ?>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-3 col-xs-6 col-xxl-6 mb-3">
                                                    <div class="media bg-light p-3 rounded align-items-center">
                                                        <svg class="mr-2" width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M6.07438 25H7.95454V22.6464C11.8595 22.302 14 19.6039 14 16.8197C14 12.7727 10.8471 11.9977 7.95454 11.3088V5.10907C9.34297 5.4535 10.1529 6.5155 10.2686 7.66361H13.7975C13.5372 4.42021 11.281 2.61194 7.95454 2.32492V0H6.07438V2.35362C2.4876 2.66935 0 4.87945 0 8.09415C0 12.1412 3.18182 12.9449 6.07438 13.6625V19.977C4.45455 19.69 3.64463 18.628 3.52893 17.1929H0C0 20.4363 2.54545 22.3594 6.07438 22.6751V25ZM10.6736 16.992C10.6736 18.4845 9.69008 19.69 7.95454 19.977V14.1504C9.51653 14.6383 10.6736 15.3559 10.6736 16.992ZM3.35537 7.92193C3.35537 6.17107 4.48347 5.22388 6.07438 5.02296V10.8209C4.5124 10.333 3.35537 9.58668 3.35537 7.92193Z" fill="#FE634E"></path>
                                                        </svg>
                                                        <div class="media-body">
                                                            <span class="fs-12 d-block mb-1">Valor da Última Cobrança</span>
                                                            <span class="fs-16 text-black"><?php echo "R$ " . $last_charge_value; ?></span>
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
                                                            <span class="fs-12 d-block mb-1">Data da Última Cobrança</span>
                                                            <span class="fs-16 text-black"><?php echo $last_charge_date; ?></span>
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

        <div class="col-xl-6 col-lg-6 col-sm-6">
            <div class="widget-stat card" style="background-color: #cffeea;">
                <div class="card-body p-4">
                    <!-- Saldo a Liberar -->
                    <div class="media">
                        <span class="mr-3" style="background-color: #2bc155;color: #cffeea; ">
                            <i class="fas fa-hourglass-half"></i>
                        </span>
                        <div class="media-body text-right">
                            <label class="h1 mt-2 font-size-value" style="color: #00895f;"><?php echo $stats['a_liberar']; ?></label>
                            <p class="mb-1 font-weight-thin">Saldo a Liberar</p>
                            <?php if (!(isset($antecipacao_btn_disable))) { ?>
                                <button type="button" class="btn btn-success btn-xs float-right" data-toggle="modal" data-target="#SolicitarAntecipacaoModal">
                                    <i class="fas fa-hand-holding-usd"></i>&nbsp;&nbsp;Antecipar
                                </button>
                            <?php } ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-4 col-sm-6">
            <div class="widget-stat card" style="background-color: #fff6db;">
                <div class="card-body p-4">
                    <!-- Sem Análise -->
                    <div class="media">
                        <span class="mr-3" style="background-color: #856404;color: #fff6db;">
                            <i class="far fa-clock"></i>
                        </span>
                        <div class="media-body text-right">
                            <label class="h1 mt-2 font-size-value" style="color: #856404;"><?php echo $stats['em_analise']; ?></label>
                            <p class="mb-1 font-weight-thin">Saque em Análise</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <div class="d-block d-md-none w-100 mb-3">   
        <button class="btn btn-light bg-white btn-lg btn-block">
            <a href="../../views/mobile/dashboard.php" style="color:inherit">Voltar</a>
        </button> 
    </div>

</div>

<div class="modal fade" id="SolicitarSaqueModal" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Solicitar Saque</h5>
            </div>
            <div class="card-body">
                <?php if ($userPlan < 5) : ?>
                    <div class="alert alert-warning fade show text-center" style=" padding: 0.6em; line-height: 1em; border-radius: 0.785em; ">
                        <small>Será descontado R$2,99 de taxa de saque.</small>
                    </div>
                <?php endif ?>
                <form id="SaveCardForm" method="POST" action="">

                    <div class="row">
                        <div class="col-md-8 mb-3 m-auto">
                            <p class="mb-3 h4 font-weight-thin d-block text-center">Valor do Saque</p>
                            <input type="text" class="form-control text-center mb-2 money" name="valor-saque" id="valor-saque" placeholder="R$ XXXX,XX" required="" style=" font-size: 1.2em; letter-spacing: 1px; ">
                            <small class="text-muted">
                                <p class="mb-4 font-weight-thin d-block text-center">Disponível p/ Saque: <strong><?php echo $stats['disponivel_saque']; ?></strong></p>
                            </small>

                        </div>
                    </div>

                    <?php
                    $get_bank_acc_list = $conn->prepare('SELECT account_id, account_user_id, account_bank, account_agency, account_number, account_type, account_pix_type, account_pix_key, account_status FROM bank_account_list WHERE account_user_id = :user__id AND account_status = 2');
                    $get_bank_acc_list->execute(array('user__id' => $user__id));

                    if ($get_bank_acc_list->rowCount() > 0) {

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
                                while ($bank_acc_list = $get_bank_acc_list->fetch()) {

                                    $bank_name = bankName($bank_acc_list['account_bank']);
                                    $agency = $bank_acc_list['account_agency'];
                                    $number = $bank_acc_list['account_number'];
                                    if ($bank_acc_list['account_type'] == 1) {
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
                    if (@$disable_request_btn != true) {
                    ?>
                        <a class="btn btn-success btn-lg btn-block billing-request mt-4" data-action="billing-request"><i class="fas fa-hand-holding-usd"></i> Confirmar Saque</a>
                    <?php
                    } else {
                    ?>
                        <span class="btn btn-success btn-lg btn-block mt-4 disabled"><i class="fas fa-hand-holding-usd"></i> Confirmar Saque</span>
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
            <div class="modal-header">
                <h5 class="modal-title">Solicitar Saque Antecipado</h5>
            </div>


            <div class="card-body">
                <?php if ($userPlan < 5) : ?>
                    <div class="alert alert-warning fade show text-center" style=" padding: 0.6em; line-height: 1em; border-radius: 0.785em; ">
                        <small>O recebimento antes do prazo incorre em taxa administrativa de 4,99% sobre o valor antecipado + R$2,99 de taxa de saque.</small>
                    </div>
                <?php else : ?>
                    <div class="alert alert-warning fade show text-center" style=" padding: 0.6em; line-height: 1em; border-radius: 0.785em; ">
                        <small>O recebimento antes do prazo incorre em taxa administrativa de 4,99% sobre o valor antecipado</small>
                    </div>
                <?php endif ?>
                <form id="SaveCardForm2" method="POST" action="">

                    <div class="row">
                        <div class="col-md-8 mb-3 m-auto">
                            <p class="mb-3 h4 font-weight-thin d-block text-center">Valor da Antecipação</p>
                            <input type="text" class="form-control text-center mb-2 money" name="valor-antecipacao" id="valor-antecipacao" placeholder="R$ XXXX,XX" required="" style=" font-size: 1.2em; letter-spacing: 1px; ">
                            <small class="text-muted">
                                <p class="mb-4 font-weight-thin d-block text-center">Disponível p/ Antecipação: <strong><?php echo $stats['a_liberar']; ?></strong></p>
                            </small>

                        </div>
                    </div>

                    <?php
                    $get_bank_acc_list = $conn->prepare('SELECT account_id, account_user_id, account_bank, account_agency, account_number, account_type, account_pix_type, account_pix_key, account_status FROM bank_account_list WHERE account_user_id = :user__id AND account_status = 2');
                    $get_bank_acc_list->execute(array('user__id' => $user__id));

                    if ($get_bank_acc_list->rowCount() > 0) {

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
                                while ($bank_acc_list = $get_bank_acc_list->fetch()) {

                                    $bank_name = bankName($bank_acc_list['account_bank']);
                                    $agency = $bank_acc_list['account_agency'];
                                    $number = $bank_acc_list['account_number'];
                                    if ($bank_acc_list['account_type'] == 1) {
                                        $type = "Corrente";
                                    } else {
                                        $type = "Poupança";
                                    }
                                ?>
                                    <tr>
                                        <td style="text-align: center; padding: 0px">
                                            <div class="custom-control custom-checkbox checkbox-success check-lg ml-3">
                                                <input type="checkbox" name="bank-account-to-antecipation" class="custom-control-input bank-checkbox-a" id="customCheckBoxA<?php echo $bank_acc_list['account_id']; ?>" value="<?php echo $bank_acc_list['account_id']; ?>" required="">
                                                <label class="custom-control-label text-center m-auto" for="customCheckBoxA<?php echo $bank_acc_list['account_id']; ?>" style="border-color: #2fde91"></label>
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

                    <input type="hidden" name="conta-selecionada" value="0" id="text-bank-checkbox-a">
                    <?php
                    if (@$disable_request_btn != true) {
                    ?>
                        <button class="btn btn-success btn-lg btn-block anticipation-request" type="submit" data-action="anticipation-request"><i class="fas fa-hand-holding-usd"></i> Confirmar Antecipação</button>
                    <?php
                    } else {
                    ?>
                        <span class="btn btn-success btn-lg btn-block disabled" type="submit" data-action="anticipation-request"><i class="fas fa-hand-holding-usd"></i> Confirmar Antecipação</span>
                    <?php
                    }
                    ?>
                    <small class="text-muted fs-12 text-center  d-block mt-2">Prazo p/ Transferência: <strong>3 dia úteis.</strong></small>

                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once(dirname(__FILE__) . '/footer.php');
?>