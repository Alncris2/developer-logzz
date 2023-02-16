<?php

require dirname(__FILE__) . "/../includes/config.php";

session_name(SESSION_NAME);
session_start();


function InternalErrorReport($error = null){
    $feedback = array('title' => 'Erro Interno', 'type' => 'error', 'msg' => 'Não foi possível solicitar o seu saque. Entre em contato com o suporte.', 'error' => $error);
    echo json_encode($feedback);
    exit;
}

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}

if(!(isset($_POST['action']))){
    header('Location: ' . SERVER_URI . '/nao-encontrado');
}


$billing_id = addslashes($_POST['billing']);
$billing_released = date("Y-m-d H:i:s");
$billing_authorizer = $_SESSION['UserID'];
$billing_type = @$_POST['isCommission']; 


# Trata o arquivo anexo comprovante.
$product_image = 'default.jpg';
if(isset($_FILES['comprovante']) && $_FILES['comprovante']['size'] > 0){

    $filetypes = array('jfif', 'pdf', 'png', 'jpeg', 'jpg');
    $image_filetype_array = explode('.', $_FILES['comprovante']['name']);
    $filetype = strtolower(end($image_filetype_array));

    # Valida se a extensão do arquivo é aceita
    if (in_array($filetype, $filetypes) == false){
          $feedback = array('title' => 'Formato Incorreto', 'type' => 'warning', 'msg' => 'Você precisa anexar um arquivo de imagem ou um PDF.');
       echo json_encode($feedback);
       exit; 
    }

    $new_name = date("Ymd-His") . 'billing-' . $billing_id . '.' . $filetype; 
    $dir = '../uploads/saques/comprovantes/'; 
    if (move_uploaded_file($_FILES['comprovante']['tmp_name'], $dir.$new_name)){

    } else {
        $feedback = array('title' => 'Erro', 'type' => 'warning', 'msg' => 'Não deu! Erro ao fazer upload da imagem do produto!');
        echo json_encode($feedback);
        exit;
    }

} else {
    $feedback = array('msg' => 'Você precisa anexar o comprovante da transação.', 'title' => 'Sem Comprovante', 'type' => 'warning'); 
    echo json_encode($feedback);
    exit;
}

# Busca o valor a ser liberado
$get_billing_value = $conn->prepare('SELECT billing_value, user__id FROM billings WHERE billing_id = :billing_id');
$get_billing_value->execute(array('billing_id' => $billing_id));


# Busca o valor a ser liberado
$get_transactions = $conn->prepare('SELECT transaction_id FROM transactions WHERE checking_copy = :billing_id');
$get_transactions->execute(array('billing_id' => $billing_id));
if (!$transaction_id = $get_transactions->fetch()[0]){
    $feedback = array('msg' => 'Aconteceu algum problema na solicitação de saque do úsuario, entre em contato com o usuário para que ele solicite outro saque ou entre em contato com um o suporte de desenvolvimento.', 'title' => 'Não é possivel completar esse saque!', 'type' => 'error'); 
    echo json_encode($feedback);
    exit;
}

