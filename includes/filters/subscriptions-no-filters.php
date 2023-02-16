<?php
    
    $graphs_context = 1;

    # Cobranças de Assinaturas (Faturamento)
    $get_total_signs = $conn->prepare('SELECT COUNT(billing_value) AS total FROM billings WHERE (billing_released BETWEEN :date_init AND :date_end) AND (billing_type = "RECURRENCE" OR billing_type = "PLAN_UPGRADE") ORDER BY billing_id');
    $get_total_signs->execute(array('date_init' => $date_init, 'date_end' => $date_end));
    
    if ($get_total_signs->rowCount() > 0){
        $get_total_signs = $get_total_signs->fetch();
        $subscriptions_count = $get_total_signs['total'];
    } else {
        $subscriptions_count = 0;
    }

    # Cobranças de Assinaturas (Quantidade)
    $get_total_signs = $conn->prepare('SELECT SUM(billing_value) AS total FROM billings WHERE (billing_released BETWEEN :date_init AND :date_end) AND (billing_type = "RECURRENCE" OR billing_type = "PLAN_UPGRADE") ORDER BY billing_id');
    $get_total_signs->execute(array('date_init' => $date_init, 'date_end' => $date_end));
    
    if ($get_total_signs->rowCount() > 0){
        $get_total_signs = $get_total_signs->fetch();
        $subscriptions_sum = "R$ " .  number_format($get_total_signs['total'], 2, ',', '.');
    } else {
        $subscriptions_sum = "R$ 0";
    }

    # Assinaturas Canceladas
	$get_total_signs = $conn->prepare('SELECT COUNT(subscription_id) FROM subscriptions WHERE subscription_pay_status = 0 AND subscription_renewal BETWEEN :date_init AND :date_end');
	$get_total_signs->execute(array('date_init' => $date_init, 'date_end' => $date_end));
    
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