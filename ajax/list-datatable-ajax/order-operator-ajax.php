<?php
// error_reporting(-1);            
// ini_set('display_errors', 1);   
header('Content-Type: application/json; charset=utf-8');
require (dirname(__FILE__)) . "/../../includes/config.php";
session_name(SESSION_NAME);
session_start();
   

function monte_string_array ($input, $minus = false) {
    if(strpos($input, ',')){ 
        $array1 = explode(',', $input);
        $array2 = array();
        for ($i = 0; $i < count($array1); $i++) { 
            array_push($array2, ( $minus && is_numeric($input) ? $array1[$i] - 1 : $array1[$i] ));
        } 
        return "'" . implode("','", $array2) . "'";
    } else {
        return $minus && is_numeric($input) ? $input - 1 : "'" . $input . "'"; 
    }
}

function monte_number_array ($input, $minus = false) {
    if(strpos($input, ',')){ 
        $array1 = explode(',', $input);
        $array2 = array();
        for ($i = 0; $i < count($array1); $i++) { 
            array_push($array2, ( $minus ? $array1[$i] - 1 : $array1[$i] ));
        } 
        return "'" . implode("','", $array2) . "'";
    } else {
        return $minus ? $input - 1 : "'" . $input . "'"; 
    }
}

function number_config($number, $config = false) {
    if($config == true) {
        return number_format((float) $number,2,'.',''); 
    } 
    return number_format((float) $number,2,',','.'); 
}


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
$export = (isset($_POST['headertype']));

$get_operation_id = $conn->prepare("SELECT lo.*, u.created_at FROM logistic_operator lo INNER JOIN users u ON lo.user_id=u.user__id WHERE lo.user_id = :user__id");
$get_operation_id->execute(array("user__id" => $user__id));

$data = $get_operation_id->fetch();
$operation_id = $data["local_operation"];
$operator_id = $data["operator_id"];
$created_at = $data["created_at"]; //"2022-10-12 19:14:31"

# Busca os produtos dos pedidos
$get_product_list = $conn->prepare('SELECT DISTINCT products.* FROM products INNER JOIN orders ON products.product_id = orders.product_id INNER JOIN local_operations_orders ON local_operations_orders.order_id = orders.order_id WHERE product_trash = 0 AND local_operations_orders.operation_id = :operation_id');
$get_product_list->execute(array('operation_id' => $operation_id));

# Busca os locais da operação do usuário
$get_order_locale = $conn->prepare("SELECT id, TRIM(city) as city FROM operations_locales WHERE operation_id = :operation_id");
$get_order_locale->execute(array("operation_id" => $operation_id));

# Busca as taxas de entrega da operação do usuário
$get_delivery_taxes = $conn->prepare("SELECT * FROM operations_delivery_taxes WHERE operation_id = :operation_id AND operator_id = :operator_id");
$get_delivery_taxes->execute(array("operation_id" => $operation_id, "operator_id" => $operator_id));

$locales = array();
$cities = $get_order_locale->fetchAll();
$delivery_taxes = $get_delivery_taxes->fetchAll();

# Relaciona as taxas de entrega aos locais em um array de chave-valor
foreach ($cities as $city) {
    for ($i = 0; $i < count($delivery_taxes); $i++) {
        $tax = $delivery_taxes[$i];
        if ($city["id"] == $tax["operation_locale"]) {
            $locales[$city["city"]] = $tax["complete_delivery_tax"] . "--" . $tax["frustrated_delivery_tax"];
        }
    }
}

$referenceData = ' WHERE orders.order_date BETWEEN :start_date AND :final_date ';

if ($params['reference-data'] == 'entrega') {
    $referenceData = ' WHERE STR_TO_DATE((CASE WHEN orders.order_status = 3 THEN orders.order_delivery_date ELSE orders.order_deadline END),"%Y-%m-%d %T") between :start_date AND :final_date  ';
}

