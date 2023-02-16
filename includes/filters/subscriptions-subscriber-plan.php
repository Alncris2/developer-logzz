<?php

    # Get USER ID
    // $get_user__id = $conn->prepare('SELECT user__id FROM users WHERE user_code = :user_code');
    // $get_user__id->execute(array('user_code' => $filter_by_subscriber));
    // $get_user__id = $get_user__id->fetch();
    //$user__id = $get_user__id[0];
    $user__id = $filter_by_subscriber;
    $user_plan = $filter_by_plan;
    $graphs_context = 4;

    # Cobranças de Assinaturas (Faturamento)
    $get_total_signs = $conn->prepare('SELECT COUNT(billing_value) AS total FROM billings AS b INNER JOIN subscriptions AS s ON b.user__id = s.user__id WHERE (billing_released BETWEEN :date_init AND :date_end AND s.user__id = :user__id) AND (billing_type = "RECURRENCE" OR billing_type = "PLAN_UPGRADE") AND user_plan = :user_plan ORDER BY billing_id');
    $get_total_signs->execute(array('user_plan' => $user_plan, 'user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end));
    
    if ($get_total_signs->rowCount() > 0){
        $get_total_signs = $get_total_signs->fetch();
        $subscriptions_count = $get_total_signs['total'];
    } else {
        $subscriptions_count = 0;
    }

    # Cobranças de Assinaturas (Quantidade)
    $get_total_signs = $conn->prepare('SELECT SUM(billing_value) AS total FROM billings AS b INNER JOIN subscriptions AS s ON b.user__id = s.user__id WHERE (billing_released BETWEEN :date_init AND :date_end AND s.user__id = :user__id) AND (billing_type = "RECURRENCE" OR billing_type = "PLAN_UPGRADE") AND user_plan = :user_plan ORDER BY billing_id');
    $get_total_signs->execute(array('user_plan' => $user_plan, 'user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end));
    
    if ($get_total_signs->rowCount() > 0){
        $get_total_signs = $get_total_signs->fetch();
        $subscriptions_sum = "R$ " .  number_format($get_total_signs['total'], 2, ',', '.');
    } else {
        $subscriptions_sum = "R$ 0";
    }

    # Assinaturas Canceladas
	$get_total_signs = $conn->prepare('SELECT COUNT(subscription_id) FROM subscriptions WHERE (subscription_pay_status = 0 AND user__id = :user__id) AND (subscription_renewal BETWEEN :date_init AND :date_end AND user_plan = :user_plan)');
	$get_total_signs->execute(array('user_plan' => $user_plan, 'user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end));
    
    if ($get_total_signs->rowCount() > 0){
        $get_total_signs = $get_total_signs->fetch();

        if ($subscriptions_count > 0){
            $reembolsos = round(($get_total_signs[0] * 100) / ($subscriptions_count + $get_total_signs[0]));
            $reembolsos = number_format($reembolsos, 0, ',', '.');
        } else if ($get_total_signs[0] > $subscriptions_count) {
            $reembolsos = 100;
        } else {
            $reembolsos = 0;
        }

    } else {
        $reembolsos = 0;
    }

    $subscriptions_count = $subscriptions_count + $get_total_signs[0];