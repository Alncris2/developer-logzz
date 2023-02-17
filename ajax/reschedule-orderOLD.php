<?php

    require "../includes/config.php";

    $data 					= addslashes($_POST['data-pedido']);
    $delivery_period 		= addslashes($_POST['periodo-pedido']);
    $order_id 				= addslashes($_POST['order']);

    if ($data == "Data" || empty($data)){
        $feedback = array('title' => 'Erro!', 'msg' => 'Informe nova data e período.', 'type' => 'warning');
        echo json_encode($feedback);
        exit;
    }

    $order_deadline = pickerDateFormate($data);

    // PEGAR QUANTIDADE DO PEDIDO E STATUS ANTERIOR
    $query = $conn->prepare("SELECT o.order_status, o.user__id,  s.sale_quantity, o.product_id FROM orders AS o INNER JOIN sales AS s WHERE o.sale_id = s.sale_id AND o.order_id = :order_id");
    $query->execute(['order_id' => $order_id]);
    $order_info = $query->fetch(\PDO::FETCH_ASSOC);

    if($order_info['order_status'] == 3){ // STATUS ERA COMPLETO
        // PEGAR ID DA LOCALIDADE DO PEDIDO
        $get_order_operation = $conn->prepare("SELECT * FROM local_operations_orders lo INNER JOIN local_operations loo ON lo.operation_id=loo.operation_id WHERE order_id=:order_id");
        $get_order_operation->execute(array("order_id" => $order_id));
        $local_operations = $get_order_operation->fetch();

        if(!$local_operations){
            $feedback = array('title' => '', 'msg' => "Não existe estoque do produto para a cidade correspondente ao CEP inserido.", 'type' => 'error');
            echo json_encode($feedback);
            exit;
        }

        // PEGAR SALDO ATUAL DO FINANCEIRO
        $query = $conn->prepare("SELECT meta_value FROM transactions_meta AS t WHERE t.user__id = :user__id AND meta_key = 'anticipation_balance'");
        $query->execute(['user__id' => $order_info['user__id']]);
        $actual_value = $query->fetch(\PDO::FETCH_ASSOC)['meta_value'];

        $new_value_meta = $actual_value - $order_infos['order_liquid_value'];
        
        // REMOVER SALDO DO FINANCEIRO
        $query = $conn->prepare("UPDATE transactions_meta AS t SET meta_value = :new_value_meta WHERE t.user__id = :user__id AND meta_key = 'anticipation_balance'");
        $query->execute([
            'new_value_meta' => $new_value_meta,
            'user__id' => $order_info['user__id']
        ]);	
        
        // GERAR INVENTORY META PARA AS CONSULTAS 
        $inventory_meta = $order_info['user__id'] . "-" . $order_info['product_id'] . "-" . $local_operations['operation_id'];

        // PEGAR QUANTIDADE ATUAL NO INVENTÁRIO 
        $query = $conn->prepare("SELECT inventory_quantity FROM inventories AS i WHERE i.inventory_meta = :inventory_meta AND i.ship_locale = 0");
        $query->execute(['inventory_meta' => $inventory_meta]);
        $inventory_qtd = $query->fetch(\PDO::FETCH_ASSOC)['inventory_quantity'];

        
        // ACRESENTAR QUANTIDADE NOVAMENTE AO INVENTARIO
        $query = $conn->prepare("UPDATE inventories AS i SET i.inventory_quantity = :inventory_quantity WHERE i.inventory_meta = :inventory_meta AND i.ship_locale = 0");
        $query->execute([
            'inventory_quantity' => $inventory_qtd + $order_info['sale_quantity'],
            'inventory_meta' => $inventory_meta
        ]);

        $stmt = $conn->prepare('UPDATE orders SET order_status = :order_status WHERE order_id = :order_id');
        $stmt->execute(array('order_status' => 1, 'order_id' => $order_id));

    }else{
        $stmt = $conn->prepare('UPDATE orders SET order_status = :order_status WHERE order_id = :order_id');
        $stmt->execute(array('order_status' => 1, 'order_id' => $order_id));
    }

    $msg = "A entrega foi reagendada para " . date_format(date_create($order_deadline), "d/m/Y") . ".";

	$feedback = array('title' => 'Feito!', 'msg' => $msg, 'type' => 'success');
	echo json_encode($feedback);
	exit;


?>