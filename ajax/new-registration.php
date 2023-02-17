<?php

require_once dirname(__FILE__) . "/../includes/config.php";
require(dirname(__FILE__) . '/../includes/classes/RandomStrGenerator.php');
require(dirname(__FILE__) . '/../includes/classes/ClassValidCPFCNPJ.php');
require(dirname(__FILE__) . "/../RequestAtendezap.php");
session_name(SESSION_NAME);
session_start();


if (isset($_POST['action']) && $_POST['action'] == 'new-registration') {

    $full_name = addslashes($_POST['nome-assinante']);
    if (!preg_match("/^[a-zA-Z-À-ú' ]*$/", $full_name)) {
        $feedback = array('status' => 0, 'msg' => 'Seu nome parece incorreto...', 'title' => "Confira seus dados");
        echo json_encode($feedback);
        exit;
    }

    $email = addslashes($_POST['email-assinante']);
    if (!(filter_var($email, FILTER_VALIDATE_EMAIL))) {
        $feedback = array('status' => 0, 'msg' => 'Seu número de email parece incorreto...', 'title' => "Confira seus dados");
        echo json_encode($feedback);
        exit;
    }
    $verify_unique_email = $conn->prepare('SELECT * FROM users WHERE email = :email');
    $verify_unique_email->execute(array('email' => $email));
    if (!($verify_unique_email->rowCount() == 0)) {
        $feedback = array('status' => 0, 'msg' => 'Acesse sua conta pela tela de login', 'title' => "Cadastro já existe");
        echo json_encode($feedback);
        exit;
    }

    $whatsapp = addslashes($_POST['whatsapp-assinante']);
    if (!preg_match("/^[(0-9-) ]*$/", $whatsapp)) {
        $feedback = array('status' => 0, 'msg' => 'Seu número de whatsapp parece incorreto...', 'title' => "Confira seus dados");
        echo json_encode($feedback);
        exit;
    }
    $verify_unique_user_phone = $conn->prepare('SELECT * FROM users WHERE user_phone = :user_phone');
    $verify_unique_user_phone->execute(array('user_phone' => $whatsapp));
    if (!($verify_unique_user_phone->rowCount() == 0)) {
        $feedback = array('status' => 0, 'msg' => 'Acesse sua conta pela tela de login', 'title' => "Cadastro já existe");
        echo json_encode($feedback);
        exit;
    }

    $razao_social = addslashes($_POST['razao-social']);
    if (!preg_match("/^[a-zA-Z-À-ú' ]*$/", $razao_social)) {
        $feedback = array('status' => 0, 'msg' => 'O campo Razão Social parece incorreto...', 'title' => "Confira seus dados");
        echo json_encode($feedback);
        exit;
    }

    $document = addslashes($_POST['documento']);
    $cpf_cnpj = new ValidaCPFCNPJ($document);
    // Verifica se o CPF ou CNPJ é válido
    if ($cpf_cnpj->valida()) {
        $verify_unique_company_doc = $conn->prepare('SELECT * FROM users WHERE company_doc = :company_doc');
        $verify_unique_company_doc->execute(array('company_doc' => $document));
        if (!($verify_unique_company_doc->rowCount() == 0)) {
            $feedback = array('status' => 0, 'msg' => 'Acesse sua conta pela tela de login', 'title' => "Cadastro já existe");
            echo json_encode($feedback);
            exit;
        }
    } else {
        $feedback = array('status' => 0, 'msg' => 'O dados do documento parece incorreto...', 'title' => "Confira seus dados");
        echo json_encode($feedback);
        exit;
    }
    $verify_unique_company_doc = $conn->prepare('SELECT * FROM users WHERE company_doc = :company_doc');
    $verify_unique_company_doc->execute(array('company_doc' => $document));
    if (!($verify_unique_company_doc->rowCount() == 0)) {
        $feedback = array('status' => 0, 'msg' => 'Acesse sua conta pela tela de login', 'title' => "Cadastro já existe");
        echo json_encode($feedback);
        exit;
    }

    $company_type = addslashes($_POST['pessoa']);
    if (!preg_match("/^[a-z]*$/", $company_type)) {
        $feedback = array('status' => 0, 'msg' => 'Atualize a Página e tente novamente', 'title' => "Erro Desconhecido");
        echo json_encode($feedback);
        exit;
    }



    if (isset($_POST['invite'])) {
        $data_host_registration = $conn->prepare('SELECT * FROM users WHERE user_code = :user_code');
        $data_host_registration->execute(array('user_code' => $_POST['invite']));
        $data_host = $data_host_registration->fetch();

        if ($data_host != null) {

            $approval = 0;
            if ($data_host['email'] == $email) {
                $approval = 1;
            } elseif ($data_host['user_phone'] == $whatsapp) {
                $approval = 1;
            } elseif ($data_host['user_phone'] == $whatsapp) {
                $approval = 1;
            } elseif ($data_host['company_doc'] == $_POST['documento']) {
                $approval = 1;
            }

            if ($approval == 1) {
                $feedback = array('status' => 0, 'msg' => 'Acesse sua conta pela tela de login', 'title' => "Cadastro inválido");
                echo json_encode($feedback);
                exit;
            }
        }
    }


    $company_doc = addslashes($_POST['documento']);

    # Cria uma senha para o usuário
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $random_string = str_shuffle($chars);
    $user_password = substr($random_string, 4, 10);
    $user_password_to_mailer = $user_password;
    $user_password = sha1($user_password);

    # Verfica se já há um usuário cadastrado com o email informado.
    $search_email = $conn->prepare('SELECT COUNT(email) FROM users WHERE email = :email');
    $search_email->execute(array('email' => $email));

    $email_found = $search_email->fetch();

    if ($email_found[0] > 0) {
        $feedback = array('status' => 0, 'msg' => 'Já existe uma conta associada ao seu email.', 'title' => "Confira seus dados");
        echo json_encode($feedback);
        exit;
    }

    # Geração do user_CODE único
    $user_code = new RandomStrGenerator();
    $user_code = $user_code->onlyLetters(6);

    $verify_unique_user_code = $conn->prepare('SELECT * FROM users WHERE user_code = :user_code');
    $verify_unique_user_code->execute(array('user_code' => $user_code));

    if (!($verify_unique_user_code->rowCount() == 0)) {
        do {
            $user_code = new RandomStrGenerator();
            $user_code = $user_code->onlyLetters(6);

            $verify_unique_user_code = $conn->prepare('SELECT * FROM users WHERE user_code = :user_code');
            $verify_unique_user_code->execute(array('user_code' => $user_code));
        } while ($stmt->rowCount() != 0);
    }

    $user_avatar                = 'profile.jpg';
    $user_plan                  = 4;
    $active                     = 1;
    $created_at = $updated_at    = date("Y-m-d H:i:s");
    $user_plan_tax              = '0.0797';
    $user_payment_term          = 30;
    $user_plan_shipping_tax     = 0;
    $user_plan_tax_padrao              = '0.0797';
    


    if ($company_type == 'fisica') {
        $company_name = $full_name;
    } else if ($company_type == 'juridica') {
        $company_name = $razao_social;
    }

    #$stmt = $conn->prepare('INSERT INTO users (user__id, full_name, user_password, email, user_phone, user_avatar, active, created_at, updated_at, user_plan, user_plan_tax, user_plan_shipping_tax, user_payment_term, company_doc, company_name, company_type, user_code) VALUES (:user__id, :full_name, :user_password, :email, :user_phone, :user_avatar, :active, :created_at, :updated_at, :user_plan, :user_plan_tax, :user_plan_shipping_tax, :user_payment_term, :company_doc, :company_name, :company_type, :user_code)');

    $create_new_user = $conn->prepare('INSERT INTO users (user__id, full_name, user_password, email, user_phone, user_avatar, active, company_doc, company_name, company_type, user_code) VALUES (:user__id, :full_name, :user_password, :email, :user_phone, :user_avatar, :active, :company_doc, :company_name, :company_type, :user_code)');

    $create_new_user_plan = $conn->prepare('INSERT INTO subscriptions (subscription_id, user__id, subscription_code, user_plan, user_plan_tax, user_external_gateway_tax, user_plan_shipping_tax, user_payment_term, custom_conditions, subscription_start, subscription_renewal, plan_price, subscription_end) VALUES (:subscription_id, :user__id, :subscription_code, :user_plan, :user_plan_tax, :user_external_gateway_tax, :user_plan_shipping_tax, :user_payment_term, :custom_conditions, :subscription_start, :subscription_renewal, :plan_price, :subscription_end)');
    $imprimi = $user_plan_tax;
    $get_last_id = $conn->prepare('SELECT user__id FROM users ORDER BY user__id DESC LIMIT 1');
    $subscription_code = "plan" . $user_code;
  
    $new_plan_id = 1;

    // CRIAR CHECKOUT PADRÃO PARA ESSA CONTA DE USUÁRIO
    $default_checkout = $conn->prepare("INSERT INTO custom_checkout (user__id, name_checkout, support_active, support_whatsapp, support_email, counter_active, isActive) VALUES (:user__id, :name_checkout, :support_active, :support_whatsapp, :support_email, :counter_active, :isActive)");

    require(dirname(__FILE__) . '/../includes/plans-list.php');

    try {
        $create_new_user->execute(array('user__id' => '0', 'full_name' => $full_name, 'user_password' => $user_password, 'email' => $email, 'user_phone' => $whatsapp, 'user_avatar' => $user_avatar, 'active' => $active, 'company_doc' => $company_doc, 'company_name' => $company_name, 'company_type' => $company_type, 'user_code' => $user_code));

        $get_last_id->execute();
        $row = $get_last_id->fetch();
        $user__id = $row['user__id'];

        //enviar mensagem whats
        $prepareSendMessage = $conn->prepare('SELECT full_name, user_phone, email, created_at FROM users WHERE user__id = :user__id');
        $prepareSendMessage->execute(["user__id" => $row['user__id']]);
        $dataSendMessageUser = $prepareSendMessage->fetch(PDO::FETCH_ASSOC);
        sendWebhookNovosUsuarios($dataSendMessageUser);
        
        $create_new_user_plan->execute(array(
            'subscription_id' => $user__id, 
            'user__id' => $user__id,
             'subscription_code' => $subscription_code, 
             'user_plan' => '1', 
             'user_plan_tax' => $user_plan_tax_padrao,
              'user_external_gateway_tax' => $user_external_gateway_tax,
               'user_plan_shipping_tax' => $user_plan_shipping_tax, 'user_payment_term' => $user_payment_term, 'custom_conditions' => '0', 'subscription_start' => $created_at, 'subscription_renewal' => $created_at, 'plan_price' => $new_plan_price, 'subscription_end' => $created_at));
              
        $default_checkout->execute(['user__id' => $row['user__id'], 'name_checkout' => 'CHECKOUT_PADRÃO', 'support_active' => 0, 'support_whatsapp' => "", 'support_email' => "", 'counter_active' => 0, 'isActive' => 1]);

        if (isset($data_host['user__id'])) {

            $create_new_recruitment_link = $conn->prepare('INSERT INTO recruitment (user__recruiter_id, recruited_id) VALUES (:user__recruiter_id, :recruited_id)');
            $create_new_recruitment_link->execute(['user__recruiter_id' => $data_host['user__id'], 'recruited_id' => $user__id]);

            // $create_new_recruitment_link = $conn->prepare('INSERT INTO recruitment (user__recruiter_id, recruited_id) VALUES (:user__recruiter_id, :recruiter__id)');
            // $create_new_recruitment_link->execute(array('user__recruiter_id' => $data_host['user__id'], 'recruited_id' => $user__id));
        }


        $_SESSION['LastSubscriberPassword'] = $user_password_to_mailer;
        $_SESSION['LastSubscriberID'] = $user__id;

        # Cria as variáveis de sessão do usuário
        $_SESSION['UserID'] = $user__id;
        $_SESSION['UserFullName'] = $full_name;
        $_SESSION['UserEmail'] = $email;
        $_SESSION['UserPlan'] = 1;
        $_SESSION['UserPlanTax'] = $user_plan_tax;
        $_SESSION['UserPlanExternalTax'] = $user_external_gateway_tax;
        $_SESSION['UserPlanString'] =  userPlanString(1);
        $_SESSION['UserPlanShipTax'] = $user_plan_shipping_tax;
        $_SESSION['UserPaymentTerm'] = $user_payment_term;

        $url = SERVER_URI . "/sendmail/first_access/" . $user__id;

        $feedback = array('status' => 1, 'url' => $url, 'taxa' =>  $imprimi);
        echo json_encode($feedback);
        exit;
    } catch (PDOException $e) {
        $error = 'ERROR: ' . $e->getMessage();
        $feedback = array('status' => 0, 'msg' => $error, 'title' => "Erro");
        echo json_encode($feedback);
        exit;
    }
} else {
    $feedback = array('status' => 0, 'msg' => 'NO ACTION!');
    echo json_encode($feedback);
    exit;
}
