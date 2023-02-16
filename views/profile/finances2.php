
<?php
error_reporting(-1);
ini_set('display_errors', 1);

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
$saque = $conn->prepare('SELECT commission_balance FROM balance_resume_ref WHERE user_id = :user__id');
$saque->execute(array('user__id' => $user__id));

$saque = $saque->fetch();
if (@$saque[0] == null) {
    $saque = 0;
    $saque_btn_disable = true;
} else {
    $saque = $saque['commission_balance'];
}

# Valor Disponível p/ Antecipação
$antecipacao = $conn->prepare('SELECT anticipation_balance FROM balance_resume_ref WHERE user_id = :user__id');
$antecipacao->execute(array('user__id' => $user__id));

$antecipacao = $antecipacao->fetch();
if (@$antecipacao['0'] == null || @$antecipacao['0'] == 0) {

    $antecipacao = 0;
    $antecipacao_btn_disable = true;
} else {

    $antecipacao = $antecipacao['anticipation_balance'];
}

# Saque em Análise
$analise = $conn->prepare('SELECT in_review_balance FROM balance_resume_ref WHERE user_id = :user__id');
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

//Verifica se os filtros estão ativos
if (isset($_GET['filtro']) && $_GET['filtro'] == 'ativo') {

    # Filtro Por DATA
    $filter_data_result = array();

    if (!(empty($_GET['data-inicio']))) {
        $start_date = pickerDateFormate($_GET['data-inicio']);
        $start_date = explode(" ", $start_date);
        $start_date = $start_date[0] . " 00:00:00";
    } else {
        $start_date = '2020-01-01';
    }

    if (!(empty($_GET['data-final']))) {
        $final_date = pickerDateFormate($_GET['data-final']);
        $final_date = explode(" ", $final_date);
        $final_date = $final_date[0] . " 23:59:59";
    } else {
        $final_date = date('Y-m-d') . " 23:59:59";
    }

    $date_ids = $conn->prepare('SELECT billing_id FROM billings WHERE billing_request BETWEEN :start_date AND :final_date');
    $date_ids->execute(array('start_date' => $start_date, 'final_date' => $final_date));

    while ($date_id = $date_ids->fetch()) {
        array_push($filter_data_result, $date_id['billing_id']);
    }

    $filter_result = $filter_data_result;

    # Filtro Por Descrição
    if (!empty($_GET['descricao'])) {

        $filter_description_result = array();

        if ($_GET['descricao'] == 'saque') {
            $description_ids = $conn->prepare('SELECT billing_id FROM billings WHERE billing_type = "SAQUE"');
            $description_ids->execute();
        } else if ($_GET['descricao'] == 'cobranca') {
            $description_ids = $conn->prepare('SELECT billing_id FROM billings WHERE billing_type = "COBRANCA"');
            $description_ids->execute();
        } else if ($_GET['descricao'] == 'antecipacao') {
            $description_ids = $conn->prepare('SELECT billing_id FROM billings WHERE billing_type = "ANTECIPACAO"');
            $description_ids->execute();
        } else if ($_GET['descricao'] == 'assinatura') {
            $description_ids = $conn->prepare('SELECT billing_id FROM billings WHERE billing_type = "PLAN_UPGRADE" OR billing_type = "RECURRENCE"');
            $description_ids->execute();
        }

        while ($description_id = $description_ids->fetch()) {
            array_push($filter_description_result, $description_id['billing_id']);
        }

        $filter_result = array_intersect($filter_result, $filter_description_result);
    }

    # Filtro Por Status
    if (!empty($_GET['status'])) {

        $filter_status_result = array();

        if ($_GET['status'] == 'pendente') {
            $status_ids = $conn->prepare('SELECT billing_id FROM billings WHERE billing_released IS NULL');
            $status_ids->execute();
        } else if ($_GET['status'] == 'sucesso') {
            $status_ids = $conn->prepare('SELECT billing_id FROM billings WHERE billing_released IS NOT NULL');
            $status_ids->execute();
        } else if ($_GET['status'] == 'recusado') {
            $status_ids = $conn->prepare('SELECT billing_id FROM billings WHERE billing_status = "REFUSED"');
            $status_ids->execute();
        } else {
            $status_ids = $conn->prepare('SELECT billing_id FROM billings');
            $status_ids->execute();
        }

        while ($status_id = $status_ids->fetch()) {
            array_push($filter_status_result, $status_id['billing_id']);
        }

        $filter_result = array_intersect($filter_result, $filter_status_result);
    }
}


$page_title = "Financeiro | Logzz";
$profile_page = true;
$select_datatable_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

?>

<input type="hidden" id="user-plan" value="<?= $userPlan ?>">

