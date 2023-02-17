<?php 
error_reporting(-1);            
ini_set('display_errors', 1);
require dirname(__FILE__) . "/../includes/config.php";
session_name(SESSION_NAME);
session_start();

# Verifica o envio do form via POST
if (!(isset($_POST))){
    exit;
}

$type_balace = addslashes($_POST['type-balance']);
if (!preg_match("/^[(a-z-A-Z)]*$/", $type_balace)) {
    $feedback = array('status' => 0, 'msg' => 'Experimente atualizar a página e tente novamente', 'title' => "Algo está errado", 'type' => 'error');
    echo json_encode($feedback);
    exit;
}

$user__id = addslashes($_POST['user__id']);
if (!preg_match("/^[(0-9)]*$/", $user__id)) {
    $feedback = array('status' => 0, 'msg' => 'Experimente atualizar a página e tente novamente', 'title' => "Algo está errado1", 'type' => 'error');
	echo json_encode($feedback);
	exit;
}

$operation = addslashes($_POST['typeOperation']);
if (!preg_match("/^[(A-Z)]*$/", $operation)) {
    $feedback = array('status' => 0, 'msg' => 'Experimente atualizar a página e tente novamente', 'title' => "Algo está errado", 'type' => 'error');
    echo json_encode($feedback);
    exit;
}