if ($columnName == "order") {
    $columnOrder = "o.order_date";

} elseif ($columnName == "client") {
    $columnOrder = "o.client_name";

} elseif ($columnName == "product") {
    $columnOrder = "o.product_name";

} elseif ($columnName == "quantity") {
    $columnOrder = "s.sale_quantity";

} elseif ($columnName == "deadline") {
    $columnOrder = "o.platform ". $columnSortOrder .", deadline";
    
} elseif ($columnName == "billing") { 
    $columnOrder = "o.order_final_price";
    
} elseif ($columnName == "commissinOp") {
    $columnOrder = "s.product_shipping_tax";
    
} elseif ($columnName == "taxmaq") {
    $columnOrder = "o.order_payment_method ". $columnSortOrder .", credit_times";    

} elseif ($columnName == "responsible") {
    $columnOrder = "lo.responsible_id";   

} elseif ($columnName == "status") {
    $columnOrder = "o.order_status";
} else {
    $columnOrder = "o.order_status";
}




$verify_if_have_any_filter_active = !(empty($params['data-inicio'])) || !(empty($params['data-final'])) || !(empty($params['nome-cliente'])) || !(empty($params['produto'])) || !(empty($params['status'])) || !(empty($params['numero-cliente-produto'])) || !(empty($params['responsavel']));

if ($verify_if_have_any_filter_active) {

    # Filtro por DATA
    $filter_data_result = array();

    if (!(empty($params['data-inicio']))) {
        $start_date = pickerDateFormate($params['data-inicio']);
        $start_date = explode(" ", $start_date);
        $start_date = $start_date[0] ." ". $params['time-inicio'];
    } else {
        $start_date = '2010-01-01';
    }

    if (!(empty($params['data-final']))) {
        $final_date = pickerDateFormate($params['data-final']);
        $final_date = explode(" ", $final_date);
        $final_date = $final_date[0] ." ". $params['time-final'];
    } else {
        $final_date = date('Y-m-d') ." ". $params['time-final'];
    }

    $date_ids = $conn->prepare("SELECT order_id FROM orders $referenceData AND order_number NOT LIKE '%AFI%' ");
    $date_ids->execute(array('start_date' => $start_date, 'final_date' => $final_date));

    while ($date_id = $date_ids->fetch()) {
        array_push($filter_data_result, $date_id['order_id']);
    }

    $filter_result = $filter_data_result;

    //Filtro por NOME DO CLIENTE
    if (!(empty($params['nome-cliente']))) {
        $filter_name_result = array();

        $cliente_name = '%' . addslashes($params['nome-cliente']) . '%';

        $name_cliente_ids = $conn->prepare('SELECT order_id FROM orders WHERE client_name LIKE :cliente_name');
        $name_cliente_ids->execute(array('cliente_name' => $cliente_name));

        while ($name_cliente_id = $name_cliente_ids->fetch()) {
            array_push($filter_name_result, $name_cliente_id['order_id']);
        }

        $filter_result = array_intersect($filter_result, $filter_name_result);
    }

    //Filtro por NOME DO PRODUTO
    if (!(empty($params['produto']))) {
        $filter_sale_result = array();

        $product = trim($params['produto']);

        $product = monte_string_array($params['produto']);

        $product_ids = $conn->prepare("SELECT order_id FROM orders WHERE product_name IN ( $product ) ");
        $product_ids->execute();

        while ($product_id = $product_ids->fetch()) {
            array_push($filter_sale_result, $product_id['order_id']);
        }

        $filter_result = array_intersect($filter_result, $filter_sale_result);
    }

    //Filtro por STATUS
    if (!(empty($params['status']))) {

        $filter_status_result = array();  
        
        $status = monte_number_array($params['status'], true);          
        $status_ids = $conn->prepare("SELECT order_id, order_status FROM orders WHERE order_status in ( $status )"); 
        $status_ids->execute();  

        while ($status_id = $status_ids->fetch()) {
            array_push($filter_status_result, $status_id['order_id']);
        }

        $filter_result = array_intersect($filter_result, $filter_status_result);
    }

    //Filtro por WHATHSAPP
    if (!(empty($params['numero-cliente-produto']))) {
        $filter_number_result = array();

        $client_number = '%' . $params['numero-cliente-produto'] . '%';

        $number_ids = $conn->prepare('SELECT order_id FROM orders WHERE client_number LIKE :client_number');
        $number_ids->execute(array('client_number' => $client_number));

        while ($number_id = $number_ids->fetch()) {
            array_push($filter_number_result, $number_id['order_id']);
        }

        $filter_result = array_intersect($filter_result, $filter_number_result);
    }

    //Filtro por RESPONSAVEL
    if (!(empty($params['responsavel']))) {
        $filter_responsible_result = array();

        $responsavel = $params['responsavel'];

        if ($responsavel == "indef") {
            $responsible_ids = $conn->prepare('SELECT DISTINCT o.order_id FROM orders o INNER JOIN local_operations_orders lo ON lo.order_id=o.order_id WHERE lo.operation_id = :operation_id AND lo.responsible_id IS NULL');
            $responsible_ids->execute(array('operation_id' => $operation_id));
        } else {
            $responsible_ids = $conn->prepare('SELECT o.order_id FROM orders o INNER JOIN local_operations_orders lo ON lo.order_id=o.order_id WHERE lo.operation_id = :operation_id AND lo.responsible_id = :responsible');
            $responsible_ids->execute(array('operation_id' => $operation_id, 'responsible' => $operator_id));
        }

        while ($responsible_id = $responsible_ids->fetch()) {
            array_push($filter_responsible_result, $responsible_id['order_id']);
        }

        $filter_result = array_intersect($filter_result, $filter_responsible_result);
    }
}
 
