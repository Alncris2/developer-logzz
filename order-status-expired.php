<?php 
error_reporting(-1);              
ini_set('display_errors', 1);  
require_once (dirname(__FILE__) . '/includes/config.php'); 

$_date = date('Y-m-d', strtotime('+2 days'));   


echo "Buscando pedidos não confirmados até um dia antes da data de entrega definida.... <br>"; 
$get_orders_expired = $conn->prepare('SELECT order_id, order_deadline, order_status, order_number FROM orders WHERE order_deadline < STR_TO_DATE(:_date, "%Y-%m-%d")  AND order_status = 0 AND order_number NOT LIKE "AFI%" ');
$get_orders_expired->execute(array('_date' => $_date));      
 
while($orders_expired = $get_orders_expired->fetch()){   

    $order_number = $orders_expired['order_number'];
    $statement = $conn->prepare("UPDATE orders SET order_status = 11, order_status_description = 'Cliente não confimou o pedido' WHERE  order_number = :order_number");
    $statement->execute(array("order_number" => $order_number));

    $statement = $conn->prepare("UPDATE orders SET order_status = 11, order_status_description = 'Cliente não confimou o pedido' WHERE  order_number = :order_number");
    $statement->execute(array("order_number" => 'AFI' . $order_number));

    $stmt_insert_historic = $conn->prepare('INSERT INTO order_details ( order_number, order_status ) VALUES ( :order_number, :order_status )');
    $stmt_insert_historic->execute(array('order_number' => $order_number, 'order_status' => 11));

 
    echo "ORDER: <b>" . $orders_expired['order_number'] ." (". $orders_expired['order_deadline'] .")</b> foi alterado para <b>EXPIRADO</b> <br>";
}

?>   