<?php
// error_reporting(-1);              
// ini_set('display_errors', 1);   
header('Content-Type: application/json; charset=utf-8');

require (dirname(__FILE__)) . "/../../includes/config.php";
session_name(SESSION_NAME); 
session_start();

$UserPlan = $_SESSION['UserPlan'];
$user__id = $_SESSION['UserID'];

$filter_data = $_POST['filter_data'];
parse_str($filter_data, $params);
$inventory_locale_id = $_POST['operation_id'];
$ship_locale = $_POST['ship_locale'];

$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
$columnIndex = $_POST['order'][0]['column']; // Column index
$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc


if ($columnName == "product") {
    $columnOrder = "product_name";

} elseif ($columnName == "inventory") {
    $columnOrder = "inventory_quantity";

} elseif ($columnName == "shipping") {
    $columnOrder = "inventory_last_ship";

} elseif ($columnName == "quantity") {
    $columnOrder = "inventory_last_ship_quant";

} elseif ($columnName == "price") { 
    $columnOrder = "product_price";  
}


$id = !(empty($params['id-cliente'])) && $params['id-cliente'] !== "" ? $params['id-cliente'] : $user__id;

$verify_if_have_any_filter_active = !(empty($params['id-cliente'])) || !(empty($params['produto']));

if ($verify_if_have_any_filter_active) {

    $filter_initial_result = array();

    $all_products = $conn->prepare('SELECT product_id FROM products');
    $all_products->execute();

    while ($all_product = $all_products->fetch()) {
        array_push($filter_initial_result, $all_product['product_id']);
    }

    $filter_result = $filter_initial_result;

    $filterHTLM = "";

    //Filtro por CLIENTE
    if (!(empty($params['id-cliente']))) {
        $filter_user_result = array();

        $user = $params['id-cliente'];

        $user_ids = $conn->prepare('SELECT product_id, user__id FROM products WHERE user__id = :user');
        $user_ids->execute(array('user' => $user));

        while ($user_id = $user_ids->fetch()) {
            array_push($filter_user_result, $user_id['product_id']);
        }
        $query = "SELECT full_name FROM users WHERE user__id = :user";
        $stmt = $conn->prepare($query);
        $stmt->execute(array('user' => $params['id-cliente']));
        $userName = $stmt->fetch()['full_name'];

        $filterHTLM .= "<span class='badge badge-success light'> Cliente: <b>". $userName ."</b></span>";

        $filter_result = array_intersect($filter_result, $filter_user_result);
    }

    //Filtro por PRODUTO
    if (!(empty($params['produto']))) {
        $filter_product_result = array();

        $produto = $params['produto'];

        $product_ids = $conn->prepare('SELECT product_id, product_name FROM products WHERE product_id = :produto');
        $product_ids->execute(array('produto' => $produto));

        while ($product_id = $product_ids->fetch()) {
            array_push($filter_product_result, $product_id['product_id']);
            $filterHTLM .= "<span class='badge badge-success light'> Produto: <b>". $product_id['product_name'] ."</b></span>";
        }

        $filter_result = array_intersect($filter_result, $filter_product_result);
    }   
    $filterHTLM .= "</span>";
}

