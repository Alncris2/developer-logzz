<?php

require (dirname(__FILE__)) . "/../../includes/config.php";
session_name(SESSION_NAME);
session_start();


$filter_data = $_POST['filter_data'];
parse_str($filter_data, $params);

$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
$columnIndex = $_POST['order'][0]['column']; // Column index
$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc

if ($columnName == "data") {
    $columnOrder = "i.created_at";
} elseif ($columnName == "product") {
    $columnOrder = "p.product_name";
} elseif ($columnName == "user") {
    $columnOrder = "u.full_name";
} elseif ($columnName == "platform") {
    $columnOrder = "i.integration_platform";
} elseif ($columnName == "status") {
    $columnOrder = "i.status";
}

$verify_if_have_any_filter_active = !(empty($params['data-inicio'])) || !(empty($params['data-final'])) || !(empty($params['id-cliente'])) || !(empty($params['produto'])) || !(empty($params['status'])) || !(empty($params['platform']));

//Verfica se os filtros estão ativos
if ($verify_if_have_any_filter_active) {

    # Filtro por DATA
    $filter_data_result = array();

    if (!(empty($params['data-inicio']))) {
        $start_date = pickerDateFormate($params['data-inicio']);
        $start_date = explode(" ", $start_date);
        $start_date = $start_date[0] . " 00:00:00";
    } else {
        $start_date = '2010-01-01';
    }

    if (!(empty($params['data-final']))) {
        $final_date = pickerDateFormate($params['data-final']);
        $final_date = explode(" ", $final_date);
        $final_date = $final_date[0] . " 23:59:59";
    } else {
        $final_date = date('Y-m-d') . " 23:59:59";
    }

    $data_ids = $conn->prepare('SELECT integration_id FROM integrations WHERE created_at BETWEEN :start_date AND :final_date');
    $data_ids->execute(array('start_date' => $start_date, 'final_date' => $final_date));

    while ($data_id = $data_ids->fetch()) {
        array_push($filter_data_result, $data_id['integration_id']);
    }

    $filter_result = $filter_data_result;

    //Filtro por CLIENTE
    if (!(empty($params['id-cliente']))) {
        $filter_name_result = array();

        $cliente_id = $params['id-cliente'];

        $cliente_name_ids = $conn->prepare('SELECT i.integration_id FROM integrations i WHERE i.integration_user_id = :cliente_id');
        $cliente_name_ids->execute(array('cliente_id' => $cliente_id));

        while ($cliente_name_id = $cliente_name_ids->fetch()) {
            array_push($filter_name_result, $cliente_name_id['integration_id']);
        }

        $filter_result = array_intersect($filter_result, $filter_name_result);
    }

    //Filtro por NOME DO PRODUTO
    if (!(empty($params['produto']))) {
        $filter_sale_result = array();

        $product = $params['produto'];

        $product_ids = $conn->prepare('SELECT integration_id FROM integrations WHERE integration_product_id = :product');
        $product_ids->execute(array('product' => $product));

        while ($product_id = $product_ids->fetch()) {
            array_push($filter_sale_result, $product_id['integration_id']);
        }

        $filter_result = array_intersect($filter_result, $filter_sale_result);
    }

    //Filtro por STATUS
    if (!(empty($params['status']))) {
        $filter_sale_result = array();

        $status = $params['status'];

        $status_ids = $conn->prepare('SELECT integration_id FROM integrations WHERE status = :o_status');
        $status_ids->execute(['o_status' => $status - 1]);


        while ($status_id = $status_ids->fetch()) {
            array_push($filter_sale_result, $status_id['integration_id']);
        }

        $filter_result = array_intersect($filter_result, $filter_sale_result);
    }

    //Filtro por PLATAFORMA
    if (!(empty($params['platform']))) {
        $filter_sale_result = array();

        $status = strtolower($params['platform']);

        $status_ids = $conn->prepare('SELECT integration_id FROM integrations WHERE integration_platform = :name_platform');
        $status_ids->execute(['name_platform' => $status]);


        while ($status_id = $status_ids->fetch()) {
            array_push($filter_sale_result, $status_id['integration_id']);
        }

        $filter_result = array_intersect($filter_result, $filter_sale_result);
    }
}

