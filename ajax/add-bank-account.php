<?php

require dirname(__FILE__) . "/../includes/config.php";
require(dirname(__FILE__) . '/../includes/classes/RandomStrGenerator.php');
require(dirname(__FILE__) . '/../includes/classes/ClassValidCPFCNPJ.php');
require(dirname(__FILE__) . '/../includes/classes/isEmail.php');

session_name(SESSION_NAME);
session_start();

# Verifica o envio do form via POST
if (!(isset($_POST['action']))) {
    exit;
}

# Variáveis internas
$user__id = $_SESSION['UserID'];
$type_key = $_POST['tipo-chave'];
$document = addslashes($_POST['chave-pix']);
$date_request = date("Y-m-d H:i:s");

# Recebe a trata os inputs
$bank = addslashes($_POST['banco']);
if (!preg_match("/^[a-zA-Z-À-ú0-9\/£$%^&*()}{:\'#~<>,;!@\|\-=\-_+\-¬\\' ]*$/", $bank)) {
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
if ($_POST['action'] == 'add-bank-account') {

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

    $digit = addslashes($_POST['digito']);
    if (!preg_match("/^[0-9]*$/", $digit)) {
        $feedback = array('status' => 0, 'msg' => 'Confira se a Conta informada está correta.', 'title' => "Algo está errado");
        echo json_encode($feedback);
        exit;
    } else if (empty($agency)) {
        $feedback = array('status' => 0, 'msg' => "Informe a Conta", 'title' => "Algo está errado");
        echo json_encode($feedback);
        exit;
    }

    $account = $account . "-" . $digit;

    $type = addslashes($_POST['tipo-conta']);
    if (!preg_match("/^[1-2]*$/", $type)) {
        $feedback = array('status' => 0, 'msg' => 'Informe o tipo de conta corretamente.', 'title' => "Algo está errado");
        echo json_encode($feedback);
        exit;
    } else if (empty($type)) {
        $feedback = array('status' => 0, 'msg' => "Informe o tipo de conta.", 'title' => "Algo está errado");
        echo json_encode($feedback);
        exit;
    }

    if (isset($_POST['tipo-chave']) && !(empty($_POST['tipo-chave']))) {
        $pix_type = addslashes($_POST['tipo-chave']);
        if (!preg_match("/^[1-9]*$/", $pix_type)) {
            $feedback = array('status' => 0, 'msg' => 'Informe o tipo de chave PIX corretamente', 'title' => "Algo está errado");
            echo json_encode($feedback);
            exit;
        }

        $pix_key =  preg_replace('/[^0-9]/', '', $document);
        if (!preg_match("/^[a-zA-Z0-9-z@.\/]*$/", $pix_key)) {
            $feedback = array('status' => 0, 'msg' => 'Informe a chave PIX corretamente', 'title' => "Algo está errado");
            echo json_encode($feedback);
            exit;
        } else if ($_POST['tipo-chave'] == 1 && empty($_POST['chave-pix'])) {
            $pix_type = null;
            $pix_key = null;
        }
    } else {
        $pix_type = null;
        $pix_key = null;
    }

    $document = addslashes($_POST['chave-pix']);
    $cpf_cnpj = new ValidaCPFCNPJ($document);

    if ($type_key == "1") {
        $phone = preg_replace('/[^0-9]/', '', $document);
        if (strlen($phone) == 13) {
            $add_new_acc = $conn->prepare('INSERT INTO bank_account_list (account_id, account_user_id, account_bank, account_agency, account_number, account_type, account_pix_type, account_pix_key, account_date_request) VALUES (:account_id, :account_user_id, :account_bank, :account_agency, :account_number, :account_type, :account_pix_type, :account_pix_key, :account_date_request)');
            $add_new_acc->execute(array('account_id' => 0, 'account_user_id' => $user__id, 'account_bank' => $bank, 'account_agency' => $agency, 'account_number' => $account, 'account_type' => $type, 'account_pix_type' => $pix_type, 'account_pix_key' => $pix_key, 'account_date_request' => $date_request));

            $feedback = array('status' => 1, 'msg' => "Você cadastrou uma nova conta!", 'title' => "Feito!");
            echo json_encode($feedback);
            return;
        }
        $feedback = array('status' => 0, 'msg' => "Telefone inválido!", 'title' => "Erro!");
        echo json_encode($feedback);
        exit;
    } else if ($type_key == "2") {

        $cpf = preg_replace('/[^0-9]/', '', $document);
        //var_dump($cpf);
        $cpf_cnpj = new ValidaCPFCNPJ($cpf);
        if ($cpf_cnpj->valida()) {
            $add_new_acc = $conn->prepare('INSERT INTO bank_account_list (account_id, account_user_id, account_bank, account_agency, account_number, account_type, account_pix_type, account_pix_key, account_date_request) VALUES (:account_id, :account_user_id, :account_bank, :account_agency, :account_number, :account_type, :account_pix_type, :account_pix_key, :account_date_request)');
            $add_new_acc->execute(array('account_id' => 0, 'account_user_id' => $user__id, 'account_bank' => $bank, 'account_agency' => $agency, 'account_number' => $account, 'account_type' => $type, 'account_pix_type' => $pix_type, 'account_pix_key' => $pix_key, 'account_date_request' => $date_request));

            $feedback = array('status' => 1, 'msg' => "Você cadastrou uma nova conta!", 'title' => "Feito!");
            echo json_encode($feedback);
            return;
        }

        $feedback = array('status' => 0, 'msg' => "Cpf inválido!", 'title' => "Erro!");
        echo json_encode($feedback);
        exit;
    } else if ($type_key == "3") {

        $cnpj = preg_replace('/[^0-9]/', '', $document);
        // var_dump($cnpj);exit;
        $cpf_cnpj = new ValidaCPFCNPJ($cnpj);
        if ($cpf_cnpj->valida()) {
            $add_new_acc = $conn->prepare('INSERT INTO bank_account_list (account_id, account_user_id, account_bank, account_agency, account_number, account_type, account_pix_type, account_pix_key, account_date_request) VALUES (:account_id, :account_user_id, :account_bank, :account_agency, :account_number, :account_type, :account_pix_type, :account_pix_key, :account_date_request)');
            $add_new_acc->execute(array('account_id' => 0, 'account_user_id' => $user__id, 'account_bank' => $bank, 'account_agency' => $agency, 'account_number' => $account, 'account_type' => $type, 'account_pix_type' => $pix_type, 'account_pix_key' => $pix_key, 'account_date_request' => $date_request));

            $feedback = array('status' => 1, 'msg' => "Você cadastrou uma nova conta!", 'title' => "Feito!");
            echo json_encode($feedback);
            return;
        }

        $feedback = array('status' => 0, 'msg' => "Cnpj inválido!", 'title' => "Erro!");
        echo json_encode($feedback);
        exit;
    } else if ($type_key == "4") {

        if (is_email($document)) {
            $pix_key = $document;
            $add_new_acc = $conn->prepare('INSERT INTO bank_account_list (account_id, account_user_id, account_bank, account_agency, account_number, account_type, account_pix_type, account_pix_key, account_date_request) VALUES (:account_id, :account_user_id, :account_bank, :account_agency, :account_number, :account_type, :account_pix_type, :account_pix_key, :account_date_request)');
            $add_new_acc->execute(array('account_id' => 0, 'account_user_id' => $user__id, 'account_bank' => $bank, 'account_agency' => $agency, 'account_number' => $account, 'account_type' => $type, 'account_pix_type' => $pix_type, 'account_pix_key' => $pix_key, 'account_date_request' => $date_request));

            $feedback = array('status' => 1, 'msg' => "Você cadastrou uma nova conta!", 'title' => "Feito!");
            echo json_encode($feedback);
            return;
        }
        $feedback = array('status' => 0, 'msg' => "Email inválido!", 'title' => "Erro!");
        echo json_encode($feedback);
        exit;
    }

    $pix_key = $document;
    $add_new_acc = $conn->prepare('INSERT INTO bank_account_list (account_id, account_user_id, account_bank, account_agency, account_number, account_type, account_pix_type, account_pix_key, account_date_request) VALUES (:account_id, :account_user_id, :account_bank, :account_agency, :account_number, :account_type, :account_pix_type, :account_pix_key, :account_date_request)');
    $add_new_acc->execute(array('account_id' => 0, 'account_user_id' => $user__id, 'account_bank' => $bank, 'account_agency' => $agency, 'account_number' => $account, 'account_type' => $type, 'account_pix_type' => $pix_type, 'account_pix_key' => $pix_key, 'account_date_request' => $date_request));

    $feedback = array('status' => 1, 'msg' => "Você cadastrou uma nova conta!", 'title' => "Feito!");
    echo json_encode($feedback);
    exit;
    
} else if ($_POST['action'] == 'update-bank-account') {

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

    $digit = addslashes($_POST['digito']);
    if (!preg_match("/^[0-9]*$/", $digit)) {
        $feedback = array('status' => 0, 'msg' => 'Confira se a Conta informada está correta.', 'title' => "Algo está errado");
        echo json_encode($feedback);
        exit;
    } else if (empty($agency)) {
        $feedback = array('status' => 0, 'msg' => "Informe a Conta", 'title' => "Algo está errado");
        echo json_encode($feedback);
        exit;
    }

    $account = $account . "-" . $digit;

    $account_id = $_SESSION['reprovedAccountID'];

    $type = addslashes($_POST['tipo-conta']);
    if (!preg_match("/^[1-2]*$/", $type)) {
        $feedback = array('status' => 0, 'msg' => 'Informe o tipo de conta corretamente.', 'title' => "Algo está errado");
        echo json_encode($feedback);
        exit;
    } else if (empty($type)) {
        $feedback = array('status' => 0, 'msg' => "Informe o tipo de conta.", 'title' => "Algo está errado");
        echo json_encode($feedback);
        exit;
    }

    if (isset($_POST['tipo-chave']) && !(empty($_POST['tipo-chave']))) {
        $pix_type = addslashes($_POST['tipo-chave']);
        if (!preg_match("/^[1-9]*$/", $pix_type)) {
            $feedback = array('status' => 0, 'msg' => 'Informe o tipo de chave PIX corretamente', 'title' => "Algo está errado");
            echo json_encode($feedback);
            exit;
        }

        $pix_key =  preg_replace('/[^0-9]/', '', $document);
        if (!preg_match("/^[a-zA-Z0-9-z@.\/]*$/", $pix_key)) {
            $feedback = array('status' => 0, 'msg' => 'Informe a chave PIX corretamente', 'title' => "Algo está errado");
            echo json_encode($feedback);
            exit;
        } else if ($_POST['tipo-chave'] == 1 && empty($_POST['chave-pix'])) {
            $pix_type = null;
            $pix_key = null;
        }
    } else {
        $pix_type = null;
        $pix_key = null;
    }

    $update_account = $conn->prepare('UPDATE bank_account_list SET account_bank = :account_bank, account_agency = :account_agency, account_number = :account_number, account_type = :account_type, account_pix_type = :account_pix_type, account_pix_key = :account_pix_key, disapproval_justification = :disapproval_justification, account_status = :account_status WHERE account_id = :account_id');
    $update_account->execute(array('account_bank' => $bank, 'account_agency' => $agency, 'account_number' => $account, 'account_type' => $type, 'account_pix_type' => $pix_type, 'account_pix_key' => $pix_key, 'disapproval_justification' => NULL, 'account_status' => 1, 'account_id' => $account_id));

    $feedback = array('status' => 1, 'msg' => "Uma nova revisão será feita em breve!", 'title' => "Conta Atualizada!");
    echo json_encode($feedback);
    exit;
} else {
    $feedback = array('status' => 0, 'msg' => 'Algo está errado! Atualize a página e tente novamente.');
    echo json_encode($feedback);
    exit;
}
