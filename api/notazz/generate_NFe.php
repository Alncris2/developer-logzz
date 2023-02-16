<?php
header("Content-type: text/html; charset=utf-8");
// Orientaчѕes para utilizaчуo da API de emissуo / consulta / atualizaчуo / exclusуo de notas fiscais do Notazz.
// Linguagem: PHP
// Versуo: 1.0.0

function send_data($fields)
{
	$fields = array("fields"=>$fields);
	$fields_string = '';
	
	//url-ify the data for the POST
	foreach($fields as $key=>$value)
	{
		$fields_string .= $key.'='.$value.'&';
	}
	
	rtrim($fields_string, '&');
	
	//open connection
	$ch = curl_init();
	
	//set the url, number of POST vars, POST data
	curl_setopt($ch,CURLOPT_URL, 'https://app.notazz.com/api');
	curl_setopt($ch,CURLOPT_POST, count($fields));
	curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, FALSE);
	
	//execute post
	$response = curl_exec($ch);
	
	//close connection
	curl_close($ch);
	
	//Convertendo json para array
	$pos = strpos($response, '{');
	
	return(json_decode(substr($response, $pos), true));
}

/*
Mщtodos disponэveis: 
	NFS-e (Nota Fiscal de Serviчo)
	 - create_nfse (cria nota de serviчo)
	 - consult_nfse (consulta nota fiscal de serviчo individual)
	 - consult_all_nfse (consulta vсrias notas de serviчo)
	 - update_nfse (atualiza os dados da nota de serviчo, sѓ щ possэvel atualizar dados de uma nota com o status = Pendente ou Rejeitada)
	 - delete_nfse (exclui nota fiscal de serviчo nуo autorizada)
	 
	NF-e (Nota Fiscal de Produto - Modelo 55)
	 - create_nfe_55 (cria nota de produto)
	 - consult_nfe_55 (consulta nota de produto individual)
	 - consult_all_nfe_55 (consulta vсrias notas de produto)
	 - update_nfe_55 (atualiza os dados da fiscal de produto, sѓ щ possэvel atualizar dados de uma nota com o status = Pendente ou Rejeitada) 
	 - delete_nfe_55 (exclui nota fiscal de produto nуo autorizada) 	 

	Status disponэveis na consulta de uma nota: 
	 - Pendente = Nota fiscal estс cadastrada no sistema mas ainda nуo ocorreu nenhuma iteraчуo com a mesma
	 - AguardandoAutorizacao = A nota foi enviada para a SEFAZ / Prefeitura mas ainda nуo tivemos o retorno dela
	 - Autorizada = Nota fiscal emitida e autorizada pela SEFAZ / Prefeitura
	 - Rejeitada = Nota fiscal foi rejeitada pela SEFAZ / Prefeitura. Nesse caso a API retorna o motivo da rejeiчуo
	 - Cancelada = Nota fiscal foi cancelada na SEFAZ / Prefeitura

	Observaчуo: a api faz um controle de requisiчѕes por minuto, sendo que a cada 1 minuto deverс ocorrer no mсximo 100 requisiчѕes.
*/

