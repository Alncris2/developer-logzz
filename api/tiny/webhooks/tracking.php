<?php

require dirname(__FILE__) . "/../../../includes/config.php";

# SALVA OS DADOS EM UM ARQUIVO DE LOG
$fp = file_put_contents('pre-request-traking.log', file_get_contents('php://input'));

/**
 * DADOS RECEBIDOS VIA WEBHOOK DE ACORDO COM A DOCUMENTAÇÃO DA TINY
 * LINK DA DOC : https://tiny.com.br/api-docs/api2-webhooks-envio-codigo-rastreio
 */

$data = json_decode(file_get_contents('php://input'), true);


$id_pedido_tiny = $data['dados']['id']; //ID DO PEDINO NA TINY
$id_sale  = (string) $data['dados']['idPedidoEcommerce'];
$tracking = (string) $data['dados']['codigoRastreio'];
$shipping = (string) $data['dados']['transportadora'];


try {
  # INFORMAR CODIGO DE RASTREIO DO PEDIDO 
  $query_to_update_tracking = "UPDATE orders SET order_tracking = :order_tracking, order_shipping = :order_shipping, order_tiny_id = :order_tiny_id WHERE order_id = :order_id";
  $stmt = $conn->prepare($query_to_update_tracking);

  $stmt->execute(['order_tracking' => $tracking, 'order_shipping' => $shipping, 'order_id' => $id_sale, 'order_tiny_id' => $id_pedido_tiny]);

  # ATUALIZAR STATUS DO PEDIDO PARA ENVIADO
  $query_to_update_status = "UPDATE orders SET order_status = :order_status WHERE order_id = :order_id";
  $stmt = $conn->prepare($query_to_update_status);

  $stmt->execute(['order_status' => 8, 'order_id' => $id_sale]);


  # VERIFICAR QUAL PLATAFORMA ESTÁ VINDO O PEDIDO.
  $query_to_verify_platform = "SELECT platform FROM orders WHERE order_id = :order_id";
  $stmt = $conn->prepare($query_to_verify_platform);
  $stmt->execute([
    'order_id' => $id_sale
  ]);

  $platform = $stmt->fetch()['platform'];

  /**
   * DEVOLUTIVA DO CODIGO DE RASTREIO PARA PLATAFORMA DE ORIGEM 
  */

  # ENVIAR CODIGO DE RASTREIO PARA A BRAIP 
  if ($platform == 'Braip') {
    // CODIGOS DE TRANSPORTADORAS CADASTRADAS NA BRAIP 
    $carriers_code = [
      'correios' => 'traenopx',
      'jadlog' => 'tramn5g2',
      'melhor_envio' => 'traongwx',
      'total_express' => 'tra12zpx'
    ];

    /**
     * TOKEN DA CONTA QUE PODE SER GERADO EM https://ev.braip.com/api
     */
    $query_to_get_token = "SELECT integration_api_token FROM integrations i INNER JOIN orders o WHERE o.order_id = :order_id AND o.user__id = i.integration_user_id";
    $stmt = $conn->prepare($query_to_get_token);
    $stmt->execute([
      'order_id' => $id_sale
    ]);
    $token = $stmt->fetch()['integration_api_token'];

    // PEGAR CHAVE DA TRANSAÇÃO DA COMPRA
    $query_to_get_transaction_key =  "SELECT order_trans_key FROM orders WHERE order_id = :order_id";
    $stmt = $conn->prepare($query_to_get_transaction_key);
    $stmt->execute(['order_id' => $id_sale]);
    $transaction = $stmt->fetch();

    $url_to_set_code_tracking = "https://ev.braip.com/api/v1/transporte";
    $payload_send = [
      'transaction_key' => $transaction['order_trans_key'],
      'shipping_company_key' => 'traenopx',
      'tracking_code' => $tracking
    ];

    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => $url_to_set_code_tracking,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CUSTOMREQUEST => 'PUT',
      CURLOPT_POSTFIELDS => http_build_query($payload_send),
      CURLOPT_HTTPHEADER => array(
        'user-agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, como Gecko ) Chrome/78.0.3904.97 Safari/537.36',
        'accept: application/json',
        "Authorization: Bearer $token",
        'Content-Type: application/x-www-form-urlencoded',
      ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    file_put_contents('error-traking-api.log', $response);
    return;
  }

  
  # ENVIAR CODIGO DE RASTREIO PARA A MONETIZZE
  if ($platform == 'Monetizze') {
    // PEGAR CHAVE DA TRANSAÇÃO DA COMPRA
    $query_to_get_transaction_key =  "SELECT order_trans_key FROM orders WHERE order_id = :order_id";
    $stmt = $conn->prepare($query_to_get_transaction_key);
    $stmt->execute(['order_id' => $id_sale]);
    $transaction = $stmt->fetch();


    /**
     * Pegar X_CONSUMER_KEY da sua conta monetizze 
     * Encontrado em https://app.monetizze.com.br/ferramentas/api
    */
    $query_to_get_token = "SELECT integration_api_token FROM integrations i INNER JOIN orders o WHERE o.order_id = :order_id AND o.id_integration = i.integration_id";
    $stmt = $conn->prepare($query_to_get_token);
    $stmt->execute(['order_id' => $id_sale]);
    $token = $stmt->fetch()['integration_api_token'];


    $url = "https://api.monetizze.com.br/2.1/token";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["X_CONSUMER_KEY: $token"]);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_URL, $url); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    
    
    $res = curl_exec($ch);
    curl_close($ch);
    $dados = json_decode($res, true);

    $token_api_monetizze = $dados['token'];

    

    $data_string = ['data' => json_encode([['codLog' => 1, 'transaction' => '39257281', 'trackingCode' => $tracking]])];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.monetizze.com.br/2.1/sales/tracking");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["TOKEN:".$token_api_monetizze]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

    $output = curl_exec($ch);
    curl_close($ch);


    
    $fp = file_put_contents('debug.log', $data_string);
    // echo $output;


    return;
  }

  /**
   * TOKEN DA CONTA QUE PODE SER GERADO EM https://ev.braip.com/api
   */
} catch (\Exception $e) {
  $fp = file_put_contents('error-traking.log', $e);
  exit;
}
