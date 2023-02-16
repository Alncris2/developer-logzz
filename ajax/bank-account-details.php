<?php
require dirname(__FILE__) . "/../includes/config.php";
session_name(SESSION_NAME);
session_start();

# Recebe o ID via GET
$account_id = intval($_GET['id']);

try {

    # 
    $get_account_details = $conn->prepare('SELECT * FROM bank_account_list WHERE account_id = :account_id');
    $get_account_details->execute(array('account_id' => $account_id));
    $account_details = $get_account_details->fetch();

    if ($account_details['account_status'] == 2){
        $acc_status = '<span tilte="Conta Aprovada" class="badge badge-sm badge-pill badge-success">Conta Aprovada</span>';
        $justification = false;
    } else if ($account_details['account_status'] == 0){
        $acc_status = '<span tilte="Conta Reprovada" class="badge badge-sm badge-pill badge-danger">Conta Reprovada</span>';
        $justification = "<i>" . $account_details['disapproval_justification'] . "</i>";
        $_SESSION['reprovedAccountID'] = $account_details['account_id'];
    } else {
        $acc_status = '<span tilte="Conta Pendente de Revisão" class="badge badge-sm badge-pill badge-warning">Revisão Pendente</span>';
        $justification = false;
    }

    if ($account_details['account_status'] == 2){
        $type = "Conta Poupança";
    } else {
        $type = "Conta Corrente";
    }

    if ($account_details['account_status'] == 2){
        $type = "Conta Poupança";
    } else {
        $type = "Conta Corrente";
    }

    if (@$account_details['account_pix_key'] != NULL && @$account_details['account_pix_type'] != NULL){
        $pix = @$account_details['account_pix_type'];
        if ($pix == 1) {
            $pix = "Telefone";
        } else if ($pix == 2) {
            $pix = "CPF";
        } else if ($pix == 3) {
            $pix = "CNPJ";
        } else if ($pix == 4) {
            $pix = "Email";
        } else if ($pix == 5) {
            $pix = "Aleatória";
        }

        $key = @$account_details['account_pix_key'];
    } else {
        $pix = "!";
        $key = "Chave não informada";
    }

    $feedback = array(
        'status' => $acc_status,
        'bank' => bankName($account_details['account_bank']),
        'agency' => $account_details['account_agency'],
        'number' => $account_details['account_number'],
        'type' => $type,
        'pix' => $pix,
        'key' => $key,
        'justification' => $justification
    );
    
    echo json_encode($feedback);
    exit;

} catch (PDOException $e) {

    $error = 'ERROR: ' . $e->getMessage();
    $feedback = array(
        'range_list' => 'ERRO'
    );
    
    echo json_encode($feedback);
    exit;
}

?>