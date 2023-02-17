<?php

require_once (dirname(__FILE__) . '/includes/config.php');

#$_date = date('Y-m-d');
$_date = '2022-01-05';


# Busca comissões não liberadas até a data definida
$get_commissions = $conn->prepare('SELECT user__id, order_datetime, SUM(order_liquid_value) AS total_value, order_commission_date FROM orders WHERE (order_commission_date < :_date AND order_commission_released = 0) AND order_status = 3 GROUP BY user__id');
$get_commissions->execute(array('_date' => $_date));

while($commission = $get_commissions->fetch()){

    $user__id = $commission['user__id'];
    $billing_value = round($commission['total_value'], 2);

    # Libera os valores
    $get_current_commission_value = $conn->prepare('SELECT meta_value, meta_id FROM transactions_meta WHERE user__id = :user__id AND meta_key = "commission_balance"');
    $get_current_commission_value->execute(array('user__id' => $user__id));

    if ($get_current_commission_value->rowCount() == 0) {

        $set_new_commission_value = $conn->prepare('INSERT INTO transactions_meta (meta_id, user__id, meta_key, meta_value) VALUES (0, :user__id, :meta_key, :meta_value)');
        $set_new_commission_value->execute(array('user__id' => $user__id, 'meta_key' => "commission_balance",'meta_value' => $billing_value));

    } else {

        $commission = $get_current_commission_value->fetch();
        $commission_id = $commission['meta_id'];
        $old_commission_value = round($commission['meta_value'], 2);

        $new_commission_value = round(($old_commission_value + $billing_value), 2);

        $get_new_commission_value = $conn->prepare('UPDATE transactions_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
        $get_new_commission_value->execute(array('meta_value' => $new_commission_value, 'meta_id' => $commission_id));

    }

    # Marca as comissões como "antecipaçao liberada";
    $get_commissions_id = $conn->prepare('SELECT order_id FROM orders WHERE (order_commission_date < :_date AND order_commission_released = 0) AND (order_status = 3 AND user__id = :user__id)');
    $get_commissions_id->execute(array('_date' => $_date, 'user__id' => $user__id));

    while ($commission_id = $get_commissions_id->fetch()) {
        $order_id = $commission_id['order_id'];

        $set_commission_released = $conn->prepare('UPDATE orders SET order_commission_released = 1 WHERE order_id = :order_id');
        $set_commission_released->execute(array('order_id' => $order_id));

    }

    echo "Criado saldo disponível de <b>" . $billing_value ."</b> para o usuário <b>". $user__id . "</b><br>";

}


echo "<br><br> ------------------------------------------------------------------ <br><br>";


# Busca comissões não liberadas p/ antecipação até a data definida
$get_commissions = $conn->prepare('SELECT user__id, order_datetime, SUM(order_liquid_value) AS total_value, order_anticipation_date FROM orders WHERE (order_anticipation_date < :_date AND order_anticipation_released = 0) AND (order_status = 3 AND order_commission_released = 0) GROUP BY user__id');
$get_commissions->execute(array('_date' => $_date));

while($commission = $get_commissions->fetch()){

    $user__id = $commission['user__id'];
    $billing_value = round($commission['total_value'], 2);

    # Libera os valores
    $get_current_anticipation_value = $conn->prepare('SELECT meta_value, meta_id FROM transactions_meta WHERE user__id = :user__id AND meta_key = "anticipation_balance"');
    $get_current_anticipation_value->execute(array('user__id' => $user__id));

    if ($get_current_anticipation_value->rowCount() == 0) {

        $set_new_anticipation_value = $conn->prepare('INSERT INTO transactions_meta (meta_id, user__id, meta_key, meta_value) VALUES (0, :user__id, :meta_key, :meta_value)');
        $set_new_anticipation_value->execute(array('user__id' => $user__id, 'meta_key' => "anticipation_balance", 'meta_value' => $billing_value));

    } else {

        $anticipation = $get_current_anticipation_value->fetch();
        $anticipation_id = $anticipation['meta_id'];

        $new_anticipation_value = round(($billing_value), 2);

        $get_new_anticipation_value = $conn->prepare('UPDATE transactions_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
        $get_new_anticipation_value->execute(array('meta_value' => $new_anticipation_value, 'meta_id' => $anticipation_id));

    }

    # Marca as comissões como "antecipaçao liberada";
    $get_commissions_id = $conn->prepare('SELECT order_id FROM orders WHERE (order_anticipation_date < :_date AND order_anticipation_released = 0) AND (order_status = 3 AND user__id = :user__id)');
    $get_commissions_id->execute(array('_date' => $_date, 'user__id' => $user__id));

    while ($commission_id = $get_commissions_id->fetch()) {
        $order_id = $commission_id['order_id'];

        $set_commission_released = $conn->prepare('UPDATE orders SET order_anticipation_released = 1 WHERE order_id = :order_id');
        $set_commission_released->execute(array('order_id' => $order_id));

    }

    echo "Criada atecipation de <b>" . $billing_value ."</b> para o usuário <b>". $user__id . "</b><br>";

}



?>