<?php

/**
 * 
 * Trata e insere os dados dos 5 formulários de
 * integração do Bling na tabela "bling_dispatches".
 * 
 * ID das Regiões (dispatche_region_id):
 * 1 - Região Sul
 * 2 - Região Norte 
 * 3 - Região Nordeste
 * 4 - Região Centro-Oeste
 * 5 - Região Sudeste
 * 
 * 
 */

require_once dirname(__FILE__) . "/../includes/config.php";
session_name(SESSION_NAME);
session_start();

$dispatche_name = addslashes($_POST['integration-name']);
if (!preg_match("/^[a-zA-Z-À-ú ]*$/", $dispatche_name)) {
    $feedback = array('status' => 0, 'msg' => 'O nome da integração possui caracteres inválidos.', 'title' => "Confira os dados");
	echo json_encode($feedback);
	exit;
}

$dispatche_api_key = addslashes($_POST['integration-unique-key']);

$dispatche_ufs = addslashes($_POST['bling-uf-list-text']);
if (!preg_match("/^[(A-Z,)]*$/", $dispatche_ufs)) {
    $feedback = array('status' => 0, 'msg' => 'Atualize a página e tente novamente.', 'title' => "Erro Interno [1]");
	echo json_encode($feedback);
	exit;
}

$dispatche_users = addslashes($_POST['bling-users-list-text']);
if (!preg_match("/^[(0-9),]*$/", $dispatche_users)) {
    $feedback = array('status' => 0, 'msg' => 'Atualize a página e tente novamente.', 'title' => "Erro Interno [2]");
	echo json_encode($feedback);
	exit;
}

$dispatche_region_id = addslashes($_POST['action']);
if (!preg_match("/^[(a-zA-Z-)]*$/", $dispatche_region_id)) {
    $feedback = array('status' => 0, 'msg' => 'Atualize a página e tente novamente.', 'title' => "Erro Interno [3]");
	echo json_encode($feedback);
	exit;
}

switch ($dispatche_region_id){
    case 'new-integration-bling-sul':
        $dispatche_region_id = 1;
        break;

    case 'new-integration-bling-norte':
            $dispatche_region_id = 2;
            break;
            
    case 'new-integration-bling-nordeste':
        $dispatche_region_id = 3;
        break;
        
    case 'new-integration-bling-oeste':
        $dispatche_region_id = 4;
        break;
        
    case 'new-integration-bling-sudeste':
        $dispatche_region_id = 5;
        break;

    default:
        $feedback = array('status' => 0, 'msg' => 'Atualize a página e tente novamente.', 'title' => "Erro Interno [4]");
        echo json_encode($feedback);
        exit;
}


    $stmt = $conn->prepare('INSERT INTO bling_dispatches (dispatche_id, dispatche_name, dispatche_api_key, dispatche_region_id, dispatche_ufs, dispatche_users) VALUES (:dispatche_id, :dispatche_name, :dispatche_api_key, :dispatche_region_id, :dispatche_ufs, :dispatche_users)');
	
	try {
		$stmt->execute(array('dispatche_id' => 0, 'dispatche_name' => $dispatche_name, 'dispatche_api_key' => $dispatche_api_key, 'dispatche_region_id' => $dispatche_region_id, 'dispatche_ufs' => $dispatche_ufs, 'dispatche_users' => $dispatche_users)); 

		$feedback = array('status' => 1);
		echo json_encode($feedback);
		exit;
		
    } catch(PDOException $e) {
        $error = 'ERROR: ' . $e->getMessage();
        $feedback = array('status' => 0, 'msg' => $error, 'title' => "Erro");
        echo json_encode($feedback);
        exit;
    }

?>