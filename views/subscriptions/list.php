<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');

session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
} else if ($_SESSION['UserPlan'] == 6) {
    header('Location: ' . SERVER_URI . '/pedidos/dashboard-operador');
    exit;
}

$user__id = $_SESSION['UserID'];

$page_title = "Lista de Assinaturas | Logzz";
$sidebar_expanded = false;
$select_datatable_page = true;
$subscriber_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

if (isset($_GET['page'])){
    if ($_GET['page'] > 1){
        $offset = ($_GET['page'] - 1) * 5;
    } else {
        $offset = 0;
    }
} else {
    $offset = 0;
}

$default_period = 90;

#Verifica se/os filtros ativos.
if (isset($_GET['filtro']) && $_GET['filtro'] == "ativo") {

    # Filtro por DATA
    if (isset($_GET['data-final']) && !(empty($_GET['data-final']))) {

        $date_formated = pickerDateFormate($_GET['data-final']);
        $date_end = date('Y-m-d', strtotime($date_formated));

    } else {

        $date_end = date('Y-m-d');
    }

    if (isset($_GET['data-inicio']) && !(empty($_GET['data-inicio']))) {

        $date_formated = pickerDateFormate($_GET['data-inicio']);
        $date_init = date('Y-m-d', strtotime($date_formated));

    } else {

        $date_init = date('Y-m-d', strtotime($date_end . '-' . $default_period . 'days'));

    }

    if (!(empty($_GET['assinante']))) {
        $filter_by_subscriber = addslashes($_GET['assinante']);

        if (!(empty($_GET['plano'])) && $_GET['plano'] > 0) {
            # Filtra por ASSINANTE e PLANO
            $filter_by_plan = addslashes($_GET['plano']);
            $get_all_subscribers = $conn->prepare("SELECT * FROM users AS u INNER JOIN subscriptions AS s ON u.user__id = s.subscription_id WHERE (s.user__id = :user__id AND s.user_plan = :plan) AND (s.subscription_renewal BETWEEN :date_init AND :date_end) ORDER BY s.subscription_renewal DESC");
            // $get_all_subscribers->execute(array('plan' => $filter_by_plan, 'date_init' => $date_init, 'date_end' => $date_end));
            $get_all_subscribers->bindParam(':user__id', $filter_by_subscriber, PDO::PARAM_INT);
            $get_all_subscribers->bindParam(':plan', $filter_by_plan, PDO::PARAM_INT);
            $get_all_subscribers->bindParam(':date_init', $date_init, PDO::PARAM_STR);
            $get_all_subscribers->bindParam(':date_end', $date_end, PDO::PARAM_STR);
            $get_all_subscribers->execute();
            
        } else {
            # Filtra por ASSINANTE
            $get_all_subscribers = $conn->prepare("SELECT * FROM users AS u INNER JOIN subscriptions AS s ON u.user__id = s.subscription_id WHERE s.user__id = :user__id AND (s.subscription_renewal BETWEEN :date_init AND :date_end) ORDER BY s.subscription_renewal DESC");
            // $get_all_subscribers->execute(array('plan' => $filter_by_plan, 'date_init' => $date_init, 'date_end' => $date_end));
            $get_all_subscribers->bindParam(':user__id', $filter_by_subscriber, PDO::PARAM_INT);
            $get_all_subscribers->bindParam(':date_init', $date_init, PDO::PARAM_STR);
            $get_all_subscribers->bindParam(':date_end', $date_end, PDO::PARAM_STR);
            $get_all_subscribers->execute();
        }
        
    } else if (!(empty($_GET['plano']))) {

        # Filtra por PLANO
        $filter_by_plan = addslashes($_GET['plano']);
        $get_all_subscribers = $conn->prepare("SELECT * FROM users AS u INNER JOIN subscriptions AS s ON u.user__id = s.subscription_id WHERE s.user_plan = :plan AND (s.subscription_renewal BETWEEN :date_init AND :date_end) ORDER BY s.subscription_renewal DESC");
        // $get_all_subscribers->execute(array('plan' => $filter_by_plan, 'date_init' => $date_init, 'date_end' => $date_end));
        $get_all_subscribers->bindParam(':plan', $filter_by_plan, PDO::PARAM_INT);
        $get_all_subscribers->bindParam(':date_init', $date_init, PDO::PARAM_STR);
        $get_all_subscribers->bindParam(':date_end', $date_end, PDO::PARAM_STR);
        $get_all_subscribers->execute();

    } else {

        # Sem filtros
        $get_all_subscribers = $conn->prepare("SELECT * FROM users AS u INNER JOIN subscriptions AS s ON u.user__id = s.subscription_id WHERE (s.user_plan > 1 AND s.user_plan < 5) AND subscription_renewal BETWEEN :date_init AND :date_end ORDER BY s.subscription_renewal DESC");
        $get_all_subscribers->execute(array('date_init' => $date_init, 'date_end' => $date_end));

    }

} else {

    $date_end = date('Y-m-d');
    $date_init = date('Y-m-d', strtotime($date_end . '-' . $default_period . 'days'));

    # Sem filtros
    $get_all_subscribers = $conn->prepare("SELECT * FROM users AS u INNER JOIN subscriptions AS s ON u.user__id = s.subscription_id WHERE (s.user_plan > 1 AND s.user_plan < 5) AND subscription_renewal BETWEEN :date_init AND :date_end ORDER BY s.subscription_renewal DESC");
    $get_all_subscribers->execute(array('date_init' => $date_init, 'date_end' => $date_end));

}



