<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');

session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}


if ($_SESSION['UserPlan'] == 6) {
    header('Location: ' . SERVER_URI . '/pedidos/dashboard-operador');
    exit;
}

$user__id = $_SESSION['UserID'];

$page_title = "Dashboard | Logzz";
$sidebar_expanded = false;
$select_datatable_page = true;
$orders_page = true;

require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

$default_period = 7; //date('t');
if (isset($_GET['data-final']) && !(empty($_GET['data-final'])) || isset($_GET['data-inicio']) && !(empty($_GET['data-inicio']))) {
    if (isset($_GET['data-final']) && !(empty($_GET['data-final']))) {
        $date_formated = pickerDateFormate($_GET['data-final']);
        $date_end = date('Y-m-d', strtotime("+1 days", strtotime($date_formated)));
    } else {
        $date_end = date('Y-m-d', strtotime("+1 days", strtotime(date('Y-m-d'))));
    }

    if (isset($_GET['data-inicio']) && !(empty($_GET['data-inicio']))) {

        $date_formated = pickerDateFormate($_GET['data-inicio']);
        $date_init = date('Y-m-d', strtotime($date_formated));
    } else {
        $date_init = date('Y-m-d', strtotime(date('Y-m-d') . '-' . $default_period . 'days'));
    }
} else {
    $date_end = date('Y-m-d', strtotime('+1 day'));
    $date_init = date('Y-m-d', strtotime($date_end . '-' . $default_period . 'days'));
}

# Sem filtros
require_once(dirname(__FILE__) . '/../../includes/filters/filters-op-local.php');

$verifyIfFiltersIsActive = !empty($_GET['data-inicio']) || !empty($_GET['data-final']) || !empty($_GET['produto']) || !empty($_GET['status']) || !empty($_GET['locale']) || !empty($_GET['oplogistico']) || !empty($_GET['users']);

// tipo de label para graficos de dias da semana caso possua filtro
$status = isset($_GET['status']) && !empty($_GET['status']) ? $_GET['status'] - 1 : '';

switch ($status) {
    case 0:
        $status_string = "Agendamentos Feitos";
        $status_string_lbl_user = "Agendamentos";
        $status_string2 = "Quantidade de agendamentos";
        $status_string_lbl = "Pedidos agendados";
        $status_string_lbl2 = "Produtos agendados";
        break;
    case 1:
        $status_string = "Reagendamentos Feitos";
        $status_string_lbl_user = "Reagendamentos";
        $status_string2 = "Quantidade de reagendamentos";
        $status_string_lbl = "Pedidos reagendados";
        $status_string_lbl2 = "Produtos reagendados";
        break;
    case 2:
        $status_string = "Atrasamentos Feitos";
        $status_string_lbl_user = "Atrasos";
        $status_string2 = "Quantidade de atrasos";
        $status_string_lbl = "Pedidos atrasados";
        $status_string_lbl2 = "Produtos atrasados";
        break;
    case 3:
        $status_string = "Pedidos Completos";
        $status_string_lbl_user = "Vendas";
        $status_string2 = "Quantidade de vendas";
        $status_string_lbl = "Pedidos enviados";
        $status_string_lbl2 = "Produtos entregues";
        break;
    case 4:
        $status_string = "Frustramentos Feitos";
        $status_string_lbl_user = "Frustramentos";
        $status_string2 = "Quantidade de frustramentos";
        $status_string_lbl = "Pedidos frustrados";
        $status_string_lbl2 = "Produtos frustrados";
        break;
    case 5:
        $status_string = "Cancelamentos Feitos";
        
        $status_string_lbl_user = "Cancelamentos";
        $status_string2 = "Quantidade de cancelamentos Feitos";
        $status_string_lbl = "Pedidos cancelados";
        $status_string_lbl2 = "Produtos cancelados";
        break;
    case 6:
        $status_string = "Reembolsos Feitos";
        $status_string_lbl_user = "Reembolsos";
        $status_string2 = "Quantidade de reembolsos Feitos";
        $status_string_lbl = "Pedidos reembolsados";
        $status_string_lbl2 = "Produtos reembolsados";
        break;
    case 9:
        $status_string = "Reembolsos Feitos";
        $status_string_lbl_user = "Reembolsos";
        $status_string2 = "Quantidade de reembolsos Feitos";
        $status_string_lbl = "Pedidos reembolsados";
        $status_string_lbl2 = "Produtos reembolsados";
        break;
    case 10:
        $status_string = "Pedidos Confirmados";
        $status_string_lbl_user = "Confirmados";
        $status_string2 = "Quantidade de Confirmados Feitos";
        $status_string_lbl = "Pedidos Confirmados";
        $status_string_lbl2 = "Produtos Confirmados"; 
        break;
    case 11:
        $status_string = "Pedidos Expirados";
        $status_string_lbl_user = "Expirados";
        $status_string2 = "Quantidade de expirados Feitos";
        $status_string_lbl = "Pedidos expirados";
        $status_string_lbl2 = "Produtos expirados";
        break;
    case 11:
        $status_string = "Pedidos Indisponíveis";
        $status_string_lbl_user = "Indisponíveis";
        $status_string2 = "Quantidade de indisponíveis Feitos";
        $status_string_lbl = "Pedidos indisponíveis";
        $status_string_lbl2 = "Produtos indisponíveis";
        break;
    default:
    
        $status_string_lbl_user = "Vendas";
        $status_string_lbl2 = "Produtos entregues";
        $status_string2 = "Quantidade de vendas";
        $status_string = "Agendamentos Feitos";
        $status_string_lbl = "Agendamentos";
}


