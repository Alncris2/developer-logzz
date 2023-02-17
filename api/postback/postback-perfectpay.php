<?php

$request = file_get_contents('webhook-perfectpay.json');
$req_dump = print_r( $request, true );

$fp = file_put_contents( 'pre-request.log', $req_dump );

require dirname(__FILE__) . "/../../includes/config.php";

$request_data = json_decode ($req_dump);

# Verifica se o Status da Solicitação é de VENDA_COMPLETA.
# Se não for VENDA_COMPLETA, a requisição será ignorada.
if ($request_data->type == "VENDA_COMPLETA") {
    
    # Verifica pelo URL a qual integraçãopertence a requisição.
    $url = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

    $get_integration = $conn->prepare('SELECT integration_user_id, integration_product_id FROM integrations WHERE integration_url = :integration_url AND integration_status = :integration_status');
    $get_integration->execute(array('integration_url' => $url, 'integration_status' => "active"));

    $integration = $get_integration->fetch();

    $integration_user_id = $integration['integration_user_id'];
    $integration_product_id = $integration['integration_product_id'];
    $product_name = $request_data->product_name;

    if ($integration_user_id == 0) {
      # Posteriormente, criar um mecanismo de log de requisção com URL inválida
      # ou requisição para integração inativa.
      exit;
    }


    # Verifica se a chave recebida na requisição é de fato a Chave Única da conta PerfectPay do assinante.
    $basic_authentication = $request_data->basic_authentication;

    $verify_auth = $conn->prepare('SELECT COUNT(*) FROM integrations WHERE integration_user_id = :integration_user_id AND integration_keys = :integration_keys');

    $verify_auth->execute(array('integration_user_id' => $integration_user_id, 'integration_keys' => $basic_authentication));

    $verified_auth = $verify_auth->fetch();

    if ($verified_auth == 0) {
      # Posteriormente, criar um mecanismo de log de requisção com Chave Única inválida
      exit;
    }

    # Pega o ID da Oferta
    $sale_mirror_key = $request_data->plan_key;

    $get_sale_id = $conn->prepare('SELECT sale_id FROM sales WHERE product_id = :integration_product_id AND sale_mirror_key = :sale_mirror_key');
    $get_sale_id->execute(array('integration_product_id' => $integration_product_id, 'sale_mirror_key' => $sale_mirror_key));

    if ($get_sale_id->rowCount() != 0) {

      $sale_id = $get_sale_id->fetch();
      $sale_id = $sale_id['sale_id'];

      echo "Oferta já espelhada! <br>";
      
    } else {

      $stmt = $conn->prepare('INSERT INTO sales (sale_id, product_id, sale_product_name, sale_name, sale_date_start, sale_date_end, sale_quantity, sale_price, sale_status, sale_url, sale_tax, product_shipping_tax, product_price, sale_cost, sale_mirror_key) VALUES (:sale_id, :product_id, :sale_product_name, :sale_name, :sale_date_start, :sale_date_end, :sale_quantity, :sale_price, :sale_status, :sale_url, :sale_tax, :product_shipping_tax, :product_price, :sale_cost, :sale_mirror_key)');
      
      $sale_id = 0;
      $product_id = $integration_product_id;
      $sale_name = $request_data->plan_name;
      $sale_date_start = date('Y-m-d H:m:s');
      $sale_date_end = date('Y-m-d H:m:s');
      $sale_quantity = $request_data->plan_amount;
      $sale_price = $request_data->trans_value / 100;
      $sale_status = 1;
      $sale_url = #;
      $sale_tax = 0;

      session_name(SESSION_NAME);
      session_start();
      $product_shipping_tax = number_format($_SESSION['UserPlanShipTax'], 2, '.', ',');;
      $product_price = ($request_data->trans_value / 100) / $request_data->plan_amount;
      $sale_cost = $request_data->trans_value / 100;
      $sale_mirror_key = $request_data->plan_key;


      try {
      $stmt->execute(array('sale_id' => $sale_id, 'product_id' => $product_id, 'sale_product_name' => $product_name, 'sale_name' => $sale_name, 'sale_date_start' => $sale_date_start, 'sale_date_end' => $sale_date_end, 'sale_quantity' => $sale_quantity, 'sale_price' => $sale_price, 'sale_status' => $sale_status, 'sale_url' => $sale_url, 'sale_tax' => $sale_tax, 'product_shipping_tax' => $product_shipping_tax, 'product_price' => $product_price, 'sale_cost' => $sale_cost, 'sale_mirror_key' => $sale_mirror_key));

      echo "Oferta espelhada! <br>";
    
          } catch(PDOException $e) {
            $error= 'ERROR: ' . $e->getMessage();

          }
      
    $get_sale_id = $conn->prepare('SELECT sale_id FROM sales WHERE product_id = :integration_product_id AND sale_mirror_key = :sale_mirror_key');
    $get_sale_id->execute(array('integration_product_id' => $integration_product_id, 'sale_mirror_key' => $sale_mirror_key));

    if ($get_sale_id->rowCount() != 0) {
      $sale_id = $get_sale_id->fetch();
      $sale_id = $sale_id['sale_id'];
    }

    }

    # Espelha o pedido.
    $order_number = "PERFP" . strtoupper($request_data->trans_key);

    $check_order_duplicate = $conn->prepare('SELECT * FROM orders WHERE order_number = :order_number');
    $check_order_duplicate->execute(array('order_number' => $order_number));

    if ($check_order_duplicate->rowCount() != 0) {
      $sale_id = $check_order_duplicate->fetch();
      $sale_id = $sale_id['sale_id'];
      echo "Pedido já espelhado.";
      exit;
    }

    $stmt = $conn->prepare('INSERT INTO orders(order_id, user__id, sale_id, product_name, order_date, order_deadline, order_status, order_delivery_date, client_name, client_address, client_number, order_delivery_time, order_number, delivery_period, use_coupon, order_final_price, order_commission, order_commission_date) VALUES (:order_id, :user__id, :sale_id, :product_name, :order_date, :order_deadline, :order_status, :order_delivery_date, :client_name, :client_address, :client_number, :order_delivery_time, :order_number, :delivery_period, :use_coupon, :order_final_price, :order_commission, :order_commission_date)');

    $order_id               = 0;
    $user__id               = $integration_user_id;
    $order_delivery_date    = "2100-01-01 00:00:01";
    $order_date             = $request_data->trans_createdate;
    $order_deadline         = "2100-01-01 00:00:01";
    $order_status           = 0;
    $order_delivery_date    = "2100-01-01 00:00:01";
    $name                   = '[P] ' . $request_data->client_name;

    $address  = $request_data->client_address . ", nº " . $request_data->client_address_number ."<br>";
    $address .= "Bairro " . $request_data->client_address_district . "<br>";
    if ($request_data->client_address_comp != null) { "Complemento: " . $address .= $request_data->client_address_comp . "<br>";}
    $address .= $request_data->client_address_city . ", " . $request_data->client_address_state . "<br>";
    $address .= "CEP: " . $request_data->client_zip_code; 

    $whats                  = $request_data->client_cel;

    $delivery_period        = "default";
    $use_coupon             = 1;
    $order_final_price      = $request_data->trans_total_value / 100;
    $order_commission       = 0;
    $order_commission_date  = "2100-01-01 00:00:01";

    try {
      $stmt->execute(array('order_id' => $order_id, 'user__id' => $user__id, 'sale_id' => $sale_id, 'product_name' => $product_name, 'order_date' => $order_date, 'order_deadline' => $order_deadline, 'order_status' => $order_status, 'order_delivery_date' => $order_delivery_date, 'client_name' => $name , 'client_address' => $address, 'client_number' => $whats, 'order_delivery_time' => $order_delivery_date, 'order_number' => $order_number, 'delivery_period' => $delivery_period, 'use_coupon' => $use_coupon, 'order_final_price' => $order_final_price, 'order_commission' => $order_commission, 'order_commission_date' => $order_commission_date));

      echo "Pedido Espelhado!";
    } 

    catch(PDOException $e) {
      $error = 'ERROR: ' . $e->getMessage();
    }


   
} else {
    
    
    
}

//header('Location: https://webhook.site/c21c1b9e-5b06-4c2d-b16c-af588e87e50e');
//header('Location: https://webhook.site/450f3057-db1a-4fa5-89b6-2c24a0134531');
?>

