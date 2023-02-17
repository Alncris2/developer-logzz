<?php

    require "includes/config.php";
    
    $sale_id = addslashes($_GET['sid']);
    $product_id = addslashes($_GET['pid']);

    $stmt = $conn->prepare('UPDATE sales SET sale_trashed = 1 WHERE sale_id = :sale_id');
    $stmt->execute(array('sale_id' => $sale_id));

    # Atualiza os valores de COMMISSION_MAX e COMMISSION_MIN do produto.
    $max_commission = $conn->prepare('SELECT MAX(sale_price) FROM sales WHERE product_id = :product_id AND (sale_shop_visibility = 1 AND sale_trashed = 0)');
    $max_commission->execute(array('product_id' => $product_id));
    $max_commission = $max_commission->fetch();
    $product_max_price = $max_commission[0];

    $min_commission = $conn->prepare('SELECT MIN(sale_price) FROM sales WHERE product_id = :product_id AND (sale_shop_visibility = 1 AND sale_trashed = 0)');
    $min_commission->execute(array('product_id' => $product_id));
    $min_commission = $min_commission->fetch();
    $product_min_price = $min_commission[0];

    if ($min_commission[0] != null && $max_commission[0] != null) {
        $update_comissiona_range = $conn->prepare('UPDATE products SET product_max_price = :product_max_price, product_min_price = :product_min_price WHERE product_id = :product_id');
        $update_comissiona_range->execute(array('product_max_price' => $product_max_price, 'product_min_price' => $product_min_price, 'product_id' => $product_id));
    }

    $msg = "Esta oferta não está mais ativa.";

	$feedback = array('status' => 2, 'msg' => $msg);
	echo json_encode($feedback);
	exit;

?>