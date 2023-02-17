<?php
    require "includes/config.php";
    session_name(SESSION_NAME);
    session_start();

    $shipment_status      = addslashes($_GET['status']);
    $shipment_id          = addslashes($_GET['id']);
    $product_id           = addslashes($_GET['product']);
    $locale_id            = addslashes($_GET['locale']);
    $inventory_quantity   = addslashes($_GET['quant']);

    $inventory_last_ship = date("Y-m-d H:i:s");

    $inventory_last_ship_quant = addslashes($_GET['quant']);

        switch ($shipment_status) {
            case 0:
                $status_string = "Enviado";
                break;
            case 2:
                $status_string = "Recebido";

                //Identifica a que usuário pertence o envio
                $get_user_id = $conn->prepare('SELECT ship_user_id FROM shipments WHERE ship_id = :shipment_id');
                $get_user_id->execute(array('shipment_id' => $shipment_id));
                $user_id = $get_user_id->fetch();
                
                $user__id = $user_id['ship_user_id'];

                $inventory_meta = $user__id . "-" . $product_id . "-" . $locale_id;

                // Verfica se o USUÁRIO já tem estoque do PRODUTO na LOCALIDADE
                // Se tiver, atualiza. Se não, cria.
                $verify_current_status = $conn->prepare('SELECT inventory_id, inventory_quantity FROM inventories WHERE inventory_meta = :inventory_meta');
                $verify_current_status->execute(array('inventory_meta' => $inventory_meta));

                if ($verify_current_status->rowCount() > 0) {
                    
                    $current_status = $verify_current_status->fetch();
                    $current_inventory = $current_status['inventory_quantity'];
                    
                    $new_value = $inventory_quantity + $current_inventory;

                    $stmt = $conn->prepare('UPDATE inventories SET inventory_quantity = :inventory_quantity, inventory_last_ship = :inventory_last_ship, inventory_last_ship_quant = :inventory_last_ship_quant  WHERE inventory_meta = :inventory_meta');

                    $stmt->execute(array('inventory_quantity' => $new_value, 'inventory_last_ship' => $inventory_last_ship, 'inventory_last_ship_quant' => $inventory_last_ship_quant, 'inventory_meta' => $inventory_meta));

                } else {

                    $insert_inventory = $conn->prepare('INSERT INTO inventories (inventory_id, inventory_user_id, inventory_product_id, inventory_locale_id, inventory_quantity, inventory_last_ship, inventory_last_ship_quant, ship_changed, inventory_meta) VALUES (:inventory_id, :inventory_user_id, :inventory_product_id, :inventory_locale_id, :inventory_quantity, :inventory_last_ship, :inventory_last_ship_quant, :ship_changed, :inventory_meta)');

                    $insert_inventory->execute(array('inventory_id' => 0, 'inventory_user_id' => $_SESSION['UserID'], 'inventory_product_id' => $product_id, 'inventory_locale_id' => $locale_id, 'inventory_quantity' => $inventory_quantity, 'inventory_last_ship' => $inventory_last_ship, 'inventory_last_ship_quant' => $inventory_last_ship_quant, 'ship_changed' => 0, 'inventory_meta' => $inventory_meta));   

                }

                break;
            case 3:
                $status_string = "Problema";
                break;
            default:
                $status_string = "A Enviar";
                break;
          }
    
          $stmt = $conn->prepare('UPDATE shipments SET ship_status = :shipment_status WHERE ship_id = :shipment_id');
          $stmt->execute(array('shipment_status' => $shipment_status, 'shipment_id' => $shipment_id));
      

    $msg = "O status do envio foi alterado para " . $status_string . ".";

	$feedback = array('status' => 2, 'msg' => $msg);
	echo json_encode($feedback);
	exit;



?>