<?php

require dirname(__FILE__) . "/../../includes/config.php";

session_name(SESSION_NAME);
session_start();

if (!(isset($_POST['action']))){
    exit;
}


$integration_id = 0;
$integration_name = addslashes($_POST['integration-name']);
$integration_type = 'external-postback';
$integration_platform = 'monetizze';
$integration_user_id = $_POST['integration_user_id'];
$integration_keys = addslashes($_POST['integration-unique-key']);
$integration_product_id = addslashes($_POST['integration-product-id']);
$integration_token_key = addslashes($POST['integration-token-key']);
$integration_status = 'active';

$get_product_name = $conn->prepare('SELECT product_name FROM products WHERE product_id = :product_id');
$get_product_name->execute(array('product_id' => $integration_product_id));

if ($get_product_name->rowCount() != 0) {
  $integration_product_name = $get_product_name->fetch();
  $integration_product_name = $integration_product_name['product_name'];

} else {
	$feedback = array('status' => 0, 'msg' => 'O produto selecionado é inválido ou não está cadastrado.', 'postback_url' => $postback_url);
	echo json_encode($feedback);
	exit;
}

	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	$random_string = str_shuffle($chars);
	$unique_string = substr($random_string, 1,16);

	$url = SERVER_URI . "/postback/monetizze/" . $unique_string; 

	$stmt = $conn->prepare('SELECT * FROM integrations WHERE integration_url = :_url');
	$stmt->execute(array('_url' => $url));

	if ($stmt->rowCount() != 0){

		do {
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
			$random_string = str_shuffle($chars);
			$unique_string = substr($random_string, 1,16);

			$url = SERVER_URI . "/postback/monetizze/" . $unique_string;

			$stmt = $conn->prepare('SELECT * FROM integrations WHERE integration_url = :_url');
			$stmt->execute(array('_url' => $url));
			
		} while ($stmt->rowCount() != 0);

	}
 
	$postback_url = $url;


if ($_POST['action'] == 'new-integration-monetizze'){

	$stmt = $conn->prepare('INSERT INTO integrations (integration_id, integration_name, integration_type, integration_platform, integration_product_id, integration_product_name, integration_user_id, integration_keys, integration_url, integration_status, integration_api_token) VALUES (:integration_id, :integration_name, :integration_type, :integration_platform, :integration_product_id, :integration_product_name, :integration_user_id, :integration_keys, :integration_url, :integration_status, :integration_api_token)');


	try {
		$stmt->execute(array('integration_id' => $integration_id, 'integration_name' => $integration_name, 'integration_type' => $integration_type, 'integration_platform' => $integration_platform, 'integration_product_id' => $integration_product_id, 'integration_product_name' => $integration_product_name, 'integration_user_id' => $integration_user_id, 'integration_keys' => $integration_keys, 'integration_url' => $postback_url, 'integration_status' => $integration_status, 'integration_api_token' => $integration_token_key));

        $feedback = array('status' => 1, 'msg' => 'URL de postback gerada com sucesso!', 'url' => $postback_url); 
    	echo json_encode($feedback);
    	exit; 

      } catch(PDOException $e) {
        $feedback = array('status' => 0, 'msg' => "Erro na geração da url!" . $e->getMessage()); 
    	echo json_encode($feedback);
        exit;
      }
}

else {
	$feedback = array('status' => 0, 'msg' => 'Algo está errado! Atualize a página e tente novamente.'); 
	echo json_encode($feedback);
	exit;
}

?>