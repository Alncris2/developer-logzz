<?php
require (dirname(__FILE__)) . "/../includes/config.php";
session_name(SESSION_NAME);
session_start();

if (!(isset($_POST['action']))){
	exit;
}

# Recebe e Trata os Inputs
$sale_id = trim(addslashes($_POST['sale_id']));
if (!preg_match("/^[(0-9) ]*$/", $sale_id)) {
    $feedback = array('title' => "Erro", 'type' => 'warning', 'msg' => 'Experimente atualizar a página e reiniciar a criação da oferta.', 'title' => "Erro de Comunicação");
    echo json_encode($feedback);
    exit;
}

$product_id = trim(addslashes($_POST['product']));
if (!preg_match("/^[(0-9) ]*$/", $product_id)) {
    $feedback = array('title' => "Erro", 'type' => 'warning', 'msg' => 'Experimente atualizar a página e reiniciar a criação da oferta.', 'title' => "Erro de Comunicação");
    echo json_encode($feedback);
    exit;
}

$sale_name = trim(addslashes($_POST['nome-oferta']));
if (!preg_match("/^[0-9a-zA-Z-À-ú' ]*$/", $sale_name)) {
    $feedback = array('title' => "Erro", 'type' => 'warning', 'msg' => 'Confira o nome da oferta.', 'title' => "Algo está errado");
    echo json_encode($feedback);
    exit;
} else if (strlen($sale_name) < 4) {
    $feedback = array('title' => "Erro", 'type' => 'warning', 'msg' => "O nome da oferta é muito curto.", 'title' => "Algo está errado");
    echo json_encode($feedback);
    exit;
}

$sale_price = floatval(addslashes(str_replace(',', '.', str_replace('.', '', $_POST['preco-oferta'])))); 

$sale_url = trim(addslashes($_POST['url-oferta']));
if (!filter_var($sale_url, FILTER_SANITIZE_URL)) {
    $feedback = array('title' => "Erro", 'type' => 'warning', 'msg' => 'A URL da Oferta contém caracteres inválidos', 'title' => "Algo está errado");
    echo json_encode($feedback);
    exit;
}

$one_click_url = SERVER_URI . '/' . $sale_url . '/1clique';

if (isset($_POST['url-upsell']) && !(empty($_POST['url-upsell']))){
    $url_upsell = $_POST['url-upsell'];            
    if (!filter_var($url_upsell, FILTER_VALIDATE_URL)) {
        $feedback = array('title' => "Erro", 'type' => 'warning', 'msg' => 'Insira uma URL válida no campo URL de Upsell', 'title' => "Algo está errado");
        echo json_encode($feedback);
        exit;
    } 

} else {
    $url_upsell = NULL;
}

$fb_pixel = trim(addslashes($_POST['meta-pixel-facebook']));
if (!(empty($_POST['meta-pixel-facebook'])) && !preg_match("/^[0-9A-Za-z']*$/", $fb_pixel)) {
    $feedback = array('title' => "Algo está errado", 'type' => 'warning', 'msg' => 'O Pixel que você informou tem caracteres inválidos.');
    echo json_encode($feedback);
    exit;
}

$facebook_api = trim(addslashes($_POST['meta-pixel-facebook-api']));
if (!(empty($_POST['meta-pixel-facebook-api'])) && !preg_match("/^[0-9A-Za-z']*$/", $facebook_api)) {
    $feedback = array('title' => "Algo está errado", 'type' => 'warning', 'msg' => 'O Token da API de Conversões do Facebook que você informou tem caracteres inválidos.');
    echo json_encode($feedback);
    exit;
}

$google_ua = trim(addslashes($_POST['meta-google-analytics']));
if (!(empty($_POST['meta-google-analytics'])) && !preg_match("/^[0-9A-Za-z-']*$/", $google_ua)) {
    $feedback = array('title' => "Algo está errado", 'type' => 'warning', 'msg' => 'O ID do Google Analytics que você informou tem caracteres inválidos.');
    echo json_encode($feedback);
    exit;
}

