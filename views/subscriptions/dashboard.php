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

$page_title = "Dashboard Assinaturas | Logzz";
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

$default_period = 30;

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
            require_once(dirname(__FILE__) . '/../../includes/filters/subscriptions-subscriber-plan.php');
            
        } else {
            # Filtra por ASSINANTE
            require_once(dirname(__FILE__) . '/../../includes/filters/subscriptions-subscriber.php');
        }
        
    } else if (!(empty($_GET['plano']))) {

        # Filtra por P_LANO
        $filter_by_plan = addslashes($_GET['plano']);
        require_once(dirname(__FILE__) . '/../../includes/filters/subscriptions-plan.php');

    } else {

        # Sem filtros
        require_once(dirname(__FILE__) . '/../../includes/filters/subscriptions-no-filters.php');

    }

} else {

    $date_end = date('Y-m-d');
    $date_init = date('Y-m-d', strtotime($date_end . '-' . $default_period . 'days'));

    # Sem filtros
    require_once(dirname(__FILE__) . '/../../includes/filters/subscriptions-no-filters.php');
}



$subscriptions_dashboard_charts = array(
);


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
                <div class="col-sm-4 dashboard-number-col">
                    <div class="card">
                        <div class="card-body card-dashboard">
                            <div class="d-flex align-items-end">
                                <div>
                                    <p class="fs-15 mb-1 d-block">Assinaturas</p>
                                    <i class="fas fa-shopping-basket" style="font-size: 1.4em;"></i><span class="fs-24 font-w700"> <?php echo $subscriptions_count; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-4 dashboard-number-col">
                    <div class="card">
                        <div class="card-body card-dashboard">
                            <div class="d-flex align-items-end">
                                <div>
                                    <p class="fs-15 mb-1 d-block">Faturamento</p>
                                    <i class="fas fa-shopping-basket" style="font-size: 1.4em;"></i><span class="fs-24 font-w700"> <?php echo $subscriptions_sum; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-4 dashboard-number-col">
                    <div class="card">
                        <div class="card-body card-dashboard">
                            <div class="d-flex align-items-end">
                                <div>
                                    <p class="fs-15 mb-1 d-block">Cancelamentos</p>
                                    <i class="fas fa-minus-circle" style="font-size: 1.4em;"></i><span class="fs-24 font-w700 d-inline"> <?php echo $reembolsos . "%"; ?>
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
                                <?php  //print_r($dashboard_charts); 
                                ?>
                                <h4 class="fs-18 mb-0 text-black font-w600">Volume de Assinaturas</h4>
                                <span class="fs-12">
                                    <?php
                                    if ((!(isset($_GET['data-inicio'])) || empty($_GET['data-inicio'])) && (!(isset($_GET['data-final'])) || empty($_GET['data-final']))) {
                                        echo "Nos ú1ltimos " . $default_period . " dias.";
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="d-flex mb-3">
                                <!-- <button type="button" class="btn btn-rounded btn-success filter-btn" class="btn btn-success text-nowrap"><i class="fas fa-sliders-h scale5 mr-3" aria-hidden="true"></i>Filtros</button> -->
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
                                <h4 class="fs-18 mb-0 text-black font-w600">Assinaturas por Status</h4>
                                <!-- <?php if (isset($_GET['plano']) && $_GET['plano'] != "") { ?><span class="fs-12">Filtro por Status não se aplica a esse gráfico.</span><?php } else { ?><span class="fs-12">Todos pedidos no período.</span><?php } ?> -->
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <canvas id="subscriptionStatusChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
 
                <div class="col-xl-4 col-xxl-6 col-lg-4">
                    <div class="card">
                        <div class="card-header align-items-start pb-0 border-0">
                            <div>
                                <h4 class="fs-18 mb-0 text-black font-w600">Assinaturas por Plano</h4>
                                <!-- <?php if (isset($_GET['plano']) && $_GET['plano'] != "") { ?><span class="fs-12">Filtro por Status não se aplica a esse gráfico.</span><?php } else { ?><span class="fs-12">Pedidos Completos no período.</span><?php } ?> -->
                            </div>
                        </div>
                        <div class="card-body pt-0">
                                <canvas id="subscriptionPlanChart" height="200"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-xxl-12 col-lg-4">
                    <div class="card">
                        <div class="card-header align-items-start pb-0 border-0 mb-4">
                            <div>
                                <h4 class="fs-18 mb-0 text-black font-w600">Assinantes Mais Antigos </h4>
                            </div>
                            <div class="d-flex mb-3">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-rounded btn-outline-success" data-toggle="dropdown" aria-expanded="false"><i class="fas fa-download scale2 mr-2"></i> Exportar</button>
                                    <div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 42px, 0px);">
                                        <a class="dropdown-item" href="#" id="export-to-csv"><i class="fas fa-file-csv scale2"></i> CSV</a>
                                        <a class="dropdown-item" href="#" id="export-to-xlsx"><i class="fas fa-file-excel scale2"></i> Excel</a>
                                        <a class="dropdown-item" href="#" id="export-to-pdf"><i class="fas fa-file-pdf scale2"></i> PDF</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-0" style="overflow-x: scroll;">
                            <?php
                            $get_all_subscribers = $conn->prepare("SELECT * FROM users AS u INNER JOIN subscriptions AS s ON u.user__id = s.subscription_id WHERE (s.user_plan > 1 AND s.user_plan < 5) AND subscription_renewal BETWEEN :date_init AND :date_end ORDER BY s.subscription_start DESC");
                            # $get_all_subscribers->bindParam(':offset', $offset, PDO::PARAM_INT);
                            $get_all_subscribers->execute(array('date_init' => $date_init, 'date_end' => $date_end));
                            $received = $get_all_subscribers->rowCount();

                            if ($get_all_subscribers->rowCount() != 0) {
                            ?>
                                <table id="older-subscribers" class="table card-table display dataTablesCard dataTable no-footer" role="grid" aria-describedby="older-subscribers_info" data-order='[[5, "desc"]]'>
                                    <thead>
                                        <tr role="row">
                                            <th class="sorting col-md-4" >Assinante</th>
                                            <th class="sorting col-md-3" >Recorrências</th>
                                            <th class="sorting col-md-2" >Plano Atual</th>
                                            <th class="sorting col-md-2" >Vendas</th>
                                            <th class="sorting col-md-2" >Envios CDs</th>
                                            <th class="sorting col-md-2" >Lucro Proveniente</th>
                                        </tr>
                                    </thead>
                                    <tbody>	
                                    <?php
                                        while ($row = $get_all_subscribers->fetch()) {
                                            $user__id = $row['user__id'];
                                            $user_fullname = $row['full_name'];
                                            $user_code = $row['user_code'];

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

                                            # Vendas
                                            $get_sales = $conn->prepare('SELECT SUM(order_liquid_value) AS sum, COUNT(order_liquid_value) AS count FROM orders WHERE (user__id = :user__id AND order_status = 3) GROUP BY user__id');
                                            $get_sales->execute(array('user__id' => $user__id));
                                            
                                            if ($get_sales->rowCount() > 0){
                                                $get_sales = $get_sales->fetch();
                                                $sales_count = $get_sales['count'];
                                                $sales_sum = $get_sales['sum'] + $all_recurrences_sum;
                                            } else {
                                                $sales_sum = $sales_count = 0;
                                            }

                                            $cd_sends = 0;

                                    ?>
                                        <tr role="row">
                                            <td><?php echo $user_fullname . " <small>[" . $user_code . "]</small>"; ?></td>
                                            <td><?php echo $all_recurrences; ?></td>
                                            <td><?php echo $plan_string; ?></td>
                                            <td><?php echo $sales_count; ?></td>
                                            <td><?php echo "0"; ?></td>
                                            <td>
                                                <?php echo "R$ " . number_format($sales_sum, 2, ",", "."); ?>
                                                <i class="fs-12 text-muted fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Valor total em assinaturas + Lucro OP Locais + Lucro Logística"></i>
                                            </td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                    </tbody>
                                </table>
                                <?php

                                    $pages = $received > 0 ? ceil($received / 30) : 1;
        
                                    $page = min($pages, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array(
                                        'options' => array(
                                            'default'   => 1,
                                            'min_range' => 1,
                                            'max_range' => $received,
                                        ),
                                    )));

                                    if ($pages >= 2) {
                                    ?>
                                        <!-- <div class="row">
                                            <div class="col-md-12">
                                                <nav style="display: flex; justify-content: center;" class="mt-3">
                                                    <ul class="pagination pagination-sm pagination-circle">
                                                        <?php
                                                        // PAGINAÇÃO SEM FILTRO FILTRO
                                                        if ($page > 1) { ?>
                                                                <li class="page-item page-indicator">
                                                                    <a title="Página Anterior" class="page-link" href="<?php echo SERVER_URI . "/produtos/solicitacoes/?page=" . @($page - 1); ?>">
                                                                        <i class="fa fa-chevron-left"></i></a>
                                                                </li>
                                                        <?php } 
                                                        $p = 1;
                                                        while ($p <= $pages) { ?>
                                                            <li class="page-item <?php if ($p == $page) { echo 'active'; } ?>">
                                                                <a title="Ir para a página <?php echo $p; ?>" class="page-link" href="<?php echo SERVER_URI . "/produtos/solicitacoes/?page=" . $p; ?>"><?php echo $p; ?></a>
                                                            </li>
                                                        <?php
                                                            $p = $p + 1;
                                                        }
                                                        if ($page < $pages) { ?>
                                                            <li class="page-item page-indicator">
                                                                <a title="Próxima Página" class="page-link" href="<?php echo SERVER_URI . "/produtos/solicitacoes/?page=" . @($page + 1); ?>">
                                                                    <i class="fa fa-chevron-right"></i>
                                                                </a>
                                                            </li>
                                                        <?php } 
                                                        ?> 
                                                    </ul>
                                                </nav>
                                            </div>
                                        </div> -->
                                    <?php
                                    }
                                } else {
                                    ?>
                                    <h5 class="text-center text-muted pt-2 pb-5">Nenhum assinante novo ou recorrente no período.</h5>
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
                                        <a href="<?php echo SERVER_URI; ?>/assinaturas/dashboard/" class="btn btn-block">Limpar Filtros</a>
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