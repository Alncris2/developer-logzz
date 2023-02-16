<?php

require dirname(__FILE__) . "/../includes/config.php";

session_name(SESSION_NAME);
session_start();

# Verifica o envio do form via POST
if (isset($_GET['action']) && $_GET['action'] == 'update-sale-privacy'){


# Recebe a trata os inputs
$sale_id = addslashes($_GET['sid']);
if (!preg_match("/^[0-9]*$/", $sale_id)) {
    $feedback = array('status' => 0, 'msg' => 'Atualize a página e tente novamente', 'title' => "Algo está errado");
    echo json_encode($feedback);
    exit;
}

$product_id = addslashes($_GET['pid']);
if (!preg_match("/^[0-9]*$/", $product_id)) {
    $feedback = array('status' => 0, 'msg' => 'Atualize a página e tente novamente', 'title' => "Algo está errado");
    echo json_encode($feedback);
    exit;
}

$sale_shop_visibility = intval(addslashes($_GET['status']));
if (!preg_match("/^[0-1]*$/", $sale_shop_visibility)) {
    $feedback = array('status' => 0, 'msg' => 'Atualize a página e tente novamente', 'title' => "Algo está errado");
    echo json_encode($feedback);
    exit;
}


# Prepara a query para atualizar a privacidade da oferta.
$stmt = $conn->prepare('UPDATE sales SET sale_shop_visibility = :sale_shop_visibility WHERE sale_id = :sale_id');

	try {
		$stmt->execute(array('sale_shop_visibility' => $sale_shop_visibility, 'sale_id' => $sale_id));

        #$url = SERVER_URI . "/produto/" . $product_id;

		$feedback = array('status' => 1, 'msg' => 'Status da Oferta Alterado!');

        # Atualiza os valores de COMMISSION_MAX e COMMISSION_MIN do produto.
        $max_commission = $conn->prepare('SELECT MAX(sale_price) FROM sales WHERE product_id = :product_id AND (sale_shop_visibility = 1 AND sale_trashed = 0)');
        $max_commission->execute(array('product_id' => $product_id));
        $max_commission = $max_commission->fetch();
        
        if ($max_commission[0] == null) {
            $product_max_price = 0;
        } else {
            $product_max_price = $max_commission[0];
        }

        $min_commission = $conn->prepare('SELECT MIN(sale_price) FROM sales WHERE product_id = :product_id AND (sale_shop_visibility = 1 AND sale_trashed = 0)');
        $min_commission->execute(array('product_id' => $product_id));
        $min_commission = $min_commission->fetch();
        $product_min_price = $min_commission[0];

        if ($max_commission[0] == null) {
            $product_min_price = 0;
        } else {
            $product_min_price = $min_commission[0];
        }


        if ($min_commission > 0 AND $max_commission > 0) {
            $update_comissiona_range = $conn->prepare('UPDATE products SET product_max_price = :product_max_price, product_min_price = :product_min_price WHERE product_id = :product_id');
            $update_comissiona_range->execute(array('product_max_price' => $product_max_price, 'product_min_price' => $product_min_price, 'product_id' => $product_id));
        }

      } catch(PDOException $e) {
        $error = 'ERROR: ' . $e->getMessage();
		$feedback = array('status' => '0', 'msg' => $error);
      }


	echo json_encode($feedback);
}

else {
	$feedback = array('status' => 0, 'msg' => 'Algo está errado! Atualize a página e tente novamente.'); 
	echo json_encode($feedback);
	exit;
}

?>