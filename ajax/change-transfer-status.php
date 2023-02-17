
<?php

require_once(dirname(__FILE__) . '/../includes/config.php');
session_name(SESSION_NAME);
session_start();

if(isset($_POST["billing"])) {

    $status = $_POST["status"];
    $billing = $_POST["billing"];

    try {
        $today = date("Y-m-d H:i:s");

        if($status == 1) {
            #Recusado

            $get_user_id = $conn->prepare("SELECT user__id, billing_value FROM billings WHERE billing_id=:id");
            $get_user_id->execute(array("id" => $billing));
            $get_user_id = $get_user_id->fetch();

            $billing_value = $get_user_id["billing_value"];
            $user_id = $get_user_id["user__id"];

            $update_transfer_review = $conn->prepare("UPDATE transactions_meta SET meta_value = meta_value - :value WHERE user__id = :user__id AND meta_key = 'in_review_transfer';");
            $update_transfer_review->execute(array("user__id" => $user_id, "value" => $billing_value)); 

            $update_transfer_balance = $conn->prepare("UPDATE transactions_meta SET meta_value = meta_value + :value WHERE user__id = :user__id AND meta_key = 'transfer_balance';");
            $update_transfer_balance->execute(array("user__id" => $user_id, "value" => $billing_value)); 

            $update_status = $conn->prepare("UPDATE billings SET billing_status = :status, billing_released = :today WHERE billing_id = :id");
            $update_status->execute(array("status" => $status, "id" => $billing, "today" => $today));
        } else {
            #Aprovado
            $update_status = $conn->prepare("UPDATE billings SET billing_status = :status, billing_released = :today WHERE billing_id = :id");
            $update_status->execute(array("status" => $status, "id" => $billing, "today" => $today));
        }

        $feedback = array('status' => 1, 'msg' => 'Operador Atualizado!');
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
