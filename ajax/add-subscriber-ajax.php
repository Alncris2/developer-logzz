<?php

require_once(dirname(__FILE__) . '/../includes/config.php');
require(dirname(__FILE__) . '/../includes/classes/RandomStrGenerator.php');
session_name(SESSION_NAME);
session_start();


if ($_POST['action'] == 'new-subscriber'){

   
	
	$user__id 					= 0;
	$full_name 					= addslashes($_POST['nome-assinante']);
	$user_phone 				= addslashes($_POST['phone-assinante']);
	$user_password 				= sha1($_POST['senha-assinante']);
    $user_password_to_mailer    = $_POST['senha-assinante'];
    $send_mail                  = $_POST['enviar-email'];

		if (empty($user_password)) {
			$feedback = array('status' => 0, 'msg' => "A senha não pode ser vazia.");
			echo json_encode($feedback);
			exit;
		}
    
    # Verfica se já há um usuário cadastrado com esse email.
	$email 						= addslashes($_POST['email-assinante']);

    
    
    $search_email = $conn->prepare('SELECT COUNT(email) FROM users WHERE email = :email');
    $search_email->execute(array('email' => $email));
    
    $email_found = $search_email->fetch();

    if ($email_found[0] > 0) {
        $feedback = array('status' => 0, 'msg' => 'Já existe uma conta associada a esse email.');
		echo json_encode($feedback);
		exit;
    }
   

	$user_avatar				= 'profile.jpg';
	$user_level = $active 		= 1;
	$created_at = $updated_at	= date("Y-m-d H:i:s");
    $user_plan 	= addslashes($_POST['plano-assinante']);
	$user_plan_commission_recruitment	= addslashes($_POST['valor-comissao-recrutamento']);

	require(dirname(__FILE__) . '/../includes/plans-list.php');

	# Busca os valores dos planos...
	if ($user_plan == 4) {
          
		$custom_conditions = 1;
		$user_plan_tax 				        = addslashes($_POST['taxa-assinante']);
		$user_plan_shipping_tax 	        = floatval(addslashes($_POST['valor-entrega-assinante']));
		$user_external_gateway_tax          = addslashes($_POST['taxa-gateway-assinante']);
		$user_payment_term 			        = addslashes($_POST['prazo-assinante']);
		$new_plan_price = 0;
	} else {
        $user_plan_shipping_tax  = floatval(addslashes($_POST['valor-entrega-assinante']));
        $user_payment_term 			        = addslashes($_POST['prazo-assinante']);
        $user_plan_tax 			 = addslashes($_POST['taxa-assinante']);
		$custom_conditions = 0;
        $user_external_gateway_tax          = addslashes($_POST['taxa-gateway-assinante']);
        $new_plan_price            = number_format(floatval( $_POST['valor-recorrencia-mensal']), 2, '.', '');
      
	}
   // var_dump($user_plan_tax);
   

    # Geração do user_CODE único p/ o usuário
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
	$subscription_code = "plan" . $user_code;

	$create_new_user = $conn->prepare('INSERT INTO users (user__id, full_name, user_password, email, user_phone, user_avatar, active, created_at, updated_at, user_code) VALUES (:user__id, :full_name, :user_password, :email, :user_phone, :user_avatar, :active, :created_at, :updated_at, :user_code)');
	$create_new_user_plan = $conn->prepare('INSERT INTO subscriptions (subscription_id, user__id, subscription_code, user_plan, user_plan_tax, user_external_gateway_tax, user_plan_shipping_tax, user_plan_commission_recruitment, user_payment_term, custom_conditions, subscription_start, subscription_renewal, plan_price, subscription_end) VALUES (:subscription_id, :user__id, :subscription_code, :user_plan, :user_plan_tax, 0, :user_plan_shipping_tax, :user_plan_commission_recruitment, :user_payment_term, :custom_conditions, :subscription_start, :subscription_renewal, :plan_price, :subscription_end)');
	$get_last_id = $conn->prepare('SELECT user__id FROM users ORDER BY user__id DESC LIMIT 1');
	
    // CRIAR CHECKOUT PADRÃO PARA ESSA CONTA DE USUÁRIO
	$default_checkout = $conn->prepare("INSERT INTO custom_checkout (user__id, name_checkout, support_active, support_whatsapp, support_email, counter_active, isActive) VALUES (:user__id, :name_checkout, :support_active, :support_whatsapp, :support_email, :counter_active, :isActive)");

	try {
		$create_new_user->execute(array('user__id' => $user__id, 'full_name' => $full_name, 'user_password' => $user_password, 'email' => $email, 'user_phone' => $user_phone, 'user_avatar' => $user_avatar, 'active' => $active, 'created_at' => $created_at, 'updated_at' => $updated_at, 'user_code' => $user_code));
		
		$get_last_id->execute();
		$row = $get_last_id->fetch();
		$user__id = $row['user__id'];

		$create_new_user_plan->execute(array('subscription_id' => $user__id, 'user__id' => $user__id, 'subscription_code' => $subscription_code, 'user_plan' => $user_plan, 'user_plan_tax' => $user_plan_tax, 'user_plan_shipping_tax' => $user_plan_shipping_tax, 'user_plan_commission_recruitment' => $user_plan_commission_recruitment, 'user_payment_term' => $user_payment_term, 'custom_conditions' => $custom_conditions, 'subscription_start' => $created_at, 'subscription_renewal' => $created_at, 'plan_price' => $new_plan_price, 'subscription_end' => $created_at));
        $default_checkout->execute(['user__id' => $row['user__id'],'name_checkout' => 'CHECKOUT_PADRÃO','support_active' => 0,'support_whatsapp' => "", 'support_email' => "",'counter_active' => 0,'isActive' => 1]);
        
        if ($send_mail = 1){
            $_SESSION['LastSubscriberPassword'] = $user_password_to_mailer;
            $_SESSION['LastSubscriberID'] = $user__id;
            $url = SERVER_URI . "/sendmail/first_access/" . $user__id;
        }

		$feedback = array('status' => 1, 'msg' => 'Usuário Cadastrado!', 'url' => $url);
		echo json_encode($feedback);
		exit;
		
      } catch(PDOException $e) {
        $error = 'ERROR: ' . $e->getMessage();
		$feedback = array('status' => 0, 'msg' => $error);
		echo json_encode($feedback);
		exit;
	}

	//echo json_encode($feedback);

}

