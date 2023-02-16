<?php

require_once(__DIR__ . '/../../../../autoload.php');

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Client;

class Bling {

  private $baseUrl;
  private $apikey;
  private $page;
  private $client;

  public function __construct(){

    $this->client = new GuzzleHttp\Client();

    $this->baseUrl = 'https://bling.com.br/Api/v2/';
    $this->page = 1;
    
  }

  public function getApikey(){

    return $this->apikey;

  }

  public function setApikey($apikey){

    return $this->apikey = $apikey;

  }

  public function getPedidos($page = 1, $filters = ''){

    $path = $this->baseUrl . 'pedidos/page=' . $page . '/json/&apikey=' . $this->apikey;

    if($filters){
      $path .= $filters;
    }

    try{
      
      $request = $this->client->request('GET', $path);
    
    }catch(ClientException $e){
      
      echo $e->getMessage();
    
    }

    if($request){

      $statusCode = $request->getStatusCode();
      $response = json_decode($request->getBody());

      if($statusCode == 200){

        return $response->retorno->pedidos;
        
      }else{
        
        return $response;
      
      }
    
    }

  }

  public function getPedidoById(String $id){

    $path = $this->baseUrl . 'pedidos/page=' . $page . '/json/&apikey=' . $this->apikey;

    if($filters){
      $path .= $filters;
    }

    try{
      
      $request = $this->client->request('GET', $path);
    
    }catch(ClientException $e){
      
      echo $e->getMessage();
    
    }

    if($request){

      $statusCode = $request->getStatusCode();
      $response = json_decode($request->getBody());

      if($statusCode == 200){

        return $response->retorno->pedidos;
        
      }else{
        
        return $response;
      
      }
    
    }

  }

  public function putStockPrice(String $sku, Int $qty, $preco, String $deposito = ''){

    $path = $this->baseUrl . 'produto/' . $sku . '/json/';

    if($deposito){
      
      $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <produto>
          <deposito>
            <estoque>' . $qty . '</estoque>
            <id>' . $deposito . '</id>
          </deposito>
        </produto>';
    
    }else{

      $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <produto>
          <estoque>' . $qty . '</estoque>
          <vlr_unit>' . $preco . '</vlr_unit>
        </produto>';

    }
    
    $header = array(
      "form_params" => array(
        "apikey" => $this->apikey,
        "xml" => rawurlencode($xml)
      )
    );

    try{

    }catch(ClientException $e){

      echo $e->getMessage();
    
    }
    
    $request = $this->client->request('POST', $path, $header);

    if($request){

      $statusCode = $request->getStatusCode();
      $response = json_decode($request->getBody());

      if($statusCode == 201){
  
        return $response->retorno->produtos;
        
      }else{
        
        return $response;

      }
  
    }

  }

  public function postProduct(String $xml){

    $path = $this->baseUrl . 'produto/json/';
    
    $header = array(
      "form_params" => array(
        "apikey" => $this->apikey,
        "xml" => rawurlencode($xml)
      )
    );

    try{
    
        $request = $this->client->request('POST', $path, $header);

    }catch(ClientException $e){

      echo $e->getMessage();
    
    }

    if($request){

      $statusCode = $request->getStatusCode();
      $response = json_decode($request->getBody());

      if($statusCode == 201){
  
        return $response->retorno->produtos;
        
      }else{
        
        return $response;

      }
  
    }

  }

  public function geraXml(Array $array, $rootElement = null, $xml = null) {
    
    $_xml = $xml;
      
    // If there is no Root Element then insert root
    if ($_xml === null) {
      $_xml = new SimpleXMLElement($rootElement !== null ? $rootElement : '<root/>');
    }
      
    // Visit all key value pair
    foreach ($array as $k => $v) {
          
      // If there is nested array then
      if (is_array($v)) { 
            
        // Call function for nested array
        $this->geraXml($v, $k, $_xml->addChild(str_replace(' ', '', $k)));

      }else {

        // Simply add child element. 
        $_xml->addChild(str_replace(' ', '', $k), $v);
      
      }
    
    }
      
    return $_xml->asXML();

  }

  public function postPedido($xml, $gerarnfe = false){

    $path = $this->baseUrl . 'pedido/json/';
    
    $header = array(
      "form_params" => array(
        "apikey" => $this->apikey,
        "gerarnfe" => $gerarnfe,
        "xml" => rawurlencode($xml)
      )
    );

    try{

    }catch(ClientException $e){

      echo $e->getMessage();
    
    }
    
    $request = $this->client->request('POST', $path, $header);

    if($request){

      $statusCode = $request->getStatusCode();
      $response = json_decode($request->getBody());

      if($statusCode == 201){
  
        return $response;
        
      }else{
        
        return $response;

      }
  
    }

  }
  
    public function postContato($xml){

        $path = $this->baseUrl . 'contato/json/';
        
        $header = array(
          "form_params" => array(
            "apikey" => $this->apikey,
            "xml" => rawurlencode($xml)
          )
        );
    
        try{
    
        }catch(ClientException $e){
    
          echo $e->getMessage();
        
        }
        
        $request = $this->client->request('POST', $path, $header);
    
        if($request){
    
          $statusCode = $request->getStatusCode();
          $response = json_decode($request->getBody());
    
          if($statusCode == 201){
      
            return $response;
            
          }else{
            
            return $response;
    
          }
      
        }
    
    }
  
    public function verifyProductBling($codigo){
        
        $path = $this->baseUrl . 'produto/' . $codigo . '/json/&apikey=' . $this->apikey;
        
        try{
      
          $request = $this->client->request('GET', $path);
        
        }catch(ClientException $e){
          
          echo $e->getMessage();
          return false;
        
        }
        
        
        
        $statusCode = $request->getStatusCode();
        $response = json_decode($request->getBody());
    
        if($statusCode == 200 && isset($response->retorno->produtos)){
        
            return true;
        
        }
        
        return false;
        
    }

  public function getContacts($page = 1){

    $path = $this->baseUrl . 'contatos/page=' . $page . '/json/&apikey=' . $this->apikey;

    try{
      
      $request = $this->client->request('GET', $path);
    
    }catch(ClientException $e){
      
      echo $e->getMessage();
    
    }

    if($request){

      $statusCode = $request->getStatusCode();
      $response = json_decode($request->getBody());

      if($statusCode == 200){
  
        if(isset($response->retorno->contatos)){
        
          return $response->retorno->contatos;
        
        }else{

          return $response->retorno;
        
        }
        
      }else{
        
        return $response->retorno;
      
      }

    }

  }

}