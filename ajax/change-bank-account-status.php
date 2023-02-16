<?php

require dirname(__FILE__) . "/../includes/config.php";

session_name(SESSION_NAME);
session_start();

# Verifica o envio do form via POST
if (isset($_GET['action']) && $_GET['action'] == 2){

    # Recebe a trata os inputs
    $account_id = addslashes($_GET['id']);
    if (!preg_match("/^[0-9]*$/", $account_id)) {
        $feedback = array('status' => 0, 'msg' => 'Atualize a página e tente novamente', 'title' => "Algo está errado");
        echo json_encode($feedback);
        exit;
    }

    $account_status = intval(addslashes($_GET['action']));
    if (!preg_match("/^[0-2]*$/", $account_status)) {
        $feedback = array('status' => 0, 'msg' => 'Atualize a página e tente novamente', 'title' => "Algo está errado");
        echo json_encode($feedback);
        exit;
    }

    # Prepara a query para atualizar a privacidade da oferta.
    $stmt = $conn->prepare('UPDATE bank_account_list SET account_status = :account_status WHERE account_id = :account_id');

        try {
            $stmt->execute(array('account_status' => $account_status, 'account_id' => $account_id));

            $feedback = array('status' => 1, 'msg' => 'Conta Aprovada!', 'string' => 'Apr.', 'classes' => 'btn-success');

        } catch(PDOException $e) {
            $error = 'ERROR: ' . $e->getMessage();
            $feedback = array('status' => '0', 'msg' => $error);
        }

    echo json_encode($feedback);

}

else if (isset($_GET['action']) && $_GET['action'] == 0){ 

    # Recebe a trata os inputs
    $account_id = addslashes($_GET['id']);
    if (!preg_match("/^[0-9]*$/", $account_id)) {
        $feedback = array('status' => 0, 'msg' => 'Atualize a página e tente novamente', 'title' => "Algo está errado");
        echo json_encode($feedback);
        exit;
    }

    $account_status = intval(addslashes($_GET['action']));
    if (!preg_match("/^[0-2]*$/", $account_status)) {
        $feedback = array('status' => 0, 'msg' => 'Atualize a página e tente novamente', 'title' => "Algo está errado");
        echo json_encode($feedback);
        exit;
    }

    $disapproval_justification = addslashes($_GET['justification']);
    if (!preg_match("/^[a-zA-Z-À-ú0-9.\/£$%^&*()}{:\'#~<>,;!@\|\-=\-_+\-¬\`\' ]*$/", $disapproval_justification)) {
        $feedback = array('status' => 0, 'msg' => 'O conteúdo da justificativa contém caracteres inválivos. Por favor, use somente letras, números e pontuação.', 'title' => "Algo está errado");
        echo json_encode($feedback);
        exit;
    }

    # Prepara a query para atualizar a privacidade da oferta.
    $stmt = $conn->prepare('UPDATE bank_account_list SET account_status = :account_status, disapproval_justification = :disapproval_justification WHERE account_id = :account_id');

        try {
            $stmt->execute(array('account_status' => $account_status, 'disapproval_justification' => $disapproval_justification, 'account_id' => $account_id));

            $feedback = array('status' => 1, 'msg' => 'Conta Aprovada!', 'string' => 'Apr.', 'classes' => 'btn-success');

        } catch(PDOException $e) {
            $error = 'ERROR: ' . $e->getMessage();
            $feedback = array('status' => '0', 'msg' => $error);
        }

    echo json_encode($feedback);

}

else {
	$feedback = array('status' => 0, 'msg' => 'Atualize a página e tente novamente', 'title' => "Algo está errado"); 
	echo json_encode($feedback);
	exit;
}

?>