# Lista os produtos em cada localidade 
$t_volume = $t_price = 0;
if (!$verify_if_have_any_filter_active) {

    if($UserPlan == 5) {
        $stmt_count = $conn->prepare("SELECT product_id, inventory_quantity, CASE WHEN inventory_quantity <> null OR inventory_quantity != '' THEN SUM(product_price * inventory_quantity) ELSE 0 END as product_total FROM products LEFT JOIN inventories ON inventory_product_id = product_id AND inventory_locale_id = :inventory_locale_id AND ship_locale = :ship_locale WHERE status = 1  GROUP BY product_id ORDER BY `inventories`.`inventory_quantity` DESC;");
        $stmt_count->execute(array('ship_locale' => $ship_locale, 'inventory_locale_id' => $inventory_locale_id)); 

        $stmt_result = $conn->prepare("SELECT product_id,product_name,product_code,inventory_quantity,inventory_last_ship,inventory_last_ship_quant, CASE WHEN inventory_quantity <> null OR inventory_quantity != '' THEN SUM(product_price * inventory_quantity) ELSE 0 END as product_price FROM products LEFT JOIN inventories ON inventory_product_id = product_id AND inventory_locale_id = :inventory_locale_id AND ship_locale = :ship_locale WHERE status = 1 AND product_trash = 0 GROUP BY product_id  ORDER BY $columnOrder $columnSortOrder LIMIT $row, $rowperpage");
        $stmt_result->execute(array('ship_locale' => $ship_locale, 'inventory_locale_id' => $inventory_locale_id));
    } else { 
        $stmt_count = $conn->prepare("SELECT product_id, inventory_quantity, CASE WHEN inventory_quantity <> null OR inventory_quantity != '' THEN SUM(product_price * inventory_quantity) ELSE 0 END as product_total FROM products LEFT JOIN inventories ON inventory_product_id = product_id AND inventory_locale_id = :inventory_locale_id AND ship_locale = :ship_locale WHERE user__id = :user__id AND status = 1 AND product_trash = 0  GROUP BY product_id ORDER BY `inventories`.`inventory_quantity` DESC;");
        $stmt_count->execute(array('user__id' => $id, 'ship_locale' => $ship_locale, 'inventory_locale_id' => $inventory_locale_id));

        $stmt_result = $conn->prepare("SELECT product_id,product_name,product_code,inventory_quantity,inventory_last_ship,inventory_last_ship_quant,CASE WHEN inventory_quantity <> null OR inventory_quantity != '' THEN SUM(product_price * inventory_quantity) ELSE 0 END as product_price FROM products LEFT JOIN inventories ON inventory_product_id = product_id AND inventory_locale_id = :inventory_locale_id AND ship_locale = :ship_locale WHERE user__id = :user__id AND status = 1 AND product_trash = 0 GROUP BY product_id ORDER BY $columnOrder $columnSortOrder LIMIT $row, $rowperpage");
        $stmt_result->execute(array('user__id' => $id, 'ship_locale' => $ship_locale, 'inventory_locale_id' => $inventory_locale_id));
    }   

    $num_filter_row = $stmt_count->rowCount();
    while($count_result = $stmt_count->fetch()){        
        $t_volume += $count_result['inventory_quantity'];
        $t_price += $count_result['product_total'];
    }

    $number_row = $stmt_result->rowCount();
    $get_product_list = $stmt_result->fetchAll();

    $filter = false; 
} else {

    $result = "'" . implode("','", $filter_result) . "'";

    if ($UserPlan == 5) {
        $stmt_count = $conn->prepare("SELECT product_id, inventory_quantity, CASE WHEN inventory_quantity <> null OR inventory_quantity != '' THEN SUM(product_price * inventory_quantity) ELSE 0 END as product_total FROM products LEFT JOIN inventories ON inventory_product_id = product_id AND inventory_locale_id = :inventory_locale_id AND ship_locale = :ship_locale WHERE product_id IN ($result) AND status = 1 AND product_trash = 0 GROUP BY product_id ORDER BY `inventories`.`inventory_quantity` DESC;");
        $stmt_count->execute(array('ship_locale' => $ship_locale, 'inventory_locale_id' => $inventory_locale_id));

        $stmt_result = $conn->prepare("SELECT product_id,product_name,product_code,inventory_quantity,inventory_last_ship,inventory_last_ship_quant, product_price FROM products LEFT JOIN inventories ON inventory_product_id = product_id AND inventory_locale_id = :inventory_locale_id AND ship_locale = :ship_locale WHERE status = 1 AND product_trash = 0 AND product_id IN ($result)  GROUP BY product_id ORDER BY $columnOrder $columnSortOrder LIMIT $row, $rowperpage");
        $stmt_result->execute(array('ship_locale' => $ship_locale, 'inventory_locale_id' => $inventory_locale_id));        
    } else {
        $stmt_count = $conn->prepare("SELECT product_id, inventory_quantity, CASE WHEN inventory_quantity <> null OR inventory_quantity != '' THEN SUM(product_price * inventory_quantity) ELSE 0 END as product_total FROM products LEFT JOIN inventories ON inventory_product_id = product_id AND inventory_locale_id = :inventory_locale_id AND ship_locale = :ship_locale WHERE user__id = :user__id AND status = 1 IN ($result) AND product_trash = 0 GROUP BY product_id ORDER BY `inventories`.`inventory_quantity` DESC;");
        $stmt_count->execute(array('user__id' => $id, 'ship_locale' => $ship_locale, 'inventory_locale_id' => $inventory_locale_id));

        $stmt_result = $conn->prepare("SELECT product_id,product_name,product_code,inventory_quantity,inventory_last_ship,inventory_last_ship_quant, CASE WHEN inventory_quantity <> null OR inventory_quantity != '' THEN SUM(product_price * inventory_quantity) ELSE 0 END as product_price FROM products LEFT JOIN inventories ON inventory_product_id = product_id AND inventory_locale_id = :inventory_locale_id AND ship_locale = :ship_locale AND product_trash = 0 WHERE user__id = :user__id AND status = 1 AND product_id IN ($result) GROUP BY product_id ORDER BY $columnOrder $columnSortOrder LIMIT $row, $rowperpage");
        $stmt_result->execute(array('user__id' => $id, 'ship_locale' => $ship_locale, 'inventory_locale_id' => $inventory_locale_id));        
    }

    $num_filter_row = $stmt_count->rowCount();
    while($count_result = $stmt_count->fetch()){         
        $t_volume += $count_result['inventory_quantity'];
        $t_price += $count_result['product_total'];
    }

    $number_row = $stmt_result->rowCount();
    $get_product_list = $stmt_result->fetchAll();

    $filter = true;
}

$aux = 0;
$aaData = [];
foreach ($get_product_list as $row) {
    # Lista a quantidade de estoque de cada produto em de cada localidade.
    $quantity = $row['inventory_quantity'] != NULL && $row['inventory_quantity'] != '' ? $row['inventory_quantity'] : 0;
    $inventory_last_ship = $row['inventory_last_ship'] != NULL && $row['inventory_last_ship'] != '' ? date_format(date_create($row['inventory_last_ship']), 'd/m/Y') : "-";
    $inventory_last_ship_quant = $row['inventory_last_ship_quant'] != NULL  && $row['inventory_last_ship_quant'] != ''  ? $row['inventory_last_ship_quant'] : 0; 
     
    $aaData[] = array(
        "product"        => $row['product_name'] .' - '. $row['product_id'], 
        "inventory"      => $quantity,
        "shipping"       => $inventory_last_ship,
        "quantity"       => $inventory_last_ship_quant,
        "price"          => "R$". number_format($row['product_price'], 2, ',', '.') 
    );
}

$json_data = array(
    "draw"              => intval($draw),
    "recordsTotal"      => intval($number_row),
    "recordsFiltered"   => intval($num_filter_row), 
    "aaData"            => $aaData,     
    "t_volume"          => number_format($t_volume, 0, '', '.') .' und', 
    "t_price"           => "R$". number_format($t_price, 2, ',', '.'), 
    "filter"            => $filter,
    "filterText"        => $filterHTLM,
);

echo json_encode($json_data);
