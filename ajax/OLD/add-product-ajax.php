<?php

require dirname(__FILE__) . "/../includes/config.php";
require (dirname(__FILE__) . '/../includes/classes/RandomStrGenerator.php');

session_name(SESSION_NAME);
session_start();

# Verifica o envio do form via POST
if (!(isset($_POST['action']))){
	exit;
}

# Variáveis internas
$product_trashed = $product_id = 0;
$user__id = $_SESSION['UserID'];
$product_shipping_tax = $_SESSION['UserPlanShipTax'];

# Recebe a trata os inputs
$product_name = addslashes($_POST['nome-produto']);
if (!preg_match("/^[a-zA-Z-À-ú0-9\/£$%^&*()}{:\'#~<>,;!@\|\-=\-_+\-¬\`\' ]*$/", $product_name)) {
    $feedback = array('status' => 0, 'msg' => 'Confira o nome do produto.', 'title' => "Algo está errado");
    echo json_encode($feedback);
    exit;
} else if (strlen($product_name) < 5) {
    $feedback = array('status' => 0, 'msg' => "O nome do produto é muito curto.", 'title' => "Algo está errado");
    echo json_encode($feedback);
    exit;
}

$product_description = addslashes($_POST['descricao-produto']);
if (empty($product_description) || strlen($product_description) == 0){
	$feedback = array('status' => 0, 'msg' => 'Confira a descrição do produto.', 'title' => "Algo está errado");
	echo json_encode($feedback);
	exit; 
}

$product_price = 0;
// if (!preg_match("/^[(0-9\,.) ]*$/", $product_price)) {
//     $feedback = array('status' => 0, 'msg' => 'Confira o preço do produto.', 'title' => "Algo está errado");
// 	echo json_encode($feedback);
// 	exit;
// } else if (empty($product_price) || $product_price == 0){
// 	$feedback = array('status' => 0, 'msg' => "Informe o preço de custo do produto.", 'title' => "Algo está errado");
// 	echo json_encode($feedback);
// 	exit; 
// }

$product_categories = addslashes($_POST['categoria-produto-select-text']);
if (!preg_match("/^[(0-9\,.) ]*$/", $product_categories)) {
    $feedback = array('status' => 0, 'msg' => 'Experimente atualizar a página e reiniciar o cadastro do produto.', 'title' => "Algo está errado");
	echo json_encode($feedback);
	exit;
}

$product_sale_page = addslashes($_POST['pagina-vendas-produto']);
if (!(filter_var($product_sale_page, FILTER_SANITIZE_URL))) {
    $feedback = array('status' => 0, 'msg' => 'Informe corretamente a URL da página de vendas.', 'title' => "Algo está errado");
	echo json_encode($feedback);
	exit;
}

$product_warranty_time = addslashes($_POST['garantia-produto']);
if (!preg_match("/^[(0-9)]*$/", $product_warranty_time )) {
    $feedback = array('status' => 0, 'msg' => 'Você informou o período de garantia corretamente?', 'title' => "Algo está errado");
	echo json_encode($feedback);
	exit;
}


