<?php
  
  require dirname(FILE) . "/../includes/config.php";

  
  if($_POST['status'] == "delete"){

    if(isset($_POST['idSend'])){

        $unique = filter_var($_POST['idSend'], FILTER_VALIDATE_INT);
        $url = addslashes($_POST['url']);
        try{
            $delete = $conn->prepare("DELETE FROM tiny_dispatches WHERE id = ?");
            $delete->execute(array($unique)); 

            // ATUALIZA O STATUS DA INTEGRAÇÃO
            $update_status = $conn->prepare("UPDATE integrations SET status = :status WHERE integration_url = :integration_url");
            $update_status->execute(['status' => 0, 'integration_url' => $url]);

            echo json_encode(['type' => 'success', "msg" => "Integração deletada com sucesso!", "title" => "A Integração foi deletada!"]);
            exit;
        }catch (Exception $e){
            echo 'Erro ao deletar: ',  $e->getMessage(), "\n";
            echo json_encode(array("type"=>"error","title"=>"Erro ao deletar"));
        }

    }
    
  }
  
  if($_POST['status'] == "update"){
    $url = addslashes($_POST['url']);

    try {
        // PEGAR STATUS ATUAL 
        $get_status = $conn->prepare("SELECT status FROM integrations WHERE integration_url = :integration_url");
        $get_status->execute(['integration_url' => $url]);
        
        // INVERTE O STATUS ATUAL
        $status = $get_status->fetch(\PDO::FETCH_ASSOC)['status'] == '0' ? '1' : '0';

        // ATUALIZA O STATUS DA INTEGRAÇÃO
        $update_status = $conn->prepare("UPDATE integrations SET status = :status_reverte WHERE integration_url = :integration_url");
        $update_status->execute(['status_reverte' => $status, 'integration_url' => $url]);

        echo json_encode(['type' => 'success', "msg" => "Status da integração foi atualizado com sucesso.", "title" => "O status foi mudado!"]);
        exit;
       
    } catch (\Exception $th) {
        echo json_encode(['type' => 'error', "msg" => "Erro na mudança do status do produto", "title" => "O status não foi mudado!"]);
        exit;
       
    }
  }




?>