//Cria uma nota fisca de serviчo
function create_nfse()
{
	$fields = json_encode(array(
		'API_KEY' => 'SUA CHAVE DE API',//Sua chave de acesso API_KEY. Para obter a chave, acesse o menu Configuraчѕes > Empresas. Cada empresa terс sua chave de comunicaчуo com a API
		'METHOD' => 'create_nfse',//Mщtodo a ser utilizado
		
		'DESTINATION_NAME' => 'Jose Maria da Silva',// Nome completo do cliente
		'DESTINATION_TAXID' => '35281474588',//CPF ou CNPJ, somente nњmeros
		'DESTINATION_IE' => '',//Inscriчуo Estadual (opcional), somente nњmeros
		'DESTINATION_IM' => '',//Inscriчуo Municipal (opcional), somente nњmeros
		'DESTINATION_TAXTYPE' => 'F',//F = Fэsica, J = Jurэdica, E = Estrangeiro
		'DESTINATION_STREET' => 'Rua de teste',//Rua do cliente
		'DESTINATION_NUMBER' => '123',//Nњmero
		'DESTINATION_COMPLEMENT' => 'Prѓximo a escola municipal floriano peixoto',//Complemento
		'DESTINATION_DISTRICT' => 'Centro',//Bairro
		'DESTINATION_CITY' => 'Belo Horizonte',//Cidade, informar corretamente o nome da cidade sem abreviaчѕes
		'DESTINATION_UF' => 'MG',//Sigla do estado
		'DESTINATION_ZIPCODE' => '35560000',//CEP, somente nњmeros
		'DESTINATION_PHONE' => '31999998888',//Telefone do cliente (opcional), somente nњmeros
		'DESTINATION_EMAIL'	=> 'test@test.com',//E-mail do cliente (opcional)
		
		'DESTINATION_EMAIL_SEND' => array(
			'1'	=> array(
				'EMAIL' => 'mail1@example.com',
			),
			
			'2'	=> array(
				'EMAIL' => 'mail2@example.com'
			)
		),//e-mail(s) que serс enviado a nota depois de emitida (opcional).

		
		'DOCUMENT_BASEVALUE' => '100.00',//Valor total da nota fiscal. Utilizar ponto para separar as casas decimais
		'DOCUMENT_DESCRIPTION' => 'Prestaчуo de Serviчo em consultoria',//Descriчуo da nota fiscal (obrigatѓrio somente para o mщtodo create_nfse e update_nfse)
		'DOCUMENT_COMPETENCE' => date("Y-m-d"), //Competъncia (opcional), se nуo informado ou informado invсlido serс utilizado a data de hoje. Utilizar o padrуo YYYY-mm-dd
		'DOCUMENT_CNAE' => '8599604', //CNAE, somente nњmeros (opcional), se nуo informado ou informado invсlido serс utilizado o padrуo das configuraчѕes da empresa. Documentaчуo: http://www.cnae.ibge.gov.br
		'SERVICE_LIST_LC116' => '0802', //Item da Lista de Serviчo da Lei Complementar 116 (opcional), somente nњmeros. Caso nуo seja informado serс utilizado o padrуo da empresa. Documentaчуo: http://www.fazenda.mg.gov.br/empresas/legislacao_tributaria/ricms/anexoxiii2002.pdf
		'WITHHELD_ISS' => '0', // ISS retido na fonte (opcional). 1 = Retido e 0 = Nуo retido. Se nуo informado ou informado invсlido serс utilizado o padrуo das configuraчѕes da empresa 
		'CITY_SERVICE_CODE' => '12345', // Cѓdigo de serviчo do municэpio (opcional), somente nњmeros. Se nуo seja informado serс utilizado o padrуo da empresa
		
		'ALIQUOTAS' => array(
			'COFINS' => '0.00', // Porcentagem (%) - Utilizar ponto para separar as casas decimais
			'CSLL' => '0.00', // Porcentagem (%) - Utilizar ponto para separar as casas decimais
			'INSS' => '0.00', // Porcentagem (%) - Utilizar ponto para separar as casas decimais
			'IR' => '0.00', // Porcentagem (%) - Utilizar ponto para separar as casas decimais
			'PIS' => '0.00', // Porcentagem (%) - Utilizar ponto para separar as casas decimais
			'ISS' => '2.00', // Porcentagem (%) - Utilizar ponto para separar as casas decimais
		), // Opcional - se nуo informado ou informado invсlido serс utilizado o padrуo das configuraчѕes da empresa 
		
		'EXTERNAL_ID' => '89678678', // ID externo do documento que serс enviado		
	));
	
	$retorno = send_data($fields);//Chamada do mщtodo de envio, retorna os dados em array
}