# Recebe e trata os inputs referentes às configuações de Afiliação do produto.
if (isset($_POST['disponivel-afiliacao']) && $_POST['disponivel-afiliacao'] == 'sim'){

    $product_membership_available = addslashes($_POST['disponivel-afiliacao']);
    if (!preg_match("/^[a-z]*$/", $product_membership_available)) {
        $feedback = array('status' => 0, 'msg' => 'disponivel-afiliacao', 'title' => "Algo está errado");
        echo json_encode($feedback);
        exit;
    }

    $product_shop_visibility = addslashes($_POST['visivel-afiliacao']);
    if (!preg_match("/^[a-z]*$/", $product_shop_visibility)) {
        $feedback = array('status' => 0, 'msg' => 'Confira os dados do produto.', 'title' => "Algo está errado");
        echo json_encode($feedback);
        exit;
    }

    $product_commission = addslashes($_POST['comissao-produto']);
    if (!preg_match("/^[0-9]*$/", $product_commission)) {
        $feedback = array('status' => 0, 'msg' => 'Confira os dados do produto.', 'title' => "Algo está errado");
        echo json_encode($feedback);
        exit;
    }

    $product_auto_membership = addslashes($_POST['afiliacao-automatica']);
    if (!preg_match("/^[a-z]*$/", $product_auto_membership)) {
        $feedback = array('status' => 0, 'msg' => 'Confira os dados do produto.', 'title' => "Algo está errado");
        echo json_encode($feedback);
        exit;
    }

    $product_cookie_time = addslashes($_POST['tempo-cookie-produto']);
    if (!preg_match("/^[0-9]*$/", $product_cookie_time)) {
        $feedback = array('status' => 0, 'msg' => 'Confira os dados do produto.', 'title' => "Algo está errado");
        echo json_encode($feedback);
        exit;
    }

    $product_membership_type = addslashes($_POST['tipo-afiliacao']);
    if (!preg_match("/^[a-z]*$/", $product_membership_type)) {
        $feedback = array('status' => 0, 'msg' => 'Confira os dados do produto.', 'title' => "Algo está errado");
        echo json_encode($feedback);
        exit;
    }

} else {
    $product_membership_available = 'nao';
    $product_shop_visibility = 'nao';
    $product_commission = 0;
    $product_cookie_time = 0;
    $product_auto_membership = 'nao';
    $product_membership_type = 'nenhum';
}

$product_rating = 0;

/**
 * 
 * Cadastro de um novo produto
 * 
 * 
 */
