<?php

require dirname(__FILE__) . "/../includes/config.php";

session_name(SESSION_NAME);
session_start();

# Verifica o envio do form via POST
if (isset($_POST['action'])){

# Recebe a trata os inputs
$pass = addslashes($_POST['senha']);
$confirme_pass = addslashes($_POST['confirma-senha']);

if ($confirme_pass != $pass){
    $feedback = array('status' => 0, 'title' => "As senhas não conferem!", 'msg' => ''); 
	echo json_encode($feedback);
	exit;
} else {
    $confirme_pass = sha1($confirme_pass);
}

$user_code = addslashes($_POST['user']);

# Prepara a query para atualizar a privacidade da oferta.
$stmt = $conn->prepare('UPDATE users SET user_password = :user_password WHERE user_code = :user_code');

	try {
		$stmt->execute(array('user_password' => $confirme_pass, 'user_code' => $user_code));

		$feedback = array('status' => 1, 'title' => 'Senha do usuário Alterada!');

      } catch(PDOException $e) {
        $error = 'ERROR: ' . $e->getMessage();
		$feedback = array('status' => '0', 'msg' => $error);
      }


	echo json_encode($feedback);
}

else {
	$feedback = array('status' => 0, 'msg' => 'Algo está errado! Atualize a página e tente novamente.'); 
	echo json_encode($feedback);
	exit;
}

?>