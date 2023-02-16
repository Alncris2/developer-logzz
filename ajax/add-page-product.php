<?php

require (dirname(__FILE__)) . "/../includes/config.php";
session_name(SESSION_NAME);
session_start();

if (!(isset($_POST['action']))){
	exit;
}


if ($_POST['action'] == 'create-page'){
	
	$page_product_id 		= addslashes($_POST['produto']);
	$page_url 				= addslashes($_POST['link-page']);	
	$page_name				= strtoupper(addslashes($_POST['nome-page']));
	if (!preg_match("/^[a-z-A-Z-0-9 ]*$/", $page_name)) {
		$feedback = array('status' => 0, 'msg' => 'O texto da página só pode ter letras e números.', 'title' => "Confira o nome", 'type' => 'warning');
		echo json_encode($feedback);
		exit;
	}

	
	try {
		$stmt = $conn->prepare('INSERT INTO pages_sales (page_product_id, page_name, page_url) VALUES (:page_product_id, :page_name, :page_url)');
		$stmt->execute(array( 'page_product_id' => $page_product_id, 'page_name' => $page_name, 'page_url' => $page_url ));

		$feedback = array('status' => 1, 'msg' => "Página de venda criado com sucesso.", 'title' => 'Sucesso', 'type' => 'success');
		echo json_encode($feedback);
		exit;

      } catch(PDOException $e) {
        $error= 'ERROR: ' . $e->getMessage();
		$feedback = array('status' => 0, 'msg' => $error);
		echo json_encode($feedback);
      }


} else if ($_POST['action'] == 'update-page'){

	$page_id 				= addslashes($_POST['id-page']);
	$page_product_id 		= addslashes($_POST['produto']);
	$page_url 				= addslashes($_POST['link-page']);	
	$page_name				= addslashes($_POST['nome-page']);
	if (!preg_match("/^[a-z-A-Z-0-9 ]*$/", $page_name)) {
		$feedback = array('status' => 0, 'msg' => 'O texto da página só pode ter letras e números.', 'title' => "Confira o nome");
		echo json_encode($feedback);
		exit;
	}
	
	try {
		$stmt = $conn->prepare('UPDATE pages_sales SET page_product_id = :page_product_id, page_name = :page_name, page_url = :page_url WHERE page_id = :page_id');
		$stmt->execute(array( 'page_id' => $page_id, 'page_product_id' => $page_product_id, 'page_name' => $page_name, 'page_url' => $page_url ));

		$feedback = array('status' => 1, 'msg' => ":)", 'title' => 'Sucesso', 'type' => 'success');
		echo json_encode($feedback);
		exit;

    } catch(PDOException $e) {
        $error= 'ERROR: ' . $e->getMessage();
		$feedback = array('status' => 0, 'msg' => $error);
		echo json_encode($feedback);
    }
} else if ($_POST['action'] == 'delete-page'){

	$page_id 				= addslashes($_POST['page_id']);

	try {
		$stmt = $conn->prepare('DELETE FROM pages_sales WHERE page_id = :page_id');
		$stmt->execute(array( 'page_id' => $page_id ));

		$feedback = array('status' => 1, 'msg' => ":)", 'title' => 'Sucesso', 'type' => 'success');
		echo json_encode($feedback);
		exit;

    } catch(PDOException $e) {
        $error= 'ERROR: ' . $e->getMessage();
		$feedback = array('status' => 0, 'msg' => $error);
		echo json_encode($feedback);
    }
} else if ($_POST['action'] == 'get-page'){

	$page_id 				= addslashes($_POST['page_id']);

	try {
		$stmt = $conn->prepare('SELECT page_id, page_name, page_url FROM pages_sales WHERE page_id = :page_id');
		$stmt->execute(array( 'page_id' => $page_id ));

		if($stmt->rowCount() == 0){
			$feedback = array('status' => 0, 'msg' => "Algo está errado! Atualize a página e tente novamente!", 'title' => 'Página não encontrada', 'type' => 'success');
			echo json_encode($feedback);
			exit;
		}

		$data = $stmt->fetch();
		$feedback = array('status' => 1, 'type' => 'success', 'data' => $data);
		echo json_encode($feedback);
		exit;

    } catch(PDOException $e) {
        $error= 'ERROR: ' . $e->getMessage();
		$feedback = array('status' => 0, 'msg' => $error);
		echo json_encode($feedback);
    }
}

else {
	$feedback = array('status' => 0, 'msg' => 'Algo está errado! Atualize a página e tente novamente.',  'product_id' => $product_id); 
	echo json_encode($feedback);
	exit;
}
?>