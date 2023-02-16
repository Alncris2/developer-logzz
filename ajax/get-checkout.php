<?php
require_once(dirname(__FILE__) . '/../includes/config.php');
session_name(SESSION_NAME);
session_start();


if ($_POST['action'] == 'get-operators'){
  $checkout_id = addslashes($_POST["idCheckout"]);

  try {
    $get_checkout = $conn->prepare("SELECT * FROM custom_checkout cc WHERE checkout_id = :checkout_id");
    $get_checkout->execute(array("checkout_id" => $checkout_id));

    $feedback = array("status" => 1, "data" => $get_checkout->fetch());
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