<?php
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);     
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
} elseif ($columnName == "comission") {
    $columnOrder = "level_user"; 
} elseif ($columnName == "last_deal") { 
    $columnOrder = "last_deal"; 
} elseif ($columnName == "level_user") {
    $columnOrder = "level_user"; 
}

// if (!$conn->querry('CREATE OR REPLACE VIEW conquest_users AS SELECT u.user__id AS user__id, u.created_at AS created_at, u.full_name AS full_name, u.user_code AS user_code, max(o.order_date) AS last_deal, tm1.meta_value AS level_user FROM users u left join orders o on(o.user__id = u.user__id) left join transactions_meta tm1 on(tm1.user__id = u.user__id and tm1.meta_key like 'total_comission') GROUP BY u.user__id') {
//     echo("<br> Error description: " . $conn->error);  
// }

$verify_if_have_any_filter_active = !(empty($params['comissao-min'])) || !(empty($params['comissao-max'])) || !(empty($params['data-inicio'])) || !(empty($params['data-final'])) || !(empty($params['usuario'])) || !(empty($params['codigo'])) || !(empty($params['level']));

if ($verify_if_have_any_filter_active) { 
 
    $filter_data_result = array();
    if (!(empty($params['data-inicio']))) {
        $start_date = pickerDateFormate($params['data-inicio']);
        $start_date = explode(" ", $start_date);
        $start_date = $start_date[0] ." 00:00:00";
    } else {
        $start_date = '2010-01-01 00:00:00'; 
    }

    if (!(empty($params['data-final']))) {
        $final_date = pickerDateFormate($params['data-final']);
        $final_date = explode(" ", $final_date);
        $final_date = $final_date[0] ." 23:59:59";
    } else {
        $final_date = date('Y-m-d') ." 23:59:59";
    }
      
    $date_ids = $conn->prepare("SELECT user__id FROM conquest_users WHERE last_deal BETWEEN :start_date AND :final_date ");
    $date_ids->execute(array('start_date' => $start_date, 'final_date' => $final_date)); 

    while ($date_id = $date_ids->fetch()) {
        array_push($filter_data_result, $date_id['user__id']); 
    }

    $filter_result = $filter_data_result;

    // echo '<br>Datas'. implode('","', $filter_result); 
    

    //Filtro por NOME DO USÚARIO
    if (!(empty($params['usuario']))) {
        $filter_user_result = array();

        $name = $params['usuario'];

        $user_ids = $conn->prepare('SELECT user__id FROM conquest_users WHERE full_name LIKE :name');
        $user_ids->execute(array('name' => '%' . $name . '%'));

        while ($user_id = $user_ids->fetch()) {
            array_push($filter_user_result, $user_id['user__id']);
        }

        $filter_result = array_intersect($filter_result, $filter_user_result);
        // echo '<br>Usuario'. implode('","', $filter_result);
    }

    //Filtro por PLANO
    if (!(empty($params['level']))) {

        $filter_level_result = array();
        $level = addslashes($params['level']); 
        $level--;  

        $strin_filter_level = ''; 
        if ($level == 0)  
            $strin_filter_level = '(CASE WHEN level_user IS NULL THEN level_user IS NULL ELSE level_user BETWEEN 0 AND 10000 END)'; 
        if ($level == 1) 
            $strin_filter_level = 'level_user BETWEEN 10000 AND 50000';
        if ($level == 2)
            $strin_filter_level = 'level_user BETWEEN 50000 AND 100000';
        if ($level == 3)
            $strin_filter_level = 'level_user BETWEEN 100000 AND 500000';
        if ($level == 4)
            $strin_filter_level = 'level_user BETWEEN 500000 AND 1000000';
        if ($level == 5)
            $strin_filter_level = 'level_user BETWEEN 1000000 AND 5000000';
        if ($level == 6)   
            $strin_filter_level = 'level_user > 5000000'; 

        $user_ids = $conn->prepare("SELECT user__id FROM conquest_users WHERE $strin_filter_level");
        $user_ids->execute();

        while ($user_id = $user_ids->fetch()) {
            array_push($filter_level_result, $user_id['user__id']);
        }

        $filter_result = array_intersect($filter_result, $filter_level_result);
        // echo '<br>Level'. implode('","', $filter_result);           
    }    

    //Filtro por NOME DO USÚARIO
    if (!(empty($params['codigo']))) { 
        $filter_document_result = array();

        $codigo = $params['codigo'];

        $user_ids = $conn->prepare('SELECT user__id FROM conquest_users WHERE user_code LIKE :codigo');
        $user_ids->execute(array('codigo' =>  $codigo)); 

        while ($user_id = $user_ids->fetch()) {
            array_push($filter_document_result, $user_id['user__id']);
        }  

        $filter_result = array_intersect($filter_result, $filter_document_result);   
    }

    // echo '<br>Ante comissão'. implode('","', $filter_result);  
    //Filtro por NOME DO USÚARIO 
    if (!(empty($params['comissao-min'])) || !(empty($params['comissao-max']))) {
        $filter_comission_result = array(); 
        $comissao_min = floatval(addslashes(str_replace(',', '.', str_replace('.', '', $params['comissao-min']))));            
        $comissao_max = floatval(addslashes(str_replace(',', '.', str_replace('.', '', $params['comissao-max']))));

        // var_dump($comissao_min, $comissao_max);  

        if (!(empty($params['comissao-min'])) && (empty($params['comissao-max']))) {
            // echo '<br> Min';
            
            $comission_ids = $conn->prepare('SELECT user__id FROM conquest_users WHERE last_deal > :comissao_min');
            $comission_ids->execute(array('comissao_min' =>  $comissao_min)); 
        }

        if ((empty($params['comissao-min'])) && !(empty($params['comissao-max']))) { 
            // echo '<br> Max'; 

            $comission_ids = $conn->prepare('SELECT user__id FROM conquest_users WHERE (CASE WHEN last_deal IS NULL THEN last_deal IS NULL ELSE last_deal < :comissao_max END)');
            $comission_ids->execute(array('comissao_max' =>  $comissao_max)); 
        }

        if (!(empty($params['comissao-min'])) && !(empty($params['comissao-max']))) {
            // echo '<br> Min e Max';           

            if($comissao_min != 0){       
                // echo ' - Normal';
                $comission_ids = $conn->prepare('SELECT user__id FROM conquest_users WHERE last_deal BETWEEN :comissao_min AND :comissao_max');  
                $comission_ids->execute(array('comissao_min' =>  $comissao_min, 'comissao_max' =>  $comissao_max));
                               
            } else {
                // echo ' - Min is zero'; 
                $comission_ids = $conn->prepare('SELECT user__id FROM conquest_users WHERE (CASE WHEN last_deal IS NULL THEN last_deal IS NULL ELSE last_deal BETWEEN :comissao_min AND :comissao_max END)');
                $comission_ids->execute(array('comissao_min' =>  $comissao_min, 'comissao_max' =>  $comissao_max));
            }
        }
 
        $all_ids = $comission_ids->fetchAll();
        foreach ($all_ids as $comission_id) {
            array_push($filter_comission_result, $comission_id['user__id']); 
        }  

        $filter_result = array_intersect($filter_result, $filter_comission_result);
    }  
}
 