if (!$verify_if_have_any_filter_active) {    
    
    $query = "";
    $stmt = $conn->prepare("SELECT * FROM orders o INNER JOIN local_operations_orders lo ON lo.order_id = o.order_id INNER JOIN sales s ON o.sale_id = s.sale_id WHERE lo.operation_id = :operation_id AND (lo.responsible_id IS NULL OR lo.responsible_id = :operator_id) AND o.order_date >= :created_at AND o.order_number NOT LIKE 'AFI%'");
    $stmt->execute(array('operation_id' => $operation_id, 'operator_id' => $operator_id, 'created_at' => $created_at));
    $num_filter_row = $stmt->rowCount();

    #Busca todos dos pedidos se o usuário for ADM.
    $query = "SELECT *,
        CASE WHEN o.platform <> null OR o.platform != '' THEN o.order_quantity ELSE s.sale_quantity END AS quantity, 
        STR_TO_DATE((CASE WHEN o.order_status = 3 THEN o.order_delivery_date ELSE o.order_deadline END),'%Y-%m-%d %T') AS deadline, 
        CASE WHEN o.use_coupon <> null OR o.use_coupon != '' THEN o.order_final_price ELSE s.sale_price END AS final_price 
    FROM orders o INNER JOIN local_operations_orders lo ON lo.order_id = o.order_id INNER JOIN sales s ON o.sale_id = s.sale_id 
    WHERE lo.operation_id = :operation_id 
        AND (lo.responsible_id IS NULL OR lo.responsible_id = :operator_id) 
        AND o.order_date >= :created_at 
        AND o.order_number NOT LIKE 'AFI%'";  
    $query .= " ORDER BY $columnOrder $columnSortOrder ";
    $query .= " LIMIT $row, $rowperpage ";
    $stmt = $conn->prepare($query);
    $stmt->execute(array('operation_id' => $operation_id, 'operator_id' => $operator_id, 'created_at' => $created_at));
    
    $get_orders_list = $stmt->fetchAll();
    $number_row = $stmt->rowCount();

    $filter = false;

} else {

    $result = "'" . implode("','", $filter_result) . "'";

    $stmt = $conn->prepare("SELECT * FROM orders o INNER JOIN local_operations_orders lo ON lo.order_id = o.order_id INNER JOIN sales s ON o.sale_id = s.sale_id WHERE lo.operation_id = :operation_id AND (lo.responsible_id IS NULL OR lo.responsible_id = :operator_id) AND o.order_date >= :created_at AND o.order_id IN ($result) AND o.order_number NOT LIKE 'AFI%'");
    $stmt->execute(array('operation_id' => $operation_id, 'operator_id' => $operator_id, 'created_at' => $created_at));
    $num_filter_row = $stmt->rowCount();

    #Busca todos dos pedidos se o usuário for ADM.
    $query = "SELECT *,
        CASE WHEN o.platform <> null OR o.platform != '' THEN o.order_quantity ELSE s.sale_quantity END AS quantity, 
        STR_TO_DATE((CASE WHEN o.order_status = 3 THEN o.order_delivery_date ELSE o.order_deadline END),'%Y-%m-%d %T') AS deadline, 
        CASE WHEN o.use_coupon <> null OR o.use_coupon != '' THEN o.order_final_price ELSE s.sale_price END AS final_price 
    FROM orders o INNER JOIN local_operations_orders lo ON lo.order_id = o.order_id INNER JOIN sales s ON o.sale_id = s.sale_id 
    WHERE lo.operation_id = :operation_id 
        AND (lo.responsible_id IS NULL OR lo.responsible_id = :operator_id) 
        AND o.order_date >= :created_at 
        AND o.order_id IN ($result) 
        AND o.order_number NOT LIKE 'AFI%'";
    $query .= " ORDER BY $columnOrder $columnSortOrder ";
    $query .= " LIMIT $row, $rowperpage ";
    $stmt = $conn->prepare($query);
    $stmt->execute(array('operation_id' => $operation_id, 'operator_id' => $operator_id, 'created_at' => $created_at));
    
    $get_orders_list = $stmt->fetchAll();
    $number_row = $stmt->rowCount();

    $filter = true;
} 

