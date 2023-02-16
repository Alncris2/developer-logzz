<?php

require_once (dirname(__FILE__) . '/../includes/config.php');
require_once (dirname(__FILE__) . '/../vendor/autoload.php');
$pagarme = new PagarMe\Client(PGME_API_KEY);

if(isset($_GET['user']) && isset($_GET['unique'])){
        
        $user__id = $_GET['user'];

        # Busca os dados do Usuário
        $get_current_plan = $conn->prepare('SELECT * FROM users INNER JOIN orders ON users.user__id = orders.user__id WHERE users.user__id = :user__id');
        $get_current_plan->execute(array('user__id' => $user__id));
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

        # Busca os dados do Pedido
        $get_producer_tax = $conn->prepare('SELECT meta_value FROM orders_meta WHERE order_number = :order_number AND meta_key = "producer_tax"');
        $get_producer_tax->execute(array('order_number' => $order_number));
        $producer_tax = $get_producer_tax->fetch();
        $producer_tax = round(($producer_tax['meta_value'] * 100), 0);

        $get_ship_tax = $conn->prepare('SELECT meta_value FROM orders_meta WHERE order_number = :order_number AND meta_key = "ship_tax"');
        $get_ship_tax->execute(array('order_number' => $order_number));
        $ship_tax = $get_ship_tax->fetch();
        $ship_tax = round(($ship_tax['meta_value'] * 100), 0);

        $amount = round(($ship_tax + $producer_tax), 0);

        # Verifica se há um cartão ativo
        $check_current_card = $conn->prepare('SELECT card_hash FROM cards WHERE card_user_id = :user__id AND card_active = 1');
        $check_current_card->execute(array('user__id' => $user__id));

        if ($check_current_card->rowCount() != 1 ){

            $feedback = array('type' => 'warning', 'title' => 'Erro ao Processar', 'msg' => 'Não foi possível realizar uma cobrança. O assinante não tem um cartão cadastrado.');
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
            $feedback = array('type' => 'warning', 'title' => 'Cadastro Incompleto', 'msg' => 'O cadastro do usuário não está completo.');
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
                        'title' => 'Taxa de Entrega - Pedido ' . $order_number,
                        'unit_price' => $ship_tax,
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
                    $type = 'success';
                    $title = 'Mudança Realizada!';
                    $msg = 'Uma cobrança de R$ ' . number_format(($amount/100), 2, ",", ".") . ' foi realizada no cartão do produtor.';

                    $stmt = $conn->prepare('UPDATE orders SET order_status = :order_status WHERE order_id = :order_id');
                    $stmt->execute(array('order_status' => $order_status, 'order_id' => $order_id));

                } else if ($transaction_status == 'refused'){
                    $type = 'error';
                    $title = 'Erro ao Processar Envio';
                    $msg = 'A cobrança de R$ ' . number_format(($amount/100), 2, ",", ".") . ' no cartão do produtor foi recusada.';
                
                } else {
                    $type = 'error';
                    $title = 'Erro ao Processar Envio';
                    $msg = 'Não foi possível realizar a cobrança. (' . $transaction_status . ')' ;
                }

                $feedback = array('type' => $type, 'title' => $title, 'msg' => $msg);
                echo json_encode($feedback);
                exit;
                
            }
            
            catch (exception $e) {

                $feedback = array('type' => 'warning', 'title' => 'Erro na Transação', 'msg' => "Não foi posível realizar a cobrança. Entre em contato com o Suporte.");
                echo json_encode($feedback);
                exit;

            }
} else {
     
}


?>