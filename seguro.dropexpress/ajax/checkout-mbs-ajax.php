<?php
// error_reporting(-1);              
// ini_set('display_errors', 1);   
require_once(dirname(__FILE__) . '/../includes/config.php');
date_default_timezone_set('America/Sao_Paulo'); 


if ($_GET['action'] == "get-delivery-days") {
    if (empty($_GET['uf']) || empty($_GET['cidade'])) {
        $feedback = array('status' => 2, 'msg' => "Você precisa informar um CEP válido.");
        echo json_encode($feedback);
        exit;
    }

    $uf = addslashes($_GET['uf']);
    $cidade = addslashes($_GET['cidade']);
    $produto = addslashes($_GET['produto']);

    $local_operations = $conn->prepare('SELECT lop.operation_delivery_days, lop.operation_id  FROM local_operations lop INNER JOIN operations_locales ol ON lop.operation_id = ol.operation_id WHERE lop.uf = :uf AND ol.city like :city');

    try {
        $local_operations->execute(array("uf" => $uf, "city" => '%' . $cidade . '%'));
        $data = null;
        
        if($delivery_days = $local_operations->fetch()){
            $data = $delivery_days['operation_delivery_days'];

            $invetoriesStmt = $conn->prepare('SELECT product_delivery_days FROM inventories WHERE inventory_locale_id = :operation_id AND inventory_product_id = :product_id');
            $invetoriesStmt->execute(array( "operation_id" => $delivery_days['operation_id'], 'product_id' => $produto ));

            if($dadosInventory = $invetoriesStmt->fetch()[0]){
                $data = $dadosInventory; 
            }
        }

        $feedback = array('status' => 1, 'msg' => ':)', 'delivery_days' => $data);

    } catch (PDOException $e) {
        $error = 'ERROR: ' . $e->getMessage();
        $feedback = array('status' => '0', 'msg' => $error);
    }

    echo json_encode($feedback);
    exit;
}

if (!(isset($_POST['action']))) {
    $feedback = array('status' => 0, 'msg' => 'Atualize a página e tente novamente.', 'data' => [ !isset($_GET) ?'': $_GET, !isset($_POST) ?'': $_POST]);
    echo json_encode($feedback);
    exit;
}

# Recebe e valida a Data de Entrega do Pedido
if ($_POST['action'] == "done-order" && ($_POST['data-pedido'] == "Data" || empty($_POST['data-pedido']))) {
    $feedback = array('status' => 2, 'msg' => "Você precisa informar a data de recebimento.");
    echo json_encode($feedback);
    exit;
}

# Verifica exista a url_de_checkout, que é única
# e utilizada para obter as informações
# do produto e da oferta.
if (!(isset($_POST['url_checkout']))) {
    header("Location: /pagina-nao-encontrada");
    exit;
}

function validarTelefone($valor) {

    //processa a string mantendo apenas números no valor de entrada.
    $valor = preg_replace("/[^0-9]/", "", $valor); 
        
    $lenValor = strlen($valor);   
    
    //DD e número de telefone não podem começar com zero.
    if($valor[0] == "0" || $valor[2] == "0")
        return false;

    //validando a quantidade de caracteres de telefone fixo ou celular.
    if($lenValor == 10 || $lenValor == 11) {
        return true;
    }   
    
    return false;
}