if (!$verify_if_have_any_filter_active) {

    $stmt = $conn->prepare("SELECT i.*, p.product_id, p.product_code, p.product_name, u.user__id, u.user_code, u.full_name FROM integrations i INNER JOIN users u ON i.integration_user_id = u.user__id INNER JOIN products p ON i.integration_product_id = p.product_id");
    $stmt->execute();
    $num_filter_row = $stmt->rowCount();

    $query = "SELECT i.*, p.product_id, p.product_code, p.product_name, u.user__id, u.user_code, u.full_name FROM integrations i INNER JOIN users u ON i.integration_user_id = u.user__id INNER JOIN products p ON i.integration_product_id = p.product_id";

    if ($columnName == "data" || $columnName == "product" || $columnName == "user" || $columnName == "platform" || $columnName == "status") {
        $query .= " ORDER BY $columnOrder $columnSortOrder ";
    }
    $query .= " LIMIT $row, $rowperpage ";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $data_users_integration = $stmt->fetchAll();
    $number_row = $stmt->rowCount();
    $filter = false;
    
} else {
    $result = "'" . implode("','", $filter_result) . "'";

    $stmt = $conn->prepare("SELECT * FROM integrations i WHERE i.integration_id IN ($result)");
    $stmt->execute();
    $num_filter_row = $stmt->rowCount();

    $query = "SELECT i.*, p.product_id, p.product_code, p.product_name, u.user__id, u.user_code, u.full_name FROM integrations i INNER JOIN users u ON i.integration_user_id = u.user__id INNER JOIN products p ON i.integration_product_id = p.product_id  WHERE i.integration_id IN ($result)";

    if ($columnName == "data" || $columnName == "product" || $columnName == "user" || $columnName == "platform" || $columnName == "status") {
        $query .= " ORDER BY $columnOrder $columnSortOrder ";
    }
    $query .= " LIMIT $row, $rowperpage ";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $data_users_integration = $stmt->fetchAll();
    $number_row = $stmt->rowCount();
    $filter = true;
}

foreach ($data_users_integration as $row) {

    $is_integrated = $row['status'] == 0 || $row['status'] == null ? false : true;

    // PEGAR QUANTIDADE DE ESTOQUE EM CENTRO DE DISTRIBUIÇÃO 
    $integration_user_id = $row['integration_user_id'];

    $inventory_meta = $row['integration_user_id'] . "-" . $row['integration_product_id'] . "-" . '1'; 
    $qtd_inventory = $conn->prepare("SELECT SUM(i.inventory_quantity) AS total FROM inventories AS i WHERE inventory_meta = :inventory_meta AND i.ship_locale = 1");
    $qtd_inventory->execute(['inventory_meta' => $inventory_meta]);

    $actual_qtd_inventory = $qtd_inventory->fetch(\PDO::FETCH_ASSOC)['total'];
    
    $htmlimg_plataform = $row['integration_platform'] == "braip" ? "<img src='/images/integrations/logos/braip.png' alt='Braip' width='70px'>" : "<img src='/images/integrations/logos/monetizze.png' alt='Monetizze' width='70px'>";

    $htmlbtnS_pendente = "<button class='badge badge-xs light badge-warning mb-1' data-userCode='" . $row['user_code'] . "' data-productCode='" . $row['product_code'] . "' data-url='" . $row['integration_url'] . "' onclick='saveDataInSessionAndRedirectToTinyIntegration(this);'>Pendente</button>";

    $htmlbtnS_integrado = "<button class='badge badge-xs badge-success mb-1' data-userCode='" . $row['user_code'] . "' data-productCode='" . $row['product_code'] . "' data-url='" . $row['integration_url'] . "'>Integrado</button>";

    $htmlbtn_action = "<button class='badge badge-xs light badge-warning' style='display:block; margin:auto;' data-idToDelete='" . $row['integration_id'] . "' data-url='" . $row['integration_url'] . "' onclick=deleteIntegration(this);><i class='fa fa-trash'></i></button>";

    $data[] = array(
        "data"      => date('d/m/Y', strtotime($row['created_at'])),
        "product"   => $row['product_name'],
        "user"      => $row['full_name'] . '<span class="text-secondary"> (' . $row['user_code'] . ')</span>',
        "platform"  => $htmlimg_plataform,
        "stock"     => $actual_qtd_inventory !== null ? $actual_qtd_inventory : '0',
        "status"    => $is_integrated  === true ? $htmlbtnS_integrado : $htmlbtnS_pendente,
        "action"    => $htmlbtn_action
    );
}

$json_data = array(
    "draw"              => intval($draw),
    "recordsTotal"      => intval($number_row),
    "recordsFiltered"   => intval($num_filter_row),
    "aaData"            => $data,
    "filter"            => $filter,
);

echo json_encode($json_data);