$dashboard_charts = array(
    'money-payments' => $a,
    'debit-payments' => $c,
    'credit-payments' => $b,
    'pix-payments' => $d,
    'products-name' => $products_name_list,
    'sale-quantity' => $total_sales_by_product,
    'sale-percent' => $percent,
    'agendada'   =>  @$sales0['Q'],
    'reagendada'   =>  @$sales1['Q'],
    'atrasada'   =>  @$sales2['Q'],
    'completa'   =>  @$sales3['Q'],
    'frustrada'    =>  @$sales4['Q'],
    'cancelada'    =>  @$sales5['Q'],
    'reembolsado'    =>  @$sales9['Q'], 
    'confirmado'    =>  @$sales10['Q'],
    'emaberto'    =>  @$sales11['Q'],
    'indisponivel'    =>  @$sales12['Q'],
);

$get_locales = $conn->prepare('SELECT * FROM local_operations WHERE operation_active = 1');
$get_locales->execute();
$locales = $get_locales->fetchAll(\PDO::FETCH_ASSOC);

?>
<style>
    .filtersList2{
        overflow-y: auto;
    }

    .filtersList2::-webkit-scrollbar {
        background-color: #EEEEEE;
        width: 7px;
        height: 7px;
    }

    .filtersList2::-webkit-scrollbar-track {
        border-radius: 40px;
        background-color: #F5F5F5;
    }

    .filtersList2::-webkit-scrollbar-thumb {
        border-radius: 40px;
        background-color: #2fde91;
    }
