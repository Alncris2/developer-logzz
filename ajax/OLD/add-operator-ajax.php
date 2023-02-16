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

  $senha = sha1($_POST["senha-operador"]);
  $conf_senha = sha1($_POST["conf-senha-operador"]);

  $operation_id = addslashes($_POST["operacao-local"]);

  $debito_tax = addslashes($_POST["taxa-debito"]);
  $credito_tax = addslashes($_POST["taxa-credito"]);
  $dinheiro_tax = addslashes($_POST["taxa-dinheiro"]);

  $entr_completa = addslashes($_POST["taxa-entrega-completa"]);
  $entr_frustrada = addslashes($_POST["taxa-entrega-frustrada"]);
  
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

	$stmt = $conn->prepare('INSERT INTO users (user__id, full_name, user_password, email, user_avatar, active, created_at, updated_at, user_plan, user_plan_tax, user_external_gateway_tax, user_plan_shipping_tax, user_payment_term, user_code) VALUES (:user__id, :full_name, :user_password, :email, :user_avatar, :active, :created_at, :updated_at, :user_plan, :user_plan_tax, :user_external_gateway_tax,:user_plan_shipping_tax, :user_payment_term, :user_code)');
  $get_last_id = $conn->prepare('SELECT user__id FROM users ORDER BY user__id DESC LIMIT 1');

	try {

		$stmt->execute(array('user__id' => $user__id, 'full_name' => $nome_operador, 'user_password' => $senha, 'email' => $email, 'user_avatar' => "profile.jpg", 'active' => 1, 'created_at' => $created_at, 'updated_at' => $updated_at, 'user_plan' => 6, 'user_plan_tax' => 0, 'user_external_gateway_tax' => 0,'user_plan_shipping_tax' => 0, 'user_code' => $user_code, 'user_payment_term' => 0));
    $get_last_id->execute();

		while($row = $get_last_id->fetch()) {
			$user__id = $row['user__id'];
		}

    $createOperator = $conn->prepare("INSERT INTO logistic_operator (local_operation, user_id, credito_tax, dinheiro_tax, debito_tax, entr_completa_tax, entr_frustrada_tax) VALUES (:operation, :user_id, :credito_tax, :dinheiro_tax, :debito_tax, :entr_completa, :entr_frustrada)");
    $createOperator->execute(array("operation" => $operation_id, "user_id" => $user__id, "credito_tax" => $credito_tax, "debito_tax" => $debito_tax, "dinheiro_tax" => $dinheiro_tax, "entr_completa" => $entr_completa, "entr_frustrada" => $entr_frustrada));

		$feedback = array('status' => 1, 'msg' => 'Operador Cadastrado!', 'url' => $url);
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
