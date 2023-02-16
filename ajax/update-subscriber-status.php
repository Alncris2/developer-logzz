<?php

require dirname(__FILE__) . "/../includes/config.php";

$user_code = addslashes($_GET['code']);
$action = addslashes($_GET['action']);
$user__id = addslashes($_GET['user__id']);

if ($action === 'reactivate-subscriber') {
    try {
        $stmt = $conn->prepare('UPDATE users SET active = 1 WHERE user__id = :user__id');
        $stmt->execute(array('user__id' => $user__id));

        $msg = "Este usuário agora tem acesso à plataforma novemate.";

        $feedback = array('msg' => $msg, 'type' => 'success', 'title' => 'Feito');
    } catch (PDOException $e) {

        $error = 'ERROR: ' . $e->getMessage();
        $feedback = array('title' => 'Erro!', 'msg' => 'Erro Interno', 'type' => 'erro');
    }
} else {
    try {
        $stmt = $conn->prepare('UPDATE users SET active = 0 WHERE user_code = :user_code');
        $stmt->execute(array('user_code' => $user_code));

        $msg = "Este usuário não tem mais acesso à plataforma.";

        $feedback = array('msg' => $msg, 'type' => 'success', 'title' => 'Feito');
    } catch (PDOException $e) {

        $error = 'ERROR: ' . $e->getMessage();
        $feedback = array('title' => 'Erro!', 'msg' => 'Erro Interno', 'type' => 'erro');
    }
}

echo json_encode($feedback);
exit;