if ($_POST['action'] == "done-order") {

    # Recebe os inputs do comprador
    $cep         =  addslashes($_POST['cep-pedido']);
    $rua         =  addslashes($_POST['endereco-pedido']);
    $numero        =  addslashes($_POST['numero-pedido']);
    $bairro     =  addslashes($_POST['bairro-pedido']);
    $cidade     =  addslashes($_POST['cidade-pedido']);
    $estado     =  addslashes($_POST['estado-pedido']);
    $referencia =  addslashes($_POST['referencia-pedido']);
    @$cupom         =  addslashes($_POST['cupom-pedido']);
    
    if (empty($rua) || mb_strpos($rua, '...') !== false) {
        $feedback = array('status' => 2, 'msg' => "Verifique o campo Rua.", 'title' => 'Cidade não encontrada');
        echo json_encode($feedback);
        exit;
    }
 
     $valid_numero = str_replace(' ', '', $numero);
    if (empty($valid_numero)) {
        $feedback = array('status' => 2, 'msg' => "Verifique o campo Número.", 'title' => 'Número não informado');
        echo json_encode($feedback); 
        exit;
    }


    if (empty($bairro) || mb_strpos($bairro, '...') !== false) {
        $feedback = array('status' => 2, 'msg' => "Verifique o campo Bairro.", 'title' => 'Bairro não encontrado');
        echo json_encode($feedback);
        exit;
    }

    if (empty($cidade) || mb_strpos($cidade, '...') !== false) {
        $feedback = array('status' => 2, 'msg' => "Verifique o campo Cidade.", 'title' => 'Cidade não encontrada');
        echo json_encode($feedback);
        exit;
    }

    if (empty($estado) || mb_strpos($estado, '...') !== false) {
        $feedback = array('status' => 2, 'msg' => "Verifique o campo Estado.", 'title' => 'Estado não encontrado');
        echo json_encode($feedback);
        exit;
    }

    $local_operations = $conn->prepare('SELECT lop.operation_id  FROM local_operations lop INNER JOIN operations_locales ol ON lop.operation_id = ol.operation_id WHERE lop.uf = :uf AND ol.city like :city');
    $local_operations->execute(array("uf" => $estado, "city" => '%' . $cidade . '%'));
    if(!$operation_id = $local_operations->fetch()){
        $feedback = array('status' => 2, 'title' => 'Poxa :(', 'msg' => 'Ainda não atendemos na região do CEP informado.', 'type' => 'warning');
        echo json_encode($feedback);  
        exit; 
    }

    # Formata o endereço
    $address  = "Rua " . $rua . ", nº " . $numero . "<br>";
    $address .= "Bairro " . $bairro . "<br>";
    $address .= $referencia . "<br>";
    $address .= $cidade . ", " . $estado . "<br>";
    $address .= "CEP: " . $cep;

    # Recebe os demais inputs do comprador
    $name                     = addslashes($_POST['nome-pedido']);
    $whats                     = addslashes($_POST['whatsapp-pedido']);
    if (validarTelefone($whats) == false) {
        $feedback = array('status' => 2, 'msg' => "O WhatsApp esta incorreto.", '');
        echo json_encode($feedback);
        exit; 
    }


    $data                     = addslashes($_POST['data-pedido']);
    $delivery_period         = addslashes($_POST['periodo-pedido']);
    $sale_url                 = addslashes($_POST['url_checkout']);
    $order_deadline          = $data;
    $order_delivery_date     = date('Y-m-d H-i-s');
    $order_delivery_time     = date('Y-m-d H-i-s');
    $order_id                 = 0;
    $order_status            = 0;
    $email                     = isset($_POST['email-pedido']) ? addslashes($_POST['email-pedido']) : null;
    if (isset($_POST['email-pedido'])) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $feedback = array('status' => 2, 'msg' => "O email está incorreto.", '');
            echo json_encode($feedback);
            exit;
        }
    }

    require(dirname(__FILE__) . '/../includes/classes/validCPFCNPJ.php');

    $documento = addslashes($_POST['documento-pedido']);
    $cpf_cnpj = new ValidaCPFCNPJ($documento);
    // Verifica se o CPF ou CNPJ é válido
    if (!$cpf_cnpj->valida()) {
        $feedback = array('status' => 0, 'msg' => 'O dados do CPF / CNPJ parece incorretos...', 'title' => "Confira seu documento");
        echo json_encode($feedback);
        exit;
    }

    # Requisita o arquivo global de configuração e starta a sessão.
    require_once(dirname(__FILE__) . '/../includes/config.php');
    session_name(SESSION_NAME);
    session_start();

    # Formata a Data de Entrega
    $order_deadline = pickerDateFormate($data);


    @$hotcode = $_SESSION['_mbsid'];    
    if ($hotcode == "" || $hotcode == null) {
        @$hotcode = $_POST['hotcode'];
    }

    # Busca Dados da Oferta no Banco de Dados
    $stmt = $conn->prepare('SELECT * FROM sales INNER JOIN products ON products.product_id = sales.product_id WHERE sale_url = :sale_url');
    $stmt->execute(array('sale_url' => $sale_url));
    while ($row = $stmt->fetch()) {
        $product_name = $row['product_name'];
        $product_id = $row['product_id'];
        $sale_id = $row['sale_id'];
        $sale_quantity = $row['sale_quantity'];
        $sale_price = $row['sale_price'];
        // $sale_price = $row['sale_price'] + $row['sale_freight'];
        $sale_produto_shipping_tax = $row['product_shipping_tax'];
        $sale_tax = $row['sale_tax'];
        $user__id = $row['user__id'];
        $product_commission = $row['product_commission'];
        @$url_upsell = $row['url_upsell'];
        $order_number = date('dm') . "S" . $sale_id . "P" . random_int(1111, 9999);

        $verify_duplicate_order_user = $conn->prepare('SELECT COUNT(*) FROM orders WHERE (client_number = :client_number OR client_document = :client_document) AND product_id = :product_id AND order_status NOT IN (3,4,5,9)');
        $verify_duplicate_order_user->execute(array('client_number' => $whats, 'client_document' => $documento, 'product_id' => $product_id)); 
        if ($verify_duplicate_order_user->fetch()[0] > 0) {
            $feedback = array('status' => 0, 'msg' => 'Já existe um pedido sem finalização em nome desse(a) cliente, aguarde a finalização antes de tentar um novo agendamento.', 'title' => "warning");
            echo json_encode($feedback);
            exit;
        }
        
        $invetoriesStmt = $conn->prepare('SELECT product_delivery_days FROM inventories WHERE inventory_locale_id = :operation_id AND inventory_product_id = :product_id AND inventory_quantity > 0 AND ship_locale = 0');
        $invetoriesStmt->execute(array( "operation_id" => $operation_id['operation_id'], 'product_id' => $product_id ));
        if($Inventory = $invetoriesStmt->rowCount() <= 0){     
            $feedback = array('status' => 4, 'title' => 'Poxa :(', 'msg' => 'Me parece que nosso estoque desse produto acabou para a região do CEP informado', 'type' => 'error'); 
            echo json_encode($feedback); 
            exit;
        } 
 
        $verify_unique_order_code = $conn->prepare('SELECT * FROM orders WHERE order_number = :order_number');
        $verify_unique_order_code->execute(array('order_number' => $order_number)); 

        if (!($verify_unique_order_code->rowCount() == 0)) {
            do {

                $order_number = date('dm') . "S" . $sale_id . "P" . random_int(1111, 9999);

                $verify_unique_order_code = $conn->prepare('SELECT order_id FROM orders WHERE order_number = :order_number'); 
                $verify_unique_order_code->execute(array('order_number' => $order_number));
            } while ($stmt->rowCount() != 0);
        } 
        $member_order_number = "AFI" . $order_number;
        $feedback = array('status' => 1, 'msg' => $order_number);
    }
    $subscription = array();
    $subscriptionQuery = $conn->prepare("SELECT * FROM  subscriptions WHERE user__id =" . $user__id . " ;");
    $subscriptionQuery->execute();

    $subscription = $subscriptionQuery->fetch()["user_plan_tax"];

    $sale_tax =  number_format($sale_price * $subscription, 2, ".", "");

    # Verifica se existe comissão personalizada para essa oferta
    $verify_custom_commision = $conn->prepare('SELECT meta_value FROM sales_meta WHERE sale_id = :sale_id AND meta_key LIKE "custom_commission" ');
    $verify_custom_commision->execute(array('sale_id' => $sale_id));

    if ($verify_custom_commision->rowCount() == 1) {
        $custom_commision = $verify_custom_commision->fetch();
        if($custom_commision['meta_value'] > 0)
            $product_commission = $custom_commision['meta_value'];
    }


    # Verifica se a Afiliação é existente e ativa

    $verfify_membership = $conn->prepare('SELECT membership_status FROM memberships WHERE memberships_hotcode = :memberships_hotcode AND membership_product_id = :membership_product_id');
    $verfify_membership->execute(array('memberships_hotcode' => $hotcode, 'membership_product_id' => $product_id));
    $membership_status = $verfify_membership->fetch();
    @$membership_status = $membership_status['membership_status'];

    if ($membership_status == 'ATIVA') {
        $has_member = true;
    } else {
        $has_member = false;
    }

    # Busca os Dados do Produtor no Banco de Dados
    $get_product_comission_term = $conn->prepare('SELECT user_payment_term, user_plan_shipping_tax, user_plan_tax FROM users INNER JOIN subscriptions ON subscriptions.user__id = users.user__id WHERE users.user__id = :user__id');
    $get_product_comission_term->execute(array('user__id' =>  $user__id));

    while ($row = $get_product_comission_term->fetch()) {
        $user_payment_term  = $row['user_payment_term'];
        $user_plan_ship_tax = $row['user_plan_shipping_tax'];
        $producer_plan_tax = $row['user_plan_tax'];
    }


    # Busca os Dados do Afiliado no BD
    $get_member_infos = $conn->prepare('SELECT user_payment_term, user_plan_tax, u.user__id FROM users AS u INNER JOIN memberships AS m ON u.user__id = m.membership_affiliate_id INNER JOIN subscriptions AS s ON s.user__id = m.membership_affiliate_id WHERE m.memberships_hotcode = :memberships_hotcode');
    $get_member_infos->execute(array('memberships_hotcode' => $hotcode));

    while ($row = $get_member_infos->fetch()) {
        $member_payment_term  = $row['user_payment_term'];
        $member_plan_tax = $row['user_plan_tax'];
        $member_user__id = $row['user__id'];
    }

    // $feedback = array('status' => 3, 'msg' => $member_user__id);
    // echo json_encode($feedback);
    // exit;

    # Cálculo Desconto Cupom
    if (!(empty($cupom))) {
        $stmt = $conn->prepare('SELECT * FROM coupons WHERE coupon_string = :coupon_string');
        $stmt->execute(array('coupon_string' => $cupom));


        if ($stmt->rowCount() != 0) {
            while ($coupon_data = $stmt->fetch()) {
                $coupon_sales = explode(",", $coupon_data['coupon_linked_sales']);

                if (in_array($sale_id, $coupon_sales)) {
                    $discount = (100 - $coupon_data['coupon_percent']) / 100;
                    $order_final_price = $sale_price * $discount;
                    $use_coupon = $cupom;
                } else {
                    $order_final_price = $sale_price;
                    $use_coupon = 0;
                }
            }
        }
    } else {

        $order_final_price = $sale_price;
        $use_coupon = 0;
    }

    # Cálculo Custos + Comissões
    if ($has_member == true) {

        $meta_key = "custom_commission_" . $hotcode;
        $get_custom_commission = $conn->prepare('SELECT meta_value FROM sales_meta WHERE sale_id = :sale_id AND meta_key = :meta_key');
        $get_custom_commission->execute(array('sale_id' => $sale_id, 'meta_key' => $meta_key));

        if ($get_custom_commission->rowCount() != 0) {
            $product_commission = $get_custom_commission->fetch();
            if($custom_commision['meta_value'] > 0)
                $product_commission = $product_commission['meta_value'];
        }

        # Comissão do Afiliado = Valor da Venda * % de Comissão - Taxa Afiliado
        $member_comission                       = round(($order_final_price * ($product_commission / 100)), 2);
        $member_total_tax                       = round(($member_comission * $member_plan_tax), 2);
        $member_comission                       = round(($member_comission - $member_total_tax), 2);

        # Data de Liberação Afiliado
        $member_order_commission_timestamp      = "+" . $member_payment_term . "days";
        $member_order_commission_date           = date('Y-m-d', strtotime($order_deadline . $member_order_commission_timestamp));

        # Custos do Produtor = Comissão Afiliado + Taxa Afiliado + Entrega + Taxa Produtor 
        $costs = round(($member_comission + $member_total_tax + $user_plan_ship_tax), 2);

        // $meta_member_comission = $member_comission + $member_total_tax;

        $order_liquid_value                     = round(($order_final_price - $costs), 2);

        # Custo Total = Comissão do Afiliado + Entrega + Taxa Produtor
        $producer_total_tax = round(($order_liquid_value * $producer_plan_tax), 2);
        $order_liquid_value = round(($order_liquid_value - $producer_total_tax), 2);

        $order_commission_timestamp             = $user_payment_term . "days";
        $order_commission_date                  = date('Y-m-d', strtotime($order_deadline . $order_commission_timestamp));
    } else {

        $costs = $user_plan_ship_tax;

        $order_liquid_value                     = round(($order_final_price - $costs), 2);

        # Custo Total = Comissão do Afiliado + Entrega + Taxa Produtor
        $producer_total_tax = round(($order_liquid_value * $producer_plan_tax), 2);
        $order_liquid_value = round(($order_liquid_value - $producer_total_tax), 2);

        $order_commission_timestamp             = "+" . $user_payment_term . "days";
        $order_commission_date                  = date('Y-m-d', strtotime($order_deadline . $order_commission_timestamp));
    }

    #$feedback = array('status' => 2, 'msg' => "Prod.: " . $order_liquid_value . " | Afil.: " . $member_comission . " | Sist.: " . ($sale_tax + $member_total_tax) . " | Entr.: " . $user_plan_ship_tax . " (" . ($order_liquid_value + $member_comission + $sale_tax + $user_plan_ship_tax + $member_total_tax + ($order_liquid_value * $producer_plan_tax)) .")");

    # Cria Pedido Original
    $stmt = $conn->prepare('INSERT INTO orders(order_id, user__id, sale_id, order_sale_price, order_sale_shipping_tax, order_sale_tax, product_id, product_name, order_date, order_deadline, order_status, order_delivery_date, client_name, client_document, client_address, client_number, client_email, order_delivery_time, order_number, delivery_period, use_coupon, order_final_price, order_liquid_value, order_commission_date) VALUES (:order_id, :user__id, :sale_id, :order_sale_price, :order_sale_shipping_tax, :order_sale_tax, :product_id, :product_name, :order_date, :order_deadline, :order_status, :order_delivery_date, :client_name, :client_document, :client_address, :client_number, :client_email, :order_delivery_time, :order_number, :delivery_period, :use_coupon, :order_final_price, :order_liquid_value, :order_commission_date)');

    # Cria os Metadados do pedido
    $order_meta_prod_comm       = $conn->prepare('INSERT INTO orders_meta (meta_id, order_number, meta_key, meta_value) VALUES (:meta_id, :order_number, :meta_key, :meta_value)');
    $order_meta_prod_tax        = $conn->prepare('INSERT INTO orders_meta (meta_id, order_number, meta_key, meta_value) VALUES (:meta_id, :order_number, :meta_key, :meta_value)');
    $order_meta_prod_tax_base   = $conn->prepare('INSERT INTO orders_meta (meta_id, order_number, meta_key, meta_value) VALUES (:meta_id, :order_number, :meta_key, :meta_value)');
    $order_meta_ship_tax        = $conn->prepare('INSERT INTO orders_meta (meta_id, order_number, meta_key, meta_value) VALUES (:meta_id, :order_number, :meta_key, :meta_value)');

    # Cria Pedido Afiliado
    if ($has_member == true) {
        $stmt_mirror = $conn->prepare('INSERT INTO orders(order_id, user__id, sale_id, product_id, product_name, order_date, order_deadline, order_status, order_delivery_date, client_name, client_document, client_address, client_number, client_email, order_delivery_time, order_number, delivery_period, use_coupon, order_final_price, order_liquid_value, order_commission_date) VALUES (:order_id, :user__id, :sale_id, :product_id, :product_name, :order_date, :order_deadline, :order_status, :order_delivery_date, :client_name, :client_document, :client_address, :client_number, :client_email, :order_delivery_time, :order_number, :delivery_period, :use_coupon, :order_final_price, :order_liquid_value, :order_commission_date)');

        # Cria os Metadados do pedido
        $order_meta_memb_comm       = $conn->prepare('INSERT INTO orders_meta (meta_id, order_number, meta_key, meta_value) VALUES (:meta_id, :order_number, :meta_key, :meta_value)');
        $order_meta_memb_comm_base  = $conn->prepare('INSERT INTO orders_meta (meta_id, order_number, meta_key, meta_value) VALUES (:meta_id, :order_number, :meta_key, :meta_value)');
        $order_meta_memb_tax        = $conn->prepare('INSERT INTO orders_meta (meta_id, order_number, meta_key, meta_value) VALUES (:meta_id, :order_number, :meta_key, :meta_value)');
        $order_meta_memb_tax_base   = $conn->prepare('INSERT INTO orders_meta (meta_id, order_number, meta_key, meta_value) VALUES (:meta_id, :order_number, :meta_key, :meta_value)');
        $order_meta_hotcode         = $conn->prepare('INSERT INTO orders_meta (meta_id, order_number, meta_key, meta_value) VALUES (:meta_id, :order_number, :meta_key, :meta_value)');
    }

    try {
        $stmt->execute(array('order_id' => $order_id, 'user__id' => $user__id, 'sale_id' => $sale_id, 'order_sale_price' => $sale_price, 'order_sale_shipping_tax' => $sale_produto_shipping_tax, 'order_sale_tax'  => $sale_tax, 'product_id' => $product_id, 'product_name' => $product_name, 'order_date' => $order_delivery_date, 'order_deadline' => $order_deadline, 'order_status' => $order_status, 'order_delivery_date' => $order_delivery_date, 'client_name' => $name, 'client_document' => $documento, 'client_address' => $address, 'client_number' => $whats, 'client_email' => $email, 'order_delivery_time' => $order_delivery_date, 'order_number' => $order_number, 'delivery_period' => $delivery_period, 'use_coupon' => $use_coupon, 'order_final_price' => $order_final_price, 'order_liquid_value' => $order_liquid_value, 'order_commission_date' => $order_commission_date));
        $order_meta_prod_comm->execute(array('meta_id' => 0, 'order_number' => $order_number, 'meta_key' => "producer_commission", 'meta_value' => $order_liquid_value));
        $order_meta_prod_tax->execute(array('meta_id' => 0, 'order_number' => $order_number, 'meta_key' => "producer_tax", 'meta_value' => $producer_total_tax));
        $order_meta_prod_tax_base->execute(array('meta_id' => 0, 'order_number' => $order_number, 'meta_key' => "producer_tax_base", 'meta_value' => $producer_plan_tax));
        $order_meta_ship_tax->execute(array('meta_id' => 0, 'order_number' => $order_number, 'meta_key' => "ship_tax", 'meta_value' => $user_plan_ship_tax));

        $get_last_order = $conn->prepare("SELECT order_id FROM orders ORDER BY order_id DESC LIMIT 1");
        $get_last_order->execute();
        $last_id = $get_last_order->fetch()['order_id'];

        $get_local_operation = $conn->prepare("SELECT * FROM local_operations lop INNER JOIN operations_locales ol ON lop.operation_id=ol.operation_id WHERE lop.uf=:uf AND ol.city like :city");
        $get_local_operation->execute(array("uf" => $estado, "city" => "%" . $cidade . "%"));

        if ($local_operation = $get_local_operation->fetch()) {
            $add_operation_order = $conn->prepare("INSERT INTO local_operations_orders(operation_id, order_id) VALUES (:operation_id, :order_id)");
            $add_operation_order->execute(array("operation_id" => $local_operation["operation_id"], "order_id" => $last_id)); 
        }

        if ($has_member == true) {
            $stmt_mirror->execute(array('order_id' => $order_id, 'user__id' => $member_user__id, 'sale_id' => $sale_id, 'product_id' => $product_id, 'product_name' => $product_name, 'order_date' => $order_delivery_date, 'order_deadline' => $order_deadline, 'order_status' => $order_status, 'order_delivery_date' => $order_delivery_date, 'client_name' => $name, 'client_document' => $documento, 'client_address' => $address, 'client_number' => $whats, 'client_email' => $email, 'order_delivery_time' => $order_delivery_date, 'order_number' => $member_order_number, 'delivery_period' => $delivery_period, 'use_coupon' => $use_coupon, 'order_final_price' => $order_final_price, 'order_liquid_value' => $member_comission, 'order_commission_date' => $order_commission_date));

            $order_meta_memb_comm->execute(array('meta_id' => 0, 'order_number' => $order_number, 'meta_key' => "member_commission", 'meta_value' => $member_comission));
            $order_meta_memb_comm_base->execute(array('meta_id' => 0, 'order_number' => $order_number, 'meta_key' => "member_commission_base", 'meta_value' => $product_commission));
            $order_meta_memb_tax->execute(array('meta_id' => 0, 'order_number' => $order_number, 'meta_key' => "member_tax", 'meta_value' => $member_total_tax));
            $order_meta_memb_tax_base->execute(array('meta_id' => 0, 'order_number' => $order_number, 'meta_key' => "member_tax_base", 'meta_value' => $member_plan_tax));
            $order_meta_hotcode->execute(array('meta_id' => 0, 'order_number' => $order_number, 'meta_key' => "membership_hotcode", 'meta_value' => $hotcode));

            $get_last_order = $conn->prepare("SELECT order_id FROM orders ORDER BY order_id DESC LIMIT 1");
            $get_last_order->execute();

            $get_local_operation = $conn->prepare("SELECT * FROM local_operations lop INNER JOIN operations_locales ol ON lop.operation_id=ol.operation_id WHERE lop.uf=:uf AND ol.city=:city");
            $get_local_operation->execute(array("uf" => $estado, "city" => $cidade));

            if ($local_operation = $get_local_operation->fetch()) {
                $add_operation_order = $conn->prepare("INSERT INTO local_operations_orders(operation_id, order_id) VALUES (:operation_id, :order_id)");
                $add_operation_order->execute(array("operation_id" => $local_operation["operation_id"], "order_id" => $get_last_order->fetch()['order_id']));
            }
        }

        $stmt_insert_historic = $conn->prepare('INSERT INTO order_details ( order_number, order_status ) VALUES ( :order_number, :order_status )');
        $stmt_insert_historic->execute(array('order_number' => $order_number, 'order_status' => 0 ));


        if (empty($url_upsell) || $url_upsell == NULL || $url_upsell == 0) {
            $url = SERVER_URI . '/meu-pedido/' . $order_number;
        } else { 

            @session_name(SESSION_NAME);
            @session_start();
            $_SESSION['oclck_order_deadline']   = $order_deadline;
            $_SESSION['oclck_client_name']        = $name;
            $_SESSION['oclck_client_address']   = $address;
            $_SESSION['oclck_client_number']    = $whats;
            $_SESSION['oclck_client_email']     = $email;
            $_SESSION['oclck_order_number']        = $order_number;
            $_SESSION['oclck_delivery_period']  = $delivery_period;

            if ($use_coupon != 0) {
                $_SESSION['oclck_coupon']          = $discount;
            }

            $url = SERVER_URI . "/upsell-purchase-check/" . $order_number  . "/" . $sale_id;
        }

        require "../includes/classes/RequestAtendezap.php"; 
        // sendWebhookStatusAuto($last_id);    

        $feedback = array('status' => 1, 'msg' => ':)', 'url' => $url);
    } catch (PDOException $e) {
        $error = 'ERROR: ' . $e->getMessage();
        $feedback = array('status' => '0', 'msg' => $error);
    }

    //$feedback = array('status' => 1, 'msg' => $product_id);
    echo json_encode($feedback);
    exit;
}
