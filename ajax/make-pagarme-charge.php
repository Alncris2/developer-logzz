<?php

require_once (dirname(__FILE__) . '/../includes/config.php');
require (dirname(__FILE__) . '/../vendor/autoload.php');
require (dirname(__FILE__) . "/../includes/classes/RandomStrGenerator.php");

session_name(SESSION_NAME);
session_start();

$user__id = $_SESSION['UserID'];
$pagarme = new PagarMe\Client(PGME_API_KEY);

$set_timeout = $conn->prepare('SET SESSION interactive_timeout = 28800');
$set_timeout->execute();

# Verifica se há um cartão ativo
$check_current_card = $conn->prepare('SELECT card_id, card_hash FROM cards WHERE card_user_id = :user__id AND card_active = 1');
$check_current_card->execute(array('user__id' => $user__id));

if ($check_current_card->rowCount() != 1 ){

    $feedback = array('type' => 'warning', 'title' => 'Erro no Pagamento', 'msg' => 'Você não tem um método');
    echo json_encode($feedback);
    exit;

} else {

	# Pega a hash do cartão ativo do usuário
	$current_card = $check_current_card->fetch();
	$card_hash = $current_card['card_hash'];

	# Busca os dados do Usuário e da Assinatura dele
	$get_current_plan = $conn->prepare('SELECT * FROM subscriptions INNER JOIN users ON users.user__id = subscriptions.user__id WHERE users.user__id = :user__id');
	$get_current_plan->execute(array('user__id' => $user__id));
	$current_plan_details = $get_current_plan->fetch();
	$plan_id = $current_plan_details['subscription_current_plan'];
	$user_code = $current_plan_details['user_code'];
	$user_name = $current_plan_details['full_name'];
	$user_email = $current_plan_details['email'];
	$subscription_renewal = $current_plan_details['subscription_renewal'];
	$plan_price = $current_plan_details['plan_price'];
	$subscription_id = $current_plan_details['subscription_id'];
	$renewal = new DateTime($subscription_renewal);

	if ($current_plan_details['company_type'] == 'fisica'){
		$company_type = 'individual';
		$doc = 'cpf';
	} else if ($current_plan_details['company_type'] == 'juridica'){
		$company_type = 'corporation';
		$doc = 'cnpj';
	} else {
		$feedback = array('type' => 'warning', 'title' => 'Cadastro Incompleto', 'msg' => 'Confira se você informou corretamente os seus dados de cadastro.');
		echo json_encode($feedback);
		exit;
	}

	$transaction_id = new RandomStrGenerator();
    $transaction_id = $transaction_id->onlyLetters(8);

	$iten_title = "Cobrança Logística | " . $transaction_id;
	
		$today = date('Y-m-d H:i:s');

		# Verifica total de cobranças pendentes
		$get_pending_charge = $conn->prepare('SELECT SUM(billing_value) AS TOTAL FROM billings WHERE user__id = :user__id AND (billing_released IS NULL AND billing_type = "COBRANCA")');
		$get_pending_charge->execute(array('user__id' => $user__id));
		$total_pending_charges = $get_pending_charge->fetch();
		$amount = $pending_charges = round(($total_pending_charges['TOTAL'] * 100), 0);

		$chars = array(".", "-", "/", " ", "(", ")");
		$doc_number = str_replace($chars, "", $current_plan_details['company_doc']);
		$user_phone = "+55" . (str_replace($chars, "", $current_plan_details['user_phone']));

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
					'title' => $iten_title,
					'unit_price' => $amount,
					'quantity' => 1,
					'tangible' => false
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
				
				# Marca as cobranças que acabaram de ser pagas como PAGAS
				$get_pending_charge = $conn->prepare('SELECT billing_id FROM billings WHERE user__id = :user__id AND (billing_released IS NULL AND billing_type = "COBRANCA")');
				$get_pending_charge->execute(array('user__id' => $user__id));
				
				while ($pending_charges = $get_pending_charge->fetch()) {
					$billing_id = $pending_charges['billing_id'];

					$set_as_paid = $conn->prepare('UPDATE billings SET billing_released = :billing_released WHERE billing_id = :billing_id');
					$set_as_paid->execute(array('billing_released' => $today, 'billing_id' => $billing_id));
				}

				$feedback = array('type' => 'success', 'title' => 'Pago!', 'msg' => 'Nenhum valor é devido no momento.');
				echo json_encode($feedback);
				exit;

			} else if ($transaction_status == 'refused'){
				$type = 'error';
				$title = 'Erro';
				$msg = 'A cobrança no seu cartão foi recusada.';
				$feedback = array('type' => $type, 'title' => $title, 'msg' => $msg);
				echo json_encode($feedback);
				exit;
			
			} else {
				$type = 'error';
				$title = 'Erro';
				$msg = 'Não foi possível realizar a cobrança no seu cartão.' ;
				$feedback = array('type' => $type, 'title' => $title, 'msg' => $msg);
				echo json_encode($feedback);
				exit;
			}
			
		}
		
		catch (exception $e) {

			if ($error == "ERROR TYPE: invalid_parameter. PARAMETER: card_id. MESSAGE: Card not found.") {
                $error = "Houve um erro com a forma de pagemento, e ela precisa ser informada novamente. Por favor, reinsira os dados do seu cartão na tela Planos.";
            } else {
                $error = 'Não foi possível realizar a cobrança no seu cartão.';
            }

			$feedback = array('type' => 'warning', 'title' => 'Cobrança não realizada', 'msg' => $error);
			echo json_encode($feedback);
			exit;

		}	

}
?>