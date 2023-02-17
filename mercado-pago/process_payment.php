<?php
    require_once '../../profile/vendor/autoload.php';
    
    #output data
$dados = json_decode(file_get_contents('php://input'),true);
    
#Variables
$email=trim(strip_tags($dados['payer']['email']));
$docType=trim(strip_tags($dados['payer']['identification']['type']));
$docNumber=trim(strip_tags($dados['payer']['identification']['number']));
$cardNumber=trim(strip_tags($dados['cardNumber']));
$securityCode=trim(strip_tags($dados['securityCode']));
$cardExpirationMonth=trim(strip_tags($dados['cardExpirationMonth']));
$cardExpirationYear=trim(strip_tags($dados['cardExpirationYear']));
$cardholderName=trim(strip_tags($dados['cardholderName']));
$installments=trim(strip_tags($dados['installments']));
$amount=trim(strip_tags($dados['transaction_amount']));
$issuer_id=trim(strip_tags($dados['issuer_id']));
$description=trim(strip_tags($dados['description']));
$paymentMethodId=trim(strip_tags($dados['payment_method_id']));
$token=trim(strip_tags($dados['token']));
$payment_type = 'credit_card';
    
    MercadoPago\SDK::setAccessToken("APP_USR-2130475008486171-091322-2fb01cf85e8d188cccbd13afc8b2b295-644673221");

    $payment = new MercadoPago\Payment();
    $payment->transaction_amount = (float)$amount;
    $payment->token = $token;
    $payment->description = "teste";
    $payment->installments = (int)$installments;
    $payment->payment_method_id = $paymentMethodId;
    $payment->issuer_id = (int)$issuer_id;

    $payer = new MercadoPago\Payer();
    $payer->email = $email;
    $payer->identification = array(
        "type" => $docType,
        "number" => $docNumber
    );
    $payment->payer = $payer;

    $payment->save();

    $response = array(
        'status' => $payment->status,
        'status_detail' => $payment->status_detail,
        'id' => $payment->id
    );
    echo json_encode($response);

if(($response['status']==='approved')&&($response['status_detail']==='accredited')){
         //$status = 'Pagamento Aprovado';
         
        // $response['status'] = 'Pagamento Aprovado';
        // echo json_encode($response['status']);
         
          echo "<script> alert('Pagamento Aprovado! Parabéns pela compra!'); window.location.href = 'http://www.dropexpress.com.br/checkout-mercado-pago';</script>";
           //echo $responseData['id'];
         exit();
    }else{
        
        $response['status'] = 'Erro no pagamento';
         echo json_encode($response['status']);
    
    switch($responseData['status_detail']) {
    case 'pending_review_manual':
        echo "<script> alert('Pagamento Em processamento! Em breve verifique se deu tudo certo'); window.location.href = 'https://www.zapcupons.com.br/index.php?page=minhaconta';</script>";
        exit();
        break;
    case 'pending_contingency':
          echo "<script> alert('Pagamento Em processamento! Em breve verifique se deu tudo certo'); window.location.href = 'https://www.zapcupons.com.br/index.php?page=minhaconta';</script>";
           exit();
        break;
    case 'cc_rejected_bad_filled_security_code':
        echo "<script> alert('Código de Segurança Incorreto!');</script>";
           exit();
        break;
        case 'cc_rejected_bad_filled_other':
        echo "<script> alert('Revise os dados.');</script>";
           exit();
        break;
        case 'cc_rejected_bad_filled_card_number':
        echo "<script> alert('Revise o número do cartão.');</script>";
           exit();
        break;
        case 'cc_rejected_bad_filled_date':
        echo "<script> alert('Revise a data de vencimento.');</script>";
           exit();
        break;
        case 'cc_rejected_blacklist':
        echo "<script> alert('Não pudemos processar seu pagamento.');</script>";
           exit();
        break;
        case 'cc_rejected_call_for_authorize':
        echo "<script> alert('Valor não autorizado para seu cartão de crédito');</script>";
           exit();
        break;
        case 'cc_rejected_card_error':
        echo "<script> alert('Não conseguimos processar seu pagamento.');</script>";
           exit();
        break;
        case 'cc_rejected_duplicated_payment':
        echo "<script> alert('Você já efetuou um pagamento com esse valor. Caso precise pagar novamente, utilize outro cartão ou outra forma de pagamento.');</script>";
           exit();
        break;
        case 'cc_rejected_insufficient_amount':
        echo "<script> alert('Limite Insuficiente!');</script>";
           exit();
        break;
        case 'cc_rejected_invalid_installments':
        echo "<script> alert('O cartão inserido não processa pagamentos em parcelas.');</script>";
           exit();
        break;
        case 'cc_rejected_max_attempts':
        echo "<script> alert('Você atingiu o limite de tentativas permitido. Escolha outro cartão ou outra forma de pagamento.');</script>";
           exit();
        break;
        case 'cc_rejected_other_reason':
        echo "<script> alert('Seu cartão não processa o pagamento.');</script>";
           exit();
        break;
        
         default:
        echo "<script> alert('Requisição Inválida! Verifique os dados do seu cartão!'); window.location.href = 'https://www.zapcupons.com.br/';</script>";
           exit();
}
    }


