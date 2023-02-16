<?php

require_once (dirname(__FILE__) . '/includes/config.php');

$get_all_orders = $conn->prepare('SELECT * FROM orders INNER JOIN products ON orders.product_id = products.product_id ORDER BY order_number');
$get_all_orders->execute();

while($commissions_recalc = $get_all_orders->fetch()){
    
    $order_number = $commissions_recalc['order_number'];
    $order_commission_date = $commissions_recalc['order_commission_date'];
    $order_date = $commissions_recalc['order_date'];
    $order_id = $commissions_recalc['order_id'];
    $user__id = $commissions_recalc['user__id'];
    $sale_id = $commissions_recalc['sale_id'];
    $product_id = $commissions_recalc['product_id'];
    $order_final_price = $commissions_recalc['order_final_price'];
    $order_old_liquid_value = $commissions_recalc['order_liquid_value'];

    if (preg_match("/AFI/", $order_number)) {
        echo "O pedido nº <b>" . $order_number . "</b> foi ignorado!<br>";
        continue;
    } else {
        $get_linked_order = $conn->prepare('SELECT * FROM orders WHERE order_number = :order_number');
        $get_linked_order->execute(array('order_number' => "AFI" . $order_number));

        if ($get_linked_order->rowCount() != 0){
            echo "O pedido nº <b>" . $order_number . "</b> TAMBÉM foi ignorado!<br>";
            continue;
        }
    }

    $get_tax_values = $conn->prepare('SELECT user_plan_tax, user_plan_shipping_tax FROM users WHERE user__id = :user__id');
    $get_tax_values->execute(array('user__id' => $user__id));
    $tax_values = $get_tax_values->fetch();
    $plan_tax = $tax_values['user_plan_tax'];
    $plan_shipping_tax = $tax_values['user_plan_shipping_tax'];
    
    $tax = round(($order_final_price * $plan_tax), 2);
    $total_tax = round(($plan_shipping_tax + $tax), 2);
    $liquid_value = round(($order_final_price - $total_tax), 2);

    $order_meta_prod_comm       = $conn->prepare('INSERT INTO orders_meta (meta_id, order_number, meta_key, meta_value) VALUES (:meta_id, :order_number, :meta_key, :meta_value)');
    $order_meta_prod_tax        = $conn->prepare('INSERT INTO orders_meta (meta_id, order_number, meta_key, meta_value) VALUES (:meta_id, :order_number, :meta_key, :meta_value)');
    $order_meta_prod_tax_base   = $conn->prepare('INSERT INTO orders_meta (meta_id, order_number, meta_key, meta_value) VALUES (:meta_id, :order_number, :meta_key, :meta_value)');
    $order_meta_ship_tax        = $conn->prepare('INSERT INTO orders_meta (meta_id, order_number, meta_key, meta_value) VALUES (:meta_id, :order_number, :meta_key, :meta_value)');
    $liquid_value_update        = $conn->prepare('UPDATE orders SET order_liquid_value = :order_liquid_value WHERE order_id = :order_id');

    $order_meta_prod_comm->execute(array('meta_id' => 0, 'order_number' => $order_number, 'meta_key' => "producer_commission", 'meta_value' => $liquid_value));
    $order_meta_prod_tax->execute(array('meta_id' => 0, 'order_number' => $order_number, 'meta_key' => "producer_tax", 'meta_value' => $tax));
    $order_meta_prod_tax_base->execute(array('meta_id' => 0, 'order_number' => $order_number, 'meta_key' => "producer_tax_base", 'meta_value' => $plan_tax));
    $order_meta_ship_tax->execute(array('meta_id' => 0, 'order_number' => $order_number, 'meta_key' => "ship_tax", 'meta_value' => $plan_shipping_tax));
    $liquid_value_update->execute(array('order_liquid_value' => $liquid_value, 'order_id' => $order_id));


    echo "O pedido <b>" . $order_number . "</b> foi de <b>R$ " . number_format($order_final_price, 2, ",", ".") . "</b>, então a taxa de <b>" . number_format($tax, 2, ",", ".") . " (" . $plan_tax * 100 . ")%</b> e a entrega <b>" . number_format($plan_shipping_tax, 2, ",", ".") . "</b> do usuário <b>" . $user__id . "</b> serão de <b>" . number_format($total_tax, 2, ", ", " . ") . "</b> e o valor líquido será de <b>" . number_format($liquid_value, 2, ", ", " . ") . "</b>, e não de " . number_format($order_old_liquid_value, 2, ",", ".")  . "!<br>";

}


?>