//Atualiza uma nota fiscal de serviчo jс criada e nуo transmitida
function update_nfse()
{
	$fields = json_encode(array(
		'API_KEY' => 'SUA CHAVE DE API',//Sua chave de acesso API_KEY. Para obter a chave, acesse o menu Configuraчѕes > Empresas. Cada empresa terс sua chave de comunicaчуo com a API
		'METHOD' => 'update_nfse',//Mщtodo a ser utilizado
		
		'DESTINATION_NAME' => 'Jose Maria da Silva',// Nome completo do cliente
		'DESTINATION_TAXID' => '35281474588',//CPF ou CNPJ, somente nњmeros
		'DESTINATION_IE' => '',//Inscriчуo Estadual (opcional), somente nњmeros
		'DESTINATION_IM' => '',//Inscriчуo Municipal (opcional), somente nњmeros
		'DESTINATION_TAXTYPE' => 'F',//F = Fэsica, J = Jurэdica, E = Estrangeiro
		'DESTINATION_STREET' => 'Rua de teste',//Rua do cliente
		'DESTINATION_NUMBER' => '123',//Nњmero
		'DESTINATION_COMPLEMENT' => 'Prѓximo a escola municipal floriano peixoto',//Complemento
		'DESTINATION_DISTRICT' => 'Centro',//Bairro
		'DESTINATION_CITY' => 'Belo Horizonte',//Cidade, informar corretamente o nome da cidade sem abreviaчѕes
		'DESTINATION_UF' => 'MG',//Sigla do estado
		'DESTINATION_ZIPCODE' => '35560000',//CEP, somente nњmeros
		'DESTINATION_PHONE' => '31999998888',//Telefone do cliente (opcional), somente nњmeros
		'DESTINATION_EMAIL'	=> 'test@test.com',//E-mail do cliente (opcional)
		
		'DESTINATION_EMAIL_SEND' => array(
			'1'	=> array(
				'EMAIL' => 'mail1@example.com',
			),
			
			'2'	=> array(
				'EMAIL' => 'mail2@example.com'
			)
		),//e-mail(s) que serс enviado a nota depois de emitida (opcional).
		
		'DOCUMENT_BASEVALUE' => '100.00',//Valor total da nota fiscal. Utilizar ponto para separar as casas decimais
		'DOCUMENT_DESCRIPTION' => 'Prestaчуo de Serviчo em consultoria',//Descriчуo da nota fiscal (obrigatѓrio somente para o mщtodo create_nfse e update_nfse)
		'DOCUMENT_COMPETENCE' => date("Y-m-d"), //Competъncia (opcional), se nуo informado ou informado invсlido serс utilizado a data de hoje. Utilizar o padrуo YYYY-mm-dd		
		'DOCUMENT_CNAE' => '8599604', //CNAE, somente nњmeros (opcional), se nуo informado ou informado invсlido serс utilizado o padrуo das configuraчѕes da empresa. Documentaчуo: http://www.cnae.ibge.gov.br
		'SERVICE_LIST_LC116' => '0802', //Item da Lista de Serviчo da Lei Complementar 116 (opcional), somente nњmeros. Caso nуo seja informado serс utilizado o padrуo da empresa. Documentaчуo: http://www.fazenda.mg.gov.br/empresas/legislacao_tributaria/ricms/anexoxiii2002.pdf
		'WITHHELD_ISS' => '0', // ISS retido na fonte (opcional). 1 = Retido e 0 = Nуo retido. Se nуo informado ou informado invсlido serс utilizado o padrуo das configuraчѕes da empresa 
		'CITY_SERVICE_CODE' => '12345', // Cѓdigo de serviчo do municэpio (opcional), somente nњmeros. Se nуo seja informado serс utilizado o padrуo da empresa
		
		'ALIQUOTAS' => array(
			'COFINS' => '0.00', // Porcentagem (%) - Utilizar ponto para separar as casas decimais
			'CSLL' => '0.00', // Porcentagem (%) - Utilizar ponto para separar as casas decimais
			'INSS' => '0.00', // Porcentagem (%) - Utilizar ponto para separar as casas decimais
			'IR' => '0.00', // Porcentagem (%) - Utilizar ponto para separar as casas decimais
			'PIS' => '0.00', // Porcentagem (%) - Utilizar ponto para separar as casas decimais
			'ISS' => '2.00', // Porcentagem (%) - Utilizar ponto para separar as casas decimais
		), // Opcional - se nуo informado ou informado invсlido serс utilizado o padrуo das configuraчѕes da empresa 
				
		'DOCUMENT_ID' => '4ed0f528ded523d2681e3f9688d63d26',//Cѓdigo retornado pelo sistema apѓs utilizar o mщtodo create_nfse ou create_nfe_55. Utilizar esta variсvel para o mщtodo consult_nfe_55, consult_nfse, delete_nfe_55, delete_nfse, update_nfe_55, update_nfse
		'EXTERNAL_ID' => '89678678', // ID externo do documento que serс atualizado		
	));
	
	$retorno = send_data($fields);//Chamada do mщtodo de envio, retorna os dados em array
}

//Consulta uma nota fiscal de serviчo individualmente pelo seu id
function consult_nfse()
{
	$fields = json_encode(array(
		'API_KEY' => 'SUA CHAVE DE API',//Sua chave de acesso API_KEY. Para obter a chave, acesse o menu Configuraчѕes > Empresas. Cada empresa terс sua chave de comunicaчуo com a API
		'METHOD' => 'consult_nfse',//Mщtodo a ser utilizado
		'DOCUMENT_ID' => '4ed0f528ded523d2681e3f9688d63d26',//Cѓdigo retornado pelo sistema apѓs utilizar o mщtodo create_nfse ou create_nfe_55. Utilizar esta variсvel para o mщtodo consult_nfe_55, consult_nfse, delete_nfe_55, delete_nfse, update_nfe_55, update_nfse
		'EXTERNAL_ID' => '89678678', // ID externo do documento que serс consultado		
	));
	
	$retorno = send_data($fields);//Chamada do mщtodo de envio, retorna os dados em array
}

