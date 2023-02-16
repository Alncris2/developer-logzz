
<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}
if ($_SESSION['UserPlan']!=5){
    header('Location: ' . SERVER_URI . '/pedidos/lista');
    exit;
}

$page_title = "Operadores Logísticos | Logzz";
$operator_page = true;
$select_datatable_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

$stmt = $conn->prepare('SELECT * FROM users AS u INNER JOIN logistic_operator AS l ON l.user_id = u.user__id INNER JOIN billings b ON u.user__id = b.user__id WHERE b.billing_type = "REPASSE"');
$stmt->execute();

//Verfica se os filtros estão ativos
if (isset($_GET['filtro']) && $_GET['filtro'] == 'ativo') {

    # Filtro por DATA
    $filter_data_result = array();

    if (!(empty($_GET['data-inicio']))) {
        $start_date = pickerDateFormate($_GET['data-inicio']);
        $start_date = explode(" ", $start_date);
        $start_date = $start_date[0] . " 00:00:00";
    } else {
        $start_date = '2010-01-01';
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


     //Filtro por STATUS
     if (!(empty($_GET['status']))) {
        
        $filter_status_result = array();
        $status = addslashes($_GET['status']);
        if($status == 'pendente') {
            $status_ids = $conn->prepare('SELECT billing_id FROM billings WHERE billing_status IS NULL OR billing_status = 0');
        } else if($status == 'reprovado') {
            $status_ids = $conn->prepare('SELECT billing_id FROM billings WHERE billing_status = 1');
        } else if($status == 'aprovado') {
            $status_ids = $conn->prepare('SELECT billing_id FROM billings WHERE billing_status = 2');
        } 
        $status_ids->execute();
      
        while ($status_id = $status_ids->fetch()) {
            array_push($filter_status_result, $status_id['billing_id']);
        }

        $filter_result = array_intersect($filter_result, $filter_status_result);
    }
    if (!(empty($_GET['operador']))) {
        $filter_operator_result = array();

        $operator_id = $_GET['operador'];

        $operator_ids = $conn->prepare('SELECT billing_id FROM billings WHERE user__id=:operator_id');
        $operator_ids->execute(array('operator_id' => $operator_id));

        while ($order_id = $operator_ids->fetch()) {
            array_push($filter_operator_result, $order_id['billing_id']);
        }

        $filter_result = array_intersect($filter_result, $filter_operator_result);
    }
}




?>
<div class="container-fluid">
    <!-- row -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Listagem de repasses de operadores</h4>
                    <div class="d-flex mb-3">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-rounded btn-outline-success mr-2" data-toggle="dropdown" aria-expanded="true"><i class="fas fa-download scale2 mr-2"></i> Exportar</button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="#" id="export-to-csv"><i class="fas fa-file-csv scale2 mr-2"></i></i> CSV</a>
                    <a class="dropdown-item" href="#" id="export-to-xlsx"><i class="fas fa-file-excel scale2 mr-2"></i> Excel</a>
                    <a class="dropdown-item" href="#" id="export-to-pdf"><i class="fas fa-file-pdf scale2 mr-2"></i> PDF</a>
                </div>
            </div>
            <button type="button" class="btn btn-rounded btn-success filter-btn" class="btn btn-success text-nowrap"><i class="fas fa-sliders-h scale2 mr-2" aria-hidden="true"></i>Filtros</button>
          
        </div>
       
    </div>

    
    <div class="table-responsive" style="overflow-x: visible;">
        <table id="exp-transfer-list" class="table card-table display dataTablesCard" data-page-length='50' data-order='[[0, "desc"]]'>
            <thead>
                <tr>
                    <th class="col-md-2 text-center">Data</th>
                    <th class="col-md-2"> Nome</th>
                    <th class="col-md-2 text-center">Valor</th>
                    <th class="col-md-2 text-center">Status</th>
                    <th class="col-md-1">Comprovante</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $stmt->fetch()) {
                    if (isset($_GET['filtro']) && $_GET['filtro'] == 'ativo') {
                        if (!(in_array($row['billing_id'], $filter_result))) {
                            continue;
                        }
                    }
                ?>
                <tr>
                    <td style="text-align: center;"><?php echo date_format(date_create($row['billing_request']), 'd/m H:i'); ?></td>
                    <td><?php echo $row["full_name"] ?></td>
                    <td style="text-align: center;"><?php echo "R$ " . number_format($row['billing_value'], 2, ",", "."); ?></td>
                    <?php
                        switch ($row['billing_status']) {
                            case 1:
                                $btn_classes = "btn-danger";
                                $status_string = "Reprovada";
                                $data_order = 2;
                                break;
                            case 0:
                                $btn_classes = "btn-warning";
                                $status_string = "Pendente";
                                $data_order = 1;
                                break;
                            case 2:
                                $btn_classes = "btn-success";
                                $status_string = "Aprovada";
                                $data_order = 3;
                            break;
                        }
                    ?>
                    <td class="center text-center" data-order="<?php echo $data_order; ?>">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn <?php echo $btn_classes ?> dropdown-toggle btn-xs" data-toggle="dropdown" aria-expanded="false"><?php echo $status_string; ?></button>
                            <div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 42px, 0px);">
                                <a class="dropdown-item approve-transfer" data-action="<?php echo $row["billing_id"] ?>" href="#">Aprovar</a>
                                <a class="dropdown-item disapprove-transfer" data-action="<?php echo $row["billing_id"] ?>" href="#">Reprovar</a>
                            </div>
                        </div>
                    </td>
                    <td style="text-align: center;">
                        <a title="Ver comprovante de trasnferência" <?php
                        if($row['billing_proof'] == NULL) {
                            echo "href='#'";
                        } else {
                            echo "href='" . SERVER_URI . "/uploads/repasses/comprovantes/" . $row['billing_proof'] . "' target='_blank'"; 
                        }?>>
                        <i class="fa fa-eye<?php if ($row['billing_proof'] == NULL) { echo '-slash';}?>"></i>
                        </a>
                    </td>
                </tr>
                <?php }?>
            </tbody>
        </table>
    </div>
                </div>
                
                <div class="card-footer">

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
                    <div class="row">
                        <div class="col-lg-12 mb-2">
                            <form method="GET">
                                <div class="mb-3">
                                    <p class="mb-1">por Data</p>
                                    <input type="hidden" class="form-control mb-2" name="filtro" value="ativo">
                                    <input name="data-inicio" placeholder="Do dia ..." class="datepicker-default form-control picker__input mb-2" id="datepicker" readonly="" aria-haspopup="true" aria-expanded="false" aria-readonly="false" aria-owns="datepicker_root">
                                    <input name="data-final" placeholder=".. ao dia" class="datepicker-default form-control picker__input" id="datepicker" readonly="" aria-haspopup="true" aria-expanded="false" aria-readonly="false" aria-owns="datepicker_root">
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
                                <p class="mb-1">por Status</p>
                                    <select class="form-control default-select" id="select-filter-status-id">
                                        <option selected disabled>Todos</option>
                                        <option value="pendente">Pendente</option>
                                        <option value="reprovado">Recusado</option>
                                        <option value="aprovado">Aprovado</option>
                                    </select>
                                    <input type="hidden" id="text-filter-status-id" name="status" value="">
                                    
                                 <div class="form-group">
                                        <label class="text-label"><small>por Operador Logístico</small></label>
                                        <select id="select-operators" class="d-block default-select" data-live-search="true">
                                            <option id="all-operators-option" value="" selected>Todos</option>
                                            <?php
                                                $get_logistic_operators = $conn->prepare("SELECT * FROM logistic_operator lo INNER JOIN users u ON u.user__id = lo.user_id");
                                                $get_logistic_operators->execute();

                                                while($operator = $get_logistic_operators->fetch()) {
                                                    if(isset($_GET['operador']) && $operator['user_id'] == $_GET['operador']) {
                                                        echo "<option selected value='" . $operator["user_id"] ."'>" . $operator["full_name"] . "</option>";
                                                    } else {
                                                        echo "<option value='" . $operator["user_id"] ."'>" . $operator["full_name"] . "</option>";
                                                    }
                                                }
                                           ?>
                                        </select>
                                        <input type="hidden" id="text-select-operators" name="operador" value="">
                                    </div>

                                </div>
                                <button type="submit" class="btn btn-block btn-success text-nowrap mt-2"><i class="fas fa-sliders-h" aria-hidden="true"></i> Aplicar Filtros</button>
                                <a href="<?php echo SERVER_URI; ?>/operadores/repasses" class="btn btn-block mt-2">Limpar Filtros</a>
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