$valueAlter = floatval(addslashes(str_replace(',', '.', str_replace('.', '', $_POST['value-alter']))));
$justification = $_POST['justification'];
$meta_key = $type_balace == 'disponivel' ? 'commission_balance' : 'anticipation_balance';
$alter_Value = (float) $valueAlter;    
try {

    // PEGAR QUANTIDADE DE ESTOQUE DO PRODUTO EM DETERMINADA LOCALIDADE
    $get_transaction = $conn->prepare('SELECT * FROM transactions_meta WHERE meta_key = :meta_key AND user__id = :user__id');
    $get_transaction->execute(array('meta_key' => $meta_key, 'user__id' => $user__id));
    $transaction = $get_transaction->fetch();

    if($get_transaction->rowCount() > 0){
        $Old_Value = (float) $transaction['meta_value'];

        if($operation == 'SUM'){
            // // adicionar valor 
            $new_value = $Old_Value + $alter_Value;
            
            $stmt = $conn->prepare("UPDATE transactions_meta SET meta_value = :new_value WHERE meta_id = :meta_id ");
            $isSuccess = $stmt->execute(array('new_value' => $new_value,'meta_id' => $transaction['meta_id'] ));

            if($isSuccess){                
                $insert_relatory = $conn->prepare("INSERT INTO billings (user__id, billing_value_full, billing_value, billing_type, billing_tax, biling_origin, billing_authorizer, billing_bank_account, billing_released, billing_request, billing_proof) VALUES (:user__id, :billing_value_full, :billing_value, :billing_type, :billing_tax, :biling_origin, :billing_authorizer, :billing_bank_account, :date, :date, :billing_proof)");
                $insert_relatory->execute(array('user__id' => $user__id, 'billing_value_full' => $alter_Value, 'billing_value' => $alter_Value, 'billing_type' => 'MANUAL', 'billing_tax' => 0.00, 'biling_origin' => 0, 'billing_authorizer' => $_SESSION['UserID'], 'billing_bank_account' => 0, 'date' => date('Y-m-d H:i:s'), 'billing_proof' => $justification));
            }
            
            $feedback = array('status' => 1, 'msg' => "O valor de R$ " . number_format($alter_Value, 2, ',', '.') . " foi adicionado!", 'title' => "Valor adicionado!", 'type' => 'success');
            echo json_encode($feedback);
            exit;
        }
        
        if($operation == 'SUB'){
            // subtrair valor
            $new_value = $Old_Value - $alter_Value;

            if($meta_key == 'anticipation_balance'){
                # Atualiza o valor "Em Análise"
                $get_billing_anticipated = $conn->prepare('SELECT meta_value, meta_id FROM transactions_meta WHERE meta_key = "anticipated_value" AND user__id = :user__id');
                $get_billing_anticipated->execute(array('user__id' => $user__id));

                if ($get_billing_anticipated->rowCount() > 0) {

                    $billing_anticipated = $get_billing_anticipated->fetch();
                    $meta_value_anticipated = $billing_anticipated['meta_value'] + $alter_Value;
                    $meta_id_anticipated = $billing_anticipated['meta_id'];

                    $set_billing_anticipated = $conn->prepare('UPDATE transactions_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
                    $set_billing_anticipated->execute(array('meta_value' => $meta_value_anticipated, 'meta_id' => $meta_id_anticipated));
                } else {

                    $set_billing_anticipated = $conn->prepare('INSERT INTO transactions_meta (meta_id, user__id, meta_key, meta_value) VALUES (:meta_id, :user__id, :meta_key, :meta_value)');
                    $set_billing_anticipated->execute(array('meta_id' => '0', 'user__id' => $user__id, 'meta_key' => "anticipated_value", 'meta_value' => $alter_Value)); 
                }
            }
            
            $stmt = $conn->prepare("UPDATE transactions_meta SET meta_value = :new_value WHERE meta_id = :meta_id ");
            $isSuccess = $stmt->execute(array('new_value' => $new_value,'meta_id' => $transaction['meta_id'] ));

            if($isSuccess){ 
                $insert_relatory = $conn->prepare("INSERT INTO billings (user__id, billing_value_full, billing_value, billing_type, billing_tax, biling_origin, billing_authorizer, billing_bank_account, billing_released, billing_request, billing_proof) VALUES (:user__id, :billing_value_full, :billing_value, :billing_type, :billing_tax, :biling_origin, :billing_authorizer, :billing_bank_account, :date, :date, :billing_proof)");
                $insert_relatory->execute(array('user__id' => $user__id, 'billing_value_full' => (-$alter_Value), 'billing_value' => (-$alter_Value), 'billing_type' => 'MANUAL', 'billing_tax' => 0.00, 'biling_origin' => 0, 'billing_authorizer' => $_SESSION['UserID'], 'billing_bank_account' => 0, 'date' => date('Y-m-d H:i:s'), 'billing_proof' => $justification));
            }

            $feedback = array('status' => 1, 'msg' => "O valor de  R$ " . number_format($alter_Value, 2, ',', '.') . " foi subtraído!", 'title' => 'Valor subtraído!', 'type' => 'success');
            echo json_encode($feedback);
            exit;
        }
    }else{
        if($operation == 'SUM'){
            // adicionar valor 
            $new_value = $alter_Value;
            
            $stmt = $conn->prepare("INSERT INTO transactions_meta ( meta_value, user__id, meta_key ) VALUES ( :meta_value, :user__id, :meta_key ) ");
            $isSuccess = $stmt->execute(array('user__id' => $user__id,'meta_value' => $new_value, 'meta_key' => $meta_key ));

            if($isSuccess){  
                $insert_relatory = $conn->prepare("INSERT INTO billings (user__id, billing_value_full, billing_value, billing_type, billing_tax, biling_origin, billing_authorizer, billing_bank_account, billing_released, billing_request, billing_proof) VALUES (:user__id, :billing_value_full, :billing_value, :billing_type, :billing_tax, :biling_origin, :billing_authorizer, :billing_bank_account, :date, :date, :billing_proof)");
                $insert_relatory->execute(array('user__id' => $user__id, 'billing_value_full' => $alter_Value, 'billing_value' => $alter_Value, 'billing_type' => 'MANUAL', 'billing_tax' => 0.00, 'biling_origin' => 0, 'billing_authorizer' => $_SESSION['UserID'], 'billing_bank_account' => 0, 'date' => date('Y-m-d H:i:s'), 'billing_proof' => $justification));
            }

            $feedback = array('status' => 1, 'msg' => "O valor de R$ " . number_format($alter_Value, 2, ',', '.') . " foi adicionado!", 'title' => "Valor adicionado!", 'type' => 'success');
            echo json_encode($feedback);
            exit;
        }
        
        if($operation == 'SUB'){
            // subtrair valor    
            $new_value = -$alter_Value;

            if($meta_key == 'anticipation_balance'){
                # Atualiza o valor "Em Análise"
                $get_billing_anticipated = $conn->prepare('SELECT meta_value, meta_id FROM transactions_meta WHERE meta_key = "anticipated_value" AND user__id = :user__id');
                $get_billing_anticipated->execute(array('user__id' => $user__id));

                if ($get_billing_anticipated->rowCount() > 0) {

                    $billing_anticipated = $get_billing_anticipated->fetch();
                    $meta_value_anticipated = $billing_anticipated['meta_value'] + $alter_Value;
                    $meta_id_anticipated = $billing_anticipated['meta_id'];

                    $set_billing_anticipated = $conn->prepare('UPDATE transactions_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
                    $set_billing_anticipated->execute(array('meta_value' => $meta_value_anticipated, 'meta_id' => $meta_id_anticipated));
                } else {

                    $set_billing_anticipated = $conn->prepare('INSERT INTO transactions_meta (meta_id, user__id, meta_key, meta_value) VALUES (:meta_id, :user__id, :meta_key, :meta_value)');
                    $set_billing_anticipated->execute(array('meta_id' => '0', 'user__id' => $user__id, 'meta_key' => "anticipated_value", 'meta_value' => $alter_Value)); 
                }
            }
            
            $stmt = $conn->prepare("INSERT INTO transactions_meta ( meta_value, user__id, meta_key ) VALUES ( :meta_value, :user__id, :meta_key ) ");
            $isSuccess = $stmt->execute(array('user__id' => $user__id,'meta_value' => $new_value, 'meta_key' => $meta_key ));

            if($isSuccess){ 
                $insert_relatory = $conn->prepare("INSERT INTO billings (user__id, billing_value_full, billing_value, billing_type, billing_tax, biling_origin, billing_authorizer, billing_bank_account, billing_released, billing_request, billing_proof) VALUES (:user__id, :billing_value_full, :billing_value, :billing_type, :billing_tax, :biling_origin, :billing_authorizer, :billing_bank_account, :date, :date, :billing_proof)");
                $insert_relatory->execute(array('user__id' => $user__id, 'billing_value_full' => (-$alter_Value), 'billing_value' => (-$alter_Value), 'billing_type' => 'MANUAL', 'billing_tax' => 0.00, 'biling_origin' => 0, 'billing_authorizer' => $_SESSION['UserID'], 'billing_bank_account' => 0, 'date' => date('Y-m-d H:i:s'), 'billing_proof' => $justification));
            }

            $feedback = array('status' => 1, 'msg' => "O valor de  R$ " . number_format($alter_Value, 2, ',', '.') . " foi subtraído!", 'title' => 'Valor subtraído!', 'type' => 'success');
            echo json_encode($feedback);
            exit;
        }        
    }
} catch(PDOException $e) {
    # Armazena o feeback negativo na variável.
    $error= 'ERROR: ' . $e->getMessage();
    $feedback = array('status' => 0, 'msg' => $error);
}

