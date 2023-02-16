<?php

require_once (dirname(__FILE__) . '/includes/config.php');
date_default_timezone_set('America/Sao_Paulo');


# pegando orders que não tem local operaction orders 
$get_orders_null_local = $conn->prepare('SELECT o.order_id, o.client_address, o.client_name FROM orders o LEFT JOIN local_operations_orders loo ON loo.order_id = o.order_id WHERE loo.operation_id IS NULL');
$get_orders_null_local->execute();

while ($order = $get_orders_null_local->fetch()) {

    $address = $order["client_address"];
    $city_state = explode("<br>", $address)[3];
    $city = explode(", ", $city_state)[0];
    $state = strtoupper(explode(", ", $city_state)[1]);

    if(strpos($city, 'CEP:') !== false){
        $city_state = explode("<br>", $address)[2];
        $city       = explode(", ", $city_state)[0];
        $state      = strtoupper(explode(", ", $city_state)[1]);
    }

    // print_r($address);

    $get_local_operation = $conn->prepare("SELECT * FROM local_operations lop INNER JOIN operations_locales ol ON lop.operation_id = ol.operation_id WHERE ol.city LIKE :city");
    $get_local_operation->execute(array("city" => '%' . $city . '%'));
    if ($local_operation = $get_local_operation->fetch()) {
        try {            
                $add_operation_order = $conn->prepare("INSERT INTO local_operations_orders(operation_id, order_id) VALUES (:operation_id, :order_id)");
                $add_operation_order->execute(array("operation_id" => $local_operation["operation_id"], "order_id" => $order["order_id"]));
            
                echo "Criada order operations local order do pedido de <b>" . $order["client_name"] . "</b> na operação local:  <b>" . $local_operation['operation_name'] . "</b><br>";

        } catch (PDOException $e) {
            $error = 'ERROR: ' . $e->getMessage();
            echo "<br><br> Não conseguimos criar o order operations local order do pedido de <b>" . $order["client_name"] . "</b><br>";
        }
        continue;
    }
    echo "<br>Não conseguimos achar uma operação local para o pedido de <b>" . $order["client_name"] . "</b> cidade-UF:  <b>" . $city .' - '. $state . "</b><br>";
}


$get_local_operation = $conn->prepare("SELECT * FROM local_operations lop INNER JOIN operations_locales ol ON lop.operation_id = ol.operation_id");
$get_local_operation->execute();

echo '<br><br><pre>';
foreach($get_local_operation->fetchALL() as $local_operation){
    var_dump($local_operation['operation_name'], $local_operation['city'], $local_operation['uf']);
    echo '<br>';
}
echo '</pre>';