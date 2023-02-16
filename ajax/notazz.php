<?php

require dirname(__FILE__) . "/../includes/config.php";

$name_send = addslashes($_POST['nameSend']);
$key_send  = addslashes($_POST['keySend']);
$note_send = (array) $_POST['noteSend'];
$user_send = (array) $_POST['userSend'];

$qtd_users = count($user_send);
$product_send = $_POST['productSend'];

$str = implode(",", array_map('intval', $product_send));

$qtd_produtos = count($product_send);
$verificar = $conn->prepare("SELECT name_integration FROM integration_notazz WHERE name_integration = ?");
$verificar->execute(array($name_send));

$note = implode(", ", $note_send);
$product = implode(", ", $product_send);
$user_id = implode(", ", $user_send);

$get_product_name = $conn->prepare("SELECT product_name, product_code FROM products WHERE product_id IN (" . $str . ")");
$get_product_name->execute();
$names = $get_product_name->fetchAll(\PDO::FETCH_ASSOC);

$string = "";
foreach($names as $name){
    $string .= " " . $name['product_name'] . "[" . $name['product_code'] . "]";
}

if ($verificar->rowCount() == 1) {
    echo json_encode(array("type" => "error", "title" => "Erro!", "icon" => "warning", "msg" => "Já existe uma integração com esse nome."));
    return;
}

$insert = $conn->prepare("INSERT INTO integration_notazz VALUES(null,?,?,?,?,?,?,?,?)");

try {
    $insert->execute(array($name_send, $key_send, $note, $qtd_users, $user_id, $product, $string, $qtd_produtos));
    echo json_encode(array("type" => "success", "title" => "Integração adicionada", "icon" => "success"));
} catch (Exception $e) {
    echo 'Erro ao inserir: ',  $e->getMessage(), "\n";
}
