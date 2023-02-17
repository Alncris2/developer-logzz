<?php 
error_reporting(-1);              
ini_set('display_errors', 1);  
require_once (dirname(__FILE__) . '/includes/config.php'); 

$_date = date('Y-m-d');   
// #$_date = '2022-03-14'; 

// $run_script = $conn->query("DELETE FROM `transactions_meta` WHERE `meta_key` IN ('anticipated_value', 'total_balance', 'anticipation_balance', 'cashed_out_balance', 'commission_balance', 'in_review_balance')");
// $run_script = $conn->query("UPDATE orders o SET o.order_commission_released = 0, o.order_anticipation_released  = 0 "); 


// # Busca comissões não liberadas até a data definida
// echo "Buscando comissões SACADO com a data igual ou inferior a ontem.... <br>";
// $get_billings = $conn->prepare("SELECT user__id, SUM(bg.billing_value_full) as total_value FROM billings bg WHERE `billing_released` IS NOT NULL GROUP BY bg.user__id");
// $get_billings->execute();

// while($billing = $get_billings->fetch()){
//     $new_billing_value = $billing_value = 0;
//     $user__id = $billing['user__id'];
//     $billing_value = round($billing['total_value'], 2);

//     # Libera os valores
//     $get_current_billing_value = $conn->prepare('SELECT meta_value, meta_id FROM transactions_meta WHERE user__id = :user__id AND meta_key = "cashed_out_balance"');
//     $get_current_billing_value->execute(array('user__id' => $user__id));

//     if ($get_current_billing_value->rowCount() == 0) {

//         $set_new_commission_value = $conn->prepare('INSERT INTO transactions_meta (meta_id, user__id, meta_key, meta_value) VALUES (0, :user__id, :meta_key, :meta_value)');
//         $set_new_commission_value->execute(array('user__id' => $user__id, 'meta_key' => "cashed_out_balance",'meta_value' => $billing_value));

//     } else {
 
//         $billing = $get_current_billing_value->fetch();
//         $billing_id = $billing['meta_id'];

//         $new_billing_value = round(($billing_value), 2);

//         $get_new_commission_value = $conn->prepare('UPDATE transactions_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
//         $get_new_commission_value->execute(array('meta_value' => $new_billing_value, 'meta_id' => $billing_id));

//     }

//     echo "Criado R$ SACADO de <b>" . $billing_value ."</b> para o usuário <b>". $user__id . "</b><br>";
 
// }  
 
// echo "<br><br> ---------------------- Usuarios atualizados ---------------------- <br><br>"; 

// echo "Buscando comissões SAQUE EM ANALISE com a data igual ou inferior a ontem.... <br>";
// $get_billings = $conn->prepare("SELECT user__id, SUM(bg.billing_value_full) as total_value FROM billings bg WHERE `billing_released` IS NULL GROUP BY bg.user__id");
// $get_billings->execute();

// while($billing = $get_billings->fetch()){
//     $new_billing_value = $billing_value = 0;
//     $user__id = $billing['user__id'];
//     $billing_value = round($billing['total_value'], 2);

//     # Libera os valores
//     $get_current_billing_value = $conn->prepare('SELECT meta_value, meta_id FROM transactions_meta WHERE user__id = :user__id AND meta_key = "in_review_balance"');
//     $get_current_billing_value->execute(array('user__id' => $user__id));

//     if ($get_current_billing_value->rowCount() == 0) {

//         $set_new_commission_value = $conn->prepare('INSERT INTO transactions_meta (meta_id, user__id, meta_key, meta_value) VALUES (0, :user__id, :meta_key, :meta_value)');
//         $set_new_commission_value->execute(array('user__id' => $user__id, 'meta_key' => "in_review_balance",'meta_value' => $billing_value));

//     } else {

//         $billing = $get_current_billing_value->fetch();
//         $billing_id = $billing['meta_id'];

//         $new_billing_value = round(($billing_value), 2);

