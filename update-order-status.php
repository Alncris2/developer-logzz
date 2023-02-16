<?php
error_reporting(-1);            
ini_set('display_errors', 1);     

require "includes/config.php";    
require "includes/classes/RequestAtendezap.php";
include "includes/classes/RandomStrGenerator.php";
session_name(SESSION_NAME);
session_start();

if(isset($_GET['id'])){
    $order_status = addslashes($_GET['status']); 
    $order_status_description = isset($_GET['status_description']) ? addslashes($_GET['status_description']) : null;
    $order_id = addslashes($_GET['id']);

    ///pegar order number para alterar pedido tanto do produtor quanto afiliado
    $queryN = $conn->prepare("SELECT order_number FROM orders  WHERE order_id = :order_id");
    $queryN->execute(['order_id' => $order_id]);
    $order_number = $queryN->fetch(\PDO::FETCH_ASSOC)["order_number"];

    $order_number = str_replace('AFI', '', $order_number);

    $queryN = $conn->prepare("SELECT order_id FROM orders  WHERE order_number = :order_number");
    $queryN->execute(['order_number' => $order_number]);
    $order_id = $queryN->fetch(\PDO::FETCH_ASSOC)["order_id"];
} 

if (isset($_GET['order_number'])) {
    $order_number = $_GET['order_number'];
    $order_status = $_GET['status'];  

    $order_number = str_replace('AFI', '', $order_number);
    $queryN = $conn->prepare("SELECT order_id FROM orders  WHERE order_number = :order_number");
    $queryN->execute(['order_number' => $order_number]);
    $order_id = $queryN->fetch(\PDO::FETCH_ASSOC)["order_id"]; 
}

