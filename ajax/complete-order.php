<?php
require "../includes/config.php"; 
require "../includes/classes/RequestAtendezap.php"; 
include "../includes/classes/RandomStrGenerator.php"; 
session_name(SESSION_NAME); 
session_start();

error_reporting(-1);             
ini_set('display_errors', 1);



if ($_POST['action'] == 'pedido-completo'){
	$locale_id					= isset($_POST['localidade']) ? addslashes($_POST['localidade']) : addslashes($_POST['operacao-local']); 
	$paymethod 					= addslashes($_POST['pagamento']);
	$pedido						= addslashes($_POST['order']);
	$order_delivery_date 		= date("Y-m-d H:i:s");

    if(isset($_POST['cpf-cliente'])) {
        $client_cpf = addslashes($_POST['cpf-cliente']);
    }
    if(isset($_POST['v_credito'])) {
        $v_credit = addslashes($_POST['v_credito']);
    } 
    if(isset($_POST['operador'])) {
        $operador = addslashes($_POST['operador']);
    }

    if(isset($_POST['custom-date']) &&  $_POST['custom-date'] == 'on' ) {

        if(empty($_POST['data'])) {
            echo json_encode(['status' => 0, 'msg' => "Por favor, Informe a data da conclusão!", 'type' => "warning"]);
            return;            
        }

        if(empty($_POST['hours'])) {
            echo json_encode(['status' => 0, 'msg' => "Por favor, Informe a hora da conclusão!", 'type' => "warning"]);
            return;            
        }

        $date = pickerDateFormate($_POST['data']);
        $date = explode(" ", $date);
        $order_delivery_date = $date[0] ." ". $_POST['hours']; 
    } 

    

	# Script Pendente de Revisão
	$get_sale_quantity = $conn->prepare('SELECT * FROM orders AS o INNER JOIN sales AS s ON s.sale_id = o.sale_id WHERE o.order_id = :order_id');
	$get_sale_quantity->execute(array('order_id' => $pedido));

	$get_sale_quantity = $get_sale_quantity->fetch();
    $id_product_user = $get_sale_quantity['user__id'];
	$sale_quantity = $get_sale_quantity['sale_quantity'];
	$product_id = $get_sale_quantity['product_id'];
    $order_number = $get_sale_quantity['order_number'];
	$order_liquid_value = $get_sale_quantity['order_liquid_value'];
	$order_id = $get_sale_quantity['order_id'];
    $new_name = null;

    if ($get_sale_quantity['order_status'] == 3 || $get_sale_quantity['order_anticipation_released'] == 1) {
        $feedback = array('status' => 0,'title' => 'Ops :(' , 'msg' => 'Esse pedido já foi completo antes.', 'type' => 'warning');
        echo json_encode($feedback);
        exit; 
    }


    if(isset($_POST['pagamento']) &&  $_POST['pagamento'] == 'pix' ) { 

        if(!isset($_FILES['comprovante-pagamento']) || $_FILES['comprovante-pagamento']['size'] <= 0){
            $feedback = array('status' => 0, 'msg' => 'Faça o upload do compravante de pagamento por pix', 'type' => 'warning', 'title' => 'Comprovante de Pagamento');
            echo json_encode($feedback);
            exit; 
        }

        ////UPLOAD DA IMAGEM COMPROVANTE
        $filetypes = array('jfif', 'png', 'jpeg', 'jpg');
        $image_filetype_array = explode('.', $_FILES['comprovante-pagamento']['name']);
        $filetype = strtolower(end($image_filetype_array));


        // Valida se a extensão do arquivo é aceita
        if (in_array($filetype, $filetypes) == false){
            $feedback = array('status' => 0, 'msg' => 'A imagem precisa estar em formato PNG, JPG ou JFIF', 'type' => 'warning');
            echo json_encode($feedback);
            exit; 
        }

        $new_name = 'comprovante_pedido_'. $order_number . '.' . $filetype; //Definindo um novo nome para o arquivo
        $dir = '../uploads/pedidos/comprovante/'; //Diretório para uploads 
        if (move_uploaded_file($_FILES['comprovante-pagamento']['tmp_name'], $dir.$new_name)){
        
    
        } else {
            $feedback = array('status' => 0, 'msg' => 'Não deu! Erro ao fazer upload da imagem.', 'type' => 'danger');
            echo json_encode($feedback);
            exit;
        }
        ////FIM UPLOAD DA IMAGEM COMPROVANTE
    }

    # Verifica se há um pedido de Afiliado
    $verfify_membership_order = $conn->prepare('SELECT * FROM orders_meta WHERE order_number = :order_number AND meta_key = "membership_hotcode"');
    $verfify_membership_order->execute(array('order_number' => $order_number));
    

	if ($verfify_membership_order->rowCount() == 1){
		$has_member = true;
	}

	$get_inventory_current = $conn->prepare('SELECT inventory_quantity FROM inventories WHERE inventory_product_id = :inventory_product_id AND inventory_locale_id = :inventory_locale_id AND ship_locale = 0');
	$get_inventory_current->execute(array('inventory_product_id' => $product_id, 'inventory_locale_id' => $locale_id));

    $verify_locale = $conn->prepare("SELECT operation_active FROM local_operations WHERE operation_id = :operation_id");
    $verify_locale->execute([
        'operation_id' => $locale_id
    ]);

    # Busca os Dados do Produtor no Banco de Dados
    $get_product_comission_term = $conn->prepare('SELECT user_payment_term, user_plan_shipping_tax, user_plan_tax FROM users INNER JOIN subscriptions ON subscriptions.user__id = users.user__id WHERE users.user__id = :user__id');
    $get_product_comission_term->execute(array('user__id' =>  $id_product_user));

    while ($row = $get_product_comission_term->fetch()) {
        $user_payment_term  = $row['user_payment_term'];
    }

    $order_commission_timestamp = "+" . $user_payment_term . "days";    
    $date_modify = date_create($order_delivery_date);
    date_modify($date_modify, $order_commission_timestamp);
    $order_commission_date = date_format($date_modify,"Y-m-d H:i:s");

    $is_active = $verify_locale->fetch(\PDO::FETCH_ASSOC)['operation_active'];
    $current_inventory = $get_inventory_current->fetch();
    $new_value = $current_inventory[0] - $sale_quantity; 

	if ($get_inventory_current->rowCount() < 1 || $current_inventory["inventory_quantity"] <= 0){
		$feedback = array('title' => 'Sem estoque!', 'msg' => 'Parece que não há estoque suficiente do produto nesta localidade.', 'type' => 'warning');
		echo json_encode($feedback);
		exit;
	} else {

        # VERIFICAR SE O DONO DO PRODUTO É RECRUTADO A UMA CONTA 
        $query = "SELECT * FROM recruitment WHERE recruited_id = :recruited_id";
        $stmt = $conn->prepare($query);
        $stmt->execute([
            'recruited_id' => $id_product_user
        ]);

        if($stmt->rowCount() > 0){
            $id_recruiter = $stmt->fetch(\PDO::FETCH_ASSOC)['user__recruiter_id'];
        
            // PEGAR VALOR POR COMISSÃO DA CONTA
            $get_users_infos = $conn->prepare('SELECT * FROM subscriptions INNER JOIN users ON users.user__id = subscriptions.user__id WHERE users.user__id = :user__id');
            $get_users_infos->execute(array('user__id' => $id_recruiter));
            $user_info = $get_users_infos->fetch(\PDO::FETCH_ASSOC);
            
            $value_for_commision = $user_info['user_plan_commission_recruitment'];

            # ENVIAR COMISSÃO PARA O RECRUTADOR
            $query = "SELECT * FROM recruitment_commission_meta WHERE user__recruiter_id = :user_id";
            $stmt = $conn->prepare($query);
            $stmt->execute([
                'user_id' => $id_recruiter 
            ]);
            
            $row = $stmt->rowCount();
            if($row == 0){
                // INSERIR PRIMEIRA COMISSÃO POR RECRUTAMENTO PARA O RECRUTADOR
                $query = "INSERT INTO recruitment_commission_meta (user__recruiter_id, meta_key, meta_value_available, meta_value_payer, meta_value_release) VALUES (:user__recruiter_id, :meta_key, :meta_value_available, :meta_value_payer, :meta_value_release)";
                $stmt = $conn->prepare($query);
                $stmt->execute([
                    'user__recruiter_id' => $id_recruiter,
                    'meta_key' => 'billing_commission_release',
                    'meta_value_available' => 0,
                    'meta_value_payer' => 0,
                    'meta_value_release' => $value_for_commision * 1
                ]);
            }else{
                //PEGAR VALOR TOTAL Á LIBERAR 
                $query = "SELECT meta_value_release FROM recruitment_commission_meta WHERE user__recruiter_id = :id_recruiter";
                $stmt = $conn->prepare($query);
                $stmt->execute([
                    'id_recruiter' => $id_recruiter
                ]);

                $value = $stmt->fetch(\PDO::FETCH_ASSOC)['meta_value_release'];
                $new_value_ig = $value + ($value_for_commision * 1);

                // ATUALIZAR COMISSÃO 
                $query = "UPDATE recruitment_commission_meta SET meta_value_release = :meta_value_release WHERE user__recruiter_id = :id_recruiter AND meta_key = 'billing_commission_release'";
                $stmt = $conn->prepare($query);
                $stmt->execute([
                    'meta_value_release' => $new_value_ig,
                    'id_recruiter' => $id_recruiter
                ]);
            }
        }

		try {


			$update_inventory = $conn->prepare('UPDATE inventories SET inventory_quantity = :inventory_quantity WHERE inventory_product_id = :inventory_product_id AND ship_locale = 0 AND inventory_locale_id = :inventory_locale_id');
			$update_inventory->execute(array('inventory_quantity' => $new_value, 'inventory_product_id' => $product_id, 'inventory_locale_id' => $locale_id));
            if($client_cpf == "") { $client_cpf = null; } 
            if($v_credit == "") { $v_credit = null; }

            $stmt = $conn->prepare('UPDATE orders SET order_status = 3, order_delivery_date = :order_delivery_date, order_payment_method = :order_payment_method, client_cpf = :cpf, credit_times = :credit, order_commission_date = :order_commission_date, payment_proof = :payment_proof WHERE order_id = :order_id');
			$stmt->execute(array('order_id' => $pedido, 'order_payment_method' => $paymethod, 'order_delivery_date' => $order_delivery_date, 'cpf' => $client_cpf, 'credit' => $v_credit, 'order_commission_date' => $order_commission_date, 'payment_proof' => $new_name));

            if(isset($operador) && $operador != "") {
                $set_operador_order = $conn->prepare('UPDATE local_operations_orders SET responsible_id = :operador WHERE order_id = :order');
                $set_operador_order->execute(array("operador" => $operador, "order" => $pedido));
            }

            if (isset($has_member)) {
                $afi_order_number = "AFI" . $order_number;
                $stmt = $conn->prepare('UPDATE orders SET order_status = 3, order_delivery_date = :order_delivery_date, order_payment_method = :order_payment_method WHERE order_number = :order_number');
                $stmt->execute(array('order_number' => $afi_order_number, 'order_payment_method' => $paymethod, 'order_delivery_date' => $order_delivery_date));
            }

            # SE A OPERAÇÃO ESTIVER INATIVADA E NÃO EXISTIR MAIS ESTOQUES EXCLUIR ELA
            if($is_active == 0){ // OPERAÇÃO FOI INATIVADA  

               // PEGAR TODA A QUANTIDADE DE ESTOQUE NESSA LOCALIDADE 
                $get_total_inventories = $conn->prepare("SELECT SUM(inventory_quantity) AS total FROM inventories WHERE inventory_locale_id = :inventory_locale_id AND ship_locale = :ship_locale");
                $get_total_inventories->execute(['inventory_locale_id' => $locale_id, 'ship_locale' => '0']);
                $total = $get_total_inventories->fetch(\PDO::FETCH_ASSOC)['total'];

                $no_have_more_stock = $total <= 0;
                if($no_have_more_stock){ // Não existe mais estoques nessa localidade. 

                    $query_to_delete = $conn->prepare("UPDATE local_operations SET operation_deleted = 1 WHERE operation_id = :operation_id");
                    $query_to_delete->execute(['operation_id' => $locale_id]);
        
                    $feedback = array('status' => 0, 'title' => "O Status do pedido foi atualizado.", 'msg' => "O estoque nessa localidade foi zerado e essa operação local foi deletada.", 'type' => 'success');
                    echo json_encode($feedback);
                    exit; 
                    
                }
            }

            require "../includes/classes/SendNotification.php"; 
            $bearerToken = getBearerTokenFromGoogleFirebase()->access_token;


			/**
			 * 
			 * 
			 *  LIBERAÇÃO DE COMISSÃO PARA ANTECIPAÇÃO
			 *  IMEDITAMENTE APÓS O PEDIDO SER MARCADO COMO
			 *  COMPLETO
			 *   
			 * 
			*/
			$user__id = $id_product_user;
			$billing_value = round($order_liquid_value, 2); 

            if($has_member){ 
                $afi_order_number = "AFI" . $order_number;

                $get_member_tax = $conn->prepare('SELECT meta_value FROM orders_meta WHERE meta_key = "member_tax" AND order_number = :order_number');
                $get_member_tax->execute(array('order_number' => $order_number));
                $get_member_tax = $get_member_tax->fetch();
                @$member_tax = $get_member_tax[0];
                
                $get_user_id_afi = $conn->prepare("SELECT * FROM orders AS o WHERE o.order_number = :order_number");
                $get_user_id_afi->execute(['order_number' => $afi_order_number]);

                $data_afi = $get_user_id_afi->fetch(\PDO::FETCH_ASSOC);

                $user_afi_id = $data_afi['user__id'];
                $billing_value_afi = round($data_afi['order_liquid_value'], 2);
                $order_afi_id = $data_afi['order_id'];

                # Busca os Dados do Produtor no Banco de Dados
                $get_product_comission_term_afi = $conn->prepare('SELECT user_payment_term, user_plan_shipping_tax, user_plan_tax FROM users INNER JOIN subscriptions ON subscriptions.user__id = users.user__id WHERE users.user__id = :user__id');
                $get_product_comission_term_afi->execute(array('user__id' =>  $user_afi_id));

                while ($row = $get_product_comission_term_afi->fetch()) {
                    $user_payment_term_afi  = $row['user_payment_term'];
                }

                $order_commission_timestamp_afi = "+" . $user_payment_term_afi . "days";
                $date_modify_afi = date_create($order_delivery_date);
                date_modify($date_modify_afi, $order_commission_timestamp_afi);
                $order_commission_date_afi = date_format($date_modify_afi,"Y-m-d H:i:s");

                $afi_order_number = "AFI" . $order_number;
                $stmt = $conn->prepare('UPDATE orders SET order_commission_date = :order_commission_date, payment_proof = :payment_proof WHERE order_number = :order_number');
                $stmt->execute(array('order_number' => $afi_order_number, 'order_commission_date' => $order_commission_date_afi, 'payment_proof' => $new_name));
                

                $transaction_code = new RandomStrGenerator();
                $transaction_code =   strtoupper(date('jn') .'R'.  $transaction_code->lettersAndNumbers(4));

                $verify_unique_transaction_code = $conn->prepare('SELECT * FROM transactions WHERE transaction_code = :transaction_code');
                $verify_unique_transaction_code->execute(array('transaction_code' => $transaction_code));

                if (!($verify_unique_transaction_code->rowCount() == 0)) {
                    do {


                        $transaction_code = new RandomStrGenerator();
                        $transaction_code = strtoupper(date('jn') .'R'. $transaction_code->lettersAndNumbers(4));

                        $verify_unique_transaction_code = $conn->prepare('SELECT * FROM transactions WHERE transaction_code = :transaction_code');
                        $verify_unique_transaction_code->execute(array('transaction_code' => $transaction_code));
                    } while ($verify_unique_transaction_code->rowCount() != 0);
                } 

                $set_new_anticipation_value = $conn->prepare('INSERT INTO transactions (transaction_id, user_id, value_liquid, value_brute, tax_value, logistic_value, status, type, date_start, date_end, transaction_code, order_number) VALUES (NULL, :user_id, :value_liquid, :value_brute, :tax_value, :logistic_value, :status, :type, :date_start, :date_end, :transaction_code, :order_number)');
                $set_new_anticipation_value->execute(array(
                    'user_id'       => $user_afi_id, 
                    'value_liquid'  => $data_afi['order_liquid_value'],
                    'value_brute'   => $data_afi['order_liquid_value'] + $member_tax,
                    'tax_value'     => $member_tax, 
                    'logistic_value'=> 0.00, 
                    'status'        => 1, 
                    'type'          => 7, 
                    'date_start'    => date('Y-m-d H:i:s'), 
                    'date_end'      => $order_commission_date_afi, 
                    'order_number'  => $afi_order_number, 
                    'transaction_code' => $transaction_code
                )); 

                # Marca a comissão como "antecipaçao liberada";
                $set_commission_released = $conn->prepare('UPDATE orders SET order_anticipation_released = 1, order_refunded = 0 WHERE order_id = :order_id');
                $set_commission_released->execute(array('order_id' => $order_afi_id));

                $get_shooting_notification = $conn->prepare("SELECT shooting_title, shooting_message  FROM shooting_notification sn WHERE shooting_action = 3 AND shooting_status = 1");
                $get_shooting_notification->execute();
                    
                if($notification_body = $get_shooting_notification->fetch()){
                    $get_user_token_google_pro = $conn->prepare("SELECT full_name, user_token_google  FROM users u WHERE user__id = :user__id AND user_token_google IS NOT NULL");
                    $get_user_token_google_pro->execute(array("user__id" => $user__id));
                    if($user_info_producer_pro = $get_user_token_google_pro->fetch()){
                        $user_token_google_pro = $user_info_producer_pro['user_token_google'];

                        $notification_title = $notification_body['shooting_title'];
                        $notification_text = $notification_body['shooting_message'];
                        if(strpos($notification_body['shooting_message'], '{{first_name}}')){
                            $notification_text = str_replace('{{first_name}}', $first_name, $notification_text);
                        }
                        if(strpos($notification_body['shooting_message'], '{{full_name}}')){
                            $notification_text = str_replace('{{full_name}}', $user_info_producer_pro['full_name'], $notification_text);
                        }
                        if(strpos($notification_body['shooting_message'], '{{liquid_value}}')){
                            $notification_text = str_replace('{{liquid_value}}', number_format($order_liquid_value, 2, ',', '.'), $notification_text);
                        }

                        sendPushNotification($bearerToken, (object) [
                            'title' => $notification_title,
                            'body' => $notification_text,
                            'targetFcmToken' => $user_token_google_pro
                        ]);

                        $set_new_notification = $conn->prepare('INSERT INTO `notifications` (`user__id`, `notification_icon`, notification_title, `notification_context`, `notification_link`) VALUES (:user__id, "fa fa-dollar-sign", :notification_title, :notification_context, :notification_link )');
                        $set_new_notification->execute(array('user__id' => $user__id, 'notification_title' => $notification_title, 'notification_context' => $notification_text, 'notification_link' => SERVER_URI . '/pedidos/' ));
                    }
                }
                                
            }

            # Cálculo dos valores as comissões
            $get_meta_values = $conn->prepare('SELECT meta_value, meta_key FROM orders_meta WHERE meta_key IN ("ship_tax", "producer_tax") AND order_number = :order_number');
            $get_meta_values->execute(array('order_number' => $order_number));
            while($meta_values = $get_meta_values->fetch()){  
                if($meta_values['meta_key'] === 'producer_tax'){
                    $producer_tax = $meta_values['meta_value'];
                    continue;
                }
                if($meta_values['meta_key'] === 'ship_tax'){
                    $ship_tax = $meta_values['meta_value'];
                    continue;
                }
            }
            
            $transaction_code = new RandomStrGenerator();
            $transaction_code =   strtoupper(date('jn') .'R'.  $transaction_code->lettersAndNumbers(4));
            $verify_unique_transaction_code = $conn->prepare('SELECT * FROM transactions WHERE transaction_code = :transaction_code');
            $verify_unique_transaction_code->execute(array('transaction_code' => $transaction_code));

            if (!($verify_unique_transaction_code->rowCount() == 0)) {
                do {
                    $transaction_code = new RandomStrGenerator();
                    $transaction_code = strtoupper(date('jn') .'R'. $transaction_code->lettersAndNumbers(4));

                    $verify_unique_transaction_code = $conn->prepare('SELECT * FROM transactions WHERE transaction_code = :transaction_code');
                    $verify_unique_transaction_code->execute(array('transaction_code' => $transaction_code));
                } while ($verify_unique_transaction_code->rowCount() != 0);
            } 

			# Libera os valores
            $set_new_anticipation_value = $conn->prepare('INSERT INTO transactions (transaction_id, user_id, value_liquid, value_brute, tax_value, logistic_value, status, type, date_start, date_end, transaction_code, order_number) VALUES (NULL, :user_id, :value_liquid, :value_brute, :tax_value, :logistic_value, :status, :type, :date_start, :date_end, :transaction_code, :order_number)');
            $set_new_anticipation_value->execute(array(
                'user_id'       => $user__id, 
                'value_liquid'  => $billing_value,
                'value_brute'   => $billing_value + $producer_tax,
                'tax_value'     => $producer_tax, 
                'logistic_value'=> $ship_tax, 
                'status'        => 1, 
                'type'          => 7, 
                'date_start'    => date('Y-m-d H:i:s'), 
                'date_end'      => $order_commission_date, 
                'order_number'  => $order_number, 
                'transaction_code' => $transaction_code
            )); 

            $get_shooting_notification = $conn->prepare("SELECT shooting_title, shooting_message  FROM shooting_notification sn WHERE shooting_action = 3 AND shooting_status = 1");
            $get_shooting_notification->execute();
                    
            if($notification_body = $get_shooting_notification->fetch()){
                $get_user_token_google_pro = $conn->prepare("SELECT full_name, user_token_google  FROM users u WHERE user__id = :user__id AND user_token_google IS NOT NULL");
                $get_user_token_google_pro->execute(array("user__id" => $user__id));
                if($user_info_producer_pro = $get_user_token_google_pro->fetch()){
                    $user_token_google_pro = $user_info_producer_pro['user_token_google'];

                    $notification_title = $notification_body['shooting_title'];
                    $notification_text = $notification_body['shooting_message'];
                    if(strpos($notification_body['shooting_message'], '{{first_name}}')){
                        $notification_text = str_replace('{{first_name}}', $first_name, $notification_text);
                    }
                    if(strpos($notification_body['shooting_message'], '{{full_name}}')){
                        $notification_text = str_replace('{{full_name}}', $user_info_producer_pro['full_name'], $notification_text);
                    }
                    if(strpos($notification_body['shooting_message'], '{{liquid_value}}')){
                        $notification_text = str_replace('{{liquid_value}}', number_format($order_liquid_value, 2, ',', '.'), $notification_text);
                    }

                    sendPushNotification($bearerToken, (object) [
                        'title' => $notification_title,
                        'body' => $notification_text,
                        'targetFcmToken' => $user_token_google_pro
                    ]);

                    $set_new_notification = $conn->prepare('INSERT INTO `notifications` (`user__id`, `notification_icon`, notification_title, `notification_context`, `notification_link`) VALUES (:user__id, "fa fa-dollar-sign", :notification_title, :notification_context, :notification_link )');
                    $set_new_notification->execute(array('user__id' => $user__id, 'notification_title' => $notification_title, 'notification_context' => $notification_text, 'notification_link' => SERVER_URI . '/pedidos/' ));
                }
            }

			# Marca a comissão como "antecipaçao liberada";
			$set_commission_released = $conn->prepare('UPDATE orders SET order_anticipation_released = 1, order_refunded = 0 WHERE order_id = :order_id');
			$set_commission_released->execute(array('order_id' => $order_id));

            $stmt_insert_historic = $conn->prepare('INSERT INTO order_details ( order_number, order_status ) VALUES ( :order_number, :order_status)');
            $stmt_insert_historic->execute(array('order_number' => $order_number, 'order_status' => 3 ));
    
            sendWebhookStatusAuto($order_id);
            $feedback = array('title' => 'Feito!', 'msg' => 'O Status do pedido e a Quantidade do produto em estoque na localidade foram atualizados.', 'type' => 'success');
            echo json_encode($feedback);
            exit;    
            
		} catch(PDOException $e) {
			$error = 'ERROR: ' . $e->getMessage();
			$feedback = array('title' => 'Erro!', 'msg' => $error, 'type' => 'erro');
			echo json_encode($feedback);
			exit;
		}
	}

} 

else {
	$feedback = array('status' => 0, 'msg' => 'Erro interno! Não foi possível processar sua solicitação.');
	echo json_encode($feedback);
	exit;
}

?>
