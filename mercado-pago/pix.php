<?php
 
#Variables
$email=filter_input(INPUT_POST,'email_pix',FILTER_VALIDATE_EMAIL);
$cardNumber=filter_input(INPUT_POST,'cardNumber',FILTER_DEFAULT);
$securityCode=filter_input(INPUT_POST,'securityCode',FILTER_DEFAULT);
$cardExpirationMonth=filter_input(INPUT_POST,'cardExpirationMonth',FILTER_DEFAULT);
$cardExpirationYear=filter_input(INPUT_POST,'cardExpirationYear',FILTER_DEFAULT);
$cardholderName=filter_input(INPUT_POST,'cardholderName',FILTER_DEFAULT);
$docType=filter_input(INPUT_POST,'docType',FILTER_DEFAULT);
$docNumber=filter_input(INPUT_POST,'docNumber_pix',FILTER_DEFAULT);
$installments=filter_input(INPUT_POST,'installments',FILTER_DEFAULT);
$amount=2;
$description=filter_input(INPUT_POST,'description_pix',FILTER_DEFAULT);
$paymentMethodId=filter_input(INPUT_POST,'paymentMethodId_pix',FILTER_DEFAULT);
$token=filter_input(INPUT_POST,'token',FILTER_DEFAULT);

//parte que pega os dados do produto e da loja
$id_produtos=filter_input(INPUT_POST,'id_produto',FILTER_DEFAULT);
$id_lojista=filter_input(INPUT_POST,'id_lojista_pix',FILTER_DEFAULT);

