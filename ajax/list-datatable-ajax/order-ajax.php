<?php
// error_reporting(-1);            
// ini_set('display_errors', 1);      
// header('Content-Type: application/json; charset=utf-8'); 

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

$referenceData = ' WHERE orders.order_date BETWEEN :start_date AND :final_date ';
if ($params['reference-data'] == 'entrega') {
    $referenceData = ' WHERE STR_TO_DATE((CASE WHEN orders.order_status = 3 THEN orders.order_delivery_date ELSE orders.order_deadline END),"%Y-%m-%d %T") between :start_date AND :final_date  ';
}
if ($params['reference-data'] == 'reembolso') {
    $referenceData = ' WHERE orders.order_status_update BETWEEN :start_date AND :final_date ';
} 


if ($columnName == "order") {
    $columnOrder = "orders.order_date";
} elseif ($columnName == "client") {
    $columnOrder = "orders.client_name";
} elseif ($columnName == "product") {
    $columnOrder = "orders.product_name";
} elseif ($columnName == "sale") {
    $columnOrder = "sales.sale_name";
} elseif ($columnName == "quantity") {
    $columnOrder = "quantity";
} elseif ($columnName == "deadline") {
    $columnOrder = "orders.platform " . $columnSortOrder . ", deadline";
} elseif ($columnName == "billing") {
    $columnOrder = "final_price";
} elseif ($columnName == "shipTax") {
    $columnOrder = "sales.sale_tax";
} elseif ($columnName == "shipTax") {
    $columnOrder = "sales.sale_tax";
} elseif ($columnName == "profit") {
    $columnOrder = "sales.product_shipping_tax";
} elseif ($columnName == "comission") {
    $columnOrder = "orders.order_liquid_value";
} elseif ($columnName == "status") {
    $columnOrder = "orders.order_status";
} elseif ($columnName == "options") {
    $columnOrder = "orders.order_status";
}


