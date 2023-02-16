<?php
error_reporting(-1);            
ini_set('display_errors', 1);   
require_once(dirname(__FILE__) . '/../../includes/config.php');

session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}

if ($_SESSION['UserPlan'] != 6) {
    header('Location: ' . SERVER_URI . '/pedidos/dashboard');
    exit;
}

$user__id = $_SESSION['UserID'];

$page_title = "Dashboard | Logzz";
$sidebar_expanded = false;
$select_datatable_page = true;
$orders_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

// function str_contains(string $haystack, string $needle): bool {
//     return '' === $needle || false !== strpos($haystack, $needle);
// }

$default_period = 30;

$get_operator = $conn->prepare("SELECT * FROM logistic_operator WHERE user_id = :user_id");
$get_operator->execute(array("user_id" => $_SESSION['UserID']));
$data = $get_operator->fetch();

$operator_id = $data['operator_id'];
$operation_id = $data['local_operation'];

$get_frustrated_deliveries = $conn->prepare("SELECT DISTINCT COUNT(order_status) FROM orders o INNER JOIN local_operations_orders lo ON lo.order_id = o.order_id WHERE o.order_status = 4 AND lo.responsible_id = :operator_id AND lo.operation_id = (SELECT logistic_operator.local_operation FROM logistic_operator WHERE logistic_operator.user_id = :user_id)");
$get_frustrated_deliveries->execute(array("user_id" => $user__id, "operator_id" => $operator_id));

$get_complete_deliveries = $conn->prepare("SELECT DISTINCT COUNT(order_status) FROM orders o INNER JOIN local_operations_orders lo ON lo.order_id = o.order_id WHERE o.order_status = 3 AND lo.responsible_id = :operator_id AND lo.operation_id = (SELECT logistic_operator.local_operation FROM logistic_operator WHERE logistic_operator.user_id = :user_id)");
$get_complete_deliveries->execute(array("user_id" => $user__id, "operator_id" => $operator_id));

$frustrated_deliveries = $get_frustrated_deliveries->fetch();

$complete_deliveries = $get_complete_deliveries->fetch();

$get_commission = $conn->prepare('SELECT SUM(meta_value) FROM transactions_meta WHERE meta_key!="in_review_transfer" AND meta_key!="transfer_balance" AND user__id=:user__id');
$get_commission->execute(array("user__id" => $user__id));
$get_comission = $get_commission->fetch();

# Busca os locais da operação do usuário
$get_order_locale = $conn->prepare("SELECT id, city FROM operations_locales WHERE operation_id = :operation_id");
$get_order_locale->execute(array("operation_id" => $operation_id));

# Busca as taxas de entrega da operação do usuário
$get_delivery_taxes = $conn->prepare("SELECT * FROM operations_delivery_taxes WHERE operation_id = :operation_id");
$get_delivery_taxes->execute(array("operation_id" => $operation_id));

$locales = array();
$cities = $get_order_locale->fetchAll();
$delivery_taxes = $get_delivery_taxes->fetchAll();

# Relaciona as taxas de entrega aos locais em um array de chave-valor
foreach($cities as $city) {
    for($i = 0; $i < sizeof($delivery_taxes); $i++) {
        $tax = $delivery_taxes[$i];
        if($city["id"] == $tax["operation_locale"]) {
            $locales[$city["city"]] = $tax["complete_delivery_tax"];
        }
    }

}