$cont = 0;

foreach ($get_orders_list as $row) {  
    $order_number = $row['order_number'];

    // pegar nome do produto
    $get_name_product = $conn->prepare("SELECT product_name FROM products AS p WHERE p.product_id = :product_id");
    $get_name_product->execute(['product_id' => $row['product_id']]);

    $name = @$get_name_product->fetch(\PDO::FETCH_ASSOC)['product_name'];

    $get_name_producer = $conn->prepare('SELECT full_name FROM users WHERE user__id = :user__id');
    $get_name_producer->execute(array('user__id' => $row['user__id']));
    $name_producer = $get_name_producer->fetch();
    $producer_name = $name_producer['full_name'];
    $producer_value = $row['order_liquid_value'];

    $order_number_afi = 'AFI' . $row['order_number'];
    $afiliate_name = '';
    $affiliate_value = '';
    $get_name_afi = $conn->prepare('SELECT full_name, order_liquid_value FROM orders os LEFT JOIN users us ON os.user__id = us.user__id WHERE order_number = :order_number');
    $get_name_afi->execute(array('order_number' => $order_number_afi));
    while ($name_afi = $get_name_afi->fetch()) {
        $afiliate_name = $name_afi['full_name'];
        $affiliate_value = (!$export? "R$ " : '') . number_config($name_afi['order_liquid_value'], $export);
    }

    // taxa do produtor
    $get_producer_tax = $conn->prepare('SELECT meta_value FROM orders_meta WHERE meta_key = "producer_tax" AND order_number = :order_number');
    $get_producer_tax->execute(array('order_number' => $order_number));
    $get_producer_tax = $get_producer_tax->fetch();
    $tax_producer = $get_producer_tax['meta_value'];

    $get_member_tax = $conn->prepare('SELECT meta_value FROM orders_meta WHERE meta_key = "member_tax" AND order_number = :order_number');
    $get_member_tax->execute(array('order_number' => $order_number));
    $get_member_tax = $get_member_tax->fetch();
    @$tax_afi = $get_member_tax[0];

    if (mb_strpos($row['order_number'], 'AFI') !== false) {
        $order_number = explode("AFI", $row['order_number']);
        $order_number = $order_number[1];

        $get_producer_tax = $conn->prepare('SELECT meta_value FROM orders_meta WHERE meta_key = "producer_tax" AND order_number = :order_number');
        $get_producer_tax->execute(array('order_number' => $order_number));
        $get_producer_tax = $get_producer_tax->fetch();
        $producer_tax = $get_producer_tax['meta_value'];

        $get_member_commission = $conn->prepare('SELECT meta_value FROM orders_meta WHERE meta_key = "member_commission" AND order_number = :order_number');
        $get_member_commission->execute(array('order_number' => $order_number));
        $get_member_commission = $get_member_commission->fetch();
        @$member_commission = $get_member_commission['meta_value'];

        $get_member_tax = $conn->prepare('SELECT meta_value FROM orders_meta WHERE meta_key = "member_tax" AND order_number = :order_number');
        $get_member_tax->execute(array('order_number' => $order_number));
        $get_member_tax = $get_member_tax->fetch();
        @$member_tax = $get_member_tax[0];

        $get_name_afi = $conn->prepare('SELECT full_name FROM users WHERE user__id = :user__id');
        $get_name_afi->execute(array('user__id' => $row['user__id']));
        $name_afi = $get_name_afi->fetch();
        $afiliate_name = $name_afi['full_name'];
        $affiliate_value = (!$export? "R$ " : '') . number_config($row['order_liquid_value'], $export);

        $get_name_producer = $conn->prepare('SELECT full_name, order_liquid_value FROM orders os INNER JOIN users us ON os.user__id = us.user__id WHERE order_number = :order_number');
        $get_name_producer->execute(array('order_number' => $order_number));
        if ($name_producer = $get_name_producer->fetch()) {
            $producer_name = $name_producer['full_name'];
            $producer_value = $name_producer['order_liquid_value'];
        }

        // taxa do produtor
        $get_producer_tax = $conn->prepare('SELECT meta_value FROM orders_meta WHERE meta_key = "producer_tax" AND order_number = :order_number');
        $get_producer_tax->execute(array('order_number' => $order_number));
        $get_producer_tax = $get_producer_tax->fetch();
        $tax_producer = $get_producer_tax['meta_value'];

        $tax = $member_tax;
        $billingAFI = $member_commission + $member_tax;
    }

    //TD DO CLIENTE
    $address = $row["client_address"];
    $city_state = explode("<br>", $address)[3];
    $cityname = $city = explode(", ", $city_state)[0];
    $state = explode(", ", $city_state)[1];
    $cep = explode("CEP: ", $row["client_address"])[1];     
    
    $road = explode(", ", $address)[0];
    $num =  explode(", ", explode("<br>", $address)[0])[1];
    $bairro = explode("<br>", $address)[1];
    $complemento = explode("<br>", $address)[2];    


    if (isset($_POST['headertype'])) {
        $htmlclient = $client_name = $row['client_name'];
    } else {
        if (strlen($row['client_name']) > 10 && preg_match("/ /", $row['client_name'])) {
            $client_name = explode(" ", $row['client_name']);
            if (strlen($client_name[1]) > 4) {
                $client_name = $client_name[0] . " " . @$client_name[1];
            } else {
                $client_name = $client_name[0] . " " . @$client_name[1] . " " . @$client_name[2];
            }
        } else {
            $client_name = $row['client_name'];
        }

        $htmlclient = "<a href='" . SERVER_URI . "/meu-pedido/" . $row['order_number'] . "' target='_blank' title='Ver detalhes do Pedido'><i class='fa fa-eye'></i></a><span class='text-nowrap'>" . $client_name . "<br><small><i class='fab fa-whatsapp'></i>&nbsp;" . $row['client_number'] . "</small></span>";
    }

    //TD DA QUANTIDADE
    if ($row['platform'] == 'Braip' || $row['platform'] == 'Monetizze') {
        if ($row['platform'] == 'Braip') {
            $deadline = "<img src='/images/integrations/logos/braip.png' alt='Braip' width='100px'>";
        } else if ($row['platform'] == 'Monetizze') {
            $deadline = "<img src='/images/integrations/logos/monetizze.png' alt='Monetizze' width='100px'>";
        }     

        $quantity = $row['order_quantity'];
    } else {
        if($row['order_status'] == 3) {
            $deadline = date_format(date_create($row['order_delivery_date']), 'd/m/y');
            $deadline .= "<br/>";
            $deadline .= date_format(date_create($row['order_delivery_date']), 'H:i');
        } else {
            $deadline =  date_format(date_create($row['order_deadline']), 'd/m/y') ."<br>";
        }

        $quantity = $row['sale_quantity'];
    }

    $maq_tax = "--";    
    if ($row["order_payment_method"] == "debit" || $row["order_payment_method"] == "credit") {
        if ($row["order_payment_method"] == "debit") {
            $maq_tax = $row["order_final_price"] * ($data["debito_tax"] / 100);
        } else if ($row["order_payment_method"] == "credit" && $row["credit_times"]) {
            $maq_tax = (!$export? "R$ " : '') .  number_config($row["order_final_price"] * ($data["credito_tax_" . $row["credit_times"] . "x"] / 100), $export);
        }
    }

    switch ($row['order_payment_method']) {
        case 'money':
            $payment_method = 'Dinheiro';
            break;
        case 'credit':
            $payment_method = 'Crédito';
            break;
        case 'debit':
            $payment_method = 'Débito';
            break;
        case 'pix':
            $payment_method = 'Pix';
            break;
        default:
            $payment_method = '';
            break;
    }

    switch ($row['order_status']) {
        case 1:
            $btn_classes = "light badge-success";
            $status_string = "Reag.";
            $ship_tax = 0;
            break;
        case 2:
            $btn_classes = "light badge-warning";
            $status_string = "Atra.";
            $ship_tax = 0;
            break;
        case 3:
            $btn_classes = "badge-success";
            $status_string = "Comp.";
            $ship_tax = explode("--", $locales[$cityname])[0];
            break;
        case 4:
            $btn_classes = "light badge-dark";
            $status_string = "Frust.";
            $ship_tax = explode("--", $locales[$cityname])[1];
            break;
        case 5:
            $btn_classes = "light badge-danger";
            $status_string = "Canc.";
            $ship_tax = 0;
            break;  
        case 9:
            $btn_classes = "light badge-info";
            $status_string = "Reembo.";
            $ship_tax = explode("--", $locales[$cityname])[0];
            break;
        case 10:
            $btn_classes = "light badge-secondary";
            $status_string = "Confirm."; 
            break;
        case 11:
            $btn_classes = "light badge-warning"; 
            $status_string = "Em aberto"; 
            break;
        case 12:
            $btn_classes = "light badge-outline-danger";   
            $status_string = "Indisponível";  
            break; 
        default:
            $btn_classes = "light badge-success";
            $status_string = "Agen.";
            $ship_tax = 0;
            break;
    }
        

    $htmlstatus = "<span class='badge badge-xs ". $btn_classes ." mb-1'>". $status_string ."</span>";
     
    $htmlsoptions = ""; 
    if ($UserPlan == 6 && ($row['order_status'] != 3 && $row['order_status'] != 4 && $row['order_status'] != 5 &&  $row['order_status'] != 12)) { 
        $htmlsoptions = "<div style='float: right;z-index: 999;margin-right: 20px;' class='dropdown text-sans-serif position-static'>";
        $htmlsoptions .= "<button class='btn btn-success tp-btn-light sharp order-dropdown' onclick='orderDropdown(this)' data-ordernum='". $row['order_number'] ."'
        data-operation='". $row['operation_id'] ."' data-order='". $row['order_id'] ."' data-toggle='dropdown' type='button' id='order-dropdown-0' data-boundary='viewport' aria-haspopup='true' aria-expanded='true'>
            <span>
                <svg xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' width='18px' height='18px' viewBox='0 0 24 24' version='1.1'>
                    <g stroke='none' stroke-width='1' fill='none' fill-rule='evenodd'>
                        <rect x='0' y='0' width='24' height='24'></rect>
                        <circle fill='#000000' cx='5' cy='12' r='2'></circle>
                        <circle fill='#000000' cx='12' cy='12' r='2'></circle>
                        <circle fill='#000000' cx='19' cy='12' r='2'></circle>
                    </g>
                </svg>
            </span>
        </button>";
 
            if ($row['responsible_id'] != null) { 
                $htmlsoptions .= "<div class='dropdown-menu dropdown-menu-right border py-0' aria-labelledby='order-dropdown-0' x-placement='top-end' style='position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(825px, 168px, 0px);'>
                    <div class='py-2'>
                        <a class='dropdown-item' onclick='updateOrderStatus(this)' data-status='0' data-id='". $row['order_id'] ."' href='#'>Agendado</a>
                        <a class='dropdown-item' onclick='updateOrderStatus(this)' data-status='2' data-id='". $row['order_id'] ."' href='#'>Atrasado</a>
                        <a class='dropdown-item' onclick='updateOrderStatus(this)' data-status='5' data-id='". $row['order_id'] ."' href='#'>Cancelado</a>
                        <a class='dropdown-item' href='". SERVER_URI ."/pedido-op/frustrar/". $row['order_number'] ."'>Frustrado</a>
                        <a class='dropdown-item' href='". SERVER_URI ."/pedido-op/completar/". $row['order_id'] ."'>Completo</a>
                        <a class='dropdown-item' href='". SERVER_URI ."/pedido-op/reagendar/". $row['order_id'] ."'>Reagendado</a>
                        <a class='dropdown-item' onclick='setIdAndStatus(this)' data-toggle='modal' data-target='#unavailableOrderModal' data-status='12' data-checkout='1' data-id='" . $row['order_id'] . "'>Indisponível</a>
                    </div>
                </div>";
            } 
        $htmlsoptions .= "</div>";
    }

    $liquid  = $row['platform'] == null ? $row['order_liquid_value'] : 0;
    $fatur = $row['platform'] == null ? $row['order_final_price'] : $profit_platform; // lucro


    if($row['order_status'] == 4){ 
        $producer_value = (!$export? "R$ " : '') . number_config(-$ship_tax, $export); 
        $affiliate_value = $affiliate_value ? (!$export? "R$ " : '') . number_config(0, $export) : '';
    } 

    if($row['order_status'] == 9){  
        $producer_value = (!$export? "R$ " : '') . number_config(-($ship_tax + $tax_producer), $export);  
        $affiliate_value = $affiliate_value ? (!$export? "R$ " : '') . number_config(-($tax_afi), $export) : ''; 
        $fatur = $tax_producer + @$tax_afi + $ship_tax; 
    }

    $aaData[] = array(
        "order"         => date_format(date_create($row['order_date']), 'd/m/y \<\b\r\> H:i'),         //Pedido
        "client"        => $htmlclient,                                         // Cliente
        "number"        => $row['client_number'],
        "email"         => $row['client_email'],                                         // Cliente
        "document"      => $row['client_document'],                                         // Cliente
        "product"       => $name,                                               // Produto
        "sale"          => $row['sale_name'],                                     // Oferta   
        "quantity"      => $quantity,                                           // Qnt
        "deadline"      => $deadline,                                            // Data Entrega
        "billing"       => (!$export? "R$ " : '') . number_config($fatur, $export),        // Fatur
        "payment"       => $payment_method,                                         // Cliente
        "parcel"        => $row['credit_times'] ? $row['credit_times'] . 'x' : '',              
        "addressRoad"   => $road,                                         // Cliente
        "addressNum"    => $num,                                         // Cliente     
        "addressDistrict" => $bairro,                                         // Cliente
        "addressCity"   => $cityname,
        "addressState"  => $state,                                         // Cliente
        "addressComplement" => $complemento,
        "afiliate"      => $afiliate_name,                                         // Cliente
        "commissinAfi"  => $affiliate_value ? $affiliate_value : '',
        "taxAfi"        => $tax_afi ? (!$export? "R$ " : '') . number_config(($tax_afi), $export) : '',                                         // Cliente          
        "produtor"      => $producer_name,                                         // Cliente
        "commissinProd" => (!$export? "R$ " : '') . number_config($producer_value, $export),
        "taxprod"       => (!$export? "R$ " : '') . number_config($tax_producer, $export),
        "commissinOp"   => (!$export? "R$ " : '') . number_config($ship_tax, $export),
        "taxmaq"        => $maq_tax,
        "responsible"   => $row["responsible_id"] == null ? "Indefinido" : "Você",
        "status"        => $htmlstatus,                                         // status
        "options"       => $htmlsoptions,                                       // options
        "cep"           => $cep  
    );
} 

$json_data = array(
    "draw"              => intval($draw),
    "recordsTotal"      => intval($number_row),
    "recordsFiltered"   => intval($num_filter_row),
    "aaData"            => @$aaData, 
    "filter"            => @$filter,  
);  

echo json_encode($json_data);
