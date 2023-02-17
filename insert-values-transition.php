<?php 
error_reporting(-1);             
ini_set('display_errors', 1);

require "includes/config.php";
include "includes/classes/RandomStrGenerator.php";

echo "Buscando comissões não liberadas p/ SAQUE com a data igual ou inferior a ontem.... <br>";
$get_commissions = $conn->prepare('SELECT *, o.order_number AS order_number FROM orders o LEFT JOIN transactions t ON o.order_number = t.order_number WHERE t.order_number IS NULL AND order_status IN (3) ORDER BY order_id DESC');
$get_commissions->execute();  

while($order = $get_commissions->fetch()){

    $verify_duplicite_transaction = $conn->prepare('SELECT * FROM transactions WHERE order_number = :order_number AND user_id = :user_id');
    $verify_duplicite_transaction->execute(array('order_number' => $order['order_number'], 'user_id' => $order['user__id']));
    if ($verify_duplicite_transaction->rowCount() > 0) {
        continue;
    }

    if (mb_strpos($order['order_number'], 'AFI') === false) {
        $order_number = $order['order_number'];        
    } else {
        $order_number = explode("AFI", $order['order_number'])[1];
    }

    $get_meta_values = $conn->prepare('SELECT meta_value, meta_key FROM orders_meta WHERE order_number = :order_number');
    $get_meta_values->execute(array('order_number' => $order_number)); 
    
    while($meta_values = $get_meta_values->fetch()){ 
        if($meta_values['meta_key'] === 'producer_tax'){
            $producer_tax = $meta_values['meta_value'];
            continue;
        }
        if($meta_values['meta_key'] === 'member_tax'){
            $afiliate_tax = $meta_values['meta_value'];
            continue;
        }
        if($meta_values['meta_key'] === 'ship_tax'){
            $ship_tax = $meta_values['meta_value']; 
            continue;
        }   
    }

    if (mb_strpos($order['order_number'], 'AFI') === false) {
        $tax = $producer_tax;        
    } else {
        $tax = $afiliate_tax;
        $ship_tax = 0.00;
    }

    $transaction_code = new RandomStrGenerator();
    $transaction_code =   strtoupper(date('jn') .'R'.  $transaction_code->lettersAndNumbers(4));

    $verify_unique_transaction_code = $conn->prepare('SELECT * FROM transactions WHERE transaction_code = :transaction_code');
    $verify_unique_transaction_code->execute(array('transaction_code' => $transaction_code));

    if (!($verify_unique_transaction_code->rowCount() == 0)) {
        do {


            $transaction_code = new RandomStrGenerator();
            $transaction_code = strtoupper(date('jn') .'R'. $transaction_code->lettersAndNumbers(4));

            $verify_unique_transaction_code = $conn->prepare('SELECT * FROM transactions WHERE transaction_code = :transaction_code');
            $verify_unique_transaction_code->execute(array('transaction_code' => $transaction_code));
        } while ($verify_unique_transaction_code->rowCount() != 0);
    } 

    $set_new_anticipation_value = $conn->prepare('INSERT INTO transactions (transaction_id, user_id, value_liquid, value_brute, tax_value, logistic_value, status, type, date_start, date_end, transaction_code, order_number) VALUES (NULL, :user_id, :value_liquid, :value_brute, :tax_value, :logistic_value, :status, :type, :date_start, :date_end, :transaction_code, :order_number)');
    $set_new_anticipation_value->execute(array(
        'user_id'       => $order['user__id'], 
        'value_liquid'  => $order['order_liquid_value'],
        'value_brute'   => $order['order_liquid_value'] + $tax - $ship_tax,
        'tax_value'     => $tax, 
        'logistic_value'=> $ship_tax, 
        'status'        => $order['order_commission_date'] > date('Y-m-d H:i:s') ? 2 : 1, 
        'type'          => 7, 
        'date_start'    => $order['order_delivery_time'], 
        'date_end'      => $order['order_commission_date'], 
        'order_number'  => $order['order_number'], 
        'transaction_code' => $transaction_code
    ));

    echo "Criado uma transação de <b>" . $order['order_liquid_value'] ." (". $transaction_code .")</b> para o usuário <b>". $order['user__id'] . "</b><br>";
}

// echo "Buscando comissões não liberadas p/ SAQUE com a data igual ou inferior a ontem.... <br>";
// $get_billings = $conn->prepare('SELECT * FROM billings b WHERE billing_type = "ANTECIPACAO" AND `billing_proof` IS NOT NULL ');
// $get_billings->execute();  

// while($billing = $get_billings->fetch()){

//     $verify_duplicite_transaction = $conn->prepare('SELECT * FROM transactions WHERE checking_copy = :checking_copy AND user_id = :user_id');
//     $verify_duplicite_transaction->execute(array('checking_copy' => $billing['billing_id'], 'user_id' => $billing['user__id']));
//     if ($verify_duplicite_transaction->rowCount() > 0) {
//         continue;
//     }


