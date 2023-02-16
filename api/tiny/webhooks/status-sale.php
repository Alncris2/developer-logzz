<?php

require dirname(__FILE__) . "/../../../includes/config.php";

# SALVA OS DADOS EM UM ARQUIVO DE LOG
$fp = file_put_contents( 'pre-request-sale.log', file_get_contents('php://input'));
$data = json_decode(file_get_contents('php://input'), true);

$id_sale =  $data['dados']['idPedidoEcommerce']; // ID DO PEDIDO
$id_pedido_tiny = $data['dados']['id']; //ID DO PEDINO NA TINY

# PEGAR TOKEN CONTA TINY 
$get_token_tiny = "SELECT t.token FROM tiny_dispatches t INNER JOIN orders o ON o.order_id = :order_id INNER JOIN integrations i ON integration_url = t.url_integration AND o.user__id = i.integration_user_id";
$stmt = $conn->prepare($get_token_tiny);
$stmt->execute([
  'order_id' => $id_sale
]);
$token_tiny =  $stmt->fetch()['token'];

# VERIFICAR SE EXISTE UM CODIGO DE RASTREIO NO PEDIDO QUANDO O STATUS DO PRODUTO MUDA

$url_to_get_info_product = "https://api.tiny.com.br/api2/pedido.obter.php";

// INICIO DA REQUISIÇÃO PARA ENVIAR O PRODUTO PARA TINY
$params = array(
  "token" => $token_tiny,
  "id" => $id_pedido_tiny,
  "formato" => "json"
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url_to_get_info_product);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
  "Content-Type: application/x-www-form-urlencoded",
));

$result = curl_exec($ch);
curl_close($ch);

$data_return = json_decode($result, true);
$cod_tracking = $data_return['retorno']['pedido']['codigo_rastreamento'];
$value_freight = $data_return['retorno']['pedido']['valor_frete'];

file_put_contents('tipe_freigth.log', $data_return['retorno']['pedido']['frete_por_conta']);

// retorno.pedido.frete_por_conta
if($cod_tracking !== null ){

  # INFORMAR CODIGO DE RASTREIO DO PEDIDO 
  $query_to_update_tracking = "UPDATE orders SET order_tracking = :order_tracking, order_shipping = :order_shipping, order_tiny_id = :order_tiny_id, order_tracking_value = :order_tracking_value WHERE order_id = :order_id";
  $stmt = $conn->prepare($query_to_update_tracking);

  $stmt->execute(['order_tracking' => $cod_tracking, 'order_shipping' => 'Correios' ,'order_id' => $id_sale, 'order_tiny_id' => $id_pedido_tiny, 'order_tracking_value' => $value_freight]);

  # ATUALIZAR STATUS DO PEDIDO PARA ENVIADO
  $query_to_update_status = "UPDATE orders SET order_status = :order_status WHERE order_id = :order_id";
  $stmt = $conn->prepare($query_to_update_status);

  $stmt->execute(['order_status' => 8,'order_id' => $id_sale]);

  # ENVIAR CODIGO DE RASTREIO E TRANSPORTADORA PARA PLATAFORMA DE ORIGEM

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
  $token =  $stmt->fetch()['integration_api_token'];

  // PEGAR CHAVE DA TRANSAÇÃO DA COMPRA
  $query_to_get_transaction_key =  "SELECT order_trans_key FROM orders WHERE order_id = :order_id";
  $stmt = $conn->prepare($query_to_get_transaction_key);
  $stmt->execute(['order_id' => $id_sale]); 
  $transaction = $stmt->fetch();

  $url_to_set_code_tracking = "https://ev.braip.com/api/v1/transporte";
  $payload_send = [
    'transaction_key' => $transaction['order_trans_key'],
    'shipping_company_key' => 'traenopx',
    'tracking_code' => $cod_tracking
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