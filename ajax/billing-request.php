<?php
error_reporting(-1);             
ini_set('display_errors', 1);    
require dirname(__FILE__) . "/../includes/config.php";
require dirname(__FILE__) . "/../includes/classes/RandomStrGenerator.php";

session_name(SESSION_NAME);
session_start();

function formatoDecimal($input = null)
{    
    $valor = str_replace('.', '', $input);
    $output = str_replace(',', '.', $valor);
    return $output;
}

if (isset($_GET['action']) && isset($_GET['value'])) {

    $action = $_GET['action'];

    $billing_value = str_replace(".", "", $_GET['value']);
    $billing_value = str_replace(",", ".", $billing_value);
} else {
    $action = 'transfer_request';
}

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}

if ($action == 'billing-request' || $action == 'billing_commission_request') {
    $fback = "Saque";
    $billing_type = "SAQUE";
} else if ($action == 'anticipation-request') {
    $fback = "Antecipação";
    $billing_type = "ANTECIPACAO";
}

$user__id = $_SESSION['UserID'];
$userPlan = $_SESSION['UserPlan'];

if ($action == 'billing-request') {

    // Valor sem subtrair as taxas para retirar do saldo
    $billing_value_full = $billing_value;

    // Taxa de saque
    // se o plano do usuário for maior que 5 não paga taxa
    // para os demais a taxa de saque é R$2.99
    $withdrawn_tax = $userPlan < 5 ? 2.99 : 0;

    // Valor total de taxas pagas
    $billing_tax = $withdrawn_tax;
    // Valor a ser recebido depois de subtrair as taxas
    $billing_value = round($billing_value - $withdrawn_tax, 2);

    $billing_bank_account = intval($_GET['account']);

    # Verifica se há saldo para saque
    $commission_balance = $conn->prepare('SELECT commission_balance FROM balance_resume_ref WHERE user_id = :user__id');
    $commission_balance->execute(array('user__id' => $user__id));
    $commission_balance = $commission_balance->fetch();

    if ($commission_balance['0'] == null) {

        # Informa se não houver valor disponível ou solicitação pendente.
        $feedback = array('title' => 'Saque Indisponível!', 'type' => 'warning', 'msg' => 'Você não possui saldo disponível para saque no momento.');
        echo json_encode($feedback);
        exit;

        # Verifica se o valor solicitado é menor ou igual o saldo disponível.
    } else if ($commission_balance['0'] < $billing_value) {

        $feedback = array('title' => 'Saldo Insuficiente!', 'type' => 'warning', 'msg' => 'O valor solicitado é maior do que o seu saldo atual.');
        echo json_encode($feedback);
        exit;

        # Se houver valor, realiza a solicitação
    } else {

        $today = date("Y-m-d H:i:s");

        
        $transaction_code = new RandomStrGenerator();
        $transaction_code =   strtoupper(date('jnyhi') .'&'.  $transaction_code->lettersAndNumbers(6));

        $verify_unique_transaction_code = $conn->prepare('SELECT * FROM transactions WHERE transaction_code = :transaction_code');
        $verify_unique_transaction_code->execute(array('transaction_code' => $transaction_code));

        if (!($verify_unique_transaction_code->rowCount() == 0)) {
            do {
                $transaction_code = new RandomStrGenerator();
                $transaction_code = strtoupper(date('jnyhi') .'&'. $transaction_code->lettersAndNumbers(6));

                $verify_unique_transaction_code = $conn->prepare('SELECT * FROM transactions WHERE transaction_code = :transaction_code');
                $verify_unique_transaction_code->execute(array('transaction_code' => $transaction_code));
            } while ($stmt->rowCount() != 0);
        } 

        $create_billing_request = $conn->prepare('INSERT INTO billings (billing_id, user__id, billing_value_full, billing_value, billing_tax, billing_bank_account, billing_type, billing_request) VALUES (:billing_id, :user__id, :billing_value_full, :billing_value, :billing_tax, :billing_bank_account, :billing_type, :billing_request)');
        $create_billing_request->execute(array('billing_id' => '0', 'user__id' => $user__id, 'billing_value_full' => $billing_value_full, 'billing_value' => $billing_value, 'billing_tax' => $billing_tax, 'billing_bank_account' => $billing_bank_account, 'billing_type' => $billing_type, 'billing_request' => $today));

        $get_last_billing = $conn->prepare("SELECT billing_id FROM billings ORDER BY billing_id DESC LIMIT 1");
        $get_last_billing->execute();
        $billing_id = $get_last_billing->fetch()[0];


        $set_new_anticipation_value = $conn->prepare('INSERT INTO transactions 
        (transaction_id, user_id, value_liquid, value_brute, tax_value, logistic_value, status, type, date_start, date_end, bank_id, checking_copy, bank_proof, transaction_code) VALUES 
        (NULL, :user_id, :value_liquid, :value_brute, :tax_value, :logistic_value, :status, :type, :date_start, :date_end, :bank_id, :checking_copy, :bank_proof, :transaction_code)');
        $set_new_anticipation_value->execute(array(
            'user_id'       => $user__id, 
            'value_liquid'  => -$billing_value,
            'value_brute'   => -$billing_value_full,
            'tax_value'     => $billing_tax, 
            'logistic_value'=> 0.00, 
            'status'        => 1, 
            'type'          => 5, 
            'date_start'    => $today, 
            'date_end'      => null,
            'bank_id'       => $billing_bank_account, //564         
            'checking_copy' => $billing_id,
            'bank_proof'    => null,    
            'transaction_code' => $transaction_code
        ));


        // # Atualiza o valor "Em Análise"
        // $get_billing_in_review = $conn->prepare('SELECT meta_value, meta_id FROM transactions_meta WHERE meta_key = "in_review_balance" AND user__id = :user__id');
        // $get_billing_in_review->execute(array('user__id' => $user__id));

        // if ($get_billing_in_review->rowCount() > 0) {

        //     $billing_in_review = $get_billing_in_review->fetch();
        //     $meta_value = $billing_in_review['meta_value'] + $billing_value;
        //     $meta_id = $billing_in_review['meta_id'];

        //     $set_billing_in_review = $conn->prepare('UPDATE transactions_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
        //     $set_billing_in_review->execute(array('meta_value' => $meta_value, 'meta_id' => $meta_id));
        // } else {

        //     $set_billing_in_review = $conn->prepare('INSERT INTO transactions_meta (meta_id, user__id, meta_key, meta_value) VALUES (:meta_id, :user__id, :meta_key, :meta_value)');
        //     $set_billing_in_review->execute(array('meta_id' => '0', 'user__id' => $user__id, 'meta_key' => "in_review_balance", 'meta_value' => $billing_value));
        // }

        // # Atualiza o valor disponível para saque
        // $get_commission_balance = $conn->prepare('SELECT meta_value, meta_id FROM transactions_meta WHERE meta_key = "commission_balance" AND user__id = :user__id');
        // $get_commission_balance->execute(array('user__id' => $user__id));

        // $commission_balance = $get_commission_balance->fetch();
        // $meta_value = $commission_balance['meta_value'] - $billing_value_full;
        // $meta_id = $commission_balance['meta_id'];

        // $set_commission_balance = $conn->prepare('UPDATE transactions_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
        // $set_commission_balance->execute(array('meta_value' => $meta_value, 'meta_id' => $meta_id));

        $billing_value = number_format($billing_value, 2, ',', '.');
        $msg = 'Seu saque de R$ ' . $billing_value . ' está sendo processado!';

        $feedback = array('title' => 'Solicitação Realizada!', 'type' => 'success', 'msg' => $msg);
        echo json_encode($feedback);
        exit;
    }
} else if ($action == 'anticipation-request') { 

    # Verifica se há saldo para antecipacao
    $commission_balance = $conn->prepare('SELECT meta_value FROM transactions_meta WHERE meta_key = "anticipation_balance" AND user__id = :user__id');
    $commission_balance->execute(array('user__id' => $user__id));
    $commission_balance = $commission_balance->fetch();

    // Valor sem subtrair as taxas para retirar do saldo
    $billing_value_full = $billing_value;

    // Taxa de antecipação
    $antecipation_tax = $billing_value * 0.0499;

    // Valor total de taxas pagas
    $billing_tax = number_format($antecipation_tax, 2, '.', ''); 

    // Valor a ser recebido depois de subtrair as taxas
    $billing_value = number_format($billing_value - $antecipation_tax, 2, '.', '');  

    if ($commission_balance['0'] == null) {

        # Informa se não houver valor disponível ou solicitação pendente.
        $feedback = array('title' => 'Antecipação Indisponível!', 'type' => 'warning', 'msg' => 'Você não possui saldo disponível para antecipação no momento.');
        echo json_encode($feedback);
        exit;

        # Verifica se o valor solicitado é menor ou igual o saldo disponível.
    } else if ($commission_balance['0'] < $billing_value_full) { 

        $feedback = array('title' => 'Saldo Insuficiente!', 'type' => 'warning', 'msg' => 'O valor solicitado é maior do que o seu saldo saldo disponível para antecipação. <br> seu saldo: '. $commission_balance['0'], 'result' => array($commission_balance['0'] < $billing_value, $commission_balance['0'], $billing_value)); 
        echo json_encode($feedback);
        exit;
 
        # Se houver valor, realiza a solicitação
    } else {

        $today = date("Y-m-d H:i:s");

        $create_billing_request = $conn->prepare('INSERT INTO billings 
                (billing_id, user__id, billing_value_full, billing_value, billing_tax, billing_bank_account, billing_type, billing_request, billing_released) 
        VALUES (:billing_id, :user__id, :billing_value_full, :billing_value, :billing_tax, :billing_bank_account, :billing_type, :billing_request, :billing_released)');
        $create_billing_request->execute(array
                ('billing_id' => '0', 'user__id' => $user__id, 'billing_value_full' => $billing_value_full, 'billing_value' => $billing_value, 'billing_tax' => $billing_tax, 'billing_bank_account' => 0, 'billing_type' => $billing_type, 'billing_request' => $today, 'billing_released' => $today));

        # Atualiza o valor "Em Análise" 
        $get_billing_in_review = $conn->prepare('SELECT meta_value, meta_id FROM transactions_meta WHERE meta_key = "commission_balance" AND user__id = :user__id');
        $get_billing_in_review->execute(array('user__id' => $user__id));

        if ($get_billing_in_review->rowCount() > 0) {

            $billing_in_review = $get_billing_in_review->fetch();
            $meta_value = $billing_in_review['meta_value'] + $billing_value;
            $meta_id = $billing_in_review['meta_id'];

            $set_billing_in_review = $conn->prepare('UPDATE transactions_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
            $set_billing_in_review->execute(array('meta_value' => $meta_value, 'meta_id' => $meta_id));
        } else {

            $set_billing_in_review = $conn->prepare('INSERT INTO transactions_meta (meta_id, user__id, meta_key, meta_value) VALUES (:meta_id, :user__id, :meta_key, :meta_value)');
            $set_billing_in_review->execute(array('meta_id' => '0', 'user__id' => $user__id, 'meta_key' => "commission_balance", 'meta_value' => $billing_value));
        } 

        # Atualiza o valor "Em Análise"
        $get_billing_anticipated = $conn->prepare('SELECT meta_value, meta_id FROM transactions_meta WHERE meta_key = "anticipated_value" AND user__id = :user__id');
        $get_billing_anticipated->execute(array('user__id' => $user__id));

        if ($get_billing_anticipated->rowCount() > 0) {

            $billing_anticipated = $get_billing_anticipated->fetch();
            $meta_value_anticipated = $billing_anticipated['meta_value'] + $billing_value_full;
            $meta_id_anticipated = $billing_anticipated['meta_id'];

            $set_billing_anticipated = $conn->prepare('UPDATE transactions_meta SET meta_value = :meta_value WHERE meta_id = :meta_id'); 
            $set_billing_anticipated->execute(array('meta_value' => $meta_value_anticipated, 'meta_id' => $meta_id_anticipated));
        } else {

            $set_billing_anticipated = $conn->prepare('INSERT INTO transactions_meta (meta_id, user__id, meta_key, meta_value) VALUES (:meta_id, :user__id, :meta_key, :meta_value)');
            $set_billing_anticipated->execute(array('meta_id' => '0', 'user__id' => $user__id, 'meta_key' => "anticipated_value", 'meta_value' => $billing_value_full));
        }

        # Atualiza o valor disponível para saque
        $get_commission_balance = $conn->prepare('SELECT meta_value, meta_id FROM transactions_meta WHERE meta_key = "anticipation_balance" AND user__id = :user__id');
        $get_commission_balance->execute(array('user__id' => $user__id));

        $commission_balance = $get_commission_balance->fetch();
        $meta_value = $commission_balance['meta_value'] - $billing_value_full;
        $meta_id = $commission_balance['meta_id'];

        $set_commission_balance = $conn->prepare('UPDATE transactions_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
        $set_commission_balance->execute(array('meta_value' => $meta_value, 'meta_id' => $meta_id));

        $billing_value = number_format($billing_value, 2, ',', '.');
        $msg = 'Seu saque de R$ ' . $billing_value . ' está disponível para saque!';

        $feedback = array('title' => 'Solicitação Realizada!', 'type' => 'success', 'msg' => $msg);
        echo json_encode($feedback);
        exit;
    }
} else if ($action == 'billing_commission_request') {

    $today = date("Y-m-d H:i:s");
    $billing_bank_account = intval($_GET['account']);

    if ($billing_value < 1) {
        $msg = 'O valor mínimo é R$ 1,00';
        $feedback = array('title' => 'Valor do Saque Baixo', 'type' => 'error', 'msg' => $msg);
        echo json_encode($feedback);
        exit;
    }

    // VERIFICAR SE EXISTE O VALOR DISPONÍVEL PARA SAQUE 
    $query = "SELECT meta_value_available FROM recruitment_commission_meta WHERE meta_key = 'billing_commission_request' AND user__recruiter_id = :user__id";
    $stmt = $conn->prepare($query);
    $stmt->execute([
        'user__id' => $user__id
    ]);

    $value_avaialabe = $stmt->fetch(\PDO::FETCH_ASSOC)['meta_value_available'];
    $row = $stmt->rowCount();

    $billing_value_formater = number_format($billing_value, 2, ',', '.');

    if ($row == 0) {
        $msg = 'Você não tem saldo suficiente para o saque de R$ ' . $billing_value_formater . '';
        $feedback = array('title' => 'Saldo indisponível!', 'type' => 'error', 'msg' => $msg);
        echo json_encode($feedback);
        exit;
    } else {
        if ($billing_value > $value_avaialabe) {
            $msg = 'Você não tem saldo suficiente para o saque de R$ ' . $billing_value_formater . '';
            $feedback = array('title' => 'Saldo indisponível!', 'type' => 'error', 'msg' => $msg);
            echo json_encode($feedback);
            exit;
        } else {
            /* VERIFICAR SE JÁ EXISTE UM VALOR SACADO. SE SIM ATUALIZAR DESSE VALOR, SE NÃO INSERIR UM NOVO META_KEY DE VALOR SACADO */
            $query = "SELECT meta_value_payer FROM recruitment_commission_meta WHERE meta_key = 'billing_commission_request_withdrawn' AND user__recruiter_id = :user__recruiter_id";
            $stmt = $conn->prepare($query);
            $stmt->execute([
                'user__recruiter_id' => $user__id
            ]);

            $rows = $stmt->rowCount();

            if ($rows == 0) {
                $query = "INSERT INTO recruitment_commission_meta (user__recruiter_id, meta_key, meta_value_payer) VALUES (:user__recruiter_id, :meta_key, :meta_value_payer)";
                $stmt = $conn->prepare($query);
                $isSuccess = $stmt->execute([
                    'user__recruiter_id' => $user__id,
                    'meta_key' => 'billing_commission_request_withdrawn',
                    'meta_value_payer' => $billing_value
                ]);

                $query = "UPDATE recruitment_commission_meta SET meta_value_available = :meta_value_available WHERE meta_key = 'billing_commission_request' AND user__recruiter_id = :user__recruiter_id";
                $stmt = $conn->prepare($query);
                $isSucess = $stmt->execute([
                    'meta_value_available' => $value_avaialabe - $billing_value,
                    'user__recruiter_id' => $user__id
                ]);

                $create_billing_request = $conn->prepare('INSERT INTO billings (user__id, billing_value, billing_bank_account, billing_type, billing_request) VALUES (:user__id, :billing_value, :billing_bank_account, :billing_type, :billing_request)');
                $create_billing_request->execute(array('user__id' => $user__id, 'billing_value' => $billing_value, 'billing_bank_account' => $billing_bank_account, 'billing_type' => $billing_type, 'billing_request' => $today));

                $msg = 'Seu saque de R$ ' . $billing_value_formater . ' está sendo processado!';
                $feedback = array('title' => 'Solicitação Realizada!', 'type' => 'success', 'msg' => $msg);
                echo json_encode($feedback);
                exit;
            }

            /* PEGAR VALOR JÁ SACADO PARA ATUALIZAR POSTERIORMENTE  */
            $query = "SELECT meta_value_payer FROM recruitment_commission_meta WHERE meta_key = :meta_key AND user__recruiter_id = :user__recruiter_id";
            $stmt = $conn->prepare($query);
            $isSuccess = $stmt->execute([
                'user__recruiter_id' => $user__id,
                'meta_key' => 'billing_commission_request_withdrawn'
            ]);

            /* ATUALIZAR VALOR SACADO */
            $query = "UPDATE recruitment_commission_meta SET meta_value_payer = :meta_value_payer WHERE meta_key = :meta_key AND user__recruiter_id = :user__recruiter_id";
            $stmt = $conn->prepare($query);
            $isSuccess = $stmt->execute([
                'user__recruiter_id' => $user__id,
                'meta_key' => 'billing_commission_request_withdrawn',
                'meta_value_payer' => ($total_withdrawn + $billing_value)
            ]);

            $query = "UPDATE recruitment_commission_meta SET meta_value_available = :meta_value_available WHERE meta_key = 'billing_commission_request' AND user__recruiter_id = :user__recruiter_id";
            $stmt = $conn->prepare($query);
            $isSucess = $stmt->execute([
                'meta_value_available' => $value_avaialabe - $billing_value,
                'user__recruiter_id' => $user__id
            ]);

            $create_billing_request = $conn->prepare('INSERT INTO billings (user__id, billing_value, billing_bank_account, billing_type, billing_request) VALUES (:user__id, :billing_value, :billing_bank_account, :billing_type, :billing_request)');
            $create_billing_request->execute(array('user__id' => $user__id, 'billing_value' => $billing_value, 'billing_bank_account' => $billing_bank_account, 'billing_type' => $billing_type, 'billing_request' => $today));

            $msg = 'Seu saque de R$ ' . $billing_value_formater . ' está sendo processado!';
            $feedback = array('title' => 'Solicitação Realizada!', 'type' => 'success', 'msg' => $msg);
            echo json_encode($feedback);
            exit;
        }
    }

    // VERIFICAR SE O VALOR INFOMADO É MENOR OU IGUAL O VALOR DISPONÍVEL

} else if (isset($_POST["action"]) && $_POST["action"] == 'request-antecipation') {
    
    $today = date("Y-m-d H:i:s");
    $fback = "Antecipação";
    $billing_type = "ANTECIPACAO";

    $optionValueAntecipatio = $_POST['ids_request'];

    
    $orders_number = 0;
    if($optionValueAntecipatio == 'all'){
        # Verifica se há saldo para antecipacao
        $get_transactions_to_antecipation = $conn->prepare("SELECT transaction_id, value_liquid, order_number FROM transactions t WHERE user_id = :user_id AND type = 7 AND date_end > now() ORDER BY transaction_id ASC");
        $get_transactions_to_antecipation->execute(array('user_id' => $user__id));
        while($transaction_to_antecipation = $get_transactions_to_antecipation->fetch()){
            $billing_value += $transaction_to_antecipation['value_liquid'];
            $ids[] = $transaction_to_antecipation['transaction_id'];
            $orders_number .= ','. $transaction_to_antecipation['order_number'];

            # ATUALIZANDO A TRANSAÇÃO DO PEDIDO
            $billing_release = $conn->prepare('UPDATE transactions SET date_end = :date_end, status = 2 WHERE transaction_id = :transaction_id');
            $billing_release->execute(array('date_end' => $today, 'transaction_id' => $transaction_to_antecipation['transaction_id']));        
        }

        // Valor sem subtrair as taxas para retirar do saldo
        $billing_value_full = $billing_value;

        // Taxa de antecipação
        $antecipation_tax = $billing_value * 0.0499;

        // Valor total de taxas pagas
        $billing_tax = number_format($antecipation_tax, 2, '.', ''); 

        // Valor a ser recebido depois de subtrair as taxas
        $billing_value = number_format($billing_value - $antecipation_tax, 2, '.', '');  

    } else {

        $billing_value = 0;
        $get_transactions_to_antecipation = $conn->prepare("SELECT transaction_id, value_liquid, order_number FROM transactions t WHERE user_id = :user_id AND type = 7 AND date_end > now() AND transaction_id IN ( $optionValueAntecipatio ) ORDER BY transaction_id ASC");
        $get_transactions_to_antecipation->execute(array('user_id' => $user__id));
        while($transaction_to_antecipation = $get_transactions_to_antecipation->fetch()){
            $billing_value += $transaction_to_antecipation['value_liquid'];
            $ids[] = $transaction_to_antecipation['transaction_id'];
            $orders_number .= ','. $transaction_to_antecipation['order_number'];

            # ATUALIZANDO A TRANSAÇÃO DO PEDIDO
            $billing_release = $conn->prepare('UPDATE transactions SET date_end = :date_end, status = 2 WHERE transaction_id = :transaction_id');
            $billing_release->execute(array('date_end' => $today, 'transaction_id' => $transaction_to_antecipation['transaction_id']));        
        }

        // Valor sem subtrair as taxas para retirar do saldo
        $billing_value_full = $billing_value;

        // Taxa de antecipação
        $antecipation_tax = $billing_value * 0.0499;

        // Valor total de taxas pagas
        $billing_tax = number_format($antecipation_tax, 2, '.', ''); 
        
        // Valor a ser recebido depois de subtrair as taxas
        $billing_value = number_format($billing_value - $antecipation_tax, 2, '.', '');  
    }

    $transaction_code = new RandomStrGenerator();
    $transaction_code =   strtoupper(date('jnyhi') .'&'.  $transaction_code->lettersAndNumbers(6));

    $verify_unique_transaction_code = $conn->prepare('SELECT * FROM transactions WHERE transaction_code = :transaction_code');
    $verify_unique_transaction_code->execute(array('transaction_code' => $transaction_code));

    if (!($verify_unique_transaction_code->rowCount() == 0)) {
        do {
            $transaction_code = new RandomStrGenerator();
            $transaction_code = strtoupper(date('jnyhi') .'&'. $transaction_code->lettersAndNumbers(6));

            $verify_unique_transaction_code = $conn->prepare('SELECT * FROM transactions WHERE transaction_code = :transaction_code');
            $verify_unique_transaction_code->execute(array('transaction_code' => $transaction_code));
        } while ($stmt->rowCount() != 0);
    }

    $create_billing_request = $conn->prepare('INSERT INTO billings 
            (billing_id, user__id, billing_value_full, billing_value, billing_tax, billing_bank_account, billing_type, billing_request, billing_released) 
    VALUES (:billing_id, :user__id, :billing_value_full, :billing_value, :billing_tax, :billing_bank_account, :billing_type, :billing_request, :billing_released)');
    $create_billing_request->execute(array
            ('billing_id' => '0', 'user__id' => $user__id, 'billing_value_full' => $billing_value_full, 'billing_value' => $billing_value, 'billing_tax' => $billing_tax, 'billing_bank_account' => 0, 'billing_type' => $billing_type, 'billing_request' => $today, 'billing_released' => $today));

    $get_last_billing = $conn->prepare("SELECT billing_id FROM billings ORDER BY billing_id DESC LIMIT 1");
    $get_last_billing->execute();
    $billing_id = $get_last_billing->fetch()[0];

    $set_new_anticipation_value = $conn->prepare('INSERT INTO transactions 
    (transaction_id, user_id, value_liquid, value_brute, tax_value, logistic_value, status, type, date_start, date_end, checking_copy, order_number, bank_proof, transaction_code, orders_antecipation) VALUES 
    (NULL, :user_id, :value_liquid, :value_brute, :tax_value, :logistic_value, :status, :type, :date_start, :date_end, :checking_copy, order_number, :bank_proof, :transaction_code, :orders_antecipation)');
    $set_new_anticipation_value->execute(array(
        'user_id'       => $user__id, 
        'value_liquid'  => -$billing_tax,
        'value_brute'   => $billing_value_full,
        'tax_value'     => $billing_tax, 
        'logistic_value'=> 0.00, 
        'status'        => 4, 
        'type'          => 1, 
        'date_start'    => $today, 
        'date_end'      => $today, 
        'checking_copy' => $billing_id,
        'bank_proof'    => null,   
        'transaction_code'      => $transaction_code,
        'orders_antecipation'   => $orders_number
    ));

    $billing_value = number_format($billing_value, 2, ',', '.');
    $msg = 'Seu saque de R$ ' . $billing_value . ' está disponível para saque!';

    $feedback = array('title' => 'Solicitação Realizada!', 'type' => 'success', 'msg' => $msg);
    echo json_encode($feedback);
    exit;

} else if (isset($_POST["action"])) {
    if (isset($_FILES["comprovante"]) && $_FILES["comprovante"]["size"] > 0) {
        $transfer_receipt = $_FILES['comprovante'];

        $fback = "Repasse";
        $billing_type = "REPASSE";

        $billing_value = str_replace(".", "", $_POST['valor-repasse']);
        $billing_value = str_replace(",", ".", $billing_value);

        # Verifica se há saldo para repasse
        $transfer_balance = $conn->prepare('SELECT meta_value FROM transactions_meta WHERE meta_key = "transfer_balance" AND user__id = :user__id');
        $transfer_balance->execute(array('user__id' => $user__id));
        $transfer_balance = $transfer_balance->fetch();

        if ($transfer_balance['0'] == null) {

            # Informa se não houver valor disponível ou solicitação pendente.
            $feedback = array('title' => 'Repasse Indisponível!', 'type' => 'warning', 'msg' => 'Você não possui saldo disponível para repassar no momento.');
            echo json_encode($feedback);
            exit;

            # Verifica se o valor solicitado é menor ou igual o saldo disponível.
        } else if ($transfer_balance['0'] < $billing_value) {

            $feedback = array('title' => 'Saldo Insuficiente!', 'type' => 'warning', 'msg' => 'O valor solicitado é maior do que o seu saldo atual.');
            echo json_encode($feedback);
            exit;

            # Se houver valor, realiza a solicitação
        } else {

            $get_last_id = $conn->prepare("SELECT billing_id FROM billings ORDER BY billing_id DESC LIMIT 1");
            $get_last_id->execute();

            $filetypes = array('jfif', 'pdf', 'png', 'jpeg', 'jpg');
            $image_filetype_array = explode('.', $_FILES['comprovante']['name']);
            $filetype = strtolower(end($image_filetype_array));

            # Valida se a extensão do arquivo é aceita
            if (in_array($filetype, $filetypes) == false) {
                $feedback = array('title' => 'Formato Incorreto', 'type' => 'warning', 'msg' => 'Você precisa anexar um arquivo de imagem ou um PDF.');
                echo json_encode($feedback);
                exit;
            }

            $new_name = date("Ymd-His") . 'billing-' . strval($get_last_id->fetch()["billing_id"] + 1) . '.' . $filetype;
            $dir = '../uploads/repasses/comprovantes/';
            if (move_uploaded_file($_FILES['comprovante']['tmp_name'], $dir . $new_name)) {
            } else {
                $feedback = array('title' => 'Erro', 'type' => 'warning', 'msg' => 'Não deu! Erro ao fazer upload da imagem do produto!');
                echo json_encode($feedback);
                exit;
            }

            $today = date("Y-m-d H:i:s");

            $create_billing_request = $conn->prepare('INSERT INTO billings (billing_id, user__id, billing_value, billing_bank_account, billing_type, billing_request, billing_proof) VALUES (:billing_id, :user__id, :billing_value, :billing_bank_account, :billing_type, :billing_request, :billing_proof)');
            $create_billing_request->execute(array('billing_id' => '0', 'user__id' => $user__id, 'billing_value' => $billing_value, 'billing_bank_account' => 0, 'billing_type' => $billing_type, 'billing_request' => $today, 'billing_proof' => $new_name));

            # Atualiza o valor "Em Análise"
            $get_billing_in_review = $conn->prepare('SELECT meta_value, meta_id FROM transactions_meta WHERE meta_key = "in_review_transfer" AND user__id = :user__id');
            $get_billing_in_review->execute(array('user__id' => $user__id));

            if ($get_billing_in_review->rowCount() > 0) {

                $billing_in_review = $get_billing_in_review->fetch();
                $meta_value = $billing_in_review['meta_value'] + $billing_value;
                $meta_id = $billing_in_review['meta_id'];

                $set_billing_in_review = $conn->prepare('UPDATE transactions_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
                $set_billing_in_review->execute(array('meta_value' => $meta_value, 'meta_id' => $meta_id));
            } else {

                $set_billing_in_review = $conn->prepare('INSERT INTO transactions_meta (meta_id, user__id, meta_key, meta_value) VALUES (:meta_id, :user__id, :meta_key, :meta_value)');
                $set_billing_in_review->execute(array('meta_id' => '0', 'user__id' => $user__id, 'meta_key' => "in_review_transfer", 'meta_value' => $billing_value));
            }

            # Atualiza o valor disponível para saque
            $get_transfer_balance = $conn->prepare('SELECT meta_value, meta_id FROM transactions_meta WHERE meta_key = "transfer_balance" AND user__id = :user__id');
            $get_transfer_balance->execute(array('user__id' => $user__id));

            $transfer_balance = $get_transfer_balance->fetch();
            $meta_value = $transfer_balance['meta_value'] - $billing_value;
            $meta_id = $transfer_balance['meta_id'];

            $set_transfer_balance = $conn->prepare('UPDATE transactions_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
            $set_transfer_balance->execute(array('meta_value' => $meta_value, 'meta_id' => $meta_id));

            $billing_value = number_format($billing_value, 2, ',', '.');
            $msg = 'Seu repasse de R$ ' . $billing_value . ' está sendo processado!';

            $feedback = array('title' => 'Solicitação Realizada!', 'type' => 'success', 'msg' => $msg);
            echo json_encode($feedback);
            exit;
        }
    } else {
        $feedback = array('msg' => 'Você precisa anexar o comprovante da transação.', 'title' => 'Sem Comprovante', 'type' => 'warning');
        echo json_encode($feedback);
        exit;
    }
}
exit;
