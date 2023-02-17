<?php
error_reporting(-1);            
ini_set('display_errors', 1);   
require_once dirname(__FILE__) . "/../includes/config.php";
session_name(SESSION_NAME);
session_start(); 



if(!isset($_POST['action'])){
    $feedback = array('status' => 0, 'msg' => 'Atualize a página e tente novamente.', 'title' => "Erro Interno [3]", 'type' => 'error');
    echo json_encode($feedback);
    exit;
}

if($_POST['action'] == 'delete-integration-az'){
    if(!isset($_GET['id'])){  
        $feedback = array('status' => 0, 'msg' => 'Atualize a página e tente novamente.', 'title' => "Erro Interno [3]", 'type' => 'error');
        echo json_encode($feedback);
        exit;
    }
    $integration_id = addslashes($_GET['id']);  
 
    // query para atualizar dados da intregração  
    $delete_integration = $conn->prepare('DELETE FROM atendezap_integration WHERE az_id = :az_id ');
    $delete_integration->execute(array('az_id' => $integration_id));
    
    $feedback = array('status' => 1, 'msg' => 'Integração deletada com sucesso', 'title' => ":)", 'type' => 'success'); 
    echo json_encode($feedback);
    exit; 
}

if(!isset($_POST['level'])){
    $feedback = array('status' => 0, 'msg' => 'Atualize a página e tente novamente.', 'title' => "Erro Interno [3]", 'type' => 'error');
    echo json_encode($feedback);
    exit;
}


$integration_name = addslashes($_POST['name']);
if (!preg_match("/^[a-zA-Z-À-ú ]*$/", $integration_name)) {
    $feedback = array('status' => 0, 'msg' => 'O nome da integração possui caracteres inválidos.', 'title' => "Confira os dados", 'type' => 'warning');
	echo json_encode($feedback);
	exit;
}

$integration_api_key = addslashes($_POST['key']);    
$integration_link = addslashes($_POST['link']); 

if($_POST['action'] == 'update-integration-az'){ 

    if(!isset($_POST['id'])){
        $feedback = array('status' => 0, 'msg' => 'Atualize a página e tente novamente.', 'title' => "Erro Interno [3]", 'type' => 'error');
        echo json_encode($feedback);
        exit;
    }
    $integration_id = addslashes($_POST['id']); 

    if(isset($_POST['status'])){
        if(empty($_POST['status'])){
            $feedback = array('status' => 0, 'msg' => 'Informe o status para essa integração', 'title' => "Confira os dados", 'type' => 'warning');
            echo json_encode($feedback);
            exit;
        } 
    
        $integration_status = $_POST['status'] - 1; 
        $get_integration = $conn->prepare('SELECT * FROM atendezap_integration WHERE az_level LIKE "owner" AND az_status LIKE :az_status AND az_active = 1 AND az_id NOT LIKE :az_id');
        $get_integration->execute(array('az_status' => $integration_status, 'az_id' => $integration_id));
        if($get_integration->rowCount() > 0) {
            $feedback = array('status' => 0, 'msg' => 'Já existe uma integração para esse status!', 'title' => "Confira os dados", 'type' => 'warning');
            echo json_encode($feedback);
            exit;
        }
    } else {
        $integration_status = NULL;
    }


    // query para atualizar dados da intregração  
    $update_integration = $conn->prepare('UPDATE atendezap_integration SET az_name = :az_name, az_key = :az_key, az_webhook = :az_webhook, az_status = :az_status WHERE az_id = :az_id ');
    $update_integration->execute(array('az_id' => $integration_id, 'az_name' => $integration_name, 'az_key' => $integration_api_key, 'az_webhook' => $integration_link, 'az_status' => $integration_status));
    
    
    $feedback = array('status' => 1, 'msg' => 'Integração Atualizada com sucesso', 'title' => ":)", 'type' => 'success'); 
    echo json_encode($feedback);
}

if($_POST['action'] == 'new-integration-az'){
    if(isset($_POST['status'])){
        if(empty($_POST['status'])){
            $feedback = array('status' => 0, 'msg' => 'Informe o status para essa integração', 'title' => "Confira os dados", 'type' => 'warning');
            echo json_encode($feedback);
            exit;
        } 
    
        $integration_status = $_POST['status'] - 1; 
        $get_integration = $conn->prepare('SELECT * FROM atendezap_integration WHERE az_level LIKE "owner" AND az_status LIKE :az_status AND az_active = 1');
        $get_integration->execute(array('az_status' => $integration_status));
        if($get_integration->rowCount() > 0) {
            $feedback = array('status' => 0, 'msg' => 'Já existe uma integração para esse status!', 'title' => "Confira os dados", 'type' => 'warning');
            echo json_encode($feedback);
            exit;
        }
    } else {
        $integration_status = NULL;
    }

    $integration_level = addslashes($_POST['level']);
    if (!preg_match("/^[a-zA-Z-À-ú ]*$/", $integration_level)) {
        $feedback = array('status' => 0, 'msg' => 'Atualize a página e tente novamente.', 'title' => "Erro Interno [3]", 'type' => 'error');
        echo json_encode($feedback);
        exit;
    } 

    // query para atualizar dados da intregração  
    $update_integration = $conn->prepare('INSERT INTO atendezap_integration  (`az_name`, `az_key`, `az_webhook`, `az_status`, `az_active`, `az_level`) VALUES (:az_name, :az_key, :az_webhook, :az_status, 1, :az_level)');
    $update_integration->execute(array('az_name' => $integration_name, 'az_key' => $integration_api_key, 'az_webhook' => $integration_link, 'az_status' => $integration_status, 'az_status' => $integration_status, 'az_level' => $integration_level));
    
    
    $feedback = array('status' => 1, 'msg' => 'Integração Atualizada com sucesso', 'title' => ":)", 'type' => 'success'); 
    echo json_encode($feedback);
}
return; 
?>