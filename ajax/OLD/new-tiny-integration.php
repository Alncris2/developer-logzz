<?php

/**
 * 
 * Trata e insere os dados dos 5 formulários de
 * integração do Tiny na tabela "tiny_dispatches".
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

$token = addslashes($_POST['integration-unique-key']);

$dispatche_region_id = addslashes($_POST['action']);
if (!preg_match("/^[(a-zA-Z-)]*$/", $dispatche_region_id)) {
    $feedback = array('status' => 0, 'msg' => 'Atualize a página e tente novamente.', 'title' => "Erro Interno [3]");
	echo json_encode($feedback);
	exit;
}

switch ($dispatche_region_id){
    case 'new-integration-tiny-sul':
        $dispatche_region_id = 1;
        break;

    case 'new-integration-tiny-norte':
            $dispatche_region_id = 2;
            break;
            
    case 'new-integration-tiny-nordeste':
        $dispatche_region_id = 3;
        break;
        
    case 'new-integration-tiny-oeste':
        $dispatche_region_id = 4;
        break;
        
    case 'new-integration-tiny-sudeste':
        $dispatche_region_id = 5;
        break;

    default:
        $feedback = array('status' => 0, 'msg' => 'Atualize a página e tente novamente.', 'title' => "Erro Interno [4]");
        echo json_encode($feedback);
        exit;
}


// VERIFICAR SE JÁ ESTÁ INTEGRADO COM TINY
$query = "SELECT * FROM tiny_dispatches WHERE nome_integracao = :nome_integracao";
$stmt = $conn->prepare($query);
$stmt->execute(['nome_integracao' => $_POST['integration-name']]);

if($stmt->rowCount() == 0){
    isset($_POST['integration_url']) ? $url = $_POST['integration_url'] : $url = "";
    
    # ATUALIZAR DADOS DO PEDIDO
    // query para atualizar dados da intregração 
    $get_integration = $conn->prepare('UPDATE integrations SET status = 1 WHERE integration_url = :url');
    $integaration_data = $get_integration->execute([
        'url' => $url
    ]);
    
    isset($_POST['tiny-users-list-text']) ? $users = $_POST['tiny-users-list-text'] : $users = $_POST['tiny-users-list'];
    
    $qtd_users = count(explode(',',$users));
    
    if($integaration_data) {
        $stmt = $conn->prepare('INSERT INTO tiny_dispatches (token, nome_integracao, ufs, users_qtd, users_id,products_ids,url_integration,dispatche_region_id) VALUES (:token, :nome_integracao, :ufs, :users_qtd, :users_id, :products_ids, :url_integration,:dispatche_region_id)');
    	try {
    		$stmt->execute(array('token' => $_POST['integration-unique-key'], 'nome_integracao' => $_POST['integration-name'], 'ufs' => $_POST['tiny-uf-list-text'], 'users_qtd' => $qtd_users, 'users_id' => $users,'products_ids' => $_POST['tiny-products-list-text'], 'url_integration' => $url, 'dispatche_region_id' => $dispatche_region_id)); 
    
    		$feedback = array('status' => 1);
    		echo json_encode($feedback);
    		exit;
        } catch(PDOException $e) {
            $error = 'ERROR: ' . $e->getMessage();
            $feedback = array('status' => 0, 'msg' => $error, 'title' => "Erro");
            echo json_encode($feedback);
            exit;
        }
    }
}

echo json_encode(['status' => 0, 'msg' => "Nome dá integração já cadastrado"]);
return;
?>