<style>
    /* .btn-group>.btn:not(:last-child):not(.dropdown-toggle), .btn-group>.btn-group:not(:last-child)>.btn */

    .filter-days .btn-group>.btn:not(:first-child),
    .filter-days .btn-group>.btn:not(:last-child) {
        border-left: unset;
        border-right: unset;
    }

    .filter-days .btn-group>.btn:first-child {
        border-left: 1px solid #B1B1B1;
        border-right: unset;
        border-top-left-radius: 30px;
        border-bottom-left-radius: 30px;
    }

    .filter-days .btn-group>.btn:last-child {
        border-left: unset;
        border-right: 1px solid #B1B1B1;
        border-top-right-radius: 30px;
        border-bottom-right-radius: 30px;
    }

    .btn.option {
        padding: .5rem .5rem;
        font-weight: 500;
        font-size: .9rem;
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

    .frb-group {
        margin: 15px 0;
    }

    .frb input[type="radio"]:empty,
    .frb input[type="checkbox"]:empty {
        display: none;
    }

    .frb input[type="radio"]~label:before,
    .frb input[type="checkbox"]~label:before {
        font-family: 'Font Awesome 5 Free';
        content: '\f0c8';
        position: absolute;
        top: 50%;
        margin-top: -11px;
        left: 15px;
        font-size: 22px;
    }

    .frb input[type="radio"]:checked~label:before,
    .frb input[type="checkbox"]:checked~label:before {
        content: '\f14a';
    }

    .frb input[type="radio"]~label,
    .frb input[type="checkbox"]~label {
        position: relative;
        cursor: pointer;
        width: 100%;
        border-radius: 5px;
        background-color: #e5e8e5eb;
        border-radius: 20px;
        padding-bottom: 0.5rem !important;
        padding-top: 0.5rem !important;
    }

    .frb input[type="radio"]~label:focus,
    .frb input[type="radio"]~label:hover,
    .frb input[type="checkbox"]~label:focus,
    .frb input[type="checkbox"]~label:hover {
        box-shadow: 0px 0px 3px #333;
    }

    .frb input[type="radio"]:checked~label,
    .frb input[type="checkbox"]:checked~label {
        color: #fafafa;
    }

    .frb input[type="radio"]:checked~label,
    .frb input[type="checkbox"]:checked~label {
        background-color: #2fde91;
    }

    .frb.frb-default input[type="radio"]:checked~label,
    .frb.frb-default input[type="checkbox"]:checked~label {
        color: #ffffff;
    }

    .frb input[type="radio"]:empty~label span,
    .frb input[type="checkbox"]:empty~label span {
        display: inline-block;
    }

    .frb input[type="radio"]:empty~label span.frb-title,
    .frb input[type="checkbox"]:empty~label span.frb-title {
        font-size: 16px;
        font-weight: 700;
        margin: 5px 0px 0px 50px;
    }

    .frb input[type="radio"]:empty~label span.frb-description,
    .frb input[type="checkbox"]:empty~label span.frb-description {
        font-weight: normal;
        color: #999;
        margin: 0px 5px 5px 50px;
    }

    .frb input[type="radio"]:empty:checked~label span.frb-description,
    .frb input[type="checkbox"]:empty:checked~label span.frb-description {
        color: #fafafa;
    }

    .frb.frb-default input[type="radio"]:empty:checked~label span.frb-description,
    .frb.frb-default input[type="checkbox"]:empty:checked~label span.frb-description {
        color: #ffffff;
    }
</style>
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

        <div class="col-xl-6 col-lg-6 col-sm-4">
            <div class="widget-stat card" style="background-color: #cffeea;">
                <div class="card-body p-4">
                    <div class="media">
                        <span class="mr-3" style="background-color: #2bc155;color: #cffeea;">
                            <i class="fas fa-hourglass-half"></i>
                        </span>
                        <div class="media-body text-right">
                            <label class="h1 mt-2" style="color: #00895f; "><?php echo $stats['a_liberar']; ?></label>
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

    </div>

    <div class="row" style="justify-content: space-between;">
        <div class="col-xl-4 col-lg-4 col-sm-4">
            <div class="widget-stat card" style="background-color: #fff6db;">
                <div class="card-body p-4">
                    <div class="media">
                        <span class="mr-3" style="background-color: #856404;color: #fff6db;">
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

        <div class="col-xl-4 col-lg-4 col-sm-4">
            <div class="widget-stat card" style="background-color: #e1e1e1;">
                <div class="card-body p-4">
                    <div class="media">
                        <span class="mr-3" style="background-color: #666c70;color: #e1e1e1;">
                            <i class="fas fa-history"></i>
                        </span>
                        <div class="media-body text-right">
                            <label class="h1 mt-2" style="color: #666c70; "><?php echo $stats['total_assinaturas']; ?></label>
                            <p class="mb-1 font-weight-thin">Total em Assinaturas</p>
                            <button type="submit" class="btn btn-success btn-xs float-right ml-2" data-toggle="modal" data-target="#recurrence-modal">
                                <i class="fas fa-search"></i> Detalhes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-4 col-sm-4">
            <div class="widget-stat card" style="background-color: #e1e1e1;">
                <div class="card-body p-4">
                    <div class="media">
                        <span class="mr-3" style="background-color: #666c70; color: #e1e1e1;">
                            <i class="fas fa-history"></i>
                        </span>
                        <div class="media-body text-right">
                            <label class="h1 mt-2" style="color: #666c70; "><?php echo $stats['total_cobrado'];; ?></label>
                            <p class="mb-1 font-weight-thin">Total Já Cobrado <i class="fs-12 text-muted fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Cobranças realizadas por utilização da logística em pedidos com status enviado."></i></p>
                            <button type="submit" class="btn btn-success btn-xs float-right ml-2" data-toggle="modal" data-target="#cashedout-modal">
                                <i class="fas fa-search"></i> Detalhes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12 col-xxl-12">
            <div class="d-flex flex-wrap mb-2 align-items-center justify-content-between">
                <div class="mb-3 mr-3">
                    <h6 id="result" class="fs-16 text-black font-w600 mb-0"></h6>
                    <span class="fs-14">&nbsp;</span>
                </div>
                <div class="event-tabs mb-3 mr-3"></div>
                <!-- <button type="button" class="btn btn-rounded btn-success filter-btn"><i class="fas fa-sliders-h scale2 mr-2" aria-hidden="true"></i>Filtros</button> -->
            </div>
        </div>
    </div>

    <div id="finances-accordion" class="accordion accordion-with-icon col-md-12">

        <div class="card accordion__item">
            <div class="card-header" data-toggle="collapse" data-target="#historic-transaction-collapse" aria-expanded="false">
                <h4 class="card-title"><i style="color: #777777" class="fas fa-angle-down"></i>&nbsp; Histórico de Movimentação</h4>
            </div>
            <div id="historic-transaction-collapse" class="card-bodyaccordion__body" data-parent="#finances-accordion">
                <div class="accordion__body--text">
                    <form id="filter-historic-transaction">
                        <div class="w-100 row">
                            <div class="form-group col-6">
                                <label class="">Filtro por data: </label>
                                <div class="input-group">
                                    <span class="filter-option-days-historic btn form-control option w-auto btn-outline-dark active" id="today" data-filter="today">Hoje</span>
                                    <span class="filter-option-days-historic btn form-control option w-auto" id="yesterday" data-filter="yesterday">Ontem</span>
                                    <span class="filter-option-days-historic btn form-control option w-auto" id="7days" data-filter="7days">7 dias</span>
                                    <span class="filter-option-days-historic btn form-control option w-auto" id="30days" data-filter="30days">30 dias</span>
                                    <span class="filter-option-days-historic btn form-control option w-auto" id="currentmonth" data-filter="currentmonth">Este mês</span>
                                    <span class="filter-option-days-historic btn form-control option w-auto" id="personalizado" data-filter="personalizado">Personalizado</span>
                                </div>

                                <!-- Modal para escolher os dias do filtro -->
                                <div class="modal fade" id="modalAlterDaysCustom" tabindex="-1" role="dialog" aria-modal="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <div class="modal-body">                                                
                                                <div class="form-group mb-2">
                                                    <input name="data-inicio" value="" placeholder="Do dia ..." class="form-control datepicker-default picker__input" id="data-inicio" readonly="" aria-haspopup="true" aria-expanded="false" aria-readonly="false" aria-owns="datepicker_root">
                                                </div>
                                                <div class="form-group mb-2">
                                                    <input name="data-final" value="" placeholder=".. ao dia" class="form-control datepicker-default picker__input" id="data-final" readonly="" aria-haspopup="true" aria-expanded="false" aria-readonly="false" aria-owns="datepicker_root">
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
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-block btn-success mt-1 close" onclick="get_historic_balance()" data-dismiss="modal" >Filtrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            

                            <div class="form-group col-2">
                                <label class="">Descrição: </label>
                                <select class="form-control " id="select-filter-descricao" name="descricao">
                                    <option value="" selected>Todos</option>
                                    <option value="saque">Saque</option>
                                    <option value="antecipacao">Antecipação</option>
                                    <option value="cobranca">Cobrança</option>
                                    <option value="comissao">Comissão</option>
                                    <option value="despesas">Despesas</option>
                                    <option value="estorno">Estorno</option>
                                </select>
                            </div>

                            <div class="form-group col-2">
                                <label class="">Movimentações: </label>
                                <select class="form-control" id="select-filter-movimentacao" name="movimentacao">
                                    <option value="" selected>Todos</option>
                                    <option value="entrada">Entrada</option>
                                    <option value="saida">Saída</option>
                                </select>
                            </div>

                            <div class="form-group col-2">
                                <label>&nbsp;</label>
                                <div class="" role="group">
                                    <button type="button" class="btn btn-rounded btn-outline-success mr-2" data-toggle="dropdown" aria-expanded="true" style="font-size: 12px;"><i class="fas fa-download scale2 mr-1"></i> Exportar</button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#" id="export-to-csv"><i class="fas fa-file-csv scale2 mr-2"></i> CSV</a>
                                        <a class="dropdown-item" href="#" id="export-to-xlsx"><i class="fas fa-file-excel scale2 mr-2"></i> Excel</a>
                                        <a class="dropdown-item" href="#" id="export-to-pdf"><i class="fas fa-file-pdf scale2 mr-2"></i> PDF</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div id="historic-transaction-accordion" class="accordion accordion-with-icon col-md-12"></div>

                    <div id="reload-transaction-accordion" class="d-none mb-2 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin:auto;background:#fff;display:block;" width="207px" height="150px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
                            <circle cx="84" cy="50" r="10" fill="#2fde91">
                                <animate attributeName="r" repeatCount="indefinite" dur="0.8620689655172413s" calcMode="spline" keyTimes="0;1" values="10;0" keySplines="0 0.5 0.5 1" begin="0s"></animate>
                                <animate attributeName="fill" repeatCount="indefinite" dur="3.4482758620689653s" calcMode="discrete" keyTimes="0;0.25;0.5;0.75;1" values="#2fde91;#2fde91;#2fde91;#2fde91;#2fde91" begin="0s"></animate>
                            </circle>
                            <circle cx="16" cy="50" r="10" fill="#2fde91">
                                <animate attributeName="r" repeatCount="indefinite" dur="3.4482758620689653s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="0;0;10;10;10" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="0s"></animate>
                                <animate attributeName="cx" repeatCount="indefinite" dur="3.4482758620689653s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="16;16;16;50;84" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="0s"></animate>
                            </circle>
                            <circle cx="50" cy="50" r="10" fill="#2fde91">
                                <animate attributeName="r" repeatCount="indefinite" dur="3.4482758620689653s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="0;0;10;10;10" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-0.8620689655172413s"></animate>
                                <animate attributeName="cx" repeatCount="indefinite" dur="3.4482758620689653s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="16;16;16;50;84" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-0.8620689655172413s"></animate>
                            </circle>
                            <circle cx="84" cy="50" r="10" fill="#2fde91">
                                <animate attributeName="r" repeatCount="indefinite" dur="3.4482758620689653s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="0;0;10;10;10" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-1.7241379310344827s"></animate>
                                <animate attributeName="cx" repeatCount="indefinite" dur="3.4482758620689653s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="16;16;16;50;84" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-1.7241379310344827s"></animate>
                            </circle>
                            <circle cx="16" cy="50" r="10" fill="#2fde91">
                                <animate attributeName="r" repeatCount="indefinite" dur="3.4482758620689653s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="0;0;10;10;10" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-2.5862068965517238s"></animate>
                                <animate attributeName="cx" repeatCount="indefinite" dur="3.4482758620689653s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="16;16;16;50;84" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-2.5862068965517238s"></animate>
                            </circle>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="card accordion__item">
            <div class="card-header" data-toggle="collapse" data-target="#future-releases-collapse" aria-expanded="false">
                <h4 class="card-title"><i style="color: #777777" class="fas fa-angle-down"></i>&nbsp; Lançamentos Futuros</h4>
            </div>
            <div id="future-releases-collapse" class="card-bodyaccordion__body" data-parent="#finances-accordion">
                <div class="accordion__body--text">
                    <form id="filter-future-releases">
                        <div class="w-100 row">
                            <div class="form-group col-6">
                                <label class="">Filtro por data: </label>
                                <div class="input-group">
                                    <span class="filter-option-days-future btn form-control option w-auto btn-outline-dark active" id="today-future" data-filter="today">Hoje</span>
                                    <span class="filter-option-days-future btn form-control option w-auto" id="tomorrow-future" data-filter="tomorrow">Amanhã</span>
                                    <span class="filter-option-days-future btn form-control option w-auto" id="7days-future" data-filter="7days">7 dias</span>
                                    <span class="filter-option-days-future btn form-control option w-auto" id="15days-future" data-filter="15days">15 dias</span>
                                    <span class="filter-option-days-future btn form-control option w-auto" id="30days-future" data-filter="30days">30 dias</span>
                                </div>
                            </div>
                            <div class="form-group col-4 my-2 me-2 text-center">
                                <p>&nbsp;</p>
                                <label>Comissão a Receber: <strong>R$ <?= number_format($antecipacao, 2, ',', '.') ?></strong></label>
                            </div>
                            <div class="form-group col-2">
                                <label>&nbsp;</label>
                                <div class="" role="group">
                                    <button type="button" class="btn btn-rounded btn-outline-success mr-2" data-toggle="dropdown" aria-expanded="true" style="font-size: 12px;"><i class="fas fa-download scale2 mr-1"></i> Exportar</button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#" id="export-to-xlsx"><i class="fas fa-file-excel scale2 mr-2"></i> Excel</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>


                    <div id="future-releases-accordion" class="accordion accordion-with-icon col-md-12">
                        <?php
                        $date_start = date('Y-m-d', strtotime('-1000 days'));

                        $get_transactions_days = $conn->prepare("SELECT date(date_format(date_end, '%Y-%m-%d')) AS transaction_day, sum(value_liquid) AS transaction_day_value, COUNT(*) AS transaction_day_quant FROM transactions b WHERE b.user_id = :user_id AND b.date_end > now() GROUP BY transaction_day ORDER BY b.date_end ASC");
                        $get_transactions_days->execute(array('user_id' => $user__id));

                        if ($get_transactions_days->rowCount() > 0) {
                            while ($transactions_days = $get_transactions_days->fetch()) { ?>

                                <div class="card accordion__item">
                                    <div class="card-header" data-toggle="collapse" data-target="#future-releases-<?php echo date('Y-m-d', strtotime($transactions_days['transaction_day'])) ?>" aria-expanded="false">
                                        <div class="col-8 d-flex">
                                            <h6><?php echo date_format(date_create($transactions_days['transaction_day']), 'd/m/y') ?>&nbsp; </h6><small> &nbsp;<?= $transactions_days['transaction_day_quant'] . ($transactions_days['transaction_day_quant'] == 1 ? ' registro' : ' registros') ?></small>
                                        </div>
                                        <div class="col-3 d-flex justify-content-end">Saldo&nbsp;&nbsp; <b>R$<?= number_format($transactions_days['transaction_day_value'], 2, ',', '.') ?></b> </div>
                                        <div class="col-1 d-flex justify-content-end">
                                            <a aria-expanded="false"><i style="color: #777777" class="fas fa-angle-down"></i></a>
                                        </div>
                                    </div>
                                    <div id="future-releases-<?php echo date('Y-m-d', strtotime($transactions_days['transaction_day'])) ?>" class="card-bodyaccordion__body collapse" data-parent="#historic-transaction-accordion">
                                        <table class="table movement-history">
                                            <thead>
                                                <tr>
                                                    <th style="text-align: center;">Agendamento</th>
                                                    <th style="text-align: center;">Conclusão</th>
                                                    <!-- <th style="text-align: center;">Reembolso</th> -->
                                                    <th style="text-align: center;">Comissão</th>
                                                    <!-- <th style="text-align: center;">Taxa</th>
                                                    <th style="text-align: center;">Líquido</th>
                                                    <th style="text-align: center;">Status</th> -->
                                                    <th style="text-align: center;">Liberação</th>
                                                    <th style="text-align: center;">Pedido</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $date_filter = $transactions_days['transaction_day'];
                                                $date_filter_start  = $date_filter . ' 00:00:00';
                                                $date_filter_end    = $date_filter . ' 23:59:59';

                                                $get_transactions_list = $conn->prepare('SELECT t.*, transaction_type_name as type, transaction_status_name as status FROM transactions t INNER JOIN transactions_type ON transaction_type_id = type INNER JOIN transaction_status ON transaction_status_id = status WHERE user_id = :user__id AND date_end between :date_filter_start AND :date_filter_end AND date_end > now() ORDER BY date_end ASC');
                                                $get_transactions_list->execute(array('user__id' => $user__id, 'date_filter_start' => $date_filter_start, 'date_filter_end' => $date_filter_end));
                                                while ($transactions_list = $get_transactions_list->fetch()) {

                                                    if ($transactions_list['type'] == "Saque") {
                                                        $signal = "-";
                                                        $color = "#ff2929";
                                                    } else if ($transactions_list['type'] == "Antecipação") {
                                                        $signal = "+";
                                                        $color = "#2bc155";
                                                    } else if ($transactions_list['type'] == "Cobrança") {
                                                        $signal = "-";
                                                        $color = "#ff2929";
                                                    } else if ($transactions_list['type'] == "Repasse") {
                                                        $signal = "+";
                                                        $color = "#2bc155";
                                                    } else if ($transactions_list['type'] == "Upgrade") {
                                                        $signal = "-";
                                                        $color = "#ff2929";
                                                    } else if ($transactions_list['type'] == "Reembolso") {
                                                        $signal = "-";
                                                        $color = "#ff2929";
                                                    } else if ($transactions_list['type'] == "Frustrado") {
                                                        $signal = "-";
                                                        $color = "#ff2929";
                                                    } else if ($transactions_list['type'] == "Pedido") {
                                                        $signal = "+";
                                                        $color = "#2bc155";
                                                    } else if ($transactions_list['type'] == "Manual") {
                                                        $description = "Alteração Manual";
                                                        $signal = $transactions_list['billing_value_full'] < 0 ? "" : '+';
                                                        $color = "#ff2929";
                                                    } else {
                                                        $description = "";
                                                        $signal = "-";
                                                        $color = "#2bc155";
                                                    }

                                                    if ($transactions_list['bank_proof'] == NULL) {
                                                        $status = "Pendente";
                                                    } else {
                                                        $status = "Liberado";
                                                    } ?>


                                                    <tr>

                                                        <td style="text-align: center;">
                                                            <?php
                                                            if ($transactions_list['type'] == 'Pedido') {
                                                                echo "<a class='btn btn-link text-primary' href='" . SERVER_URI . '/meu-pedido/' . $transactions_list['order_number'] . "' target='_blank'>Pedido</a>";
                                                            } else {
                                                                echo $transactions_list['type'];
                                                            }
                                                            ?>
                                                        </td>
                                                        <td class="fs-12 text-center" data-order="<?php echo date_format(date_create($transactions_list['date_start']), 'YmdHis'); ?>">
                                                            <?php echo date_format(date_create($transactions_list['date_start']), 'd/m/y H:i'); ?></td>
                                                        <!-- <td style="text-align: center;">
                                                            <?php
                                                            if ($transactions_list['type'] == 'Pedido') {
                                                                echo "Entrada";
                                                            } else if ($transactions_list['type'] == 'Antecipação') {
                                                                echo "--";
                                                            } else {
                                                                echo "Saída";
                                                            }
                                                            ?>
                                                        </td> -->
                                                        <td style="text-align: center; color: <?php echo $color; ?>; font-weight: bold;">
                                                            <?php echo $signal . " R$ " . number_format($transactions_list['value_brute'], 2, ",", "."); ?>
                                                        </td>
                                                        <!-- <td style="text-align: center; color: #7e7e7e; font-weight: bold;"><?= "- R$ " . number_format($transactions_list['tax_value'], 2, ",", "."); ?></td>
                                                        <td style="text-align: center; color: <?php echo $color; ?>; font-weight: bold;"><?php echo " R$ " . number_format($transactions_list['value_liquid'], 2, ",", "."); ?></td>
                                                        <td>
                                                            <span class="badge badge-sm d-block m-auto light badge-warning"><i class="far fa-clock"></i> Pendente</span>
                                                        </td> -->
                                                        <td class="fs-12 text-center" data-order="<?php echo date_format(date_create($transactions_list['date_end']), 'YmdHis'); ?>">
                                                            <?php echo date_format(date_create($transactions_list['date_end']), 'd/m/y H:i'); ?>
                                                        </td>
                                                        <td style="text-align: center;">
                                                            <a title="Ver pedido" href="<?= SERVER_URI . "/meu-pedido/" . $transactions_list['order_number'] ?>" target="_blank">
                                                                <i class="fa fa-eye"></i>
                                                            </a>
                                                        </td>
                                                    </tr>

                                                <?php }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                        <?php
                            }
                        } ?>
                    </div>

                    <div id="reload-future-accordion" class="d-none mb-2 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin:auto;background:#fff;display:block;" width="207px" height="150px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
                            <circle cx="84" cy="50" r="10" fill="#2fde91">
                                <animate attributeName="r" repeatCount="indefinite" dur="0.8620689655172413s" calcMode="spline" keyTimes="0;1" values="10;0" keySplines="0 0.5 0.5 1" begin="0s"></animate>
                                <animate attributeName="fill" repeatCount="indefinite" dur="3.4482758620689653s" calcMode="discrete" keyTimes="0;0.25;0.5;0.75;1" values="#2fde91;#2fde91;#2fde91;#2fde91;#2fde91" begin="0s"></animate>
                            </circle>
                            <circle cx="16" cy="50" r="10" fill="#2fde91">
                                <animate attributeName="r" repeatCount="indefinite" dur="3.4482758620689653s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="0;0;10;10;10" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="0s"></animate>
                                <animate attributeName="cx" repeatCount="indefinite" dur="3.4482758620689653s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="16;16;16;50;84" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="0s"></animate>
                            </circle>
                            <circle cx="50" cy="50" r="10" fill="#2fde91">
                                <animate attributeName="r" repeatCount="indefinite" dur="3.4482758620689653s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="0;0;10;10;10" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-0.8620689655172413s"></animate>
                                <animate attributeName="cx" repeatCount="indefinite" dur="3.4482758620689653s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="16;16;16;50;84" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-0.8620689655172413s"></animate>
                            </circle>
                            <circle cx="84" cy="50" r="10" fill="#2fde91">
                                <animate attributeName="r" repeatCount="indefinite" dur="3.4482758620689653s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="0;0;10;10;10" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-1.7241379310344827s"></animate>
                                <animate attributeName="cx" repeatCount="indefinite" dur="3.4482758620689653s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="16;16;16;50;84" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-1.7241379310344827s"></animate>
                            </circle>
                            <circle cx="16" cy="50" r="10" fill="#2fde91">
                                <animate attributeName="r" repeatCount="indefinite" dur="3.4482758620689653s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="0;0;10;10;10" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-2.5862068965517238s"></animate>
                                <animate attributeName="cx" repeatCount="indefinite" dur="3.4482758620689653s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="16;16;16;50;84" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-2.5862068965517238s"></animate>
                            </circle>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>






<!-- Modal Detalhes de Assinaturas -->
<div class="modal fade" id="recurrence-modal" tabindex="-1" role="dialog" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header center text-center d-block">
                <h5 class="modal-title" id="recurrence">Detalhes de Assinaturas</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true"></span>
                </button>
            </div>

            <?php
            $get_last_recurrence = $conn->prepare('SELECT billing_released, billing_value AS total FROM billings WHERE (user__id = :user__id AND billing_released IS NOT NULL) AND (billing_type = "RECURRENCE" OR billing_type = "PLAN_UPGRADE") ORDER BY billing_id DESC LIMIT 1');
            $get_last_recurrence->execute(array('user__id' => $user__id));

            if ($get_last_recurrence->rowCount() > 0) {
                $get_last_recurrence = $get_last_recurrence->fetch();
                $last_recurrence_value = number_format($get_last_recurrence['total'], 2, ",", ".");
                $last_recurrence_date = date_format(date_create($get_last_recurrence['billing_released']), "d/m");
            } else {
                $last_recurrence_date = "--/--";
                $last_recurrence_value = 0;
            }

            $get_next_recurrence = $conn->prepare('SELECT billing_released, SUM(billing_value) AS total FROM billings WHERE user__id = :user__id AND (billing_type = "COBRANCA" AND billing_released IS NULL) GROUP BY billing_released ORDER BY billing_id DESC LIMIT 1');
            $get_next_recurrence->execute(array('user__id' => $user__id));

            if ($get_next_recurrence->rowCount() > 0) {
                $get_next_recurrence = $get_next_recurrence->fetch();
                $next_recurrence_value = number_format($get_next_recurrence['total'], 2, ",", ".");
            } else {
                $next_recurrence_value = 0;
            }

            $get_plan_detais = $conn->prepare('SELECT plan_price, subscription_renewal FROM subscriptions WHERE user__id = :user__id');
            $get_plan_detais->execute(array('user__id' => $user__id));

            if ($get_plan_detais->rowCount() > 0) {
                $get_plan_detais = $get_plan_detais->fetch();
                $plan_price_value = number_format($get_plan_detais['plan_price'], 2, ",", ".");
                if ($get_plan_detais['subscription_renewal'] != NULL) {
                    $subscription_renewal = date_format(date_create($get_plan_detais['subscription_renewal']), "d/m");
                } else {
                    $subscription_renewal = "--/--";
                }
            } else {
                $plan_price_value = 0;
                $subscription_renewal = "--/--";
            }

            ?>


            <div class="card-body">
                <h4 class="fs-16 d-block font-w600" style="text-align: left;">Próx. Mensalidade</h4>

                <div class="row">
                    <div class="col-lg-8 col-md-12 mb-3">
                        <p class="fs-28 text-black font-w600 mb-1" style="text-align: left;"><?php echo "R$ " . $plan_price_value; ?></p>
                        <small class="d-block text-left">Renovação Automática dia <strong><?php echo $subscription_renewal; ?></strong></small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-3 col-xs-6 col-xxl-6 mb-3">
                        <div class="media bg-light p-3 rounded align-items-center">
                            <svg class="mr-2" width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6.07438 25H7.95454V22.6464C11.8595 22.302 14 19.6039 14 16.8197C14 12.7727 10.8471 11.9977 7.95454 11.3088V5.10907C9.34297 5.4535 10.1529 6.5155 10.2686 7.66361H13.7975C13.5372 4.42021 11.281 2.61194 7.95454 2.32492V0H6.07438V2.35362C2.4876 2.66935 0 4.87945 0 8.09415C0 12.1412 3.18182 12.9449 6.07438 13.6625V19.977C4.45455 19.69 3.64463 18.628 3.52893 17.1929H0C0 20.4363 2.54545 22.3594 6.07438 22.6751V25ZM10.6736 16.992C10.6736 18.4845 9.69008 19.69 7.95454 19.977V14.1504C9.51653 14.6383 10.6736 15.3559 10.6736 16.992ZM3.35537 7.92193C3.35537 6.17107 4.48347 5.22388 6.07438 5.02296V10.8209C4.5124 10.333 3.35537 9.58668 3.35537 7.92193Z" fill="#FE634E"></path>
                            </svg>
                            <div class="media-body">
                                <span class="fs-12 d-block mb-1">Mensalidade Anterior</span>
                                <span class="fs-16 text-black"><?php echo "R$ " . $last_recurrence_value; ?></span>
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
                                <span class="fs-16 text-black"><?php echo $last_recurrence_date; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-rounded btn-primary mt-1" data-dismiss="modal">Fechar</button>
            </div>
        </div>
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
                        <a class="btn btn-primary btn-lg btn-block billing-request mt-4" data-action="billing-request"><i class="fas fa-hand-holding-usd"></i> Confirmar Saque</a>
                    <?php
                    } else {
                    ?>
                        <span class="btn btn-primary btn-lg btn-block mt-4 disabled"><i class="fas fa-hand-holding-usd"></i> Confirmar Saque</span>
                    <?php
                    }
                    ?>
                    <small class="text-muted fs-12 text-center  d-block mt-2">Prazo p/ Transferência: <strong>3 dias úteis.</strong></small>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="SolicitarAntecipacaoModal" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Antecipação de saldo</h5>
            </div>


            <div class="card-body">
                <div class="alert alert-warning fade show text-center" style=" padding: 0.6em; line-height: 1em; border-radius: 0.785em; ">
                    <small>O recebimento antes do prazo incorre em taxa administrativa de 4,99% sobre o valor antecipado</small>
                </div>
                <input type="hidden" name="TotalAmountAmount" id="TotalAmountAmount" value="<?php echo number_format($antecipacao, 2, ',', '.') ?>">

                <div class="row">
                    <div class="col-md-8 mb-3 m-auto">
                        <p class="mb-3 h4 font-weight-thin d-block text-center">Valor da Antecipação</p>
                        <input type="text" class="form-control text-center mb-2 money" name="valor-antecipacao" id="valor-antecipacao" placeholder="R$ XXXX,XX" value="<?php echo number_format($antecipacao, 2, ',', '.'); ?>" required="" style=" font-size: 1.2em; letter-spacing: 1px; ">
                        <small class="text-muted">
                            <p class="mb-4 font-weight-thin d-block text-center">
                                <span id="text-value-available-antecipate">Disponível p/ Antecipação: <strong><?php echo $stats['a_liberar']; ?></strong></span>
                            </p>
                        </small>
                    </div>
                </div>
                <div id="orders-simulation" class="d-none mb-2 text-center">
                    <form id="anticipation-request-new">
                        <input type="hidden" name="user__id" value="<?= $_SESSION['UserID'] ?>">
                        <input type="hidden" name="action" value="request-antecipation">
                        <div class="w-100">
                            <span class="text-muted ml-3">Escolha o opção para antecipação:</span>
                            <div id="result-orders-simulation" class="row mb-3 mt-2">
                            </div>
                        </div>
                        <button class="btn btn-primary btn-lg btn-block" type="submit" id="anticipation-request-btn">
                            <i class="fas fa-hand-holding-usd"></i><span id="anticipation-request-text">Confirmar Antecipação</span>
                        </button>
                    </form>
                </div>
                <div id="reload-antecipation" class="d-none">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin:auto;background:#fff;display:block;" width="207px" height="150px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
                        <circle cx="84" cy="50" r="10" fill="#2fde91">
                            <animate attributeName="r" repeatCount="indefinite" dur="0.8620689655172413s" calcMode="spline" keyTimes="0;1" values="10;0" keySplines="0 0.5 0.5 1" begin="0s"></animate>
                            <animate attributeName="fill" repeatCount="indefinite" dur="3.4482758620689653s" calcMode="discrete" keyTimes="0;0.25;0.5;0.75;1" values="#2fde91;#2fde91;#2fde91;#2fde91;#2fde91" begin="0s"></animate>
                        </circle>
                        <circle cx="16" cy="50" r="10" fill="#2fde91">
                            <animate attributeName="r" repeatCount="indefinite" dur="3.4482758620689653s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="0;0;10;10;10" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="0s"></animate>
                            <animate attributeName="cx" repeatCount="indefinite" dur="3.4482758620689653s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="16;16;16;50;84" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="0s"></animate>
                        </circle>
                        <circle cx="50" cy="50" r="10" fill="#2fde91">
                            <animate attributeName="r" repeatCount="indefinite" dur="3.4482758620689653s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="0;0;10;10;10" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-0.8620689655172413s"></animate>
                            <animate attributeName="cx" repeatCount="indefinite" dur="3.4482758620689653s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="16;16;16;50;84" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-0.8620689655172413s"></animate>
                        </circle>
                        <circle cx="84" cy="50" r="10" fill="#2fde91">
                            <animate attributeName="r" repeatCount="indefinite" dur="3.4482758620689653s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="0;0;10;10;10" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-1.7241379310344827s"></animate>
                            <animate attributeName="cx" repeatCount="indefinite" dur="3.4482758620689653s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="16;16;16;50;84" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-1.7241379310344827s"></animate>
                        </circle>
                        <circle cx="16" cy="50" r="10" fill="#2fde91">
                            <animate attributeName="r" repeatCount="indefinite" dur="3.4482758620689653s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="0;0;10;10;10" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-2.5862068965517238s"></animate>
                            <animate attributeName="cx" repeatCount="indefinite" dur="3.4482758620689653s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="16;16;16;50;84" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-2.5862068965517238s"></animate>
                        </circle>
                    </svg>
                </div>
                <button class="btn btn-primary btn-lg btn-block" id="anticipation-simulation-btn">
                    <i class="fas fa-hand-holding-usd"></i><span id="anticipation-simulation-text">Simular Antecipação</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="JustificativaModal" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Justificativa de alteração nos valores</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <i class="fa fa-times" aria-hidden="false"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group my-4" id="text-justification">

                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary btn-lg btn-block mt-4" data-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>


