<?php

function gera_token(){
    
    $url = "https://api.monetizze.com.br/2.1/token";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "X_CONSUMER_KEY: W15K1jFS5esPrzckt4pVPReGc6czPPUF"
    ));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_URL, $url); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    
    
    $res = curl_exec($ch);
    
    $dados = json_decode($res);

    print_r($dados);

    return $dados->TOKEN;
    
    
}

function envia_codigo_rastreio($transaction, $rastreio){
    
    $token = gera_token();

    $dados = array(
        array(
            'codLog' => 1,
            'transaction' => $transaction,
            'trackingCode' => $rastreio
        )
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.monetizze.com.br/2.1/sales/tracking");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $data_string = json_encode($dados);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["TOKEN:" . $token]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ["data" => $data_string]);

    $output = curl_exec($ch);
    curl_close($ch);

    echo $output;

}


echo '<pre>';
gera_token();





