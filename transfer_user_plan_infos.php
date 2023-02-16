<?php

require_once (dirname(__FILE__) . '/includes/config.php');

$get_all_users = $conn->prepare('SELECT * FROM users AND active = 1');
$get_all_users->execute();

while($users_info = $get_all_users->fetch()){
    
    $user__id = $users_info['user__id'];

    # Verifica se as infos já estão na nova tabela
    $verify_transfered_infos = $conn->prepare('SELECT * FROM subscriptions WHERE user__id = :user__id');
    $verify_transfered_infos->execute(array('user__id' => $user__id));

    if ($verify_transfered_infos->rowCount() > 0) {

        echo "As infos do plano do Usuário <b>" . $user__id . "</b> já foram transferidas ateriormente!<br>";

    } else {

        # Se não estiverem, transfere
        $subscription_id = 0;
        $subscription_code = "plan" . $users_info['user_code'];
        $subscription_current_plan = $users_info['user_plan'];
        $user_plan_tax = $users_info['user_plan_tax'];
        $user_external_gateway_tax = $users_info['user_external_gateway_tax'];
        $user_plan_shipping_tax = $users_info['user_plan_shipping_tax'];
        $user_payment_term = $users_info['user_payment_term'];
        $custom_conditions = 1;
        $subscription_start = $users_info['user_plan'];

        $transfer_infos = $conn->prepare('INSERT INTO subscriptions (subscription_id, user__id, subscription_code, subscription_current_plan, user_plan_tax, user_external_gateway_tax, user_plan_shipping_tax, user_payment_term, custom_conditions, subscription_start) VALUES (:subscription_id, :user__id, :subscription_code, :subscription_current_plan, :user_plan_tax, :user_external_gateway_tax, :user_plan_shipping_tax, :user_payment_term, :custom_conditions, :subscription_start)');

        $transfer_infos->execute(array('subscription_id' => $subscription_id, 'user__id' => $user__id, 'subscription_code' => $subscription_code, 'subscription_current_plan' => $subscription_current_plan, 'user_plan_tax' => $user_plan_tax, 'user_external_gateway_tax' => $user_external_gateway_tax, 'user_plan_shipping_tax' => $user_plan_shipping_tax, 'user_payment_term' => $user_payment_term, 'custom_conditions' => $custom_conditions, 'subscription_start' => $subscription_start));
        

        echo "As infos do plano do Usuário <b>" . $user__id . "</b> foram transferidas!<br>";
    }
}



?>