switch ($order_status) {
    case 1: {
        $status_string = "Reagendado"; // redireciona para pagina de reagendar 

        if(isset($_GET['data_pedido'])){  
            $data = addslashes($_GET['data_pedido']);
    
            if ($data == "Data" || empty($data)) {
                $feedback = array('title' => 'Erro!', 'msg' => 'Informe nova data e período.', 'type' => 'warning');
                echo json_encode($feedback);
                exit;
            }

            $order_deadline  = pickerDateFormate($data);
            // getDataByReagendamento($order_id, $order_deadline);
            $delivery_period = '';
    
            if ($data == "Data" || empty($data)) {
                $feedback = array('title' => 'Erro!', 'msg' => 'Informe nova data e período.', 'type' => 'warning');
                echo json_encode($feedback);
                exit;
            }
    
            ///pegar order number para alterar pedido tanto do produtor quanto afiliado
            $queryN = $conn->prepare("SELECT order_number FROM orders  WHERE order_id = :order_id");
            $queryN->execute(['order_id' => $order_id]);
            $order_number = $queryN->fetch(\PDO::FETCH_ASSOC)["order_number"];
    
            $order_number = str_replace('AFI', '', $order_number);
    
            $queryN = $conn->prepare("SELECT order_id FROM orders  WHERE order_number = :order_number");
            $queryN->execute(['order_number' => $order_number]);
            $order_id = $queryN->fetch(\PDO::FETCH_ASSOC)["order_id"];
    
            // PEGAR QUANTIDADE DO PEDIDO E STATUS ANTERIOR
            $query = $conn->prepare("SELECT o.order_status, o.user__id,  s.sale_quantity, o.product_id FROM orders AS o INNER JOIN sales AS s WHERE o.sale_id = s.sale_id AND o.order_id = :order_id");
            $query->execute(['order_id' => $order_id]);
            $order_info = $query->fetch(\PDO::FETCH_ASSOC);
    
            if ($order_info['order_status'] == 3) { // STATUS ERA COMPLETO
                // PEGAR ID DA LOCALIDADE DO PEDIDO
                $get_order_operation = $conn->prepare("SELECT * FROM local_operations_orders lo INNER JOIN local_operations loo ON lo.operation_id=loo.operation_id WHERE order_id=:order_id");
                $get_order_operation->execute(array("order_id" => $order_id));
                $local_operations = $get_order_operation->fetch();
    
                if (!$local_operations) {
                    $feedback = array('title' => '', 'msg' => "Não existe estoque do produto para a cidade correspondente ao CEP inserido.", 'type' => 'error');
                    echo json_encode($feedback);
                    exit;
                }
    
                // // PEGAR SALDO ATUAL DO FINANCEIRO
                // $query = $conn->prepare("SELECT meta_value FROM transactions_meta AS t WHERE t.user__id = :user__id AND meta_key = 'anticipation_balance'");
                // $query->execute(['user__id' => $order_info['user__id']]);
                // $actual_value = $query->fetch(\PDO::FETCH_ASSOC)['meta_value'];
    
                // $new_value_meta = $actual_value - $order_infos['order_liquid_value'];
    
                // // REMOVER SALDO DO FINANCEIRO
                // $query = $conn->prepare("UPDATE transactions_meta AS t SET meta_value = :new_value_meta WHERE t.user__id = :user__id AND meta_key = 'anticipation_balance'");
                // $query->execute([
                //     'new_value_meta' => $new_value_meta,
                //     'user__id' => $order_info['user__id']
                // ]);



                
    
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
            }
            $stmt = $conn->prepare('UPDATE orders SET order_status = :order_status , order_deadline="' . $order_deadline . '"   WHERE order_id = :order_id   ');
            $stmt->execute(array('order_status' => 1, 'order_id' => $order_id));
    
            $stmt = $conn->prepare('UPDATE orders SET order_status = 1 , order_deadline="' . $order_deadline . '"  WHERE  order_number= "AFI' . $order_number . '"  ');
            $stmt->execute();
    
            $msg = "A entrega foi reagendada para " . date_format(date_create($order_deadline), "d/m/Y") . ".";
    
            $stmt_insert_historic = $conn->prepare('INSERT INTO order_details ( order_number, order_status) VALUES ( :order_number, :order_status)');
            $stmt_insert_historic->execute(array('order_number' => $order_number, 'order_status' => $order_status));

            
            sendWebhookStatusAuto($order_id);  
            $url = SERVER_URI . '/meu-pedido/' . $order_number;   
            $feedback = array('type' => 'success', 'msg' => $msg, 'title' => "Feito!", 'url' => $url );
            echo json_encode($feedback); 
            return; 
        }
         
        break;
    }    
    case 2: {
        $status_string = "Atrasado";
        $stmt = $conn->prepare('UPDATE orders SET order_status = :order_status WHERE order_id = :order_id');
        $stmt->execute(array('order_status' => $order_status, 'order_id' => $order_id));

        $stmt = $conn->prepare('UPDATE orders SET order_status = "' . $order_status . '" WHERE  order_number= "AFI' . $order_number . '" ');
        $stmt->execute();
        break;
    }
    case 3: {
        $status_string = "Completo"; // redireciona para completar
        break;
    }
    case 4: {
        $status_string = "Frustrado"; // redireciona para frustar 
        break;
    }
    case 5: {
        $status_string = "Cancelado";

        // PEGAR QUANTIDADE DO PEDIDO E STATUS ANTERIOR
        $query = $conn->prepare("SELECT o.order_status, o.user__id,  s.sale_quantity, o.product_id FROM orders AS o INNER JOIN sales AS s WHERE o.sale_id = s.sale_id AND o.order_id = :order_id");
        $query->execute(['order_id' => $order_id]);
        $order_info = $query->fetch(\PDO::FETCH_ASSOC);

        if ($order_info['order_status'] == 3) { // STATUS ERA COMPLETO
            // PEGAR ID DA LOCALIDADE DO PEDIDO
            $get_order_operation = $conn->prepare("SELECT * FROM local_operations_orders lo INNER JOIN local_operations loo ON lo.operation_id=loo.operation_id WHERE order_id=:order_id");
            $get_order_operation->execute(array("order_id" => $order_id));
            $local_operations = $get_order_operation->fetch();


            if (!$local_operations) {
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
        } else {
            $stmt = $conn->prepare('UPDATE orders SET order_status = :order_status, order_status_description = :order_status_description WHERE order_id = :order_id');
            $stmt->execute(array('order_status' => $order_status, 'order_status_description' => $order_status_description, 'order_id' => $order_id));
        }

        $stmt = $conn->prepare('UPDATE orders SET order_status = "' . $order_status . '" WHERE  order_number= "AFI' . $order_number . '" ');
        $stmt->execute();

        $stmt_insert_historic = $conn->prepare('INSERT INTO order_details ( order_number, order_status, order_status_description ) VALUES ( :order_number, :order_status, :order_status_description)');
        $stmt_insert_historic->execute(array('order_number' => $order_number, 'order_status' => $order_status, 'order_status_description' => $order_status_description));
        
        sendWebhookStatusAuto($order_id); 
        $msg = "Status do pedido foi alterado para " . $status_string . "."; 
        $feedback = array('type' => 'success', 'msg' => $msg, 'title' => "Feito!"); 
        echo json_encode($feedback);
        exit;
        break;
    }
    case 6: { // CENTRO DE DISTRIBUIÇÃO
        $status_string = "A Enviar";
        break;
    }
    case 7: { // CENTRO DE DISTRIBUIÇÃO
        $status_string = "Enviando";
        break;
    }
    case 8: { // CENTRO DE DISTRIBUIÇÃO
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

        if ($max_charge_amount < $pending_charges) {

            require_once(dirname(__FILE__) . '/vendor/autoload.php');
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

            if ($check_current_card->rowCount() != 1) {

                $feedback = array('type' => 'error', 'title' => 'Envio Não Permitido', 'msg' => 'O usuário possui pendências financeiras e não tem um cartão cadastrado ativo para cobrança.');
                echo json_encode($feedback);
                exit;
            } else {

                # Pega Hash do Cartão
                $current_card = $check_current_card->fetch();
                $card_hash = $current_card['card_hash'];
            }

            if ($current_plan_details['company_type'] == 'fisica') {
                $company_type = 'individual';
                $doc = 'cpf';
            } else if ($current_plan_details['company_type'] == 'juridica') {
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
                        'phone_numbers' => [$user_phone]
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
                if ($transaction_status == 'paid') {

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
                } else if ($transaction_status == 'refused') {
                    $type = 'error';
                    $title = 'Envio Não Permitido';
                    $msg = 'O usuário possui pendências financeiras [4]';
                    $feedback = array('type' => $type, 'title' => $title, 'msg' => $msg);
                    echo json_encode($feedback);
                    exit;
                } else {
                    $type = 'error';
                    $title = 'Envio Não Permitido';
                    $msg = 'O usuário possui pendências financeiras [5]';
                    $feedback = array('type' => $type, 'title' => $title, 'msg' => $msg);
                    echo json_encode($feedback);
                    exit;
                }
            } catch (exception $e) {

                $feedback = array('type' => 'warning', 'title' => 'Envio Não Permitido', 'msg' => 'O usuário possui pendências financeiras [6]');
                echo json_encode($feedback);
                exit;
            }
        }

        # Cria a cobrança
        $create_charge_request = $conn->prepare('INSERT INTO billings (billing_id, user__id, billing_value, billing_bank_account, billing_type, billing_request, billing_orders_referency) VALUES (:billing_id, :user__id, :billing_value, :billing_bank_account, :billing_type, :billing_request, :billing_orders_referency)');
        $create_charge_request->execute(array('billing_id' => '0', 'user__id' => $user__id, 'billing_value' => $billing_value, 'billing_bank_account' => '0', 'billing_type' => 'COBRANCA', 'billing_request' => $today, 'billing_orders_referency' => $order_number));

        break;    
    }
    case 9: {
        $status_string = "Reembolsado"; // redireciona para pagina de reagendar  

        $confirme_order_status = $conn->prepare('SELECT * FROM orders WHERE order_id = :order_id LIMIT 1');
        $confirme_order_status->execute(array('order_id' => $order_id));
        $order_infos = $confirme_order_status->fetch();

        if (!$order_infos) {
            $feedback = array('title' => 'Erro!', 'msg' =>  'Atualize a página e tente novamente', 'type' => 'error');
            echo json_encode($feedback);
            exit;
        }

        if ($order_infos['order_refunded'] != 0) {
            $feedback = array('title' => 'Erro!', 'msg' => 'Este pedido já foi reembolsado.', 'type' => 'warning');
            echo json_encode($feedback);
            exit;
        }

        if ($order_infos['order_anticipation_released'] == 0) {
            $feedback = array('title' => 'Erro!', 'msg' => 'Este pedido não pode ser reembolsado, para o pedido ser reembolsado precisa já ter sido completo alguma vez. ', 'type' => 'warning');
            echo json_encode($feedback);
            exit;
        }

        $order_status_update = date('Y-m-d H:i:s');

        // PEGAR QUANTIDADE DO PEDIDO E STATUS ANTERIOR
        $query = $conn->prepare("SELECT o.order_status, o.user__id,  s.sale_quantity, o.product_id, order_commission_released, order_liquid_value FROM orders AS o INNER JOIN sales AS s WHERE o.sale_id = s.sale_id AND o.order_id = :order_id");
        $query->execute(['order_id' => $order_infos['order_id']]);
        $order_info = $query->fetch(\PDO::FETCH_ASSOC);

        // PEGAR ID DA LOCALIDADE DO PEDIDO
        $get_order_operation = $conn->prepare("SELECT * FROM local_operations_orders lo INNER JOIN local_operations loo ON lo.operation_id=loo.operation_id WHERE order_id=:order_id");
        $get_order_operation->execute(array("order_id" => $order_infos['order_id']));
        $local_operations = $get_order_operation->fetch();

        if (!$local_operations) {
            $feedback = array('status' => 2, 'msg' => "Não existe estoque do produto para a cidade correspondente ao CEP inserido.", 'type' => 'warning');
            echo json_encode($feedback);
            exit;
        }

        if (!$local_operations) { 
            $feedback = array('status' => 2, 'msg' => $order_info['order_commission_released'], 'type' => 'warning');
            echo json_encode($feedback);
            exit; 
        }

        $get_user_plan_shipping_tax = $conn->prepare("SELECT user_plan_shipping_tax FROM subscriptions AS s WHERE s.user__id = :user__id");
        $get_user_plan_shipping_tax->execute(array('user__id' => $order_info['user__id']));
  
        $get_tax_order = $conn->prepare('SELECT meta_value, meta_key FROM orders_meta WHERE meta_key IN ("producer_tax","member_tax") AND order_number = :order_number');
        $get_tax_order->execute(array('order_number' => $order_number));    
        while($tax_order = $get_tax_order->fetch()){  
            if($tax_order['meta_key'] == 'producer_tax'){
                $producer_tax = $tax_order['meta_value'];
            }
            if($tax_order['meta_key'] == 'member_tax'){
                $member_tax = $tax_order['meta_value'];
            }
        } 

        #  PEGAR SALDO ATUAL DO FINANCEIRO DO AFILIADO
        $query = $conn->prepare("SELECT transaction_id FROM transactions AS t WHERE t.user_id = :user__id AND type = 7 AND order_number = :order_number AND date_end > now()");
        $query->execute(array('user__id' => $order_info['user__id'], 'order_number' => $order_number));
        if ($transaction = $query->fetch()) {    
            # ANTECIPA VALOR DO PEDIDO PARA COBRAR NO DISPONIVEL
            $query = $conn->prepare("UPDATE transactions SET date_end = now(), status = 2  WHERE transaction_id = :transaction_id");
            $query->execute(array('transaction_id' => $transaction['transaction_id']));
        }
        $stmt = $conn->prepare('UPDATE orders SET order_commission_released = 1, order_commission_date = :order_commission_date WHERE order_number = :order_number');
        $stmt->execute(array('order_number' => $order_number, 'order_commission_date' => date('Y-m-d H:i:s')));

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

        $user_plan_shipping_tax = $get_user_plan_shipping_tax->fetch()['0'];    
        $set_new_anticipation_value = $conn->prepare('INSERT INTO transactions (user_id, value_liquid, value_brute, tax_value, logistic_value, status, type, date_start, date_end, order_number, transaction_code) VALUES (:user_id, :value_liquid, :value_brute, :tax_value, :logistic_value, :status, :type, :date_start, :date_end, :order_number, :transaction_code)');
        $set_new_anticipation_value->execute(array(
            'user_id'      => $order_info['user__id'], 
            'value_liquid'   => -($order_info['order_liquid_value'] + ($user_plan_shipping_tax + $producer_tax)),
            'value_brute'   => -($order_info['order_liquid_value']),
            'tax_value'     => $producer_tax, 
            'logistic_value'=> $user_plan_shipping_tax, 
            'status'        => 4, 
            'type'          => 8, 
            'date_start'    => date('Y-m-d H:i:s'), 
            'date_end'      => date('Y-m-d H:i:s'), 
            'order_number'  => $order_number,
            'transaction_code' => $transaction_code
        ));
        $new_liquid_value_productor = $order_infos['order_liquid_value'] - ($order_infos['order_liquid_value'] + $user_plan_shipping_tax + $producer_tax);


        $get_orders_afiliate = $conn->prepare('SELECT order_id, order_number, order_liquid_value, user__id FROM orders WHERE order_number = :order_number');
        $get_orders_afiliate->execute(array('order_number' => "AFI" . $order_number));
        if($info_order_afiliate = $get_orders_afiliate->fetch()){   
            $order_liquid_value_afi = $info_order_afiliate['order_liquid_value'];
            $order_id_afiliate      = $info_order_afiliate['order_id'];
            $order_number_afiliate  = $info_order_afiliate['order_number'];
            $affiliated_id          = $info_order_afiliate['user__id'];
            
            #  PEGAR SALDO ATUAL DO FINANCEIRO DO AFILIADO
            $query = $conn->prepare("SELECT transaction_id FROM transactions AS t WHERE t.user_id = :user__id AND type = 7 AND order_number = :order_number AND date_end > now()");
            $query->execute(array('user__id' => $affiliated_id, 'order_number' => "AFI" . $order_number));
            if ($transaction_affiliated = $query->fetch()) {    
                # ANTECIPA VALOR DO PEDIDO PARA COBRAR NO DISPONIVEL
                $query = $conn->prepare("UPDATE transactions SET date_end = now(), status = 2  WHERE transaction_id = :transaction_id");
                $query->execute(array('transaction_id'    => $transaction_affiliated['transaction_id']));
    
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
            
            $set_new_anticipation_value = $conn->prepare('INSERT INTO transactions (user_id, value_liquid, value_brute, tax_value, logistic_value, status, type, date_start, date_end, order_number, transaction_code) VALUES (:user_id, :value_liquid, :value_brute, :tax_value, :logistic_value, :status, :type, :date_start, :date_end, :order_number, :transaction_code)');
            $set_new_anticipation_value->execute(array(
                'user_id'      => $affiliated_id, 
                'value_liquid'  => -($order_liquid_value_afi + ($member_tax)),
                'value_brute'   => -($order_liquid_value_afi),
                'tax_value'     => -($member_tax), 
                'logistic_value'=> 0.00, 
                'status'        => 4, 
                'type'          => 8, 
                'date_start'    => date('Y-m-d H:i:s'), 
                'date_end'      => date('Y-m-d H:i:s'), 
                'order_number'  => "AFI" . $order_number,
                'transaction_code' => $transaction_code
            ));
    
            
            $stmt = $conn->prepare('UPDATE orders SET order_commission_released = 1, order_commission_date = :order_commission_date WHERE order_id = :order_id');
            $stmt->execute(array('order_id' => $order_id_afiliate, 'order_commission_date' => date('Y-m-d H:i:s')));
            $new_liquid_value_afiliate = $order_liquid_value_afi - ($order_liquid_value_afi + $member_tax);
        }

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

        $stmt = $conn->prepare('UPDATE orders SET order_status = 9, order_refunded = 1, order_status_update = :order_status_update WHERE order_number = :order_number');
        $stmt->execute(array('order_number' => $order_number, 'order_status_update' => $order_status_update));

        $stmt = $conn->prepare('UPDATE orders SET order_status = 9, order_refunded = 1, order_status_update = :order_status_update WHERE  order_number= :order_number');
        $stmt->execute(array('order_number' => "AFI" . $order_number, 'order_status_update' => $order_status_update));

        break;
    }
    case 10: {
        $status_string = "Confirmado";

        // PEGAR QUANTIDADE DO PEDIDO E STATUS ANTERIOR
        $query = $conn->prepare("SELECT o.order_status, o.user__id,  s.sale_quantity, o.product_id, order_commission_released FROM orders AS o INNER JOIN sales AS s WHERE o.sale_id = s.sale_id AND o.order_id = :order_id");
        $query->execute(['order_id' => $order_id]); 
        $order_info = $query->fetch(\PDO::FETCH_ASSOC); 

        // PEGAR ID DA LOCALIDADE DO PEDIDO
        $get_order_operation = $conn->prepare("SELECT * FROM local_operations_orders lo INNER JOIN local_operations loo ON lo.operation_id=loo.operation_id WHERE order_id=:order_id");
        $get_order_operation->execute(array("order_id" => $order_id));
        $local_operations = $get_order_operation->fetch();

        if (!$local_operations) {
            $feedback = array('status' => 2, 'msg' => "Não existe estoque do produto para a cidade correspondente ao CEP inserido.", 'type' => 'warning');
            echo json_encode($feedback);
            exit;
        }  

        // GERAR INVENTORY META PARA AS CONSULTAS 
        $inventory_meta = $order_info['user__id'] . "-" . $order_info['product_id'] . "-" . $local_operations['operation_id']; //423 13 

        // // PEGAR QUANTIDADE ATUAL NO INVENTÁRIO 
        // $query = $conn->prepare("SELECT inventory_quantity, inventory_id FROM inventories AS i WHERE i.inventory_product_id = :inventory_product_id AND inventory_locale_id = :inventory_locale_id AND i.ship_locale = 0");
        // $query->execute(array('inventory_product_id' => $order_info['product_id'], 'inventory_locale_id' => $local_operations['operation_id'] )); 
         
        // if (!$inventory_data = $query->fetch()) {
        //     $feedback = array('status' => 2, 'msg' => "Não Conseguimos atualizar a quantidade de estoque, por favor, atualize a página.", 'type' => 'warning');
        //     echo json_encode($feedback);  
        //     exit; 
        // } 
        // $inventory_qtd = $inventory_data['inventory_quantity'];
        // $inventory_id = $inventory_data['inventory_id'];  

        // // SUBTRAI QUANTIDADE NOVAMENTE AO INVENTARIO
        // $query = $conn->prepare("UPDATE inventories AS i SET i.inventory_quantity = :inventory_quantity WHERE i.inventory_id = :inventory_id AND i.ship_locale = 0");
        // $query->execute([
        //     'inventory_quantity' => $inventory_qtd - $order_info['sale_quantity'],
        //     'inventory_id' => $inventory_id  
        // ]);


        $statement = $conn->prepare("UPDATE orders SET order_status = 10 WHERE  order_number = :order_number");
        $statement->execute(array("order_number" => $order_number));

        $statement = $conn->prepare("UPDATE orders SET order_status = 10 WHERE  order_number = :order_number");
        $statement->execute(array("order_number" => 'AFI' . $order_number)); 
 
        $msg = "Seu pedido está confirmado. A partir de agora, caso deseje reagendar ou cancelar sua entrega por qualquer eventual contratempo, contacte através do WhatsApp";  
        $url = SERVER_URI . '/meu-pedido/' . $order_number;  

        
        $stmt_insert_historic = $conn->prepare('INSERT INTO order_details ( order_number, order_status ) VALUES ( :order_number, :order_status )');
        $stmt_insert_historic->execute(array('order_number' => $order_number, 'order_status' => $order_status));

        sendWebhookStatusAuto($order_id);
        $feedback = array('type' => 'success', 'msg' => $msg, 'title' => "Feito!", 'url' => $url );
        echo json_encode($feedback);  
        return;
        break; 
    }
    case 11: {
        $status_string = "Expirado"; // redireciona para pagina de reagendar  
        $stmt = $conn->prepare('UPDATE orders SET order_status = :order_status, order_status_description = "Cliente não confimou o pedido" WHERE order_id = :order_id');
        $stmt->execute(array('order_status' => $order_status, 'order_id' => $order_id));  

        $stmt = $conn->prepare('UPDATE orders SET order_status = :order_status, order_status_description = "Cliente não confimou o pedido" WHERE order_number = :order_number');
        $stmt->execute(array('order_status' => $order_status, 'order_number' => 'AFI'. $order_number));  
        
        $stmt_insert_historic = $conn->prepare('INSERT INTO order_details ( order_number, order_status ) VALUES ( :order_number, :order_status )');
        $stmt_insert_historic->execute(array('order_number' => $order_number, 'order_status' => $order_status));
        
        sendWebhookStatusAuto($order_id);
        $feedback = array('type' => 'success', 'msg' => "Status do pedido foi Expirado", 'title' => "Feito!" ) ;
        echo json_encode($feedback);              
        return;
        break;
    }
    case 12: {
        $status_string = "Indisponível"; 

        // PEGAR QUANTIDADE DO PEDIDO E STATUS ANTERIOR
        
        $stmt = $conn->prepare('UPDATE orders SET order_status = :order_status, order_status_description = :order_status_description WHERE order_id = :order_id');
        $stmt->execute(array('order_status' => $order_status, 'order_status_description' => $order_status_description, 'order_id' => $order_id));

        $stmt = $conn->prepare('UPDATE orders SET order_status = :order_status, order_status_description = :order_status_description WHERE  order_number= :order_number ');
        $stmt->execute(array('order_status' => $order_status, 'order_status_description' => $order_status_description, 'order_number' => "AFI". $order_number));

        $stmt_insert_historic = $conn->prepare('INSERT INTO order_details ( order_number, order_status, order_status_description ) VALUES ( :order_number, :order_status, :order_status_description)');
        $stmt_insert_historic->execute(array('order_number' => $order_number, 'order_status' => $order_status, 'order_status_description' => $order_status_description));

        sendWebhookStatusAuto($order_id); 
        $msg = "Status do pedido foi alterado para " . $status_string . "."; 
        $feedback = array('type' => 'success', 'msg' => $msg, 'title' => "Feito!"); 
        echo json_encode($feedback);
        exit;

        break;
    }
    default:
        $status_string = "Agendado";
        break;
} 

$stmt_producer = $conn->prepare('UPDATE orders SET order_status = :order_status WHERE order_id = :order_id');
$stmt_producer->execute(array('order_status' => $order_status, 'order_id' => $order_id));

$stmt_member = $conn->prepare('UPDATE orders SET order_status = :order_status WHERE  order_number= :order_number');
$stmt_member->execute(array('order_status' => $order_status, 'order_number' => "AFI" . $order_number)); 

$stmt_insert_historic = $conn->prepare('INSERT INTO order_details ( order_number, order_status ) VALUES ( :order_number, :order_status )');
$stmt_insert_historic->execute(array('order_number' => $order_number, 'order_status' => $order_status));

sendWebhookStatusAuto($order_id); 
$msg = "Status do pedido foi alterado para " . $status_string . "."; 
$feedback = array('type' => 'success', 'msg' => $msg, 'title' => "Feito!"); 
echo json_encode($feedback);
exit;
