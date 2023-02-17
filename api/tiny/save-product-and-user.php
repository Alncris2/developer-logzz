<?php 

    require_once(dirname(__FILE__) . '/../../includes/config.php');
    session_name(SESSION_NAME);
    
    !session_start() && session_start();
    
    $_SESSION['userCode'] = $_REQUEST['userCode'];
    $_SESSION['productCode'] = $_REQUEST['productCode'];
    $_SESSION['integration_url'] = $_REQUEST['integration_url'];
    
    
    echo json_encode(["status" => 'ok']);
?>