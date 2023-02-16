<?php 
    
require dirname(__FILE__) . "/../includes/config.php";
session_name(SESSION_NAME);
session_start();

# Verifica o envio do form via POST
if (!(isset($_POST))){
	exit;
}

$user = addslashes($_POST['select-user-val']);
if (!preg_match("/^[(0-9)]*$/", $user)) {
    $feedback = array('status' => 0, 'msg' => 'Experimente atualizar a página e reiniciar a seleção do usuário', 'title' => "Algo está errado1", 'type' => 'error');
	echo json_encode($feedback);
	exit;
}

$product = addslashes($_POST['select-product-val']);
if (!preg_match("/^[(0-9)]*$/", $product)) {
    $feedback = array('status' => 0, 'msg' => 'Experimente atualizar a página e reiniciar a seleção do  produto.', 'title' => "Algo está errado", 'type' => 'error');
	echo json_encode($feedback);
	exit;
}

$qtd = addslashes($_POST['qtd-user-text']);
if (!preg_match("/^[(0-9)]*$/", $qtd)) {
    $feedback = array('status' => 0, 'msg' => 'Experimente atualizar a página e reiniciar a seleção da quantidade.', 'title' => "Algo está errado", 'type' => 'error');
	echo json_encode($feedback);
	exit;
}

$operation = addslashes($_POST['typeOperation']);
if (!preg_match("/^[(A-Z)]*$/", $operation)) {
    $feedback = array('status' => 0, 'msg' => 'Experimente atualizar a página e reiniciar a seleção da localidade', 'title' => "Algo está errado", 'type' => 'error');
	echo json_encode($feedback);
	exit;
} 

$get_locale = explode('+', $_POST['select-locale-val']);
$id_locale = $get_locale[0];
$type_locale = $get_locale[1];

$nid_locale =  $id_locale;
$ntype_locale =  $get_locale[1] == "OPL" ? '0' : '1';
$inventory_last_ship = date('Y-m-d H:i:s');

// PEGAR QUANTIDADE DE ESTOQUE DO PRODUTO EM DETERMINADA LOCALIDADE
$get_inventory = $conn->prepare('SELECT * FROM inventories WHERE inventory_product_id = :product_id AND inventory_locale_id = :inventory_locale_id AND ship_locale = :ship_locale ORDER BY inventory_id DESC');
$get_inventory->execute(array('product_id' => $product, 'inventory_locale_id' => $nid_locale, 'ship_locale' => $ntype_locale));
$inventories = $get_inventory->fetch();

if($get_inventory->rowCount() > 0){
    $qtd_inventory = (int) $inventories['inventory_quantity'];
    $last_ship = (int) $qtd;
    

    if($operation == 'SUM'){
        // // adicionar valor 
        $new_value = $qtd_inventory + $qtd;
        $query_to_update = "UPDATE inventories SET inventory_quantity = :new_value, inventory_last_ship_quant = :inventory_last_ship_quant, inventory_last_ship = :inventory_last_ship WHERE inventory_product_id = :product_id AND inventory_locale_id = :inventory_locale_id AND ship_locale = :ship_locale";
        $stmt = $conn->prepare($query_to_update);
        $isSuccess = $stmt->execute(array('new_value' => $new_value, 'inventory_last_ship_quant' => $last_ship, 'product_id' => $product, 'inventory_locale_id' => $nid_locale, 'ship_locale' => $ntype_locale, 'inventory_last_ship' => $inventory_last_ship ));
        
        $feedback = array('status' => 1, 'msg' => "O valor de " . $qtd . " foi adicionado!", 'title' => "Valor adicionado!", 'type' => 'success');
    	echo json_encode($feedback);
    	exit;
    }
    
    if($operation == 'SUB'){
        // subtrair valor
        $qtd_inventory <= $qtd ? $new_value = 0 : $new_value = ($qtd_inventory - $qtd);
        
        $query_to_update = "UPDATE inventories SET inventory_quantity = :new_value WHERE inventory_product_id = :product_id AND inventory_locale_id = :inventory_locale_id AND ship_locale = :ship_locale";
        $stmt = $conn->prepare($query_to_update);
        $isSuccess = $stmt->execute(array('new_value' => $new_value, 'product_id' => $product, 'inventory_locale_id' => $nid_locale, 'ship_locale' => $ntype_locale));
        
        $feedback = array('status' => 1, 'msg' => "O valor de " . $qtd . " foi subtraído!", 'title' => 'Valor subtraído!', 'type' => 'success');
    	echo json_encode($feedback);
    	exit;
    }
}else{
    if($operation == 'SUM'){
        $meta = $user . "-". $product . "-" . $nid_locale;
        $insert_query = "INSERT INTO inventories (inventory_meta, inventory_user_id, inventory_product_id, inventory_locale_id, inventory_quantity, ship_locale, inventory_last_ship) VALUES (:inventory_meta, :inventory_user_id, :inventory_product_id, :inventory_locale_id, :inventory_quantity, :ship_locale, :inventory_last_ship)";
        $stmt = $conn->prepare($insert_query);
        $stmt->execute(array('inventory_meta' => $meta, 'inventory_user_id' => $_SESSION['UserID'], 'inventory_product_id' => $product, 'inventory_locale_id' => $nid_locale, 'inventory_quantity' => $qtd, 'ship_locale' => $ntype_locale, 'inventory_last_ship' => $inventory_last_ship));
        
        $feedback = array('status' => 1, 'msg' => "O valor de " . $qtd . " foi adicionado!", 'title' => 'Valor adicionado!', 'type' => 'success');
    	echo json_encode($feedback);
    	exit;    
    }else{
        $feedback = array('status' => 0, 'msg' => "O usuário não tem estoque cadastrado nessa localidade para ser subtraído", 'type' => 'error');
    	echo json_encode($feedback);
    	exit;  
    }
    
}