<?php
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/mathjs/11.5.1/math.js" integrity="sha512-gIaHF8pKynuRYPvDDLkS6Gj6dS+tpE4khy3CwBIwyKxK3rOJ+LXFGM97BoZh5xtGnGSIky6TJqxfAZcGA8DN3Q==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    $(document).ready(function() {
        var u = location.protocol + "//" + window.location.hostname;

        $(".segment-select").Segment();

        $('#today').click();

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        $.fn.dataTable.ext.errMode = 'none';

        const formatUS = (value) => {
            var local_value = value.replace(".", "");
            var local_value = local_value.replace(",", ".");
            return parseFloat(local_value).toFixed(2);
        }

        let dollarUS = Intl.NumberFormat("pt-BR", {
            style: "currency",
            currency: "BRL",
        });

        document.querySelector("#valor-antecipacao").addEventListener("input", () => {
            let ValueSimulationAnticipation = formatUS($('#valor-antecipacao').val());
            let TotalAmountAmount = formatUS($('#TotalAmountAmount').val());

            $('#orders-simulation').addClass('d-none');
            $('#anticipation-simulation-btn').removeClass('d-none');

            if (ValueSimulationAnticipation == 'NaN' || math.compare(ValueSimulationAnticipation, 0.00) == 0) {
                $('#anticipation-simulation-btn').attr('disabled', true);
                $('#anticipation-simulation-text').html('Simular Antecipação');
                $('#text-value-available-antecipate').html('Disponível p/ Antecipação: <strong>' + dollarUS.format(TotalAmountAmount) + '</strong>').removeAttr('style');
                return;
            }
            if (math.compare(ValueSimulationAnticipation, TotalAmountAmount) == 0) {
                $('#anticipation-simulation-btn').removeAttr('disabled');
                $('#anticipation-simulation-text').html('Simular Antecipação');
                $('#text-value-available-antecipate').html('Disponível p/ Antecipação: <strong>' + dollarUS.format(TotalAmountAmount) + '</strong>').removeAttr('style');
                return;
            }
            if (math.compare(ValueSimulationAnticipation, TotalAmountAmount) > 0) {
                $('#anticipation-simulation-btn').attr('disabled', true);
                $('#anticipation-simulation-text').html('Simular Antecipação');
                $('#text-value-available-antecipate').html('Valor Máximo para antecipar: <strong>' + dollarUS.format(TotalAmountAmount) + '</strong>').css('color', 'red');
                return;
            }
            if (math.compare(ValueSimulationAnticipation, TotalAmountAmount) < 0) {
                $('#anticipation-simulation-btn').removeAttr('disabled');
                $('#anticipation-simulation-text').html('Simular Antecipação');
                $('#text-value-available-antecipate').html('Disponível p/ Antecipação: <strong>' + dollarUS.format(TotalAmountAmount) + '</strong>').removeAttr('style');
                return;
            }
        })

        $("#anticipation-simulation-btn").click(function() {
            let value_request = $('#valor-antecipacao').val();

            $.ajax({
                url: u + '/ajax/request-values-next-antecipation.php?value_request=' + value_request,
                type: "GET",
                dataType: "json",
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $('#anticipation-simulation-btn').addClass('d-none');
                    $('#reload-antecipation').removeClass('d-none');
                },
                complete: function() {
                    $('#reload-antecipation').addClass('d-none');
                },
                success: function(feedback) {
                    let values = feedback.values;
                    $('#result-orders-simulation').html('');
                    $.each(values, function(index, value) {
                        $('#result-orders-simulation').append(`<div class="col-6 frb frb-default"><input type="radio" id="radio-button-${index}" data-ids="${value.ids}" name="option-value-antecipation" value="${value.value}"><label for="radio-button-${index}"><span class="frb-title">Disponível: <br> <span class="fs-20">R$ ${value.value}</span></span><span class="frb-description">Pedidos: ${value.quant}</span></label></div>`)
                    });
                    $('#orders-simulation').removeClass('d-none');

                },
            }).fail(function(data) {
                $('#anticipation-simulation-btn').removeClass('d-none');
                Swal.fire({
                    title: "Erro de Conexão",
                    text: "não foi possivel simular antecipação, tente novamente.",
                    icon: "warning",
                });
            })
        })

        $("#anticipation-request-new").submit(function() {
            event.preventDefault();
            $('#SolicitarAntecipacaoModal').modal('toggle');

            // formdata.append('value-request', $('#valor-antecipacao').val());
            var value = $('input[name=option-value-antecipation]:checked', '#anticipation-request-new').val();
            var ids = $('input[name=option-value-antecipation]:checked', '#anticipation-request-new').data('ids');

            var local_value = value.replace(".", "");
            var local_value = local_value.replace(",", ".");
            // Taxa de antecipação
            let antecipationTax = local_value * 0.0499;
            // Taxa de saque

            let dollarUS = Intl.NumberFormat("pt-BR", {
                style: "currency",
                currency: "BRL",
            });

            antecipationTax = dollarUS.format(antecipationTax);

            if (value < 1) {
                Swal.fire({
                    title: "Valor da Antecipação Baixo",
                    text: "O valor mínimo é R$ 1,00",
                    icon: "warning",
                });
                return false;
            }

            Swal.fire({
                title: "Confirmar Antecipação de R$ " + value + " ?",
                text: `Uma taxa de ${antecipationTax} será debitada do valor antecipado.`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#2BC155",
                cancelButtonColor: "#FF6D4D",
                confirmButtonText: "Confirmar",
                cancelButtonText: "Cancelar",
                customClass: {
                    title: 'fs-22',
                    htmlContainer: 'fs-14',
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Captura os dados do formulário
                    var anticipationRequestNew = document.getElementById('anticipation-request-new');
                    // Instância o FormData passando como parâmetro o formulário
                    var formdata = new FormData(anticipationRequestNew);
                    formdata.append('ids_request', ids);

                    $.ajax({
                        url: u + "/ajax/billing-request.php",
                        type: "POST",
                        data: formdata,
                        dataType: "json",
                        processData: false,
                        contentType: false,
                        cache: false,
                        beforeSend: function() {
                            display_loader();
                        },
                        complete: function() {
                            display_loader(false);
                        },
                        success: function(feedback) {
                            Swal.fire({
                                title: feedback.title,
                                text: feedback.msg,
                                icon: feedback.type,
                            }).then((value) => {
                                if (feedback.type == "success") {
                                    document.location.reload(true);
                                }
                            });
                        },
                    });
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    Toast.fire({
                        icon: 'success',
                        title: 'Antecipação cancelada!'
                    })
                    return false;
                }
            });
        });

        $('.open-justification').on('click', function() {
            $('#text-justification').html($(this).data('text'));
            $('#JustificativaModal').modal('toggle');
        });

        $('#select-filter-days').on('change', function() {
            if (this.value == 'personalizado') {
                $('#filter-data-custom').click();
            }
        });

        // let RequestHistoricTransation = () => {
        //     let formData = new FormData();
        //     formData.append('type_date', $('#select-filter-days').val());
        //     formData.append('description', $('#select-filter-descricao').val());
        //     formData.append('movement', $('#select-filter-movimentacao').val());
        //     if ($('select-filter-days').val() == 'personalizado') {
        //         formData.append('date_start', $('#data-inicio').val());
        //         formData.append('date_end', $('#data-final').val());
        //     }

        //     $.ajax({
        //         url: url,
        //         type: "GET",
        //         data: {
        //             order
        //         },
        //         dataType: 'json',
        //         processData: true,
        //         contentType: false,
        //         success: function(feedback) {
        //             if (feedback.status != 1) {
        //                 Swal.fire({
        //                     title: feedback.title,
        //                     text: feedback.msg,
        //                     icon: feedback.type,
        //                 }).then((result) => {
        //                     if (result1.isConfirmed) {
        //                         dataTable.ajax.reload();
        //                     }
        //                 });
        //             }

        //             feedback

        //         }
        //     })
        // }

    });

    (function($) {
        $.fn.extend({
            Segment: function() {
                $(this).each(function() {
                    var self = $(this);
                    var onchange = self.attr('onchange');
                    var wrapper = $("<div>", {
                        class: "input-group"
                    });
                    $(this).find("option").each(function() {
                        var option = $("<span>", {
                            class: 'btn form-control option w-auto',
                            onclick: onchange,
                            text: $(this).text(),
                            value: $(this).val()
                        });
                        if ($(this).is(":selected")) {
                            option.addClass("btn-outline-dark active");
                        }
                        wrapper.append(option);
                    });
                    wrapper.find("span.option").click(function() {
                        wrapper.find("span.option").removeClass("btn-outline-dark active");
                        $(this).addClass("btn-outline-dark active");
                        self.val($(this).attr('value'));
                    });
                    $(this).after(wrapper);
                    $(this).hide();
                });
            }
        });
    })(jQuery);
