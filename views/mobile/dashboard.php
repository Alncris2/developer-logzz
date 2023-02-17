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
$status = isset($_GET['status']) && !empty($_GET['status']) ? $_GET['status'] - 1 : 7;

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
    'reembolsada'    =>  @$sales6['Q'],
);

$get_locales = $conn->prepare('SELECT * FROM local_operations WHERE operation_active = 1');
$get_locales->execute();
$locales = $get_locales->fetchAll(\PDO::FETCH_ASSOC);

?>

<style>
    .filtersList2 {
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

    .img-fluid {
        max-width: 40%;
        display: block;
        margin-left: 100px;
        margin-right: auto;
        margin-bottom: 10px;
    }

    .notification {
        padding: 0 15px;
        top: 5px;
        right: -30px;
    }

    .all-notification {
        border-top: 0px !important;
    }

    .drop-text {
        left: -10px !important;
        min-width: 10px !important;
    }
    .badge-notification {
        position: absolute !important;
        font-size: smaller;
        margin-left: -0px;
        margin-top: -10px;
        padding: 0px 8px;        
        color: white;
    }
</style>

<div class="container-fluid h-100 mt-5">

    <div class="col-12 d-md-none d-block d-flex">
        <img class="img-fluid" src="/images/logo-full.png">
        <div class="nav-item dropdown notification_dropdown notification">
            <?php  $get_notifications = $conn->prepare('SELECT * FROM `notifications` WHERE (`user__id` = :user__id OR `user__id` IS NULL) AND `notification_open` = 0 ');
            $get_notifications->execute(array('user__id' => $_SESSION['UserID']));
            $count_my_notifications =  $get_notifications->rowCount(); ?>
            
            <a class="nav-link btn btn-icon-left ai-icon sharp" href="javascript:void(0)" role="button" data-toggle="dropdown" style="padding: 0.5rem 0.50rem 0.5rem;">
                <i class="fa fa-bell fs-18 text-primary" style="font-size: 1.1rem;"><span class='badge badge-pill badge-notification badge-primary'><?= $count_my_notifications ?></span></i>
            </a>
            <div class="dropdown-menu rounded dropdown-menu-right drop-text">
                <a class="all-notification" style="text-align: right;" href="../../views/mobile/list-notification.php">Histórico</i></a>
                <a class="all-notification" style="text-align: right;" href="../../views/mobile/notification.php">Configurações</i></a>
            </div>
        </div>
    </div>

    <div class="row my-5" style="justify-content: space-between;">

        <!-- <div class="col-xl-12 col-xxl-12 ">
            <div class="mb-3 mr-3">
                <h6 class="fs-14 text-muted"><?php echo @$breadcumb; ?></h6>
            </div> -->

        <div class="col-12">


            <!--<div class="d-block d-md-none w-100 mb-3">-->
            <!--    <a class="btn btn-light bg-white btn-lg btn-block" href="/views/mobile/finances.php">-->
            <!--        <i class="fas fa-dollar-sign text-primary" style="font-size: 1.2em;"></i> &nbsp; Solicitar Saque-->
            <!--    </a>-->
            <!--</div>-->

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
                                        <span class="fs-14 font-w700"><small>R$ </small> <?php echo number_format($faturamento, 2, ',', '.'); ?>
                                    </div>

                                    <div>
                                        <i class="fas fa-donate"></i>
                                        <p class="fs-12 mb-1 d-inline">Despesas Plataforma
                                            <span class="ml-1" style="cursor:pointer;" data-toggle="tooltip" data-placement="top" title='Comissões de usuários [produtor, coprodutor e afiliado], comissão operador logístico; taxa financeira de cartão [do cadastro do operador]'>
                                                <i class="fas fa-info-circle" style="color:#ccc; font-size:14px;"></i>
                                            </span>
                                        </p>
                                        <span class="fs-14 font-w700"><small>R$ </small><?php echo number_format($comissao, 2, ',', '.'); ?> <small>(<?php echo number_format($comissao_por100, 2, ',', ''); ?>%)</small></span>
                                    </div>

                                    <div>
                                        <i class="fas fa-donate"></i>
                                        <p class="fs-12 mb-1 d-inline">Lucro Plataforma
                                            <span class="ml-1" style="cursor:pointer;" data-toggle="tooltip" data-placement="top" title='Receita - Despesas'>
                                                <i class="fas fa-info-circle" style="color:#ccc; font-size:14px;"></i>
                                            </span>
                                        </p>
                                        <span class="fs-14 font-w700 d-block"><small> </small> <?php echo 'R$ ' . number_format($faturamento - $comissao, 2, ',', '.'); ?> </span>
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
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <span class="fs-16 font-w700" id="num-fatur"><small>R$ </small> <?php echo number_format($faturamento, 2, ',', '.'); ?></span>
                                            </div>
                                            <i class="fas fa-eye-slash eye-disabled" data-id="#num-fatur" style="margin-right: 4px; padding: 5px; width: 20px;"></i>
                                        </div>
                                    </div>

                                    <div class="justify-content-between">
                                        <i class="fas fa-donate"></i>
                                        <p class="fs-12 mb-1 d-inline">Comissão
                                            <span class="ml-1" style="cursor:pointer;" data-toggle="tooltip" data-placement="top" title='Montante disponibilizado para saque: faturamento - taxas financeiras, logísticas e comissões de coprodutores e afiliados'>
                                                <i class="fas fa-info-circle" style="color:#ccc; font-size:14px;"></i>
                                            </span>
                                        </p>
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <span class="fs-16 font-w700" id="num-comiss"><small>R$ </small><?php echo number_format($comissao, 2, ',', '.'); ?> <small>(<?php echo number_format($comissao_por100, 2, ',', ''); ?>%)</small></span>
                                            </div>
                                            <i class="fas fa-eye-slash eye-disabled" data-id="#num-comiss" style="margin-right: 4px; padding: 5px; width: 20px;"></i>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>

                <div class="col-lg-2 col-sm-6 col-12 dashboard-number-col">
                    <div class="card">
                        <div class="card-body card-dashboard">
                            <div class="">
                                <div>
                                    <p class="fs-12 mb-1 d-block my-2">Vendas</p>
                                    <i class="fas fa-shopping-basket" style="font-size: 1.2em;"></i><span class="fs-20 font-w700"> <?php echo $vendas[0]; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-2 col-sm-6 col-12 dashboard-number-col">
                    <div class="card">
                        <div class="card-body card-dashboard">
                            <div class="">
                                <div>
                                    <p class="fs-12 mb-1 d-block my-2">Produtos</p>
                                    <i class="fas fa-tags" style="font-size: 1.2em;"></i><span class="fs-20 font-w700 d-inline"> <?php echo $produtos; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="<?= $_SESSION['UserPlan'] == 5 ? 'col-lg-2' : 'col-lg-4' ?> col-sm-6 col-12 dashboard-number-col">
                    <div class="card">
                        <div class="card-body card-dashboard">
                            <div class="">
                                <div>
                                    <p class="fs-12 mb-1 d-block my-2">Clientes</p>
                                    <i class="fas fa-users" style="font-size: 1.2em;"></i><span class="fs-20 font-w700 d-inline"> <?php echo $clientes; ?>
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
                                <h4 class="fs-18 mb-0 text-black font-w600">Volume de Vendas</h4>
                                <span class="fs-12">
                                    <?php
                                    if ((!(isset($_GET['data-inicio'])) || empty($_GET['data-inicio'])) && (!(isset($_GET['data-final'])) || empty($_GET['data-final']))) {
                                        echo "Nos ú1ltimos " . $default_period . " dias.";
                                    }
                                    ?>
                                </span>
                            </div>

                        </div>
                        <div class="card-body pt-0">
                            <canvas id="salesVolumeChart" height="120"></canvas>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

</div>
</div>

<!-- FOOTER -->
<?php require_once(dirname(__FILE__) . '/footer.php'); ?>

<!-- TEMPORARIO -->

<script>
     $(document).ready(function(){
        $('.eye-disabled').on('click', function(){
            let elemt = $(this).data('id');
            console.log(elemt);
            $(elemt).toggleClass('d-none');
        })
    })
</script>
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