if ($_POST['action'] == 'new-product'){

    # Geração do PRODUCT_CODE único
    $product_code = new RandomStrGenerator();
    $product_code = $product_code->onlyLetters(6);

    $verify_unique_product_code = $conn->prepare('SELECT * FROM products WHERE product_code = :product_code');
    $verify_unique_product_code->execute(array('product_code' => $product_code));

    if (!($verify_unique_product_code->rowCount() == 0)) {
        do {
            $product_code = new RandomStrGenerator();
            $product_code = $product_code->onlyLetters(6);

            $verify_unique_product_code = $conn->prepare('SELECT * FROM products WHERE product_code = :product_code');
            $verify_unique_product_code->execute(array('product_code' => $product_code));
        } while ($stmt->rowCount() != 0);
    }

    # Recebe e trata a imagem do produto.
	$product_image = 'default.jpg';
	if(isset($_FILES['product-image']) && $_FILES['product-image']['size'] > 0){
 
		$filetypes = array('jfif', 'png', 'jpeg', 'jpg');
		$image_filetype_array = explode('.', $_FILES['product-image']['name']);
	    $filetype = strtolower(end($image_filetype_array));

	    # Valida se a extensão do arquivo é aceita.
	    if (in_array($filetype, $filetypes) == false){
	   	   $feedback = array('status' => 0, 'msg' => 'A imagem do produto precisa estar em formato JPEG, PNG ou JFIF.', 'title' => "Algo está errado");
	       echo json_encode($feedback);
	       exit; 
		}

        # Renomeia e Faz o upload do arquivo.
		$new_name = date("Ymd-His") . '.' . $filetype;
		$dir = '../uploads/imagens/produtos/';
		if (move_uploaded_file($_FILES['product-image']['tmp_name'], $dir.$new_name)){

		} else {
			$feedback = array('status' => 0, 'msg' => 'Não deu! Erro ao fazer upload da imagem do produto!', 'title' => "Algo está errado");
			echo json_encode($feedback);
			exit;
		}

	} else {
		$feedback = array('status' => 0, 'msg' => 'Você precisa selecionar a imagem do produto!', 'title' => "Algo está errado"); 
		echo json_encode($feedback);
		exit;
	}

    # Prepara a query para inserir os dados no DB.
	$stmt = $conn->prepare('INSERT INTO products (product_id, product_code, user__id, product_name, product_price, product_description, product_image, product_trash, product_shipping_tax, product_categories, product_sale_page, product_warranty_time, product_membership_available, product_rating, product_shop_visibility, product_commission, product_auto_membership, product_membership_type, product_cookie_time ) VALUES (:product_id, :product_code, :user__id, :product_name, :product_price, :product_description, :product_image, :product_trash, :product_shipping_tax, :product_categories, :product_sale_page, :product_warranty_time, :product_membership_available, :product_rating, :product_shop_visibility, :product_commission, :product_auto_membership, :product_membership_type, :product_cookie_time)');

	# Prepara a query que busca o ID do produto logo a após a inserção.
    $get_last_id = $conn->prepare('SELECT product_id FROM products ORDER BY product_id DESC LIMIT 1');
    

	try {
        # Executa a inserção dos dados do novo produto no DB.
		$stmt->execute(array('product_id' => $product_id, 'product_code' => $product_code, 'user__id' => $user__id, 'product_name' => $product_name, 'product_price' => $product_price, 'product_description' => $product_description, 'product_image' => $new_name, 'product_trash' => $product_trashed, 'product_shipping_tax' => $product_shipping_tax, 'product_categories' => $product_categories, 'product_sale_page' => $product_sale_page, 'product_warranty_time' => $product_warranty_time, 'product_membership_available' => $product_membership_available, 'product_rating' => $product_rating, 'product_shop_visibility' => $product_shop_visibility, 'product_commission' => $product_commission, 'product_auto_membership' => $product_auto_membership, 'product_membership_type' => $product_membership_type, 'product_cookie_time' => $product_cookie_time));

        # Pega o ID do produto inserido.
		$get_last_id->execute();
		while($row = $get_last_id->fetch()) {
			$product_id = $row['product_id'];
		}

        $url = SERVER_URI . "/produto/" . $product_id;

        # Retorna o feeback positivo.
		$feedback = array('status' => 1, 'title' => 'Produto cadastrado!', 'msg' => 'Agora você pode criar ofertas e cupons.', 'url' => $url);
		echo json_encode($feedback);

      } catch(PDOException $e) {

        # Retorna o feeback de erro.
        $error = 'ERROR: ' . $e->getMessage();
		$feedback = array('status' => 0, 'msg' => $error);
		echo json_encode($feedback);
      }

}




/**
 * 
 * Atualização de produto já cadastrado.
 * 
 * 
 */
