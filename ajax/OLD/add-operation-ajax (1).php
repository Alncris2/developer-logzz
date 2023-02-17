<?php

require_once(dirname(__FILE__) . '/../includes/config.php');
session_name(SESSION_NAME);
session_start();


if ($_POST['action'] == 'new-operation'){
  $nome_operacao = addslashes($_POST['nome-operacao']);
  $doc = addslashes($_POST['doc-destinatario']);
  $telefone = addslashes($_POST['telefone-destinatario']);

  $cep    =  addslashes($_POST['cep-operacao']);
  $rua    =  addslashes($_POST['endereco-operacao']);
  $numero   =  addslashes($_POST['numero-operacao']);
  $bairro   =  addslashes($_POST['bairro-operacao']);
  $cidade   =  addslashes($_POST['cidades-operacao']);
  $estado   =  addslashes($_POST['estado-operacao']);
  $referencia =  addslashes($_POST['referencia-operacao']);

  $address  = $rua . ", nº " . $numero ."<br>";
  $address .= "Bairro " . $bairro . "<br>";
  $address .= $referencia . "<br>";
  $address .= $cidade . ", " . $estado . "<br>";
  $address .= "CEP: " . $cep; 

  $estado_operacao = addslashes($_POST['uf-operacao']);
  $cidades_operacao = addslashes($_POST['cidades-operacao']);

	$stmt = $conn->prepare('INSERT INTO local_operations (operation_name, storage_address, telefone, destinatary_doc, uf) VALUES (:nome_operacao, :address, :telefone, :doc, :uf)');
  $get_last_id = $conn->prepare('SELECT operation_id FROM local_operations ORDER BY operation_id DESC LIMIT 1');
	try {

		$stmt->execute(array('nome_operacao' => $nome_operacao, 'address' => $address, 'telefone' => $telefone, 'doc' => $doc, 'uf' => $estado_operacao));
    $get_last_id->execute();

		while($row = $get_last_id->fetch()) {
			$operation_id = $row['operation_id'];
		}

    $cidades_operacao = explode(",", $cidades_operacao);
    foreach ($cidades_operacao as $city) {
      $add_cities = $conn->prepare('INSERT INTO operations_locales (operation_id, city) VALUES (:operation_id, :city)');
      $add_cities->execute(array('operation_id' => $operation_id, "city" => $city));
    }

    $get_orders_list = $conn->prepare("SELECT o.order_id, o.client_address FROM orders o LEFT JOIN local_operations_orders l ON o.order_id = l.order_id WHERE l.order_id IS NULL AND o.client_address LIKE :uf ORDER BY o.order_id");
    $get_orders_list->execute(array('uf' => "%" . $estado_operacao . "%"));
    
    // Realiza o relacionamento da nova operação local com os pedidos de seu estado
    while($row = $get_orders_list->fetch()) {
      $address_exploded = explode("<br>" , $row['client_address']);
      $location = explode(", ", $address_exploded[3]);

      if(in_array($location[0], $cidades_operacao) && $location[1] == $estado_operacao) {
        $add_to_local_operation = $conn->prepare('INSERT INTO local_operations_orders(operation_id, order_id) VALUES (:operation_id, :order_id)');
        $add_to_local_operation->execute(array('operation_id' => $operation_id, 'order_id' => $row['order_id']));
      }
		}

		$feedback = array('status' => 1, 'msg' => 'Assinante Cadastrado!', 'url' => $url);
		echo json_encode($feedback);
		exit;
    } catch(PDOException $e) {

    $error = 'ERROR: ' . $e->getMessage();
		$feedback = array('status' => 0, 'msg' => $error);
		echo json_encode($feedback);
		exit;
	}
}
?>
