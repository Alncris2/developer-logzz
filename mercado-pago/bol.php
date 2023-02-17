<?php


$email=filter_input(INPUT_POST,'email',FILTER_VALIDATE_EMAIL);

$docType=filter_input(INPUT_POST,'docTypeBoleto',FILTER_DEFAULT);
$docNumber=filter_input(INPUT_POST,'docNumber',FILTER_DEFAULT);
$amount=100;
$description=filter_input(INPUT_POST,'description',FILTER_DEFAULT);
$paymentMethodId=filter_input(INPUT_POST,'paymentMethodId',FILTER_DEFAULT);

$primeiro_nome=filter_input(INPUT_POST,'nome',FILTER_DEFAULT);
$segundo_nome=filter_input(INPUT_POST,'sobrenome',FILTER_DEFAULT);
$rua=filter_input(INPUT_POST,'rua',FILTER_DEFAULT);
$bairro=filter_input(INPUT_POST,'bairro',FILTER_DEFAULT);
$cidade=filter_input(INPUT_POST,'cidade',FILTER_DEFAULT);
$estado=filter_input(INPUT_POST,'estado',FILTER_DEFAULT);
$cep=filter_input(INPUT_POST,'cep',FILTER_DEFAULT);
//parte que pega os dados do produto e da loja
$id_lojista=filter_input(INPUT_POST,'id_lojista',FILTER_DEFAULT);



$jayParsedAry = [
               
               'transaction_amount' => $amount,
               'payment_method_id' => 'bolbradesco', 
               //'payment_type_id' => $payment_type,
               'token' => $token, 
               'description' =>  "Pedido de Teste",
               'application_fee' => '1',
               'external_reference' => "Teste",
               'notification_url' => "https://www.zapcupons.com.br",

         
   "payer" => [
         "email" => $email, 
         "first_name" => $primeiro_nome,
         "last_name" => $segundo_nome,
         "identification" => [
            "type" => "cpf",
            "number" => $docNumber
             ],
             
         "address" => [
             
              "zip_code" => $cep,
         "street_name" => $rua,
         "street_number" => "0000",
         "neighborhood" => $bairro,
         "city" => $cidade,
         "federal_unit" => $estado
         
             ] 
      ],
    
    ];

     $access_token = "APP_USR-5243212187003185-060402-8ed6f9e71eea85b84716d8ed708ada67-748559036";

    
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
 //$responseData['id'];
 $id_pagamento = $responseData['id'];
 //print_r($jayParsedAry);
