<?php

require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/functions.php');

$ROOT_TAG = 'pedido';

$cabecalho_xml = geraCabecalhoXML($ROOT_TAG);

$sql = "SELECT 
    * 
FROM 
    orders AS o,
    users  AS u
WHERE
    o.user__id = u.user__id AND
    o.order_status = 6
";

//o.order_status = 6 -> A Enviar

$stmt = $conn->prepare($sql);
$stmt->execute();

echo "<pre>";

$servico = 'PAC - CONTRATO';

while($row = $stmt->fetch(PDO::FETCH_OBJ)) {
    
    $address = $row->client_address;
    
    $nomeCliente = str_replace(array('[', ']'), '', trim($row->client_name));
    $endereco = getEndereco($address);
    $numero = getNumeroResidencia($address);
    $cep = getCEP($address);
    $bairro = getBairro($address);
    $complemento = getComplemento($address);
    $municipio = getMunicipio($address);
    
    switch($row->company_type){
        case 'fisica':
            $type_company = 'F';
            break;
        default:
            $type_company = 'J';
            break;
    }
    
    $cliente_dados = array(
        'nome' => $nomeCliente,
        'endereco' => $endereco,
        'tipoPessoa' => $type_company,
        'cpf_cnpj' => $row->company_doc,
        'numero' => $numero,
        'complemento' => $complemento,
        'cep' => $cep,
        'cidade' => $municipio['cidade'],
        'uf' => $municipio['uf'],
        'bairro' => $bairro,
        'contribuinte' => 2,
        'ie_rg' => 'ISENTO'
    );
    
    $dados = array(
        'cliente' => array(
            'nome' => $nomeCliente,
            'endereco' => $endereco,
            'tipoPessoa' => $type_company,
            'cpf_cnpj' => $row->company_doc,
            'numero' => $numero,
            'complemento' => $complemento,
            'cep' => $cep,
            'cidade' => $municipio['cidade'],
            'uf' => $municipio['uf'],
            'bairro' => $bairro
        ),
        'transporte' => array(
            'transportadora' => 'CORREIOS',
            'tipo_frete' => 'R',
            'servico_correios' => $servico,
            'dados_etiqueta' => array(
                'nome' => $nomeCliente,
                'endereco' => $endereco,
                'numero' => $numero,
                'complemento' => $complemento,
                'cep' => $cep,
                'municipio' => $municipio['cidade'],
                'uf' => $municipio['uf'],
                'bairro' => $bairro
            ),
            'volumes' => array(
                'volume' => array(
                    'servico' => $servico,
                    'codigoRastreamento' => null
                )
            )
        ),
        'obs_internas' => $row->order_id
    );
    
    $getItems = "SELECT * FROM sales as s WHERE sale_id = $row->sale_id";
    $stmt_items = $conn->prepare($getItems);
    $stmt_items->execute();
    
    $items = $stmt_items->fetchAll(PDO::FETCH_OBJ);
    
    foreach($items as $key => $item){
        
        $verifyProduct = $bling->verifyProductBling($row->product_id);
        
        if(!$verifyProduct){
            
            enviaProduto($row->product_id);
            
        }
        
        /*
        * ADICIONA ESPAÇOS EM BRANCO NA TAG PARA QUE NÃO SOBRESCREVA AS KEYS DO ARRAY
        * QUANDO A STRING DO XML É GERADA, ESSES ESPAÇOS SÃO RETIRADOS
        */
        
        $nome_campo = 'item' . str_repeat(' ', $key);
        
        $dados['itens'][$nome_campo] = array(
            'codigo' => $row->product_id,
            'descricao' => $row->product_name,
            'qtde' => $item->sale_quantity,
            'vlr_unit' => $item->product_price
        );
        
    }
    
    // $cabecalho_xml_cliente = geraCabecalhoXML('contato');
    // $cliente_xml = $bling->geraXml($cliente_dados, $cabecalho_xml_cliente);
    // $cliente = $bling->postContato($cliente_xml);
    // print_r($cliente_xml);
    // print_r($cliente_dados);
    // print_r($cliente);
    
    // exit();
    
    $xml = $bling->geraXml($dados, $cabecalho_xml);
    print_r($xml);
    $pedido = $bling->postPedido($xml);
    print_r($pedido);
    
    if(isset($pedido->retorno->pedidos)){
        
        $numeroPedidoBling = $pedido->retorno->pedidos[0]->pedido->numero;
        
        $statusAtualizado = atualizaStatusPedido($row->order_id);
        $statusAtualizado ? inserePedidoTabelaBling($row->order_id, $numeroPedidoBling) : false;
        
    }
    
    exit();

}


exit();

$dados = array(
  'cliente' => array (
    'nome' => 'Noida',
    'tipoPessoa' => 'UP',
    'endereco' => 'feedback@geeksforgeeks.org',
    'cpf_cnpj' => 'feedback@geeksforgeeks.org',
    'ie' => 'feedback@geeksforgeeks.org',
    'numero' => 'feedback@geeksforgeeks.org',
    'complemento' => 'feedback@geeksforgeeks.org',
    'bairro' => 'feedback@geeksforgeeks.org',
    'cep' => 'feedback@geeksforgeeks.org',
    'cidade' => 'feedback@geeksforgeeks.org',
    'uf' => 'feedback@geeksforgeeks.org',
    'fone' => 'feedback@geeksforgeeks.org',
    'email' => 'feedback@geeksforgeeks.org'
  ),
  'itens' => array(
    'item' => array(
      'codigo' => '001',
      'descricao' => '001',
      'un' => 'Pç',
      'qtde' => '10',
      'vlr_unit' => '1.68',
    )
  ),
  'name' => 'GFG',
  'subject' => 'CS'
);


$xml = '<pedido>
  <cliente>
   <nome>Organisys Software</nome>
   <tipoPessoa>J</tipoPessoa>
   <endereco>Rua Visconde de São Gabriel</endereco>
   <cpf_cnpj>00000000000000</cpf_cnpj>
   <ie>3067663000</ie>
   <numero>392</numero>
   <complemento>Sala 54</complemento>
   <bairro>Cidade Alta</bairro>
   <cep>95.700-000</cep>
   <cidade>Bento Gonçalves</cidade>
   <uf>RS</uf>
   <fone>5481153376</fone>
   <email>teste@teste.com.br</email>
  </cliente>
<itens>
   <item>
      <codigo>001</codigo>
      <descricao>Caneta 001</descricao>
      <un>Pç</un>
      <qtde>10</qtde>
      <vlr_unit>1.68</vlr_unit>
   </item>
</itens>
<parcelas>
   <parcela>
      <data>01/09/2009</data>
      <vlr>100</vlr>
      <obs>Teste obs 1</obs>
   </parcela>
   <parcela>
      <data>06/09/2009</data>
      <vlr>50</vlr>
      <obs />
   </parcela>
   <parcela>
      <data>11/09/2009</data>
      <vlr>50</vlr>
      <obs>Teste obs 3</obs>
   </parcela>
</parcelas>
<vlr_frete>15</vlr_frete>
<vlr_desconto>10</vlr_desconto>
<obs>Testando o campo observações do pedido</obs>
<obs_internas>Testando o campo observações internas do pedido</obs_internas>
</pedido>';