//Consulta vсrias notas fiscais de serviчo de acordo com os filtros configurados
function consult_all_nfse()
{
	$fields = json_encode(array(
		'API_KEY' => 'SUA CHAVE DE API',//Sua chave de acesso API_KEY. Para obter a chave, acesse o menu Configuraчѕes > Empresas. Cada empresa terс sua chave de comunicaчуo com a API
		'METHOD' => 'consult_all_nfse',//Mщtodo a ser utilizado
		'FILTER' => array(
			'INITIAL_DATE' => '2017-01-01', // Data Inicial. Campo obrigatѓrio, enviar no formato yyyy-mm-dd
			'FINAL_DATE' => '2017-01-01', // Data Final. Campo obrigatѓrio, enviar no formato yyyy-mm-dd
			'STATUS' => 'Autorizada', // Status da nota fiscal. Campo opcional
			'NUMBER' => '25', // Nњmero da nota fiscal jс autorizada ou cancelada. Campo opcional
		)
	));
	
	$retorno = send_data($fields);//Chamada do mщtodo de envio, retorna os dados em array
}

//Cancela uma nota fiscal de serviчo que jс foi autorizada
function cancel_nfse()
{
	$fields = json_encode(array(
		'API_KEY' => 'SUA CHAVE DE API',//Sua chave de acesso API_KEY. Para obter a chave, acesse o menu Configuraчѕes > Empresas. Cada empresa terс sua chave de comunicaчуo com a API
		'METHOD' => 'cancel_nfse',//Mщtodo a ser utilizado
		'DOCUMENT_ID' => '4ed0f528ded523d2681e3f9688d63d26',//Cѓdigo retornado pelo sistema apѓs utilizar o mщtodo create_nfse ou create_nfe_55. Utilizar esta variсvel para o mщtodo consult_nfe_55, consult_nfse, delete_nfe_55, delete_nfse, update_nfe_55, update_nfse
		'EXTERNAL_ID' => '89678678', // ID externo do documento que serс cancelado		
	));
	
	$retorno = send_data($fields);//Chamada do mщtodo de envio, retorna os dados em array
}

//Exclui uma nota fiscal de serviчo que ainda nуo foi autorizada
function delete_nfse()
{
	$fields = json_encode(array(
		'API_KEY' => 'SUA CHAVE DE API',//Sua chave de acesso API_KEY. Para obter a chave, acesse o menu Configuraчѕes > Empresas. Cada empresa terс sua chave de comunicaчуo com a API
		'METHOD' => 'delete_nfse',//Mщtodo a ser utilizado
		'DOCUMENT_ID' => '4ed0f528ded523d2681e3f9688d63d26',//Cѓdigo retornado pelo sistema apѓs utilizar o mщtodo create_nfse ou create_nfe_55. Utilizar esta variсvel para o mщtodo consult_nfe_55, consult_nfse, delete_nfe_55, delete_nfse, update_nfe_55, update_nfse
		'EXTERNAL_ID' => '89678678', // ID externo do documento que serс removido		
	));
	
	$retorno = send_data($fields);//Chamada do mщtodo de envio, retorna os dados em array
}

//Consulta se uma cidade estс atendida pela nossa API
function cidades_atendidas()
{
	$fields = json_encode(array(
		'API_KEY' => 'SUA CHAVE DE API',//Sua chave de acesso API_KEY. Para obter a chave, acesse o menu Configuraчѕes > Empresas. Cada empresa terс sua chave de comunicaчуo com a API
		'METHOD' => 'cidades_atendidas',//Mщtodo a ser utilizado
		'FILTER' => array(
			'STATE' => 'MG', // Sigla do estado
			'CITY' => 'Belo Horizonte', // Sigla da cidade
		)
	));
	
	$retorno = send_data($fields);//Chamada do mщtodo de envio, retorna os dados em array
}