//echo "<pre>", print_r($responseData); "</pre>";



 $id_pagamento = $responseData['id'];
 $situacao = $responseData['status_detail'];
 $tipo = "BOLETO";
 
  $state = $responseData['status'];

 //atualiza o registro do pedido com código do item
  
 //echo '<pre>', $responseData['status']; '</pre>';
 
 
 //echo "<pre>", print_r($payment), "</pre>";
 $falha = $responseData['error'];
 if($falha!=''){
           echo "<p align='center' class='alert alert-danger'> Verifique os dados. </p><script> alert('É necessário informar os dados correto.') </script>";
   exit();
 }
 $erro = $responseData['error']['message'];
 if(isset($erro)&&($erro!='')){
     if($erro==="The customer can't be equal to the collector."){
         echo "<p align='center' class='alert alert-danger'> O cliente não pode ser igual ao cobrador. Verifique o Email ou Cpf.</p><script> alert('O cliente não pode ser igual ao cobrador.') </script>";
        exit();
     }
     if($erro==="To generate a registered boleto the following parameters are required: payer.identification.type , payer.identification.number: Offline API Error"){
         echo "<p align='center' class='alert alert-danger'> É necessário informar o número do Documento. </p><script> alert('É necessário informar o número do Documento.') </script>";
        exit();
              }
 }else{
     
    $cpf = $_POST['docNumber'];
    $email = $_POST['email'];
    
    $sql2 = "SELECT * FROM `user` WHERE `user`.`email` = '$email'";
    				$result2 = mysqli_query($con, $sql2);
    				$row2 = mysqli_fetch_array($result2);
                     {
                         $number = "55".$row2['mobile'];
                         $nome = $row2['realname'];
                     }
 
 
 $codigo = $responseData['barcode']['content'];        

 echo "
<script>
   var Bankslip = (function(window, document) {
  'use strict';

  // Return a block by string
  var block = function(string, start, end) {
    return string.slice(start, end);
  }

  var general_dv = function(bankslip) {
    return block(bankslip, 4, 5);
  }

  var currency_info = function(bankslip) {
    return block(bankslip, 3, 4);
  }

  var bank_info = function(bankslip) {
    return block(bankslip, 0, 3);
  }

  var duedate_factor = function(bankslip) {
    return block(bankslip, 5, 9);
  }

  var amount = function(bankslip) {
    return block(bankslip, 9, 19);
  }

  var bankslip_segment_1 = function(bankslip) {
    return append_dv_block([
      bank_info(bankslip),
      currency_info(bankslip),
      block(bankslip, 19, 24)
    ]);
  }

  var bankslip_segment_2 = function(bankslip) {
    return append_dv_block([
      block(bankslip, 24, 34)
    ]);
  }

  var bankslip_segment_3 = function(bankslip) {
    return append_dv_block([
      block(bankslip, 34, 44)
    ]);
  }

  var bankslip_segment_4 = function(bankslip) {
    return [
      duedate_factor(bankslip),
      amount(bankslip)
    ].join('');
  }

  // Receive a string array and return a string appended DV
  var append_dv_block = function(array_data) {
    var str = array_data.join('');
    return str.concat(get_sum_from_sequence(str));
  }

  // Generate multiply sequence eg.: [2, 1, 2]
  function multiply_sequence(len) {
    var bars = new Array();
    var start_dig = 2;

    for (var i = 0; i < len; i++) {
      bars.push(start_dig);
      if (start_dig === 1) {
        start_dig++;
      } else {
        start_dig--;
      }
    }
    return bars;
  }

  // Generate digit verificator
  function get_sum_from_sequence(seq) {
    var bar = [];
    var mseq = multiply_sequence(seq.length).reverse();

    mseq.forEach(function(value, index) {
      bar.push(value * parseInt(seq[index]));
    });

    var string_bar = bar.join('');
    var bar_value = 0;
    bar = Array.from(string_bar);

    var sum = bar.forEach(function(value) {
      bar_value += parseInt(value);
    });

    return (10 - (bar_value % 10));
  }

  var bankslip = function(bankslip_number) {
    return [
      bankslip_segment_1(bankslip_number),
      bankslip_segment_2(bankslip_number),
      bankslip_segment_3(bankslip_number),
      general_dv(bankslip_number),
      bankslip_segment_4(bankslip_number)
    ].join('');
  }

  //Public API
  return {
    parse: bankslip
  }

}(window, document));


var original_number = document.getElementById('codebar').textContent;
document.getElementById('generated_number').value = Bankslip.parse(original_number);
					</script>
 <p id='codebar' style='display:none';>$codigo</p>
 <input type='text' reandoly id='generated_number' class='form-control' style='margin-bottom: 5px; width:40%; text-align:center; display: revert; margin-top: 10px;'></p>
  <button id='copybol' class='btn btn-default btn-block' style='border-radius: 7px; background:#0f4867; color:white; width:40%; display: revert; height: 55px;'>  <img src='../../img/PIX_Colar_Azul.png' width='28' height='28' style='margin-top:-5px'/> Copiar Código de Barras</button><br>
 <a href='".$responseData['transaction_details']['external_resource_url']."' style='background: #9e0a22; background: #9e0a22 !important; color:white; width: 40%;' class='buy-button btn btn-danger' target='_blank' id='btn_open'><i class='fa fa-print' aria-hidden='true' style='margin-top:3px;'></i> 
 Abrir Boleto Bancário</a> <br>
 <script>
   $('#copybol').on('click', function(){
        //Visto que o 'copy' copia o texto que estiver selecionado, talvez você queira colocar seu valor em um txt escondido
    $('#generated_number').select();
    alert('Código Copiado!');
    try {
            var ok = document.execCommand('copy');
            if (ok) { pixcopiado(); }
        } catch (e) {
        mensagem(e)
    }
});
 </script>
 ";
 
}
 ?>