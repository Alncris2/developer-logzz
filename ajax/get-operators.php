
<?php
require_once(dirname(__FILE__) . '/../includes/config.php');
session_name(SESSION_NAME);
session_start();


if ($_POST['action'] == 'get-operators') {
    $operation = addslashes($_POST["operation"]);

    try {

        $get_operation_cities = $conn->prepare("SELECT o.operator_id, u.full_name FROM logistic_operator o INNER JOIN users u ON o.user_id = u.user__id WHERE u.active = 1 AND o.local_operation = :operation");
        $get_operation_cities->execute(array("operation" => $operation));

        $feedback = array("status" => 1, "data" => $get_operation_cities->fetchAll());
        echo json_encode($feedback);

        exit;
    } catch (PDOException $e) {

        $error = 'ERROR: ' . $e->getMessage();
        $feedback = array('status' => 0, 'msg' => $error);
        echo json_encode($feedback);
        exit;
    }
}

?>
