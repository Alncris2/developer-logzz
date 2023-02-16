<?php
error_reporting(-1);            
ini_set('display_errors', 1);     

require "includes/classes/RequestAtendezap.php";  
require "includes/config.php";  

if($_GET['number']) {
    $order_number = $_GET['number'];
    $order_number = str_replace('AFI', '', $order_number); 
    $queryN = $conn->prepare("SELECT order_id FROM orders  WHERE order_number = :order_number");
    $queryN->execute(['order_number' => $order_number]);
    $order_id = $queryN->fetch(\PDO::FETCH_ASSOC)["order_id"]; 

    if(sendWebhookStatusAuto($order_id))  
        echo '<h1>Mensagem enviada!!</h1>';
    else
        echo '<h1>Mensagem não enviada, aconteceu algum problema</h1>';
} else {
    echo '<h1>Pedido não encontrado</h1>';
}



