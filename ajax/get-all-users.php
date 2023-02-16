
<?php
// error_reporting(-1);            
// ini_set('display_errors', 1);  
require_once(dirname(__FILE__) . '/../includes/config.php');
session_name(SESSION_NAME);
session_start();

try {
    $get_all_users = $conn->prepare("SELECT user__id, full_name FROM users u WHERE u.active = 1");
    $get_all_users->execute();

    while($user = $get_all_users->fetch()){          
        $users[] =  array(
            'name'  => $user['full_name'],
            'id'    => $user['user__id']
        );
    }   

    $feedback = array("status" => 1, "data" => $users);
    echo json_encode($feedback); 

    exit;
} catch (PDOException $e) { 

    $error = 'ERROR: ' . $e->getMessage();
    $feedback = array('status' => 0, 'msg' => $error);
    echo json_encode($feedback);
    exit;
}
?>
