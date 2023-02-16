<?php
require_once (dirname(__FILE__) . "/../../vendor/autoload.php");

$client = new \GuzzleHttp\Client();

$response = $client->request('POST', 'https://logzz.api-us1.com/api/3/contacts', [
  'body' => '{"contact":{"email":"johndoe22@example.com","firstName":"John","lastName":"Doe","phone":"7223224241","fieldValues":[{"field":"1","value":"The Value for First Field"},{"field":"6","value":"2008-01-20"}]}}',
  'headers' => [
    'Accept' => 'application/json',
    'Api-Token' => '0d209c1add67a70d91111633451cd92572bace06b0333a099b8c9246449eb035d14ea2c4',
    'Content-Type' => 'application/json',
  ],
]);

$data = $response->getBody();

$data = json_decode($data, true);

echo $data['fieldValues'][0]['contact'];