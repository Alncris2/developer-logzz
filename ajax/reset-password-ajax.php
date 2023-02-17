<?php

    require_once (dirname(__FILE__) . '/../includes/config.php');
    session_name(SESSION_NAME);
    session_start();

    $user_password      		= addslashes($_POST['senha']);
    $user_confirme_password	    = addslashes($_POST['confirma-senha']);
    $user__id	                = $_SESSION['UserChangePasswordID'];


    	if(strlen($user_password) < 8){
			$feedback = array('status' => 0, 'msg' => "As senha precisa ter pelo menos 8 caracteres.", 'type' => 'warning');
			echo json_encode($feedback);
			exit;

		} else if($user_password != $user_confirme_password){
			$feedback = array('status' => 0, 'msg' => "As senhas não conferem...", 'type' => 'warning');
			echo json_encode($feedback);
			exit;

		} else if (empty($user_password)) {
			$feedback = array('status' => 0, 'msg' => "A senha não pode ser vazia.", 'type' => 'warning');
			echo json_encode($feedback);
			exit;

		} else if (empty($user_confirme_password)) {
			$feedback = array('status' => 0, 'msg' => "Você precisa confirmar a senha!", 'type' => 'warning');
			echo json_encode($feedback);
			exit;
        }
    
    $user_password = sha1($user_password);

    $stmt = $conn->prepare('UPDATE users SET user_password = :user_password WHERE user__id = :user__id');
    $stmt->execute(array('user_password' => $user_password, 'user__id' => $user__id));

    $msg = "Sua senha foi alterada!";

    $url = SERVER_URI . "/login";

	$feedback = array('title' => 'Feito!', 'msg' => $msg, 'type' => 'success', 'url' => $url);
	echo json_encode($feedback);
	exit;



?>