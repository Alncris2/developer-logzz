<?php
require "includes/config.php";

$order_number = $_POST['order'];
$status = $_POST['status']

$order_number = str_replace('AFI', '', $order_number);
$queryN = $conn->prepare("SELECT order_id FROM orders  WHERE order_number = :order_number");
$queryN->execute(['order_number' => $order_number]);
$order_id = $queryN->fetch(\PDO::FETCH_ASSOC)["order_id"];

try {
    switch ($status) {
        case 10:
            $status_string = "Confirmado";

            // PEGAR QUANTIDADE DO PEDIDO E STATUS ANTERIOR
            $query = $conn->prepare("SELECT o.order_status, o.user__id,  s.sale_quantity, o.product_id, order_commission_released FROM orders AS o INNER JOIN sales AS s WHERE o.sale_id = s.sale_id AND o.order_id = :order_id");
            $query->execute(['order_id' => $order_number]);
            $order_info = $query->fetch(\PDO::FETCH_ASSOC);

            // PEGAR ID DA LOCALIDADE DO PEDIDO
            $get_order_operation = $conn->prepare("SELECT * FROM local_operations_orders lo INNER JOIN local_operations loo ON lo.operation_id=loo.operation_id WHERE order_id=:order_id");
            $get_order_operation->execute(array("order_id" => $order_id));
            $local_operations = $get_order_operation->fetch();

            if (!$local_operations) {
                $feedback = array('status' => 2, 'msg' => "Não existe estoque do produto para a cidade correspondente ao CEP inserido.", 'type' => 'warning');
                echo json_encode($feedback);
                exit;
            }

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


            $statement = $conn->prepare("UPDATE orders SET order_status = 10, order_status_description = 'Pedido confirmado pelo Usuário' WHERE  order_number = :order_number");
            $statement->execute(array("order_number" => $order_number));

            $statement = $conn->prepare("UPDATE orders SET order_status = 10, order_status_description = 'Pedido confirmado pelo Usuário' WHERE  order_number = :order_number");
            $statement->execute(array("order_number" => 'AFI' . $order_number));

            $msg = "Status do pedido foi alterado para " . $status_string . ".";
            $feedback = array('type' => 'success', 'msg' => $msg, 'title' => "Feito!");
            echo json_encode($feedback);
            exit;
    
            break;
        case 5:
            $status_string = "Cancelado";

            // PEGAR QUANTIDADE DO PEDIDO E STATUS ANTERIOR
            $query = $conn->prepare("SELECT o.order_status, o.user__id,  s.sale_quantity, o.product_id FROM orders AS o INNER JOIN sales AS s WHERE o.sale_id = s.sale_id AND o.order_id = :order_id");
            $query->execute(['order_id' => $order_id]);
            $order_info = $query->fetch(\PDO::FETCH_ASSOC);

            if ($order_info['order_status'] == 3) { // STATUS ERA COMPLETO
                // PEGAR ID DA LOCALIDADE DO PEDIDO
                $get_order_operation = $conn->prepare("SELECT * FROM local_operations_orders lo INNER JOIN local_operations loo ON lo.operation_id=loo.operation_id WHERE order_id=:order_id");
                $get_order_operation->execute(array("order_id" => $order_id));
                $local_operations = $get_order_operation->fetch();


                if (!$local_operations) {
                    $feedback = array('status' => 2, 'msg' => "Não existe estoque do produto para a cidade correspondente ao CEP inserido.");
                    echo json_encode($feedback);
                    exit;
                }

                // PEGAR SALDO ATUAL DO FINANCEIRO DO PRODUTOR
                $query = $conn->prepare("SELECT meta_value, meta_id FROM transactions_meta AS t WHERE t.user__id = :user__id AND meta_key = :meta_key ");
                $query->execute(array('user__id' => $order_info['user__id'], 'meta_key' => $meta_key));
                if ($transaction_productor = $query->fetch()) {

                    $actual_value = $transaction_productor['meta_value'];
                    $id_transaction_productor = $transaction_productor['meta_id'];
                    $new_value_meta = $actual_value - $order_infos['order_liquid_value'];

                    // REMOVER SALDO DO FINANCEIRO
                    $query = $conn->prepare("UPDATE transactions_meta AS t SET meta_value = :new_value_meta WHERE t.user__id = :user__id AND meta_id = :meta_id ");
                    $query->execute(array(
                        'new_value_meta'    => $new_value_meta,
                        'user__id'          => $order_info['user__id'],
                        'meta_id'           => $id_transaction_productor
                    ));
                }

                // PEGAR QUANTIDADE DO PEDIDO E STATUS ANTERIOR
                $query = $conn->prepare("SELECT o.user__id, o.order_liquid_value FROM orders AS o WHERE o.order_id = :order_id");
                $query->execute(['order_id' => "AFI" . $order_number]);
                if ($affiliated_row = $query->fetch()) {
                    $affiliated_id = $affiliated_row['user__id'];
                    $order_liquid_value_afi = $affiliated_row['order_liquid_value'];

                    // PEGAR SALDO ATUAL DO FINANCEIRO DO AFILIADO
                    $query = $conn->prepare("SELECT meta_value, meta_id FROM transactions_meta AS t WHERE t.user__id = :user__id AND meta_key = :meta_key ");
                    $query->execute(array('user__id' => $affiliated_id, 'meta_key' => $meta_key));
                    if ($transaction_affiliated = $query->fetch()) {

                        $actual_value_afi = $transaction_affiliated['meta_value'];
                        $id_transaction_affiliated = $transaction_affiliated['meta_id'];
                        $new_value_meta_afi = $actual_value_afi - $order_liquid_value_afi;

                        // REMOVER SALDO DO FINANCEIRO
                        $query = $conn->prepare("UPDATE transactions_meta AS t SET meta_value = :new_value_meta WHERE t.user__id = :user__id AND meta_id = :meta_id ");
                        $query->execute(array(
                            'new_value_meta'    => $new_value_meta_afi,
                            'user__id'          => $affiliated_id,
                            'meta_id'           => $id_transaction_affiliated
                        )); 
                    }
                }

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
            } else {
                $stmt = $conn->prepare('UPDATE orders SET order_status = :order_status, order_status_description = :order_status_description WHERE order_id = :order_id');
                $stmt->execute(array('order_status' => $order_status, 'order_status_description' => $order_status_description, 'order_id' => $order_id));
            }

            $stmt = $conn->prepare('UPDATE orders SET order_status = "' . $order_status . '" WHERE  order_number= "AFI' . $order_number . '" ');
            $stmt->execute();



            $sql = "UPDATE orders SET order_status = 5, order_status_description = 'Pedido cancelado pelo Usuário' WHERE  order_number = :order_number";
            $statement = $conn->prepare($sql);
            $statement->execute(array("order_number" => $order_number));
            break;
        case 1:
            $status_string = "Reagendado";
            $data = $_POST['data'];
            $sql = "UPDATE orders SET order_status = 1, order_status_description = 'Pedido Reagendado', order_deadline = '$data' WHERE  order_number = :order_number";
            $statement = $conn->prepare($sql);
            $teste = $statement->execute(array("order_number" => $order_number));
        default:
            $feedback = array('type' => 'success', 'msg' => "Status não encontrado. por favor atualize a página!.", 'title' => ":(");
            echo json_encode($feedback);
            break;
    }
} catch (\Throwable $th) {
    $feedback = array( 'type' => 'success', 'title' => "Erro", 'msg' => $th );
    echo json_encode($feedback);
    break;
}