//Cria uma nota fiscal de produto
function create_nfe_55()
{
	$fields = json_encode(array(
		'API_KEY' => 'SUA CHAVE DE API',//Sua chave de acesso API_KEY. Para obter a chave, acesse o menu Configuraчѕes > Empresas. Cada empresa terс sua chave de comunicaчуo com a API
		'METHOD' => 'create_nfe_55',//Mщtodo a ser utilizado
		
		'DESTINATION_NAME' => 'Jose Maria da Silva',// Nome completo do cliente
		'DESTINATION_TAXID' => '35281474588',//CPF ou CNPJ, somente nњmeros
		'DESTINATION_IE' => '',//Inscriчуo Estadual (opcional), somente nњmeros
		'DESTINATION_IM' => '',//Inscriчуo Municipal (opcional), somente nњmeros
		'DESTINATION_TAXTYPE' => 'F',//F = Fэsica, J = Jurэdica, E = Estrangeiro
		'DESTINATION_STREET' => 'Rua de teste',//Rua do cliente
		'DESTINATION_NUMBER' => '123',//Nњmero
		'DESTINATION_COMPLEMENT' => 'Prѓximo a escola municipal floriano peixoto',//Complemento
		'DESTINATION_DISTRICT' => 'Centro',//Bairro
		'DESTINATION_CITY' => 'Belo Horizonte',//Cidade, informar corretamente o nome da cidade sem abreviaчѕes
		'DESTINATION_UF' => 'MG',//Sigla do estado
		'DESTINATION_ZIPCODE' => '35560000',//CEP, somente nњmeros
		'DESTINATION_PHONE' => '31999998888',//Telefone do cliente (opcional), somente nњmeros
		'DESTINATION_EMAIL'	=> 'test@test.com',//E-mail do cliente (opcional)
		
		'DESTINATION_EMAIL_SEND' => array(
			'1'	=> array(
				'EMAIL' => 'mail1@example.com',
			),
			
			'2'	=> array(
				'EMAIL' => 'mail2@example.com'
			)
		),//e-mail(s) que serс enviado a nota depois de emitida (opcional).
		
		'DOCUMENT_BASEVALUE' => '100.00',//Valor total da nota fiscal. Utilizar ponto para separar as casas decimais
		'DOCUMENT_DESCRIPTION' => 'Prestaчуo de Serviчo em consultoria',//Descriчуo da nota fiscal (obrigatѓrio somente para o mщtodo create_nfse e update_nfse)
		'DOCUMENT_CNAE' => '8599604', //CNAE, somente nњmeros (opcional), se nуo informado ou informado invсlido serс utilizado o padrуo das configuraчѕes da empresa 
		
		//Produto(s). Enviar parтmetro somente quando utilizar o mщtodo create_nfe_55 ou update_nfe_55
		'DOCUMENT_PRODUCT' => array(
			'1' => array(
				'DOCUMENT_PRODUCT_COD' => '190520367911', // Cѓd do produto
				'DOCUMENT_PRODUCT_NAME' => 'Camisa Polo', // Nome do produto
				'DOCUMENT_PRODUCT_QTD' => '2', //Quantidade de itens,
				'DOCUMENT_PRODUCT_UNITARY_VALUE' => '5.00',//Valor unitсrio do produto, utilizar ponto para separar as casas decimais
				'DOCUMENT_PRODUCT_NCM' => '62069000',//Cѓdigo NCM do produto, somente nњmeros. (Opcional), se nуo informado serс utilizado o padrуo das configuraчѕes da empresa
				'DOCUMENT_PRODUCT_CFOP' => '5101',//Cѓdigo CFOP a ser utilizado, somente nњmeros. (Opcional), se nуo informado serс utilizado o padrуo das configuraчѕes da empresa
			),
			'2' => array(
				'DOCUMENT_PRODUCT_COD' => '0020970', // Cѓd do produto
				'DOCUMENT_PRODUCT_NAME' => 'Oculos Feminino', // Nome do produto
				'DOCUMENT_PRODUCT_QTD' => '1', //Quantidade de itens,
				'DOCUMENT_PRODUCT_UNITARY_VALUE' => '90.00',//Valor unitсrio do produto, utilizar ponto para separar as casas decimais
				'DOCUMENT_PRODUCT_NCM' => '62069000',//Cѓdigo NCM do produto, somente nњmeros. (Opcional), se nуo informado serс utilizado o padrуo das configuraчѕes da empresa
				'DOCUMENT_PRODUCT_CFOP' => '5101',//Cѓdigo CFOP a ser utilizado, somente nњmeros. (Opcional), se nуo informado serс utilizado o padrуo das configuraчѕes da empresa
			),
		),
	
		//Frete (opcional). Enviar parтmetro somente quando utilizar o mщtodo create_nfe_55 ou update_nfe_55. Caso nуo seja informado serс utilizado o padrуo 9 = Sem Frete
		'DOCUMENT_FRETE' => array(
			'DOCUMENT_FRETE_MOD' => '0', // 0 = Por conta do emitente, 1 = Por conta do destinatсrio / remetente, 2 = Por conta de terceiros, 9 = Sem frete
			'DOCUMENT_FRETE_VALUE' => '100.00',//Valor total do frete. Utilizar ponto para separar as casas decimais
	
			//Transportadora (opcional). Enviar parтmetro somente quando utilizar o mщtodo create_nfe_55 ou update_nfe_55 e quando a variсvel DOCUMENT_MOD_FRETE for diferente de 9.
			'DOCUMENT_FRETE_TRANSPORTADORA' => array(
				'DOCUMENT_FRETE_TRANSPORTADORA_NAME' => 'Empresa Brasileira de Correios e Telщgrafos', // Nome ou Razуo Social da transportadora
				'DOCUMENT_FRETE_TRANSPORTADORA_TAXID' => '34028316002742', //CPF ou CNPJ, somente nњmeros
				'DOCUMENT_FRETE_TRANSPORTADORA_IE' => '12345678', //Inscriчуo Estadual, somente nњmeros
				'DOCUMENT_FRETE_TRANSPORTADORA_STREET' => 'Rua de teste',//Rua da transportadora
				'DOCUMENT_FRETE_TRANSPORTADORA_NUMBER' => '123',//Nњmero
				'DOCUMENT_FRETE_TRANSPORTADORA_DISTRICT' => 'Centro',//Bairro
				'DOCUMENT_FRETE_TRANSPORTADORA_CITY' => 'Belo Horizonte',//Cidade, informar corretamente o nome da cidade sem abreviaчѕes
				'DOCUMENT_FRETE_TRANSPORTADORA_UF' => 'MG',//Sigla do estado
			),
			
			//Veэculo (opcional). Enviar parтmetro somente quando utilizar o mщtodo create_nfe_55 ou update_nfe_55.
			'DOCUMENT_FRETE_VEICULO' => array(
				'DOCUMENT_FRETE_VEICULO_PLACA' => 'ZZZ1100', // Placa do veэculo
				'DOCUMENT_FRETE_VEICULO_UF' => 'MG', //Sigla do estado
			)		
		),

		'EXTERNAL_ID' => '89678678', // ID externo do documento que serс enviado		
	));
	
	$retorno = send_data($fields);//Chamada do mщtodo de envio, retorna os dados em array
}

