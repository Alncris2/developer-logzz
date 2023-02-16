<?php

require_once(__DIR__ . '/config.php');

function inserePedidoTabelaBling($pedidoId, $pedidoIdBling){
    
    global $conn;
    
    $sql = "INSERT INTO pedidos_bling (order_id, order_id_bling) VALUES ($pedidoId, $pedidoIdBling)";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    if($stmt->rowCount()){
        
        echo "Status do pedido inserido na tabela pedidos_bling!";
        return true;
        
    }
    
    echo 'Erro ao inserir pedido na tabela pedidos_bling!';
    return false;
    
}


function atualizaStatusPedido($pedidoId){
    
    global $conn;
    
    $sql = "UPDATE orders SET order_status = 7 WHERE order_id = $pedidoId";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    if ($stmt->rowCount()){
        
        echo "Status do pedido alterado para ENVIANDO";
        return true;
        
    }
    
    echo 'Erro ao atualizar status do pedido para ENVIANDO!';
    return false;
    
}

function getEstoqueByProductId($id_produto){
    
    global $conn;
    
    $sql = "SELECT MAX(`inventory_id`) as id FROM `inventories` WHERE `inventory_product_id` = $id_produto LIMIT 0,1";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_OBJ);
    $id_estoque = !empty($result->id) ? $result->id : false;
    
    unset($sql, $stmt, $result);
    
    if($id_estoque){
        
        $sql = "SELECT * FROM `inventories` WHERE `inventory_id` = $id_estoque LIMIT 0,1";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        
        return $qty_estoque = $result->inventory_quantity;
        
    }
    
    return false;
    
}


function enviaProduto($codigo){
    
    global $bling, $conn;
    
    $qty_estoque = getEstoqueByProductId($codigo);
    
    $ROOT_TAG = 'produto';

    $cabecalho_xml = '<?xml version="1.0" encoding="UTF-8"?><' . $ROOT_TAG . '></' . $ROOT_TAG . '>';
    
    $sql = "SELECT 
        * 
    FROM 
        products as p
    WHERE
        p.product_id = $codigo
    LIMIT 0,1 ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    while($row = $stmt->fetch(PDO::FETCH_OBJ)) {
        
        $produto = array(
            'codigo' => $row->product_id,
            'descricaoCurta' => $row->product_description,
            'descricao' => $row->product_name,
            'situacao' => 'Ativo',
            'vlr_unit' => $row->product_price,
            'estoque' => $qty_estoque
        );
        
        $xml = $bling->geraXml($produto, $cabecalho_xml);
        print_r($xml);
        
        $response = $bling->postProduct($xml);
        print_r($response);
    
    }
    
    
}


function matchExpress($string, $tipo = 'residencia'){
    
    $patterns = array(
        'residencia' => array(
            '/.+ [C|c]asa (\d{1,5}).+/',
            '/.+ nÂº[ ]*(\d+)/',
            '/.+ n[º]*[ ]*(\d+)/'
        ),
        'cep' => array(
            '/.+[C|c][E|e][P|p]:[ ]*(\d{1,8})/'
        )
    );
    
    foreach($patterns[$tipo] as $pattern){
        
        preg_match($pattern, $string, $matches);
        
        if($matches){
            return $matches[1];
        }
        
        continue;
        
    }
    
    return "";
    
}


/*function getNumeroResidencia($string){

    $retorno = matchExpress($string);
    
    if($retorno){
        return $retorno;
    }
    
    return "";
    
}*/

function getNumeroResidencia($string){

    $endereco = explode('<br>', $string);
    $numero = explode(',', $endereco[0]);
    
    if($numero){
        
        $excluir = array('nÂº', 'nº', ' ', '/\D+/');
        $numeroResidencia = str_replace($excluir, '', $numero[1]);
        
        return $numeroResidencia;
        
    }
    
    return "";
    
}


function getBairro($string){

    $bairro = explode('<br>', $string);
    
    if($bairro){
        return $bairro[1];
    }
    
    return "";
    
}

function getEndereco($string){

    $endereco = explode('<br>', $string);
    
    if($endereco){
        return $endereco[0];
    }
    
    return "";
    
}

function getComplemento($string){

    $complemento = explode('<br>', $string);
    
    if($complemento){
        return $complemento[2];
    }
    
    return "";
    
}

function getMunicipio($string){

    $municipioArray = explode('<br>', $string);
    
    if($municipioArray){
        
        $r = explode(',', $municipioArray[2]);
        
        $municipio = array(
            'cidade' => $r[0],
            'uf' => $r[1]
        );
        
        return $municipio;
    }
    
    return "";
    
}

function getCEP($string){

    $retorno = matchExpress($string, 'cep');
    
    if($retorno){
        return $retorno;
    }
    
    return "";
    
}

function getExternalId($order_id){
    
    global $conn;
    
    $sql = "SELECT external_id, client_name FROM orders WHERE order_id = $order_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    $pedido = $stmt->fetch(PDO::FETCH_OBJ);
    
    $transp = explode(' ', $pedido->client_name);
    $transp = str_replace(array('[', ']'), '', trim($transp[0]));
    
    switch($transp){
        case 'B':
            $transportadora = 'BRAIP';
            break;
        case 'M':
            $transportadora = 'MONETIZZE';
            break;
        default:
            $transportadora = null;
            break;
    }
    
    $dados = (object) array(
        'external_id' => $pedido->external_id,
        'transportadora' => $transportadora
    );
    
    return $dados;
    
}









