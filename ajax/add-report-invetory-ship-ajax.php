<?php

require "../includes/config.php";
session_name(SESSION_NAME);
session_start();


if ($_POST['action'] == 'report-ship'){
    
    $locale = explode("+", addslashes($_POST['localidade']));
    
	
	$user__id 		= $_SESSION['UserID'];
	$locale_id		= $locale[0];
	$locale_type    = $locale[1] == 'CD' ? 1 : 0;
	$product_id		= addslashes($_POST['produto']);
	$ship_quantity	= addslashes($_POST['quantidade-enviada']);
	$ship_code		= addslashes($_POST['codigo-rastreio']);
	
	$ship_date 		= date("Y-m-d H:i:s");

	if(isset($_FILES['nota-fiscal']) && $_FILES['nota-fiscal']['size'] > 0){
 
		$filetypes = array('pdf' ,'png', 'jpeg', 'jpg');
		$image_filetype_array = explode('.', $_FILES['nota-fiscal']['name']);
	    $filetype = strtolower(end($image_filetype_array));

	    // Valida se a extensão do arquivo é aceita
	    if (in_array($filetype, $filetypes) == false){
	   	   $feedback = array('status' => 0, 'msg' => 'O arquivo da Nota Fiscal precisa estar no formato PDF, PNG ou JPG.');
	       echo json_encode($feedback);
	       exit; 
		}
		

		$new_name = date("Ymd-His") . '.' . $filetype; //Definindo um novo nome para o arquivo
		$dir = '../uploads/envios/notas/'; //Diretório para uploads 
		if (move_uploaded_file($_FILES['nota-fiscal']['tmp_name'], $dir.$new_name)){
		

		} else {
			$feedback = array('status' => 0, 'msg' => 'Não deu! Erro ao fazer upload do arquivo da Nota Fiscal!');
			echo json_encode($feedback);
			exit;
		}

		$stmt = $conn->prepare('INSERT INTO shipments (ship_id, ship_user_id, ship_product_id, ship_locale_id, ship_quantity, ship_track_code, ship_date, ship_status, ship_invoice, type_shipments) VALUES (:ship_id, :ship_user_id, :ship_product_id, :ship_locale_id, :ship_quantity, :ship_track_code, :ship_date, :ship_status, :ship_invoice, :type_shipments)');
	
		try {
			$stmt->execute(array('ship_id' => 0, 'ship_user_id' => $user__id, 'ship_product_id' => $product_id, 'ship_locale_id' => $locale_id, 'ship_quantity' => $ship_quantity, 'ship_track_code' => $ship_code, 'ship_date' => $ship_date, 'ship_status'  => 0, 'ship_invoice' => $new_name, 'type_shipments' => $locale_type));

			$feedback = array('status' => 1, 'msg' => 'Envio confirmado!');
			echo json_encode($feedback);
			exit;
			
		} catch(PDOException $e) {
			$error = 'ERROR: ' . $e->getMessage();
			$feedback = array('status' => 0, 'msg' => $error);
			echo json_encode($feedback);
			exit;
		}
 
	echo json_encode($feedback);

	} else {


	$stmt = $conn->prepare('INSERT INTO shipments (ship_id, ship_user_id, ship_product_id, ship_locale_id, ship_quantity, ship_track_code, ship_date, ship_status, type_shipments) VALUES (:ship_id, :ship_user_id, :ship_product_id, :ship_locale_id, :ship_quantity, :ship_track_code, :ship_date, :ship_status, :type_shipments)');
	
	try {
		$stmt->execute(array('ship_id' => 0, 'ship_user_id' => $user__id, 'ship_product_id' => $product_id, 'ship_locale_id' => $locale_id, 'ship_quantity' => $ship_quantity, 'ship_track_code' => $ship_code, 'ship_date' => $ship_date, 'ship_status'  => 0, 'type_shipments' => $locale_type));

		$feedback = array('status' => 1, 'msg' => 'Envio confirmado!');
		echo json_encode($feedback);
		exit;
		
      } catch(PDOException $e) {
        $error = 'ERROR: ' . $e->getMessage();
		$feedback = array('status' => 0, 'msg' => $error);
		echo json_encode($feedback);
		exit;
	}

} 

}


else {
	$feedback = array('status' => 0, 'msg' => 'Erro interno! Não foi possível processar sua solicitação.');
	echo json_encode($feedback);
	exit;
}

?>