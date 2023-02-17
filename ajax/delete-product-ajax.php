<?php
require dirname(__FILE__) . "/../includes/config.php";
session_name(SESSION_NAME);
session_start();
	
$product_id	= addslashes($_GET['id']);
if (!preg_match("/^[0-9]*$/", $product_id)) {
    $feedback = array('msg' => 'Não foi possível excluir o produto.', 'title' => "Algo está errado", 'type' => 'error');
	echo json_encode($feedback);
	exit;
}


try {

	$update_inventory = $conn->prepare('UPDATE products SET product_trash = 1 WHERE product_id = :product_id');
	$update_inventory->execute(array('product_id' => $product_id));

	$feedback = array('title' => 'Feito!', 'msg' => 'O produto foi excluído da sua lista.', 'type' => 'success');
	echo json_encode($feedback);
	exit;

} catch(PDOException $e) {
	$error = 'ERROR: ' . $e->getMessage();
	$feedback = array('msg' => $error, 'title' => "Erro Interno", 'type' => 'error');
	echo json_encode($feedback);
	exit;
}

?>