/*
#output data
$dados = json_decode(file_get_contents('php://input'),true);
    
#Variables
$email=trim(strip_tags($dados['payer']['email']));
$docType=trim(strip_tags($dados['payer']['identification']['type']));
$docNumber=trim(strip_tags($dados['payer']['identification']['number']));
$cardNumber=trim(strip_tags($dados['cardNumber']));
$securityCode=trim(strip_tags($dados['securityCode']));
$cardExpirationMonth=trim(strip_tags($dados['cardExpirationMonth']));
$cardExpirationYear=trim(strip_tags($dados['cardExpirationYear']));
$cardholderName=trim(strip_tags($dados['cardholderName']));
$installments=trim(strip_tags($dados['installments']));
$amount=trim(strip_tags($dados['transaction_amount']));
$issuer_id=trim(strip_tags($dados['issuer_id']));
$description=trim(strip_tags($dados['description']));
$paymentMethodId=trim(strip_tags($dados['payment_method_id']));
$token=trim(strip_tags($dados['token']));
$payment_type = 'credit_card';


// Post DATA Para mercadopago
 $jayParsedAry = [    
   'transaction_amount' => $amount,
   'token' => $token, 
   'description' => 'Teste de Pagamento',
   'installments' => $installments,
   'payment_method_id' => $paymentMethodId, 
   'issuer_id' => $issuer_id,
    'payer' => [
         'email' => $email
      ]
]; 
         
// Encode de datos para parsarlos por el curl
$result = json_encode($jayParsedAry, JSON_PRETTY_PRINT);

// Declaro la URL del CURL
          $ch = curl_init('https://api.mercadopago.com/v1/payments'); 
		  curl_setopt_array($ch, array(
	      CURLOPT_POST => TRUE, 
	      CURLOPT_RETURNTRANSFER => TRUE, 
	      CURLOPT_SSL_VERIFYPEER => TRUE,
	      CURLOPT_HTTPHEADER => array(
	       'Accept: application/json', 
	       'Content-Type: application/json',
	       //aqui insere o access token 
	       'Authorization: Bearer APP_USR-2130475008486171-091322-2fb01cf85e8d188cccbd13afc8b2b295-644673221' 
	      ), 
	      CURLOPT_POSTFIELDS => $result 
	  	)); 

		// Envío el post  
	  	$response = curl_exec($ch); 
	  	
	  	// Chequeo si hay errores 
	  	if($response === FALSE){ 
	      die(curl_error($ch)); 
	  	} 

	  	// Decodifico la respuesta del curl 
	  	$responseData = json_decode($response, TRUE); 
	  	
	  	$response = array(
            'status' => $responseData['status_detail'],
            'message' => $responseData['status'],
            'id' => $responseData['id']
        );
        echo json_encode($response);
        exit();

 
    //echo $responseData['status'];
    //print_r($jayParsedAry);
    
 $id_pagamento = $responseData['id'];
 $situacao = $responseData['status_detail'];
 $state = $responseData['status'];
 $tipocartao = $responseData['payment_method_id'];
 $tipo = 'CREDITO';
 
 
 
 //atualiza o registro do pedido com código do item
 include('conexao.php');
   $sqlz = "UPDATE `order` SET `pagmp` = '$state', `status_detail` = '$situacao', `pedidoid` = '$id_pagamento', `tipo_pag` = '$tipo', `tipocartao` = '$tipocartao', `origin`='$amount' WHERE `order`.`id` = '$description'";
				if(mysqli_query($con, $sqlz)){
				}  
				
	
	
     if(($responseData['status']==='approved')&&($responseData['status_detail']==='accredited')){
         $status = 'Pagamento Aprovado';
         
         $response['status'] = 'Pagamento Aprovado';
         echo json_encode($response['status']);
         
          //echo "<script> alert('Pagamento Aprovado! Parabéns pela compra!'); window.location.href = 'http://www.zapcupons.com.br/success.php?loja=$nomedaloja&&valor=$amount&&pedido=$description';</script>";
           //echo $responseData['id'];
         exit();
    }else{
        
        $response['status'] = 'Erro no pagamento';
         echo json_encode($response['status']);
    
    switch($responseData['status_detail']) {
    case 'pending_review_manual':
        echo "<script> alert('Pagamento Em processamento! Em breve verifique se deu tudo certo'); window.location.href = 'https://www.zapcupons.com.br/index.php?page=minhaconta';</script>";
        exit();
        break;
    case 'pending_contingency':
          echo "<script> alert('Pagamento Em processamento! Em breve verifique se deu tudo certo'); window.location.href = 'https://www.zapcupons.com.br/index.php?page=minhaconta';</script>";
           exit();
        break;
    case 'cc_rejected_bad_filled_security_code':
        echo "<script> alert('Código de Segurança Incorreto!');</script>";
           exit();
        break;
        case 'cc_rejected_bad_filled_other':
        echo "<script> alert('Revise os dados.');</script>";
           exit();
        break;
        case 'cc_rejected_bad_filled_card_number':
        echo "<script> alert('Revise o número do cartão.');</script>";
           exit();
        break;
        case 'cc_rejected_bad_filled_date':
        echo "<script> alert('Revise a data de vencimento.');</script>";
           exit();
        break;
        case 'cc_rejected_blacklist':
        echo "<script> alert('Não pudemos processar seu pagamento.');</script>";
           exit();
        break;
        case 'cc_rejected_call_for_authorize':
        echo "<script> alert('Valor não autorizado para seu cartão de crédito');</script>";
           exit();
        break;
        case 'cc_rejected_card_error':
        echo "<script> alert('Não conseguimos processar seu pagamento.');</script>";
           exit();
        break;
        case 'cc_rejected_duplicated_payment':
        echo "<script> alert('Você já efetuou um pagamento com esse valor. Caso precise pagar novamente, utilize outro cartão ou outra forma de pagamento.');</script>";
           exit();
        break;
        case 'cc_rejected_insufficient_amount':
        echo "<script> alert('Limite Insuficiente!');</script>";
           exit();
        break;
        case 'cc_rejected_invalid_installments':
        echo "<script> alert('O cartão inserido não processa pagamentos em parcelas.');</script>";
           exit();
        break;
        case 'cc_rejected_max_attempts':
        echo "<script> alert('Você atingiu o limite de tentativas permitido. Escolha outro cartão ou outra forma de pagamento.');</script>";
           exit();
        break;
        case 'cc_rejected_other_reason':
        echo "<script> alert('Seu cartão não processa o pagamento.');</script>";
           exit();
        break;
        
         default:
        echo "<script> alert('Requisição Inválida! Verifique os dados do seu cartão!'); window.location.href = 'https://www.zapcupons.com.br/';</script>";
           exit();
}
    }
    
   
?>

