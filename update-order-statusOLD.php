<?php

    require "includes/config.php";
    session_name(SESSION_NAME);
    session_start();

    $order_status = addslashes($_GET['status']);
    $order_id = addslashes($_GET['id']);

    switch ($order_status) {
        case 1:
            $status_string = "Reagendado"; // redireciona para pagina de reagendar 
            break;
        case 2:
            $status_string = "Atrasado";
            $stmt = $conn->prepare('UPDATE orders SET order_status = :order_status WHERE order_id = :order_id');
            $stmt->execute(array('order_status' => $order_status, 'order_id' => $order_id));
        break;
        case 3:
            $status_string = "Completo"; // redireciona para completar
            break;
        case 4:
            $status_string = "Frustrado"; // redireciona para frustar 
            break;
        case 5:
            $status_string = "Cancelado";
            
            // PEGAR QUANTIDADE DO PEDIDO E STATUS ANTERIOR
            $query = $conn->prepare("SELECT o.order_status, o.user__id,  s.sale_quantity, o.product_id FROM orders AS o INNER JOIN sales AS s WHERE o.sale_id = s.sale_id AND o.order_id = :order_id");
            $query->execute(['order_id' => $order_id]);
            $order_info = $query->fetch(\PDO::FETCH_ASSOC);

            if($order_info['order_status'] == 3){ // STATUS ERA COMPLETO
                // PEGAR ID DA LOCALIDADE DO PEDIDO
                $get_order_operation = $conn->prepare("SELECT * FROM local_operations_orders lo INNER JOIN local_operations loo ON lo.operation_id=loo.operation_id WHERE order_id=:order_id");
                $get_order_operation->execute(array("order_id" => $order_id));
                $local_operations = $get_order_operation->fetch();
                

                if(!$local_operations){
                    $feedback = array('status' => 2, 'msg' => "Não existe estoque do produto para a cidade correspondente ao CEP inserido.");
                    echo json_encode($feedback);
                    exit;
                }
                
                // PEGAR SALDO ATUAL DO FINANCEIRO
				$query = $conn->prepare("SELECT meta_value FROM transactions_meta AS t WHERE t.user__id = :user__id AND meta_key = 'anticipation_balance'");
				$query->execute(['user__id' => $order_info['user__id']]);
				$actual_value = $query->fetch(\PDO::FETCH_ASSOC)['meta_value'];

				$new_value_meta = $actual_value - $order_infos['order_liquid_value'];
				
				// REMOVER SALDO DO FINANCEIRO
				$query = $conn->prepare("UPDATE transactions_meta AS t SET meta_value = :new_value_meta WHERE t.user__id = :user__id AND meta_key = 'anticipation_balance'");
				$query->execute([
					'new_value_meta' => $new_value_meta,
					'user__id' => $order_info['user__id']
				]);	

                // GERAR INVENTORY META PARA AS CONSULTAS 
                $inventory_meta = $order_info['user__id'] . "-" . $order_info['product_id'] . "-" . $local_operations['operation_id'];

                // PEGAR QUANTIDADE ATUAL NO INVENTÁRIO 
                $query = $conn->prepare("SELECT inventory_quantity FROM inventories AS i WHERE i.inventory_meta = :inventory_meta AND i.ship_locale = 0");
                $query->execute(['inventory_meta' => $inventory_meta]);
                $inventory_qtd = $query->fetch(\PDO::FETCH_ASSOC)['inventory_quantity'];

                
                // ACRESENTAR QUANTIDADE NOVAMENTE AO INVENTARIO
                $query = $conn->prepare("UPDATE inventories AS i SET i.inventory_quantity = :inventory_quantity WHERE i.inventory_meta = :inventory_meta AND i.ship_locale = 0");
                $query->execute([
                    'inventory_quantity' => $inventory_qtd + $order_info['sale_quantity'],
                    'inventory_meta' => $inventory_meta
                ]);
            }else{
                $stmt = $conn->prepare('UPDATE orders SET order_status = :order_status WHERE order_id = :order_id');
                $stmt->execute(array('order_status' => $order_status, 'order_id' => $order_id));
            }

        break;
        case 6: // CENTRO DE DISTRIBUIÇÃO
            $status_string = "A Enviar";
            break;
        case 7: // CENTRO DE DISTRIBUIÇÃO
            $status_string = "Enviando";
            break;
        case 8: // CENTRO DE DISTRIBUIÇÃO
            $status_string = "Enviado";

            $today = date("Y-m-d H:i:s");

            # Busca os dados do User e da Order
            $get_user_id = $conn->prepare('SELECT orders.user__id AS OI, user_plan_shipping_tax AS ST, order_number AS ONU FROM orders INNER JOIN subscriptions ON subscriptions.user__id = orders.user__id WHERE order_id = :order_id');
            $get_user_id->execute(array('order_id' => $order_id));
            $get_user_id = $get_user_id->fetch();
            $user__id = $get_user_id['OI'];
            $billing_value = $get_user_id['ST'];
            $order_number = $get_user_id['ONU'];

            # Verifica inadimplência
            $stmt = $conn->prepare('SELECT last_charge_ok, max_charge_amount FROM users WHERE user__id = :user__id');
            $stmt->execute(['user__id' => $user__id]);
            $result = $stmt->fetch();
            $last_charge_ok = $result['last_charge_ok'];
            $max_charge_amount = round($result['max_charge_amount'] * 100, 0);

            if ($last_charge_ok == 0) {

                $feedback = array('type' => 'error', 'title' => 'Envio Não Permitido', 'msg' => "O usuário possui pendências financeiras [1]");
                echo json_encode($feedback);
                exit;

            }

            # Verifica total de cobranças pendentes
            $get_pending_charge = $conn->prepare('SELECT SUM(billing_value) AS TOTAL FROM billings WHERE user__id = :user__id AND (billing_released IS NULL AND billing_type = "COBRANCA")');
            $get_pending_charge->execute(array('user__id' => $user__id));
            $total_pending_charges = $get_pending_charge->fetch();
            $amount = $pending_charges = round(($total_pending_charges['TOTAL'] * 100), 0);

            if ($max_charge_amount < $pending_charges){

                require_once (dirname(__FILE__) . '/vendor/autoload.php');
                $pagarme = new PagarMe\Client(PGME_API_KEY);

                # Busca os dados do Usuário
                $get_current_plan = $conn->prepare('SELECT * FROM users INNER JOIN orders ON users.user__id = orders.user__id WHERE orders.order_id = :order_id');
                $get_current_plan->execute(array('order_id' => $order_id));
                $current_plan_details = $get_current_plan->fetch();
                $user_name = $current_plan_details['full_name'];
                $user_email = $current_plan_details['email'];
                $user__id = $current_plan_details['user__id'];
                $user_code  = $current_plan_details['user_code'];
                $doc_number  = $current_plan_details['company_doc'];
                $user_phone  = $current_plan_details['user_phone'];
                $chars = array(".", "-", "/", " ", "(", ")");
                $user_phone = "+55" . (str_replace($chars, "", $user_phone));
                $order_number  = $current_plan_details['order_number'];
        
                # Verifica se há um cartão ativo
                $check_current_card = $conn->prepare('SELECT card_hash FROM cards WHERE card_user_id = :user__id AND card_active = 1');
                $check_current_card->execute(array('user__id' => $user__id));
        
                if ($check_current_card->rowCount() != 1 ){
        
                    $feedback = array('type' => 'error', 'title' => 'Envio Não Permitido', 'msg' => 'O usuário possui pendências financeiras e não tem um cartão cadastrado ativo para cobrança.');
                    echo json_encode($feedback);
                    exit;
        
                } else {
                    
                    # Pega Hash do Cartão
                    $current_card = $check_current_card->fetch();
                    $card_hash = $current_card['card_hash'];
        
                }
        
                if ($current_plan_details['company_type'] == 'fisica'){
                    $company_type = 'individual';
                    $doc = 'cpf';
                } else if ($current_plan_details['company_type'] == 'juridica'){
                    $company_type = 'corporation';
                    $doc = 'cnpj';
                } else {
                    $feedback = array('type' => 'error', 'title' => 'Envio Não Permitido', 'msg' => 'Há divergências nas informaçõe de cadastro do Produtor, impossibilitando a cobrança de valores pendentes.');
                    echo json_encode($feedback);
                    exit;
                }
        
                    try {
                        # Faz a cobrança
                        $transaction = $pagarme->transactions()->create([
                            'amount' => $amount,
                            'card_id' => $card_hash,
                            'payment_method' => 'credit_card',
                            'postback_url' => 'http://requestb.in/pkt7pgpk',
                            'customer' => [
                            'external_id' => $user_code,
                            'name' => $user_name, 
                            'email' => $user_email,
                            'type' => $company_type,
                            'country' => 'br',
                            'documents' => [
                                [
                                'type' => $doc,
                                'number' => $doc_number
                                ]
                            ],
                            'phone_numbers' => [ $user_phone ]
                            ],
                            'items' => [
                                [
                                'id' => '1',
                                'title' => 'Taxas ' . strtoupper($user_code),
                                'unit_price' => $amount,
                                'quantity' => 1,
                                'tangible' => true
                                ],
                            ]
                        ]);
                        
                        # Busca o Status da Transação de Cobrança
                        $transaction_id = $transaction->id; 
                        $tries = 0;
        
                        do {
                            sleep(3);
                            $transaction = $pagarme->transactions()->get([
                                'id' => $transaction_id
                            ]);
                            $transaction_status = $transaction->status;
                            $tries = $tries + 1;    
                        } while ($transaction_status == 'processing' && $tries < 3);
        
                        # Feedback ao usuário
                        if ($transaction_status == 'paid'){
        
                            $stmt = $conn->prepare('UPDATE orders SET order_status = :order_status WHERE order_id = :order_id');
                            $stmt->execute(array('order_status' => $order_status, 'order_id' => $order_id));
                            
                            # Marca as cobranças que acabaram de ser pagas como PAGAS
                            $get_pending_charge = $conn->prepare('SELECT billing_id FROM billings WHERE user__id = :user__id AND (billing_released IS NULL AND billing_type = "COBRANCA")');
                            $get_pending_charge->execute(array('user__id' => $user__id));
                            
                            while ($pending_charges = $get_pending_charge->fetch()) {
                                $billing_id = $pending_charges['billing_id'];

                                $set_as_paid = $conn->prepare('UPDATE billings SET billing_released = :billing_released WHERE billing_id = :billing_id');
                                $set_as_paid->execute(array('billing_released' => $today, 'billing_id' => $billing_id));
                            }
        
                        } else if ($transaction_status == 'refused'){
                            $type = 'error';
                            $title = 'Envio Não Permitido';
                            $msg = 'O usuário possui pendências financeiras [4]';
                            $feedback = array('type' => $type, 'title' => $title, 'msg' => $msg);
                            echo json_encode($feedback);
                            exit;
                        
                        } else {
                            $type = 'error';
                            $title = 'Envio Não Permitido';
                            $msg = 'O usuário possui pendências financeiras [5]' ;
                            $feedback = array('type' => $type, 'title' => $title, 'msg' => $msg);
                            echo json_encode($feedback);
                            exit;
                        }
                        
                    }
                    
                    catch (exception $e) {
        
                        $feedback = array('type' => 'warning', 'title' => 'Envio Não Permitido', 'msg' => 'O usuário possui pendências financeiras [6]');
                        echo json_encode($feedback);
                        exit;
        
                    }

            }

            # Cria a cobrança
            $create_charge_request = $conn->prepare('INSERT INTO billings (billing_id, user__id, billing_value, billing_bank_account, billing_type, billing_request, billing_orders_referency) VALUES (:billing_id, :user__id, :billing_value, :billing_bank_account, :billing_type, :billing_request, :billing_orders_referency)');
            $create_charge_request->execute(array('billing_id' => '0', 'user__id' => $user__id, 'billing_value' => $billing_value, 'billing_bank_account' => '0', 'billing_type' => 'COBRANCA', 'billing_request' => $today, 'billing_orders_referency' => $order_number));
            
            break;

        default:
            $status_string = "Agendado";
            $stmt = $conn->prepare('UPDATE orders SET order_status = :order_status WHERE order_id = :order_id');
            $stmt->execute(array('order_status' => $order_status, 'order_id' => $order_id));
        break;
    }

    $stmt = $conn->prepare('UPDATE orders SET order_status = :order_status WHERE order_id = :order_id');
    $stmt->execute(array('order_status' => $order_status, 'order_id' => $order_id));

    $msg = "Status do pedido foi alterado para " . $status_string . ".";

	$feedback = array('type' => 'success', 'msg' => $msg, 'title' => "Feito!");
	echo json_encode($feedback);
	exit;



?>