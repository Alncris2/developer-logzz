<?php 
require_once(dirname(__FILE__) . '/../includes/config.php');
session_name(SESSION_NAME);
session_start();

// var_dump("dewfe");die;
try {
    $_POST['delivery-days'];
    $daysString = '[';
    foreach($_POST['delivery-days'] as $days){
        $daysString .= $days . ',';
    }
    $daysString = rtrim($daysString, ',');
    $daysString .= ']';

    $stmtUpdate = $conn->prepare('UPDATE inventories SET product_delivery_days = :product_delivery_days WHERE inventory_id = :inventory_id');
    $resultUpdate = $stmtUpdate->execute(array("product_delivery_days" => $daysString, "inventory_id" => $_POST['inventory_id_select']));
    if(!$resultUpdate){
        throw new Exception("Falha ao atulizar!");
    }
    $feedback = array('status' => 1, 'mgg' => "Atualização realizada com sucesso!");
    echo json_encode($feedback);
} catch (Exception $error) {
    $feedback = array('status' => 2, 'msg' => $error->getMessage());
    echo json_encode($feedback);
}