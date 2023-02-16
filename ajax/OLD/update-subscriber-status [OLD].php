<?php

    require dirname(__FILE__) . "/../includes/config.php";

    $user_code = addslashes($_GET['code']);

    # $feedback = array('title' => 'Erro!', 'msg' => $user_code, 'type' => 'warning');

    try {
        $stmt = $conn->prepare('UPDATE users SET active = 0 WHERE user_code = :user_code');
        $stmt->execute(array('user_code' => $user_code));

        $msg = "Este usuário não tem mais acesso à plataforma.";

        $feedback = array('msg' => $msg, 'type' => 'success', 'title' => 'Feito');
    
    } catch(PDOException $e) {
			
        $error = 'ERROR: ' . $e->getMessage();
		$feedback = array('title' => 'Erro!', 'msg' => 'Erro Interno', 'type' => 'erro');

    }

	echo json_encode($feedback);
	exit;

?>