//conversão
/*$fmt = new NumberFormatter( 'de_DE', NumberFormatter::CURRENCY );
$num = "$amount\xc2\xa0$";
$amount = $fmt->parseCurrency($num, $curr);
*/
//select no banco 
$jayParsedAry = [
               'transaction_amount' => $amount,
               'payment_method_id' => $paymentMethodId, 
               //'payment_type_id' => $payment_type,
               'token' => $token, 
               'description' =>  $description." Nº do Pedido, na Loja ". $nomedaloja,
               'application_fee' => 1,
               'external_reference' => "Nº do Pedido: ".$description.", na Loja ". $nomedaloja,
               'notification_url' => "https://www.zapcupons.com.br/notificacaomp.php?order=$description",
               //'picture_url' => "https://www.zapcupons.com.br/include/logo/logo.png",
    
   "payer" => [
         "email" => $email,
         
         "identification" => [
            "type" => "cpf",
            "number" => $docNumber
             ],
             
         "address" => [
             
              "zip_code" => "000000",
         "street_name" => "Teste",
         "street_number" => "0000",
         "neighborhood" => "Bonfim",
         "city" => "Teste",
         "federal_unit" => "00"
         
             ] 
      ],
    
    ];
    
    $access_token = "APP_USR-2130475008486171-091322-2fb01cf85e8d188cccbd13afc8b2b295-644673221";

    
    $result = json_encode ($jayParsedAry, JSON_PRETTY_PRINT);
    
    $ch = curl_init('https://api.mercadopago.com/v1/payments'); 
		curl_setopt_array($ch, array(
	      CURLOPT_POST => TRUE, 
	      CURLOPT_RETURNTRANSFER => TRUE, 
	      CURLOPT_SSL_VERIFYPEER => TRUE,
	      CURLOPT_HTTPHEADER => array(
	       'Accept: application/json', 
	       'Content-Type: application/json',
	       'Authorization: Bearer '.$access_token.''
	      ), 
	      CURLOPT_POSTFIELDS => $result 
	  	)); 

	  	$response = curl_exec($ch); 

	  	if($response === FALSE){ 
	      die(curl_error($ch)); 
	  	} 

	  	$responseData = json_decode($response, TRUE); 
    
    echo "<pre>", print_r($responseData); "</pre>";
    //echo "Erro:". $responseData['message'];
    //echo "<br> Metodo: ".$jayParsedAry['payment_method_id'];
    //print_r($jayParsedAry);
 
    //echo $responseData['id'];
    //echo $responseData['status_detail'];
    //echo $description;
 $state = $responseData['status'];     
 $id_pagamento = $responseData['id'];
 $situacao = $responseData['status_detail'];
 $tipo = "PIX";
 

 //atualiza o registro do pedido com código do item
 include('conexao.php');
   
   $sqlz = "UPDATE `order` SET `pagmp` = '$state', `status_detail` = '$situacao', `pedidoid` = '$id_pagamento', `tipo_pag`='$tipo', `origin`='$amount3' WHERE `order`.`id` = '$description'";
				if(mysqli_query($con, $sqlz)){
				}  
     
 $erro = $responseData['error']['message'];
 if(isset($erro)&&($erro!='')){
     if($erro==="The customer can't be equal to the collector."){
         echo "<p align='center' class='alert alert-danger'> O cliente não pode ser igual ao cobrador, verifique o email e outros dados. </p><script> alert('O cliente não pode ser igual ao cobrador, verifique o email e outros dados.') </script>";
     }
     if($erro==="To generate a registered boleto the following parameters are required: payer.identification.type , payer.identification.number: Offline API Error"){
         echo "<p align='center' class='alert alert-danger'> É necessário informar o número do Documento. </p><script> alert('É necessário informar o número do Documento.') </script>";
     }
       echo "<p align='center' class='alert alert-danger'> Verifique seus dados. </p><script> alert('Verifique seus dados.') </script>";
   
 }else{
 echo "<p align='center'>
  <style>
  @media screen and (min-width: 900px){
  #esquerda{
      margin-left: 200px;
  }
  #direita{
    margin-top: 42px !important;
    margin-bottom: 20px;
    margin-left: -281px;
  }
  #chavepix{
      margin-bottom: 5px;
    width: 50%;
    margin-left: 120px;
    margin-top: -10px;
  }
  #copypix{
      width: 50%;
    margin-left: 120px;
    margin-top: -10px;
  }
   #atualizar{
      width: 50%;
    margin-left: 120px;
    margin-top: -10px;
  }
  
}
@media screen and (max-width: 360px){
#img_pix{
    width:185px !important;
    height:165px !important;
    margin-left:3px !important;
}
}
  </style>
  <div class='row' style='text-align: center; background: white;'>
  <div class='col-md-6' id='esquerda' style='position:initial;'><img src='data:image/png;base64, ".$responseData['point_of_interaction']['transaction_data']['qr_code_base64']."' id='Pix_ZapCupons' class='img-rounded' alt='Pix ZapCupons' style='width:304px; height:300px; margin-left:-19px;' id='img_pix'>
  
   <input type='hidden' name='pid' id='pid' value='".$id_pagamento."'/>
   <div class='input-group' style='width:100%; background: white;'>
   <input type='text' class='form-control' style='margin-bottom: 5px;' id='chavepix' value='".$responseData['point_of_interaction']['transaction_data']['qr_code']."' readonly>
   <button id='copypix' class='btn btn-default btn-block' style='border-radius: 7px; background:#00abe0; color:white;'><img src='../../img/PIX_Colar_Azul.png' width='28' height='28' style='margin-top:-5px'/> Copiar Chave Pix</button>
   <button id='atualizar' class='btn btn-default btn-block' style='border-radius: 7px; height: 40px; margin-top: 2px; background:#00668b; color:white;'><i class='fa fa-refresh'></i> Atualizar QRCODE</button>
   </p>
    </div></p>
  </div>
  
 
  <div class='col-md-6' style='margin-top: 22px; margin-bottom: 20px;' id='direita'>
  <p><img src='../img/PIX_Celular_Azul.png' width='35' height='35' alt='pix' style=' border-radius: 15px; margin-left: -206px;'></p>
            <p style='color: grey;margin:0px; margin-top:-44px; margin-left: 45px; font-size: 15px;'>
                <span style='color:red; margin-left: -12px; font-size: 15px;'>1. </span>&nbsp;&nbsp;
                
                Abra o aplicatico do seu banco <br> 
            ou instiruição financeira e entre <br>
            no ambiente Pix;</p>
            <p><img src='../img/PIX_QRCODE_Azul.png' style='width:35px; height: 35px; margin-left: -206px;'>
            </p>
            
            <p style='color: grey;margin:0px; margin-top:-36px; margin-left: 45px; font-size: 15px;'>
                <span style='color:red; margin-left: -31px; font-size: 15px;'>2. </span>&nbsp;
                Escolha a opcão pagar com <br> QR CODE e escaneie o código ao lado.</p>
            
            <p><img src='../img/PIX_Confirmação_Azul.png' style='width:35px; height: 35px; margin-left: -206px;'></p>
            
            <p style='color: grey;margin:0px; margin-top:-36px; margin-left: 45px; font-size: 15px;'>
            <span style='color:red; margin-left: -30px; font-size: 15px;'>3. </span>&nbsp;
                Confirme as informações  e <br>  finalize a compra. <br> 
            </p>
            
            <p><img src='../img/PIX_Confirmação_Pagamento_Azul.png' style='width:35px; height: 35px; margin-left: -206px;'></p>
            
            <p style='color: grey;margin:0px; margin-top:-36px; margin-left: 45px; font-size: 15px;'>
            <span style='color:red; font-size: 15px;'>4. </span>&nbsp;
                Assim que finalizar o pagamento <br> clique no <span style='color:red;'>botão vermelho</span></p>
                <br>
            
  
  <button id='confirma_pag' class='btn btn-danger' style='height: 40px; color:white;'><i class='fa fa-spinner fa-pulse'></i> Confirme o Pagamento</button>
   </div>
   
</div>
 <br>
    <script>
    $('#copypix').on('click', function(){
      
        //Visto que o 'copy' copia o texto que estiver selecionado, talvez você queira colocar seu valor em um txt escondido
    $('#chavepix').select();
    alert('Pix Copiado');
    try {
            var ok = document.execCommand('copy');
            if (ok) { pixcopiado(); }
        } catch (e) {
        mensagem(e)
    }
});
    </script>
    <script>
      
    $('#confirma_pag').on('click', function(){
        var idpag = '$description';
           $.ajax({ 
                url: '../../pix_confirma.php', 
                method: 'POST', 
                data:{idpag:idpag}, 
                success: function(dados) { 
               if(dados==='Pagamento Aprovado'){
                    window.location.href = 'http://www.site.com.br/success.php?loja=$nomedaloja&&valor=$amount&&pedido=$description';
               }else{
                   alert(dados);
               } 
                } 
           })
    })
</script>
    ";
    
 }
 
 
  ?>