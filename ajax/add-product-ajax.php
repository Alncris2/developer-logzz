<?php
// error_reporting(-1);            
// ini_set('display_errors', 1);     
require dirname(__FILE__) . "/../includes/config.php";
require (dirname(__FILE__) . '/../includes/classes/RandomStrGenerator.php');
require (dirname(__FILE__) . '/../includes/classes/compress_imagem/Compress.php');
 
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
$product_name = $_POST['nome-produto'];
// if (!preg_match("/^[a-zA-Z-À-ú0-9\/£$%^&*()}{:\'#~<>,;!@\|\-=\-_+\-¬\`\ ]*$/", $product_name)) {
//     $feedback = array('status' => 0, 'msg' => 'Confira o nome do produto.', 'title' => "Algo está errado");
//     echo json_encode($feedback);
//     exit;
// } else
 if (strlen($product_name) < 5) {
    $feedback = array('status' => 0, 'msg' => "O nome do produto é muito curto.", 'title' => "Algo está errado");
    echo json_encode($feedback);
    exit;
}

$product_description = $_POST['descricao-produto'];
if (empty($product_description) || strlen($product_description) == 0) {
    $feedback = array('status' => 0, 'msg' => 'Confira a descrição do produto.', 'title' => "Algo está errado");
    echo json_encode($feedback);
    exit;
}

$product_price = $_POST['preco-produto'];
if (!preg_match("/^[(0-9\,.) ]*$/", $product_price)) {
    $feedback = array('status' => 0, 'msg' => 'Confira o preço do produto.', 'title' => "Algo está errado");
	echo json_encode($feedback);
	exit;
} else if (empty($product_price) || $product_price == 0){
	$feedback = array('status' => 0, 'msg' => "Informe o preço de custo do produto.", 'title' => "Algo está errado");
	echo json_encode($feedback);
	exit;  
}

$product_price = floatval(addslashes(str_replace(',', '.', str_replace('.', '', $_POST['preco-produto']))));

$product_categories = addslashes($_POST['categoria-produto-select-text']);
    
if(empty($product_categories)){
    $feedback = array('status' => 0, 'msg' => 'Informe a categoria do produto.', 'title' => "Algo está errado");
    echo json_encode($feedback);
    exit;
}

if (!preg_match("/^[(0-9\,.) ]*$/", $product_categories)) {
    $feedback = array('status' => 0, 'msg' => 'Experimente atualizar a página e reiniciar o cadastro do produto.', 'title' => "Algo está errado");
    echo json_encode($feedback);
    exit;
}

$product_sale_page = addslashes($_POST['pagina-vendas-produto']);
$isAUrl = filter_var($product_sale_page, FILTER_VALIDATE_URL);

if($isAUrl == false){
    $feedback = array('status' => 0, 'msg' => 'Informe uma URL da página de vendas válida.', 'title' => "Algo está errado");
    echo json_encode($feedback);
    exit;
}

if(empty($product_sale_page)){
    $feedback = array('status' => 0, 'msg' => 'Informe a URL da página de vendas.', 'title' => "Algo está errado");
    echo json_encode($feedback);
    exit;
}

if (!(filter_var($product_sale_page, FILTER_SANITIZE_URL))) {
    $feedback = array('status' => 0, 'msg' => 'Informe corretamente a URL da página de vendas.', 'title' => "Algo está errado");
    echo json_encode($feedback);
    exit;
}

$product_warranty_time = addslashes($_POST['garantia-produto']);
if(empty($product_warranty_time)){
    $feedback = array('status' => 0, 'msg' => 'Informe o tempo de garantia do produto.', 'title' => "Algo está errado");
    echo json_encode($feedback);
    exit;
}

$kind_packing = addslashes(isset($_POST['kind-packing']) ? $_POST['kind-packing'] : '');
if(empty($kind_packing)){
    $feedback = array('status' => 0, 'msg' => 'Informe o tipo da embalagem do produto.', 'title' => "Algo está errado");
    echo json_encode($feedback);
    exit;
}

if (!preg_match("/^[(0-9)]*$/", $product_warranty_time )) {
    $feedback = array('status' => 0, 'msg' => 'Você informou o período de garantia corretamente?', 'title' => "Algo está errado");
    echo json_encode($feedback);
    exit;
}


