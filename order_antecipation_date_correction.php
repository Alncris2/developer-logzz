<?php

require_once (dirname(__FILE__) . '/includes/config.php');

$get_all_orders = $conn->prepare('SELECT order_id, user__id, order_date, order_commission_date FROM orders WHERE order_status = 3 ORDER BY user__id');
$get_all_orders->execute();

echo $get_all_orders->rowCount();

while($date_correction = $get_all_orders->fetch()){
    
    $user__id = $date_correction['user__id'];
    $wrong_date = $date_correction['order_commission_date'];
    $order_date = $date_correction['order_date'];
    $order_id = $date_correction['order_id'];

    $order_commission_timestamp             = "+2days";
    $order_anticipation_date = date('Y-m-d', strtotime($order_date . $order_commission_timestamp));

    $date_fix = $conn->prepare('UPDATE orders SET order_anticipation_date = :order_anticipation_date WHERE order_id = :order_id');
    $date_fix->execute(array('order_anticipation_date' => $order_anticipation_date, 'order_id' => $order_id));

    echo "O usuário <b>" . $user__id . "</b> recebe em  <b> 2 dias</b>, então o pedido do dia " . date('d/m', strtotime($order_date)) . " deverá ser pago dia " . date('d/m', strtotime( $order_anticipation_date)) . ", e não dia " . date('d/m', strtotime($wrong_date)) ."!<br>";


}

?>