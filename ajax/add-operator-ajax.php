<?php

require_once(dirname(__FILE__) . '/../includes/config.php');
require(dirname(__FILE__) . '/../includes/classes/RandomStrGenerator.php');
session_name(SESSION_NAME);
session_start();


if ($_POST['action'] == 'new-operator'){
  $user__id = 0;

  $nome_operador = addslashes($_POST["nome-operador"]);
  $email = addslashes($_POST["email-operador"]);
  $telefone = addslashes($_POST["telefone-operador"]);
  $doc = addslashes($_POST["doc-operador"]);

  $senha = sha1($_POST["senha-operador"]);
  $conf_senha = sha1($_POST["conf-senha-operador"]);

  $operation_id = addslashes($_POST["operacao-local"]);

  $debito_tax = addslashes($_POST["taxa-debito"]);
  $debito_tax = (float) $debito_tax/100;

  $credito_tax = json_decode($_POST["taxa-credito"]);

  $dinheiro_tax = addslashes($_POST["taxa-dinheiro"]);
  $dinheiro_tax = (float) $dinheiro_tax/100;

  $delivery_taxes = json_decode($_POST["taxas-entrega"]);
  
  if($senha != $conf_senha) {
		$feedback = array('status' => 0, 'msg' => "As senhas não coincidem");
		echo json_encode($feedback);
		exit;
  }

	$created_at = $updated_at	= date("Y-m-d H:i:s");

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

  // Cria o usuário operador na tabela users e também seu plano na tabela subscriptions
  $stmt = $conn->prepare('INSERT INTO users (user__id, full_name, user_phone,user_password, company_doc,email, user_avatar, active, created_at, updated_at, user_code, user_terms_accepted) VALUES (:user__id, :full_name, :user_phone, :user_password, :doc, :email, :user_avatar, :active, :created_at, :updated_at, :user_code, :user_terms_accepted)');
  $create_new_user_plan = $conn->prepare('INSERT INTO subscriptions (subscription_id, user__id, subscription_code, user_plan, user_plan_tax, user_external_gateway_tax, user_plan_shipping_tax, user_payment_term, custom_conditions, subscription_start, subscription_renewal, plan_price, subscription_end) VALUES (:subscription_id, :user__id, :subscription_code, :user_plan, :user_plan_tax, :user_external_gateway_tax, :user_plan_shipping_tax, :user_payment_term, :custom_conditions, :subscription_start, :subscription_renewal, :plan_price, :subscription_end)');

  $get_last_id = $conn->prepare('SELECT user__id FROM users ORDER BY user__id DESC LIMIT 1');

	try {

    $stmt->execute(array('user__id' => $user__id, 'full_name' => $nome_operador, 'user_password' => $senha, 'user_phone' => $telefone, 'doc' => $doc,'email' => $email, 'user_avatar' => "profile.jpg", 'active' => 1, 'created_at' => $created_at, 'updated_at' => $updated_at, 'user_code' => $user_code, 'user_terms_accepted' => "true"));
    $get_last_id->execute();

		while($row = $get_last_id->fetch()) {
			$user__id = $row['user__id'];
		}

    $subscription_code = "plan" . $user_code;
		$create_new_user_plan->execute(array('subscription_id' => $user__id, 'user__id' => $user__id, 'subscription_code' => $subscription_code, 'user_plan' => '6', 'user_plan_tax' => 0, 'user_external_gateway_tax' => 0, 'user_plan_shipping_tax' => 0, 'user_payment_term' => 0, 'custom_conditions' => '0', 'subscription_start' => $created_at, 'subscription_renewal' => $created_at, 'plan_price' => 0, 'subscription_end' => $created_at));


    // Cria o operador logístico adicionando-o na tabela logistic_operator junto a suas taxas
    $createOperator = $conn->prepare("INSERT INTO logistic_operator 
    (local_operation, user_id, dinheiro_tax, debito_tax, credito_tax_1x, credito_tax_2x, credito_tax_3x, credito_tax_4x, credito_tax_5x, credito_tax_6x, credito_tax_7x,
    credito_tax_8x, credito_tax_9x, credito_tax_10x, credito_tax_11x, credito_tax_12x) 
    VALUES (:operation, :user_id, :dinheiro_tax, :debito_tax, :cre_1x, :cre_2x, :cre_3x, 
    :cre_4x, :cre_5x, :cre_6x, :cre_7x, :cre_8x, :cre_9x, :cre_10x, :cre_11x, :cre_12x)");
    
    $createOperator->execute(array("operation" => $operation_id, "debito_tax" => str_replace(',', '.', $debito_tax), "user_id" => $user__id,
    "dinheiro_tax" => str_replace(',', '.', $dinheiro_tax), "cre_1x" => floatval(str_replace(',', '.', $credito_tax->{'1'})), "cre_2x" => floatval(str_replace(',', '.', $credito_tax->{'2'})), "cre_3x" => floatval(str_replace(',', '.', $credito_tax->{'3'})), "cre_4x" => floatval(str_replace(',', '.', $credito_tax->{'4'})), 
    "cre_5x" => floatval(str_replace(',', '.', $credito_tax->{'5'})), "cre_6x" => floatval(str_replace(',', '.', $credito_tax->{'6'})), "cre_7x" => floatval(str_replace(',', '.', $credito_tax->{'7'})), "cre_8x" => floatval(str_replace(',', '.', $credito_tax->{'8'})), 
    "cre_9x" => floatval(str_replace(',', '.', $credito_tax->{'9'})), "cre_10x" => floatval(str_replace(',', '.', $credito_tax->{'10'})), "cre_11x" => floatval(str_replace(',', '.', $credito_tax->{'11'})), "cre_12x" => floatval(str_replace(',', '.', $credito_tax->{'12'}))));

    $get_last_operator_id = $conn->prepare('SELECT operator_id FROM logistic_operator ORDER BY operator_id DESC LIMIT 1');
    $get_last_operator_id->execute();

    while($row = $get_last_operator_id->fetch()) {
      $operator_id = $row["operator_id"];
    }

    foreach ($delivery_taxes as $obj) {
      $add_delivery_taxes = $conn->prepare("INSERT INTO operations_delivery_taxes 
      (operator_id, operation_id, operation_locale, complete_delivery_tax, frustrated_delivery_tax)
      VALUES (:operator_id, :operation_id,:operation_locale, :complete_tax, :frustrated_tax)");

      $get_operation_locale = $conn->prepare("SELECT id FROM operations_locales WHERE operation_id = :operation_id AND city = :city");
      $get_operation_locale->execute(array("operation_id" => $operation_id, "city" => $obj->{'city'}));
      
      while($row = $get_operation_locale->fetch()) {
        $operation_locale = $row["id"];
      }

      $add_delivery_taxes->execute(array("operator_id" => $operator_id, "operation_id" => $operation_id, "operation_locale" => $operation_locale, "complete_tax" => $obj->{'complete_tax'}, "frustrated_tax" => $obj->{'frustrated_tax'}));
    } 



		$feedback = array('status' => 1, 'msg' => 'Operador Cadastrado!');
		echo json_encode($feedback);
		exit;
    } catch(PDOException $e) {

    $error = 'ERROR: ' . $e->getMessage();
		$feedback = array('status' => 0, 'msg' => $error);
		echo json_encode($feedback);
		exit;
	}
}

else if ($_POST['action'] == 'update-operator'){
  $user_code = addslashes($_POST["cod-operador"]);
  $operator_id = addslashes($_POST["operator-id"]);
  
  $nome_operador = addslashes($_POST["nome-operador"]);
  $email = addslashes($_POST["email-operador"]);
  $telefone = addslashes($_POST["telefone-operador"]);
  $doc = addslashes($_POST["doc-operador"]);

  $operation_id = addslashes($_POST["operacao-local"]);

  $debito_tax = addslashes($_POST["taxa-debito"]);
  $debito_tax = str_replace("%","",$debito_tax);

  $credito_tax = json_decode($_POST["taxa-credito"]);

  $dinheiro_tax = addslashes($_POST["taxa-dinheiro"]);
  $dinheiro_tax = str_replace("%","",$dinheiro_tax);

  $delivery_taxes = json_decode($_POST["taxas-entrega"]);
  $updated_at	= date("Y-m-d H:i:s");

  // Atualiza o usuário operador na tabela users
  $stmt = $conn->prepare('UPDATE users SET full_name=:full_name, email=:email, updated_at=:updated_at, company_doc=:doc, user_phone=:user_phone WHERE user_code=:user_code');

	try {

    $stmt->execute(array('user_code' => $user_code, 'full_name' => $nome_operador, 'email' => $email, 'updated_at' => $updated_at, 'doc' => $doc,'user_phone' => $telefone));
    
    $get_user_id = $conn->prepare("SELECT user__id FROM users WHERE user_code = :code");
    $get_user_id->execute(array("code" => $user_code));

    // Cria o operador logístico adicionando-o na tabela logistic_operator junto a suas taxas
    $createOperator = $conn->prepare("UPDATE logistic_operator SET local_operation=:operation, debito_tax=:debito_tax, dinheiro_tax=:dinheiro_tax, credito_tax_1x=:cre_1x, credito_tax_2x=:cre_2x, credito_tax_3x=:cre_3x, 
    credito_tax_4x=:cre_4x, credito_tax_5x=:cre_5x, credito_tax_6x=:cre_6x, credito_tax_7x=:cre_7x, credito_tax_8x=:cre_8x, credito_tax_9x=:cre_9x, credito_tax_10x=:cre_10x, credito_tax_11x=:cre_11x, credito_tax_12x=:cre_12x 
    WHERE user_id=:user_id");
    
    $createOperator->execute(array("operation" => $operation_id, "debito_tax" => str_replace(',', '.', $debito_tax), 
    "dinheiro_tax" => str_replace(',', '.', $dinheiro_tax), "cre_1x" => floatval(str_replace(',', '.', $credito_tax->{'1'})), "cre_2x" => floatval(str_replace(',', '.', $credito_tax->{'2'})), "cre_3x" => floatval(str_replace(',', '.', $credito_tax->{'3'})), "cre_4x" => floatval(str_replace(',', '.', $credito_tax->{'4'})), 
    "cre_5x" => floatval(str_replace(',', '.', $credito_tax->{'5'})), "cre_6x" => floatval(str_replace(',', '.', $credito_tax->{'6'})), "cre_7x" => floatval(str_replace(',', '.', $credito_tax->{'7'})), "cre_8x" => floatval(str_replace(',', '.', $credito_tax->{'8'})), 
    "cre_9x" => floatval(str_replace(',', '.', $credito_tax->{'9'})), "cre_10x" => floatval(str_replace(',', '.', $credito_tax->{'10'})), "cre_11x" => floatval(str_replace(',', '.', $credito_tax->{'11'})), "cre_12x" => floatval(str_replace(',', '.', $credito_tax->{'12'})),
    "user_id" => $get_user_id->fetch()['user__id']));

    $reset_delivery_taxes = $conn->prepare('DELETE FROM operations_delivery_taxes WHERE operator_id=:operator_id');
    $reset_delivery_taxes->execute(array("operator_id" => $operator_id));
    foreach ($delivery_taxes as $obj) {

      $add_delivery_taxes = $conn->prepare("INSERT INTO operations_delivery_taxes 
      (operator_id, operation_id, operation_locale, complete_delivery_tax, frustrated_delivery_tax)
      VALUES (:operator_id, :operation_id,:operation_locale, :complete_tax, :frustrated_tax)");

      $get_operation_locale = $conn->prepare("SELECT id FROM operations_locales WHERE operation_id = :operation_id AND city = :city");
      $get_operation_locale->execute(array("operation_id" => $operation_id, "city" => $obj->{'city'}));
      
      while($row = $get_operation_locale->fetch()) {
        $operation_locale = $row["id"];
      }

      $add_delivery_taxes->execute(array("operator_id" => $operator_id, "operation_id" => $operation_id, "operation_locale" => $operation_locale, "complete_tax" => number_format($obj->{'complete_tax'},2,".",""), "frustrated_tax" =>  number_format($obj->{'frustrated_tax'},2,".","")));
    } 



		$feedback = array('status' => 1, 'msg' => 'Operador Atualizado!');
		echo json_encode($feedback);
		exit;
    } catch(PDOException $e) {

    $error = 'ERROR: ' . $e->getMessage();
		$feedback = array('status' => 0, 'msg' => $error);
		echo json_encode($feedback);
		exit;
	}
}
?>
