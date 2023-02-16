<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
require "../includes/config.php";
require "../includes/classes/RequestAtendezap.php";
include "../includes/classes/RandomStrGenerator.php"; 


$verify = 1;
if ($_POST['action'] == 'entrega-frustrada') {

    $order_number = addslashes($_POST['order']);

    $confirme_order_status = $conn->prepare('SELECT * FROM orders WHERE order_number = :order_number LIMIT 1');
    $confirme_order_status->execute(array('order_number' => $order_number));

    $order_infos = $confirme_order_status->fetch();

    $order_id  = $order_infos['order_id'];

    if ($order_infos['order_status'] == 4) {
        $feedback = array('title' => 'Erro!', 'msg' => 'O pedido já havia sido definido como frustrado anterioramente.', 'type' => 'warning');
        echo json_encode($feedback);
        exit;
    }

    $operador_id = addslashes($_POST['operador']);
    if (!preg_match("/^[(0-9)]*$/", $operador_id)) {
        $feedback = array('status' => 0, 'msg' => 'Selecione um operador logístico', 'type' => 'error');
        echo json_encode($feedback);
        exit;
    }

    $client_freight = null;
    if (isset($_POST['confirm-pay']) && $_POST['confirm-pay'] == 'on') {
        $client_freight = floatval(addslashes(str_replace(',', '.', str_replace('.', '', $_POST['valor-pago']))));
        if (empty($client_freight)) {
            $feedback = array('status' => 0, 'msg' => 'Informe o valor que o cliente pago. ', 'type' => 'warning');
            echo json_encode($feedback);
            exit;
        }
    }

    $order_status_update = date('Y-m-d H:i:s');


    if (!isset($_FILES['comprovante-tentativa-entrega']) || $_FILES['comprovante-tentativa-entrega']['size'] <= 0) {
        $feedback = array('title' => 'Nenhum arquivo...', 'msg' => 'Você precisa anexar a foto de comprovação de tentativa de entrega.', 'type' => 'warning');
        echo json_encode($feedback);
        exit;
    } else {
        $filetypes = array('jfif', 'png', 'jpeg', 'jpg');
        $image_filetype_array = explode('.', $_FILES['comprovante-tentativa-entrega']['name']);
        $filetype = strtolower(end($image_filetype_array));
    
        // Valida se a extensão do arquivo é aceita
        if (in_array($filetype, $filetypes) == false) {
            $feedback = array('status' => 0, 'msg' => 'A imagem precisa estar em formato PNG, JPG ou JFIF.');
            echo json_encode($feedback);
            exit;
        }
    
        $fail_delivery_attemp = 'comprovante_entrega_' . $order_number . '.' . $filetype; //Definindo um novo nome para o arquivo
        $dir = '../uploads/pedidos/frustrados/'; //Diretório para uploads 
        if (move_uploaded_file($_FILES['comprovante-tentativa-entrega']['tmp_name'], $dir . $fail_delivery_attemp)) {
        } else {
            $feedback = array('status' => 0, 'msg' => 'Não deu! Erro ao fazer upload da imagem.');
            echo json_encode($feedback);
            exit;
        }
    }

    ////UPLOAD DA IMAGEM COMPROVANTE

    if (!isset($_FILES['comprovante-tentativa-contato']) || $_FILES['comprovante-tentativa-contato']['size'] <= 0) {
        $feedback = array('status' => 0, 'msg' => 'Faça o upload do compravante de tentativa de contato', 'type' => 'warning', 'title' => 'Comprovante de Tentativa de Contato');
        echo json_encode($feedback);
        exit;
    } else {
        $filetypes = array('jfif', 'png', 'jpeg', 'jpg');
        $image_filetype_array = explode('.', $_FILES['comprovante-tentativa-contato']['name']);
        $filetype = strtolower(end($image_filetype_array));
    
        // Valida se a extensão do arquivo é aceita
        if (in_array($filetype, $filetypes) == false) {
            $feedback = array('status' => 0, 'msg' => 'A imagem precisa estar em formato PNG, JPG ou JFIF', 'type' => 'warning');
            echo json_encode($feedback);
            exit;
        }
    
        $proof_contact_attempt = 'comprovante_contato_' . $order_number . '.' . $filetype; //Definindo um novo nome para o arquivo
        $dir = '../uploads/pedidos/frustrados/'; //Diretório para uploads 
        if (!move_uploaded_file($_FILES['comprovante-tentativa-contato']['tmp_name'], $dir . $proof_contact_attempt)) {
            $feedback = array('status' => 0, 'msg' => 'Não deu! Erro ao fazer upload da imagem.', 'type' => 'danger');
            echo json_encode($feedback);
            exit;
        }
    }



    // 3  KMujiJ
    try {

        // PEGAR QUANTIDADE DO PEDIDO E STATUS ANTERIOR
        $query = $conn->prepare("SELECT o.order_status, o.user__id,  s.sale_quantity, o.product_id, o.order_commission_released FROM orders AS o INNER JOIN sales AS s WHERE o.sale_id = s.sale_id AND o.order_id = :order_id");
        $query->execute(['order_id' => $order_infos['order_id']]);
        $order_info = $query->fetch(\PDO::FETCH_ASSOC);

        if ($order_info['order_status'] == 3) { // STATUS ERA COMPLETO
            // PEGAR ID DA LOCALIDADE DO PEDIDO
            $get_order_operation = $conn->prepare("SELECT * FROM local_operations_orders lo INNER JOIN local_operations loo ON lo.operation_id=loo.operation_id WHERE order_id=:order_id");
            $get_order_operation->execute(array("order_id" => $order_infos['order_id']));
            $local_operations = $get_order_operation->fetch();

            if (!$local_operations) {
                $feedback = array('status' => 2, 'msg' => "Não existe estoque do produto para a cidade correspondente ao CEP inserido.");
                echo json_encode($feedback);
                exit;
            }

            $update_operator = $conn->prepare('UPDATE local_operations_orders SET responsible_id = :responsible_id WHERE id = :id');
            $update_operator->execute(array('id' => $local_operations['id'], 'responsible_id' => $operador_id));

            $meta_key = $order_info['order_commission_released'] == 1 ? 'commission_balance' : 'anticipation_balance';

            $get_user_plan_shipping_tax = $conn->prepare("SELECT user_plan_shipping_tax FROM subscriptions AS s WHERE s.user__id = :user__id");
            $get_user_plan_shipping_tax->execute(array('user__id' => $order_info['user__id']));

            $oder_liquid_value_producer = null;

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
                'value_liquid'  => -($order_info['order_liquid_value'] + ($user_plan_shipping_tax / 2)),
                'value_brute'   => -($order_info['order_liquid_value']),
                'tax_value'     => 0.00, 
                'logistic_value'=> ($user_plan_shipping_tax / 2), 
                'status'        => 4, 
                'type'          => 9, 
                'date_start'    => date('Y-m-d H:i:s'), 
                'date_end'      => date('Y-m-d H:i:s'), 
                'order_number'  => $order_number, 
                'transaction_code' => $transaction_code
            )); 


            $set_new_anticipation_value = $conn->prepare('INSERT INTO transaction (user_id, value_liquid, value_brute, tax_value, logistic_value, status, type, date_start, date_end, order_number) VALUES (:user_id, :value_liquid, :value_brute, :tax_value, :logistic_value, :status, :type, :date_start, :date_end, :order_number)');
            $set_new_anticipation_value->execute(array(
                'user_id'      => $user_afi_id, 
                'value_liquid'   => -($order_infos['order_liquid_value'] + ($user_plan_shipping_tax / 2)),
                'value_brute'   => -($data_afi['order_liquid_value']),
                'tax_value'     => 0.00, 
                'logistic_value'=> ($user_plan_shipping_tax / 2), 
                'status'        => 4, 
                'type'          => 9, 
                'date_start'    => date('Y-m-d H:i:s'), 
                'date_end'      => date('Y-m-d H:i:s'), 
                'order_number'  => $order_number
            ));

            $oder_liquid_value_producer = -($user_plan_shipping_tax / 2);

            // PEGAR SALDO ATUAL DO FINANCEIRO DO AFILIADO 
            $get_order_afi = $conn->prepare("SELECT o.user__id, o.order_liquid_value FROM orders AS o WHERE o.order_number = :order_number");
            $get_order_afi->execute(['order_number' => "AFI" . $order_number]);

            // //////Anderson ////////
            $oder_liquid_value_afiliate = null;
            if ($affiliated_row = $get_order_afi->fetch()) {
                $affiliated_id = $affiliated_row['user__id'];
                $order_liquid_value_afi = $affiliated_row['order_liquid_value'];

                $set_new_anticipation_value = $conn->prepare('INSERT INTO transaction (user_id, value_liquid, value_brute, tax_value, logistic_value, status, type, date_start, date_end, order_number) VALUES (:user_id, :value_liquid, :value_brute, :tax_value, :logistic_value, :status, :type, :date_start, :date_end, :order_number)');
                $set_new_anticipation_value->execute(array(
                    'user_id'      => $user_afi_id, 
                    'value_liquid'   => -($order_infos['order_liquid_value'] + (4.99)),
                    'value_brute'   => -($data_afi['order_liquid_value']),
                    'tax_value'     => (0.00), 
                    'logistic_value'=> (4.99), 
                    'status'        => 4, 
                    'type'          => 9, 
                    'date_start'    => date('Y-m-d H:i:s'), 
                    'date_end'      => date('Y-m-d H:i:s'), 
                    'order_number'  => $order_number
                ));
    
                $oder_liquid_value_afiliate = -4.99;
            }



            // GERAR INVENTORY META PARA AS CONSULTAS 
            $inventory_meta = $order_info['user__id'] . "-" . $order_info['product_id'] . "-" . $local_operations['operation_id'];

            // PEGAR QUANTIDADE ATUAL NO INVENTÁRIO 
            $query = $conn->prepare("SELECT inventory_quantity, inventory_id FROM inventories AS i WHERE i.inventory_product_id = :inventory_product_id AND inventory_locale_id = :inventory_locale_id AND i.ship_locale = 0");
            $query->execute(array('inventory_product_id' => $order_info['product_id'], 'inventory_locale_id' => $local_operations['operation_id']));

            if (!$inventory_data = $query->fetch()) {
                $feedback = array('status' => 2, 'msg' => "Não Conseguimos atualizar a quantidade de estoque, por favor, atualize a página.", 'type' => 'warning');
                echo json_encode($feedback);
                exit;
            }

            $inventory_qtd = $inventory_data['inventory_quantity'];
            $inventory_id = $inventory_data['inventory_id'];

            // ACRESENTAR QUANTIDADE NOVAMENTE AO INVENTARIO
            $query = $conn->prepare("UPDATE inventories AS i SET i.inventory_quantity = :inventory_quantity WHERE i.inventory_id = :inventory_id AND i.ship_locale = 0");
            $query->execute([
                'inventory_quantity' => $inventory_qtd + $order_info['sale_quantity'],
                'inventory_id' => $inventory_id
            ]);

            $stmt = $conn->prepare('UPDATE orders SET order_status = 4, fail_delivery_attemp = :fail_delivery_attemp, proof_contact_attempt = :proof_contact_attempt, order_freight = :order_freight  WHERE order_number = :order_number');
            $stmt->execute(array('fail_delivery_attemp' => $fail_delivery_attemp, 'proof_contact_attempt' => $proof_contact_attempt, 'order_number' => $order_number, 'order_freight' => $client_freight));

            $stmt = $conn->prepare('UPDATE orders SET order_status = 4, fail_delivery_attemp = :fail_delivery_attemp, proof_contact_attempt = :proof_contact_attempt, order_freight = :order_freight WHERE  order_number= :order_number ');
            $stmt->execute(array('fail_delivery_attemp' => $fail_delivery_attemp, 'proof_contact_attempt' => $proof_contact_attempt, 'order_number' => "AFI" . $order_number, 'order_freight' => $client_freight));
        } else {

            $get_user_plan_shipping_tax = $conn->prepare("SELECT user_plan_shipping_tax FROM subscriptions AS s WHERE s.user__id = :user__id");
            $get_user_plan_shipping_tax->execute(array('user__id' => $order_info['user__id']));
            $user_plan_shipping_tax = $get_user_plan_shipping_tax->fetch()['0'];    

            #  PEGAR SALDO ATUAL DO FINANCEIRO DO PRODUTOR
            $query = $conn->prepare("SELECT transaction_id FROM transactions AS t WHERE t.user_id = :user__id AND type = 7 AND order_number = :order_number AND date_end > now()");
            $query->execute(array('user__id' => $order_info['user__id'], 'order_number' => $order_number));
            if ($transaction = $query->fetch()) {    
                # ANTECIPA VALOR DO PEDIDO PARA COBRAR NO DISPONIVEL
                $query = $conn->prepare("UPDATE transactions SET date_end = now(), status = 2  WHERE transaction_id = :transaction_id");
                $query->execute(array('transaction_id' => $transaction['transaction_id']));
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
                'user_id'      => $order_infos['user__id'], 
                'value_liquid'   => -($user_plan_shipping_tax / 2),
                'value_brute'   => -($user_plan_shipping_tax / 2),
                'tax_value'     => 0.00, 
                'logistic_value'=> ($user_plan_shipping_tax / 2), 
                'status'        => 4, 
                'type'          => 9, 
                'date_start'    => date('Y-m-d H:i:s'), 
                'date_end'      => date('Y-m-d H:i:s'), 
                'order_number'  => $order_number,
                'transaction_code' => $transaction_code
            ));
            $oder_liquid_value_producer = -($user_plan_shipping_tax / 2);

            $stmt = $conn->prepare('UPDATE orders SET order_commission_released = 1, order_commission_date = :order_commission_date WHERE order_number = :order_number');
            $stmt->execute(array('order_number' => $order_number, 'order_commission_date' => date('Y-m-d H:i:s')));

            // PEGAR SALDO ATUAL DO FINANCEIRO DO AFILIADO 
            $get_order_afi = $conn->prepare("SELECT order_id, o.user__id, o.order_number, o.order_liquid_value FROM orders AS o WHERE o.order_number = :order_number");
            $get_order_afi->execute(['order_number' => "AFI" . $order_number]);
            if ($affiliated_row = $get_order_afi->fetch()) {
                $affiliated_id              = $affiliated_row['user__id'];
                $affiliated_liquid          = $affiliated_row['order_liquid_value'];
                $affiliated_order_number    = $affiliated_row['order_number'];
                $order_id_afiliate          = $affiliated_row['order_id'];                

                #  PEGAR SALDO ATUAL DO FINANCEIRO DO AFILIADO
                $query = $conn->prepare("SELECT transaction_id FROM transactions AS t WHERE t.user_id = :user__id AND type = 7 AND order_number = :order_number AND date_end > now()");
                $query->execute(array('user__id' => $affiliated_id, 'order_number' => $affiliated_order_number));
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
                    'value_liquid'  => -4.99,
                    'value_brute'   => -4.99,
                    'tax_value'     => 0.00, 
                    'logistic_value'=> 4.99, 
                    'status'        => 4, 
                    'type'          => 9, 
                    'date_start'    => date('Y-m-d H:i:s'), 
                    'date_end'      => date('Y-m-d H:i:s'), 
                    'order_number'  => "AFI" . $order_number,
                    'transaction_code' => $transaction_code
                ));                
            
                $stmt = $conn->prepare('UPDATE orders SET order_commission_released = 1, order_commission_date = :order_commission_date WHERE order_id = :order_id');
                $stmt->execute(array('order_id' => $order_id_afiliate, 'order_commission_date' => date('Y-m-d H:i:s')));                
                $oder_liquid_value_afiliate = -4.99;
            }

            // PEGAR ID DA LOCALIDADE DO PEDIDO
            $get_order_operation = $conn->prepare("SELECT * FROM local_operations_orders lo INNER JOIN local_operations loo ON lo.operation_id=loo.operation_id WHERE order_id=:order_id");
            $get_order_operation->execute(array("order_id" => $order_infos['order_id']));
            $local_operations = $get_order_operation->fetch();

            if (!$local_operations) {
                $feedback = array('status' => 2, 'msg' => "Não existe estoque do produto para a cidade correspondente ao CEP inserido.");
                echo json_encode($feedback);
                exit;
            }

            $update_operator = $conn->prepare('UPDATE local_operations_orders SET responsible_id = :responsible_id WHERE order_id = :order_id');
            $update_operator->execute(array('order_id' => $order_infos['order_id'], 'responsible_id' => $operador_id));

            $stmt = $conn->prepare('UPDATE orders SET order_status = 4, fail_delivery_attemp = :fail_delivery_attemp, proof_contact_attempt = :proof_contact_attempt, order_freight = :order_freight WHERE  order_number = :order_number ');
            $stmt->execute(array('fail_delivery_attemp' => $fail_delivery_attemp, 'proof_contact_attempt' => $proof_contact_attempt, 'order_number' => $order_number, 'order_freight' => $client_freight));

            $stmt = $conn->prepare('UPDATE orders SET order_status = 4, fail_delivery_attemp = :fail_delivery_attemp, proof_contact_attempt = :proof_contact_attempt, order_freight = :order_freight  WHERE order_number = :order_number');
            $stmt->execute(array('fail_delivery_attemp' => $fail_delivery_attemp, 'proof_contact_attempt' => $proof_contact_attempt, 'order_number' => 'AFI' . $order_number, 'order_freight' => $client_freight));

            // order_status_update
        }

        $stmt_insert_historic = $conn->prepare('INSERT INTO order_details ( order_number, order_status, oder_liquid_value_producer, oder_liquid_value_afiliate ) VALUES ( :order_number, :order_status, :oder_liquid_value_producer, :oder_liquid_value_afiliate )');
        $stmt_insert_historic->execute(array('order_number' => $order_number, 'order_status' => 4, 'oder_liquid_value_producer' => $oder_liquid_value_producer, 'oder_liquid_value_afiliate' => $oder_liquid_value_afiliate));


        // $response = sendWebhookStatusAuto($order_id);             
        $feedback = array('title' => 'Feito!', 'msg' => "A tentativa frustrada de fazer a entrega foi registrada.", 'type' => 'success');
        echo json_encode($feedback);
        exit;
    } catch (PDOException $e) {
        $error = 'ERROR: ' . $e;
        $feedback = array('status' => 0, 'msg' => $error);
        echo json_encode($feedback);
        exit;
    }
} else {
    $feedback = array('title' => 'Erro!', 'msg' => '---', 'type' => 'error');
    echo json_encode($feedback);
    exit;
}