$verify_if_have_any_filter_active = !(empty($params['data-inicio'])) || !(empty($params['data-final'])) || !(empty($params['nome-cliente'])) || !(empty($params['documento-cliente'])) || !(empty($params['produto'])) || !(empty($params['afiliado'])) || !(empty($params['produtor'])) || !(empty($params['status'])) || !(empty($params['numero-cliente-produto'])) || !(empty($params['operacao'])) || !(empty($params['operador']));
 
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
    
    if ($UserPlan == 5) {
        $date_ids = $conn->prepare("SELECT order_id FROM orders $referenceData AND order_number NOT LIKE '%AFI%' ");
    } else {
        $date_ids = $conn->prepare("SELECT order_id FROM orders $referenceData AND user__id = " . $user__id);
    }

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

    //Filtro por DOCUMENTO DO CLIENTE
    if (!(empty($params['documento-cliente']))) {
        $filter_document_result = array();

        $document_name = '%' . addslashes($params['documento-cliente']) . '%';

        $document_cliente_ids = $conn->prepare('SELECT order_id FROM orders WHERE client_document LIKE :client_document');
        $document_cliente_ids->execute(array('client_document' => $document_name));

        while ($document_cliente_id = $document_cliente_ids->fetch()) {
            array_push($filter_document_result, $document_cliente_id['order_id']);
        }

        $filter_result = array_intersect($filter_result, $filter_document_result);
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

    //Filtro por NOME DO AFILIADO
    if (!(empty($params['afiliado']))) {
        $filter_affiliate_result = array();
        $afiliatte = $params['afiliado'];
        if ($UserPlan == 5) {

            $all_orders  = $conn->prepare("SELECT o.order_id , o.order_number FROM orders AS o");
            $all_orders->execute();

            foreach ($all_orders->fetchAll() as $row_all) {
                $affiliate_ids  = $conn->prepare("SELECT o.order_id , REPLACE (o.order_number, 'AFI', '') as find_order  FROM orders AS o  WHERE  o.order_number LIKE :order_number AND o.user__id = :aff_id");
                $affiliate_ids->execute(['order_number' => "%" . $row_all["order_number"], 'aff_id' => $afiliatte]);


                while ($row_aff = $affiliate_ids->fetch()) {
                    if ($row_aff["find_order"] == $row_all["order_number"]) {
                        array_push($filter_affiliate_result, $row_all['order_id']);
                    }
                }
            }
        } else {

            $all_orders  = $conn->prepare("SELECT o.order_id , o.order_number FROM orders AS o  WHERE  o.user__id = :user__id");
            $all_orders->execute(['user__id' => $user__id]);

            foreach ($all_orders->fetchAll() as $row_all) {
                $affiliate_ids  = $conn->prepare("SELECT o.order_id , REPLACE (o.order_number, 'AFI', '') as find_order  FROM orders AS o  WHERE  o.order_number LIKE :order_number AND o.user__id = :aff_id");
                $affiliate_ids->execute(['order_number' => "%" . $row_all["order_number"], 'aff_id' => $afiliatte]);


                while ($row_aff = $affiliate_ids->fetch()) {
                    if ($row_aff["find_order"] == $row_all["order_number"]) {
                        array_push($filter_affiliate_result, $row_all['order_id']);
                    }
                }
            }
        }

        $filter_result = array_intersect($filter_result, $filter_affiliate_result);
    }

    //Filtro por NOME DO AFILIADO
    if (!(empty($params['produtor']))) {
        $filter_producer_result = array();
        $producer = $params['produtor'];         

        $producer_ids  = $conn->prepare("SELECT o.order_id FROM orders AS o WHERE user__id = :user__id");
        $producer_ids->execute(array('user__id' => $producer));  

        while ($producer_id = $producer_ids->fetch()) {
            array_push($filter_producer_result, $producer_id['order_id']); 
        }

        $filter_result = array_intersect($filter_result, $filter_producer_result);
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

    if (!(empty($params['operacao']))) {
        $filter_operation_result = array();

        $operation_id = $params['operacao'];

        $operation_ids = $conn->prepare('SELECT o.order_id FROM orders o INNER JOIN local_operations_orders loo ON loo.order_id = o.order_id WHERE loo.operation_id = :operation_id');
        $operation_ids->execute(array('operation_id' => $operation_id));

        while ($order_id = $operation_ids->fetch()) {
            array_push($filter_operation_result, $order_id['order_id']);
        }

        $filter_result = array_intersect($filter_result, $filter_operation_result);
    }

    if (!(empty($params['operador']))) {
        $filter_operator_result = array();

        $operator_id = $params['operador'];

        if ($operator_id != "indefinido") {
            $operator_ids = $conn->prepare('SELECT o.order_id FROM orders o INNER JOIN local_operations_orders loo ON loo.order_id = o.order_id WHERE loo.responsible_id = :operator_id');
            $operator_ids->execute(array('operator_id' => $operator_id));
        } else {
            $operator_ids = $conn->prepare('SELECT o.order_id FROM orders o INNER JOIN local_operations_orders loo ON loo.order_id = o.order_id WHERE loo.responsible_id IS NULL');
            $operator_ids->execute();
        }

        while ($order_id = $operator_ids->fetch()) {
            array_push($filter_operator_result, $order_id['order_id']);
        }

        $filter_result = array_intersect($filter_result, $filter_operator_result);
    }
}

if (!$verify_if_have_any_filter_active) {

    $query = "";
    if ($UserPlan == 5) {

        $stmt = $conn->prepare("SELECT * FROM orders INNER JOIN sales ON orders.sale_id = sales.sale_id AND orders.order_number NOT LIKE 'AFI%'"); // NOT LIKE AFI
        $stmt->execute();
        $num_filter_row = $stmt->rowCount();



        #Busca todos dos pedidos se o usuário for ADM.
        $query = "SELECT *, CASE WHEN orders.platform <> null OR orders.platform != '' THEN orders.order_quantity ELSE sales.sale_quantity END AS quantity, 
            STR_TO_DATE((CASE WHEN orders.order_status = 3 THEN orders.order_delivery_date ELSE orders.order_deadline END),'%Y-%m-%d %T') AS deadline, 
            CASE WHEN orders.use_coupon <> null OR orders.use_coupon != '' THEN orders.order_final_price ELSE sales.sale_price END AS final_price 
        FROM orders INNER JOIN sales ON orders.sale_id = sales.sale_id AND orders.order_number NOT LIKE 'AFI%'
        ORDER BY $columnOrder $columnSortOrder
        LIMIT $row, $rowperpage";
        $stmt = $conn->prepare($query);
        $stmt->execute();
    } else {

        $stmt = $conn->prepare("SELECT * FROM orders INNER JOIN sales ON orders.sale_id = sales.sale_id WHERE user__id = :user__id");
        $stmt->execute(array('user__id' => $user__id));
        $num_filter_row = $stmt->rowCount();

        # Busca apenas os pedidos do usário, se ele não for ADM.
        $query = "SELECT *, 
            CASE WHEN orders.platform <> null OR orders.platform != '' THEN orders.order_quantity ELSE sales.sale_quantity END AS quantity, 
            STR_TO_DATE((CASE WHEN orders.order_status = 3 THEN orders.order_delivery_date ELSE orders.order_deadline END),'%Y-%m-%d %T') AS deadline, 
            CASE WHEN orders.use_coupon <> null OR orders.use_coupon != '' THEN orders.order_final_price ELSE sales.sale_price END AS final_price
        FROM orders INNER JOIN sales ON orders.sale_id = sales.sale_id WHERE user__id = :user__id 
        ORDER BY $columnOrder $columnSortOrder
        LIMIT $row, $rowperpage";
        $stmt = $conn->prepare($query);
        $stmt->execute(array('user__id' => $user__id));
    }

    $get_orders_list = $stmt->fetchAll();
    $number_row = $stmt->rowCount();

    $filter = false;
} else {


    $result = "'" . implode("','", $filter_result) . "'";
    // $retorno = $result;
    $query = "";
    if ($UserPlan == 5) {

        $stmt = $conn->prepare("SELECT orders.order_id FROM orders INNER JOIN sales ON orders.sale_id = sales.sale_id WHERE orders.order_id IN ($result)");  // NOT LIKE AFI
        $stmt->execute();
        $num_filter_row = $stmt->rowCount();

        $filterquery = "SELECT * FROM orders INNER JOIN sales ON orders.sale_id = sales.sale_id AND orders.order_number NOT LIKE 'AFI%'";


        #Busca todos dos pedidos se o usuário for ADM.
        $query = "SELECT *, 
            CASE WHEN orders.platform <> null OR orders.platform != '' THEN orders.order_quantity ELSE sales.sale_quantity END AS quantity, 
            STR_TO_DATE((CASE WHEN orders.order_status = 3 THEN orders.order_delivery_date ELSE orders.order_deadline END),'%Y-%m-%d %T') AS deadline, 
            CASE WHEN orders.use_coupon <> null OR orders.use_coupon != '' THEN orders.order_final_price ELSE sales.sale_price END AS final_price
        FROM orders INNER JOIN sales ON orders.sale_id = sales.sale_id WHERE orders.order_id IN ($result)  
        ORDER BY $columnOrder $columnSortOrder 
        LIMIT $row, $rowperpage";
        $stmt = $conn->prepare($query);
        $stmt->execute();
    } else {

        $stmt = $conn->prepare("SELECT * FROM orders INNER JOIN sales ON orders.sale_id = sales.sale_id AND orders.order_id IN ($result) AND user__id = :user__id");
        $stmt->execute(array('user__id' => $user__id));
        $num_filter_row = $stmt->rowCount();
        //$filterquery = "SELECT * FROM orders INNER JOIN sales ON orders.sale_id = sales.sale_id AND orders.order_number NOT LIKE 'AFI%'";
        # Busca apenas os pedidos do usário, se ele não for ADM.
        $query = "SELECT *, 
            CASE WHEN orders.platform <> null OR orders.platform != '' THEN orders.order_quantity ELSE sales.sale_quantity END AS quantity, 
            STR_TO_DATE((CASE WHEN orders.order_status = 3 THEN orders.order_delivery_date ELSE orders.order_deadline END),'%Y-%m-%d %T') AS deadline,
            CASE WHEN orders.use_coupon <> null OR orders.use_coupon != '' THEN orders.order_final_price ELSE sales.sale_price END AS final_price
        FROM orders INNER JOIN sales ON orders.sale_id = sales.sale_id WHERE orders.order_id IN ($result) 
        ORDER BY $columnOrder $columnSortOrder
        LIMIT $row, $rowperpage ";
        $stmt = $conn->prepare($query);
        $stmt->execute();
    }

    $get_orders_list = $stmt->fetchAll();
    $number_row = $stmt->rowCount();

    $filter = true;
}


foreach ($get_orders_list as $row) {
   

    if (mb_strpos($row['order_number'], 'AFI') === false) {
        $order_number = $row['order_number'];        
        $order_number_afi = "AFI". $row['order_number'];
        
        $get_name_producer = $conn->prepare('SELECT full_name FROM users WHERE user__id = :user__id');
        $get_name_producer->execute(array('user__id' => $row['user__id']));
        @$producer_name = $get_name_producer->fetch()['full_name'];

        $afiliate_name = '';
        $get_name_afiliate = $conn->prepare('SELECT full_name FROM orders os LEFT JOIN users us ON os.user__id = us.user__id WHERE order_number = :order_number');
        $get_name_afiliate->execute(array('order_number' => $order_number_afi));
        @$afiliate_name = $get_name_afiliate->fetch()['full_name'];  
    } else {
        $order_number = explode("AFI", $row['order_number'])[1];
        $order_number_afi = $row['order_number'];
        
        $get_name_producer = $conn->prepare('SELECT full_name FROM orders os LEFT JOIN users us ON os.user__id = us.user__id WHERE order_number = :order_number');
        $get_name_producer->execute(array('order_number' => $order_number));
        @$producer_name = $get_name_producer->fetch()['full_name'];  

        $afiliate_name = '';
        $get_name_afiliate = $conn->prepare('SELECT full_name FROM users WHERE user__id = :user__id');
        $get_name_afiliate->execute(array('user__id' => $row['user__id']));
        @$afiliate_name = $get_name_afiliate->fetch()['full_name'];
    }

    $get_meta_values = $conn->prepare('SELECT meta_value, meta_key FROM orders_meta WHERE order_number = :order_number');
    $get_meta_values->execute(array('order_number' => $order_number));

    $producer_commission = $afiliate_commission = $producer_tax = $afiliate_tax = $ship_tax = 0.00;
    while($meta_values = $get_meta_values->fetch()){  
        if($meta_values['meta_key'] === 'producer_commission'){
            $producer_commission = $meta_values['meta_value'];
            continue;
        }

        if($meta_values['meta_key'] === 'member_commission'){
            $afiliate_commission = $meta_values['meta_value'];
            continue;
        }
        if($meta_values['meta_key'] === 'producer_tax'){
            $producer_tax = $meta_values['meta_value'];
            continue;
        }
        if($meta_values['meta_key'] === 'member_tax'){
            $afiliate_tax = $meta_values['meta_value'];
            continue;
        }
        if($meta_values['meta_key'] === 'ship_tax'){
            $ship_tax = $meta_values['meta_value']; 
            continue;
        }   
    }

    //TD DO CLIENTE
    $address        = $row["client_address"];
    $road           = explode(", ", $address)[0];
    $num            = explode(", ", explode("<br>", $address)[0])[1];
    $bairro         = explode("<br>", $address)[1];
    $cep            = explode("CEP: ", $address)[1];         
    $complemento    = explode("<br>", $address)[2];    
    $city_state     = explode("<br>", $address)[3];
    $cityname       = explode(", ", $city_state)[0];
    $state          = explode(", ", $city_state)[1];


    $tax                = $afiliate_tax;
    $expenses           = 0;
    $billing_afiliate   = $afiliate_commission + $afiliate_tax;
    
    $final_price        = $row['order_final_price'];
    $liquid_user        = $row['platform'] == null ? $row['order_liquid_value'] : 0;
    $tax_user           = $row['platform'] == null ? (mb_strpos($row['order_number'], 'AFI') !== false ? $afiliate_tax : $producer_tax) : 0;
    $freight            = $row['platform'] == null ? $row['order_freight'] : $row['sale_freight'];  
    $ship_tax           = mb_strpos($row['order_number'], 'AFI') !== false ? 0 : $ship_tax;
    
    $get_order_operator = $conn->prepare("SELECT * FROM local_operations_orders AS loo WHERE loo.order_id = :order_id");
    $get_order_operator->execute(['order_id' => $row['order_id']]);
    $local_operations_order = $get_order_operator->fetch(\PDO::FETCH_ASSOC);

    $get_locale = $conn->prepare('SELECT * FROM operations_locales WHERE city = :city AND operation_id = :operation_id');
    $get_locale->execute(array('city' =>  $cityname, "operation_id" => $local_operations_order['operation_id']));
    $locale_info = $get_locale->fetch(\PDO::FETCH_ASSOC);

    if(!empty($local_operations_order['responsible_id'])){
        $get_delivery_taxes = $conn->prepare('SELECT * FROM operations_delivery_taxes WHERE operation_locale = :locale AND operator_id = :operator');
        $get_delivery_taxes->execute(array('operator' => $local_operations_order['responsible_id'], 'locale' => $locale_info['id']));
        $delivery_taxes = $get_delivery_taxes->fetchAll();
        
        # Relaciona as taxas de entrega aos locais em um array de chave-valor   
        $locales = array();
        for ($i = 0; $i < count($delivery_taxes); $i++) {
            $tax = $delivery_taxes[$i];
            if ($locale_info["id"] == $tax["operation_locale"]) {
                $locales[$locale_info["city"]] = $tax["complete_delivery_tax"] . "--" . $tax["frustrated_delivery_tax"];
            }
        } 

        $maq_tax = 0;
        $responsible_name = '';
        $get_operation_id = $conn->prepare("SELECT lo.*, u.full_name, u.created_at  FROM logistic_operator lo INNER JOIN users u ON lo.user_id=u.user__id WHERE lo.operator_id = :operator_id");
        $get_operation_id->execute(array("operator_id" => $local_operations_order['responsible_id']));
        if ($data_responsible = $responsible_info = $get_operation_id->fetch()) {
            
            $responsible_name = $data_responsible['full_name'];
            
            if ($row["order_payment_method"] == "debit" || $row["order_payment_method"] == "credit") {
                if ($row["order_payment_method"] == "debit") {
                    $maq_tax = $row["order_final_price"] * (@$data_responsible["debito_tax"] / 100);
                } else if ($row["order_payment_method"] == "credit" && $row["credit_times"]) {
                    $maq_tax = $row["order_final_price"] * (@$data_responsible["credito_tax_" . $row["credit_times"] . "x"] / 100);
                }
                $expenses += @$maq_tax;
            }
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

    //TD DO CLIENTE
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
        $quantity = $row['order_quantity'];
    } else {
        $quantity = $row['sale_quantity'];
    }

    $product_name = $row['product_name'];

    //TD DA ENTREGA
    if ($row['platform'] == 'Braip') {
        $deadline = "<img src='/images/integrations/logos/braip.png' alt='Braip' width='100px'>";
    } else if ($row['platform'] == 'Monetizze') {
        $deadline = "<img src='/images/integrations/logos/monetizze.png' alt='Monetizze' width='100px'>";
    } else {
        if ($row['order_status'] == 3) {
            $deadline = date_format(date_create($row['order_delivery_date']), 'd/m/y \<\b\r\> H:i');
        } else {
            $deadline =  date_format(date_create($row['order_deadline']), 'd/m/y \<\b\r\> ');
        }
    }

    if ($row['platform'] == null) {

        // faturamento do produtor 
        $expenses += $producer_commission;
        $expenses += $afiliate_commission;

        // faturamento do produtor 
        $expenses_platform = $row['order_tracking_value']; // fatur

        // pegar valor de frete da conta
        $profit_platform = $ship_tax;

        switch ($row['order_status']) {
            case 1:
                $btn_classes = "light badge-success";
                $status_string = "Reag.";
                break;
            case 2:
                $btn_classes = "light badge-warning";
                $status_string = "Atra.";
                break;
            case 3:
                $btn_classes = "badge-success";
                $status_string = "Comp.";
                $expenses += $operator_comission  = (float) explode("--", @$locales[$cityname])[0]; 
                break;
            case 4:
                $btn_classes = "light badge-dark";
                $status_string = "Frust.";
                $expenses += $operator_comission  = (float) explode("--", @$locales[$cityname])[1]; 
                break;
            case 5:
                $btn_classes = "light badge-danger";
                $status_string = "Canc.";
                break;
            case 9:
                $btn_classes = "light badge-info";
                $status_string = "Reembo."; 
                $expenses += $operator_comission  = (float) explode("--", @$locales[$cityname])[0]; 
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
                break;
        } 

        $htmlstatus = "<span class='badge badge-xs " . $btn_classes . " mb-1'>" . $status_string . "</span>";
    } else {
        switch ($row['order_status']) {
            case 6:
                $btn_classes = "light badge-success";
                $status_string = "À Enviar.";
                break;
            case 7:
                $btn_classes = "light badge-dark";
                $status_string = "Enviando.";
                break;
            case 8:
                $btn_classes = "badge-success";
                $status_string = "Enviado.";
                break;
        }
        $htmlstatus = "<span class='badge badge-xs " . $btn_classes . " mb-1'>" . $status_string . "</span>";
    }

    $htmlsoptions = "";
    if ($UserPlan == 5) {    
        //($row['order_status'] != 4 && $row['order_status'] != 8) 

        $htmlsoptions .= "
        <div style='float: right;z-index: 999;margin-right: 20px;' class='dropdown text-sans-serif position-static'><button class='btn btn-success tp-btn-light sharp' type='button' id='order-dropdown-0' data-toggle='dropdown' data-boundary='viewport' aria-haspopup='true' aria-expanded='true'><span><svg xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' width='18px' height='18px' viewBox='0 0 24 24' version='1.1'>
            <g stroke='none' stroke-width='1' fill='none' fill-rule='evenodd'>
                <rect x='0' y='0' width='24' height='24'></rect>
                <circle fill='#000000' cx='5' cy='12' r='2'></circle>
                <circle fill='#000000' cx='12' cy='12' r='2'></circle>
                <circle fill='#000000' cx='19' cy='12' r='2'></circle>
            </g>
            </svg></span></button>
            <div class='dropdown-menu dropdown-menu-right border py-0' aria-labelledby='order-dropdown-0' x-placement='top-end' style='position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(825px, 168px, 0px);'>
                <div class='py-2'>";
        if ($row['platform'] == null) {
            if($row['order_status'] != 0 && $row['order_status'] != 3 && $row['order_status'] != 4 && $row['order_status'] != 9 && $row['order_status'] != 12 ) {
                $htmlsoptions .= "<a class='dropdown-item' onclick='updateOrderStatus(this)' data-status='0' data-checkout='1' data-id='" . $row['order_id'] . "' href='#'>Agendado</a>";
            }
            if($row['order_status'] != 2 && $row['order_status'] != 3 && $row['order_status'] != 4 && $row['order_status'] != 9 && $row['order_status'] != 12 ) {
                $htmlsoptions .= "<a class='dropdown-item' onclick='updateOrderStatus(this)' data-status='2' data-checkout='1' data-id='" . $row['order_id'] . "' href='#'>Atrasado</a>";
            }
            if($row['order_status'] != 3 && $row['order_status'] != 4 && $row['order_status'] != 5 && $row['order_status'] != 9) {
                $htmlsoptions .= "<a class='dropdown-item' onclick='setIdAndStatus(this)'  data-toggle='modal' data-target='#cancelOrderModal' data-status='5' data-checkout='1' data-id='" . $row['order_id'] . "'>Cancelado</a>";
            }
            if($row['order_status'] != 3 && $row['order_status'] != 4 && $row['order_status'] != 9 && $row['order_status'] != 12 ) {
                $htmlsoptions .= "<a class='dropdown-item' href='" . SERVER_URI . "/pedido/frustrar/" . $row['order_number'] . "'>Frustrado</a>";
            }
            if($row['order_status'] != 3 && $row['order_status'] != 4 && $row['order_status'] != 9 && $row['order_status'] != 12 ) { 
                $htmlsoptions .= "<a class='dropdown-item' href='" . SERVER_URI . "/pedido/completar/" . $row['order_id'] . "'>Completo</a>";
            }
            if($row['order_status'] != 1 && $row['order_status'] != 3 && $row['order_status'] != 4 && $row['order_status'] != 9) {
                $htmlsoptions .= "<a class='dropdown-item' href='" . SERVER_URI . "/pedido/reagendar/" . $row['order_id'] . "'>Reagendado</a>";            
            }
            if($row['order_status'] != 4 && $row['order_status'] != 9 && $row['order_status'] != 12) { 
                $htmlsoptions .= "<a class='dropdown-item' onclick='updateOrderStatus(this)' data-status='9' data-checkout='1' data-id='" . $row['order_id'] . "' data-statusname='Reembolsado'>Reembolsado</a>";
            } 
            if($row['order_status'] != 3 && $row['order_status'] != 12) { 
                $htmlsoptions .= "<a class='dropdown-item' onclick='setIdAndStatus(this)' data-toggle='modal' data-target='#unavailableOrderModal' data-status='12' data-checkout='1' data-id='" . $row['order_id'] . "'>Indisponível</a>";
            } 
            if($row['order_status'] != 4 && $row['order_status'] != 3 && $row['order_status'] != 9 && $row['order_status'] != 12 && $row['order_status'] != 11) {
                $htmlsoptions .= "<a class='dropdown-item' onclick='updateOrderStatus(this)' data-status='11' data-checkout='1' data-id='" . $row['order_id'] . "' data-statusname='Em Aberto'>Em Aberto</a>";
            }
            if($row['order_status'] != 4 && $row['order_status'] != 9) {
                $htmlsoptions .= "<div class='dropdown-divider'></div>";
            }
            $htmlsoptions .= "<a class='dropdown-item' onclick='deleteOrderLink(this)' data-id='" . $row['order_number'] . "' href='#'>Deletar</a>";
        } else {
            $htmlsoptions .= "<a class='dropdown-item' onclick='updateOrderStatus(this)' data-status='6' data-checkout='' data-id='" . $row['order_id'] . "'>À Enviar</a>";
            $htmlsoptions .= "<a class='dropdown-item' onclick='updateOrderStatus(this)' data-status='7' data-checkout='' data-id='" . $row['order_id'] . "' >Enviando</a>";
            $htmlsoptions .= "<a class='dropdown-item' href='" . SERVER_URI . "/pedido/enviando/" . $row['order_id'] . "'>Enviado</a>";
        }
        $htmlsoptions .=
            "</div>
            </div>
        </div>"; 
    } else {  

        $order_deadline = date("Y-m-d", strtotime($row['order_deadline']));
        $date = date_create($order_deadline ." 21:00:00");
        date_modify($date,"-1 days");
        $order_deadline_minus = date_format($date,"Y-m-d H:i:s"); 
 
        if (($status_string == "Agen." && (date("Y-m-d H:i:s") < $order_deadline_minus)) || $status_string == "Expirado"  || $status_string == "Indisponível") { // && (date("H:i:s", strtotime($row['order_deadline'])) > "21:00:00")
            $htmlsoptions .= "
            <div style='float: right;z-index: 999;margin-right: 20px;' class='dropdown text-sans-serif position-static'><button class='btn btn-success tp-btn-light sharp' type='button' id='order-dropdown-0' data-toggle='dropdown' data-boundary='viewport' aria-haspopup='true' aria-expanded='true'><span><svg xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' width='18px' height='18px' viewBox='0 0 24 24' version='1.1'>
                <g stroke='none' stroke-width='1' fill='none' fill-rule='evenodd'>
                    <rect x='0' y='0' width='24' height='24'></rect>
                    <circle fill='#000000' cx='5' cy='12' r='2'></circle>
                    <circle fill='#000000' cx='12' cy='12' r='2'></circle>
                    <circle fill='#000000' cx='19' cy='12' r='2'></circle>
                </g>
                </svg></span></button>
                <div class='dropdown-menu dropdown-menu-right border py-0' aria-labelledby='order-dropdown-0' x-placement='top-end' style='position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(825px, 168px, 0px);'>
                    <div class='py-2'>";
            if ($row['platform'] == null) {
                $htmlsoptions .= "<a class='dropdown-item' onclick='setIdAndStatus(this)'  data-toggle='modal' data-target='#cancelOrderModal'  data-status='5' data-checkout='1' data-id='" . $row['order_id'] . "' href='#'>Cancelado</a>";
                $htmlsoptions .= "<a class='dropdown-item' href='" . SERVER_URI . "/pedido/reagendar/" . $row['order_id'] . "'>Reagendado</a>";
                if($row['order_status'] != 12) { 
                    $htmlsoptions .= "<a class='dropdown-item' onclick='deleteOrderLink(this)' data-id='" . $row['order_number'] . "' href='#'>Deletar</a>";
                }
            }
            $htmlsoptions .=
                "</div>
                </div>
            </div>";
        }
    }

    $billingByOrder = mb_strpos($row['order_number'], 'AFI') !== false ? $billing_afiliate : $final_price;
    $fatur = $row['platform'] == null ? $billingByOrder : $profit_platform; // lucro
    $expenses_dinamic = $row['platform'] == null ? $expenses : $expenses_platform; // despesas 
    $profit = $fatur - $expenses_dinamic; // lucro      
 
    if($row['order_status'] == 4){ 
        # Busca historico de atualização dos status
        $get_values_order_liquid = $conn->prepare('SELECT oder_liquid_value_producer, oder_liquid_value_afiliate FROM order_details WHERE order_number = :order_number AND order_status = 4 ORDER BY details_id DESC LIMIT 1');
        $get_values_order_liquid->execute(array('order_number' => $order_number));
        if($values_order_liquid = $get_values_order_liquid->fetch()) {
            $profit = (($values_order_liquid['oder_liquid_value_producer'] + $values_order_liquid['oder_liquid_value_afiliate']) * (-1)) - $operator_comission;    
            $producer_commission = ($export ? "" : "R$ ") . number_config($values_order_liquid['oder_liquid_value_producer'], $export); 
            $afiliate_commission = $values_order_liquid['oder_liquid_value_afiliate'] ? ($export ? "" : "R$ ") . number_config($values_order_liquid['oder_liquid_value_afiliate'], $export) : '';
            $ship_tax = mb_strpos($row['order_number'], 'AFI') !== false ? (($values_order_liquid['oder_liquid_value_afiliate']) * (-1)) : (($values_order_liquid['oder_liquid_value_producer']) * (-1));
            $liquid_user = mb_strpos($row['order_number'], 'AFI') !== false ? $values_order_liquid['oder_liquid_value_afiliate'] : $values_order_liquid['oder_liquid_value_producer'];
            
            $fatur = (($values_order_liquid['oder_liquid_value_producer'] + $values_order_liquid['oder_liquid_value_afiliate']) * (-1)); 
            
        } else {
            $profit = $ship_tax - $operator_comission;    
            $producer_commission = ($export ? "" : "R$ ") . number_config(-$ship_tax, $export); 
            $afiliate_commission = $afiliate_commission ? ($export ? "" : "R$ ") . number_config(0, $export) : '';
            $ship_tax = mb_strpos($row['order_number'], 'AFI') !== false ? 0 : $ship_tax;
            $liquid_user = mb_strpos($row['order_number'], 'AFI') !== false ? 0 : -$ship_tax;
            
            $fatur = $ship_tax; 
        }
        
        $afiliate_tax = $producer_tax = '';
        $expenses_dinamic = $operator_comission;  
        $billingByOrder = 0;
        $tax_user = 0;
    } 

    if($row['order_status'] == 9){  

        $profit = ($producer_tax + @$maq_tax + @$afiliate_tax + $ship_tax) - $operator_comission;    
        $producer_commission = ($export ? "" : "R$ "). number_config(-($ship_tax + $producer_tax), $export);  
        $afiliate_commission = $afiliate_commission ? ($export ? "" : "R$ ") . number_config(-($afiliate_tax), $export) : ''; 
        
        
        $fatur = $producer_tax + @$afiliate_tax + $ship_tax; 
        $expenses_dinamic = $operator_comission + @$maq_tax;  

        if(mb_strpos($row['order_number'], 'AFI') !== false){
            $liquid_user =  $afiliate_commission ? -$afiliate_tax : '' ;    
        } else {
            $liquid_user = -($ship_tax + $producer_tax);   
        }
    }
    

     
    if ($_SESSION['UserPlan'] == 5) {

        $data[] = array(
            'ordernumber'   => $order_number,
            "order"         => date_format(date_create($row['order_date']), 'd/m/y \<\b\r\> H:i'),         //Pedido
            "client"        => $htmlclient,                                         // Cliente
            "number"        => $row['client_number'],
            "email"         => $row['client_email'],                                         // Cliente
            "document"      => $row['client_document'],                                         // Cliente
            "product"       => $product_name,                                               // Produto
            "produtor"      => $producer_name,                                         // Cliente
            "sale"          => $row['sale_name'],                                     // Oferta   
            "quantity"      => $quantity,                                           // Qnt
            "deadline"      => $deadline,                                            // Data Entrega
            "billing"       => ($export ? "" : "R$ ") . number_config($fatur, $export),        // Fatur
            "payment"       => $payment_method,                                         // Cliente
            "parcel"        => $row['credit_times'] ? $row['credit_times'] . 'x' : '',                                         // Cliente
            "expenses"      => ($export ? "" : "R$ ") . number_config($expenses_dinamic, $export), // despesas
            "freight"       => $freight ? ($export ? "" : "R$ ") . number_config($freight, $export) : '', // despesas
            "taxfreight"    => ($export ? "" : "R$ ") . number_config($ship_tax, $export), // despesas
            "addressRoad"   => $road,                                         // Cliente
            "addressNum"    => $num,                                         // Cliente     
            "addressDistrict" => $bairro,                                         // Cliente
            "addressCity"   => $cityname,
            "addressState"  => $state,                                          // Cliente             
            "addressComplement" => $complemento,
            "commissinProd" => ($export ? "" : "R$ ") . number_config($producer_commission, $export),  
            "taxprod"       => ($export ? "" : "R$ ") . number_config($producer_tax, $export),
            "commissinAfi"  => $afiliate_commission ? ($export ? "" : "R$ ") . number_config($afiliate_commission, $export) : '',
            "commissinOp"   => ($export ? "" : "R$ ") . number_config(@$operator_comission, $export), 
            "taxAfi"        => $afiliate_tax ? ($export ? "" : "R$ ") . number_config(($afiliate_tax), $export) : '',                                         // Cliente          
            "afiliate"      => $afiliate_name,                                         // Cliente
            "operator"      => $responsible_name,
            "taxmaq"        => isset($maq_tax) ? ($export ? "" : "R$ ") . number_config($maq_tax, $export) : '',
            "profit"        => ($export ? "" : "R$ ") . number_config(($profit), $export), // lucro 
            "status"        => $htmlstatus,                                         // status
            "options"       => $htmlsoptions,                                      // options
            "cep"           => $cep,
            "CMV"           => 0,
            "date_remp"     => $row['order_status_update'] ? date_format(date_create($row['order_status_update']), 'd/m/y H:i') : '---',
        );
    } else {   

        $data[] = array(
            'ordernumber'       => $order_number,
            "order"             => date_format(date_create($row['order_date']), 'd/m/y \<\b\r\> H:i'),
            "client"            => $htmlclient,
            "number"            => $row['client_number'],
            "email"             => $row['client_email'],
            "document"          => $row['client_document'],

            "product"           => $product_name, 
            "produtor"          => $producer_name,
            "sale"              => $row['sale_name'],
            "quantity"          => $quantity,
            "deadline"          => $deadline, 

            "billing"           => ($export ? "" : "R$ ") . number_config($billingByOrder, $export),
            "payment"           => $payment_method,
            "parcel"            => $row['credit_times'] ? $row['credit_times'] . 'x' : '',
            "taxprod"           => $producer_tax ? ($export ? "" : "R$ ") . number_config(($producer_tax), $export) : '', 
            "freight"           => $freight ? ($export ? "" : "R$ ") . number_config($freight, $export) : '',
            "taxfreight"        => ($export ? "" : "R$ ") . number_config($ship_tax, $export),

            "addressRoad"       => $road,
            "addressNum"        => $num,
            "addressDistrict"   => $bairro,
            "addressCity"       => $cityname,
            "addressState"      => $state, 
            "addressComplement" => $complemento,

            "commissinProd"     => $producer_commission,

            "commissinAfi"      => $afiliate_commission ,
            "taxAfi"            => $afiliate_tax ? ($export ? "" : "R$ ") . number_config(($afiliate_tax), $export) : '', 
            "afiliate"          => $afiliate_name,

            "taxuser"           => ($export ? "" : "R$ ") . number_config($tax_user, $export),
            "commissin"         => ($export ? "" : "R$ ") . number_config($liquid_user, $export),

            "status"            => $htmlstatus,
            "options"           => $htmlsoptions,                                       // options
            "cep"               => $cep ,
            "CMV"               => 0, 
            "date_remp"         => $row['order_status_update'] ? date_format(date_create($row['order_status_update']), 'd/m/y H:i') : '---',
        ); 
    }  
}

$json_data = array(
    "draw"              => intval($draw),
    "recordsTotal"      => intval($number_row),
    "recordsFiltered"   => intval($num_filter_row),
    "aaData"            => @$data,
    "filter"            => @$filter,
);

echo json_encode($json_data); 
 