$get_complete_deliveries = $conn->prepare("SELECT DISTINCT o.order_id, o.client_address FROM orders o INNER JOIN local_operations_orders lo ON lo.order_id = o.order_id WHERE o.order_status = 3 AND lo.responsible_id = :operator_id AND lo.operation_id = (SELECT logistic_operator.local_operation FROM logistic_operator WHERE logistic_operator.user_id = :user_id)");
$get_complete_deliveries->execute(array("user_id" => $user__id, "operator_id" => $operator_id));

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

    if (!(empty($_GET['produto'])) && $_GET['produto'] != 0) {
        $filter_by_product = addslashes($_GET['produto']);

        if (!(empty($_GET['status'])) && $_GET['status'] != 11) {
            # Filtra por PRODUTO e STATUS
            $filter_by_status = addslashes($_GET['status']);
            $filter_by_status--;

            $filter_by_product = addslashes($_GET['produto']);
            if (preg_match("/,/", $filter_by_product)) {
                $filter_by_product = explode(',', $filter_by_product);
                require_once(dirname(__FILE__) . '/../../includes/filters/multiple-produto-status.php');
            } else {
                require_once(dirname(__FILE__) . '/../../includes/filters/produto-status.php');
            }
            
        } else {
            # Filtra por PRODUTO
            $filter_by_product = addslashes($_GET['produto']);
            if(preg_match("/,/", $filter_by_product)){
                $filter_by_product = explode(',', $filter_by_product);
                require_once(dirname(__FILE__) . '/../../includes/filters/multiple-produto.php');
            } else {
                require_once(dirname(__FILE__) . '/../../includes/filters/produto.php');
            }
        }
        
    } else if (!(empty($_GET['status']))) {
        # Filtra por STATUS
        $filter_by_status = addslashes($_GET['status']);
        $filter_by_status--;
        require_once(dirname(__FILE__) . '/../../includes/filters/status.php');
    } else {
        # Sem filtros
        require_once(dirname(__FILE__) . '/../../includes/filters/no-filters-op.php');
    }
} else { 

    $date_end = date('Y-m-d', strtotime('+2 day'));
    $date_init = date('Y-m-d', strtotime($date_end . '-' . $default_period . 'days'));

    # Sem filtros
    require_once(dirname(__FILE__) . '/../../includes/filters/no-filters-op.php');
}


$op_charts = true;
$dashboard_charts = array(
    'money-payments' => $a,
    'debit-payments' => $c,
    'credit-payments' => $b,
    'pix-payments' => $d,
    'agendada'   =>  @$sales0['Q'],
    'reagendada'   =>  @$sales1['Q'],
    'atrasada'   =>  @$sales2['Q'],
    'completa'   =>  @$sales3['Q'],
    'frustrada'    =>  @$sales4['Q'],
    'cancelada'    =>  @$sales5['Q'],
    'reembolsada'    =>  @$sales9['Q'],
    'confirmada'    =>  @$sales10['Q'],
    'emaberto'    =>  @$sales11['Q'],
    'indisponivel'    =>  @$sales12['Q'],
);


