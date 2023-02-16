<?php 

    require_once(dirname(__FILE__) . '/../includes/config.php');
    
    $idToDelete = $_REQUEST['idToDelete'];   
    $url = $_REQUEST['url'];

    $query = "DELETE i,d FROM integrations i LEFT JOIN tiny_dispatches d ON i.integration_url = d.url_integration WHERE i.integration_id = :id";
    $stmt = $conn->prepare($query);
    
    try{
        $stmt->execute([
            'id' => $idToDelete,
        ]);
        
        echo json_encode(['status' => 'ok', 'msg' => "Produto deletado com sucesso!"]);
    }catch(\Exception $e){
        print_r("ERROR: $e");
    }