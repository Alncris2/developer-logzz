<?php

    require "includes/config.php";
    
    $user_hashed = explode("217", addslashes($_GET['id']));
    $user__id = $user_hashed[1];

    $stmt = $conn->prepare('UPDATE users SET active = 0 WHERE user__id = :user__id');
    $stmt->execute(array('user__id' => $user__id));

    $msg = "Este usuário não tem mais acesso à plataforma.";

	$feedback = array('status' => 2, 'msg' => $msg);
	echo json_encode($feedback);
	exit;

?>