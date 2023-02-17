<?php

// namespace Classes\Braip;

class Braip{
    
    private $token;
    private $baseUrl;
    private $user_agent = 'braip_bling/1.0';
    
    const CORREIOS = 'traenopx';
    
    public function __construct(){
        
        $this->token = file_get_contents(__DIR__ . '/credentials.txt');
        $this->baseUrl = 'https://ev.braip.com/api/';
        
    }
    
    public function getToken(){
        return $this->token;
    }
    
    public function setCodigoRastreio($transaction, $rastreio, $transp = 'traenopx'){
        
        $token = $this->getToken();
    
        $dados = array(
            'transaction_key' => $transaction,
            'shipping_company_key' => $transp,
            'tracking_code' => $rastreio
        );
        
        $endpoint = 'v1/transporte';
    
        $url = $this->baseUrl . $endpoint;
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        $data_string = json_encode($dados);
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
            'User-Agent: ' . $this->user_agent
        ));
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    
        $output = curl_exec($ch);
        $error = curl_error($ch);
        
        if($error){
            echo '<pre>';
            echo $error;
            echo '</pre>';
        }
    
        return json_decode($output);
    
    }
    
    public function getVendas(){
        
        $token = $this->getToken();
        
        $endpoint = 'vendas?' . 'date_min=' . urlencode('2021-11-05 11:02:50') . '&date_max=' . urlencode('2021-12-05 11:02:50');
    
        $url = $this->baseUrl . $endpoint;
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Authorization: Bearer ' . $token,
            'User-Agent: ' . $this->user_agent
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
        $output = curl_exec($ch);
        curl_close($ch);
    
        return $output;
    
    }
    
}
