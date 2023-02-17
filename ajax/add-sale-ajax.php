<?php

require dirname(__FILE__) . "/../includes/config.php";
session_name(SESSION_NAME);
session_start();

# Verifica se o envio do form foi via POST
if (!(isset($_POST['action']))){
	exit;
}

# Variáveis internas
$sale_id = 0;
$user__id = $_SESSION['UserID'];
$sale_date_start = $sale_date_end = date('Y-m-d H-i-s');
$product_shipping_tax = number_format($_SESSION['UserPlanShipTax'], 2, '.', ',');

# Recebe a trata os inputs
$product_id = addslashes($_POST['produto']);
if (!preg_match("/^[(0-9) ]*$/", $product_id)) {
    $feedback = array('type' => 'warning', 'status' => 0, 'msg' => 'Experimente atualizar a página e reiniciar a criação da oferta.', 'title' => "Erro de Comunicação");
    echo json_encode($feedback);
    exit;
}

$sale_name = addslashes($_POST['nome-oferta']);
if (!preg_match("/^[0-9a-zA-Z-À-ú' ]*$/", $sale_name)) {
    $feedback = array('type' => 'warning', 'status' => 0, 'msg' => 'Confira o nome da oferta.', 'title' => "Algo está errado");
    echo json_encode($feedback);
    exit;
} else if (strlen($sale_name) < 4) {
    $feedback = array('type' => 'warning', 'status' => 0, 'msg' => "O nome da oferta é muito curto.", 'title' => "Algo está errado");
    echo json_encode($feedback);
    exit;
}

$sale_price = floatval(addslashes(str_replace(',', '.', str_replace('.', '', $_POST['preco-oferta'])))); 
$sale_tax = number_format($sale_price * $_SESSION['UserPlanTax'], 2, '.', ''); 
 
$sale_url = addslashes($_POST['url-oferta']);
$sale_quantity = addslashes($_POST['quantidade-oferta']);

if (@$_POST['status-oferta']){
	$sale_status = addslashes($_POST['status-oferta']);
} else {
	$sale_status = 1;
}

$sale_shop_visibility = addslashes($_POST['privacidade-oferta']);
if (!preg_match("/^[(0-9)]*$/", $product_id)) {
    $feedback = array('type' => 'warning', 'status' => 0, 'msg' => 'Experimente atualizar a página e reiniciar a criação da oferta.', 'title' => "Erro de Comunicação");
    echo json_encode($feedback);
    exit;
}

if (isset($_POST['comissao-personalizada'])){
    $sale_custom_commission = addslashes($_POST['comissao-personalizada']);
    if (!preg_match("/^[(0-9)]*$/", $sale_custom_commission)) {
        $feedback = array('type' => 'warning', 'status' => 0, 'msg' => "Confira o valor da Comissõa Personalizada", 'title' => "Algo está errado");
        echo json_encode($feedback);
        exit;
    }
}

$stmt = $conn->prepare('SELECT * FROM products WHERE product_id = :product_id AND product_trash = 0 LIMIT 1');
$stmt->execute(array('product_id' => $product_id));
while($row = $stmt->fetch()) {
	$product_price = $row['product_price'];
	$sale_cost = ($product_price * $sale_quantity) + $_SESSION['UserPlanShipTax'] + $sale_tax;
}



/**
 * 
 * Rotina de Criação de uma nova oferta
 * 
 * 
 */
