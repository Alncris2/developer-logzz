<?php
error_reporting(-1);            
ini_set('display_errors', 1);   
require_once (dirname(__FILE__) . '/includes/config.php');

$get_all_orders = $conn->prepare('SELECT * FROM orders WHERE order_number LIKE "AFI%"');
$get_all_orders->execute();

while($row = $get_all_orders->fetch()){

    $order_id = $row["order_id"];

    //TD DO CLIENTE
    $address = $row["client_address"];
    $city_state = explode("<br>", $address)[3];
    $cityname = $city = explode(", ", $city_state)[0];
    
    $get_locale = $conn->prepare('SELECT * FROM operations_locales WHERE city = :city');
    $get_locale->execute(array('city' =>  $cityname)); 
    $id_locale = $get_locale->fetch(\PDO::FETCH_ASSOC);

    $get_local_operations_order = $conn->prepare('SELECT id FROM local_operations_orders WHERE order_id = :order_id');
    $get_local_operations_order->execute(array('order_id' => $order_id));

    $local_operations_order = $get_local_operations_order->fetch(); 
    

    $date_fix = $conn->prepare('UPDATE local_operations_orders SET locale_id = :locale_id, city = :city WHERE id = :id '); 
    @$date_fix->execute(array('id' => $local_operations_order['id'], 'locale_id' => $id_locale['id'], 'city' => $city));  

    echo "O Pedido é da cidade <b>" . $city . "</b> e tem o id locale  <b>". $id_locale['id'] . "</b>!<br>"; 
}
?>