if ($get_billing_value->rowCount() > 0){
    $billing_value_ = $get_billing_value->fetch();
    $billing_value = $billing_value_['billing_value'];
    $user__id = $billing_value_['user__id'];    
}

    try{

        $billing_release = $conn->prepare('UPDATE billings SET billing_released = :billing_released, billing_authorizer = :billing_authorizer, billing_proof = :billing_proof WHERE billing_id = :billing_id');
        $billing_release->execute(array('billing_released' => $billing_released, 'billing_authorizer' => $billing_authorizer, 'billing_proof' => $new_name, 'billing_id' => $billing_id));

        # ATUALIZANDO A TRANSAÇÃO DO SAQUE
        $billing_release = $conn->prepare('UPDATE transactions SET date_end = :date_end, bank_proof = :bank_proof, status = 2 WHERE transaction_id = :transaction_id');
        $billing_release->execute(array('date_end' => $billing_released, 'bank_proof' => $new_name, 'transaction_id' => $transaction_id));
    

        // if(isset($billing_type) && $billing_type == 'sacar-comissao'){
        
        //     // atualizar valor sacado 
        //     $query = "SELECT meta_value_payer FROM recruitment_commission_meta WHERE user__recruiter_id = :user__id AND meta_key = 'billing_commission_request'";
        //     $stmt = $conn->prepare($query);
        //     $stmt->execute(['user__id' => $billing_authorizer]);
            
        //     $value = $stmt->fetch()['meta_value_payer'];
            
        //     $query = "UPDATE recruitment_commission_meta SET meta_value_payer = :new_value WHERE user__recruiter_id = :user__id AND meta_key = 'billing_commission_request'";
        //     $stmt = $conn->prepare($query);
        //     $stmt->execute([
        //         'user__id' => $billing_authorizer,
        //         'new_value' => $value + $billing_value
        //     ]);
            
        //     // atualizar valor em analise 
        //     $query = "SELECT meta_value_available FROM recruitment_commission_meta WHERE user__recruiter_id = :user__id AND meta_key = 'in_review_balance_commission'";
        //     $stmt = $conn->prepare($query);
        //     $stmt->execute(['user__id' => $billing_authorizer]);
            
        //     $value_in_review = $stmt->fetch()['meta_value_available'];
            
            
        //     $query = "UPDATE recruitment_commission_meta SET meta_value_available = :new_value WHERE user__recruiter_id = :user__id AND meta_key = 'in_review_balance_commission'";
        //     $stmt = $conn->prepare($query);
        //     $stmt->execute([
        //         'user__id' => $billing_authorizer,
        //         'new_value' => $value_in_review - $billing_value
        //     ]);
            
        //     $feedback = array('title' => 'Saque Liberado', 'type' => 'success', 'msg' => 'O status da solicitação foi alterado.');
        //     echo json_encode($feedback);
        //     exit;
            
        // } else {
            
        //     # Atualiza o valor "Em Análise"
        //     // $get_billing_in_review = $conn->prepare('SELECT meta_value, meta_id FROM transactions_meta WHERE meta_key = "in_review_balance" AND user__id = :user__id');
        //     // $get_billing_in_review->execute(array('user__id' => $user__id));            
    
        //     // if ($get_billing_in_review->rowCount() > 0){
                
        //     //     $billing_in_review = $get_billing_in_review->fetch();
        //     //     $meta_value = $billing_in_review['meta_value'] - $billing_value;
        //     //     $meta_id = $billing_in_review['meta_id'];                
    
        //     //     $set_billing_in_review = $conn->prepare('UPDATE transactions_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
        //     //     $set_billing_in_review->execute(array('meta_value' => $meta_value, 'meta_id' => $meta_id));
    
        //     // } else {                
        //     //     $set_billing_in_review = $conn->prepare('INSERT INTO transactions_meta (user__id, meta_key, meta_value) VALUES (:user__id, :meta_key, :meta_value)');
        //     //     $set_billing_in_review->execute(array('user__id' => $user__id, 'meta_key' => "in_review_balance", 'meta_value' => $billing_value));    
        //     // }

        //     # Atualiza o valor "Sacado"
        //     // $get_billing_anticipated = $conn->prepare('SELECT meta_value, meta_id FROM transactions_meta WHERE meta_key = "anticipated_value" AND user__id = :user__id');
        //     // $get_billing_anticipated->execute(array('user__id' => $user__id));
            
        //     // if ($get_billing_anticipated->rowCount() > 0){
        //     //     $billing_anticipate = $get_billing_anticipated->fetch();
        //     //     $meta_anticipate_value = $billing_anticipate['meta_value'] - $billing_value;
        //     //     $meta_anticipate_id = $billing_anticipate['meta_id'];

        //     //     $set_billing_anticipated = $conn->prepare('UPDATE transactions_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
        //     //     $set_billing_anticipated->execute(array('meta_value' => $meta_anticipate_value, 'meta_id' => $meta_anticipate_id));

        //     // }  else {
        //     //     $set_billing_anticipated = $conn->prepare('INSERT INTO transactions_meta (user__id, meta_key, meta_value) VALUES (:user__id, :meta_key, :meta_value)');
        //     //     $set_billing_anticipated->execute(array('user__id' => $user__id, 'meta_key' => "anticipated_value", 'meta_value' => $billing_value));
        //     // }
    
        //     # Atualiza o valor "Em Análise"
        //     // $get_billing_in_review = $conn->prepare('SELECT meta_value, meta_id FROM transactions_meta WHERE meta_key = "cashed_out_balance" AND user__id = :user__id');
        //     // $get_billing_in_review->execute(array('user__id' => $user__id));
    
        //     // if ($get_billing_in_review->rowCount() > 0){
                
        //     //     $billing_in_review = $get_billing_in_review->fetch();
        //     //     $meta_value = $billing_in_review['meta_value'] + $billing_value;
        //     //     $meta_id = $billing_in_review['meta_id'];
    
        //     //     $set_billing_in_review = $conn->prepare('UPDATE transactions_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
        //     //     $set_billing_in_review->execute(array('meta_value' => $meta_value, 'meta_id' => $meta_id));
    
        //     // } else {
                
        //     //     $set_billing_in_review = $conn->prepare('INSERT INTO transactions_meta (user__id, meta_key, meta_value) VALUES (:user__id, :meta_key, :meta_value)');
        //     //     $set_billing_in_review->execute(array('user__id' => $user__id, 'meta_key' => "cashed_out_balance", 'meta_value' => $billing_value));
    
        //     // }
    
        //     // $set_commission_balance = $conn->prepare('UPDATE transactions_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
        //     // $set_commission_balance->execute(array('meta_value' => $meta_value, 'meta_id' => $meta_id));
    
        //     $feedback = array('title' => 'Saque Liberado', 'type' => 'success', 'msg' => 'O status da solicitação foi alterado.');
        //     echo json_encode($feedback);
        //     exit;
        // }            

    }  catch(PDOException $e) {

        InternalErrorReport('ERROR: ' . $e->getMessage());

    }

?>
