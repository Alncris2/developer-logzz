<?php

require_once (dirname(__FILE__) . '/includes/config.php');
require('vendor/autoload.php');

$pagarme = new PagarMe\Client('ak_test_N951BStfuEcJlJV9x0sKtSpPASgn28');

$card = $pagarme->cards()->create([
    'holder_name' => 'Yoda',
    'number' => '5233302128979646',
    'expiration_date' => '0224',
    'cvv' => '497'
]);


?>