//         $get_new_commission_value = $conn->prepare('UPDATE transactions_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
//         $get_new_commission_value->execute(array('meta_value' => $new_billing_value, 'meta_id' => $billing_id));

//     }

//     echo "Criado R$ SAQUE EM ANALISE de <b>" . $billing_value ."</b> para o usuário <b>". $user__id . "</b><br>";
 
// }  
 
// echo "<br><br> ---------------------- Usuarios atualizados ---------------------- <br><br>"; 
  
echo "Buscando comissões não liberadas p/ Valor TOTAL ARRECADADO com a data igual ou inferior a ontem.... <br>"; 
$get_commissions = $conn->prepare('SELECT user__id, SUM(order_final_price) AS total_value FROM orders WHERE order_status = 3 AND (order_anticipation_released = 1) GROUP BY user__id');
$get_commissions->execute();    

while($commission = $get_commissions->fetch()){
    $new_total_value = $billing_value = $old_total_value = 0; 

    $user__id = $commission['user__id'];
    $billing_value = round($commission['total_value'], 2);

    # Libera os valores
    $get_current_total_value = $conn->prepare('SELECT meta_value, meta_id FROM transactions_meta WHERE user__id = :user__id AND meta_key = "total_balance"');
    $get_current_total_value->execute(array('user__id' => $user__id));

    if ($get_current_total_value->rowCount() == 0) { 

        $new_total_value = round(($billing_value ), 2);

        $set_new_total_value = $conn->prepare('INSERT INTO transactions_meta (meta_id, user__id, meta_key, meta_value) VALUES (0, :user__id, :meta_key, :meta_value)');
        $set_new_total_value->execute(array('user__id' => $user__id, 'meta_key' => "total_balance", 'meta_value' => $new_total_value));

    } else {  

        $total = $get_current_total_value->fetch();
        $total_id = $total['meta_id'];
        $old_total_value = round($total['meta_value'], 2);

        $new_total_value = round(($billing_value), 2); 

        $update_new_total_value = $conn->prepare('UPDATE transactions_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
        $update_new_total_value->execute(array('meta_value' => $new_total_value, 'meta_id' => $total_id)); 

    }
    echo "Criando VALOR TOTAL ARRECADADO de <b>" . $new_total_value ." (". $old_total_value .")</b> para o usuário <b>". $user__id . "</b><br>";
}    
   
echo "<br><br> ---------------------- Usuarios atualizados ---------------------- <br><br>";   
 
echo "Buscando comissões não liberadas p/ Valor TOTAL COMISSIONADO com a data igual ou inferior a ontem.... <br>"; 
$get_commissions = $conn->prepare('SELECT user__id, SUM(order_liquid_value) AS total_value FROM orders WHERE order_status = 3 AND (order_anticipation_released = 1) GROUP BY user__id');
$get_commissions->execute();    

while($commission = $get_commissions->fetch()){
    $new_total_value = $billing_value = $old_total_value = 0; 

    $user__id = $commission['user__id'];
    $billing_value = round($commission['total_value'], 2); 

    # Libera os valores
    $get_current_total_value = $conn->prepare('SELECT meta_value, meta_id FROM transactions_meta WHERE user__id = :user__id AND meta_key = "total_comission"');
    $get_current_total_value->execute(array('user__id' => $user__id));

    if ($get_current_total_value->rowCount() == 0) { 

        $new_total_value = round(($billing_value ), 2);

        $set_new_total_value = $conn->prepare('INSERT INTO transactions_meta (meta_id, user__id, meta_key, meta_value) VALUES (0, :user__id, :meta_key, :meta_value)');
        $set_new_total_value->execute(array('user__id' => $user__id, 'meta_key' => "total_comission", 'meta_value' => $new_total_value));

    } else {  

        $total = $get_current_total_value->fetch();
        $total_id = $total['meta_id'];
        $old_total_value = round($total['meta_value'], 2);

        $new_total_value = round(($billing_value), 2); 

        $update_new_total_value = $conn->prepare('UPDATE transactions_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
        $update_new_total_value->execute(array('meta_value' => $new_total_value, 'meta_id' => $total_id)); 

    }
    echo "Criando VALOR TOTAL COMISSIONADO de <b>" . $new_total_value ." (". $old_total_value .")</b> para o usuário <b>". $user__id . "</b><br>"; 
}    
   
echo "<br><br> ---------------------- Usuarios atualizados ---------------------- <br><br>";
 
// # Busca comissões não liberadas p/ antecipação até a data definida
// echo "Buscando comissões não liberadas p/ ANTECIPAÇÃO com a data igual ou inferior a ontem.... <br>";
// $get_commissions = $conn->prepare('SELECT user__id, order_datetime, SUM(order_liquid_value) AS total_value, order_commission_date FROM orders WHERE (order_anticipation_released = 0) AND order_status = 3 GROUP BY user__id');
// $get_commissions->execute();

// while($commission = $get_commissions->fetch()){
//     $new_anticipation_value = $billing_value = $old_anticipation_value = 0;

//     $user__id = $commission['user__id'];
//     $billing_value = round($commission['total_value'], 2);

//     # Libera os valores
//     $get_current_anticipation_value = $conn->prepare('SELECT meta_value, meta_id FROM transactions_meta WHERE user__id = :user__id AND meta_key = "anticipation_balance"');
//     $get_current_anticipation_value->execute(array('user__id' => $user__id));

//     if ($get_current_anticipation_value->rowCount() == 0) {

//         $new_anticipation_value = round(($billing_value), 2);

//         $set_new_anticipation_value = $conn->prepare('INSERT INTO transactions_meta (meta_id, user__id, meta_key, meta_value) VALUES (0, :user__id, :meta_key, :meta_value)');
//         $set_new_anticipation_value->execute(array('user__id' => $user__id, 'meta_key' => "anticipation_balance", 'meta_value' => $new_anticipation_value));

//     } else { 

//         $anticipation = $get_current_anticipation_value->fetch();
//         $anticipation_id = $anticipation['meta_id'];
//         $old_anticipation_value = round($anticipation['meta_value'], 2);

//         $new_anticipation_value = round(($billing_value + $old_anticipation_value), 2);

//         $get_new_anticipation_value = $conn->prepare('UPDATE transactions_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
//         $get_new_anticipation_value->execute(array('meta_value' => $new_anticipation_value, 'meta_id' => $anticipation_id));

//     }
    
//     # Marca as comissões como "antecipaçao liberada";
//     $get_commissions_id = $conn->prepare('SELECT order_id FROM orders WHERE (order_anticipation_released = 0) AND (order_status = 3 AND user__id = :user__id)');
//     $get_commissions_id->execute(array('user__id' => $user__id));

//     while ($commission_id = $get_commissions_id->fetch()) {
//         $order_id = $commission_id['order_id'];

//         $set_commission_released = $conn->prepare('UPDATE orders SET order_anticipation_released = 1 WHERE order_id = :order_id');
//         $set_commission_released->execute(array('order_id' => $order_id)); 

//     }

//     echo "Criada ANTECIPAÇÃO DISPONÍVEL de <b>" . $new_anticipation_value ." (". $old_anticipation_value .")</b> para o usuário <b>". $user__id . "</b><br>";

// }  
   
// echo "<br><br> ---------------------- Usuarios atualizados ---------------------- <br><br>"; 

// echo "Criando valor sacado temporario.... <br>";
// $get_current_cashed_value = $conn->prepare('SELECT user__id, SUM(meta_value) AS total_anticipated FROM `transactions_meta` WHERE meta_key IN ("cashed_out_balance", "in_review_balance") GROUP BY user__id');
// $get_current_cashed_value->execute(); 

// while($cashed = $get_current_cashed_value->fetch()){ 

//     $user__id = $cashed['user__id'];  
//     $total_value = round($cashed['total_anticipated'], 2); 

//     # Recebe os valores JÁ ANTECIPADO
//     $get_current_anticipated_value = $conn->prepare('SELECT meta_value, meta_id FROM transactions_meta WHERE user__id = :user__id AND meta_key = "anticipated_value"');
//     $get_current_anticipated_value->execute(array('user__id' => $user__id));

//     if ($get_current_anticipated_value->rowCount() != 0) {

//         $anticipated_value = $get_current_anticipated_value->fetch();
//         $anticipated_id = $anticipated_value['meta_id'];

//         $get_new_anticipation_value = $conn->prepare('UPDATE transactions_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
//         $get_new_anticipation_value->execute(array('meta_value' => $total_value, 'meta_id' => $anticipated_id));
//     } else {
//         $insert_values = $conn->prepare("INSERT INTO `transactions_meta`(`user__id`, `transaction_id`, `meta_key`, `meta_value`) VALUES (:user__id, null, 'anticipated_value', :meta_value )");
//         $insert_values->execute(array('user__id' => $user__id, 'meta_value' => $total_value));   
//     }   

//     echo "Criado valor Antecipado <b>" . $total_value ."</b> para o usuário <b>". $user__id . "</b><br>";
// }
         
// echo "<br><br> ---------------------- Usuarios atualizados ---------------------- <br><br>";  

// echo "Removendo valor sacado do a liberar.... <br>";
// $get_current_cashed_value = $conn->prepare('SELECT meta_value, user__id, meta_id FROM transactions_meta WHERE meta_key = "anticipation_balance" GROUP BY user__id');
// $get_current_cashed_value->execute();

// while($anticipation = $get_current_cashed_value->fetch()){ 
//     $new_anticipation_value = $old_anticipation_value = $total_value = 0;
//     $user__id = $anticipation['user__id'];  
//     $anticipation_id = $anticipation['meta_id'];
//     $old_anticipation_value = round($anticipation['meta_value'], 2);

//     # Libera os valores SALDO ANTECIPAÇÃO
//     $get_current_anticipated_value = $conn->prepare('SELECT meta_value, meta_id FROM transactions_meta WHERE user__id = :user__id AND meta_key = "anticipated_value"');
//     $get_current_anticipated_value->execute(array('user__id' => $user__id));

//     if ($get_current_anticipated_value->rowCount() != 0) { 
 
//         $anticipated_value = $get_current_anticipated_value->fetch();
//         $new_anticipation_value = round(($old_anticipation_value - $anticipated_value['meta_value']), 2);   

//         $get_new_anticipation_value = $conn->prepare('UPDATE transactions_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
//         $get_new_anticipation_value->execute(array('meta_value' => $new_anticipation_value, 'meta_id' => $anticipation_id));
    
//         echo "Alterando valor Antecipado <b>" . $new_anticipation_value ." (". $old_anticipation_value ." - ". $anticipated_value['meta_value'] .")</b> para o usuário <b>". $user__id . "</b><br>";
//     }
// }
         
// echo "<br><br> ---------------------- Usuarios atualizados ---------------------- <br><br>";   
 

// # Busca comissões não liberadas até a data definida 
// echo "Buscando comissões não liberadas p/ SAQUE com a data igual ou inferior a ontem.... <br>";
// $get_commissions = $conn->prepare('SELECT user__id, order_datetime, SUM(order_liquid_value) AS total_value, order_commission_date FROM orders WHERE (order_commission_date <= :_date AND order_commission_released = 0) AND order_status = 3 GROUP BY user__id');
// $get_commissions->execute(array('_date' => $_date));

// while($commission = $get_commissions->fetch()){
  
//     $new_commission_value = $new_value_antecipated = $value_anticipation_recalc = $new_value_antecipated = $new_anticipation_value = 0.00;
//     $billing_value = $anticipated = $anticipation = $commission_value = 0.00;
//     // @$anticipation['meta_value'] = @$anticipated['meta_value'] = 0.00 ;      

//     $user__id = $commission['user__id'];
//     $billing_value = round($commission['total_value'], 2);


//     # Libera os valores SALDO DISPONIVEL
//     $get_current_commission_value = $conn->prepare('SELECT meta_value, meta_id FROM transactions_meta WHERE user__id = :user__id AND meta_key = "commission_balance"');
//     $get_current_commission_value->execute(array('user__id' => $user__id));

//     # Libera os valores SALDO ANTECIPAÇÃO
//     $get_current_anticipation_value = $conn->prepare('SELECT meta_value, meta_id FROM transactions_meta WHERE user__id = :user__id AND meta_key = "anticipation_balance"');
//     $get_current_anticipation_value->execute(array('user__id' => $user__id));

//     # Recebe os valores JÁ ANTECIPADO
//     $get_current_anticipated_value = $conn->prepare('SELECT meta_value, meta_id FROM transactions_meta WHERE user__id = :user__id AND meta_key = "anticipated_value"');
//     $get_current_anticipated_value->execute(array('user__id' => $user__id));

//     $value_anticipation_recalc = $commission_value = $billing_value;

//     if ($get_current_anticipated_value->rowCount() != 0) {

        
//         $anticipated = $get_current_anticipated_value->fetch();
//         $anticipated_id = $anticipated['meta_id'];

//         $commission_value = round(($billing_value - $anticipated['meta_value']), 2);  

//         $get_new_anticipated_value = $conn->prepare('UPDATE transactions_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
//         $get_new_anticipated_value->execute(array('meta_value' => $new_value_antecipated, 'meta_id' => $anticipated_id));
//     }

//     if ($get_current_commission_value->rowCount() == 0) {

//         $set_new_commission_value = $conn->prepare('INSERT INTO transactions_meta (meta_id, user__id, meta_key, meta_value) VALUES (0, :user__id, :meta_key, :meta_value)');
//         $set_new_commission_value->execute(array('user__id' => $user__id, 'meta_key' => "commission_balance",'meta_value' => $commission_value));

//     } else { 


//         $commission = $get_current_commission_value->fetch();
//         $commission_id = $commission['meta_id'];
//         $old_commission_value = round($commission['meta_value'], 2);        

//         $new_commission_value = round(($old_commission_value + $commission_value), 2);      

//         $get_new_commission_value = $conn->prepare('UPDATE transactions_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
//         $get_new_commission_value->execute(array('meta_value' => $new_commission_value, 'meta_id' => $commission_id));

//     }

//     if (!$get_current_anticipation_value->rowCount() == 0) {

        
//         $anticipation = $get_current_anticipation_value->fetch();
//         $anticipation_id = $anticipation['meta_id'];
//         $old_anticipation_value = round($anticipation['meta_value'], 2);

//         $new_anticipation_value = round(($old_anticipation_value - $commission_value), 2);
        

//         $get_new_anticipation_value = $conn->prepare('UPDATE transactions_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
//         $get_new_anticipation_value->execute(array('meta_value' => $new_anticipation_value, 'meta_id' => $anticipation_id));

//     }
   
//     # Marca as comissões como "antecipaçao liberada";
//     $get_commissions_id = $conn->prepare('SELECT order_id FROM orders WHERE (order_commission_date <= :_date AND order_commission_released = 0) AND (order_status = 3 AND user__id = :user__id)');
//     $get_commissions_id->execute(array('_date' => $_date, 'user__id' => $user__id));

//     while ($commission_id = $get_commissions_id->fetch()) {
//         $order_id = $commission_id['order_id'];

//         $set_commission_released = $conn->prepare('UPDATE orders SET order_commission_released = 1 WHERE order_id = :order_id');
//         $set_commission_released->execute(array('order_id' => $order_id)); 
 
//     }  

//     echo "Criado novo SALDO DISPONÍVEL de <b>" . $new_commission_value ." (". $commission_value .")</b>  e NOVO SALDO DE ANTECIPAÇÃO de <b>" . $new_anticipation_value ." (". $old_anticipation_value ." - ". @$value_anticipation_recalc .")</b> para o usuário <b>". $user__id . "</b><br>";

// }
 
?>   