?>
<div class="container-fluid">
    <?php
    if ((@$vendas[0] == 0 || @$vendas[0] == null) && !(isset($_GET['filtro']))) {
        # Mensagem genérica caso todos os dados do gráfico estejam zerados.
    ?>
    <?php
    }

    if (isset($_GET['filtro']) && $_GET['filtro'] == 'ativo') {

        $breadcumb = "Filtros Ativos:&nbsp; ";

        if (!(empty(@$_GET['data-inicio']))) {
            $breadcumb .= '<span class="badge badge-success light">Data: <b>' . date("d/m", strtotime($date_init)) . '</b> a <b>' . date("d/m", strtotime($date_end)) . '</b></span>';
        }

        if (!(empty(@$_GET['assinante']))) {

            $users_search_list = $conn->prepare("SELECT full_name FROM users WHERE user__id = :user__id");
            $users_search_list->execute(array('user__id' => $_GET['assinante']));
            $user_name = $users_search_list->fetch();
            

                $breadcumb .= '<span class="badge badge-success light">Assinante: <b>' . $user_name[0] . '</b></span>';

            
        }

        if (!(empty(@$_GET['plano']))) {
            switch ($_GET['plano']) {
                case 2:
                    $status = 'Silver';
                    break;
                case 4:
                    $status = 'Personalizado';
                    break;
                case 3:
                    $status = 'Gold';
                    break;
                case 1:
                    $status = 'Bronze';
                    break;
                default:
                    $status = 'Todos';
                    break;
            }
            $breadcumb .= '<span class="badge badge-success light">Plano: <b>' . $status . '</b></span>';
        }
    }
    ?>

                <div class="d-flex flex-wrap mb-2 align-items-center justify-content-end">
                    <div class="d-flex mb-3">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-rounded btn-outline-success mr-2" data-toggle="dropdown" aria-expanded="false"><i class="fas fa-download scale2 mr-2"></i> Exportar</button>
                            <div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 42px, 0px);">
                                <a class="dropdown-item" href="#" id="export-to-csv"><i class="fas fa-file-csv scale2 mr-2"></i> CSV</a>
                                <a class="dropdown-item" href="#" id="export-to-xlsx"><i class="fas fa-file-excel scale2 mr-2"></i> Excel</a>
                                <a class="dropdown-item" href="#" id="export-to-pdf"><i class="fas fa-file-pdf scale2 mr-2"></i> PDF</a>
                            </div>
                            <button type="button" class="btn btn-rounded btn-success filter-btn"><i class="fas fa-sliders-h scale2 mr-2" aria-hidden="true"></i>Filtros</button>
                        </div>
					</div>
				</div>

    <div class="row">
        <div class="col-xl-12 col-xxl-12">
            <div class="mb-3 mr-3">
                <h6 class="fs-14 text-muted mb-0"><?php echo @$breadcumb; ?></h6>
            </div>
            <div class="row">
                <div class="col-12">
                        <div class="card">
                            <!-- <div class="card-header">
                                <h4 class="card-title">Fees Collection</h4>
                            </div> -->
                            <div class="card-body">
                                <div class="table-responsive">
                                    <div id="example4_wrapper" class="dataTables_wrapper no-footer">
                                    <?php
                                        // $get_all_subscribers = $conn->prepare("SELECT * FROM users AS u INNER JOIN subscriptions AS s ON u.user__id = s.subscription_id WHERE (s.user_plan > 0 AND s.user_plan < 5) AND subscription_renewal BETWEEN :date_init AND :date_end ORDER BY s.subscription_start DESC");
                                        // $get_all_subscribers->execute(array('date_init' => $date_init, 'date_end' => $date_end));
                                        // $received = $get_all_subscribers->rowCount();

                                        if ($get_all_subscribers->rowCount() != 0) {
                                    ?>
                                        <table id="example4" class="display min-w850 dataTable no-footer">
                                            <thead>
                                                <tr role="row">
                                                    <th>Data</th>
                                                    <th>Usuário</th>
                                                    <th>Faturamento</th>
                                                    <th>Taxa (R$)</th>
                                                    <th>Plano</th>
                                                    <th>Mês</th>
                                                    <th>Status</th>
                                            </thead>
                                        <tbody>
                                    <?php
                                        while ($row = $get_all_subscribers->fetch()) {
                                            $user__id = $row['user__id'];
                                            $user_fullname = $row['full_name'];
                                            $user_code = $row['user_code'];
                                            $subscription_renewal = $row['subscription_renewal'];
                                            $plan_price = $row['plan_price'];
                                            $subscription_pay_status = $row['subscription_pay_status'];

                                            # Recorrências
                                            $get_count_recurrences = $conn->prepare('SELECT COUNT(billing_value) AS total, SUM(billing_value) AS sum FROM billings WHERE (user__id = :user__id AND billing_released IS NOT NULL) AND (billing_type = "RECURRENCE" OR billing_type = "PLAN_UPGRADE") GROUP BY billing_released');
                                            $get_count_recurrences->execute(array('user__id' => $user__id));
                                            
                                            if ($get_count_recurrences->rowCount() > 0){
                                                $get_count_recurrences = $get_count_recurrences->fetch();
                                                $all_recurrences = $get_count_recurrences['total'];
                                                $all_recurrences_sum = $get_count_recurrences['sum'];
                                            } else {
                                                $all_recurrences_sum = $all_recurrences = 0;
                                            }

                                            $plan_id = $row['user_plan'];
                                            $plan_string = userPlanString($plan_id);

                                            switch ($subscription_pay_status) {
                                                case 0:
                                                    $status = '<span class="badge light badge-danger">Cancelada</span>';
                                                    break;
                                                case 1:
                                                    $status = '<span class="badge light badge-success">Ativa</span>';
                                                    break;
                                                case 2:
                                                    $status = '<span class="badge light badge-warning">Em aberto</span>';
                                                    break;
                                                case 3:
                                                    $status = '<span class="badge light badge-light">Reembolsada</span>';
                                                    break;
                                                default:
                                                    $status = '<span class="badge light badge-success">Ativa</span>';
                                                    break;
                                            }
                                    ?>
                                            <tr>
                                                <td><?php echo date_format(date_create($subscription_renewal), 'd/m'); ?></td>
                                                <td><?php echo $user_fullname . " <small>[" . $user_code . "]</small>"; ?></td>
                                                <td><?php echo "R$ " . number_format($plan_price, 2, ",", "."); ?></td>
                                                <td><?php echo "R$ " . number_format(($plan_price * 0.0379), 2, ",", ".");?></td>
                                                <td><?php echo $plan_string; ?></td>
                                                <td><?php echo $all_recurrences + 1; ?></td>
                                                <td><?php echo $status; ?></td>
                                            </tr>
                                            <?php
                                        }
                                    ?>
                                        </tbody>
                                    </table>
                                    <?php
                                        } else {
                                    ?>
                                    <h5 class="text-center text-muted pt-2 pb-5">Nenhum resultado para o(s) filtro(s) ativo(s).</h5>
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

<div class="chatbox">
    <div class="chatbox-close"></div>
    <div class="col-xl-12">
        <div class="card">
            <div class="mt-4 center text-center ">
                <h4 class="card-title">Filtros</h4>
            </div>
            <div class="card-body">
                <div id="smartwizard" class="form-wizard order-create">
                    <div class="row">
                        <div class="col-lg-12 mb-2">

                            <form id="ShopFilter" action="" method="GET">
                                <div class="row">

                                    <div class="col-lg-12">
                                        <input type="hidden" class="form-control mb-2" name="filtro" value="ativo">
                                        <p class="mb-1"><small>por Data</small></p>
                                        <input type="hidden" class="form-control mb-2" name="filtro" value="ativo">
                                        <input name="data-inicio" placeholder="Do dia ..." class="datepicker-default form-control picker__input mb-2 data-inicio" id="datepicker" readonly="" aria-haspopup="true" aria-expanded="false" aria-readonly="false" aria-owns="datepicker_root" value="<?php echo @$_GET['data-inicio']; ?>">
                                        <input name="data-final" placeholder=".. ao dia" class="datepicker-default form-control picker__input data-final" id="datepicker" readonly="" aria-haspopup="true" aria-expanded="false" aria-readonly="false" aria-owns="datepicker_root" value="<?php echo @$_GET['data-final']; ?>">

                                        <div class="picker" id="datepicker_root" aria-hidden="true">
                                            <div class="picker__holder" tabindex="-1">
                                                <div class="picker__frame">
                                                    <div class="picker__wrap">
                                                        <div class="picker__box">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-12 mt-3">
                                        <div class="example">
                                            <div class="form-group">
                                                <label class="text-label"><small>por Assinante</small></label>
                                                <select id="select-subscriber-name" class="d-flex default-select" data-live-search="true">
                                                    <option selected value="">Todos os Assinantes</option>
                                                    <?php
                                                    $users_search_list = $conn->prepare("SELECT full_name, user__id FROM users");
                                                    $users_search_list->execute();
                                                    while ($user_name = $users_search_list->fetch()) {
                                                    ?>
                                                        <option <?php if (isset($_GET['assinante'])) { if($user_name["user__id"] == $_GET['assinante']) {echo "selected"; }} ?> value="<?php echo $user_name["user__id"]; ?>"><?php echo $user_name["full_name"]; ?></option>
                                                    <?php
                                                    }
                                                    ?>
                                                </select>
                                                <input type="hidden" id="text-subscriber-name" name="assinante" value="<?php echo @$_GET['assinante']; ?>" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                            <p style="margin-bottom: 0.5rem;"><small>por Plano</small></p>
                                            <select id="select-filter-status-id" class="d-block default-select">
                                                <option id="all-status-option" value="" selected>Todos os Planos</option>
                                                <option <?php if (@$_GET['plano'] == '1') {
                                                            echo "selected";
                                                        } ?> value="1">Bronze</option>
                                                <option <?php if (@$_GET['plano'] == '2') {
                                                            echo "selected";
                                                        } ?> value="2">Silver</option>
                                                <option <?php if (@$_GET['plano'] == '3') {
                                                            echo "selected";
                                                        } ?> value="3">Gold</option>
                                                <option <?php if (@$_GET['plano'] == '4') {
                                                            echo "selected";
                                                        } ?> value="4">Personalizado</option>
                                            </select>
                                            <input type="hidden" id="text-filter-status-id" name="plano" value="<?php echo @$_GET['plano']; ?>">
                                    </div>

                                    <div class="col-lg-12 mt-4">
                                        <button type="submit" id="SubmitButton" class="btn btn-block btn-success"><i class="fas fa-sliders-h scale2 mr-2" aria-hidden="true"></i>Filtrar</button>
                                        <a href="<?php echo SERVER_URI; ?>/assinaturas/lista/" class="btn btn-block">Limpar Filtros</a>
                                    </div>
                            </form>

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