//     $transaction_code = new RandomStrGenerator();
//     $transaction_code =   strtoupper(date('jn') .'R'.  $transaction_code->lettersAndNumbers(4));

//     $verify_unique_transaction_code = $conn->prepare('SELECT * FROM transactions WHERE transaction_code = :transaction_code');
//     $verify_unique_transaction_code->execute(array('transaction_code' => $transaction_code));

//     if (!($verify_unique_transaction_code->rowCount() == 0)) {
//         do {
//             $transaction_code = new RandomStrGenerator();
//             $transaction_code = strtoupper(date('jn') .'R'. $transaction_code->lettersAndNumbers(4));

//             $verify_unique_transaction_code = $conn->prepare('SELECT * FROM transactions WHERE transaction_code = :transaction_code');
//             $verify_unique_transaction_code->execute(array('transaction_code' => $transaction_code));
//         } while ($verify_unique_transaction_code->rowCount() != 0);
//     } 

//     $set_new_anticipation_value = $conn->prepare('INSERT INTO transactions (transaction_id, user_id, value_liquid, value_brute, tax_value, logistic_value, status, type, date_start, date_end, bank_id, bank_proof, checking_copy, transaction_code) VALUES (NULL, :user_id, :value_liquid, :value_brute, :tax_value, :logistic_value, :status, :type, :date_start, :date_end, :bank_id, :bank_proof, :checking_copy, :transaction_code)');
//     $set_new_anticipation_value->execute(array(
//         'user_id'       => $billing['user__id'], 
//         'value_liquid'  => -$billing['billing_value'],
//         'value_brute'   => -$billing['billing_value_full'],
//         'tax_value'     => $billing['billing_tax'], 
//         'logistic_value'=> 0.00, 
//         'status'        => $billing['billing_released'] ? 2 : 1, 
//         'type'          => 1, 
//         'date_start'    => $billing['billing_request'], 
//         'date_end'      => $billing['billing_released'],
//         'bank_id'       => $billing['billing_bank_account'], //564 
//         'bank_proof'    => $billing['billing_proof'],        
//         'checking_copy' => $billing['billing_id'],
//         'transaction_code' => $transaction_code
//     ));

//     echo "Criado uma transação de <b>" . -$billing['billing_value'] ." (". $transaction_code .")</b> para o usuário <b>". $billing['user__id'] . "</b><br>";
// }

// echo "Buscando comissões não liberadas p/ SAQUE com a data igual ou inferior a ontem.... <br>";
// $get_billings = $conn->prepare('SELECT * FROM billings b WHERE billing_type = "SAQUE" AND `billing_proof` IS NOT NULL ');
// $get_billings->execute();  

// while($billing = $get_billings->fetch()){

//     $verify_duplicite_transaction = $conn->prepare('SELECT * FROM transactions WHERE checking_copy = :checking_copy AND user_id = :user_id');
//     $verify_duplicite_transaction->execute(array('checking_copy' => $billing['billing_id'], 'user_id' => $billing['user__id']));
//     if ($verify_duplicite_transaction->rowCount() > 0) {
//         continue;
//     }


//     $transaction_code = new RandomStrGenerator();
//     $transaction_code =   strtoupper(date('jn') .'R'.  $transaction_code->lettersAndNumbers(4));

//     $verify_unique_transaction_code = $conn->prepare('SELECT * FROM transactions WHERE transaction_code = :transaction_code');
//     $verify_unique_transaction_code->execute(array('transaction_code' => $transaction_code));

//     if (!($verify_unique_transaction_code->rowCount() == 0)) {
//         do {
//             $transaction_code = new RandomStrGenerator();
//             $transaction_code = strtoupper(date('jn') .'R'. $transaction_code->lettersAndNumbers(4));

//             $verify_unique_transaction_code = $conn->prepare('SELECT * FROM transactions WHERE transaction_code = :transaction_code');
//             $verify_unique_transaction_code->execute(array('transaction_code' => $transaction_code));
//         } while ($verify_unique_transaction_code->rowCount() != 0);
//     } 

//     $set_new_anticipation_value = $conn->prepare('INSERT INTO transactions (transaction_id, user_id, value_liquid, value_brute, tax_value, logistic_value, status, type, date_start, date_end, bank_id, bank_proof, checking_copy, transaction_code) VALUES (NULL, :user_id, :value_liquid, :value_brute, :tax_value, :logistic_value, :status, :type, :date_start, :date_end, :bank_id, :bank_proof, :checking_copy, :transaction_code)');
//     $set_new_anticipation_value->execute(array(
//         'user_id'       => $billing['user__id'], 
//         'value_liquid'  => -$billing['billing_value'],
//         'value_brute'   => -$billing['billing_value_full'],
//         'tax_value'     => $billing['billing_tax'], 
//         'logistic_value'=> 0.00, 
//         'status'        => $billing['billing_released'] ? 2 : 1, 
//         'type'          => 5, 
//         'date_start'    => $billing['billing_request'], 
//         'date_end'      => $billing['billing_released'],
//         'bank_id'       => $billing['billing_bank_account'], //564 
//         'bank_proof'    => $billing['billing_proof'],        
//         'checking_copy' => $billing['billing_id'],
//         'transaction_code' => $transaction_code
//     ));