if (!$verify_if_have_any_filter_active) {

    $stmt = $conn->prepare("SELECT * FROM conquest_users");
    $stmt->execute();
    $num_filter_row = $stmt->rowCount();

    $stmt = $conn->prepare("SELECT SUM(level_user) FROM conquest_users");
    $stmt->execute();
    $total_fatured = $stmt->fetch()[0];

    $query = "SELECT * FROM conquest_users";
    $query .= " ORDER BY $columnOrder $columnSortOrder ";
    $query .= " LIMIT $row, $rowperpage ";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $get_all_conquest = $stmt->fetchAll();
    $number_row = $stmt->rowCount();

    $filter = false;
} else {

    $result = "'" . implode("','", $filter_result) . "'";

    $stmt = $conn->prepare("SELECT * FROM conquest_users WHERE user__id IN ($result)");
    $stmt->execute();
    $num_filter_row = $stmt->rowCount();

    $stmt = $conn->prepare("SELECT SUM(level_user) FROM conquest_users WHERE user__id IN ($result)");
    $stmt->execute();
    $total_fatured = $stmt->fetch()[0];

    $query = "SELECT * FROM conquest_users WHERE user__id IN ($result)";
    $query .= " ORDER BY $columnOrder $columnSortOrder ";
    $query .= " LIMIT $row, $rowperpage ";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $get_all_conquest = $stmt->fetchAll();
    $number_row = $stmt->rowCount();

    $filter = true;
}
 
foreach ($get_all_conquest as $row) {

    $namehtml = utf8_encode(utf8_decode($row['full_name'])) . ' <small>[<a data-toggle="tooltip" data-placement="top" title="Clique para copiar" onclick="copyUserCode(this)" data-code="' . $row['user_code'] . '">' . $row['user_code'] . '</a>]</small>';
    
    $raised = $row['level_user'];  
    $result_string = ''; 
    if ($raised >= 0 && $raised <= 10000) {
        $result_string = 'Sem Nível'; 
        $result_color = 'transparent; color: black';    
    } 
    if ($raised > 10000 && $raised <= 50000){
        $result_string = 'Bronze';
        $result_color = '#cd7f32'; //cd7f32
    }
    if ($raised > 50000 && $raised <= 100000){
        $result_string = 'Silver';
        $result_color = '#c0c0c0'; //c0c0c0
    }
    if ($raised > 100000 && $raised <= 500000){
        $result_string = 'Gold';
        $result_color = '#FFD700'; //FFD700  
    }
    if ($raised > 500000 && $raised <= 1000000){
        $result_string = 'Diamond';
        $result_color = '#96d5ff'; //96d5ff
    }
    if ($raised > 1000000 && $raised <= 5000000){
        $result_string = 'Black';
        $result_color = '#3e3e3e';
    }
    if ($raised > 5000000 && $raised <= 10000000)   {
        $result_string = 'Hero';  
        $result_color = '#ff8300'; 
    }  
    if ($raised > 10000000)   {
        $result_string = 'Legend';  
        $result_color = '#2fde91'; 
    }    
   
    $htmlstatus = "<span class='badge badge-xs btn-primary mb-1' style='background-color:" . $result_color . "'>" . $result_string . "</span>";

    $no_value = '--';      

    $data[] = array(
        "date"          => date_format(date_create($row['created_at']), 'd/m/y'),
        "name"          => $namehtml,     
        "comission"     => $row['level_user'] ? '<p class="text-center">R$ ' . number_format($row['level_user'], 2, ',', '.') .'<p class="text-center">': $no_value , 
        "last_deal"     => $row['last_deal'] ? '<p class="text-center">'. date_format(date_create($row['last_deal']), 'd/m/y H:i') .'<p class="text-center">' : $no_value ,
        "level_user"    => $htmlstatus   
    ); 
}

$json_data = array(
    "draw"              => intval($draw),
    "recordsTotal"      => intval($number_row),
    "recordsFiltered"   => intval($num_filter_row),
    "aaData"            => $data,    
    "filter"            => $verify_if_have_any_filter_active,
    "total"             => 'R$ ' . number_format($total_fatured, 2, ',', '.') 
);

echo json_encode($json_data); 