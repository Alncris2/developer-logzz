<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
}

$page_title = "Indique e Ganhe | Logzz";
$indicate_page = true;
$orders_page = $select_datatable_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

$user__id = $_SESSION['UserID'];

$get_users_infos = $conn->prepare('SELECT * FROM subscriptions INNER JOIN users ON users.user__id = subscriptions.user__id WHERE users.user__id = :user__id');
$get_users_infos->execute(array('user__id' => $user__id));

if ($get_users_infos->rowCount() != 0) {
    while ($row = $get_users_infos->fetch()) {
        $user_cod               = $row['user_code'];
        $user_name              = $row['full_name'];
        $user_comission         = $row['user_plan_commission_recruitment'];
    }
} else {
    exit;
}

$get_user_card = $conn->prepare('SELECT * FROM cards WHERE card_user_id = :user__id AND card_active = 1');
$get_user_card->execute(array('user__id' => $user__id));

$array_total_release = $conn->prepare('SELECT (SELECT COUNT(o.order_id) FROM orders o INNER JOIN users u WHERE o.user__id = u.user__id AND order_delivery_date > CURRENT_DATE()-7 AND o.order_status = 3 or o.order_status = 8) as requests
FROM recruitment r INNER JOIN subscriptions s INNER JOIN users u ON r.recruited_id = u.user__id AND u.user__id = s.user__id WHERE s.user_plan < 4 AND r.user__recruiter_id = :user__recruiter_id');
$array_total_release->execute(array('user__recruiter_id' => $user__id));

$array_total_released = $conn->prepare('SELECT (SELECT COUNT(o.order_id) FROM orders o WHERE o.user__id = u.user__id AND order_delivery_date < CURRENT_DATE()-7 AND o.order_status = 3 or o.order_status = 8) as requests
FROM recruitment r INNER JOIN subscriptions s INNER JOIN users u ON r.recruited_id = u.user__id AND u.user__id = s.user__id WHERE s.user_plan < 4 AND r.user__recruiter_id = :user__recruiter_id');
$array_total_released->execute(array('user__recruiter_id' => $user__id));

while ($row = $array_total_release->fetch()) {
    $total_release +=  $row['requests'];
}
while ($row = $array_total_released->fetch()) {
    $total_released +=  $row['requests'];
}


$total_release = $user_comission * $total_release;
$total_released = $user_comission * $total_released;


/**
 * PEGAR SALDO Á LIBERAR (DATA DO PEDIDO MENOR QUE 7 DIAS) E ATUALIZAR NO BANCO.
 */
$total_release_query = "SELECT COUNT(*) as request FROM orders o INNER JOIN recruitment r WHERE o.user__id = r.recruited_id AND o.order_status = 3 AND r.user__recruiter_id = :user__id_released AND order_delivery_date BETWEEN NOW() - INTERVAL 7 DAY AND NOW()";
$stmt = $conn->prepare($total_release_query);
$stmt->execute(['user__id_released' => $user__id]);
$total_release = $stmt->fetch(\PDO::FETCH_ASSOC)['request'];


//Se Vendedor Indique e ganhe padrao 3 se Filiado Padrao 1
$total_release *= $user_comission;


$query = "UPDATE recruitment_commission_meta SET meta_value_release = :to_release WHERE meta_key = 'in_review_balance_commission' AND user__recruiter_id = :user__recruiter_id";
$stmt = $conn->prepare($query);
$stmt->execute([
    'to_release' => $total_release,
    'user__recruiter_id' => $user__id
]);


/**
 * PEGAR TOTAL JÁ SACADO (billing_commission_request_withdrawn)
 */

$query = "SELECT meta_value_payer FROM recruitment_commission_meta WHERE meta_key = 'billing_commission_request_withdrawn' AND user__recruiter_id = :user__recruiter_id";
$stmt = $conn->prepare($query);
$stmt->execute([
    'user__recruiter_id' => $user__id
]);

$total_withdrawn = $stmt->fetch(\PDO::FETCH_ASSOC)['meta_value_payer'];


/**
 * PEGAR SALDO DISPONÍVEL E ATUALIZAR (QUE JÁ PASSARAM DE 7 DIAS)
 */
$total_release_query = "SELECT COUNT(*) as request FROM orders o INNER JOIN recruitment r WHERE o.user__id = r.recruited_id AND o.order_status = 3 AND r.user__recruiter_id = :user__id AND o.order_delivery_date < NOW() - INTERVAL 7 DAY AND o.order_delivery_date NOT BETWEEN NOW() - INTERVAL 7 DAY AND NOW()";
$stmt = $conn->prepare($total_release_query);
$stmt->execute(['user__id' => $user__id]);
$total_release_avaialabe = $stmt->fetch(\PDO::FETCH_ASSOC)['request'];

$total_release_disponible = $total_release_avaialabe - $total_withdrawn;
$query = "UPDATE recruitment_commission_meta SET meta_value_available = :meta_value_available WHERE meta_key = 'billing_commission_request' AND user__recruiter_id = :user__recruiter_id";
$stmt = $conn->prepare($query);
$stmt->execute([
    'meta_value_available' => $total_release_disponible,
    'user__recruiter_id' => $user__id
]);



// PEGAR DADOS DE COMISSÃO
$query = "SELECT * FROM recruitment_commission_meta WHERE meta_key = 'billing_commission_request' AND user__recruiter_id = :user__id";
$stmt = $conn->prepare($query);
$stmt->execute(['user__id' => $_SESSION['UserID']]);
$data_comissions = $stmt->fetch();



// VERIFICAR SE EXISTEM USUARIOS CADASTRADO PELO LINK DE CONVITE
$query_to_verify_invitation = "SELECT * FROM recruitment r WHERE user__recruiter_id = :user__id";
$stmt = $conn->prepare($query_to_verify_invitation);
$stmt->execute([
    'user__id' => $user__id
]);

$get_all_users_indicate_rows = $stmt->rowCount();
$get_all_users_indicate_data = $stmt->fetchAll(\PDO::FETCH_ASSOC);


if ($get_all_users_indicate_rows > 0) {
    $array_data_user = [];


    foreach ($get_all_users_indicate_data as $user) {

        //PEGAR USUÁRIO 
        $query = "SELECT * FROM users u WHERE u.user__id = :user__id";
        $stmt = $conn->prepare($query);
        $stmt->execute([
            'user__id' => $user['recruited_id']
        ]);

        $user_data = $stmt->fetch(\PDO::FETCH_ASSOC);

        // QUANTIDADE DE PEDIDO
        $query = "SELECT COUNT(*) as requests FROM orders o WHERE o.user__id = :user__id AND o.order_status = 3";
        $stmt = $conn->prepare($query);
        $stmt->execute([
            'user__id' => $user['recruited_id']
        ]);

        $requests = $stmt->fetch();

        array_push($array_data_user, [
            'name' => $user_data['full_name'] . " " . '[' . $user_data['user_code'] . ']',
            'requests' => $requests['requests'],
            'total' => $requests['requests'] * $user_comission
        ]);
    }
}



if ($get_user_card->rowCount() != 0) {
    $has_active_card = true;

    $card_details = $get_user_card->fetch();
    $card_brand = $card_details['card_brand'];
    $card_final = $card_details['card_final'];
    $card_id = $card_details['card_id'];
} else {
    $has_active_card = false;
}
?>

<!--

SELECT u.user__id, full_name, user_code,
(SELECT COUNT(o.order_id) FROM orders o WHERE o.user__id = u.user__id AND o.order_status = 3 or o.order_status = 8) as requests
FROM recruitment r INNER JOIN subscriptions s INNER JOIN users u ON r.recruited_id = u.user__id AND u.user__id = s.user__id WHERE s.user_plan < 4 AND r.user__recruiter_id = 2

-->

<!-- row -->
<div class="container-fluid">
    <div class="row" style="justify-content: space-between;">
        <div class="col-xl-12 col-lg-12 col-sm-12">
            <div class="widget-stat card">
                <div class="card-header">
                    <div class="col-xl-6 col-lg-6 col-sm-6">
                        <h2>Recrutamento</h2>
                    </div>
                    <div class="col-xl-6 col-lg-6 col-sm-6">
                        <div class="widget-stat card bg-success my-0">
                            <div class="card-body py-3">
                                <div class="media ai-icon">
                                    <span class="mr-2" style="background-color: #cffeea; color: #2bc155;">
                                        <a class="btn-copy-link" data-text="<?php echo SERVER_URI . '/cadastrar/?invite=' . $user_cod ?>" data-toggle="tooltip" data-placement="top" title="Copiar link" data-original-title="Copiar link"><i class="fas fa-link"></i>
                                        </a>
                                    </span>
                                    <div class="media-body">
                                        <p class="mb-1 text-white">LINK DE CONVITE</p>
                                        <input type="text" class="form-control btn-copy-address" value="<?php echo SERVER_URI . '/cadastrar/?invite=' . $user_cod ?>" readonly data-text="<?php echo SERVER_URI . '/cadastrar/?invite=' . $user_cod ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <span>Recrute usuários para o Logzz, e ganhe <b><u><?php echo "R$ " . number_format($user_comission, 2, ',', ''); ?></u></b> sobre todos os seus pedidos concluidos</span>
                    <i class="fas fa-info-circle ml-1" role="button" id="helper" data-toggle="tooltip" data-placement="top" title="Você receberá uma comissão todas as vezes que um usuário recrutado por você cadastrar um produto e realizar um pedido de status COMPLETO ou ENVIADO."></i>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-3 col-sm-6">
            <div class="widget-stat card">
                <div class="card-body">
                    <div class="media ai-icon">
                        <span class="mr-2" style="background-color: #cffeea; color: #2bc155;">
                            <i class="fas fa-dollar-sign"></i>
                        </span>
                        <div class="media-body">
                            <p class="mb-1">Total de comissão sacada </p>
                            <h4 class="mb-0"><?php echo "R$ "  .  number_format($total_withdrawn, 2, ',', ''); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-3 col-sm-6">
            <div class="widget-stat card">
                <div class="card-body">
                    <div class="media ai-icon">
                        <span class="mr-2" style="background-color: #cffeea; color: #2bc155;">
                            <i class="fas fa-dollar-sign"></i>
                        </span>
                        <div class="media-body">
                            <p class="mb-1">A liberar</p>
                           <?php // var_dump($total_release); ?>
                            <h4 class="mb-0"><?php echo "R$ "  .  number_format($total_release, 2, ',', ''); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-3 col-sm-6">
            <div class="widget-stat card">
                <div class="card-body">
                    <div class="media ai-icon">
                        <span class="mr-2" style="background-color: #cffeea; color: #2bc155;">
                            <i class="fas fa-clock"></i>
                        </span>
                        <div class="media-body">
                            <p class="mb-1">Em Análise</p>
                            <h4 class="mb-0"><?php echo "R$ "  .  number_format(0, 2, ',', ''); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-3 col-sm-6">
            <div class="widget-stat card">
                <div class="card-body">
                    <div class="media ai-icon">
                        <span class="mr-2" style="background-color: #cffeea; color: #2bc155;">
                            <i class="fas fa-hand-holding-usd"></i>
                        </span>
                        <div class="media-body">
                            <p class="mb-1">Disponível para saque</p>
                            <h4 class="mb-0"><?php echo "R$ "  .  number_format($total_release_disponible, 2, ',', ''); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-12 col-lg-12 col-sm-12">
            <div class="widget-stat card">
                <div class="card-header">
                    <h4 class="card-title">Relatórios</h4>
                    <div class="card-bottom">
                        <button type="button" class="btn btn-success btn-billing-request" data-toggle="modal" data-target="#SolicitarSaqueModal">
                            <i class="fas fa-hand-holding-usd"></i>&nbsp;&nbsp;Sacar
                        </button>
                        <!-- <button type="button" class="btn btn-rounded btn-success filter-btn" class="btn btn-success text-nowrap"><i class="fas fa-sliders-h scale2 mr-2" aria-hidden="true"></i>Filtros</button> -->
                    </div>
                </div>
                <div class="card-body">
                    <div class="col-xl-12" style="padding-left: 5px;">
                        <div class="tab-content">
                            <div id="All" class="tab-pane active fade show">
                                <div class="table-responsive" style="overflow-x: visible;">
                                    <table id="recruited-list" class="table card-table display dataTablesCard" data-page-length='10' data-order='[[0, "desc"]]'>
                                        <thead>
                                            <tr>
                                                <th class="col-md-3">Usuário [Cód.]</th>
                                                <th class="col-md-3">Pedidos</th>
                                                <th class="col-md-3">Comissão total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($get_all_users_indicate_rows > 0) : ?>
                                                <?php foreach ($array_data_user as $user) : ?>
                                                    <tr>
                                                        <td><?= $user['name']; ?></td>
                                                        <td><?= $user['requests']; ?></td>
                                                        <td><?= "R$ " . number_format($user['total'], 2, ',', '');  ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">

                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>

<div class="modal fade" id="SolicitarSaqueModal" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header center text-center d-block">
                <h5 class="modal-title">Solicitar Saque</h5>
            </div>
            <div class="card-body">
                <form id="SaveCardForm" method="POST" action="">
                    <div class="row">
                        <div class="col-md-8 mb-3 m-auto">
                            <p class="mb-3 h4 font-weight-thin d-block text-center">Valor do Saque</p>
                            <input type="text" class="form-control text-center mb-2 money" name="valor-saque" id="valor-saque" placeholder="R$ XXXX,XX" required="" style=" font-size: 1.2em; letter-spacing: 1px; ">
                            <small class="text-muted">
                                <p class="mb-4 font-weight-thin d-block text-center">Disponível p/ Saque: <strong><?= number_format($total_release_disponible, 2, ',', ''); ?></strong></p>
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
                                                <input type="checkbox" name="bank-account-to-transfer" class="custom-control-input bank-checkbox-s" id="customCheckBoxS<?php echo $bank_acc_list['account_id']; ?>" value="<?php echo $bank_acc_list['account_id']; ?>">
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
                        <a class="btn btn-success btn-lg btn-block billing-request mt-4" data-action="billing_commission_request"><i class="fas fa-hand-holding-usd"></i> Confirmar Saque</a>
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
<?php require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php'); ?>