<?php

require dirname(__FILE__) . "/../includes/config.php";
require (dirname(__FILE__) . '/../includes/classes/RandomStrGenerator.php');

session_name(SESSION_NAME);
session_start();

# Verifica o envio do form via POST
if (!(isset($_POST['action']))){
	exit;
}

# Variáveis internas
$user__id = $_SESSION['UserID'];

# Recebe a trata os inputs
$bank = addslashes($_POST['banco']);
if (!preg_match("/^[a-zA-Z-À-ú0-9\/£$%^&*()}{:\'#~<>,;!@\|\-=\-_+\-¬\`\' ]*$/", $bank)) {
    $feedback = array('status' => 0, 'msg' => 'Informe o banco.', 'title' => "Algo está errado");
    echo json_encode($feedback);
    exit;
} else if (empty($bank)) {
    $feedback = array('status' => 0, 'msg' => "Informe o banco.", 'title' => "Algo está errado");
    echo json_encode($feedback);
    exit;
}

/**
 * 
 * Adiciona uma nova conta
 * 
 * 
 */
if ($_POST['action'] == 'add-bank-account'){

$agency = addslashes($_POST['agencia']);
if (!preg_match("/^[0-9\- ]*$/", $agency)) {
    $feedback = array('status' => 0, 'msg' => 'Confira se a Agência informada está correta.', 'title' => "Algo está errado");
    echo json_encode($feedback);
    exit;
} else if (empty($agency)) {
    $feedback = array('status' => 0, 'msg' => "Informe a Agência", 'title' => "Algo está errado");
    echo json_encode($feedback);
    exit;
}

$account = addslashes($_POST['conta']);
if (!preg_match("/^[0-9\- ]*$/", $account)) {
    $feedback = array('status' => 0, 'msg' => 'Confira se a Conta informada está correta.', 'title' => "Algo está errado");
    echo json_encode($feedback);
    exit;
} else if (empty($agency)) {
    $feedback = array('status' => 0, 'msg' => "Informe a Conta", 'title' => "Algo está errado");
    echo json_encode($feedback);
    exit;
}

$account_type = addslashes($_POST['tipo-conta']);
if (!preg_match("/^[1-2]*$/", $account_type)) {
    $feedback = array('status' => 0, 'msg' => 'Informe o tipo de conta corretamente.', 'title' => "Algo está errado");
    echo json_encode($feedback);
    exit;
} else if (empty($account_type)) {
    $feedback = array('status' => 0, 'msg' => "Informe o tipo de conta.", 'title' => "Algo está errado");
    echo json_encode($feedback);
    exit;
}


    $verify_added_accs         = $conn->prepare('SELECT meta_value FROM users_meta WHERE user__id = :user__id AND meta_key = "added_accounts"');
    $verify_added_accs->execute(array('user__id' => $user__id));

    if ($verify_added_accs->rowCount() == 1){
        
        $added_accs = $verify_added_accs->fetch();
        $added_accs = $added_accs['meta_value'];
        $thi_acc_id = $added_accs + 1;

        $set_added_accs = $conn->prepare('UPDATE users_meta SET meta_value = :meta_value WHERE user__id = :user__id AND meta_key = "added_accounts"');
        $set_added_accs->bindParam(':meta_value', $thi_acc_id, PDO::PARAM_INT);
        $set_added_accs->bindParam(':user__id', $user__id, PDO::PARAM_INT);

    } else {
        
        $thi_acc_id = $added_accs = 1;

        $set_added_accs = $conn->prepare('INSERT INTO users_meta (meta_id, user__id, meta_key, meta_value) VALUES ("0", :user__id, "added_accounts", :meta_value)');
        $set_added_accs->bindParam(':meta_value', $thi_acc_id, PDO::PARAM_INT);
        $set_added_accs->bindParam(':user__id', $user__id, PDO::PARAM_INT);

    }
    
    $meta_key_default = "ACC_U" . $user__id . "-A" . $thi_acc_id . "_";

    $insert_bank            = $conn->prepare('INSERT INTO users_meta (meta_id, user__id, meta_key, meta_value) VALUES (:meta_id, :user__id, :meta_key, :meta_value)');
    $insert_agency          = $conn->prepare('INSERT INTO users_meta (meta_id, user__id, meta_key, meta_value) VALUES (:meta_id, :user__id, :meta_key, :meta_value)');
    $insert_account         = $conn->prepare('INSERT INTO users_meta (meta_id, user__id, meta_key, meta_value) VALUES (:meta_id, :user__id, :meta_key, :meta_value)');
    $insert_account_type    = $conn->prepare('INSERT INTO users_meta (meta_id, user__id, meta_key, meta_value) VALUES (:meta_id, :user__id, :meta_key, :meta_value)');

    $set_added_accs->execute();
    $insert_bank->execute(array('meta_id' => 0, 'user__id' => $user__id, 'meta_key' => $meta_key_default . "BANK", 'meta_value' => $bank));
    $insert_agency->execute(array('meta_id' => 0, 'user__id' => $user__id, 'meta_key' => $meta_key_default . "AGENCY", 'meta_value' => $agency));
    $insert_account->execute(array('meta_id' => 0, 'user__id' => $user__id, 'meta_key' => $meta_key_default . "ACCOUNT", 'meta_value' => $account));
    $insert_account_type->execute(array('meta_id' => 0, 'user__id' => $user__id, 'meta_key' => $meta_key_default . "TYPE", 'meta_value' => $account_type));

    $feedback = array('status' => 1, 'msg' => "Você cadastrou uma nova conta!", 'title' => "Feito!");
    echo json_encode($feedback);
    exit;

} else if ($_POST['action'] == 'add-pix-account'){

}

else {
	$feedback = array('status' => 0, 'msg' => 'Algo está errado! Atualize a página e tente novamente.'); 
	echo json_encode($feedback);
	exit;
}