# Recebe e trata os inputs referentes às configuações de Afiliação do produto.
// if (isset($_POST['disponivel-afiliacao']) && $_POST['disponivel-afiliacao'] == 'sim'){

//     $product_membership_available = addslashes($_POST['disponivel-afiliacao']);
//     if (!preg_match("/^[a-z]*$/", $product_membership_available)) {
//         $feedback = array('status' => 0, 'msg' => 'disponivel-afiliacao', 'title' => "Algo está errado");
//         echo json_encode($feedback);
//         exit;
//     }

//     $product_shop_visibility = addslashes($_POST['visivel-afiliacao']);
//     if (!preg_match("/^[a-z]*$/", $product_shop_visibility)) {
//         $feedback = array('status' => 0, 'msg' => 'Confira os dados do produto.', 'title' => "Algo está errado");
//         echo json_encode($feedback);
//         exit;
//     }

//     $product_commission = addslashes($_POST['comissao-produto']);
//     if (!preg_match("/^[0-9]*$/", $product_commission)) {
//         $feedback = array('status' => 0, 'msg' => 'Confira os dados do produto.', 'title' => "Algo está errado");
//         echo json_encode($feedback);
//         exit;
//     }

//     $product_auto_membership = addslashes($_POST['afiliacao-automatica']);
//     if (!preg_match("/^[a-z]*$/", $product_auto_membership)) {
//         $feedback = array('status' => 0, 'msg' => 'Confira os dados do produto.', 'title' => "Algo está errado");
//         echo json_encode($feedback);
//         exit;
//     }

//     $product_cookie_time = addslashes($_POST['tempo-cookie-produto']);
//     if (!preg_match("/^[0-9]*$/", $product_cookie_time)) {
//         $feedback = array('status' => 0, 'msg' => 'Confira os dados do produto.', 'title' => "Algo está errado");
//         echo json_encode($feedback);
//         exit;
//     }

//     $product_membership_type = addslashes($_POST['tipo-afiliacao']);
//     if (!preg_match("/^[0-9]*$/", $product_membership_type)) {
//         $feedback = array('status' => 0, 'msg' => 'Confira os dados do produto.', 'title' => "Algo está errado");
//         echo json_encode($feedback);
//         exit;
//     }

// } else {
// }
    $product_membership_available = 'nao';
    $product_shop_visibility = 'nao';
    $product_commission = 0;
    $product_cookie_time = 0;
    $product_auto_membership = 'nao';
    $product_membership_type = 'nenhum';

$product_weight = addslashes($_POST['weight-product']);
$product_packaging = addslashes($_POST['kind-packing']);

/**
 * 
 * Cadastro de um novo produto
 * 
 * 
 */
