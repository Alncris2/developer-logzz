<?php

require (dirname(__FILE__)) . "/../includes/config.php";

    $order_number = addslashes($_GET['order']);
    $order_number = str_replace('AFI', '', $order_number); 

    $stmt = $conn->prepare('DELETE FROM orders WHERE order_number = :order_number');
    $stmt->execute(array('order_number' => $order_number));

    // VERIFICAR SE O PEDIDO TEM VENDA DE AFILIADO
    $order_afi = "AFI". $order_number;

    $verify_afi = $conn->prepare("SELECT * FROM orders AS o WHERE o.order_number = :order_number");
    $verify_afi->execute(['order_number' => $order_afi]);

    if($verify_afi->rowCount() > 0){
        // DELETAR PEDIDO DO AFILIADO 
        $stmt = $conn->prepare('DELETE FROM orders WHERE order_number = :order_number');
        $stmt->execute(['order_number' => $order_afi]);
    }

    $msg = "O pedido foi excluído com sucesso.";

	$feedback = array('type' => 'success', 'title' => 'Feito!', 'msg' => $msg);
	echo json_encode($feedback);
	exit;

?>