//Atualiza uma nota fiscal de produto jс criada e nуo transmitida
function update_nfe_55()
{
	$fields = json_encode(array(
		'API_KEY' => 'SUA CHAVE DE API',//Sua chave de acesso API_KEY. Para obter a chave, acesse o menu Configuraчѕes > Empresas. Cada empresa terс sua chave de comunicaчуo com a API
		'METHOD' => 'update_nfe_55',//Mщtodo a ser utilizado
		
		'DESTINATION_NAME' => 'Jose Maria da Silva',// Nome completo do cliente
		'DESTINATION_TAXID' => '35281474588',//CPF ou CNPJ, somente nњmeros
		'DESTINATION_IE' => '',//Inscriчуo Estadual (opcional), somente nњmeros
		'DESTINATION_IM' => '',//Inscriчуo Municipal (opcional), somente nњmeros
		'DESTINATION_TAXTYPE' => 'F',//F = Fэsica, J = Jurэdica, E = Estrangeiro
		'DESTINATION_STREET' => 'Rua de teste',//Rua do cliente
		'DESTINATION_NUMBER' => '123',//Nњmero
		'DESTINATION_COMPLEMENT' => 'Prѓximo a escola municipal floriano peixoto',//Complemento
		'DESTINATION_DISTRICT' => 'Centro',//Bairro
		'DESTINATION_CITY' => 'Belo Horizonte',//Cidade, informar corretamente o nome da cidade sem abreviaчѕes
		'DESTINATION_UF' => 'MG',//Sigla do estado
		'DESTINATION_ZIPCODE' => '35560000',//CEP, somente nњmeros
		'DESTINATION_PHONE' => '31999998888',//Telefone do cliente (opcional), somente nњmeros
		'DESTINATION_EMAIL'	=> 'test@test.com',//E-mail do cliente (opcional)
		
		'DESTINATION_EMAIL_SEND' => array(
			'1'	=> array(
				'EMAIL' => 'mail1@example.com',
			),
			
			'2'	=> array(
				'EMAIL' => 'mail2@example.com'
			)
		),//e-mail(s) que serс enviado a nota depois de emitida (opcional).
		
		'DOCUMENT_BASEVALUE' => '100.00',//Valor total da nota fiscal. Utilizar ponto para separar as casas decimais
		'DOCUMENT_DESCRIPTION' => 'Prestaчуo de Serviчo em consultoria',//Descriчуo da nota fiscal (obrigatѓrio somente para o mщtodo create_nfse e update_nfse)
		'DOCUMENT_CNAE' => '8599604', //CNAE, somente nњmeros (opcional), se nуo informado ou informado invсlido serс utilizado o padrуo das configuraчѕes da empresa 
		
		//Produto(s). Enviar parтmetro somente quando utilizar o mщtodo create_nfe_55 ou update_nfe_55
		'DOCUMENT_PRODUCT' => array(
			'1' => array(
				'DOCUMENT_PRODUCT_COD' => '190520367911', // Cѓd do produto
				'DOCUMENT_PRODUCT_NAME' => 'Camisa Polo', // Nome do produto
				'DOCUMENT_PRODUCT_QTD' => '2', //Quantidade de itens,
				'DOCUMENT_PRODUCT_UNITARY_VALUE' => '5.00',//Valor unitсrio do produto, utilizar ponto para separar as casas decimais
				'DOCUMENT_PRODUCT_NCM' => '62069000',//Cѓdigo NCM do produto, somente nњmeros. (Opcional), se nуo informado serс utilizado o padrуo das configuraчѕes da empresa
				'DOCUMENT_PRODUCT_CFOP' => '5101',//Cѓdigo CFOP a ser utilizado, somente nњmeros. (Opcional), se nуo informado serс utilizado o padrуo das configuraчѕes da empresa
			),
			'2' => array(
				'DOCUMENT_PRODUCT_COD' => '0020970', // Cѓd do produto
				'DOCUMENT_PRODUCT_NAME' => 'Oculos Feminino', // Nome do produto
				'DOCUMENT_PRODUCT_QTD' => '1', //Quantidade de itens,
				'DOCUMENT_PRODUCT_UNITARY_VALUE' => '90.00',//Valor unitсrio do produto, utilizar ponto para separar as casas decimais
				'DOCUMENT_PRODUCT_NCM' => '62069000',//Cѓdigo NCM do produto, somente nњmeros. (Opcional), se nуo informado serс utilizado o padrуo das configuraчѕes da empresa
				'DOCUMENT_PRODUCT_CFOP' => '5101',//Cѓdigo CFOP a ser utilizado, somente nњmeros. (Opcional), se nуo informado serс utilizado o padrуo das configuraчѕes da empresa
			),
		),
	
		//Frete (opcional). Enviar parтmetro somente quando utilizar o mщtodo create_nfe_55 ou update_nfe_55. Caso nуo seja informado serс utilizado o padrуo 9 = Sem Frete
		'DOCUMENT_FRETE' => array(
			'DOCUMENT_FRETE_MOD' => '0', // 0 = Por conta do emitente, 1 = Por conta do destinatсrio / remetente, 2 = Por conta de terceiros, 9 = Sem frete
			'DOCUMENT_FRETE_VALUE' => '100.00',//Valor total do frete. Utilizar ponto para separar as casas decimais
	
			//Transportadora (opcional). Enviar parтmetro somente quando utilizar o mщtodo create_nfe_55 ou update_nfe_55 e quando a variсvel DOCUMENT_MOD_FRETE for diferente de 9.
			'DOCUMENT_FRETE_TRANSPORTADORA' => array(
				'DOCUMENT_FRETE_TRANSPORTADORA_NAME' => 'Empresa Brasileira de Correios e Telщgrafos', // Nome ou Razуo Social da transportadora
				'DOCUMENT_FRETE_TRANSPORTADORA_TAXID' => '34028316002742', //CPF ou CNPJ, somente nњmeros
				'DOCUMENT_FRETE_TRANSPORTADORA_IE' => '12345678', //Inscriчуo Estadual, somente nњmeros
				'DOCUMENT_FRETE_TRANSPORTADORA_STREET' => 'Rua de teste',//Rua da transportadora
				'DOCUMENT_FRETE_TRANSPORTADORA_NUMBER' => '123',//Nњmero
				'DOCUMENT_FRETE_TRANSPORTADORA_DISTRICT' => 'Centro',//Bairro
				'DOCUMENT_FRETE_TRANSPORTADORA_CITY' => 'Belo Horizonte',//Cidade, informar corretamente o nome da cidade sem abreviaчѕes
				'DOCUMENT_FRETE_TRANSPORTADORA_UF' => 'MG',//Sigla do estado
			),
			
			//Veэculo (opcional). Enviar parтmetro somente quando utilizar o mщtodo create_nfe_55 ou update_nfe_55.
			'DOCUMENT_FRETE_VEICULO' => array(
				'DOCUMENT_FRETE_VEICULO_PLACA' => 'ZZZ1100', // Placa do veэculo
				'DOCUMENT_FRETE_VEICULO_UF' => 'MG', //Sigla do estado
			)		
		),
		
		'DOCUMENT_ID' => '4ed0f528ded523d2681e3f9688d63d26',//Cѓdigo retornado pelo sistema apѓs utilizar o mщtodo create_nfse ou create_nfe_55. Utilizar esta variсvel para o mщtodo consult_nfe_55, consult_nfse, delete_nfe_55, delete_nfse, update_nfe_55, update_nfse
		'EXTERNAL_ID' => '89678678', // ID externo do documento que serс atualizado		
	));
	
	$retorno = send_data($fields);//Chamada do mщtodo de envio, retorna os dados em array
}

