<?php 
    require dirname(__FILE__) . "/../includes/config.php";

    $id_to_inative = filter_var($_GET['idClicked'], FILTER_SANITIZE_NUMBER_INT);

    try {
        
        
        
        // VERIFICAR SE EXISTEM ESTOQUES NESSA LOCALIDADE 
        
        $get_total_inventories = $conn->prepare("SELECT SUM(inventory_quantity) AS total FROM inventories WHERE inventory_locale_id = :inventory_locale_id AND ship_locale = :ship_locale");
        $get_total_inventories->execute(['inventory_locale_id' => $id_to_inative, 'ship_locale' => '0']);
        $total = $get_total_inventories->fetch(\PDO::FETCH_ASSOC)['total'];

        if($total == null || $total <= 0){
            $query_to_delete_operation = $conn->prepare("UPDATE local_operations SET operation_active = 0, operation_deleted = 1 WHERE operation_id = :operation_id");
            $query_to_delete_operation->execute(['operation_id' => $id_to_inative]);

            echo json_encode(['type' => 'success', 'title' => 'Operação local deletada com sucesso!', 'msg' => 'Essa operação não poderá mais receber estoques, e foi excluída por nao ter estoques.']);
            exit;
        }

        # QUERY PARA INATIVAR/DELETAR OPERAÇÃO LOCAL
        $query_to_delete_operation = $conn->prepare("UPDATE local_operations SET operation_active = 0 WHERE operation_id = :operation_id");
        $query_to_delete_operation->execute(['operation_id' => $id_to_inative]);
        echo json_encode(['type' => 'success', 'title' => 'Operação local inativada com sucesso!', 'msg' => 'Essa operação não poderá mais receber estoques, e será excluida assim que não houver estoques.']);
        exit;

    } catch (\Exception $e) {
        echo json_encode(['type' => 'error', 'title' => 'O Erro foi nosso, desculpe!', 'msg' => "ERRO:". $th->getMessage()]);
        exit;
    }
