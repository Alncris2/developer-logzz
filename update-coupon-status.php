<?php

    require "includes/config.php";
    
    $coupon = addslashes($_GET['coupon']);
    $product = addslashes($_GET['product']);
    $status = addslashes($_GET['status']);

    $stmt = $conn->prepare('UPDATE coupons SET coupon_trashed = :data_status WHERE coupon_product_id = :product AND coupon_string = :coupon');
    $stmt->execute(array('coupon' => $coupon, 'product' => $product, 'data_status' => $status));

    if ($status == 0){
        $msg = "O cupom " . strtoupper($coupon) . " foi ativado novamente.";
    } else {
        $msg = "O cupom " . strtoupper($coupon) . " foi desativado.";
    }

	$feedback = array('status' => 2, 'msg' => $msg);
	echo json_encode($feedback);
	exit;

?>