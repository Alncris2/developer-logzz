<?php
    require (dirname(__FILE__) . "/../includes/config.php");
    
    $coupon_string  = addslashes($_GET['coupon']);
    $sale_id        = addslashes($_GET['sale']);

    if(empty($coupon_string)){
        $feedback = array('title' => 'Cupom vazio!', 'status' => 0, 'msg' => 'Você precisa informar o texto do cupom.');
        echo json_encode($feedback);
        exit;
    }

    $stmt = $conn->prepare('SELECT * FROM coupons WHERE coupon_string = :coupon_string');
    $stmt->execute(array('coupon_string' => $coupon_string));

    if ($stmt->rowCount() != 0){
        while($coupon_data = $stmt->fetch()) {
            if ($coupon_data['coupon_trashed'] != 0) {
                $status = 0;
                $msg = "Cupom Expirado!";
                $discount = 0;
            
            } else {
            
                $coupon_sales = explode(",", $coupon_data['coupon_linked_sales']);
                
                if(in_array($sale_id, $coupon_sales)){
                    $status = 1;
                    $discount = (100 - $coupon_data['coupon_percent']) / 100;
                    $msg = "Você ganhou " . $coupon_data['coupon_percent'] . "% de desconto no seu pedido.";
                
                } else {
                    $status = 0;
                    $msg = "Cupom Inválido para esta oferta.";
                    $discount = 0;
                }


            }
        }
    } else {
        $status = 0;
        $msg = "Cupom Inválido";
        $discount = 0;
    }

	$feedback = array('status' => $status, 'msg' => $msg, 'discount' => $discount);
	echo json_encode($feedback);
	exit;

?>