else if ($_POST['action'] == 'update-subscriber'){

  // var_dump($_POST['valor-recorrencia-mensal']);
  // exit;

	$user_code                  = addslashes($_POST['user']);
	$full_name 					= addslashes($_POST['nome-assinante']);
    $user_plan 				    = addslashes($_POST['plano-assinante']);
	$user_plan_tax 				= addslashes($_POST['taxa-assinante']);
	$user_payment_term 			= addslashes($_POST['prazo-assinante']);
    $user_external_gateway_tax  = 0; //addslashes($_POST['taxa-gateway-assinante']);
    $user_plan_shipping_tax 	= number_format(floatval(str_replace(",",".",$_POST['valor-entrega-assinante'])), 2, '.', '');
    $user_plan_price            = number_format(floatval( $_POST['valor-recorrencia-mensal']), 2, '.', '');
	$user_plan_commission_recruitment	=  number_format(floatval( $_POST['valor-comissao-recrutamento']), 2, '.', '');
   // var_dump();
  //  print_r($user_plan_shipping_tax);

	$get_user_id = $conn->prepare('SELECT user__id FROM users WHERE user_code = :user_code');
	$get_user_id->execute(array('user_code' => $user_code));
	$row = $get_user_id->fetch();
	$user__id = $row['user__id'];

	$update_user = $conn->prepare('UPDATE users SET full_name = :full_name WHERE user_code = :user_code');
	$update_subscription = $conn->prepare('UPDATE subscriptions SET user_plan = :user_plan, user_plan_tax = :user_plan_tax, user_plan_shipping_tax = :user_plan_shipping_tax, user_plan_commission_recruitment = :user_plan_commission_recruitment, user_payment_term = :user_payment_term, user_external_gateway_tax = :user_external_gateway_tax, plan_price = :plan_price WHERE user__id = :user__id');

    $update_sales_end_product = $conn->prepare('UPDATE sales , products  SET sales.product_shipping_tax = :product_shipping_tax , products.product_shipping_tax = :product_shipping_tax WHERE user__id = :user__id AND sales.product_id = products.product_id');
 // $update_sales_end_product = $conn->prepare('UPDATE sales , products  SET sales.product_shipping_tax = :product_shipping_tax , sales.sale_tax = :sale_tax , products.product_shipping_tax = :product_shipping_tax WHERE user__id = :user__id AND sales.product_id = products.product_id');
   

	try {
       
		$update_user->execute(array('full_name' => $full_name, 'user_code' => $user_code));
		$update_subscription->execute(array('user_plan' => $user_plan, 'user_plan_tax' => $user_plan_tax, 'user_plan_shipping_tax' => $user_plan_shipping_tax, 'user_plan_commission_recruitment' => $user_plan_commission_recruitment, 'user_payment_term' => $user_payment_term, 'user_external_gateway_tax' => $user_external_gateway_tax, 'user__id' => $user__id, 'plan_price' => $user_plan_price));
        $update_sales_end_product->execute(array('product_shipping_tax' => $user_plan_shipping_tax, 'user__id' => $user__id));

		$feedback = array('status' => 1);
		echo json_encode($feedback);
		exit;

	} catch(PDOException $e) {
		$error = 'ERROR: ' . $e->getMessage();
		$feedback = array('status' => 0, 'msg' => $error);
		echo json_encode($feedback);
		exit;
	}

} 


