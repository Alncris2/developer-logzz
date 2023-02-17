<?php

require_once (dirname(__FILE__) . '/../includes/config.php');
require (dirname(__FILE__) . '/../vendor/autoload.php');

session_name(SESSION_NAME);
session_start();

$user__id = $_SESSION['UserID'];
$new_plan_id = $_GET['plan'];
require (dirname(__FILE__) . '/../includes/plans-list.php');

$pagarme = new PagarMe\Client('ak_test_N951BStfuEcJlJV9x0sKtSpPASgn28');

# Verifica se há um cartão ativo
$check_current_card = $conn->prepare('SELECT card_id, card_hash FROM cards WHERE card_user_id = :user__id AND card_active = 1');
$check_current_card->execute(array('user__id' => $user__id));

if ($check_current_card->rowCount() != 1 ){

    $feedback = array('type' => 'warning', 'title' => 'Erro no Pagamento', 'msg' => 'Você precisa informar um método de pagamento válido antes de mudar de plano.');
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

	# Aborta se o plano atual e o plano selecionado forem o mesmo
	if ($plan_id == $new_plan_id && $current_plan_details['custom_conditions'] == 0){
		$new_plan_string = userPlanString($new_plan_id);

		$msg = "O Plano " . $new_plan_string . " já é o seu plano atual.";

		$feedback = array('type' => 'warning', 'title' => 'Impossível', 'msg' => $msg);
		echo json_encode($feedback);
		exit;
	}


	# Dá nome aos planos
	$new_plan_string = userPlanString($new_plan_id);
	if ($current_plan_details['custom_conditions'] == 1){
		$plan_string = "personalizado";
	} else {
		$plan_string = userPlanString($plan_id);
	}

	$iten_title = "Mudança de Plano | " . ucfirst($plan_string) . " p/ " . ucfirst($new_plan_string);

	# Seta valores dos planos
	$bronze_price = 0;
	$silver_price = 197;
	$gold_price = 497.90;

	// # Seta o valor no novo plano
	// switch ($new_plan_id) {
	// 	case 1:
	// 		$new_plan_price = $bronze_price;
	// 		break;
	// 	case 2:
	// 		$new_plan_price = $silver_price;
	// 		break;
	// 	case 3:
	// 		$new_plan_price = $gold_price;
	// 		break;
	// }

	# Verifica se é DownGrade ou Upgrade
	if ($plan_id == 1){
		$make_transaction = true;
	} else if ($plan_id == 2) {
		if ($new_plan_id == 1){
			$make_transaction = false;
		} else if ($new_plan_id == 3) {
			$make_transaction = true;
		}
	} else if ($plan_id == 3) {
		$make_transaction = false;
	} else if ($plan_id == 4) {
		$make_transaction = true;
	} else if ($plan_id == 5) {
		$feedback = array('type' => 'warning', 'title' => 'Mudança Indisponível', 'msg' => 'ADMs não podem mudar de plano.');
		echo json_encode($feedback);
		exit;
	} else {
		$feedback = array('type' => 'warning', 'title' => 'Mudança Indisponível', 'msg' => 'Entre em contato com o seu Gerente de Conta.');
		echo json_encode($feedback);
		exit;
	}
 	
	if ($new_plan_id == 1){
		$make_transaction = false;
	}

	# Em caso de UPGRADE, uma cobrança é feita referente ao
	# valor do novo plano selecionado - valor propocional ao aos dias restantes do plano atual.
	if ($make_transaction){
	
		#Cálcula o valor a ser cobrado.
		$today = new DateTime(date('Y-m-d'));
		$day_remain = $today->diff($renewal);

		$day_remain = $day_remain->days;

		if ($plan_price > 0){
			$discount = round(($plan_price / 30) * $day_remain);
		} else {
			$discount = 0;
		}

		$amount = round(($new_plan_price - $discount) * 100, 0);

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
						'id' => '10',
						'title' => 'Desconto - Propocional Plano Anterior',
						'unit_price' => round(($discount * 100), 0),
						'quantity' => 1,
						'tangible' => false
						],
						[
						'id' => $new_plan_id,
						'title' => $iten_title,
						'unit_price' => ($new_plan_price * 100),
						'quantity' => 1,
						'tangible' => false
						]
					]
				]);
				
				# Busca o Status da Transação de Cobrança
				$transaction_id = $transaction->id; 
				$tries = 0;

				do {
					$transaction = $pagarme->transactions()->get([
						'id' => $transaction_id
					]);
					$transaction_status = $transaction->status;
					$tries = $tries + 1;
					sleep(5);
				} while ($transaction_status == 'processing' && $tries < 2);

				# Feedback ao usuário sobre a cobrança/mudança
				if ($transaction_status == 'paid'){
					$type = 'success';
					$title = 'Mudança Realizada!';
					$msg = 'Uma cobrança de R$ ' . number_format(($amount/100), 2, ",", ".") . ' foi realizada no seu cartão.';

					$subscription_start = date('Y-m-d');
					$subscription_renewal = date('Y-m-d', strtotime($subscription_start . "+30days"));
					$custom_conditions = 0;

					# Insere os novos dados de plano do usuário no banco de dados,
					# em caso de aprovação da cobrança
					$update_user_plan = $conn->prepare('UPDATE subscriptions SET subscription_current_plan = :subscription_current_plan, user_plan_tax = :user_plan_tax, user_external_gateway_tax = :user_external_gateway_tax, user_plan_shipping_tax = :user_plan_shipping_tax, user_payment_term = :user_payment_term, custom_conditions = :custom_conditions, subscription_start = :subscription_start, subscription_renewal = :subscription_renewal, plan_price = :plan_price WHERE subscription_id = :subscription_id');
					$update_user_plan->execute(array('subscription_current_plan' => $new_plan_id, 'user_plan_tax' => $user_plan_tax, 'user_external_gateway_tax' => $user_external_gateway_tax, 'user_plan_shipping_tax' => $user_plan_shipping_tax, 'user_payment_term' => $user_payment_term, 'custom_conditions' => $custom_conditions, 'subscription_start' => $subscription_start, 'subscription_renewal' => $subscription_renewal, 'plan_price' => $new_plan_price, 'subscription_id' => $subscription_id));

					# Cria um Histórico dessa Mudança
					$meta_value = "{" . $plan_id . "{" . $new_plan_id . "{" . $subscription_start . "{}}}}";
					$create_change_plan_history = $conn->prepare('INSERT INTO subscriptions_meta (meta_id, subscription_id, meta_key, meta_value) VALUES (:meta_id, :subscription_id, :meta_key, :meta_value)');
					$create_change_plan_history->execute(array('meta_id' => 0, 'subscription_id' => $subscription_id, 'meta_key' => 'plan_change_done', 'meta_value' => $meta_value));

					# Atualiza as variáveis de sessão do usuário
					$_SESSION['UserPlan'] = $new_plan_id;
					$_SESSION['UserPlanTax'] = $user_plan_tax;
					$_SESSION['UserPlanString'] = userPlanString($new_plan_id);
					$_SESSION['UserPlanShipTax'] = $user_plan_shipping_tax;
					$_SESSION['UserPaymentTerm'] = $user_payment_term;        

				} else if ($transaction_status == 'refused'){
					$type = 'error';
					$title = 'Erro no Pagamento';
					$msg = 'A cobrança de R$ ' . number_format(($amount/100), 2, ",", ".") . ' no seu cartão foi recusada.';
				}

				$feedback = array('type' => $type, 'title' => $title, 'msg' => $msg);
				echo json_encode($feedback);
				exit;
				
			}
			
			catch (exception $e) {
		
				$feedback = array('type' => 'warning', 'title' => 'Erro na Transação (#500)', 'msg' => "Não foi posível processar o seu pagamento. Entre em contato com o Suporte e informe o código deste erro.");
				echo json_encode($feedback);
				exit;
		
			}
	}
	
	# Em caso de DOWNGRADE, nenhuma cobrança é realizada
	# e a mudança de plano é agendada para o final do próximo ciclo de cobrança do plano atual.
	else {
		
		# Cria um Histórico dessa Mudança
		$meta_value = "{" . $plan_id . "{" . $new_plan_id . "{" . date('Y-m-d') . "{}}}}";
		$search_scheduled_changes = $conn->prepare('SELECT meta_id FROM subscriptions_meta WHERE subscription_id = :user__id AND meta_key = "plan_change_scheduled"');
        $search_scheduled_changes->execute(array('user__id' => $user__id));
		
		if ($search_scheduled_changes->rowCount() > 0){
			$meta_id = $search_scheduled_changes->fetch();
			$meta_id = $meta_id['meta_id'];

			$upadate_change_plan_history = $conn->prepare('UPDATE subscriptions_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
			$upadate_change_plan_history->execute(array('meta_id' => $meta_id, 'meta_value' => $meta_value));	
		} else {
			$create_change_plan_history = $conn->prepare('INSERT INTO subscriptions_meta (meta_id, subscription_id, meta_key, meta_value) VALUES (:meta_id, :subscription_id, :meta_key, :meta_value)');
			$create_change_plan_history->execute(array('meta_id' => 0, 'subscription_id' => $subscription_id, 'meta_key' => 'plan_change_scheduled', 'meta_value' => $meta_value));	
		}

		$msg = "Seu Plano " . ucfirst($plan_string) . " continuará ativo até o dia " . date_format($renewal, "d/M") . ", e então sua conta passará para o Plano " . $new_plan_string . ".";
		$feedback = array('type' => 'success', 'title' => 'Mudança Realizada', 'msg' => $msg);
		echo json_encode($feedback);
		exit;

	}
	

}
?>