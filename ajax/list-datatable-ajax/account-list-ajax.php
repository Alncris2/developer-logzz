<?php
error_reporting(-1);            
ini_set('display_errors', 1);  

header('Content-Type: application/json; charset=utf-8');
require (dirname(__FILE__)) . "/../../includes/config.php";
session_name(SESSION_NAME);
session_start();



$filter_data = $_POST['filter_data'];
parse_str($filter_data, $params);
$UserPlan = $_SESSION['UserPlan'];
$user__id = $_SESSION['UserID'];

$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
$columnIndex = $_POST['order'][0]['column']; // Column index
$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc

if ($columnName == "date") {
    $columnOrder = "account_date_request" ;

} elseif ($columnName == "user") {
    $columnOrder = "full_name";

} elseif ($columnName == "bank") {
    $columnOrder = "account_bank";

} elseif ($columnName == "agency") {
    $columnOrder = "account_agency";

} elseif ($columnName == "account") {
    $columnOrder = "account_number";

} elseif ($columnName == "type") {
    $columnOrder = "account_type";
    
} elseif ($columnName == "keypix") {
    $columnOrder = "account_pix_key";
    
} elseif ($columnName == "document") {
    $columnOrder = "company_doc";
    
} elseif ($columnName == "status") {
    $columnOrder = "account_status";  
}


$verify_if_have_any_filter_active = !(empty($params['usuario'])) || !(empty($params['banco'])) || !(empty($params['chavepix'])) || !(empty($params['status']));

if ($verify_if_have_any_filter_active) {

    # Filtro por DATA
    $filter_initial_result = array();

    $all_accounts = $conn->prepare('SELECT account_id FROM bank_account_list');
    $all_accounts->execute();

    while ($all_account = $all_accounts->fetch()) {
        array_push($filter_initial_result, $all_account['account_id']);
    }

    $filter_result = $filter_initial_result;

    //Filtro por NOME DO USÚARIO
    if (!(empty($params['usuario']))) {
        $filter_user_result = array();

        $usuario = $params['usuario'];

        $user_ids = $conn->prepare('SELECT account_id, full_name  FROM bank_account_list INNER JOIN users ON user__id = account_user_id WHERE full_name LIKE :name');
        $user_ids->execute(array('name' => '%' . $usuario . '%'));

        while ($user_id = $user_ids->fetch()) {
            array_push($filter_user_result, $user_id['account_id']);
        }

        $filter_result = array_intersect($filter_result, $filter_user_result);
    }

    //Filtro por BANCO
    if (!(empty($params['banco']))) {
        $filter_bank_result = array();

        $banco = $params['banco'];

        $bank_ids = $conn->prepare('SELECT account_id, bank_name  FROM bank_account_list INNER JOIN bank_list ON bank_number = account_bank WHERE bank_name LIKE :banco');
        $bank_ids->execute(array('banco' => '%' . $banco . '%'));

        while ($bank_id = $bank_ids->fetch()) {
            array_push($filter_bank_result, $bank_id['account_id']);
        }

        $filter_result = array_intersect($filter_result, $filter_bank_result);
    }

    //Filtro por CHAVE PIX
    if (!(empty($params['chavepix']))) {

        $filter_keypix_result = array();
        $keypix = addslashes($params['chavepix']);

        $keypix_ids = $conn->prepare('SELECT account_id, account_pix_key FROM bank_account_list  WHERE account_pix_key LIKE :keypix');
        $keypix_ids->execute(array('keypix' =>'%' . $keypix . '%' ));

        while ($keypix_id = $keypix_ids->fetch()) {
            array_push($filter_keypix_result, $keypix_id['account_id']);
        }

        $filter_result = array_intersect($filter_result, $filter_keypix_result);
    }

    //Filtro por STATUS
    if (!(empty($params['status']))) {

        $filter_status_result = array();
        $status = addslashes($params['status']);

        $status_ids = $conn->prepare('SELECT account_id, account_status FROM bank_account_list WHERE account_status = :status');
        $status_ids->execute(array('status' => $status - 1));

        while ($status_id = $status_ids->fetch()) {
            array_push($filter_status_result, $status_id['account_id']);
        }

        $filter_result = array_intersect($filter_result, $filter_status_result);
    }
}