</style>
<div class="container-fluid">
    <?php
    if (($vendas[0] == 0 || $vendas[0] == null) && !(isset($_GET['filtro']))) {
        # Mensagem genérica caso todos os dados do gráfico estejam zerados.
    ?>
        <div class="alert alert-success solid fade show mb-3">
            <i class="fa fa-info-circle" aria-hidden="true"></i><strong> Você ainda não fez a sua primeira venda.</strong> Assim que ela acontecer, todos os dados aparecerão aqui.
        </div>
    <?php
    }
    if ($verifyIfFiltersIsActive) {
        $breadcumb = "Filtros Ativos:&nbsp; ";

        if (!(empty(@$_GET['data-inicio']))) {
            $breadcumb .= '<span class="badge badge-success light">Data: <b>' . date("d/m", strtotime($date_init)) . '</b> a <b>' . date("d/m", strtotime("-1 days", strtotime($date_end))) . '</b></span>';
        }

        if (!(empty(@$_GET['produto']))) {

            if (preg_match("/,/", $_GET['produto'])) {

                $filter_by_product = addslashes($_GET['produto']);

                $get_product_name = $conn->prepare('SELECT product_name FROM products WHERE product_id = :product_id LIMIT 1');
                $get_product_name->execute(array('product_id' => $filter_by_product));

                if ($get_product_name->rowCount() > 0) {
                    $name_string = $get_product_name->fetch();

                    if (strlen($name_string['product_name']) > 20) {
                        $name_string_ = substr($name_string['product_name'], 0, 20) . "...";
                    } else {
                        $name_string_ = $name_string['product_name'];
                    }

                    $breadcumb .= '<span class="badge badge-success light">Produto: <b>' . $name_string_ . '</b></span>';
                }
            } else {

                $get_product_name = $conn->prepare('SELECT product_name FROM products WHERE product_id = :product_id LIMIT 1');
                $get_product_name->execute(array('product_id' => $_GET['produto']));

                $name_string = $get_product_name->fetch();
                $name_string_ = $name_string[0];

                $breadcumb .= '<span class="badge badge-success light">Produto: <b>' . $name_string_ . '</b></span>';
            }
        }

        if (!(empty(@$_GET['status']))) {
            switch ($_GET['status']) {
                case 0:
                    $status = 'Agendado';
                    break;
                case 1:
                    $status = 'Reagendado';
                    break;
                case 2:
                    $status = 'Atrasado';
                    break;
                case 3:
                    $status = 'Completo';
                    break;
                case 4:
                    $status = 'Frustrado';
                    break;
                case 5:
                    $status = 'Cancelado';
                    break;
                case 9:
                    $status = 'Reembolsado';
                    break;
                case 10:
                    $status = 'Confirmado';
                    break;
                case 11:
                    $status = 'Em aberto';
                    break;
                case 12:
                    $status = 'Indisponível';
                    break;
                default:
                    $status = 'Todos';
                    break;
            }
            $breadcumb .= '<span class="badge badge-success light">Status: <b>' . $status . '</b></span>';
            $status_str = $status;
        }
        
        if (!(empty(@$_GET['locale']))) {
            $get_locale_name = $conn->prepare('SELECT operation_name FROM local_operations WHERE operation_id = :operation_id LIMIT 1');
            $get_locale_name->execute(array('operation_id' => $_GET['locale']));

            $name_string = $get_locale_name->fetch();
            $name_string_ = $name_string[0];

            $breadcumb .= '<span class="badge badge-success light">Operação Local: <b>' . $name_string_ . '</b></span>';
        }
        if (!(empty(@$_GET['users']))) {
            $get_locale_name = $conn->prepare('SELECT full_name FROM users WHERE user__id = :user__id LIMIT 1');
            $get_locale_name->execute(array('user__id' => $_GET['users']));

            $name_string = $get_locale_name->fetch();
            $name_string_ = $name_string[0];

            $breadcumb .= '<span class="badge badge-success light">Usuário: <b>' . $name_string_ . '</b></span>';
        }
        if (!(empty(@$_GET['oplogistico']))) {
            $get_locale_name = $conn->prepare('SELECT u.*, lo.operator_id FROM logistic_operator lo INNER JOIN users u ON u.user__id = lo.user_id INNER JOIN local_operations_orders AS loo WHERE loo.responsible_id = lo.operator_id AND lo.operator_id = :op_id LIMIT 1');
            $get_locale_name->execute(array('op_id' => $_GET['oplogistico']));

            $name_string = $get_locale_name->fetch();
            $name_string_ = $name_string['full_name'];

            $breadcumb .= '<span class="badge badge-success light">Operador Logístico: <b>' . $name_string_ . '</b></span>';
        }
    }
    ?>

    <div class="row">

        <div class="col-xl-12 col-xxl-12 ">
            <div class="mb-3 mr-3">
                <h6 class="fs-14 text-muted mb-0"><?php echo @$breadcumb; ?></h6>
            </div>
            <div class="row dashboard-numbers">
                <div class="<?= $_SESSION['UserPlan'] == 5 ? 'col-xl-6 col-xxl-6 col-lg-6 col-sm-6' : 'col-xl-4 col-xxl-4 col-lg-4 col-sm-4' ?> dashboard-number-col">
                    <?php if ($_SESSION['UserPlan'] == 5) : ?>
                        <div class="card">
                            <div class="card-body card-dashboard">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <i class="fas fa-money-bill-wave"></i>
                                        <p class="fs-12 mb-1 d-inline">Receita Total
                                            <span class="ml-1" style="cursor:pointer;" data-toggle="tooltip" data-placement="top" title='Todo valor faturado de todos os usuários'>
                                                <i class="fas fa-info-circle" style="color:#ccc; font-size:14px;"></i>
                                            </span>
                                        </p>
                                        <span class="fs-20 font-w700 d-block"><small>R$ </small> <?php echo number_format($faturamento, 2, ',', '.'); ?>
                                    </div>
                                    <div>
                                        <i class="fas fa-donate"></i>
                                        <p class="fs-12 mb-1 d-inline">Despesas Plataforma
                                            <span class="ml-1" style="cursor:pointer;" data-toggle="tooltip" data-placement="top" title='Comissões de usuários [produtor, coprodutor e afiliado], comissão operador logístico; taxa financeira de cartão [do cadastro do operador]'>
                                                <i class="fas fa-info-circle" style="color:#ccc; font-size:14px;"></i>
                                            </span>
                                        </p>
                                        <span class="fs-20 font-w700 d-block"><small>R$ </small><?php echo number_format($comissao, 2, ',', '.'); ?> <small>(<?php echo number_format($comissao_por100, 2, ',', ''); ?>%)</small></span>
                                    </div>
                                    <div>
                                        <i class="fas fa-donate"></i>
                                        <p class="fs-12 mb-1 d-inline">Lucro Plataforma
                                            <span class="ml-1" style="cursor:pointer;" data-toggle="tooltip" data-placement="top" title='Receita - Despesas'>
                                                <i class="fas fa-info-circle" style="color:#ccc; font-size:14px;"></i>
                                            </span>
                                        </p>
                                        <span class="fs-20 font-w700 d-block"><small> </small>  <?php echo 'R$ '. number_format($faturamento - $comissao, 2, ',', '.'); ?> </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="card">
                            <div class="card-body card-dashboard">
                                <div class="d-flex justify-content-between">

                                    <div>
                                        <i class="fas fa-money-bill-wave"></i>
                                        <p class="fs-12 mb-1 d-inline">Faturamento
                                            <span class="ml-1" style="cursor:pointer;" data-toggle="tooltip" data-placement="top" title="Todo o valor somado de pedidos com status <?= !empty($_GET['status']) ? $status : 'Completo' ?>">
                                                <i class="fas fa-info-circle" style="color:#ccc; font-size:14px;"></i>
                                            </span>
                                        </p>
                                        <span class="fs-20 font-w700 d-block"><small>R$ </small> <?php echo number_format($faturamento, 2, ',', '.'); ?></span> 
                                    </div>
                                    <div>
                                        <i class="fas fa-donate"></i>
                                        <p class="fs-12 mb-1 d-inline">Comissão
                                            <span class="ml-1" style="cursor:pointer;" data-toggle="tooltip" data-placement="top" title='Montante disponibilizado para saque: faturamento - taxas financeiras, logísticas e comissões de coprodutores e afiliados'>
                                                <i class="fas fa-info-circle" style="color:#ccc; font-size:14px;"></i>
                                            </span>
                                        </p>
                                        <span class="fs-20 font-w700 d-block"><small>R$ </small><?php echo number_format($comissao, 2, ',', '.'); ?> <small>(<?php echo number_format($comissao_por100, 2, ',', ''); ?>%)</small></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>

                <div class="col-lg-2 col-sm-6 dashboard-number-col">
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

                <div class="col-lg-2 col-sm-6 dashboard-number-col">
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

                <div class="<?= $_SESSION['UserPlan'] == 5 ? 'col-lg-2' : 'col-lg-4' ?> col-sm-6 dashboard-number-col">
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

                <!-- <div class="col-lg-2 col-sm-6 dashboard-number-col">
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
                </div> -->


                <div class="col-xl-4 col-xxl-12 col-lg-4">
                    <div class="card">
                        <div class="card-header align-items-start pb-0 border-0">
                            <div>
                                <h4 class="fs-18 mb-0 text-black font-w600">Volume de Vendas</h4>
                                <span class="fs-12">
                                    <?php
                                    if ((!(isset($_GET['data-inicio'])) || empty($_GET['data-inicio'])) && (!(isset($_GET['data-final'])) || empty($_GET['data-final']))) {
                                        echo "Nos ú1ltimos " . $default_period . " dias.";
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="d-flex mb-3">
                                <button type="button" class="btn btn-rounded btn-success filter-btn" class="btn btn-success text-nowrap"><i class="fas fa-sliders-h scale5 mr-3" aria-hidden="true"></i>Filtros</button>
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
                                <?php if ($verifyIfFiltersIsActive) : ?>
                                    <span class="fs-12 mt-1">(De acordo com os seus filtros)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <?php if ($haveDataFromGraphsSales == 0): ?>
                                <label class="text-label mt-2">Não há dados suficientes para este gráfico.</label>
                                <canvas id="salesStatusChart" height="250" class="d-none"></canvas>
                            <?php else: ?>
                                <canvas id="salesStatusChart" height="250"></canvas>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-xxl-6 col-lg-4">
                    <?php if (isset($_GET['status']) && !empty($_GET['status'])) : ?>
                        <?php if($_GET['status'] - 1 == 3): ?>
                            <div class="card">
                                <div class="card-header align-items-start pb-0 border-0">
                                    <div>
                                        <h4 class="fs-18 mb-0 text-black font-w600">Vendas por Forma de Pagamento</h4>
                                        <!-- <?php if (isset($_GET['status']) && $_GET['status'] != "") { ?><span class="fs-12">Filtro por Status não se aplica a esse gráfico.</span><?php } else { ?><span class="fs-12">Pedidos Completos no período.</span><?php } ?> -->
                                    </div>
                                </div>
                                <div class="card-body pt-0">
                                    <?php
                                    if (@$a == 0 && @$b == 0 && @$c == 0 && @$d == 0) {
                                        echo '<label class="text-label mt-2">Não há dados suficientes para este gráfico.</label>';
                                    } else {
                                    ?>
                                        <canvas id="salesPayMethodChart" height="200"></canvas>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="card">
                                <div class="card-header align-items-start pb-0 border-0">
                                    <div>
                                        <h4 class="fs-18 mb-0 text-black font-w600">Vendas por Forma de Pagamento</h4>
                                        <!-- <?php if (isset($_GET['status']) && $_GET['status'] != "") { ?><span class="fs-12">Filtro por Status não se aplica a esse gráfico.</span><?php } else { ?><span class="fs-12">Pedidos Completos no período.</span><?php } ?> -->
                                    </div>
                                </div>
                                <div class="card-body">
                                    <span class="fs-12">(Filtro por status <?= $status_str ?> não se aplica a esse gráfico)</span>
                                </div>
                            </div>

                        <?php endif; ?>
                    <?php else : ?>
                        <div class="card">
                            <div class="card-header align-items-start pb-0 border-0">
                                <div>
                                    <h4 class="fs-18 mb-0 text-black font-w600">Vendas por Forma de Pagamento</h4>
                                    <!-- <?php if (isset($_GET['status']) && $_GET['status'] != "") { ?><span class="fs-12">Filtro por Status não se aplica a esse gráfico.</span><?php } else { ?><span class="fs-12">Pedidos Completos no período.</span><?php } ?> -->
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <?php
                                if (@$a == 0 && @$b == 0 && @$c == 0 && @$d == 0) {
                                    echo '<label class="text-label mt-2">Não há dados suficientes para este gráfico.</label>';
                                } else {
                                ?>
                                    <canvas id="salesPayMethodChart" height="200"></canvas>
                                <?php
                                }
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if ($_SESSION['UserPlan'] == 5) : ?>
                    <?php $sum_all = 0 ?>
                    <?php foreach ($total_percent_and_values as $comissions) : ?>
                        <?php @$sum_all += $comissions['value']; ?>
                    <?php endforeach; ?>

                    <div class="col-xl-5 col-xxl-6 col-lg-5">
                        <div class="card">
                            <div class="card-header align-items-start pb-0 border-0">
                                <div>
                                    <h4 class="fs-18 mb-0 text-black font-w600">Controle de despesas</h4>
                                    <?php if ($verifyIfFiltersIsActive) : ?>
                                        <span class="fs-12 mt-1">(De acordo com os seus filtros)</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if($sum_all > 0): ?>
                                <div class="card-body pt-5">
                                    <div class="row">
                                        <div class="col-sm-12 mb-2">
                                            <div id="All" class="tab-pane active fade show">
                                                <div class="table-responsive" style="overflow-x: visible;">
                                                    <table id="orders-list2" class="table card-table dataTablesCard" data-page-length='30' data-order='[[0, "desc"]]'>
                                                        <thead>
                                                            <tr>
                                                                <th>Descrição</th>
                                                                <th>Valor</th>
                                                                <th>%</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($total_percent_and_values as $comissions) : ?>
                                                                <tr>
                                                                    <td><?= $comissions['description']; ?></td>
                                                                    <td>R$ <?= $comissions['value'] ?></td>
                                                                    <td><?= $comissions['percent'] ?> %</td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="card-body pt-0">
                                    <label class="text-label mt-2">Não há dados suficientes para este gráfico.</label>
                                </div>      
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="<?= $_SESSION['UserPlan'] == 5 ? 'col-xl-7 col-xxl-6 col-lg-7' : 'col-xl-12 col-xxl-12 col-lg-12' ?>">
                    <div class="card">
                        <?php if (isset($_GET['status']) && !empty($_GET['status'])) : ?>
                            <?php if($_GET['status'] - 1 == 3): ?>
                                <div class="card-header d-flex flex-column align-items-start">
                                    <h4 class="fs-18 mb-0 text-black font-w600">Produtos mais vendidos</h4>
                                    <?php if ($verifyIfFiltersIsActive) : ?>
                                        <span class="fs-12 mt-1">(De acordo com os seus filtros)</span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-sm-12 mb-2">
                                            <div id="All" class="tab-pane active fade show">
                                                <div class="table-responsive" style="overflow-x: auto;">
                                                    <table id="orders-list" class="table card-table dataTablesCard" data-page-length='5' data-order='[[0, "desc"]]'>
                                                        <thead>
                                                            <tr>
                                                                <th>Nome</th>
                                                                <th>Vendas</th>
                                                                <th>Produtos</th>
                                                                <th>%</th>
                                                                <th>Faturamento
                                                                    <span class="ml-1" style="cursor:pointer;" data-toggle="tooltip" data-placement="top" title='<?= $_SESSION['UserPlan'] == 5 ? "total faturado no período selecionado no filtro geral da tela" : "total faturado (status completo) no período selecionado no filtro geral da tela"?>'>
                                                                        <i class="fas fa-info-circle" style="color:#ccc; font-size:14px;"></i>
                                                                    </span>
                                                                </th>
                                                                <th>Afiliado
                                                                    <span class="ml-1" style="cursor:pointer;" data-toggle="tooltip" data-placement="top" title='total de comissões distribuídas no período selecionado no filtro geral da tela'>
                                                                        <i class="fas fa-info-circle" style="color:#ccc; font-size:14px;"></i>
                                                                    </span>
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($assoc_arr as $product) : ?>
                                                                <tr>
                                                                    <td><a href="<?php echo SERVER_URI . "produtos/todos/" . $product['products_id'] ?>" target="_blank" class="text-black"><?php echo $product['product_name'] . " "."[". $product['product_code']  ."]"; ?></a></td>
                                                                    <td><?php echo $product['sales']; ?></td>
                                                                    <td><?php echo $product['products']; ?></td>
                                                                    <td><?php echo $product['percent']; ?></td>
                                                                    <td><?php echo number_format($product['invoicing'], 2, ',', '.'); ?></td>
                                                                    <td><?php echo number_format($product['afiliate'], 2, ',', '.'); ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="card-header align-items-start pb-0 border-0">
                                    <div>
                                        <h4 class="fs-18 mb-0 text-black font-w600">Produtos mais vendidos</h4>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <span class="fs-12">(Filtro por Status <?= $status_str ?> não se aplica a esse gráfico)</span>
                                </div>
                            <?php endif; ?>
                        <?php else : ?>
                            <div class="card-header d-flex flex-column align-items-start">
                                <h4 class="fs-18 mb-0 text-black font-w600">Produtos mais vendidos</h4>
                                <?php if ($verifyIfFiltersIsActive) : ?>
                                    <span class="fs-12 mt-1">(De acordo com os seus filtros)</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-12 mb-2">
                                        <div id="All" class="tab-pane active fade show">
                                            <div class="table-responsive" style="overflow-x: auto;">
                                                <table id="orders-list" class="table card-table dataTablesCard" data-page-length='5' data-order='[[0, "desc"]]'>
                                                    <thead>
                                                        <tr>
                                                            <th>Nome</th>
                                                            <th>Vendas</th>
                                                            <th>Produtos</th>
                                                            <th>%</th>
                                                            <th>Faturamento
                                                                <span class="ml-1" style="cursor:pointer;" data-toggle="tooltip" data-placement="top" title="<?= $_SESSION['UserPlan'] == 5 ? "total faturado no período selecionado no filtro geral da tela" : "total faturado (status completo) no período selecionado no filtro geral da tela"?>">
                                                                    <i class="fas fa-info-circle" style="color:#ccc; font-size:14px;"></i>
                                                                </span>
                                                            </th>
                                                            <th>Afiliados
                                                                <span class="ml-1" style="cursor:pointer;" data-toggle="tooltip" data-placement="top" title='Total de comissões distribuídas no período selecionado no filtro geral da tela'>
                                                                    <i class="fas fa-info-circle" style="color:#ccc; font-size:14px;"></i>
                                                                </span>
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($assoc_arr as $product) : ?>
                                                            <tr>
                                                                <td><a href="<?php echo SERVER_URI . "/produtos/todos/" . $product['products_id'] ?>" target="_blank" class="text-black"><?php echo $product['product_name'] . " ". "[". "<span class='fs-12 mt-1'>".$product['product_code']  ."</span>"  ."]"; ?></a></td>
                                                                <td><?php echo $product['sales']; ?></td>
                                                                <td><?php echo $product['products']; ?></td>
                                                                <td><?php echo $product['percent']; ?></td>
                                                                <td><?php echo number_format($product['invoicing'], 2, ',', '.'); ?></td>
                                                                <td><?php echo number_format($product['afiliate'], 2, ',', '.'); ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>


            <div class="col-xl-12 col-xxl-12 col-lg-12">
                <div class="card">
                    <div class="card-header align-items-start pb-0 border-0">
                        <div>
                            <h4 class="fs-18 mb-0 text-black font-w600"><?= $_SESSION['UserPlan'] == 5 ? $status_string_lbl2 : $status_string_lbl_user . " " ?> por operação local</h4>

                            <?php if ($verifyIfFiltersIsActive) : ?>
                                <span class="fs-12">(De acordo com os seus filtros)</span>
                            <?php endif; ?>

                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <?php
                        if (@$vendas[0] == 0) {
                            echo '<label class="text-label mt-2">Não há dados suficientes para este gráfico.</label>';
                        } else {
                        ?>
                            <canvas id="salesByOperation" height="100"></canvas>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="col-xl-12 col-xxl-12 col-lg-12">
                <div class="card">
                    <div class="card-header align-items-start pb-0 border-0">
                        <div>
                            <h4 class="fs-18 mb-0 text-black font-w600"><?= $status_string_lbl . " " ?> por dia da semana</h4>
                            <?php if ($verifyIfFiltersIsActive): ?>
                                <span class="fs-12">(De acordo com os seus filtros)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <?php if ($haveDataFromDaysOfWeek == 0) : ?>
                            <?= '<label class="text-label mt-2">Não há dados suficientes para este gráfico.</label>'; ?>
                            <canvas id="daysOfWeek" height="100" class="d-none"></canvas>
                        <?php else: ?>
                            <canvas id="daysOfWeek" height="100"></canvas>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
</div>

<div class="chatbox">
    <div class="chatbox-close"></div>
    <div class="col-xl-12 ">
        <div class="card" style="height: 100vh;">
            <div class="mt-4 center text-center ">
                <h4 class="card-title">Filtros</h4>
            </div> 
            <div class="card-body filtersList2">
                <div id="smartwizard" class="form-wizard order-create" >
                    <div class="row" >
                        <div class="col-lg-12 mb-2">
                            <form id="ShopFilter" action="" method="GET">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <input type="hidden" class="form-control mb-2" name="filtro" value="ativo">
                                        <p class="mb-1"><small>por Data</small></p>
                                        <input type="hidden" class="form-control mb-2" name="filtro" value="ativo">
                                        <input name="data-inicio" value="<?php echo @addslashes($_GET['data-inicio']); ?>" placeholder="Do dia ..." class="datepicker-default form-control picker__input mb-2" id="datepicker" readonly="" aria-haspopup="true" aria-expanded="false" aria-readonly="false" aria-owns="datepicker_root">
                                        <input name="data-final" value="<?php echo @addslashes($_GET['data-final']); ?>" placeholder=".. ao dia" class="datepicker-default form-control picker__input" id="datepicker" readonly="" aria-haspopup="true" aria-expanded="false" aria-readonly="false" aria-owns="datepicker_root">

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
                                                <label class="text-label"><small>por Produto</small></label>
                                                <select id="select-ship-product" class="d-block default-select" data-live-search="true">
                                                    <option id="all-products-option" value="" selected>Todos os Produtos</option>
                                                    <?php if ($_SESSION['UserPlan'] == 5) : ?>
                                                        <?php
                                                        $query = $conn->prepare("SELECT p.product_id, p.product_name, p.product_code FROM products AS p WHERE p.product_trash = 0");
                                                        $query->execute();

                                                        $allProducts = $query->fetchAll(\PDO::FETCH_ASSOC);
                                                        ?>
                                                    <?php else : ?>
                                                        <?php
                                                        $query = $conn->prepare("SELECT p.product_id, p.product_name, p.product_code FROM products AS p WHERE p.user__id = :user__id AND p.product_trash = 0");
                                                        $query->execute(['user__id' => $user__id]);

                                                        $allProducts = $query->fetchAll(\PDO::FETCH_ASSOC);
                                                        ?>
                                                    <?php endif; ?>


                                                    <?php foreach ($allProducts as $product) : ?>
                                                        <option id="" value="<?= $product['product_id'] ?>" <?php if (!(empty(@$_GET['produto'])) && $_GET['produto'] == $product['product_id']) echo "selected" ?>>
                                                            <?php if (strlen($product['product_name']) > 20) {
                                                                echo substr($product['product_name'], 0, 20) . "...";
                                                            } else {
                                                                echo $product['product_name'];
                                                            }
                                                            echo " <small>[" . $product['product_code'] . "]</small>";
                                                            ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <input type="hidden" id="text-ship-product" name="produto" value="<?php echo @$_GET['produto']; ?>" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <p style="margin-bottom: 0.5rem;"><small>por Status</small></p>
                                        <select id="select-filter-status-id" class="d-block default-select">
                                            <option id="all-status-option" value="" selected>Todos os Status</option>
                                            <option <?php if (@$_GET['status'] == '1') {
                                                        echo "selected";
                                                    } ?> value="1">Agendada</option>
                                            <option <?php if (@$_GET['status'] == '3') {
                                                        echo "selected"; 
                                                    } ?> value="3">Atrasada</option>
                                            <option <?php if (@$_GET['status'] == '6') {
                                                        echo "selected";
                                                    } ?> value="6">Cancelada</option>
                                            <option <?php if (@$_GET['status'] == '5') {
                                                        echo "selected";
                                                    } ?> value="5">Frustrada</option>
                                            <option <?php if (@$_GET['status'] == '4') {
                                                        echo "selected";
                                                    } ?> value="4">Completa</option>
                                            <option <?php if (@$_GET['status'] == '2') {
                                                        echo "selected";
                                                    } ?> value="2">Reagendada</option>
                                            <option <?php if (@$_GET['status'] == '10') {
                                                    echo "selected";
                                                    } ?> value="10">Reembolsado</option>
                                            <option <?php if (@$_GET['status'] == '11') {
                                                    echo "selected";
                                                    } ?> value="11">Confirmado</option>
                                            <option <?php if (@$_GET['status'] == '12') {
                                                    echo "selected";
                                                    } ?> value="12">Em aberto</option>
                                            <option <?php if (@$_GET['status'] == '13') {
                                                    echo "selected";
                                                    } ?> value="13">Indisponível</option>
                                        </select>
                                        <input type="hidden" id="text-filter-status-id" name="status" value="<?php echo @$_GET['status']; ?>" required>
                                    </div>

                                    <div class="col-lg-12 mt-3">
                                        <div class="example">
                                            <div class="form-group">
                                                <label class="text-label"><small>por Operação Local</small></label>
                                                <select id="select-locale-center" name="locale" class="d-block default-select" required>
                                                    <option disabled selected>Selecione a localidade</option>
                                                    <?php foreach ($locales as $locale) : ?>
                                                        <option value="<?= $locale['operation_id'] ?>" <?php echo !empty($_GET['locale']) && $_GET['locale'] == $locale['operation_id'] ? 'selected' : '' ?>> <?= $locale['operation_name']; ?> </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if ($_SESSION['UserPlan'] == 5) : ?>
                                        <div class="col-lg-12 mt-3">
                                            <div class="example">
                                                <div class="form-group">
                                                    <label class="text-label"><small>por Usuário</small></label>
                                                    <select id="select-locale-center" name="users" class="d-block default-select" data-live-search="true">
                                                        <?php
                                                        $query = $conn->prepare("SELECT user__id, full_name, user_code FROM users AS u WHERE u.active = 1");
                                                        $query->execute();

                                                        $users = $query->fetchAll(\PDO::FETCH_ASSOC);
                                                        ?>
                                                        <option disabled <?php echo empty($_GET['users']) ? 'selected' : '' ?>>Selecione o Usuário</option>
                                                        <?php foreach ($users as $user) : ?>
                                                            <option 
                                                                value="<?= $user['user__id'] ?>"
                                                                <?php echo !empty($_GET['users']) && $_GET['users'] == $user['user__id'] ? 'selected' : '' ?>
                                                            > 
                                                            <?= strlen($user['full_name']) > 25 ? substr($user['full_name'], 0, 25) . "..." : $user['full_name'] ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-12 mt-3">
                                            <div class="example">
                                                <div class="form-group">
                                                    <label class="text-label"><small>por Operador Logístico</small></label>
                                                    <select id="select-operators" name="oplogistico" class="d-block default-select" data-live-search="true">
                                                        <option id="" value="" selected>Todos</option>
                                                        <?php
                                                        $get_logistic_operators = $conn->prepare("SELECT * FROM logistic_operator");
                                                        $get_logistic_operators->execute();

                                                        while ($operator = $get_logistic_operators->fetch()) {
                                                            echo "<option value='" . $operator["operator_id"] . "'>" . $operator["full_name"] . "</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                    <?php endif; ?>
                                    <div class="col-lg-12 mt-4">
                                        <button type="submit" id="SubmitButton" class="btn btn-block btn-success"><i class="fas fa-sliders-h scale2 mr-2" aria-hidden="true"></i>Filtrar</button>
                                        <a href="<?php echo SERVER_URI; ?>/pedidos/dashboard/" class="btn btn-block">Limpar Filtros</a>
                                    </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php'); ?>
<!-- TEMPORARIO -->
<script>
    const daysOfWeek = document.getElementById("daysOfWeek").getContext('2d');
    new Chart(daysOfWeek, {
        type: 'bar',
        data: {
            labels: ["Domingo", "Segunda", "Terça", "Quarta", "Quinta", "Sexta", "Sabádo"],
            datasets: [{
                label: "<?= $status_string ?>",
                data: [<?= $sun ?>, <?= $mon ?>, <?= $tue ?>, <?= $wed ?>, <?= $thu ?>, <?= $fri ?>, <?= $sat ?>],
                backgroundColor: [
                    'rgb(47, 222, 145, 0.9)',
                    'rgb(47, 222, 145, 0.9)',
                    'rgb(47, 222, 145, 0.9)',
                    'rgb(47, 222, 145, 0.9)',
                    'rgb(47, 222, 145, 0.9)',
                    'rgb(47, 222, 145, 0.9)',
                    'rgb(47, 222, 145, 0.9)'
                ],
                borderColor: [
                    'rgb(47, 222, 145)',
                    'rgb(47, 222, 145)',
                    'rgb(47, 222, 145)',
                    'rgb(47, 222, 145)',
                    'rgb(47, 222, 145)',
                    'rgb(47, 222, 145)',
                    'rgb(47, 222, 145)',
                    'rgb(52, 58, 64)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    const salesByOperation = document.getElementById("salesByOperation").getContext('2d');
    new Chart(salesByOperation, {
        type: 'bar',
        data: {
            labels: [
                <?php
                foreach ($operations_name as $operation_name) {
                    echo "'" . $operation_name . "', ";
                }
                ?>
            ],
            datasets: [{
                label: "<?= $status_string2 ?>",
                data: [
                    <?php
                    foreach ($total_by_operation as $total) {
                        echo "'" . $total['total'] . "', ";
                    }
                    ?>
                ],
                backgroundColor: [
                    'rgb(47, 222, 145, 0.9)',
                    'rgb(47, 222, 145, 0.9)',
                    'rgb(47, 222, 145, 0.9)',
                    'rgb(47, 222, 145, 0.9)',
                    'rgb(47, 222, 145, 0.9)',
                    'rgb(47, 222, 145, 0.9)',
                    'rgb(47, 222, 145, 0.9)'
                ],
                borderColor: [
                    'rgb(47, 222, 145)',
                    'rgb(47, 222, 145)',
                    'rgb(47, 222, 145)',
                    'rgb(47, 222, 145)',
                    'rgb(47, 222, 145)',
                    'rgb(47, 222, 145)',
                    'rgb(47, 222, 145)',
                    'rgb(52, 58, 64)'
                ],
                borderWidth: 1,
                datalabels: {
                    anchor: 'end',
                    align: 'top',
                    offset: 15,
                    formatter: (val, ctx) => (ctx.chart.data.labels2[ctx.dataIndex])
                }
            }],
            labels2: [
                <?php
                foreach ($total_by_operation as $total) {
                    echo "'" . $total['percent'] . " %" . "', ";
                }
                ?>
            ]
        },
        plugins: [ChartDataLabels],
        options: {
            scales: {
                yAxes: [{
                    display: true,
                    ticks: {
                        suggestedMin: 1, // minimum will be 0, unless there is a lower value.
                        // OR // 
                        beginAtZero: true // minimum value will be 0. 
                    }
                }]
            },
            responsive: true,
            legend: false,
        }
    });
</script>