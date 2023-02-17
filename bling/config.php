<?php

require_once(__DIR__ . '/../includes/config.php');
require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/vendor/guzzlehttp/guzzle/src/Bling/Bling.php');

function geraCabecalhoXML($ROOT_TAG){
    $cabecalho = '<?xml version="1.0" encoding="UTF-8"?><'. $ROOT_TAG . '></' . $ROOT_TAG . '>';
    
    return $cabecalho;
}

$apiKey = '1c1793a4beac6036d4fbedc5abb42dcd71297897ea8bdc3af247523ed9e1302079edd7a1';

$bling = new Bling();
$bling->setApikey($apiKey);