//Consulta uma nota fiscal de produto individualmente pelo seu id
function consult_nfe_55()
{
	$fields = json_encode(array(
		'API_KEY' => 'SUA CHAVE DE API',//Sua chave de acesso API_KEY. Para obter a chave, acesse o menu Configuraчѕes > Empresas. Cada empresa terс sua chave de comunicaчуo com a API
		'METHOD' => 'consult_nfe_55',//Mщtodo a ser utilizado
		'DOCUMENT_ID' => '4ed0f528ded523d2681e3f9688d63d26',//Cѓdigo retornado pelo sistema apѓs utilizar o mщtodo create_nfse ou create_nfe_55. Utilizar esta variсvel para o mщtodo consult_nfe_55, consult_nfse, delete_nfe_55, delete_nfse, update_nfe_55, update_nfse
		'EXTERNAL_ID' => '89678678', // ID externo do documento que serс consultado		
	));
	
	$retorno = send_data($fields);//Chamada do mщtodo de envio, retorna os dados em array
}

//Consulta vсrias notas fiscais de produto de acordo com os filtros configurados
function consult_all_nfe_55()
{
	$fields = json_encode(array(
		'API_KEY' => 'SUA CHAVE DE API',//Sua chave de acesso API_KEY. Para obter a chave, acesse o menu Configuraчѕes > Empresas. Cada empresa terс sua chave de comunicaчуo com a API
		'METHOD' => 'consult_all_nfe_55',//Mщtodo a ser utilizado
		'FILTER' => array(
			'INITIAL_DATE' => '2017-01-01', // Data Inicial. Campo obrigatѓrio, enviar no formato yyyy-mm-dd
			'FINAL_DATE' => '2017-01-01', // Data Final. Campo obrigatѓrio, enviar no formato yyyy-mm-dd
			'STATUS' => 'Autorizada', // Status da nota fiscal. Campo opcional
			'NUMBER' => '25', // Nњmero da nota fiscal jс autorizada ou cancelada. Campo opcional
			'DOC' => '25', // CPF / CNPJ do cliente. Campo opcional
		)
	));
	
	$retorno = send_data($fields);//Chamada do mщtodo de envio, retorna os dados em array
}

