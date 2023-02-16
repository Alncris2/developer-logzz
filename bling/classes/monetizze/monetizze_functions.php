<?php


class Monetizze{
    
    private $codLog = null;
    private $consumerKey;
    private $baseUrl;
    
    public function __construct(){
        
        $this->baseUrl = 'https://api.monetizze.com.br/2.1/';
        
    }
    
    public function setConsumerKey($consumerKey){
        $this->consumerKey = $consumerKey;
    }
    
    public function setCodLog($codLog){
        $this->codLog = $codLog;
    }
    
    public function gera_token(){
    
        $endpoint = 'token';
    
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "X_CONSUMER_KEY: " . $this->consumerKey
        ));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_URL, $url); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    
        
        $res = curl_exec($ch);
        
        $dados = json_decode($res);
    
        return $dados->TOKEN;
        
        
    }
    
    public function envia_codigo_rastreio($transaction, $rastreio){
        
        $token = $this->gera_token();
    
        $dados = array(
            array(
                'codLog' => $this->codLog,
                'transaction' => $transaction,
                'trackingCode' => $rastreio
            )
        );
        
        $endpoint = 'sales/tracking';
    
        $url = $this->baseUrl . $endpoint;
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        $data_string = json_encode($dados);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["TOKEN:" . $token]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ["data" => $data_string]);
    
        $output = curl_exec($ch);
        curl_close($ch);
    
        echo $output;
    
    }
    
}




