<?php

    require_once(dirname(__FILE__) . '/../../includes/config.php');

    session_name(SESSION_NAME);
    session_start();

    if (!(isset($_SESSION['UserID']))) {
        header('Location: ' . SERVER_URI . '/login');
        exit;
    }

    $user__id = $_SESSION['UserID'];

    $page_title = "Dashboard | Logzz";
    $orders_page = $select_datatable_page = true;
    $sidebar_expanded = false;
    $orders_page = true;
    
    require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');


    $default_period = date("t");
    
    if(isset($_GET['data-final']) && !(empty($_GET['data-final'])) || isset($_GET['data-inicio']) && !(empty($_GET['data-inicio']))){
        if (isset($_GET['data-final']) && !(empty($_GET['data-final']))) {
            $date_formated = pickerDateFormate($_GET['data-final']);
            $date_end = date('Y-m-d', strtotime("+1 days", strtotime($date_formated)));
    
        } else {
            $date_end = date('Y-m-d', strtotime("+1 days", strtotime(date('Y-m-d'))));
        }
    
        if (isset($_GET['data-inicio']) && !(empty($_GET['data-inicio']))) {
    
            $date_formated = pickerDateFormate($_GET['data-inicio']);
            $date_init = date('Y-m-d', strtotime($date_formated));
    
        }else{
            $date_init = date('Y-m-d', strtotime(date('Y-m-d') . '-' . $default_period . 'days'));
        }
    }else{
        $date_end = date('Y-m-d', strtotime('+1 day'));
        $date_init = date('Y-m-d', strtotime($date_end . '-' . $default_period . 'days'));
    }

    # Sem filtros
    require_once(dirname(__FILE__) . '/../../includes/filters/filters-logistic.php');


    $verifyIfFiltersIsActive = !empty($_GET['data-inicio']) || !empty($_GET['data-final']) || !empty($_GET['produto']) || !empty($_GET['status']) || !empty($_GET['cliente']);

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
            switch ($_GET['status'] - 1) {
                case 0:
                    $status = 'Agendado';
                    break;
                case 2:
                    $status = 'Atrasado';
                    break;
                case 5:
                    $status = 'Cancelado';
                    break;
                case 4:
                    $status = 'Frustrado';
                    break;
                case 3:
                    $status = 'Completo';
                    break;
                case 1:
                    $status = 'Reagendado';
                    break;
                default:
                    $status = 'Todos';
                    break;
            }
            $breadcumb .= '<span class="badge badge-success light">Status: <b>' . $status . '</b></span>';
            $status_str = $status;
        }
        
        if (!(empty(@$_GET['cliente']))) {
            $get_locale_name = $conn->prepare('SELECT full_name FROM users WHERE user__id = :user__id LIMIT 1');
            $get_locale_name->execute(array('user__id' => $_GET['cliente']));

            $name_string = $get_locale_name->fetch();
            $name_string_ = $name_string[0];

            $breadcumb .= '<span class="badge badge-success light">Usuário: <b>' . $name_string_ . '</b></span>';
        }

    }
?>