else if ($_POST['action'] == 'update-product'){

    # Recebe e trata o ID do produto.
    $product_id = addslashes($_POST['produto']);

    # Verifica se o produto já tem um PRODUCT_CODE
    $verify_unique_product_code = $conn->prepare('SELECT product_code FROM products WHERE product_id = :product_id');
    $verify_unique_product_code->execute(array('product_id' => $product_id));
    $product_code = $verify_unique_product_code->fetch();

    # Se não tiver PRODUCT_CODE, gerar um.
    if ($product_code[0] == null || empty($product_code[0])) {

        $product_code = new RandomStrGenerator();
        $product_code = $product_code->onlyLetters(6);

        $verify_unique_product_code = $conn->prepare('SELECT * FROM products WHERE product_code = :product_code');
        $verify_unique_product_code->execute(array('product_code' => $product_code));

        if (!($verify_unique_product_code->rowCount() == 0)) {
            do {
                $product_code = new RandomStrGenerator();
                $product_code = $product_code->onlyLetters(6);

                $verify_unique_product_code = $conn->prepare('SELECT * FROM products WHERE product_code = :product_code');
                $verify_unique_product_code->execute(array('product_code' => $product_code));
            } while ($stmt->rowCount() != 0);
        }

        // $feedback = array('status' => 0, 'title' => $product_code, 'msg' => 'This is a NEW product_code.');
        // echo json_encode($feedback);
        // exit; 
    } 

    # Se já tiver PRODUCT_CODE, não altera.
    else {
        $product_code = $product_code[0];

        // $feedback = array('status' => 0, 'title' => $product_code, 'msg' => 'This is a current product_code.');
        // echo json_encode($feedback);
        // exit; 
    }

    # Atualização de produto COM alteração de imagem.
	if(isset($_FILES['product-image']) && $_FILES['product-image']['size'] > 0){
 
		$filetypes = array('webp' ,'png', 'jpeg', 'jpg');
		$image_filetype_array = explode('.', $_FILES['product-image']['name']);
	    $filetype = strtolower(end($image_filetype_array));

	    # Valida se a extensão do arquivo é aceita.
	    if (array_search($filetype, $filetypes) == false){
	   	   $feedback = array('status' => 0, 'title' => "Erro." , 'msg' => 'A imagem do produto precisa estar no JPEG, PNG ou WEBP.');
	       echo json_encode($feedback);
	       exit; 
		}

        # Renomeia e Faz o upload do arquivo.
		$new_name = date("Ymd-His") . '.' . $filetype;
		$dir = '../uploads/imagens/produtos/';
		if (move_uploaded_file($_FILES['product-image']['tmp_name'], $dir.$new_name)){
			
		} else {
			$feedback = array('status' => 0, 'msg' => 'Não deu! Erro ao fazer upload da imagem do produto!');
			echo json_encode($feedback);
			exit;
		}

        # Prepara a query.
		$stmt = $conn->prepare('UPDATE products SET product_name = :product_name, product_price = :product_price, product_description = :product_description, product_image = :product_image, product_categories = :product_categories, product_sale_page = :product_sale_page, product_warranty_time = :product_warranty_time, product_rating = :product_rating, product_code = :product_code WHERE product_id = :product_id');

		try {
			$stmt->execute(array('product_name' => $product_name, 'product_price' => $product_price, 'product_description' => $product_description, 'product_image' => $new_name, 'product_categories' => $product_categories, 'product_sale_page' => $product_sale_page, 'product_warranty_time' => $product_warranty_time, 'product_rating' => $product_rating, 'product_id' => $product_id, 'product_code' => $product_code));

            $url = SERVER_URI . "/produto/". $product_id ."/";

			$feedback = array('status' => '1', 'title' => 'Produto Atualizado!', 'product_id' => $product_id, 'url' => $url);

		} catch(PDOException $e) {
			$error = 'ERROR: ' . $e->getMessage();
			$feedback = array('status' => '0', 'msg' => $error);
		}
 
	echo json_encode($feedback);

	} else {

        # Atualização de produto SEM alteração de imagem.
		$stmt = $conn->prepare('UPDATE products SET product_name = :product_name, product_price = :product_price, product_description = :product_description, product_categories = :product_categories, product_sale_page = :product_sale_page, product_warranty_time = :product_warranty_time, product_code = :product_code, product_rating = :product_rating WHERE product_id = :product_id');

	try {
		$stmt->execute(array('product_name' => $product_name, 'product_price' => $product_price, 'product_description' => $product_description, 'product_categories' => $product_categories, 'product_sale_page' => $product_sale_page, 'product_warranty_time' => $product_warranty_time, 'product_code' => $product_code, 'product_rating' => $product_rating, 'product_id' => $product_id));

        $url = SERVER_URI . "/produto/". $product_id ."/";

		$feedback = array('status' => 1, 'title' => 'Produto Atualizado!', 'product_id' => $product_id, 'url' => $url);

      } catch(PDOException $e) {
        $error = 'ERROR: ' . $e->getMessage();
		$feedback = array('status' => '0', 'msg' => $error);
      }

	  echo json_encode($feedback);
	}

}
else {
	$feedback = array('status' => 0, 'msg' => 'Algo está errado! Atualize a página e tente novamente.'); 
	echo json_encode($feedback);
	exit;
}