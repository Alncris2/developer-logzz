<?php

require_once(dirname(__FILE__) . '/../includes/config.php');
session_name(SESSION_NAME);
session_start();


if ($_POST['action'] == 'new-operation') {
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

    $address  = $rua . ", nº " . $numero . "<br>";
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

        while ($row = $get_last_id->fetch()) {
            $operation_id = $row['operation_id'];
        }

        $cidades_operacao = explode(",", $cidades_operacao);
        foreach ($cidades_operacao as $city) {
            $add_cities = $conn->prepare('INSERT INTO operations_locales (operation_id, city) VALUES (:operation_id, TRIM(:city))');
            $add_cities->execute(array('operation_id' => $operation_id, "city" => $city));
        }

        $get_orders_list = $conn->prepare("SELECT o.order_id, o.client_address FROM orders o LEFT JOIN local_operations_orders l ON o.order_id = l.order_id WHERE l.order_id IS NULL AND o.client_address LIKE :uf ORDER BY o.order_id");
        $get_orders_list->execute(array('uf' => "%" . $estado_operacao . "%"));

        // Realiza o relacionamento da nova operação local com os pedidos de seu estado
        while ($row = $get_orders_list->fetch()) {
            $address_exploded = explode("<br>", $row['client_address']);
            $location = explode(", ", $address_exploded[3]);

            if (in_array($location[0], $cidades_operacao) && $location[1] == $estado_operacao) {
                $add_to_local_operation = $conn->prepare('INSERT INTO local_operations_orders(operation_id, order_id) VALUES (:operation_id, :order_id)');
                $add_to_local_operation->execute(array('operation_id' => $operation_id, 'order_id' => $row['order_id']));
            }
        }

        $feedback = array('status' => 1, 'msg' => 'Assinante Cadastrado!', 'url' => $url);
        echo json_encode($feedback);
        exit;
    } catch (PDOException $e) {

        $error = 'ERROR: ' . $e->getMessage();
        $feedback = array('status' => 0, 'msg' => $error);
        echo json_encode($feedback);
        exit;
    }
} else if ($_POST['action'] == 'update-operation') {

    $operation_id = addslashes($_POST['operation']);
    $nome_operacao = addslashes($_POST['nome-operacao']);
    $doc = addslashes($_POST['doc-destinatario']);
    $telefone = addslashes($_POST['telefone-destinatario']);

    $cep    =  addslashes($_POST['cep-operacao']);
    $rua    =  addslashes($_POST['endereco-operacao']);
    $numero   =  addslashes($_POST['numero-operacao']);
    $bairro   =  addslashes($_POST['bairro-operacao']);
    $cidade   =  addslashes($_POST['cidade-operacao']);
    $estado   =  addslashes($_POST['estado-operacao']);
    $referencia =  addslashes($_POST['referencia-operacao']);

    $doc2 = addslashes($_POST['doc-destinatario-2']);
    $telefone2 = addslashes($_POST['telefone-destinatario-2']);
    $cep2    =  addslashes($_POST['cep-operacao-2']);
    $rua2    =  addslashes($_POST['endereco-operacao-2']);
    $numero2   =  addslashes($_POST['numero-operacao-2']);
    $bairro2   =  addslashes($_POST['bairro-operacao-2']);
    $cidade2   =  addslashes($_POST['cidade-operacao-2']);
    $estado2   =  addslashes($_POST['estado-operacao-2']);
    $referencia2 =  addslashes($_POST['referencia-operacao-2']);

    $delivery_days = [];
    foreach ($_POST['delivery-days'] as $delivery_day) {
        $delivery_days[] = intval($delivery_day);
    }

    $delivery_days = json_encode($delivery_days);


    $address  = $rua . ", nº " . $numero . "<br>";
    $address .= $bairro . "<br>";
    $address .= $referencia . "<br>";
    $address .= $cidade . ", " . $estado . "<br>";
    $address .= "CEP: " . $cep;

    $address2  = $rua2 . ", nº " . $numero2 . "<br>";
    $address2 .= $bairro2 . "<br>";
    $address2 .= $referencia2 . "<br>";
    $address2 .= $cidade2 . ", " . $estado2 . "<br>";
    $address2 .= "CEP: " . $cep2;

    $estado_operacao = addslashes($_POST['uf-operacao']);
    $cidades_operacao = addslashes($_POST['cidades-operacao']);

    $estado_operacao_2 = addslashes($_POST['uf-operacao-2']);
    $cidades_operacao_2 = addslashes($_POST['cidades-operacao-2']);

    $update_operation = $conn->prepare('UPDATE local_operations SET operation_name=:nome_operacao, storage_address=:address, telefone=:telefone, destinatary_doc=:doc, uf=:uf, storage_address_2=:address_2, telefone_2=:telefone_2, destinatary_doc_2=:doc_2, operation_delivery_days=:operation_delivery_days WHERE operation_id=:operation_id');
    $get_last_id = $conn->prepare('SELECT operation_id FROM local_operations ORDER BY operation_id DESC LIMIT 1');

    try {

        $update_operation->execute(array('nome_operacao' => $nome_operacao, 'address' => $address, 'telefone' => $telefone, "doc" => $doc, "uf" => $estado_operacao, 'address_2' => $address2, 'telefone_2' => $telefone2, "doc_2" => $doc2, "operation_delivery_days" => $delivery_days, "operation_id" => $operation_id));

        // $reset_delivery_taxes = $conn->prepare("DELETE od.* FROM operations_delivery_taxes od INNER JOIN operations_locales ol ON ol.id = od.operation_locale WHERE ol.operation_id=:operation_id");
        // $reset_delivery_taxes->execute(array('operation_id' => $operation_id));

        // $reset_cities = $conn->prepare("DELETE FROM operations_locales WHERE operation_id=:operation_id");
        // $reset_cities->execute(array('operation_id' => $operation_id));

        // $cidades_operacao = explode(",", $cidades_operacao);

        // foreach ($cidades_operacao as $city) {
        //   $add_cities = $conn->prepare('INSERT INTO operations_locales (operation_id, city) VALUES (:operation_id, :city)');
        //   $add_cities->execute(array('operation_id' => $operation_id, "city" => $city));
        // }

        // $get_orders_list = $conn->prepare("SELECT o.order_id, o.client_address FROM orders o LEFT JOIN local_operations_orders l ON o.order_id = l.order_id WHERE l.order_id IS NULL AND o.client_address LIKE :uf ORDER BY o.order_id");
        // $get_orders_list->execute(array('uf' => "%" . $estado_operacao . "%"));

        // // Realiza o relacionamento da nova operação local com os pedidos de seu estado
        // while ($row = $get_orders_list->fetch()) {
        //   $address_exploded = explode("<br>", $row['client_address']);
        //   $location = explode(", ", $address_exploded[3]);

        //   if (in_array($location[0], $cidades_operacao) && $location[1] == $estado_operacao) {
        //     $add_to_local_operation = $conn->prepare('INSERT INTO local_operations_orders(operation_id, order_id) VALUES (:operation_id, :order_id)');
        //     $add_to_local_operation->execute(array('operation_id' => $operation_id, 'order_id' => $row['order_id']));
        //   }
        // }

        $feedback = array('status' => 1);
        echo json_encode($feedback);
        exit;
    } catch (PDOException $e) {
        $error = 'ERROR: ' . $e->getMessage();
        $feedback = array('status' => 0, 'msg' => $error);
        echo json_encode($feedback);
        exit;
    }
} else if ($_POST['action'] == 'update-cities-operation') {

    $operation_id = addslashes($_POST['operation']);

    $estado_operacao = addslashes($_POST['uf-operacao']);
    $cidades_operacao = addslashes($_POST['cidades-operacao']);

    $get_cities_local = $conn->prepare('SELECT city, id FROM operations_locales WHERE operation_id = :operation_id');
    $get_cities_local->execute(array('operation_id' => $operation_id));
    try {

        $cidades_operacao = explode(",", $cidades_operacao);

        $remove_id_city = $remove_id_operacao = array();

        if($cities = $get_cities_local->fetchALL()){

            for ($i = 0; $i < count($cities); $i++) { 
                for ($j = 0; $j < count($cidades_operacao); $j++) {
                    if($cities[$i]['city'] == $cidades_operacao[$j]){
                        array_splice($cities, $i, 1);                        
                        array_splice($cidades_operacao, $j, 1);
                        $i--;
                        $j--; 
                    }
                }
            }

            foreach ($cities as $delete_city) {                
                $reset_cities = $conn->prepare("DELETE FROM operations_locales WHERE id = :id");
                $reset_cities->execute(array('id' => $delete_city['id']));

                $reset_delivery_taxes = $conn->prepare("DELETE od.* FROM operations_delivery_taxes od INNER JOIN operations_locales ol ON ol.id = od.operation_locale WHERE ol.operation_id = :operation_id AND od.operation_locale = :id");
                $reset_delivery_taxes->execute(array('operation_id' => $operation_id, 'id' => $delete_city['id']));
            }

            foreach ($cidades_operacao as $city) {
                $add_cities = $conn->prepare('INSERT INTO operations_locales (operation_id, city) VALUES (:operation_id, TRIM(:city))');
                $add_cities->execute(array('operation_id' => $operation_id, "city" => $city));
            }   
        } else {
            foreach ($cidades_operacao as $city) {
                $add_cities = $conn->prepare('INSERT INTO operations_locales (operation_id, city) VALUES (:operation_id, TRIM(:city))');
                $add_cities->execute(array('operation_id' => $operation_id, "city" => $city));
            }
        }

        $feedback = array('status' => 1);
        echo json_encode($feedback);
        exit;

    } catch (PDOException $e) {
        $error = 'ERROR: ' . $e->getMessage();
        $feedback = array('status' => 0, 'msg' => $error);
        echo json_encode($feedback);
        exit;
    }
} else if ($_POST['action'] == 'update-availability-operation') {

    $operation_id = addslashes($_POST['operation']);    
    $delivery_days = [];
    foreach ($_POST['delivery-days'] as $delivery_day) {
        $delivery_days[] = intval($delivery_day);
    }

    $delivery_days = json_encode($delivery_days);
    $update_operation = $conn->prepare('UPDATE local_operations SET operation_delivery_days = :operation_delivery_days WHERE operation_id = :operation_id');

    try {
        $update_operation->execute(array("operation_delivery_days" => $delivery_days, "operation_id" => $operation_id));

        $feedback = array('status' => 1);
        echo json_encode($feedback);
        exit;

    } catch (PDOException $e) {

        $error = 'ERROR: ' . $e->getMessage();
        $feedback = array('status' => 0, 'msg' => $error);
        echo json_encode($feedback);
        exit;
    }
}