<div class="container-fluid">
<div class="row">
        <div class="col-xl-12 col-xxl-12">
            <div class="mb-3 mr-3">
                <h6 class="fs-14 text-muted mb-0"><?php echo @$breadcumb; ?></h6>
            </div>
            <div class="row dashboard-numbers">
                <div class="col-xl-6 col-xxl-6 col-lg-6 col-sm-6 dashboard-number-col">
                    <div class="card">
                        <div class="card-body card-dashboard">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <i class="fas fa-money-bill-wave"></i>
                                    <p class="fs-12 mb-1 d-inline"> <?= $_SESSION['UserPlan'] == 5 ? 'Receitas total ' : 'Faturamento' ?> 
                                        <span class="ml-1" style="cursor:pointer;"  data-toggle="tooltip" data-placement="top" title='<?= $_SESSION['UserPlan'] == 5 ? 'Todo o valor cobrado dos usuários por pedido com status "enviado"' : 'Valor recebido por plataformas externas, não entra em comissões como valor a liberar para saque' ?>'> 
                                            <i class="fas fa-info-circle" style="color:#ccc; font-size:14px;"></i>
                                        </span>
                                   </p>
                                    <span class="fs-20 font-w700 d-block"><small>R$ </small> <?php echo number_format($_SESSION['UserPlan'] == 5 ? $total_freight : $faturamento, 2, ',', '.'); ?>
                                    </span>
                                </div>
                                <div class="d-flex flex-column">
                                    <?php if($_SESSION['UserPlan'] == 5): ?>
                                        <div>
                                            <i class="fas fa-donate"></i>
                                            <p class="fs-12 mb-1 d-inline">Despesas Plataforma
                                                <span class="ml-1" style="cursor:pointer;"  data-toggle="tooltip" data-placement="top" title='Valor cobrado pelas transportadoras'> 
                                                    <i class="fas fa-info-circle" style="color:#ccc; font-size:14px;"></i>
                                                </span>
                                            </p>
                                        </div>    
                                        <div class="d-flex align-items-center">
                                            <span class="fs-20 font-w700" id="value_platform"><small>R$</small> <?= number_format($expenses, 2, ',', ' '); ?> </span>
                                        
                                            <?php if(isset($_GET['status']) && !empty($_GET['status']) && $_GET['status'] !== '8'): ?>
                                                <span class="ml-1" style="cursor:pointer;"  data-toggle="tooltip" data-placement="right" title='Não é possível realizar o calculo de despesas quando o filtro por status está ativo.'> 
                                                    <i class="fas fa-info-circle text-warning" style="font-size:14px;"></i>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div>
                                            <i class="fas fa-donate"></i>
                                            <p class="fs-12 mb-1 d-inline">Custo frete 
                                                <span class="ml-1" style="cursor:pointer;"  data-toggle="tooltip" data-placement="top" title='Quantidade de envios vezes o valor cobrado por envio de acordo com o seu plano na plataforma'> 
                                                    <i class="fas fa-info-circle" style="color:#ccc; font-size:14px;"></i>
                                                </span>
                                            </p>
                                        </div>    
                                        <div class="d-flex align-items-center">
                                            <span class="fs-20 font-w700" id="value_platform"><small>R$</small> <?= number_format($freight_total, 2, ',', ' '); ?></span>
                                        </div>
                                    <?php endif; ?> 
                                </div>
                                <?php if($_SESSION['UserPlan'] == 5): ?>
                                    <div class="d-flex flex-column">
                                        <div class="">
                                            <i class="fas fa-donate"></i>
                                            <p class="fs-12 mb-1 d-inline">Lucro Plataforma</p>
                                        </div>

                                        <span class="fs-20 font-w700" id="value_profit"><small>R$ </small> <?= number_format($total_freight - $expenses, 2, ',', ' ') ?> </span>
                                       
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex flex-column">
                                        <div class="">
                                            <i class="fas fa-donate"></i>
                                            <p class="fs-12 mb-1 d-inline">Lucro</p>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <!-- FATURAMENTO - CUSTO DO FRETE -->
                                            <span class="fs-20 font-w700" id="value_profit"><small>R$ </small> <span id="value_l"> <?= number_format($faturamento - $freight_total, 2, ',', ' ') ?> </span> </span>
                                        </div>

                                    </div>
                                <?php endif;?>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-2 col-sm-6 dashboard-number-col">
                    <div class="card">
                        <div class="card-body card-dashboard">
                            <div class="d-flex align-items-end">
                                <div>
                                    <p class="fs-12 mb-1 d-block"><?= $_SESSION['UserPlan'] == 5 ? 'Vendas' : 'Envios' ?></p>
                                    <i class="fas fa-shopping-basket" style="font-size: 1.2em;"></i><span class="fs-20 font-w700"> <?php echo $shipments ?>
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
                                    <p class="fs-12 mb-1 d-block"><?= $_SESSION['UserPlan'] == 5 ? 'Produtos' : 'Produtos Enviados' ?></p>
                                    <i class="fas fa-minus-circle" style="font-size: 1.2em;"></i><span class="fs-20 font-w700 d-inline"> <?php echo $products; ?>
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
                                    <p class="fs-12 mb-1 d-block"><?= $_SESSION['UserPlan'] == 5 ? 'Clientes Novos' : 'Clientes Únicos' ?></p>
                                    <i class="fas fa-tags" style="font-size: 1.2em;"></i><span class="fs-20 font-w700 d-inline"> <?php echo $clientes?>
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
                        <?php if($shipments > 0): ?>
                            <div class="card-body pt-0">
                                <canvas id="salesVolumeChartSales" height="120"></canvas>
                            </div>
                        <?php else: ?>
                            <div class="card-header align-items-start pb-0 border-0">
                                <label class="text-label">Não há dados suficientes para este gráfico.</label>
                                <canvas class="d-none" id="salesVolumeChartSales" height="120"></canvas>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-xl-4 col-xxl-6 col-lg-4">
                    <div class="card">
                        <div class="card-header align-items-start pb-0 border-0 d-flex align-items-center mb-2">
                            <div>
                                <h4 class="fs-18 mb-0 text-black font-w600">Envios por status</h4>
                                <span class="fs-12">
                                    
                                </span>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <?php $total = $total_to_send + $total_to_sending + $total_to_sent; ?>
                            <?php if($total == 0 ): ?>
                                <label class="text-label">Não há dados suficientes para este gráfico.</label>
                                <canvas id="salesVolumeChartLogistic" class="d-none" height="120"></canvas>
                            <?php endif; ?>
                            <canvas id="salesVolumeChartLogistic" height="120"></canvas>
                            <input type="hidden" id="total_to_send" value="<?= $total_to_send ?>">
                            <input type="hidden" id="total_to_sending" value="<?= $total_to_sending?>">
                            <input type="hidden" id="total_to_sent" value="<?= $total_to_sent ?>">
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-xxl-6 col-lg-4">
                    <div class="card">
                        <div class="card-header align-items-start pb-0 border-0 d-flex align-items-center mb-2">
                            <div class="">
                                <h4 class="fs-18 mb-0 text-black font-w600">Envios por transportadoras</h4>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <?php if($total_correios > 0): ?>
                                <canvas id="salesShippingCompanyChartLogistic" height="120"></canvas>
                                <input type="hidden" id="total_correios" value="<?= $total_correios ?>">
                            <?php else: ?>
                                <label class="text-label">Não há dados suficientes para este gráfico.</label>
                                <canvas class="d-none" id="salesShippingCompanyChartLogistic" height="120"></canvas>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-12 col-xxl-12">
            <div class="card p-3">
                <div class="card-header align-items-start pb-0 border-0 d-flex align-items-center mb-2">
                    <div class="">
                        <h4 class="fs-18 mb-0 text-black font-w600">Envios por dias da semana</h4>
                    </div>
                </div>
                <?php  $total_days = $sun + $mon + $tue + $wed + $thu + $fri + $sat ?>
                <?php if($total_days == 0 ): ?>
                    
                    <div class="text-center">
                        <label class="text-label">Não há dados suficientes para este gráfico.</label>
                        <canvas id="daysOfWeek" class="d-none" height="400"></canvas>
                    </div>
                <?php else: ?>
                    <canvas id="daysOfWeek" height="400"></canvas>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-xl-12 col-xxl-12">
            <div class="card p-3">
                <div class="card-header align-items-start pb-0 border-0 d-flex align-items-center mb-2">
                    <div class="">
                        <h4 class="fs-18 mb-0 text-black font-w600">Produtos mais enviados</h4>
                    </div>
                </div>
                <div class="col-xl-12" style="padding-left: 5px;">
                    <div class="tab-content">
                        <div id="All" class="tab-pane active fade show">
                            <div class="table-responsive" style="overflow-x: visible;">
                                <table id="orders-list" class="table card-table display dataTablesCard" data-page-length='30' data-order='[[0, "desc"]]' >
                                    <thead>
                                        <tr>
                                            <th>Nome</th>
                                            <th>Envios</th>
                                            <th>Produtos</th>
                                            <th>%</th>
                                            <th><?= $_SESSION['UserPlan'] == 5 ? 'Receita Plataforma' : 'Faturamento' ?>  
                                                <span class="ml-1" style="cursor:pointer;"  data-toggle="tooltip" data-placement="top" title='<?= $_SESSION['UserPlan'] == 5 ? 'Total cobrado do usuário no período selecionado no filtro geral da tela' : 'Total recebido por plataformas externas, não entra em comissões como valor a liberar para saque' ?>'> 
                                                    <i class="fas fa-info-circle" style="color:#ccc; font-size:14px;"></i>
                                                </span>
                                            </th>
                                            <th><?= $_SESSION['UserPlan'] == 5 ? 'Despesa Plataforma' : 'Custo Frete' ?>  
                                                <span class="ml-1" style="cursor:pointer;"  data-toggle="tooltip" data-placement="top" title='<?= $_SESSION['UserPlan'] == 5 ? 'Total de custo de frete pro administrador no período selecionado no filtro geral da tela' : 'Quantidade de envios vezes o valor cobrado por envio de acordo com seu plano na plataforma' ?>'> 
                                                    <i class="fas fa-info-circle" style="color:#ccc; font-size:14px;"></i>
                                                </span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        
                                        <?php foreach($products_most_sales as $product): ?>
                                            <tr class="redirectToProduct" style="cursor:pointer;" data-id="<?= $product['id_product']?>">
                                                <td><?= $product['name'] . " "."[". $product['product_code']  ."]"; ?></td>
                                                <td><?= $product['sales']?></td>
                                                <td><?= $product['products']?></td>
                                                <td><?= $product['percent'] . "%"; ?></td>
                                                <td>R$ <?= number_format($product['income'], 2, ',', ' '); ?></td>
                                                <td id="<?= $product['id']; ?>">R$ 0,00</td>
                                            </tr>
                                        <?php endforeach; ?>
                                       
                                    </tbody>
                                </table>
                            </div>
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
                                        <input name="data-inicio" placeholder="Do dia ..." class="datepicker-default form-control picker__input mb-2 data-inicio" id="datepicker" readonly="" aria-haspopup="true" aria-expanded="false" aria-readonly="false" aria-owns="datepicker_root">
                                        <input name="data-final" placeholder=".. ao dia" class="datepicker-default form-control picker__input data-final" id="datepicker" readonly="" aria-haspopup="true" aria-expanded="false" aria-readonly="false" aria-owns="datepicker_root">

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
                                                    <?php if($_SESSION['UserPlan'] == 5): ?>
                                                        <?php 
                                                            $query = $conn->prepare("SELECT p.product_id, p.product_name, p.product_code FROM products AS p WHERE p.product_trash = 0"); 
                                                            $query->execute();
                                                            
                                                            $allProducts = $query->fetchAll(\PDO::FETCH_ASSOC);
                                                        ?>
                                                    <?php else: ?>
                                                        <?php 
                                                            $query = $conn->prepare("SELECT p.product_id, p.product_name, p.product_code FROM products AS p WHERE p.user__id = :user__id AND p.product_trash = 0"); 
                                                            $query->execute(['user__id' => $user__id]);
                                                            
                                                            $allProducts = $query->fetchAll(\PDO::FETCH_ASSOC);
                                                        ?>
                                                    <?php endif; ?>
                                                   

                                                    <?php foreach($allProducts as $product): ?>
                                                        <option id="" value="<?= $product['product_id']?>" <?php if (!(empty(@$_GET['produto'])) && $_GET['produto'] == $product['product_id']) echo "selected" ?>>
                                                            <?php if (strlen($product['product_name']) > 20) {
                                                                    echo substr($product['product_name'], 0, 20) . "...";
                                                                } else {
                                                                    echo $product['product_name'];
                                                                } echo " <small>[" . $product['product_code'] . "]</small>";  
                                                            ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <input type="hidden" id="text-ship-product" name="produto" value="<?php echo @$_GET['produto']; ?>" required>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if($_SESSION['UserPlan'] == 5): ?>
                                        <div class="col-lg-12 mt-3">
                                            <div class="example">
                                                <div class="form-group">
                                                    <label class="text-label"><small>por Usuário</small></label>
                                                    <select id="select-ship-name" class="d-block default-select" data-live-search="true">
                                                        <option id="all-products-option" value="" selected>Todos os Usuários</option>
                                                        <?php 
                                                            $query = $conn->prepare("SELECT u.user__id, u.full_name, u.user_code FROM users AS u WHERE active = 1");
                                                            $query->execute();

                                                            $all_users = $query->fetchAll(\PDO::FETCH_ASSOC);
                                                        ?>

                                                        <?php foreach($all_users as $user): ?>
                                                            <option value="<?= $user['user__id'] ?>" <?php if (!(empty(@$_GET['cliente'])) && $_GET['cliente'] == $user['user__id']) echo "selected" ?>>
                                                                <?php if (strlen($user['full_name']) > 20) {
                                                                        echo substr($user['full_name'], 0, 20) . "...";
                                                                    } else {
                                                                        echo $user['full_name'];
                                                                    } echo " <small>[" . $user['user_code'] . "]</small>";  
                                                                ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <input type="hidden" id="text-ship-name" name="cliente" value="" required>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="col-lg-12">
                                            <p style="margin-bottom: 0.5rem;"><small>por Status</small></p>
                                            <select id="select-filter-status-id" class="d-block default-select">
                                                <option id="all-status-option" value="" selected>Todos os Status</option>
                                                <option value="8" <?php if (!(empty(@$_GET['status'])) && $_GET['status'] == 8) echo "selected" ?>>Enviado</option>
                                                <option value="7" <?php if (!(empty(@$_GET['status'])) && $_GET['status'] == 7) echo "selected" ?>>Enviando</option>
                                                <option value="6" <?php if (!(empty(@$_GET['status'])) && $_GET['status'] == 6) echo "selected" ?>>Á Enviar</option>
                                            </select>
                                            <input type="hidden" id="text-filter-status-id" name="status" value="<?php echo @$_GET['status']; ?>" required>
                                    </div>

                                    <div class="col-lg-12 mt-4">
                                        <button type="submit" id="SubmitButton" class="btn btn-block btn-success"><i class="fas fa-sliders-h scale2 mr-2" aria-hidden="true"></i>Filtrar</button>
                                        <a href="<?php echo SERVER_URI; ?>/pedidos/dashboard/logistic" class="btn btn-block">Limpar Filtros</a>
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

