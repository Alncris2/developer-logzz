<?php
require_once(dirname(__FILE__) . '/../includes/config.php');
session_name(SESSION_NAME);
session_start();


if ($_POST['action'] == 'get-cities'){
  $operation_id = addslashes($_POST["operation_id"]);

  try {

    $get_operation_cities = $conn->prepare("SELECT id, city FROM operations_locales WHERE operation_id = :operation_id");
    $get_operation_cities->execute(array("operation_id" => $operation_id));

    $feedback = array("status" => 1, "data" => $get_operation_cities->fetchAll());
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