else if ($_POST['action'] == 'update-pay-infos'){

	$user__id 					= $_SESSION['UserID'];
	$user_plan 					= addslashes($_POST['plano-assinante']);
	$company_name				= addslashes($_POST['assinante-nome-completo']);
	$company_doc				= addslashes($_POST['assinante-documento']);
	$company_bank				= addslashes($_POST['assinante-banco']);
	$company_agency				= addslashes($_POST['assinante-agencia']);
	$company_account			= addslashes($_POST['assinante-conta']);
	$company_account_type		= addslashes($_POST['assinante-tipo-conta']);
	$company_type				= addslashes($_POST['tipo-de-pessoa']);
	$company_pix_key			= addslashes($_POST['chave-pix']);

	$stmt = $conn->prepare('UPDATE users SET user_plan = :user_plan, company_name = :company_name, company_doc = :company_doc, company_bank = :company_bank,company_agency= :company_agency, company_account = :company_account, company_account_type = :company_account_type, company_type = :company_type, company_pix_key = :company_pix_key WHERE user__id = :user__id');

	try {

		$stmt->execute(array('user_plan' => $user_plan, 'company_name' => $company_name, 'company_doc' => $company_doc, 'company_bank' => $company_bank, 'company_agency' => $company_agency, 'company_account' => $company_account, 'company_account_type' => $company_account_type, 'company_type' => $company_type, 'user__id' => $user__id, 'company_pix_key' => $company_pix_key));

		$feedback = array('status' => 1);
		echo json_encode($feedback);
		exit;

	} catch(PDOException $e) {
		$error = 'ERROR: ' . $e->getMessage();
		$feedback = array('status' => 0, 'msg' => $error);
		echo json_encode($feedback);
		exit;
	}

} 


else {
	$feedback = array('status' => 0, 'msg' => 'NO ACTION!');
	echo json_encode($feedback);
	exit;
}

?>