<?php
error_reporting(-1);            
ini_set('display_errors', 1);      
require (dirname(__FILE__)) . "/../../includes/config.php";
error_reporting(0);

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

if ($columnName == "date") {
    $columnOrder = "created_at";
} elseif ($columnName == "name") {
    $columnOrder = "full_name";
} elseif ($columnName == "email") {
    $columnOrder = "email";
} elseif ($columnName == "plan") {
    $columnOrder = "user_plan";
} elseif ($columnName == "tax") {
    $columnOrder = "user_plan_tax";
} elseif ($columnName == "freight") {
    $columnOrder = "user_plan_shipping_tax";
} elseif ($columnName == "action") {
    $columnOrder = "user__id";
}

$verify_if_have_any_filter_active = !(empty($params['usuario'])) || !(empty($params['email'])) || !(empty($params['plan'])) || !(empty($params['documento']));

if ($verify_if_have_any_filter_active) { 

    $filter_initial_result = array();

    $all_users = $conn->prepare('SELECT user__id FROM users');
    $all_users->execute();

    while ($all_user = $all_users->fetch()) {
        array_push($filter_initial_result, $all_user['user__id']);
    }

    $filter_result = $filter_initial_result;

    //Filtro por NOME DO USÚARIO
    if (!(empty($params['usuario']))) {
        $filter_user_result = array();

        $name = $params['usuario'];

        $user_ids = $conn->prepare('SELECT user__id FROM users WHERE full_name LIKE :name');
        $user_ids->execute(array('name' => '%' . $name . '%'));

        while ($user_id = $user_ids->fetch()) {
            array_push($filter_user_result, $user_id['user__id']);
        }

        $filter_result = array_intersect($filter_result, $filter_user_result);
    }

    //Filtro por EMAIL DO USÚARIO
    if (!(empty($params['email']))) {
        $filter_email_result = array();

        $email = $params['email'];

        $user_ids = $conn->prepare('SELECT user__id FROM users WHERE email LIKE :email');
        $user_ids->execute(array('email' => '%' . $email . '%'));

        while ($user_id = $user_ids->fetch()) {
            array_push($filter_email_result, $user_id['user__id']);
        }

        $filter_result = array_intersect($filter_result, $filter_email_result);
    }

    //Filtro por PLANO
    if (!(empty($params['plan']))) {

        $filter_plan_result = array();
        $plan = addslashes($params['plan']);

        $user_ids = $conn->prepare('SELECT u.user__id, s.user_plan FROM users u INNER JOIN subscriptions s ON u.user__id = s.user__id WHERE s.user_plan = :plan');
        $user_ids->execute(array('plan' => $plan));

        while ($user_id = $user_ids->fetch()) {
            array_push($filter_plan_result, $user_id['user__id']);
        }

        $filter_result = array_intersect($filter_result, $filter_plan_result);
    }    

    //Filtro por NOME DO USÚARIO
    if (!(empty($params['documento']))) { 
        $filter_document_result = array();

        $document = $params['documento'];

        $user_ids = $conn->prepare('SELECT user__id FROM users WHERE company_doc LIKE :document');
        $user_ids->execute(array('document' =>  $document . '%'));

        while ($user_id = $user_ids->fetch()) {
            array_push($filter_document_result, $user_id['user__id']);
        }  

        $filter_result = array_intersect($filter_result, $filter_document_result);
    }
}


if (!$verify_if_have_any_filter_active) {

    $stmt = $conn->prepare("SELECT users.user__id, full_name, created_at,  email, user_code, user_plan, user_plan_tax, subscriptions.user_plan_shipping_tax, active FROM users INNER JOIN subscriptions ON users.user__id = subscriptions.user__id");
    $stmt->execute();
    $num_filter_row = $stmt->rowCount();

    $query = "SELECT users.user__id, full_name, created_at, email,user_phone, user_code, user_plan, user_plan_tax, subscriptions.user_plan_shipping_tax, active FROM users INNER JOIN subscriptions ON users.user__id = subscriptions.user__id";
    $query .= " ORDER BY $columnOrder $columnSortOrder ";
    $query .= " LIMIT $row, $rowperpage ";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $get_all_users = $stmt->fetchAll();
    $number_row = $stmt->rowCount();

    $filter = false;
} else {

    $result = "'" . implode("','", $filter_result) . "'";

    $stmt = $conn->prepare("SELECT users.user__id, full_name, created_at, email,user_phone, user_code, user_plan, user_plan_tax, subscriptions.user_plan_shipping_tax, active FROM users INNER JOIN subscriptions ON users.user__id = subscriptions.user__id WHERE users.user__id IN ($result)");
    $stmt->execute();
    $num_filter_row = $stmt->rowCount();

    $query = "SELECT users.user__id, full_name, created_at, email,user_phone, user_code, user_plan, user_plan_tax, subscriptions.user_plan_shipping_tax, active FROM users INNER JOIN subscriptions ON users.user__id = subscriptions.user__id WHERE users.user__id IN ($result)";
    $query .= " ORDER BY $columnOrder $columnSortOrder ";
    $query .= " LIMIT $row, $rowperpage ";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $get_all_users = $stmt->fetchAll();
    $number_row = $stmt->rowCount();

    $filter = true;
}
    

foreach ($get_all_users as $row) {
    $user_plan_string = userPlanString($row['user_plan']);

    $is_integrated = $row['status'] == 0 || $row['status'] == null ? false : true;

    // PEGAR QUANTIDADE DE ESTOQUE EM CENTRO DE DISTRIBUIÇÃO 
    $namehtml= utf8_encode(utf8_decode($row['full_name'])) . ' <small>[<a data-toggle="tooltip" data-placement="top" title="Clique para copiar" onclick="copyUserCode(this)" data-code="' . $row['user_code'] . '">' . $row['user_code'] . '</a>]</small>';

    if ($row['active'] != 0) {
        $action = "<a href='". SERVER_URI ."/usuario/". $row['user_code'] ."/' title='Alterar Dados' class='btn btn-primary btn-xs sharp mr-1' style='float: left;'><i class='fas fa-pencil-alt'></i></a>
        <a href='". SERVER_URI ."/usuario/financeiro/". $row['user__id'] ."/". $row['user_code'] ."' title='Ver Financeiro' class='btn btn-info btn-xs sharp mr-1' style=' float: left;'><i class='fas fa-wallet'></i></a>";
    } else {
        $action = "<button onclick='autorizarAcesso(". $row['user__id'] .")' style='font-size: 0.6em;padding: 1px 5px;' class='badge badge-pill badge-secondary'>Inativo</button>";
    }
    
    $data[] = array(
        "date"      => date_format(date_create($row['created_at']), 'd/m/y'),
        "name"      => $namehtml,
        "email"     => $row['email'],
        "user_phone"     => $row['user_phone'],
        "plan"      => $user_plan_string,
        "tax"       => $row['user_plan_tax'] * 100 . "%",
        "freight"   => "R$ " . number_format($row['user_plan_shipping_tax'], 2, ',', ''),
        "action"    => $action,
    );
}

$json_data = array(
    "draw"              => intval($draw),
    "recordsTotal"      => intval($number_row),
    "recordsFiltered"   => intval($num_filter_row),
    "aaData"            => $data,
    "quary"             => $query,
    "filter"            => $result,
);

echo json_encode($json_data);