$google_aw = trim(addslashes($_POST['meta-google-ads-id']));
if (!(empty($_POST['meta-google-ads-id'])) && !preg_match("/^[0-9A-Za-z-']*$/", $google_aw)) {
    $feedback = array('title' => "Algo está errado", 'type' => 'warning', 'msg' => 'O ID de Conversão Google Ads que você informou tem caracteres inválidos.');
    echo json_encode($feedback);
    exit;
}

$tiktok_pixel = trim(addslashes($_POST['meta-tiktok-pixel']));
if (!(empty($tiktok_pixel)) && !preg_match("/^[0-9A-Za-z-']*$/", $tiktok_pixel)) {
    $feedback = array('title' => "Algo está errado", 'type' => 'warning', 'msg' => 'Tiktok Pixel que você informou é inválido.');
    echo json_encode($feedback);
    exit;
}

$sale_quantity = trim(addslashes($_POST['quantidade-oferta']));
if (!preg_match("/^[(0-9) ]*$/", $sale_quantity)) {
    $feedback = array('title' => "Erro", 'type' => 'warning', 'msg' => 'Informe corretamente a quantidade de produtos da oferta', 'title' => "Erro de Comunicação");
    echo json_encode($feedback);
    exit;
}

$sale_freight = floatval(addslashes(str_replace(',', '.', $_POST['disponivel-frete'])));

if (isset($_POST['disponivel-estoque']) && !(empty($_POST['disponivel-estoque']))){
    $sale_url_redirect_or = $_POST['disponivel-estoque']; 
    if (!filter_var($sale_url_redirect_or, FILTER_VALIDATE_URL)) {
        $feedback = array('title' => "Erro", 'type' => 'warning', 'msg' => 'Insira uma URL válida no campo redirecionar caso produto esteja sem estoque', 'title' => "Erro de Comunicação");
        echo json_encode($feedback);
        exit;
    } 
} else {
    $sale_url_redirect_or = NULL;
}

if (isset($_POST['disponivel-oferts']) && !(empty($_POST['disponivel-oferts']))){
    $sale_url_redirect_post = $_POST['disponivel-oferts'];            
    if (!filter_var($sale_url_redirect_post, FILTER_VALIDATE_URL)) {
        $feedback = array('title' => "Erro", 'type' => 'warning', 'msg' => 'Insira uma URL válida no campo redirecionar após concluir a compra', 'title' => "Erro de Comunicação");
        echo json_encode($feedback);
        exit;
    }
} else {
    $sale_url_redirect_post = NULL;
}

# Variáveis Internas
$sale_tax = round($sale_price * $_SESSION['UserPlanTax'], 2);
$sale_date_start = date('Y-m-d H-i-s');
$sale_date_end = date('Y-m-d H-i-s');
$product_shipping_tax = number_format($_SESSION['UserPlanShipTax'], 2, '.', ',');
$sale_status = 1;

# Cálcula o preço de custo da oferta
$get_product_id = $conn->prepare('SELECT * FROM products WHERE product_id = :product_id LIMIT 1');
$get_product_id->execute(array('product_id' => $product_id));
$product_price = $get_product_id->fetch();
$product_price = $product_price['product_price'];
$sale_cost = ($product_price * $sale_quantity) + $_SESSION['UserPlanShipTax'] + $sale_tax;
$name_checkout = $_POST['checkout'];

# Creat/Update Commission Meta, se for o caso
if (isset($_POST['comissao-personalizada'])) {
    $sale_custom_commission = trim(addslashes($_POST['comissao-personalizada']));
    if (!preg_match("/^[(0-9)]*$/", $sale_custom_commission)) {
        $feedback = array('type' => 'warning', 'status' => 0, 'msg' => "Confira o valor da Comissõa Personalizada", 'title' => "Algo está errado");
        echo json_encode($feedback);
        exit;
    }
}


