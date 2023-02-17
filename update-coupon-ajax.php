<?php

require "includes/config.php";
session_name(SESSION_NAME);
session_start();

if (!(isset($_POST['action']))){
	exit;
}


$coupon_linked_sales  	= addslashes($_POST['ofertas-vinculadas-mult-select-text']);

if (empty($coupon_linked_sales)){
	$feedback = array('status' => 0, 'msg' => 'O cupom precisa estar vinculado a pelo menos 1 oferta.');
	echo json_encode($feedback); 
	exit;
}

$coupon_string 			= addslashes($_POST['texto-cupom']);
$coupon_percent 		= addslashes($_POST['porcentagem-cupom']);
$coupon_limit 			= addslashes($_POST['quantidade-cupom']);
$coupon_id 				= addslashes($_POST['cupom']);

		$stmt = $conn->prepare('UPDATE coupons SET coupon_linked_sales = :coupon_linked_sales, coupon_string = :coupon_string, coupon_percent = :coupon_percent, coupon_limit = :coupon_limit WHERE coupon_id = :coupon_id');

		try {
			$stmt->execute(array('coupon_linked_sales' => $coupon_linked_sales, 'coupon_string' => $coupon_string, 'coupon_percent' => $coupon_percent, 'coupon_limit' => $coupon_limit, 'coupon_id' => $coupon_id));

			$msg = 'Informações do cupom foram atualizadas.';
	
			$feedback = array('status' => 1, 'msg' => $msg);
			echo json_encode($feedback); 


		  } catch(PDOException $e) {
			$error = 'ERROR: ' . $e->getMessage();

			$feedback = array('status' => 0, 'msg' => 'Houve um erro ao atualizar os dados. Atualize a página e tente novamente.',);
			echo json_encode($feedback); 

		  }
	 
	
?>