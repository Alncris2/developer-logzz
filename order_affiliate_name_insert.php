<?php

require_once (dirname(__FILE__) . '/includes/config.php');

$get_all_orders = $conn->prepare('SELECT * FROM orders WHERE order_number LIKE "AFI%"');
$get_all_orders->execute();

while($date_correction = $get_all_orders->fetch()){

    $order_number = $date_correction['order_number'];
    $user__id = $date_correction['user__id'];

    $new_order_number = explode("AFI", $order_number);
    $new_order_number = $new_order_number[1];

    $get_original_order = $conn->prepare('SELECT order_id FROM orders WHERE order_number = :order_number');
    $get_original_order->execute(array('order_number' => $new_order_number));

    $original_order = $get_original_order->fetch();
    @$original_order = $original_order['order_id'];


    $get_affiliate_name = $conn->prepare('SELECT full_name FROM users WHERE user__id = :user__id');
    $get_affiliate_name->execute(array('user__id' => $user__id));

    $affiliate_name = $get_affiliate_name->fetch();
    $affiliate_name = $affiliate_name['full_name'];


    $date_fix = $conn->prepare('UPDATE orders SET affiliate_name = :affiliate_name WHERE order_id = :order_id');
    @$date_fix->execute(array('affiliate_name' => $affiliate_name, 'order_id' => $original_order));

    echo "O afiliado do Pedido <b>" . $new_order_number . "</b> (" . $order_number .") é <b>". $affiliate_name . "</b>!, cujo ID é <b>" . $user__id . "</b>!<br>";

    #" . $order_id ."
}



?>