<script>

    console.log(new Chart);
    const salesVolumeChartLogistic = document.getElementById("salesVolumeChartLogistic").getContext('2d');
    new Chart(salesVolumeChartLogistic, {
        type: 'pie',
        data: {
            datasets: [{
                data: [$('#total_to_send').val(), $('#total_to_sending').val(), $('#total_to_sent').val() ],
                borderWidth: 0,
                backgroundColor: ["#e3f9e9", "#0b352b", "#2bc156"],
                hoverBackgroundColor: ["#e3f9e9", "#0b352b", "#2bc156"]

            }],
            labels: [
                "A Enviar", "Enviando", "Enviado"
            ]
        },
        options: {
            responsive: true,
            legend: false,
        }
    });

    const salesShippingCompanyChartLogistic = document.getElementById("salesShippingCompanyChartLogistic").getContext('2d');
    new Chart(salesShippingCompanyChartLogistic, {
        type: 'pie',
        data: {
            datasets: [{
                data: ["0", $('#total_correios').val(), "0"],
                borderWidth: 0,
                backgroundColor: ["#d3161c", "#eecc09", "#0b352b"],
                hoverBackgroundColor: ["#d3161c", "#eecc09", "#0b352b"]

            }],
            labels: [
                "Jadlog", "Correios", "Melhor envio"
            ]
        },
        options: {
            responsive: true,
            legend: false,
        }
    });
    
    var ctx = document.getElementById('salesVolumeChartSales');
    var salesVolumeChart = new Chart(ctx, {
        data: {
            datasets: [{
                type: 'line',
                label: "<?= $_SESSION['UserPlan'] == 5 ? 'Receita Total' : 'Faturamento' ?>",
                yAxisID: 'B',
                data: [
                    <?php 
                        $begin = new DateTime($date_init);
                        $end = new DateTime($date_end);
                        $end = $end->modify('+1 dia');

                        $interval = DateInterval::createFromDateString('1 day');
                        $period = new DatePeriod($begin, $interval, $end);

                        
                        foreach ($period as $date) {
                            $date = $date->format("Y-m-d");

                            $order_date =  $date  . "%";
                            $status = isset($_GET['status']) && !empty($_GET['status']) ? $_GET['status'] : 8;
                            if($_SESSION['UserPlan'] == 5){
                                $get_orders_total = $conn->prepare("SELECT SUM(s.product_shipping_tax) AS total FROM orders AS o INNER JOIN sales AS s ON s.sale_id = o.sale_id WHERE o.order_status = :status AND o.order_date LIKE :date_init $str_in_q_user");
                                $get_orders_total->execute(array('date_init' => $order_date, 'status' => $status));
                            }else{
                                $get_orders_total = $conn->prepare("SELECT SUM(o.order_liquid_value) AS total FROM orders AS o WHERE o.user__id = :user__id AND o.order_status = :status AND o.order_date LIKE :date_init");
                                $get_orders_total->execute(array('user__id' => $user__id, 'date_init' => $order_date, 'status' => $status));
                            }

                            $orders = $get_orders_total->fetch();

                            if ($orders[0] == null) {
                                echo $orders = 0;
                                echo ", ";
                            } else {

                                echo $orders = $orders['0'];
                                echo ", ";
                            }
                        }   
                    ?>
                ],
                borderColor: 'rgb(47,222,145)',
                backgroundColor: 'rgb(47,222,145)',
                tension: 0.4
            },
            {
                type: 'bar',
                label: "<?= $_SESSION['UserPlan'] == 5 ? 'Produtos' : 'Produtos Enviados' ?>",
                yAxisID: 'A',
                data: [
                    <?php
                    $begin = new DateTime($date_init);
                    $end = new DateTime($date_end);
                    $end = $end->modify('+1 dia');

                    $interval = DateInterval::createFromDateString('1 day');
                    $period = new DatePeriod($begin, $interval, $end);

                    foreach ($period as $date) {

                        $date = $date->format("Y-m-d");
                        $order_date = "%" . $date  . "%";
                        $status = isset($_GET['status']) && !empty($_GET['status']) ? $_GET['status'] : 8;
                        if($_SESSION['UserPlan'] == 5){
                            $get_orders_total = $conn->prepare("SELECT SUM(o.order_quantity) FROM orders AS o WHERE o.order_status = :status AND o.order_date LIKE :date_init $str_in_q_user");
                            $get_orders_total->execute(array('date_init' => $order_date, 'status' => $status));
                        }else{
                            $get_orders_total = $conn->prepare("SELECT SUM(o.order_quantity) FROM orders AS o WHERE o.user__id = :user__id AND o.order_status = :status AND o.order_date LIKE :date_init");
                            $get_orders_total->execute(array('user__id' => $user__id, 'date_init' => $order_date, 'status' => $status));
                        }

                        $orders = $get_orders_total->fetch();

                        if ($orders[0] == null) {
                            echo $orders = 0;
                            echo ", ";
                        } else {

                            echo $orders = $orders['0'];
                            echo ", ";
                        }
                    }
                    ?>
                ],
                borderColor: 'rgb(212 249 232)',
                backgroundColor: 'rgb(212 249 232)',
            },
            {
                type: 'line',
                label: '<?= $_SESSION['UserPlan'] == 5 ? 'Lucro plataforma' : 'Lucro' ?>',
                yAxisID: 'A',
                data: [
                    <?php
                    $begin = new DateTime($date_init);
                    $end = new DateTime($date_end);
                    $end = $end->modify('+1 dia');

                    $interval = DateInterval::createFromDateString('1 day');
                    $period = new DatePeriod($begin, $interval, $end);

                    if($_SESSION['UserPlan'] == 5 ){
                        foreach ($period as $date) {

                            $date = $date->format("Y-m-d");
                            $order_date =  $date  . "%";

                            $status = isset($_GET['status']) && !empty($_GET['status']) ? $_GET['status'] : 8;
                            $get_orders_total = $conn->prepare("SELECT SUM(s.product_shipping_tax) AS total FROM orders AS o INNER JOIN sales AS s ON s.sale_id = o.sale_id WHERE o.order_status = :status AND o.order_date LIKE :date_init $str_in_q_user");
                            $get_orders_total->execute(array('date_init' => $order_date, 'status' => $status));
                            $orders = $get_orders_total->fetch();
    
                            $get_orders_traking = $conn->prepare("SELECT SUM(o.order_tracking_value) AS total FROM orders AS o INNER JOIN sales AS s ON s.sale_id = o.sale_id WHERE o.order_status = :status AND o.order_date LIKE :date_init $str_in_q_user");
                            $get_orders_traking->execute(array('date_init' => $order_date, 'status' => $status));

                            $tracking = $get_orders_traking->fetch();

                            if ($orders[0] == null) {
                                echo $orders = 0;
                                echo ", ";
                            } else {
                                echo $orders = ($orders[0] - $tracking[0]);
                                echo ", ";
                            }
                           
                        }
                    }else{
                        foreach ($period as $date) {

                            $date = $date->format("Y-m-d");
                            $order_date =  $date  . "%";
                            $status = isset($_GET['status']) && !empty($_GET['status']) ? $_GET['status'] : 8;
                            
                            $get_orders_total = $conn->prepare("SELECT SUM(o.order_liquid_value) AS total FROM orders AS o WHERE o.user__id = :user__id AND o.order_status = :status AND o.order_date LIKE :date_init");
                            $get_orders_total->execute(array('user__id' => $user__id, 'date_init' => $order_date, 'status' => $status));
                            
    
                            $orders = $get_orders_total->fetch();
    
                            if ($orders[0] == null) {
                                echo $orders = 0;
                                echo ", ";
                            } else {
    
                                // SOMAR QUANTIDADE DE VENDAS NO  DIA 
                                    
                                $query = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o WHERE o.user__id = :user__id AND o.order_status = :status AND o.order_date LIKE :date_init");
                                $query->execute(['date_init' => $order_date, 'status' => $status, 'user__id' => $user__id,]);
                                
                                $total = $query->fetch(\PDO::FETCH_ASSOC)['total'];
                                
                                $query = $conn->prepare("SELECT user_plan_shipping_tax FROM subscriptions WHERE user__id = :user__id");
                                $query->execute(['user__id' => $user__id]);

                                $shipping_tax = $query->fetch(\PDO::FETCH_ASSOC)['user_plan_shipping_tax'];
 
                                $total_freigth_today = $total * $shipping_tax;

                                echo $orders = $orders['0'] - $total_freigth_today;
                                echo ", ";
                            }
                           
                        }
                    }
                    ?>
                ],
                borderColor: 'rgb(11,53,43)',
                backgroundColor: 'rgb(11,53,43)',
            }],
            labels: [
                <?php
                    $begin = new DateTime($date_init);
                    $end = new DateTime($date_end);
                    $end = $end->modify('+1 dia');

                    $interval = DateInterval::createFromDateString('1 day');
                    $period = new DatePeriod($begin, $interval, $end);

                    foreach ($period as $date) {
                        // echo $date = $date->format("m\d");
                        echo "'" . $date->format("d/m") . "'";
                        echo ", ";
                    }
                ?>
            ],
        },
        options: {
            scales: {
                A: {
                    beginAtZero: true,
                    type: 'linear',
                    position: 'left',
                    suggestedMax: 10
                },
                B: {
                    beginAtZero: true,
                    type: 'linear',
                    position: 'right',
                }
            }

        }
    });

    const daysOfWeek = document.getElementById("daysOfWeek").getContext('2d');

    new Chart(daysOfWeek, {
        type: 'bar',
        data: {
            labels: ["Domingo", "Segunda", "Terça", "Quarta", "Quinta", "Sexta", "Sabádo"],
            datasets: [{
                label: 'Produtos vendidos',
                data: [<?= $sun ?>, <?= $mon ?>,<?= $tue ?>,<?= $wed ?>, <?= $thu ?>, <?= $fri ?>, <?= $sat?>],
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
    });

</script>

<script>
    const products = document.querySelectorAll('.redirectToProduct');

    products.forEach(element => {
        element.addEventListener('click', function() {
            window.location.href = `/produtos/todos/${element.dataset.id}`;
        });
    })

</script>