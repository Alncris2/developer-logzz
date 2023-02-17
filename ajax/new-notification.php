<?php

// use function PHPSTORM_META\type;

// error_reporting(-1);            
// ini_set('display_errors', 1); 
require_once dirname(__FILE__) . "/../includes/config.php";
// require(dirname(__FILE__) . '/../includes/classes/SendNotification.php');
// require(dirname(__FILE__) . "/../RequestAtendezap.php");
session_name(SESSION_NAME);
session_start(); 


if (isset($_POST['action']) && $_POST['action'] == 'Shoot-notification') {

    $notification_context = addslashes($_POST['texto']);
    if (empty($notification_context)) {
        $feedback = array('status' => 0, 'msg' => 'A mensagem não pode ser vazia', 'title' => "Confira seus dados", 'type' => 'warning');
        echo json_encode($feedback);
        exit;
    }

    $notification_icon = addslashes($_POST['icon']);
    if (empty($notification_icon)) {
        $feedback = array('status' => 0, 'msg' => 'Icone  não pode ser vazio', 'title' => "Confira seus dados", 'type' => 'warning');
        echo json_encode($feedback);
        exit; 
    }

    $notification_link = addslashes($_POST['link']);
    if (empty($notification_link)) {
        $notification_link = SERVER_URI;
    }
    
    $users_ids = $_POST['usuarios'];  

    try {
        
        if(strpos($users_ids, ',') !== false){ 
            $users_ids = explode(',', $users_ids);
            
            foreach($users_ids as $user__id) {
                if($user__id == ''){ 
                    continue;
                }

                $verify_user_existence = $conn->prepare('SELECT full_name FROM users WHERE user__id = :user__id');
                $verify_user_existence->execute(array('user__id' => $user__id)); 
                if($verify_user_existence->rowCount() < 1){   
                    $feedback = array('status' => 0, 'msg' => 'Usúario não encontrado', 'title' => "Erro no sistema", 'type' => 'warning');
                    echo json_encode($feedback);
                    exit;
                }
                
                $create_new_notification = $conn->prepare("INSERT INTO notifications (user__id, notification_context, notification_icon, notification_link, notification_open) VALUES (:user__id, :notification_context, :notification_icon, :notification_link, 0)");
                $create_new_notification->execute(array('user__id' => $user__id, 'notification_context' => $notification_context, 'notification_icon' => $notification_icon, 'notification_link' => $notification_link));
            } 
        }  else {

            if($users__id != ''){
                $verify_user_existence = $conn->prepare('SELECT full_name FROM users WHERE user__id = :user__id');
                $verify_user_existence->execute(array('user__id' => $user__id)); 
                if($verify_user_existence->rowCount() < 1){   
                    $feedback = array('status' => 0, 'msg' => 'Usúario não encontrado', 'title' => "Erro no sistema", 'type' => 'warning');
                    echo json_encode($feedback);
                    exit;
                }
            }
            
            $create_new_notification = $conn->prepare("INSERT INTO notifications (user__id, notification_context, notification_icon, notification_link, notification_open) VALUES (:user__id, :notification_context, :notification_icon, :notification_link, 0)");
            $create_new_notification->execute(array('user__id' => $user__id, 'notification_context' => $notification_context, 'notification_icon' => $notification_icon, 'notification_link' => $notification_link));
        }

        $feedback = array('status' => 0, 'msg' => 'Notificação envidada com sucesso', 'title' => "Sucesso", 'type' => 'success');
        echo json_encode($feedback);
        exit;

    } catch (PDOException $e) {
        $error = 'ERROR: ' . $e->getMessage(); 
        
        $feedback = array('status' => 0, 'msg' => $error, 'title' => "Erro", 'response' => array($create_new_notification,  ['user__id' => $user__id, 'notification_context' => $notification_context, 'notification_icon' => $notification_icon, 'notification_link' => $notification_link]));
        echo json_encode($feedback); 

        exit;
    }
} else {
    $feedback = array('status' => 0, 'msg' => 'NO ACTION!');
    echo json_encode($feedback);
    exit;
}
    