//     echo "Criado uma transação de <b>" . -$billing['billing_value'] ." (". $transaction_code .")</b> para o usuário <b>". $billing['user__id'] . "</b><br>";
// }



// echo "Buscando comissões não liberadas p/ SAQUE com a data igual ou inferior a ontem.... <br>";
// $get_commissions = $conn->prepare('SELECT *, o.order_number AS order_number FROM orders o LEFT JOIN transactions t ON o.order_number = t.order_number WHERE t.order_number IS NULL AND order_status IN (4) ORDER BY order_id DESC');
// $get_commissions->execute();  

// while($order = $get_commissions->fetch()){

//     $verify_duplicite_transaction = $conn->prepare('SELECT * FROM transactions WHERE order_number = :order_number AND user_id = :user_id');
//     $verify_duplicite_transaction->execute(array('order_number' => $order['order_number'], 'user_id' => $order['user__id']));
//     if ($verify_duplicite_transaction->rowCount() > 0) {
//         continue;
//     }

//     if (mb_strpos($order['order_number'], 'AFI') === false) {
//         $order_number = $order['order_number'];        
//     } else {
//         $order_number = explode("AFI", $order['order_number'])[1];
//     }

//     $get_meta_values = $conn->prepare('SELECT meta_value, meta_key FROM orders_meta WHERE order_number = :order_number');
//     $get_meta_values->execute(array('order_number' => $order_number)); 
    
//     while($meta_values = $get_meta_values->fetch()){ 
//         if($meta_values['meta_key'] === 'producer_tax'){
//             $producer_tax = $meta_values['meta_value'];
//             continue;
//         }
//         if($meta_values['meta_key'] === 'member_tax'){
//             $afiliate_tax = $meta_values['meta_value'];
//             continue;
//         }
//         if($meta_values['meta_key'] === 'ship_tax'){
//             $ship_tax = $meta_values['meta_value']; 
//             continue;
//         }   
//     }

//     if (mb_strpos($order['order_number'], 'AFI') === false) {
//         $tax = $producer_tax;        
//     } else {
//         $tax = $afiliate_tax;
//         $ship_tax = 0.00;
//     }

//     $transaction_code = new RandomStrGenerator();
//     $transaction_code =   strtoupper(date('jn') .'R'.  $transaction_code->lettersAndNumbers(4));

//     $verify_unique_transaction_code = $conn->prepare('SELECT * FROM transactions WHERE transaction_code = :transaction_code');
//     $verify_unique_transaction_code->execute(array('transaction_code' => $transaction_code));

//     if (!($verify_unique_transaction_code->rowCount() == 0)) {
//         do {


//             $transaction_code = new RandomStrGenerator();
//             $transaction_code = strtoupper(date('jn') .'R'. $transaction_code->lettersAndNumbers(4));

//             $verify_unique_transaction_code = $conn->prepare('SELECT * FROM transactions WHERE transaction_code = :transaction_code');
//             $verify_unique_transaction_code->execute(array('transaction_code' => $transaction_code));
//         } while ($verify_unique_transaction_code->rowCount() != 0);
//     } 

//     $set_new_anticipation_value = $conn->prepare('INSERT INTO transactions (transaction_id, user_id, value_liquid, value_brute, tax_value, logistic_value, status, type, date_start, date_end, transaction_code, order_number) VALUES (NULL, :user_id, :value_liquid, :value_brute, :tax_value, :logistic_value, :status, :type, :date_start, :date_end, :transaction_code, :order_number)');
//     $set_new_anticipation_value->execute(array(
//         'user_id'       => $order['user__id'], 
//         'value_liquid'  => $order['order_liquid_value'],
//         'value_brute'   => $order['order_liquid_value'] + $tax,
//         'tax_value'     => $tax, 
//         'logistic_value'=> $ship_tax, 
//         'status'        => $order['order_commission_date'] > date('Y-m-d H:i:s') ? 2 : 1, 
//         'type'          => 7, 
//         'date_start'    => $order['order_delivery_time'], 
//         'date_end'      => $order['order_commission_date'], 
//         'order_number'  => $order['order_number'], 
//         'transaction_code' => $transaction_code
//     ));

//     echo "Criado uma transação de <b>" . $order['order_liquid_value'] ." (". $transaction_code .")</b> para o usuário <b>". $order['user__id'] . "</b><br>";
// }