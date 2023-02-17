<?php
require "includes/config.php";
try {
    // Pega dados formato = Form URL Encoded
    parse_str(urldecode(file_get_contents("php://input")), $dataJson);
    if (empty($dataJson)) {
        throw new Exception("Data is invalid", 400);
    }
    if (!isset($dataJson['order_id']) && !isset($dataJson['status'])) {
        throw new Exception("Data is invalid", 400);
    }
    // Gera dados de log
    generateTxtResquest(json_encode($dataJson));
    $dataOrder = getOrderById($dataJson['order_id']);
    if (empty($dataOrder)) {
        throw new Exception("Data is invalid", 400);
    }
    if ($dataJson['status'] == 'reagendar') {
        $data = $dataJson['data_reagendamento'];
        $order_id = $dataJson['order_id'];
        ///pegar order number para alterar pedido tanto do produtor quanto afiliado
        $queryN = $conn->prepare("SELECT order_number FROM orders  WHERE order_id = :order_id");
        $queryN->execute(['order_id' => $order_id]);
        $order_number = $queryN->fetch(\PDO::FETCH_ASSOC)["order_number"];
        $order_number = str_replace('AFI', '', $order_number);

        $queryN = $conn->prepare("SELECT order_id FROM orders  WHERE order_number = :order_number");
        $queryN->execute(['order_number' => $order_number]);
        $order_id = $queryN->fetch(\PDO::FETCH_ASSOC)["order_id"];

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
        }
        $stmt = $conn->prepare('UPDATE orders SET order_status = :order_status , order_deadline="' . $order_deadline . '"   WHERE order_id = :order_id   ');
        $stmt->execute(array('order_status' => 1, 'order_id' => $order_id));

        $stmt = $conn->prepare('UPDATE orders SET order_status = 1 , order_deadline="' . $order_deadline . '"  WHERE  order_number= "AFI' . $order_number . '"  ');
        $stmt->execute();

        $feedback = array('title' => 'Feito!', 'type' => 'success');
        echo json_encode($feedback);
        exit;
    }
} catch (Exception $error) {
    $errorResponse = array("message" => $error->getMessage(), "status" => $error->getCode());
    http_response_code($error->getCode());
    echo json_encode($errorResponse);
}

/**
 * Salva log retorno
 *
 * @param string $txt
 * @return void
 */
function generateTxtResquest($txt)
{
    $fp = fopen('logRetornoAtendezap.txt', "a+");
    fwrite($fp, $txt . PHP_EOL);
    fclose($fp);
}

/**
 * Busca pelo número do pedido
 *
 * @param string|int $order_id
 * @return void
 */
function getOrderById($order_id)
{
    global $conn;
    $sql = "SELECT order_id FROM orders WHERE order_id = :order_id";
    $statement = $conn->prepare($sql);
    $statement->execute(array("order_id" => $order_id));
    $dataOrder = $statement->fetchAll(\PDO::FETCH_ASSOC)[0];
    return $dataOrder;
}
