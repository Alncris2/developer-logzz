<?php 
require_once(dirname(__FILE__) . '/../includes/config.php');
session_name(SESSION_NAME);
session_start();

// var_dump("dewfe");die;
try {
    if (!$_GET['action'] == "get-delivery-days") {
        throw new Exception("Ação não encontrada!");
    }
    if (empty($_GET['operation_id'])) {
        throw new Exception("Operador id não informado!");
    }
    if (empty($_GET['inventory_id'])) {
        throw new Exception("Operador id não informado!");
    }
    $operation_id = $_GET['operation_id'];
    $inventory_id = $_GET['inventory_id'];
    
    $local_operations = $conn->prepare('SELECT operation_delivery_days FROM local_operations WHERE operation_id = :operation_id');
    $local_operations->execute(array("operation_id" => $operation_id));
    $delivery_days = $local_operations->fetchAll(PDO::FETCH_ASSOC)[0];
    $data = $delivery_days["operation_delivery_days"];
    
    $invetoriesStmt = $conn->prepare('SELECT product_delivery_days FROM inventories WHERE inventory_id = :inventory_id');
    $invetoriesStmt->execute(array("inventory_id" => $inventory_id));
    $dadosInventory = $invetoriesStmt->fetchAll(PDO::FETCH_ASSOC)[0];
    if($dadosInventory["product_delivery_days"]){
        $datacustom = $dadosInventory["product_delivery_days"];
    }


    $feedback = array('status' => 1, 'delivery_days' => $data, 'days_custom' => $datacustom );
    echo json_encode($feedback);
} catch (Exception $error) {
    $feedback = array('status' => 2, 'msg' => $error->getMessage());
    echo json_encode($feedback);
}