?>
<div class="container-fluid">
    <?php
    if (isset($_GET['filtro']) && $_GET['filtro'] == 'ativo') {

        $breadcumb = "Filtros Ativos:&nbsp; ";

        if (!(empty(@$_GET['data-inicio']))) {
            $breadcumb .= '<span class="badge badge-success light">Data: <b>' . date("d/m", strtotime($date_init)) . '</b> a <b>' . date("d/m", strtotime($date_end)) . '</b></span>';
        }

        if (!(empty(@$_GET['produto']))) {

            if (preg_match("/,/", $_GET['produto'])) {
                
                foreach ($filter_by_product as $product) {

                    $get_product_name = $conn->prepare('SELECT product_name FROM products WHERE product_id = :product_id LIMIT 1');
                    $get_product_name->execute(array('product_id' => $product));

                    if ($get_product_name->rowCount() > 0){
                        $name_string = $get_product_name->fetch();
                    
                        if (strlen($name_string['product_name']) > 20) {
                            $name_string_ = substr($name_string['product_name'], 0, 20) . "...";
                        } else {
                            $name_string_ = $name_string['product_name'];
                        }

                        $breadcumb .= '<span class="badge badge-success light">Produto: <b>' . $name_string_ . '</b></span>';
                    }

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
            switch ($filter_by_status) {
                case 0:
                    $status = 'Agendada';
                    break;
                case 1:
                    $status = 'Reagendada';
                    break;
                case 2:
                    $status = 'Atrasada';
                    break;
                case 3:
                    $status = 'Completa';
                    break;                    
                case 4:
                    $status = 'Frustrada';
                    break;
                case 5:
                    $status = 'Cancelada';
                    break;
                case 9:
                    $status = 'Reembolsada';
                    break;
                case 10:
                    $status = 'Confirmado';
                    break;
                case 11:
                    $status = 'Em Aberto';
                    break;
                case 12:
                    $status = 'Indisponível';
                    break;
                default:
                    $status = 'Todos';
                    break;
            }
            $breadcumb .= '<span class="badge badge-success light">Status: <b>' . $status . '</b></span>';
        }
    }
    ?>

    <div class="row">
        <div class="col-xl-12 col-xxl-12">
            <div class="mb-3 mr-3">
                <h6 class="fs-14 text-muted mb-0"><?php echo @$breadcumb; ?></h6>
            </div>
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
                                    <p class="fs-12 mb-1 d-inline">Comissão</p>
                                    <span class="fs-20 font-w700 d-block"><small>R$ </small><?php echo number_format($get_comission[0], 2, ',', '.'); ?>
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
                                    <p class="fs-12 mb-1 d-block">Entregas Completas</p>
                                    <i class="fas fa-shopping-basket" style="font-size: 1.2em;"></i><span class="fs-20 font-w700"> <?php echo $complete_deliveries[0]; ?>
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
                                    <p class="fs-12 mb-1 d-block">Entregas Frustradas</p>
                                    <i class="fas fa-minus-circle" style="font-size: 1.2em;"></i><span class="fs-20 font-w700 d-inline"> <?php echo $frustrated_deliveries[0]; ?>
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

                <div class="col-lg-2 col-sm-6 dashboard-number-col">
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
                                <!-- <?php if (isset($_GET['status']) && $_GET['status'] != "") { ?><span class="fs-12">Filtro por Status não se aplica a esse gráfico.</span><?php } else { ?><span class="fs-12">Todos pedidos no período.</span><?php } ?> -->
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <?php
                            if (@$vendas[0] == 0) {
                                echo '<label class="text-label">Não há dados suficientes para este gráfico.</label>';
                            } else {
                            ?>
                                <canvas id="salesStatusChart" height="250"></canvas>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
 
                <div class="col-xl-4 col-xxl-6 col-lg-4">
                    <div class="card">
                        <div class="card-header align-items-start pb-0 border-0">
                            <div>
                                <h4 class="fs-18 mb-0 text-black font-w600">Entregas por Forma de Pagamento</h4>
                                <!-- <?php if (isset($_GET['status']) && $_GET['status'] != "") { ?><span class="fs-12">Filtro por Status não se aplica a esse gráfico.</span><?php } else { ?><span class="fs-12">Pedidos Completos no período.</span><?php } ?> -->
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <?php
                            if (@$a == 0 && @$b == 0 && @$c == 0 && @$d == 0) {
                                echo '<label class="text-label">Não há dados suficientes para este gráfico.</label>';
                            } else {
                            ?>
                                <canvas id="salesPayMethodChart" height="200"></canvas>
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
                                                <select id="select-ship-product" class="d-block default-select" data-live-search="true" multiple>
                                                    <option id="all-products-option" value="" selected>Todos os Produtos</option>
                                                    <?php
                                                    while ($prodcut = $get_product_list->fetch()) {
                                                    ?>
                                                        <option <?php if (@$_GET['produto'] ===  $prodcut['product_id']) {
                                                                    echo "selected";
                                                                } ?> value="<?php echo $prodcut['product_id']; ?>"><?php if (strlen($prodcut['product_name']) > 30) {
                                                                                                                        echo substr($prodcut['product_name'], 0, 30) . "...";
                                                                                                                    } else {
                                                                                                                        echo $prodcut['product_name'];
                                                                                                                    } ?></option>
                                                    <?php
                                                    }
                                                    ?>
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
                                                <option <?php if (@$_GET['status'] == '3') {
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
                                            <input type="hidden" id="text-filter-status-id" name="status" value="<?php echo @$_GET['status'];  ?>" required>
                                    </div>

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

<?php
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>
