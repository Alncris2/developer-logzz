<?php

require_once(dirname(__FILE__) . '/../includes/config.php');

session_name(SESSION_NAME);
session_start();

$user__id = $_SESSION['UserID'];

if ($_POST['action'] == 'enviar-order'){
	$pedido						= $_POST['order']; 
	$order_status				= 8; 
	$order_tracking				= addslashes($_POST['cod-rastreio']); 
	$order_shipping 			= addslashes($_POST['transportadora']);
	$order_distribution_center	= addslashes($_POST['centro-distribuicao']);
	$value_tracking =  addslashes(str_replace(",", ".", $_POST['custo-envio']));

	# VALIDAR ESTOQUE E DAR BAIXA NO CENTRO DE DISTRIBUIÇÃO 

	// Pegar product ID para gerar meta key 
	$get_id_product = $conn->prepare("SELECT product_id, user__id, order_quantity FROM orders AS o WHERE o.order_id = :order_id");
	$get_id_product->execute(['order_id' => $pedido]);

	$product = $get_id_product->fetch(\PDO::FETCH_ASSOC);
	$meta_inventory = $product['user__id'] . "-" . $product['product_id'] . "-" . "1";

	$query = "SELECT * FROM inventories WHERE inventory_meta = :inventory_meta AND ship_locale = 1";
	$stmt_inventory = $conn->prepare($query);

	$stmt_inventory->execute(['inventory_meta' => $meta_inventory]);

	$quantity = $stmt_inventory->fetch(\PDO::FETCH_ASSOC)['inventory_quantity'];
	$quantity_this_product = $product['order_quantity'];

	$quantity_after_lowering = ($quantity - $quantity_this_product);

	if($quantity_after_lowering <= 0){
		$feedback = array('title' => 'Feito!', 'msg' => 'Estoque insuficiente', 'type' => 'success');
        echo json_encode($feedback);
		exit;
	}
		
	// Atualizar quantidade no estoque 
	$query = "UPDATE inventories SET inventory_quantity = :inventory_quantity WHERE inventory_meta = :inventory_meta";

	try {

		$stmt = $conn->prepare($query);
		$stmt->execute(['inventory_quantity' => $quantity_after_lowering,'inventory_meta' => $meta_inventory]);

		$stmt = $conn->prepare('UPDATE orders SET order_status = :order_status, order_tracking = :order_tracking, order_shipping = :order_shipping, order_distribution_center = :order_distribution_center, order_tiny_id = 0, order_tracking_value = :order_tracking_value WHERE order_id = :order_id');
		$stmt->execute(array('order_id' => $pedido, 'order_status' => $order_status, 'order_tracking' => $order_tracking, 'order_shipping' => $order_shipping, 'order_distribution_center' => $order_distribution_center, 'order_tracking_value' => $value_tracking));

		$feedback = array('title' => 'Feito!', 'msg' => 'As informações de envio e status foram atualizadas!.', 'type' => 'success');
        echo json_encode($feedback);

	} catch(PDOException $e) {
		$error = 'ERROR: ' . $e->getMessage();
		$feedback = array('title' => 'Erro!', 'msg' => $error, 'type' => 'erro');
		echo json_encode($feedback);
		exit;
	}

} else {
	$feedback = array('status' => 0, 'msg' => 'Erro interno! Não foi possível processar sua solicitação.');
	echo json_encode($feedback);
	exit;
}

?>