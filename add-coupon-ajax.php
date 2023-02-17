<?php

require "includes/config.php";

session_name(SESSION_NAME);
session_start();

if (!(isset($_POST['action']))){
	exit;
}

$coupon_id = 0;
$coupon_product_id 		= addslashes($_POST['produto']);
$coupon_linked_sales 	= addslashes($_POST['ofertas-vinculadas-mult-select-text']);
$coupon_string			= strtoupper(addslashes($_POST['texto-cupom']));
$coupon_percent			= addslashes($_POST['porcentagem-cupom']);
$coupon_limit			= addslashes($_POST['quantidade-cupom']);

$coupon_limit_date = date('Y-m-d H:i:s');




if ($_POST['action'] == 'create-coupon'){
 

	$stmt = $conn->prepare('INSERT INTO coupons (coupon_id, coupon_product_id, coupon_linked_sales, coupon_string, coupon_percent, coupon_limit, coupon_limit_date) VALUES (:coupon_id, :coupon_product_id, :coupon_linked_sales, :coupon_string, :coupon_percent, :coupon_limit, :coupon_limit_date)');
	
	try {
		$stmt->execute(array('coupon_id' => $coupon_id, 'coupon_product_id' => $coupon_product_id, 'coupon_linked_sales' => $coupon_linked_sales, 'coupon_string' => $coupon_string, 'coupon_percent' => $coupon_percent, 'coupon_limit' => $coupon_limit, 'coupon_limit_date' => $coupon_limit_date));

		$feedback = array('status' => 1, 'msg' => ":)");
		echo json_encode($feedback);
		exit;

      } catch(PDOException $e) {
        $error= 'ERROR: ' . $e->getMessage();
		$feedback = array('status' => 1, 'msg' => $error);
		echo json_encode($feedback);
      }


}

else {
	$feedback = array('status' => 0, 'msg' => 'Algo está errado! Atualize a página e tente novamente.',  'product_id' => $product_id); 
	echo json_encode($feedback);
	exit;
}
?>