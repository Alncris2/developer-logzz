<?php
require '../../vendor/autoload.php';

// $bearerToken = getBearerTokenFromGoogleFirebase()->access_token;

// $data = (object) [
//     'title' => 'Primeira notificação via API',
//     'body' => 'Esta notificação foi enviada pelo servidor usando a API do Firebase',
//     'targetFcmToken' => 'clAt6rdWvYo:APA91bGq-rUY0mFoqFmoGdvSRIqGOq7hCpP22n1O6EmmVE4I3K6uY8F7EFmIRoEwktVtIxy-QZ2dzW-LOhXoy9jfcydzSyTKbhjItWZZD7aXctJQuUOvWgn5aYYlX-ciYck4tPjgXifN'
// ];

// var_dump(sendPushNotification($bearerToken, $data));




function getBearerTokenFromGoogleFirebase($path_router = null): object
{
    $client = new Google_Client();
    $client->setAuthConfig($path_router);
    $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
    $client->refreshTokenWithAssertion();
    $token = (object) $client->getAccessToken();

    return $token;
}

function sendPushNotification($bearerToken, $data): object
{
    $mandatoryFields = [
        'title', 'body', 'targetFcmToken'
    ];

    if (!is_object($data)) {
        throw new Error("A variável data deve ser um objeto contendo os atributos title, body e targetFcmToken");
    }

    foreach ($mandatoryFields as $field) {
        if (!property_exists($data, $field)) {
            throw new Error("O atributo $field é obrigatório dentro da variável data");
        }
    }

    $client = new GuzzleHttp\Client();
    $response = $client->post('https://fcm.googleapis.com/v1/projects/app-logzz/messages:send', [
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer $bearerToken"
        ],

        'body' => json_encode([
            'message' => [
                'token' => $data->targetFcmToken,
                'notification' => [
                    'body' => $data->body,
                    'title' => $data->title
                ]
            ]
        ])
    ]);

    return (object) [
        'code' => $response->getStatusCode(),
        'body' => $response->getBody()->getContents()
    ];
}