if ($_POST['action'] == 'new-product'){
    $product_rating = 0;

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
    
    if(!(isset($_FILES['product-image']) && $_FILES['product-image']['size'] > 0)){
        $feedback = array('status' => 0, 'msg' => 'Você precisa selecionar a imagem do produto!', 'title' => "Algo está errado"); 
        echo json_encode($feedback);
        exit;
    }
        
    $filetypes = array('png', 'jpg', 'jpeg', 'gif', 'jfif', 'webp', 'mp4', 'mkv');
    $image_filetype_array = explode('.', $_FILES['product-image']['name']);
    $filetype = strtolower(end($image_filetype_array));

    $maxSize = in_array($filetype, ['mp4', 'mkv']) ?  10000000 : 5000000; // 5MB para imagem 5MB para vídeo
    $imageSize = $_FILES['product-image']['size'];
    
    
    # Valida se a extensão do arquivo é aceita.
    if (!in_array($filetype, $filetypes)){
        # Se a extensão do arquivo não for aceita, verifica se é vídeo ou imagem pra definir a mensagem de error certa
        $feedback = in_array($filetype, ['mp4', 'mkv'])
            ? array('status' => 0, 'msg' => 'O vídeo do produto precisa estar em formato MP4 ou MKV.', 'title' => "Algo está errado")
            : array('status' => 0, 'msg' => 'A imagem do produto precisa estar em formato  PNG, JPG, JPEG, GIF ou JFIF.', 'title' => "Algo está errado");

        echo json_encode($feedback);
        exit; 
    }

    # Valida o tamanho da arquivo
    if ($imageSize > $maxSize) {
        $feedback = $feedback = in_array($filetype, ['mp4', 'mkv'])
            ? array('status' => 0, 'msg' => 'O vídeo do produto precisa ser menor que 5 Megabyte.', 'title' => "Algo está errado")
            : array('status' => 0, 'msg' => 'A imagem do produto precisa ser menor que 5 Megabyte.', 'title' => "Algo está errado");
        echo json_encode($feedback);
        exit; 
    }
    
    # Renomeia, Faz a compressão e o upload do arquivo.
    $product_image =  date("Ymd-His"). $product_code;
    $dir = '../uploads/imagens/produtos/';

    if (in_array($filetype, ['mp4', 'mkv'])) {
        $product_image = $product_image.'.'.$filetype;
        if (!move_uploaded_file($_FILES['product-image']['tmp_name'], $dir.$product_image)) {
            $feedback = array('status' => 0, 'msg' => 'Não deu! Erro ao fazer upload do vídeo do produto!', 'title' => "Algo está errado");
            echo json_encode($feedback);
            exit;
        }
    }

    if($filetype == 'gif' || $filetype == 'webp'){ 
        if (!move_uploaded_file($_FILES['product-image']['tmp_name'], $dir . $product_image .'.'. $filetype)){
            $feedback = array('title' => 'Erro', 'type' => 'warning', 'msg' => 'Não deu! Erro ao fazer upload da imagem do produto!');
            echo json_encode($feedback);
            exit;
        } 
        $product_image = $product_image .'.'. $filetype;
    } else {
        $img = Compress::shrink($_FILES['product-image']['tmp_name'], $dir, 'webp', $product_image);
        if ($img->error){
            $feedback = array('status' => 0, 'msg' => 'Não deu! Erro ao fazer upload da imagem do produto!', 'title' => "Algo está errado");
            echo json_encode($feedback);
            exit;
        }  
        $product_image = $img->image_name;
    }

    # Recebe e trata as imagens secundárias do produto.
    if(isset($_FILES['product-images']) && $_FILES['product-images']['size'][0] > 0){
        
        $filetypes = array('png', 'jpg', 'jpeg', 'gif', 'jfif', 'webp',);
        $new_image_names = [];

        $maxSize = 5000000; // 5MB

        # Valida todos imagens secundáris, faz o upload e grava o node delas para salvar no banco
        # Cria 9 registros de imagens no banco, caso tiver uma imagem no payload salva o nome,
        # caso não tiver o payload deixa vazio, tela de update mostra o placeholder, na tela do cliente não mostra
        for ($i=0; $i < 9; $i++) { 
            if (isset($_FILES['product-images']['name'][$i])) {
                $image_filetype_array = explode('.', $_FILES['product-images']['name'][$i]);
                $filetype = strtolower(end($image_filetype_array));

                $imageSize = $_FILES['product-images']['size'][$i];

                # Valida se a extensão do arquivo é aceita.
                if (in_array($filetype, $filetypes) == false){
                    $feedback = array('status' => 0, 'msg' => 'A imagem do produto precisa estar em formato JPEG, PNG ou JFIF.', 'title' => "Algo está errado");
                    echo json_encode($feedback);
                    exit; 
                }
            
                # Valida se a extensão do arquivo é aceita.
                if (in_array($filetype, $filetypes) == false){
                    $feedback = array('status' => 0, 'msg' => 'A imagem do produto precisa estar em formato JPEG, PNG ou JFIF.', 'title' => "Algo está errado");
                    echo json_encode($feedback);
                    exit; 
                }
        
                # Valida o tamanho da imagem
                if ($imageSize > $maxSize) {
                    $feedback = array('status' => 0, 'msg' => 'Cada imagem secundária do produto precisa ser menor que 5 Megabyte.', 'title' => "Algo está errado");
                    echo json_encode($feedback);
                    exit; 
                }
                
                # Renomeia, faz o upload dos arquivos e salva o nome delas para salvar no banco.
                $new_image_names[$i] =  'img_' . $i . '_' . date("Ymd-His");
                $dir = '../uploads/imagens/produtos/' . $product_code . '/';

                # Cria a pasta se ela não existir
                if (!file_exists($dir)) {
                    mkdir($dir , 0755, true);
                }

                if($filetype == 'gif' || $filetype == 'webp'){ 
                    $new_name = date("Ymd-His") . '__'.  $i .'_'  . $filetype;  
                    if (!move_uploaded_file($_FILES['product-images']['tmp_name'][$i], $dir.$new_name)){
                        $feedback = array('title' => 'Erro', 'type' => 'warning', 'msg' => 'Não deu! Erro ao fazer upload da imagem do produto!');
                        echo json_encode($feedback);
                        exit;
                    } 
                    $new_image_names[$i] = $product_code . '/' . $new_name;
                } else {
                    $img = Compress::shrink($_FILES['product-images']['tmp_name'][$i], $dir, 'webp', $new_image_names[$i]);
                    if ($img->error){
                        $feedback = array('status' => 0, 'msg' => 'Não deu! Erro ao fazer upload da imagem do produto!', 'title' => "Algo está errado");
                        echo json_encode($feedback);
                        exit;
                    }  
                    $new_image_names[$i] = $product_code . '/' . $img->image_name;
                }

            } else {
                $new_image_names[$i] =  '';
            }
        }   
    }  

    # Prepara a query para inserir os dados no DB.
    $stmt = $conn->prepare('INSERT INTO products (product_id, product_code, user__id, product_name, product_price, product_description, product_image, product_trash, product_shipping_tax, product_categories, product_sale_page, product_warranty_time, product_membership_available, product_rating, product_shop_visibility, product_commission, product_auto_membership, product_membership_type, product_cookie_time,status, type_packaging, product_weight) VALUES (:product_id, :product_code, :user__id, :product_name, :product_price, :product_description, :product_image, :product_trash, :product_shipping_tax, :product_categories, :product_sale_page, :product_warranty_time, :product_membership_available, :product_rating, :product_shop_visibility, :product_commission, :product_auto_membership, :product_membership_type, :product_cookie_time, :status, :type_packaging, :product_weight)');
    # Prepara a query para inserir o nome imagens no DB.
    $stmt_images = $conn->prepare('INSERT INTO products_images (product_id, product_image) VALUES (:product_id, :product_image)');

    # Prepara a query que busca o ID do produto logo a após a inserção.
    $get_last_id = $conn->prepare('SELECT product_id FROM products ORDER BY product_id DESC LIMIT 1');
    

    try {
        # Executa a inserção dos dados do novo produto no DB.
        $stmt->execute(array('product_id' => $product_id, 'product_code' => $product_code, 'user__id' => $user__id, 'product_name' => $product_name, 'product_price' => $product_price, 'product_description' => $product_description, 'product_image' => $product_image, 'product_trash' => $product_trashed, 'product_shipping_tax' => $product_shipping_tax, 'product_categories' => $product_categories, 'product_sale_page' => $product_sale_page, 'product_warranty_time' => $product_warranty_time, 'product_membership_available' => $product_membership_available, 'product_rating' => $product_rating, 'product_shop_visibility' => $product_shop_visibility, 'product_commission' => $product_commission, 'product_auto_membership' => $product_auto_membership, 'product_membership_type' => $product_membership_type, 'product_cookie_time' => $product_cookie_time, 'status' => 0, 'type_packaging' => $product_packaging , 'product_weight' => $product_weight));

        # Pega o ID do produto inserido.
        $get_last_id->execute();
        while($row = $get_last_id->fetch()) {
            $product_id = $row['product_id'];
        }

        $url = SERVER_URI . "/produto/" . $product_id;

        # Executa query para inserir imagens não obrigatorias no tabela products_images
        if (isset($new_image_names) && count($new_image_names) > 0) {
            for ($i=0; $i < count($new_image_names); $i++) { 
                $stmt_images->execute(array('product_id' => $product_id, 'product_image' => $new_image_names[$i],));
            }
        }

        # Retorna o feeback positivo.
        $feedback = array('status' => 1, 'title' => 'Produto cadastrado!', 'msg' => 'As demais informações sobre afiliação e personalização estarão disponíveis após a aprovação do produto.', 'url' => $url);
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

    $product_rating = 0;


    $product_weight = addslashes($_POST['weight-product']);
    $product_packaging = addslashes($_POST['kind-packing']);
    
    # Recebe e trata o ID do produto.
    $product_id = addslashes($_POST['produto']);

    # Verifica se o produto já tem um PRODUCT_CODE
    $verify_unique_product_code = $conn->prepare('SELECT product_code FROM products WHERE product_id = :product_id');
    $verify_unique_product_code->execute(array('product_id' => $product_id));
    $product_code = $verify_unique_product_code->fetch();

    # busca as imagens associadas ao produto
    $product_images_data = $conn->prepare('SELECT * FROM products_images WHERE product_id = :product_id');
    $product_images_data->execute(array('product_id' =>  $product_id));


    # se o produto não tiver imagens cadastradas no products_images cria os registros
    if ($product_images_data->rowCount() === 0) {
        $stmt_product_images = $conn->prepare('INSERT INTO products_images (product_id, product_image) VALUES (:product_id, :product_image)');
        for ($i=0; $i < 9; $i++) { 
            $stmt_product_images->execute(array('product_id' => $product_id, 'product_image' => ''));
        }
        $product_images_data->execute(array('product_id' =>  $product_id));
    }

    $old_product_images = [];
    while ($row = $product_images_data->fetch()) {
        $old_product_images[] = [
            'image_id' => $row['image_id'],
            'product_id' => $row['product_id'],
            'product_image' => $row['product_image'],
        ];
    }

    # Recebe e trata as imagens secundárias do produto para update.
    function updateSecundaryImages($old_product_images, $product_code) {

        $filetypes = array('png', 'jpg', 'jpeg', 'gif', 'jfif', 'webp',);
        $new_image_names = [];

        $maxSize = 5000000; // 5MB

        # Valida todos imagens secundáris, faz o upload e grava o node delas para salvar no banco
        # Cria 9 registros de imagens no banco, caso tiver uma imagem no payload salva o nome,
        # caso não tiver o payload deixa vazio, tela de update mostra o placeholder, na tela do cliente não mostra
        for ($i=0; $i < 9; $i++) { 
            # deleta as imagens antigas
            $path = $_SERVER['DOCUMENT_ROOT'] . '/uploads/imagens/produtos/' . $old_product_images[$i]['product_image'];
            unlink($path);

            # checa cada imagem recebida, se não veio vazio
            if (isset($_FILES['product-images']['name'][$i]) && $_FILES['product-images']['size'][$i] > 0) {
                $image_filetype_array = explode('.', $_FILES['product-images']['name'][$i]);
                $filetype = strtolower(end($image_filetype_array));

                $imageSize = $_FILES['product-images']['size'][$i];

                # Valida se a extensão do arquivo é aceita.
                if (in_array($filetype, $filetypes) == false){
                    $feedback = array('status' => 0, 'msg' => 'A imagem do produto precisa estar em formato JPEG, PNG ou JFIF.', 'title' => "Algo está errado");
                    echo json_encode($feedback);
                    exit; 
                }
            
                # Valida se a extensão do arquivo é aceita.
                if (in_array($filetype, $filetypes) == false){
                    $feedback = array('status' => 0, 'msg' => 'A imagem do produto precisa estar em formato JPEG, PNG ou JFIF.', 'title' => "Algo está errado");
                    echo json_encode($feedback);
                    exit; 
                }
        
                # Valida o tamanho da imagem
                if ($imageSize > $maxSize) {
                    $feedback = array('status' => 0, 'msg' => 'Cada imagem secundária do produto precisa ser menor que 5 Megabyte.', 'title' => "Algo está errado");
                    echo json_encode($feedback);
                    exit; 
                }

                # Renomeia, faz o upload dos arquivos e salva o nome delas para salvar no banco.
                $dir = '../uploads/imagens/produtos/' . $product_code . '/';

                # Cria a pasta se ela não existir
                if (!file_exists($dir)) {
                    mkdir($dir , 0755, true);
                }
                $new_image_names[$i] =  'img_' . $i . '_' . date("Ymd-His");

                if($filetype == 'gif'){
                    $new_name = date("Ymd-His") . '_gif_'.  $i .'_'  . $filetype; 
                    if (!move_uploaded_file($_FILES['product-images']['tmp_name'][$i], $dir.$new_name)){
                        $feedback = array('title' => 'Erro', 'type' => 'warning', 'msg' => 'Não deu! Erro ao fazer upload da imagem do produto!');
                        echo json_encode($feedback);
                        exit;
                    } 
                    $new_image_names[$i] = $product_code . '/' . $new_name;
                } else {
                    $img = Compress::shrink($_FILES['product-images']['tmp_name'][$i], $dir, 'webp', $new_image_names[$i]);
                    if ($img->error){
                        $feedback = array('status' => 0, 'msg' => 'Não deu! Erro ao fazer upload da imagem do produto!', 'title' => "Algo está errado");
                        echo json_encode($feedback);
                        exit;
                    }  
                    $new_image_names[$i] = $product_code . '/' . $img->image_name;
                }

                

            } else {
                # se a imagem veio vazia, define o nome como vario pra remover o caminho da antiga do banco
                $new_image_names[$i] =  '';
            }
        }        
        return $new_image_names;
    }

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

    # Atualização de produto COM alteração de imagem ou vídeo principal.
    if(isset($_FILES['product-image']) && $_FILES['product-image']['size'] > 0){

        # tipos de arquivos aceitos na imagem principal
        $filetypes = array('png', 'jpg', 'jpeg', 'gif', 'jfif', 'webp', 'mp4', 'mkv');
        $image_filetype_array = explode('.', $_FILES['product-image']['name']);
        $filetype = strtolower(end($image_filetype_array));

        $maxSize = in_array($filetype, ['mp4', 'mkv']) ?  10000000 : 5000000; // 5MB para vídeo ou 5MB para imagem 
        $imageSize = $_FILES['product-image']['size'];
        
        
        # Valida se a extensão do arquivo é aceita.
        if (!in_array($filetype, $filetypes)){
            # Se a extensão do arquivo não for aceita, verifica se é vídeo ou imagem pra definir a mensagem de error certa
            $feedback = in_array($filetype, ['mp4', 'mkv'])
                ? array('status' => 0, 'msg' => 'O vídeo do produto precisa estar em formato MP4 ou MKV.', 'title' => "Algo está errado")
                : array('status' => 0, 'msg' => 'A imagem do produto precisa estar em formato  PNG, JPG, JPEG, GIF ou JFIF.', 'title' => "Algo está errado");

            echo json_encode($feedback);
            exit; 
        }

        # Valida o tamanho da arquivo
        if ($imageSize > $maxSize) {
            $feedback = $feedback = in_array($filetype, ['mp4', 'mkv'])
                ? array('status' => 0, 'msg' => 'O vídeo do produto precisa ser menor que 5 Megabyte.', 'title' => "Algo está errado")
                : array('status' => 0, 'msg' => 'A imagem do produto precisa ser menor que 5 Megabyte.', 'title' => "Algo está errado");
            echo json_encode($feedback);
            exit; 
        }

       # Renomeia, Faz a compressão e o upload do arquivo.
        $product_image =  date("Ymd-His"). $product_code;
        $dir = '../uploads/imagens/produtos/';

        if (in_array($filetype, ['mp4', 'mkv'])) {
            $product_image = $product_image.'.'.$filetype; 
            if (!move_uploaded_file($_FILES['product-image']['tmp_name'], $dir.$product_image)) {
                $feedback = array('status' => 0, 'msg' => 'Não deu! Erro ao fazer upload do vídeo do produto!', 'title' => "Algo está errado");
                echo json_encode($feedback);
                exit;
            }
        }

        if($filetype == 'gif' || $filetype == 'webp'){
            if (!move_uploaded_file($_FILES['product-image']['tmp_name'], $dir . $product_image .'.'. $filetype)){
                $feedback = array('title' => 'Erro', 'type' => 'warning', 'msg' => 'Não deu! Erro ao fazer upload da imagem do produto!');
                echo json_encode($feedback);
                exit;
            } 
            $product_image = $product_image .'.'. $filetype; 
        } else {
            $img = Compress::shrink($_FILES['product-image']['tmp_name'], $dir, 'webp', $product_image);
            if ($img->error){
                $feedback = array('status' => 0, 'msg' => 'Não deu! Erro ao fazer upload da imagem do produto!', 'title' => "Algo está errado");
                echo json_encode($feedback);
                exit; 
            }  
            $product_image = $img->image_name;
        }

        # faz o update das imagens secundárias
        if ($_FILES['product-images']['name'][0]) {
            $updatedImagesNames = updateSecundaryImages($old_product_images, $product_code);
        }

        # Prepara a query pra imagem principal.
        $stmt = $conn->prepare('UPDATE products SET product_name = :product_name, product_price = :product_price, product_description = :product_description, product_image = :product_image, product_categories = :product_categories, product_sale_page = :product_sale_page, product_warranty_time = :product_warranty_time, product_rating = :product_rating, product_code = :product_code, type_packaging = :type_packaging, product_weight = :product_weight WHERE product_id = :product_id');

        # Prepara a querey para as imagens secundárias
        $stmt_images = $conn->prepare('UPDATE products_images SET product_image = :product_image WHERE image_id = :image_id');

        try {
            $stmt->execute(array('product_name' => $product_name, 'product_price' => $product_price, 'product_description' => $product_description, 'product_image' => $product_image, 'product_categories' => $product_categories, 'product_sale_page' => $product_sale_page, 'product_warranty_time' => $product_warranty_time, 'product_rating' => $product_rating, 'product_id' => $product_id, 'product_code' => $product_code, 'type_packaging' => $product_packaging, 'product_weight' => $product_weight));

            $url = SERVER_URI . "/produto/". $product_id ."/";

            # Executa query para inserir imagens não obrigatorias no tabela products_images
            if ($_FILES['product-images']['name'][0]) {
                for ($i=0; $i < 9; $i++) { 
                    $stmt_images->execute(array('image_id' => $old_product_images[$i]['image_id'], 'product_image' => $updatedImagesNames[$i],));
                }
            }

            $feedback = array('status' => '1', 'title' => 'Produto Atualizado!', 'product_id' => $product_id, 'url' => $url);
            echo json_encode($feedback);
        } catch(PDOException $e) {
            $error = 'ERROR: ' . $e->getMessage();
            $feedback = array('status' => '0', 'msg' => $error);
            echo json_encode($feedback);
        }

    } else {
        
        if ($_FILES['product-images']['name'][0]) {
            $updatedImagesNames = updateSecundaryImages($old_product_images, $product_code);
        }

        # Atualização de produto SEM alteração de imagem ou vídeo principal.
        $stmt = $conn->prepare('UPDATE products SET product_name = :product_name, product_price = :product_price, product_description = :product_description, product_categories = :product_categories, product_sale_page = :product_sale_page, product_warranty_time = :product_warranty_time, product_code = :product_code, product_rating = :product_rating, type_packaging = :type_packaging, product_weight = :product_weight WHERE product_id = :product_id');

        # Prepara a querey para as imagens secundárias
        $stmt_images = $conn->prepare('UPDATE products_images SET product_image = :product_image WHERE image_id = :image_id');

        try {
            $stmt->execute(array('product_name' => $product_name, 'product_price' => $product_price, 'product_description' => $product_description, 'product_categories' => $product_categories, 'product_sale_page' => $product_sale_page, 'product_warranty_time' => $product_warranty_time, 'product_code' => $product_code, 'product_rating' => $product_rating, 'type_packaging' => $product_packaging, 'product_weight' => $product_weight, 'product_id' => $product_id));

            # Executa query para inserir imagens não obrigatorias no tabela products_images
            if ($_FILES['product-images']['name'][0]) {
                for ($i=0; $i < 9; $i++) { 
                    $stmt_images->execute(array('image_id' => $old_product_images[$i]['image_id'], 'product_image' => $updatedImagesNames[$i],));
                }
            }

            $url = SERVER_URI . ( $_SESSION['UserPlan'] != 5 ? "/produto/" : "/produtos/todos/" ) . $product_id ."/";    
            

            $feedback = array('status' => 1, 'title' => 'Produto Atualizado!', 'product_id' => $product_id, 'url' => $url);
            echo json_encode($feedback);

        } catch(PDOException $e) {
            $error = 'ERROR: ' . $e->getMessage();
            $feedback = array('status' => '0', 'msg' => $error);
            echo json_encode($feedback);
        }

    }



} else {
    $feedback = array('status' => 0, 'msg' => 'Algo está errado! Atualize a página e tente novamente.'); 
    echo json_encode($feedback);
    exit;
} 