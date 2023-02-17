<?php

$data = file_get_contents('php://input');
$ser_data = serialize($data);
$fp = fopen('webhook.txt', 'a+');
fwrite($fp, date('Y-m-d H:i:s') . ' - ' .  $ser_data . "\n\n");
fclose($fp);

include_once(__DIR__ . '/classes/braip/braip.php');
include_once(__DIR__ . '/functions.php');

$json = json_decode($data);

$order_id = null;
$codigoRastreamento = null;

foreach($json->retorno->pedidos as $pedido){
    
    $pedido = $pedido->pedido;
    
    $order_id = (int) @$pedido->observacaointerna;
    $codigoRastreamento = @$pedido->codigosRastreamento->codigoRastreamento;

}

if(!$order_id){
    $message = 'Pedido não encontrado!'; 
    throw new ErrorException($message, 400);
}

$dadosOrder = getExternalId($order_id);

$braip = new Braip();

if($codigoRastreamento && $dadosOrder->transportadora == 'BRAIP'){
    
    // $transaction -> Codigo do pedido na Braip
    $retorno = $braip->setCodigoRastreio($dadosOrder->external_id, $codigoRastreamento);
    
    echo '<pre>';
    print_r($retorno);
    echo '</pre>';
    
    if($retorno->success){
        echo $codigoRastreamento . PHP_EOL;
        echo '<br>';
        echo 'Codigo de rastreio inserido na BRAIP!';
    }else{
        // echo '<pre>';
        // print_r($retorno->fields);
        // echo '</pre>';
    }
    
}


