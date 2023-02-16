<?php
error_reporting(-1);              
ini_set('display_errors', 1);        
require "includes/config.php";
require "includes/classes/RequestAtendezap.php"; 

if (isset($_GET['order_number'])) {
    header("Location: " . SERVER_URI ); 
} 


$order_number = $_GET['order_number'];

$order_number = str_replace('AFI', '', $order_number);
$queryN = $conn->prepare("SELECT order_id FROM orders  WHERE order_number = :order_number");
$queryN->execute(['order_number' => $order_number]);
$order_id = $queryN->fetch(\PDO::FETCH_ASSOC)["order_id"]; 

updateUserOnStatus3($order_id);  

$url = SERVER_URI . '/meu-pedido/' . $order_number;   
$feedback = array('type' => 'success', 'msg' => $msg, 'title' => "Feito!", 'url' => $url );
echo json_encode($feedback); 

?>