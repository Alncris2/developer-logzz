<?php
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
    $columnOrder = "orders.platform ". $columnSortOrder .", deadline";
    
} elseif ($columnName == "billing") {
    $columnOrder = "final_price";
    
} elseif ($columnName == "shipTax") {
    $columnOrder = "sales.sale_tax";
    
} elseif ($columnName == "freight") {
    $columnOrder = "sales.product_shipping_tax";
    
} elseif ($columnName == "comission") {
    $columnOrder = "orders.order_liquid_value";
    
} elseif ($columnName == "status") {
    $columnOrder = "orders.order_status";    
}


$verify_if_have_any_filter_active = !(empty($params['data-inicio'])) || !(empty($params['data-final'])) || !(empty($params['nome-cliente'])) || !(empty($params['produto'])) || !(empty($params['afiliado'])) || !(empty($params['status'])) || !(empty($params['numero-cliente-produto'])) || !(empty($params['operacao'])) || !(empty($params['operador']));

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

    $date_ids = $conn->prepare('SELECT order_id FROM orders WHERE order_date BETWEEN :start_date AND :final_date');
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

        //$produto = '%' . addslashes($params['produto']) . '%';

        $product_ids = $conn->prepare('SELECT order_id FROM orders WHERE product_name LIKE :product');
        $product_ids->execute(array('product' => '%' . $product . '%'));
        
        while ($product_id = $product_ids->fetch()) {
            array_push($filter_sale_result, $product_id['order_id']);
        }

        $filter_result = array_intersect($filter_result, $filter_sale_result);
    }

    //Filtro por NOME DO AFILIADO
    if (!(empty($params['afiliado']))) {
        $filter_affiliate_result = array();

        $afiliatte = $params['afiliado'];

        $query = "SELECT o.order_id FROM orders AS o INNER JOIN memberships AS m ON m.membership_affiliate_id = :aff_id AND m.membership_producer_id = :user__id INNER JOIN sales AS s WHERE o.sale_id = s.sale_id AND o.user__id = :aff_id2 AND o.order_number LIKE 'AFI%' AND m.membership_product_id = o.product_id"; 
        $affiliate_ids = $conn->prepare($query);
        $affiliate_ids->execute(['aff_id' => $afiliatte, 'aff_id2' => $afiliatte, 'user__id' => $user__id]);
        
        

        while ($order_id = $affiliate_ids->fetch()) {
            array_push($filter_affiliate_result, $order_id['order_id']);
        }
        $filter_result = array_intersect($filter_result, $filter_affiliate_result);
    }

    //Filtro por STATUS
    if (!(empty($params['status']))) {

        $filter_status_result = array();
        $status = addslashes($params['status']);

        $status_ids = $conn->prepare('SELECT order_id, order_status FROM orders WHERE order_status = :o_status');
        $status_ids->execute(array('o_status' => $status - 1));

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

        if($operator_id != "indefinido") {
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
        $query = "SELECT * ";
        if($columnName == "quantity"){
            $query .= ", CASE WHEN orders.platform <> null OR orders.platform != '' THEN orders.order_quantity ELSE sales.sale_quantity END AS quantity ";
        }
        if($columnName == "deadline"){
            $query .= ", CASE WHEN orders.order_status = 3 THEN orders.order_delivery_date ELSE orders.order_deadline END AS deadline ";
        }
        if($columnName == "billing"){
            $query .= ", CASE WHEN orders.use_coupon <> null OR orders.use_coupon != '' THEN orders.order_final_price ELSE sales.sale_price END AS final_price ";
        }

        $query .= "FROM orders INNER JOIN sales ON orders.sale_id = sales.sale_id AND orders.order_number NOT LIKE 'AFI%'"; // NOT LIKE AFI 
        $query .= " ORDER BY $columnOrder $columnSortOrder";
        $query .= " LIMIT $row, $rowperpage ";
        $stmt = $conn->prepare($query);
        $stmt->execute();
    } else {
        
        $stmt = $conn->prepare("SELECT * FROM orders INNER JOIN sales ON orders.sale_id = sales.sale_id WHERE user__id = :user__id");
        $stmt->execute(array('user__id' => $user__id));
        $num_filter_row = $stmt->rowCount();

        # Busca apenas os pedidos do usário, se ele não for ADM.
        $query = "SELECT *";
        if($columnName == "quantity"){
            $query .= ", CASE WHEN orders.platform <> null OR orders.platform != '' THEN orders.order_quantity ELSE sales.sale_quantity END AS quantity";
        }
        if($columnName == "deadline"){
            $query .= ", CASE WHEN orders.order_status = 3 THEN orders.order_delivery_date ELSE orders.order_deadline END AS deadline";
        }
        if($columnName == "billing"){
            $query .= ", CASE WHEN orders.use_coupon <> null OR orders.use_coupon != '' THEN orders.order_final_price ELSE sales.sale_price END AS final_price";
        }
        $query .= " FROM orders INNER JOIN sales ON orders.sale_id = sales.sale_id WHERE user__id = :user__id";
        $query .= " ORDER BY $columnOrder $columnSortOrder ";
        $query .= " LIMIT $row, $rowperpage ";
        $stmt = $conn->prepare($query);
        $stmt->execute(array('user__id' => $user__id));
    }

    $get_orders_list = $stmt->fetchAll();
    $number_row = $stmt->rowCount();

    $filter = false;

} else {

    $result = "'" . implode("','", $filter_result) . "'";

    $query = "";
    if ($UserPlan == 5) {

        $stmt = $conn->prepare("SELECT * FROM orders INNER JOIN sales ON orders.sale_id = sales.sale_id WHERE orders.order_id IN ($result) AND orders.order_number NOT LIKE 'AFI%'");  // NOT LIKE AFI
        $stmt->execute();
        $num_filter_row = $stmt->rowCount();

        $filterquery = "SELECT * FROM orders INNER JOIN sales ON orders.sale_id = sales.sale_id AND orders.order_number NOT LIKE 'AFI%'";

        #Busca todos dos pedidos se o usuário for ADM.
        $query = "SELECT * ";
        if($columnName == "quantity"){
            $query .= ", CASE WHEN orders.platform <> null OR orders.platform != '' THEN orders.order_quantity ELSE sales.sale_quantity END AS quantity ";
        }
        if($columnName == "deadline"){
            $query .= ", CASE WHEN orders.order_status = 3 THEN orders.order_delivery_date ELSE orders.order_deadline END AS deadline ";
        }
        if($columnName == "billing"){
            $query .= ", CASE WHEN orders.use_coupon <> null OR orders.use_coupon != '' THEN orders.order_final_price ELSE sales.sale_price END AS final_price ";
        }
        $query .= "FROM orders INNER JOIN sales ON orders.sale_id = sales.sale_id WHERE orders.order_id IN ($result) AND orders.order_number NOT LIKE 'AFI%'"; // NOT LIKE AFI
        $query .= " ORDER BY $columnOrder $columnSortOrder ";
        $query .= " LIMIT $row, $rowperpage ";
        $stmt = $conn->prepare($query);
        $stmt->execute();
    } else {

        $stmt = $conn->prepare("SELECT * FROM orders INNER JOIN sales ON orders.sale_id = sales.sale_id AND orders.order_id IN ($result) AND user__id = :user__id");
        $stmt->execute(array('user__id' => $user__id));
        $num_filter_row = $stmt->rowCount();

        # Busca apenas os pedidos do usário, se ele não for ADM.
        $query = "SELECT * ";
        
        if($columnName == "quantity"){
            $query .= ", CASE WHEN orders.platform <> null OR orders.platform != '' THEN orders.order_quantity ELSE sales.sale_quantity END AS quantity ";
        }
        if($columnName == "deadline"){
            $query .= ", CASE WHEN orders.order_status = 3 THEN orders.order_delivery_date ELSE orders.order_deadline END AS deadline ";
        }
        if($columnName == "billing"){
            $query .= ", CASE WHEN orders.use_coupon <> null OR orders.use_coupon != '' THEN orders.order_final_price ELSE sales.sale_price END AS final_price ";
        }
        $query .= "FROM orders INNER JOIN sales ON orders.sale_id = sales.sale_id WHERE orders.order_id IN ($result) AND user__id = :user__id";
        $query .= " ORDER BY $columnOrder $columnSortOrder ";
        $query .= " LIMIT $row, $rowperpage ";
        $stmt = $conn->prepare($query);
        $stmt->execute(array('user__id' => $user__id));
    }

    $get_orders_list = $stmt->fetchAll();
    $number_row = $stmt->rowCount();

    $filter = true;

}