</script>

<script>
    $('.filter-option-days-historic').on('click', function(event){

        let data = $(event.target).attr('id');
        $(".filter-option-days-historic").each(function(index, value) {
            $(this).removeClass('btn-outline-dark active');
            if (data == $(this).attr('id')) {
                $(this).addClass('btn-outline-dark active');
            }
        });

        if(data == 'personalizado'){
            $("#modalAlterDaysCustom").modal('toggle');
            return true;
        }

        get_historic_balance();
        return true;
    })


    
    $('#filter-historic-transaction .form-control').change(function(){
        get_historic_balance();
    });

    function get_historic_balance() {
        dados = new FormData();
        $(".filter-option-days-historic").each(function(index, value) {
            if ($(this).hasClass('active')) {
                dados.append('tipo_date', $(this).data('filter'));
            }
        });
        dados.append('description', $("#select-filter-descricao").val());
        dados.append('movement', $("#select-filter-movimentacao").val());
        dados.append('date_start', $("#data-inicio").val());
        dados.append('date_end', $("#data-final").val());

        $.ajax({
            url: u + "/ajax/get-historic-transaction.php",
            type: "POST",
            data: dados,
            dataType: "json",
            processData: false,
            contentType: false,
            cache: false,
            beforeSend: function() {
                $('#historic-transaction-accordion').html('');
                $('#reload-transaction-accordion').removeClass('d-none');
            },
            success: function(feedback) {
                if (feedback.status == 1) {
                    $('#reload-transaction-accordion').addClass('d-none');

                    $('#historic-transaction-accordion').html(feedback.html_response);

                    $('.movement-history').DataTable({
                        searching: false,
                        paging: true,
                        select: false,
                        lengthChange: false,
                        pageLength: 5,
                        language: {
                            "url": "//cdn.datatables.net/plug-ins/1.13.2/i18n/pt-BR.json"
                        }
                    });


                }

            },
        });
    }

    $('.filter-option-days-future').on('click', function(event){
        let data = $(event.target).attr('id');
        let info = $(event.target).attr('id');
        $(".filter-option-days-future").each(function(index, value) {
            $(this).removeClass('btn-outline-dark active');
            if (data == $(this).attr('id')) {
                $(this).addClass('btn-outline-dark active');
            }
        });
        get_future_releases();
    });

    function get_future_releases() {
        
        dados = new FormData();
        $(".filter-option-days-future").each(function(index, value) {
            if ($(this).hasClass('active')) {
                dados.append('tipo_date', $(this).data('filter'));
            }
        });

        $.ajax({
            url: u + "/ajax/get-future-releases.php",
            type: "POST",
            data: dados,
            dataType: "json",
            processData: false,
            contentType: false,
            cache: false,
            beforeSend: function() {
                $('#future-releases-accordion').html('');
                $('#reload-future-accordion').removeClass('d-none');
            },
            success: function(feedback) {
                if (feedback.status == 1) {
                    $('#reload-future-accordion').addClass('d-none');

                    $('#future-releases-accordion').html(feedback.html_response);

                    $('.movement-history').DataTable({
                        searching: false,
                        paging: true,
                        select: false,
                        lengthChange: false,
                        pageLength: 5,
                        language: {
                            "url": "//cdn.datatables.net/plug-ins/1.13.2/i18n/pt-BR.json"
                        }
                    });
                }
            },
        });
    }
</script>