if ($_POST['action'] == 'create-oferta'){

    if(!$sale_custom_commission) {
        $get_default_commision = $conn->prepare('SELECT product_commission FROM `products` WHERE product_id = :product_id');
        $get_default_commision->execute(array('product_id' => $product_id));
        
        if($default_commisio = $get_default_commision->fetch()){
            $sale_custom_commission = $default_commisio['product_commission'];
        }
    }

    # Prepara a query para inserir os dados no DB.
	$stmt = $conn->prepare('INSERT INTO sales (sale_id, product_id, sale_product_name, sale_name, sale_date_start, sale_date_end, sale_quantity, sale_price, sale_status, sale_shop_visibility, sale_url, sale_tax, product_shipping_tax, product_price, sale_cost, type_checkout) VALUES (:sale_id, :product_id, :sale_product_name, :sale_name, :sale_date_start, :sale_date_end, :sale_quantity, :sale_price, :sale_status, :sale_shop_visibility, :sale_url, :sale_tax, :product_shipping_tax, :product_price, :sale_cost, :type_checkout)');
	
	try {
	    $stmt->execute(array('sale_id' => $sale_id, 'product_id' => $product_id, 'sale_product_name' => '#ProductName', 'sale_name' => $sale_name, 'sale_date_start' => $sale_date_start, 'sale_date_end' => $sale_date_end, 'sale_quantity' => $sale_quantity, 'sale_price' => $sale_price, 'sale_status' => $sale_status, 'sale_shop_visibility' => $sale_shop_visibility, 'sale_url' => $sale_url, 'sale_tax' => $sale_tax, 'product_shipping_tax' => $product_shipping_tax, 'product_price' => $product_price, 'sale_cost' => $sale_cost, 'type_checkout' => 'CHECKOUT_PADRÃO'));

        # Armazena o feeback positivo na variável.
	    $feedback = array('title' => "Oferta Criada!", 'type' => 'success', 'status' => 1, 'msg' => ":)");

    } catch(PDOException $e) {
        # Armazena o feeback negativo na variável.
        $error= 'ERROR: ' . $e->getMessage();
		$feedback = array('status' => 0, 'msg' => $error);
    }

    # Creat/Upadte Commission Meta
    if (isset($sale_custom_commission)) {

        $get_last_id = $conn->prepare('SELECT sale_id FROM sales ORDER BY sale_id DESC LIMIT 1');
        $get_last_id->execute();
        $last_id = $get_last_id->fetch();

        $last_id = $last_id[0];

        $get_sale_custom_commision = $conn->prepare('SELECT meta_id FROM sales_meta WHERE sale_id = :sale_id AND meta_key = "custom_commission"');
        $get_sale_custom_commision->execute(array('sale_id' => $last_id));

        if ($get_sale_custom_commision->rowCount() == 0) {
            $set_sale_custom_commission = $conn->prepare('INSERT INTO sales_meta (meta_id, sale_id, meta_key, meta_value) VALUES (:meta_id, :sale_id, :meta_key, :meta_value)');
            $set_sale_custom_commission->execute(array('meta_id' => '0', 'sale_id' => $last_id, 'meta_key' => "custom_commission", 'meta_value' => $sale_custom_commission));
        } else {
            $custcon_commision_id = $get_sale_custom_commision->fetch();
            $custcon_commision_id = $custcon_commision_id['meta_id'];
            $set_membership_meta_pixel = $conn->prepare('UPDATE sales_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
            $set_membership_meta_pixel->execute(array('meta_value' => $sale_custom_commission, 'meta_id' => $custcon_commision_id));
        }
    }

    # Atualiza os valores de COMMISSION_MAX e COMMISSION_MIN do produto.
    $max_commission = $conn->prepare('SELECT MAX(sale_price) FROM sales WHERE product_id = :product_id AND (sale_shop_visibility = 1 AND sale_trashed = 0)');
    $max_commission->execute(array('product_id' => $product_id));
    $max_commission = $max_commission->fetch();
    $product_max_price = $max_commission[0];

    $min_commission = $conn->prepare('SELECT MIN(sale_price) FROM sales WHERE product_id = :product_id AND (sale_shop_visibility = 1 AND sale_trashed = 0)');
    $min_commission->execute(array('product_id' => $product_id));
    $min_commission = $min_commission->fetch();
    $product_min_price = $min_commission[0];

    if($min_commission[0] != null && $max_commission[0] != null){
        $update_comissiona_range = $conn->prepare('UPDATE products SET product_max_price = :product_max_price, product_min_price = :product_min_price WHERE product_id = :product_id');
        $update_comissiona_range->execute(array('product_max_price' => $product_max_price, 'product_min_price' => $product_min_price, 'product_id' => $product_id));
    }

    # Retorna o feeback
    echo json_encode($feedback);
    exit;

}


else {
	$feedback = array('title' => "Erro", 'type' => 'warning', 'status' => 0, 'msg' => 'Algo está errado! Atualize a página e tente novamente.',  'product_id' => $product_id); 
	echo json_encode($feedback);
	exit;
}
?>