if (!$verify_if_have_any_filter_active) {
    
    $query = "";
    $stmt = $conn->prepare("SELECT account_id, account_user_id, account_bank, account_agency, account_number, account_type, account_pix_type, account_pix_key, account_status, account_date_request, user__id, full_name, company_doc FROM bank_account_list INNER JOIN users ON account_user_id = user__id");
    $stmt->execute();
    $num_filter_row = $stmt->rowCount();

    #Busca todos as contas cadastradas dos usuários.
    $query = "SELECT account_id, account_user_id, account_bank, account_agency, account_number, account_type, account_pix_type, account_pix_key, account_status, account_date_request, user__id, full_name, company_doc FROM bank_account_list INNER JOIN users ON account_user_id = user__id";
    $query .= " ORDER BY $columnOrder $columnSortOrder ";
    $query .= " LIMIT $row, $rowperpage ";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $get_bank_acc_list = $stmt->fetchAll();
    $number_row = $stmt->rowCount();

    $filter = false;
 } else {

    $result = "'" . implode("','", $filter_result) . "'";

    $query = "";
    $stmt = $conn->prepare("SELECT account_id, account_user_id, account_bank, account_agency, account_number, account_type, account_pix_type, account_pix_key, account_status, account_date_request, user__id, full_name, company_doc FROM bank_account_list INNER JOIN users ON account_user_id = user__id WHERE account_id IN ($result)");
    $stmt->execute();
    $num_filter_row = $stmt->rowCount();

    #Busca todos as contas cadastradas dos usuários.
    $query = "SELECT account_id, account_user_id, account_bank, account_agency, account_number, account_type, account_pix_type, account_pix_key, account_status, account_date_request, user__id, full_name, company_doc FROM bank_account_list INNER JOIN users ON account_user_id = user__id WHERE account_id IN ($result)";
    $query .= " ORDER BY $columnOrder $columnSortOrder ";
    $query .= " LIMIT $row, $rowperpage ";
    $stmt = $conn->prepare($query);
    $stmt->execute();

    $get_bank_acc_list = $stmt->fetchAll();
    $number_row = $stmt->rowCount();
    $filter = true;
}

foreach ($get_bank_acc_list as $row) {  

        $bank_name = bankName($row['account_bank']);
        $agency = $row['account_agency'];
        $number = $row['account_number'];
        $pix = $row['account_pix_type'];
        $key = $row['account_pix_key'];
        $doc = $row['company_doc'];
        $user = $row['full_name'];
        $date = $row['account_date_request'];

        if (strlen($user) > 10 && preg_match("/ /", $user)) {
            $client_name = explode(" ", $user);
            if (strlen($client_name[1]) > 4) {
                $user = $client_name[0] . " " . @$client_name[1];
            } else {
                $user = $client_name[0] . " " . @$client_name[1] . " " . @$client_name[2];
            }
        } else {
            $user = $user;
        }

        if ($row['account_type'] == 1){
            $type = "Corrente";
        } else {
            $type = "Poupança";
        }

        switch ($row['account_status']) {
            case 0:
                $btn_classes = "btn-danger";
                $status_string = "Reprovada";
                $data_order = 2;
                break;
            case 1:
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
        $htmlstatus = "<div class='btn-group' role='group'>
            <button type='button' class='btn ". $btn_classes ." dropdown-toggle btn-xs' data-toggle='dropdown' aria-expanded='false' id='bank-account-status-btn-". $row['account_id'] ."'>". $status_string ."</button>
            <div class='dropdown-menu' x-placement='bottom-start' style='position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 42px, 0px);'>";
        if($row['account_status'] != 2) $htmlstatus .= "<a class='dropdown-item' onclick='approveBankAccount(this)' data-action='2' data-id='". $row['account_id'] ."' href='#' data-btn-id='bank-account-status-btn-". $row['account_id'] ."'>Aprovar</a>";
        if($row['account_status'] != 0) $htmlstatus .= "<a class='dropdown-item' onclick='disapproveBankAccount(this)' data-action='0' data-id='". $row['account_id'] ."' href='#' data-toggle='modal' data-target='#ModalReprovarConta'>Reprovar</a>";
            $htmlstatus .="</div>
        </div>";

    $aaData[] = array(
        "date"          => date_format(date_create($date), 'd/m/y'), 	    // data
        "user"          => $user, 	                                                    //Pedido
        "bank"          => $bank_name, 	                                                // Cliente
        "agency"        => $agency, 	                                                // Produto
        "account"       => $number,                                                     // Qnt
        "type"          => $type,	                                                    // Entreg
        "keypix"        => $key,	                                                    // Fatur
        "document"      => $doc,                                                        // Comis
        "status"        => $htmlstatus,                                                 // options
    );
}

$json_data = array(
    "draw"              => intval($draw),
    "recordsTotal"      => intval($number_row),
    "recordsFiltered"   => intval($num_filter_row),
    "aaData"            => $aaData,
    "filter"            => $filter
);

echo json_encode($json_data);
