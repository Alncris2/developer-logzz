<?php

require_once (dirname(__FILE__) . '/../includes/config.php');
require (dirname(__FILE__) . '/../vendor/autoload.php');

session_name(SESSION_NAME);
session_start();

# Variáveis internas
$user__id = $_SESSION['UserID'];

if (!$_POST){
	exit;
} else {
	$holder_name = $_POST['cc-name'];
	if (!preg_match("/^[(A-Za-z) ]*$/", $holder_name)) {
		$feedback = array('type' => 'warning', 'title' => 'Cartão Inválido', 'msg' => 'Confira o nome impresso no cartão e tente novamente.');
		echo json_encode($feedback);
		exit;
	}

	$number = str_replace(" ", "", $_POST['cc-number']);
	if (!preg_match("/^[(0-9) ]*$/", $number)) {
		$feedback = array('type' => 'warning', 'title' => 'Cartão Inválido', 'msg' => 'Confira o número do cartão e tente novamente.');
		echo json_encode($feedback);
		exit;
	}

	$expiration_date = str_replace("/", "", $_POST['cc-expiration']);
	if (!preg_match("/^[(0-9) ]*$/", $expiration_date)) {
		$feedback = array('type' => 'warning', 'title' => 'Cartão Inválido', 'msg' => 'Confira a data de validade do cartão e tente novamente.');
		echo json_encode($feedback);
		exit;
	} else if ($expiration_date > 1299 || $expiration_date < 122){
		$feedback = array('type' => 'warning', 'title' => 'Cartão Inválido', 'msg' => 'Confira a data de validade do cartão e tente novamente.');
		echo json_encode($feedback);
		exit;
	}

	$cvv = $_POST['cc-cvv'];
	if (!preg_match("/^[(0-9) ]*$/", $expiration_date)) {
		$feedback = array('type' => 'warning', 'title' => 'Cartão Inválido', 'msg' => 'Confira a data de validade do cartão e tente novamente.');
		echo json_encode($feedback);
		exit;
	}
}

$pagarme = new PagarMe\Client(PGME_API_KEY);

$add_new_card = $pagarme->cards()->create([
    'holder_name' => $holder_name,
    'number' => $number,
    'expiration_date' => $expiration_date,
    'cvv' => $cvv
]);


if (!($add_new_card->valid)){
	
	$feedback = array('type' => 'warning', 'title' => 'Cartão Inválido', 'msg' => 'Não foi possível validar este cartão. Confira os dados ou tente outro.');
	echo json_encode($feedback);
	exit;

} else {

	$check_current_card = $conn->prepare('SELECT card_id FROM cards WHERE card_user_id = :user__id AND card_active = 1');
	$check_current_card->execute(array('user__id' => $user__id));

	if ($check_current_card->rowCount() != 0 ){

		$card_id = $check_current_card->fetch();
		$card_id = $card_id['card_id'];

		$change_current_card = $conn->prepare('UPDATE cards SET card_active = 0 WHERE card_id = :card_id');
		$change_current_card->execute(array('card_id' => $card_id));
	}

	$card_id = 0;
	$card_user_id = $user__id;
	$card_hash = $add_new_card->id;
	$card_brand = $add_new_card->brand;
	$card_final = $add_new_card->last_digits;
	$card_active = 1;

	$save_card = $conn->prepare('INSERT INTO cards (card_id, card_user_id, card_hash, card_brand, card_final, card_active) VALUES (:card_id, :card_user_id, :card_hash, :card_brand, :card_final, :card_active)');
	$save_card->execute(array('card_id' => $card_id, 'card_user_id' => $card_user_id, 'card_hash' => $card_hash, 'card_brand' => $card_brand, 'card_final' => $card_final, 'card_active' => $card_active));

	$feedback = array('type' => 'success');
	echo json_encode($feedback);
	exit;
}

?>