//Cancela uma nota fiscal de produto que ainda nуo foi autorizada
function cancel_nfe_55()
{
	$fields = json_encode(array(
		'API_KEY' => 'SUA CHAVE DE API',//Sua chave de acesso API_KEY. Para obter a chave, acesse o menu Configuraчѕes > Empresas. Cada empresa terс sua chave de comunicaчуo com a API
		'METHOD' => 'cancel_nfe_55',//Mщtodo a ser utilizado
		'REASON' => 'O valor da nota fiscal foi informado incorretamente',//Mѓtivo do cancelamento
		'DOCUMENT_ID' => '4ed0f528ded523d2681e3f9688d63d26',//Cѓdigo retornado pelo sistema apѓs utilizar o mщtodo create_nfse ou create_nfe_55. Utilizar esta variсvel para o mщtodo consult_nfe_55, consult_nfse, delete_nfe_55, delete_nfse, update_nfe_55, update_nfse
		'EXTERNAL_ID' => '89678678', // ID externo do documento que serс cancelado		
	));
	
	$retorno = send_data($fields);//Chamada do mщtodo de envio, retorna os dados em array
}

//Exclui uma nota fiscal de produto que ainda nуo foi autorizada
function delete_nfe_55()
{
	$fields = json_encode(array(
		'API_KEY' => 'SUA CHAVE DE API',//Sua chave de acesso API_KEY. Para obter a chave, acesse o menu Configuraчѕes > Empresas. Cada empresa terс sua chave de comunicaчуo com a API
		'METHOD' => 'delete_nfe_55',//Mщtodo a ser utilizado
		'DOCUMENT_ID' => '4ed0f528ded523d2681e3f9688d63d26',//Cѓdigo retornado pelo sistema apѓs utilizar o mщtodo create_nfse ou create_nfe_55. Utilizar esta variсvel para o mщtodo consult_nfe_55, consult_nfse, delete_nfe_55, delete_nfse, update_nfe_55, update_nfse
		'EXTERNAL_ID' => '89678678', // ID externo do documento que serс removido		
	));
	
	$retorno = send_data($fields);//Chamada do mщtodo de envio, retorna os dados em array
}

//Atualiza a quantidade de itens no estoque
function update_stock()
{
	$fields = json_encode(array(
		'API_KEY' => 'SUA CHAVE DE API',//Sua chave de acesso API_KEY. Para obter a chave, acesse o menu Configuraчѕes > Empresas. Cada empresa terс sua chave de comunicaчуo com a API
		'METHOD' => 'update_stock',//Mщtodo a ser utilizado
		'PRODUCT_COD' => '8655210717228',// Cѓdigo do produto
		'PRODUCT_QTD' => '10',//Quantidade de itens que serуo atualizados. Utilizar o sinal de negativo quando for para deduzir itens do estoque.
	));
	
	$retorno = send_data($fields);//Chamada do mщtodo de envio, retorna os dados em array
}
?>