foreach ($get_orders_list as $row) {  

    // pegar nome do produto
    $get_name_product = $conn->prepare("SELECT product_name FROM products AS p WHERE p.product_id = :product_id");
    $get_name_product->execute(['product_id' => $row['product_id']]);

    $name = @$get_name_product->fetch(\PDO::FETCH_ASSOC)['product_name'];

    if(mb_strpos($row['order_number'], 'AFI') !== false){
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

        $tax = $member_tax;
        $billingAFI = $member_commission + $member_tax;
    }

    // taxa do produtor
    // $tax_producer = ($row['order_final_price'] / 100) * $row['order_sale_tax'];
    $tax_producer =  $row['order_sale_tax'];

    //TD DO CLIENTE
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

    $htmlclient = "<a href='". SERVER_URI . "/meu-pedido/" . $row['order_number'] ."' target='_blank' title='Ver detalhes do Pedido'><i class='fa fa-eye'></i></a>
    <span class='text-nowrap'>". $client_name ."<br><small><i class='fab fa-whatsapp'></i>&nbsp;". $row['client_number'] ."</small></span>";

    //TD DA QUANTIDADE
    if ($row['platform'] == 'Braip' || $row['platform'] == 'Monetizze') {
        $quantity = $row['order_quantity'];
    } else {
        $quantity = $row['sale_quantity'];
    }

    if ($row['delivery_period'] == "manha") {
        $period = "Manhã";
    } else {
        $period = "Tarde";
    }

    //TD DA ENTREGA
    if ($row['platform'] == 'Braip') {
        $deadline = "<img src='/images/integrations/logos/braip.png' alt='Braip' width='100px'>";
    } else if ($row['platform'] == 'Monetizze') {
        $deadline = "<img src='/images/integrations/logos/monetizze.png' alt='Monetizze' width='100px'>";
    } else {
        if($row['order_status'] == 3) {
            $deadline = date_format(date_create($row['order_delivery_date']), 'd/m/y');
            $deadline .= "<br/>";
            $deadline .= date_format(date_create($row['order_delivery_date']), 'H:i');
        } else {
            $deadline =  date_format(date_create($row['order_deadline']), 'd/m/y') ."<br>". $period;
        }
    }   

    //TD DE VALORES;

    $final_price = $row['order_final_price'];
    
    $liquid  = $row['platform'] == null ? $row['order_liquid_value'] : 0;
    $freight = $row['sale_freight'];
    $ship_tax = mb_strpos($row['order_number'], 'AFI') !== false ? 0 : $row['order_sale_shipping_tax'];

    // var_dump(mb_strpos($row['order_number'], 'AFI') !== false);

    # CALCULO DE DESPESAS DESSE PRODUTO
    $expenses = 0;
    
    if($row['order_status'] == 3){
        if($_SESSION['UserPlan'] == 5){
            # Pegar id do operador responsável pela entrega
            $get_order_operator = $conn->prepare("SELECT * FROM local_operations_orders AS loo WHERE loo.order_id = :order_id");
            $get_order_operator->execute(['order_id' => $row['order_id']]);
            $data_order = $get_order_operator->fetch(\PDO::FETCH_ASSOC);
            
            # Busca as taxas de entrega da operação do operador
            $get_delivery_taxes = $conn->prepare("SELECT * FROM operations_delivery_taxes WHERE operation_id = :operation_id AND operator_id = :operator_id");
            $get_delivery_taxes->execute(array("operation_id" => $data_order['operation_id'], "operator_id" => $data_order['responsible_id']));

            # Busca os locais da operação do usuário
            $get_order_locale = $conn->prepare("SELECT id, city FROM operations_locales WHERE operation_id = :operation_id");
            $get_order_locale->execute(array("operation_id" => $data_order['operation_id']));

            $locales = array();
            $cities = $get_order_locale->fetchAll();
            $delivery_taxes = $get_delivery_taxes->fetchAll();

            # Taxa de entrega incluindo na despesa
            $expenses += $delivery_taxes[0]['complete_delivery_tax'];

            # Relaciona as taxas de entrega aos locais em um array de chave-valor
            foreach ($cities as $city) {
                for ($i = 0; $i < sizeof($delivery_taxes); $i++) {
                    $tax = $delivery_taxes[$i];
                    if ($city["id"] == $tax["operation_locale"]) {
                        $locales[$city["city"]] = $tax["complete_delivery_tax"] . "--" . $tax["frustrated_delivery_tax"];
                    }
                }
            }

            //TD DO CLIENTE
            $address = $row["client_address"];
            $city_state = explode("<br>", $address)[3];
            $city = explode(", ", $city_state)[0];

            // if(strpos(@$locales[$city], '--')) { 
                
            //     $expenses += array_sum(explode("--", $locales[$city]));
            
            //     // pegar id do operador logistico
            //     $get_responsible_id = $conn->prepare("SELECT * FROM logistic_operator AS lo WHERE lo.operator_id = :operator_id");
            //     $get_responsible_id->execute(['operator_id' => @$data_order['responsible_id']]);
            //     $data_responsible = $get_responsible_id->fetch(\PDO::FETCH_ASSOC);
    
            //     // $get_operation_id = $conn->prepare("SELECT lo.*, u.created_at FROM logistic_operator lo INNER JOIN users u ON lo.user_id=u.user__id WHERE lo.user_id = :user__id");
            //     // $get_operation_id->execute(array("user__id" => @$data_responsible['user_id']));
            //     // $taxes_card = $get_operation_id->fetch();
    
            //     // if ($row["order_payment_method"] == "debit" || $row["order_payment_method"] == "credit"){
            //     //     if ($row["order_payment_method"] == "debit") {
            //     //         $maq_tax = $row["order_final_price"] * @$taxes_card["debito_tax"];
            //     //     }  else if ($row["order_payment_method"] == "credit" && $row["credit_times"]) {
            //     //         $maq_tax = $row["order_final_price"] * (@$taxes_card["credito_tax_" . $row["credit_times"] . "x"] / 100);
            //     //     }
    
            //     //     $expenses += @$maq_tax; 
            //     // }
            // }
          
           
        }
    }
    $get_responsible_id = $conn->prepare("SELECT * FROM logistic_operator AS lo WHERE lo.operator_id = :operator_id");
    $get_responsible_id->execute(['operator_id' => @$data_order['responsible_id']]);
    $data_responsible = $get_responsible_id->fetch(\PDO::FETCH_ASSOC);

    $get_operation_id = $conn->prepare("SELECT lo.*, u.created_at FROM logistic_operator lo INNER JOIN users u ON lo.user_id=u.user__id WHERE lo.user_id = :user__id");
    $get_operation_id->execute(array("user__id" => @$data_responsible['user_id']));
    $taxes_card = $get_operation_id->fetch();

    if ($row["order_payment_method"] == "debit" || $row["order_payment_method"] == "credit"){
        if ($row["order_payment_method"] == "debit") {
            $maq_tax = $row["order_final_price"] * (@$taxes_card["debito_tax"] / 100);
        }  else if ($row["order_payment_method"] == "credit" && $row["credit_times"]) {
            $maq_tax = $row["order_final_price"] * (@$taxes_card["credito_tax_" . $row["credit_times"] . "x"] / 100);
        }

        $expenses += @$maq_tax; 
    }
    if($row['platform'] == null){

        // faturamento do produtor 
        $expenses += $row['order_liquid_value'];
    
        // faturamento do afiliado 
        $order_afi = "AFI" . $row['order_number'];
        
        $get_sale_afi = $conn->prepare("SELECT * FROM orders AS o INNER JOIN sales AS s WHERE o.order_number = :order_number");
        $get_sale_afi->execute(['order_number' => $order_afi]);
        $data_afi_sale = $get_sale_afi->fetch(\PDO::FETCH_ASSOC);
    
        if($get_sale_afi->rowCount() > 0){
            $expenses += $data_afi_sale['order_liquid_value'];
        }
        
    }

    if($row['platform'] !== null){
        // faturamento do produtor 
        $expenses_platform = $row['order_tracking_value']; // fatur

        // pegar valor de frete da conta
        $get_freight_account = $conn->prepare("SELECT * FROM subscriptions AS s INNER JOIN users AS u WHERE u.user__id = s.user__id AND s.user__id = :user__id");
        $get_freight_account->execute(['user__id' => $row['user__id']]);
        $profit_platform = $get_freight_account->fetch(\PDO::FETCH_ASSOC)['user_plan_shipping_tax'];
    }


    if ($row['platform'] == null) {
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
                break;
            case 4:
                $btn_classes = "light badge-dark";
                $status_string = "Frust.";
                break;
            case 5:
                $btn_classes = "light badge-danger";
                $status_string = "Canc.";
                break;
            default:
                $btn_classes = "light badge-success";
                $status_string = "Agen.";
                break;
        }

        $htmlstatus = "<span class='badge badge-xs ". $btn_classes ." mb-1'>". $status_string ."</span>";

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

        $htmlstatus = "<span class='badge badge-xs ". $btn_classes ." mb-1'>". $status_string ."</span>";
    }

    $htmlsoptions = "";
    if ($UserPlan == 5) {

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
                        $htmlsoptions .= "<a class='dropdown-item' onclick='updateOrderStatus(this)' data-status='0' data-checkout='1' data-id='". $row['order_id'] ."' href='#'>Agendado</a>";
                        $htmlsoptions .= "<a class='dropdown-item' onclick='updateOrderStatus(this)' data-status='2' data-checkout='1' data-id='". $row['order_id'] ."' href='#'>Atrasado</a>";
                        $htmlsoptions .= "<a class='dropdown-item' onclick='updateOrderStatus(this)' data-status='5' data-checkout='1' data-id='". $row['order_id'] ."' href='#'>Cancelado</a>";
                        $htmlsoptions .= "<a class='dropdown-item' href='". SERVER_URI ."/pedido/frustrar/". $row['order_number'] ."'>Frustrado</a>";
                        $htmlsoptions .= "<a class='dropdown-item' href='". SERVER_URI ."/pedido/completar/". $row['order_id'] ."'>Completo</a>";
                        $htmlsoptions .= "<a class='dropdown-item' href='". SERVER_URI ."/pedido/reagendar/". $row['order_id'] ."'>Reagendado</a>";
                        $htmlsoptions .= "<div class='dropdown-divider'></div>";
                        $htmlsoptions .= "<a class='dropdown-item' onclick='deleteOrderLink(this)' data-id='". $row['order_number'] ."' href='#'>Deletar</a>";

                    } else {
                        $htmlsoptions .= "<a class='dropdown-item' onclick='updateOrderStatus(this)' data-status='6' data-checkout='' data-id='". $row['order_id'] ."'>À Enviar</a>";
                        $htmlsoptions .= "<a class='dropdown-item' onclick='updateOrderStatus(this)' data-status='7' data-checkout='' data-id='". $row['order_id'] ."' >Enviando</a>";
                        $htmlsoptions .= "<a class='dropdown-item' href='". SERVER_URI ."/pedido/enviando/". $row['order_id'] ."'>Enviado</a>";
                    }
            $htmlsoptions .= 
                "</div>
            </div>
        </div>";
    }

    // var_dump(mb_strpos($row['order_number'], 'AFI') !== false);

    $billingByOrder = mb_strpos($row['order_number'], 'AFI') !== false ? $billingAFI : $final_price;
    $fatur = $row['platform'] == null ? $billingByOrder : $profit_platform ; // lucro
    $expenses_dinamic = $row['platform'] == null ? $expenses : $expenses_platform; // despesas 
    $profit = $fatur - $expenses_dinamic; // lucro

    if($_SESSION['UserPlan'] == 5){

        $data[] = array(
            "order"         => date_format(date_create($row['order_date']), 'd/m/y \<\b\r\> H:i'), 	    //Pedido
            "client"        => $htmlclient, 	                                    // Cliente
            "product"       => $name,               	                            // Produto
            "sale"          => $row['sale_name'], 	                                // Oferta
            "quantity"      => $quantity,                                           // Qnt
            "deadline"      => $deadline,	                                        // Data Entrega
            "billing"       => "R$ ". number_format($fatur, 2, ',', '.'),	    // Fatur
            "expenses"      => "R$ ". number_format($expenses_dinamic, 2, ",", "."), // despesas
            "profit"        => "R$ ". number_format(($profit), 2, ",", "."), // lucro 
            "status"        => $htmlstatus,                                         // status
            "options"       => $htmlsoptions,                                       // options
        );

    }else{
        $data[] = array(
            "order"         => date_format(date_create($row['order_date']), 'd/m/y \<\b\r\> H:i'), 	    //Pedido
            "client"        => $htmlclient, 	                                    // Cliente
            "product"       => $name,               	                            // Produto
            "sale"          => $row['sale_name'], 	                                // Oferta
            "quantity"      => $quantity,                                           // Qnt
            "deadline"      => $deadline,	                                        // Entreg
            "billing"       => "R$ ". number_format(mb_strpos($row['order_number'], 'AFI') !== false ? $billingAFI : $final_price, 2, ',', '.'),	    // Fatur
            "shipTax"       => "R$ ". number_format(mb_strpos($row['order_number'], 'AFI') !== false ? $tax : $tax_producer, 2, ',', '.'),	// Taxa
            "freight"       => "R$ ". number_format($ship_tax, 2, ',', '.'),	    // Entreg
            "comission"     => "R$ ". number_format($liquid, 2, ',', '.'),          // Comis
            "status"        => $htmlstatus,                                         // status
            "options"       => $htmlsoptions,                                       // options
        );
    

    }

    $calc += $profit;
}

$json_data = array(
    "draw"              => intval($draw),
    "recordsTotal"      => intval($number_row),
    "recordsFiltered"   => intval($num_filter_row),
    "aaData"            => @$data,
    "filter"            => @$filter,
    "soma"              => @$calc
);

echo json_encode($json_data);
    