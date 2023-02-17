<?php

require (dirname(__FILE__)) . "/../includes/config.php";
session_name(SESSION_NAME);
session_start();


if (!(isset($_POST['action'])) && (!$_GET['delete-coupon'])){
    $feedback = array('status' => 0, 'msg' => 'Atualize a página e tente novamente.');
    echo json_encode($feedback);
	exit;
}

if (isset($_GET['delete-coupon'])){

    $feedback = array('status' => 0, 'msg' => 'Atualize a página e tente novamente.');
    echo json_encode($feedback);
    exit;
    
    $coupon_string = addslashes($_POST['cupom']);
    if (empty($coupon_string)) {
        $feedback = array('status' => 0, 'msg' => 'Atualize a página e tente novamente.');
        echo json_encode($feedback);
    }

    $stmt = $conn->prepare('UPDATE coupons SET coupon_trashed = 1 WHERE coupon_string = :coupon_string');

    try {
        
        $stmt->execute(array('coupon_string' => $coupon_string));

        $msg = 'Este cupom não poderá mais ser usado.';

        $feedback = array('status' => 1, 'msg' => $msg, 'title' => 'Feito.');
        echo json_encode($feedback);

    } catch (PDOException $e) {
        
        $error = 'ERROR: ' . $e->getMessage();

        $feedback = array('type' => 'warning', 'status' => 0, 'msg' => 'Houve um erro ao atualizar os dados. Atualize a página e tente novamente.',);
        echo json_encode($feedback);

    }


} else {

    $coupon_string             = addslashes($_POST['texto-cupom']);
    if (!preg_match("/^[A-Z-0-9]*$/", $coupon_string)) {
        $feedback = array('status' => 0, 'msg' => 'O texto do cupom só pode ter letras e números.', 'title' => "Confira o Cupom");
        echo json_encode($feedback);
        exit;
    }

    $coupon_linked_sales      = addslashes($_POST['ofertas-vinculadas-mult-select-text']);
    if (empty($coupon_linked_sales)) {
        $feedback = array('status' => 0, 'msg' => 'O cupom precisa estar vinculado a pelo menos 1 oferta.');
        echo json_encode($feedback);
        exit;
    }

    $coupon_percent         = addslashes($_POST['porcentagem-cupom']);
    if (!preg_match("/^[0-9]*$/", $coupon_percent) || empty($coupon_percent)) {
        $feedback = array('status' => 0, 'msg' => 'Informe corretamente a porcentagem de desconto.');
        echo json_encode($feedback);
        exit;
    }

    $coupon_limit             = addslashes($_POST['quantidade-cupom']);
    if (empty($coupon_limit)) {
        $coupon_limit = 0;
    }

    $coupon_id                 = addslashes($_POST['cupom']);
    if (empty($coupon_linked_sales)) {
        $feedback = array('status' => 0, 'msg' => 'Atualize a página e tente novamente.');
        echo json_encode($feedback);
        exit;
    }



    $stmt = $conn->prepare('UPDATE coupons SET coupon_linked_sales = :coupon_linked_sales, coupon_string = :coupon_string, coupon_percent = :coupon_percent, coupon_limit = :coupon_limit WHERE coupon_id = :coupon_id');

    try {
        $stmt->execute(array('coupon_linked_sales' => $coupon_linked_sales, 'coupon_string' => $coupon_string, 'coupon_percent' => $coupon_percent, 'coupon_limit' => $coupon_limit, 'coupon_id' => $coupon_id));

        $msg = 'Informações do cupom foram atualizadas.';

        $feedback = array('status' => 1, 'msg' => $msg);
        echo json_encode($feedback);
    } catch (PDOException $e) {
        $error = 'ERROR: ' . $e->getMessage();

        $feedback = array('status' => 0, 'msg' => 'Houve um erro ao atualizar os dados. Atualize a página e tente novamente.',);
        echo json_encode($feedback);
    }

}
	 
	
?>