<?php
require (dirname(__FILE__)) . "/../includes/config.php";
session_name(SESSION_NAME);
session_start();

if (!(isset($_POST))){
	exit;
}

$to_id = (int) filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
$to_status = (int) filter_var($_POST['status'], FILTER_SANITIZE_NUMBER_INT);
$msgRecuse = isset($_POST['text-recuse']) ? addslashes($_POST['text-recuse']) : NULL;

$query_to_update_status_product = "UPDATE products SET status = :status, last_recuse_text = :last_recuse_text WHERE product_id = :product_id";
$stmt = $conn->prepare($query_to_update_status_product);
try {

  $isSuccess = $stmt->execute([
    'status' => $to_status,
    'product_id' => $to_id,
    'last_recuse_text' => $msgRecuse
  ]);

  if($isSuccess){
    if($to_status == 0){
      echo json_encode(['status' => 1, 'msg' => "Novo pedido de aprovação enviado!"]);
      exit;
    }
    if($to_status == 1){
      echo json_encode(['status' => 1, 'msg' => "Status do produto atualizado com sucesso!"]);
      exit;
    }
    if($to_status == 2){
      echo json_encode(['status' => 1, 'msg' => "O cliente foi informado do motivo da recusa.", "title" => "O produto foi recusado!"]);
      exit;
    }
  }else{
    echo json_encode(['status' => 0, 'msg' => "Desculpe o erro foi nosso. tente novamente mais tarde"]);
    exit;
  }
} catch (\Exception $e) {
  var_dump('oi3');

  echo json_encode(['status' => 0, 'msg' => "Falha ao atualizar o status do produto", 'error' => $e]);
  exit;
}
