<?php

require_once (dirname(__FILE__) . '/includes/config.php');

$get_all_orders = $conn->prepare('SELECT order_id, user__id, order_date, order_commission_date FROM orders ORDER BY user__id');
$get_all_orders->execute();

while($date_correction = $get_all_orders->fetch()){
    
    $user__id = $date_correction['user__id'];
    $wrong_date = $date_correction['order_commission_date'];
    $order_date = $date_correction['order_date'];
    $order_id = $date_correction['order_id'];

    $get_pay_term = $conn->prepare('SELECT user_payment_term FROM users WHERE user__id = :user__id');
    $get_pay_term->execute(array('user__id' => $user__id));
    $pay_term = $get_pay_term->fetch();
    $pay_term = $pay_term['user_payment_term'];

    $order_commission_timestamp             = "+" . $pay_term . "days";
    $order_commission_date = date('Y-m-d', strtotime($order_date . $order_commission_timestamp));

    $date_fix = $conn->prepare('UPDATE orders SET order_commission_date = :order_commission_date WHERE order_id = :order_id');
    $date_fix->execute(array('order_commission_date' => $order_commission_date, 'order_id' => $order_id));

    echo "O usuário <b>" . $user__id . "</b> recebe em  <b>" . $pay_term . " dias</b>, então o pedido do dia " . date('d/m', strtotime($order_date)) . " deverá ser pago dia " . date('d/m', strtotime( $order_commission_date)) . ", e não dia " . date('d/m', strtotime($wrong_date)) ."!<br>";

    #" . $order_id ."
}

?>