if (isset($sale_custom_commission)) {

    $get_sale_custom_commision = $conn->prepare('SELECT meta_id FROM sales_meta WHERE sale_id = :sale_id AND meta_key = "custom_commission"');
    $get_sale_custom_commision->execute(array('sale_id' => $sale_id));

    if ($get_sale_custom_commision->rowCount() == 0) {
        $set_sale_custom_commission = $conn->prepare('INSERT INTO sales_meta (meta_id, sale_id, meta_key, meta_value) VALUES (:meta_id, :sale_id, :meta_key, :meta_value)');
        $set_sale_custom_commission->execute(array('meta_id' => '0', 'sale_id' => $sale_id, 'meta_key' => "custom_commission", 'meta_value' => $sale_custom_commission));
    } else {
        $custcon_commision_id = $get_sale_custom_commision->fetch();
        $custcon_commision_id = $custcon_commision_id['meta_id'];
        $set_membership_meta_pixel = $conn->prepare('UPDATE sales_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
        $set_membership_meta_pixel->execute(array('meta_value' => $sale_custom_commission, 'meta_id' => $custcon_commision_id));
    }

}

        # Prepara e executa a query
		$stmt = $conn->prepare('UPDATE sales SET sale_name = :sale_name, sale_quantity  = :sale_quantity, sale_price = :sale_price, sale_url = :sale_url, sale_fb_pixel = :sale_fb_pixel, sale_google_aw = :sale_google_aw, sale_google_ua = :sale_google_ua, sale_tiktok_pixel = :sale_tiktok_pixel, meta_pixel_facebook_api = :meta_pixel_facebook_api, one_click_url = :one_click_url, url_upsell = :url_upsell, sale_cost = :sale_cost, sale_tax = :sale_tax, sale_freight = :sale_freight, sale_url_redirect_post = :sale_url_redirect_post, sale_url_redirect_or = :sale_url_redirect_or, type_checkout = :type_checkout WHERE sale_id = :sale_id');
		
		try {
			$stmt->execute(array('sale_name' => $sale_name, 'sale_quantity' => $sale_quantity, 'sale_price' => $sale_price, 'sale_url' => $sale_url, 'sale_fb_pixel' => $fb_pixel, 'sale_tiktok_pixel' => $tiktok_pixel, 'sale_google_ua' => $google_ua, 'sale_google_aw' => $google_aw, 'meta_pixel_facebook_api' => $facebook_api, 'sale_id' => $sale_id, 'one_click_url' => $one_click_url, 'url_upsell' => $url_upsell, 'sale_cost' => $sale_cost, 'sale_tax' => $sale_tax, 'sale_freight' => $sale_freight, 'sale_url_redirect_post' => $sale_url_redirect_post, 'sale_url_redirect_or' => $sale_url_redirect_or, 'type_checkout' => $name_checkout));

            # Atualiza os valores de COMMISSION_MAX e COMMISSION_MIN do produto.
            $max_commission = $conn->prepare('SELECT MAX(sale_price) FROM sales WHERE product_id = :product_id AND sale_trashed = 0');
            $max_commission->execute(array('product_id' => $product_id));
            $max_commission = $max_commission->fetch();
            $product_max_price = $max_commission[0];

            $min_commission = $conn->prepare('SELECT MIN(sale_price) FROM sales WHERE product_id = :product_id AND sale_trashed = 0');
            $min_commission->execute(array('product_id' => $product_id));
            $min_commission = $min_commission->fetch();
            $product_min_price = $min_commission[0];

            if ($min_commission != null && $max_commission != null) {
                $update_comissiona_range = $conn->prepare('UPDATE products SET product_max_price = :product_max_price, product_min_price = :product_min_price WHERE product_id = :product_id');
                $update_comissiona_range->execute(array('product_max_price' => $product_max_price, 'product_min_price' => $product_min_price, 'product_id' => $product_id));
            }

            # Encerra o script com feedback
			$feedback = array('title' => "Feito!", 'type' => 'success', 'msg' => 'As informações da oferta foram atualizadas.');
			echo json_encode($feedback);
            exit; 

		  } catch(PDOException $e) {
			
            $error = 'ERROR: ' . $e->getMessage();

			$feedback = array('title' => "Erro", 'type' => 'warning', 'msg' => $error);
			echo json_encode($feedback);
            exit;

		  }
?>