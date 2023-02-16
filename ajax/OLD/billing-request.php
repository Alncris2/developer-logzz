<?php

require dirname(__FILE__) . "/../includes/config.php";

session_name(SESSION_NAME);
session_start();

$action = $_GET['action'];

$billing_value = str_replace(".", "", $_GET['value']);
$billing_value = str_replace(",", ".", $billing_value);

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}

if ($action == 'billing-request'){
    $fback = "Saque";
    $billing_type = "SAQUE";
} else if ($action == 'anticipation-request'){
    $fback = "Antecipação";
    $billing_type = "ANTECIPACAO";
}

$user__id = $_SESSION['UserID'];


if ($action == 'billing-request'){

    # Verifica se há saldo para saque
    $commission_balance = $conn->prepare('SELECT meta_value FROM transactions_meta WHERE meta_key = "commission_balance" AND user__id = :user__id');
    $commission_balance->execute(array('user__id' => $user__id));
    $commission_balance = $commission_balance->fetch();
    
    if ($commission_balance['0'] == null){
        
        # Informa se não houver valor disponível ou solicitação pendente.
        $feedback = array('title' => 'Saque Indisponível!', 'type' => 'warning', 'msg' => 'Você não possui saldo disponível para saque no momento.');
        echo json_encode($feedback);
        exit;
      
    # Verifica se o valor solicitado é menor ou igual o saldo disponível.
    } else if ($commission_balance['0'] < $billing_value){
        
        $feedback = array('title' => 'Saldo Insuficiente!', 'type' => 'warning', 'msg' => 'O valor solicitado é maior do que o seu saldo atual.');
        echo json_encode($feedback);
        exit;
      
    # Se houver valor, realiza a solicitação
    } else {

        $today = date("Y-m-d H:i:s");

        $create_billing_request = $conn->prepare('INSERT INTO billings (billing_id, user__id, billing_value, billing_type, billing_request) VALUES (:billing_id, :user__id, :billing_value, :billing_type, :billing_request)');
        $create_billing_request->execute(array('billing_id' => '0', 'user__id' => $user__id, 'billing_value' => $billing_value, 'billing_type' => $billing_type, 'billing_request' => $today));
        
        # Atualiza o valor "Em Análise"
        $get_billing_in_review = $conn->prepare('SELECT meta_value, meta_id FROM transactions_meta WHERE meta_key = "in_review_balance" AND user__id = :user__id');
        $get_billing_in_review->execute(array('user__id' => $user__id));

        if ($get_billing_in_review->rowCount() > 0){
            
            $billing_in_review = $get_billing_in_review->fetch();
            $meta_value = $billing_in_review['meta_value'] + $billing_value;
            $meta_id = $billing_in_review['meta_id'];

            $set_billing_in_review = $conn->prepare('UPDATE transactions_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
            $set_billing_in_review->execute(array('meta_value' => $meta_value, 'meta_id' => $meta_id));

        } else {
            
            $set_billing_in_review = $conn->prepare('INSERT INTO transactions_meta (meta_id, user__id, meta_key, meta_value) VALUES (:meta_id, :user__id, :meta_key, :meta_value)');
            $set_billing_in_review->execute(array('meta_id' => '0', 'user__id' => $user__id, 'meta_key' => "in_review_balance", 'meta_value' => $billing_value));

        }
        
        # Atualiza o valor disponível para saque
        $get_commission_balance = $conn->prepare('SELECT meta_value, meta_id FROM transactions_meta WHERE meta_key = "commission_balance" AND user__id = :user__id');
        $get_commission_balance->execute(array('user__id' => $user__id));

        $commission_balance = $get_commission_balance->fetch();
        $meta_value = $commission_balance['meta_value'] - $billing_value;
        $meta_id = $commission_balance['meta_id'];

        $set_commission_balance = $conn->prepare('UPDATE transactions_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
        $set_commission_balance->execute(array('meta_value' => $meta_value, 'meta_id' => $meta_id));

        $billing_value = number_format($billing_value, 2, ',', '.');
        $msg = 'Seu saque de R$ '. $billing_value . ' está sendo processado!';

        $feedback = array('title' => 'Solicitação Realizada!', 'type' => 'success', 'msg' => $msg);
        echo json_encode($feedback);
        exit;    

        }


} else if ($action == 'anticipation-request'){

    # Verifica se há saldo para antecipacao
    $commission_balance = $conn->prepare('SELECT meta_value FROM transactions_meta WHERE meta_key = "anticipation_balance" AND user__id = :user__id');
    $commission_balance->execute(array('user__id' => $user__id));
    $commission_balance = $commission_balance->fetch();
    
    if ($commission_balance['0'] == null){
        
        # Informa se não houver valor disponível ou solicitação pendente.
        $feedback = array('title' => 'Antecipação Indisponível!', 'type' => 'warning', 'msg' => 'Você não possui saldo disponível para antecipação no momento.');
        echo json_encode($feedback);
        exit;
      
    # Verifica se o valor solicitado é menor ou igual o saldo disponível.
    } else if ($commission_balance['0'] < $billing_value){
        
        $feedback = array('title' => 'Saldo Insuficiente!', 'type' => 'warning', 'msg' => 'O valor solicitado é maior do que o seu saldo saldo disponível para antecipação.');
        echo json_encode($feedback);
        exit;
      
    # Se houver valor, realiza a solicitação
    } else {

        $today = date("Y-m-d H:i:s");

        $create_billing_request = $conn->prepare('INSERT INTO billings (billing_id, user__id, billing_value, billing_type, billing_request) VALUES (:billing_id, :user__id, :billing_value, :billing_type, :billing_request)');
        $create_billing_request->execute(array('billing_id' => '0', 'user__id' => $user__id, 'billing_value' => $billing_value, 'billing_type' => $billing_type, 'billing_request' => $today));
        
        # Atualiza o valor "Em Análise"
        $get_billing_in_review = $conn->prepare('SELECT meta_value, meta_id FROM transactions_meta WHERE meta_key = "in_review_balance" AND user__id = :user__id');
        $get_billing_in_review->execute(array('user__id' => $user__id));

        if ($get_billing_in_review->rowCount() > 0){
            
            $billing_in_review = $get_billing_in_review->fetch();
            $meta_value = $billing_in_review['meta_value'] + $billing_value;
            $meta_id = $billing_in_review['meta_id'];

            $set_billing_in_review = $conn->prepare('UPDATE transactions_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
            $set_billing_in_review->execute(array('meta_value' => $meta_value, 'meta_id' => $meta_id));

        } else {
            
            $set_billing_in_review = $conn->prepare('INSERT INTO transactions_meta (meta_id, user__id, meta_key, meta_value) VALUES (:meta_id, :user__id, :meta_key, :meta_value)');
            $set_billing_in_review->execute(array('meta_id' => '0', 'user__id' => $user__id, 'meta_key' => "in_review_balance", 'meta_value' => $billing_value));

        }
        
        # Atualiza o valor disponível para saque
        $get_commission_balance = $conn->prepare('SELECT meta_value, meta_id FROM transactions_meta WHERE meta_key = "anticipation_balance" AND user__id = :user__id');
        $get_commission_balance->execute(array('user__id' => $user__id));

        $commission_balance = $get_commission_balance->fetch();
        $meta_value = $commission_balance['meta_value'] - $billing_value;
        $meta_id = $commission_balance['meta_id'];

        $set_commission_balance = $conn->prepare('UPDATE transactions_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
        $set_commission_balance->execute(array('meta_value' => $meta_value, 'meta_id' => $meta_id));

        $billing_value = number_format($billing_value, 2, ',', '.');
        $msg = 'Seu saque de R$ '. $billing_value . ' está sendo processado!';

        $feedback = array('title' => 'Solicitação Realizada!', 'type' => 'success', 'msg' => $msg);
        echo json_encode($feedback);
        exit;    

        }


    
}

exit;


?>