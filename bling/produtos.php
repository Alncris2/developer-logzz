<?php

require_once(__DIR__ . '/config.php');

$ROOT_TAG = 'produto';

$cabecalho_xml = '<?xml version="1.0" encoding="UTF-8"?><' . $ROOT_TAG . '></' . $ROOT_TAG . '>';

$sql = "SELECT 
    * 
FROM 
    products as p
WHERE
    p.product_id NOT IN (
        SELECT 
            COD_PROD
        FROM
            produtos_bling
    )
LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->execute();

echo "<pre>";

while($row = $stmt->fetch(PDO::FETCH_OBJ)) {
    
    $produto = array(
        'codigo' => $row->product_id,
        'descricaoCurta' => $row->product_description,
        'descricao' => $row->product_name,
        'situacao' => 'Ativo',
        'vlr_unit' => $row->product_price
    );
    
    $xml = $bling->geraXml($produto, $cabecalho_xml);
    print_r($xml);
    
    $response = $bling->postProduct($xml);
    print_r($response);
    
    exit();

}

