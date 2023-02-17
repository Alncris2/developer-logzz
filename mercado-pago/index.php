<html>
    <head>
        <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    </head>
    <body>
										<div class="visible-desktop my-cart-footer-wrapper clearfix"> 
											<div class="my-cart-gift-box"> 
												 <h3>Endereço de Entrega</h3>
													<div class="endentrega" style="margin-top: -15px;">  
											
										</div>  
												
										<ul class="list-gift-wrapper"></ul>
										</div> 
											
										<div class="my-cart-gift-box"> 
										<h3 style="color:#b41919; font-size:16px; margin-bottom: 6px; text-align:center;">Selecione Uma Forma de pagamento:</h3>
    <div class="btn-group" style="width: 100%;">
    <p align="center">
       
    <label class="btn btn-default pagamento" id="pix" style="height: 56px;"><input type="radio" name="pagamento" id="pix" value="pix" required>  Pagar com Pix</label>
    <label class="custom-control-label" for="customCheck"></label>
    
    <label class="btn btn-default pagamento" id="cartao" style="height: 56px;"><input type="radio" name="pagamento" id="cartao" value="cartao">  Cartão Crédito </label>
    <label class="custom-control-label" for="customCheck"></label>
     
    <label class="btn btn-default pagamento" id="boleto" style="height: 56px;"><input type="radio" name="pagamento" id="boleto" value="boleto">  Boleto Bancário</label>
    <label class="custom-control-label" for="customCheck"></label>
    
    
    </p></div>
  </div>
						<!-- aba pagamento -->
						<script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-masker/1.1.0/vanilla-masker.min.js"></script>
						<script src="https://cdnjs.cloudflare.com/ajax/libs/imask/3.4.0/imask.min.js"></script>
						
					
		
			<!-- FORMATANDO O VALOR -->
<?php
$fmt = new NumberFormatter( 'de_DE', NumberFormatter::CURRENCY );
$num = "$totalprice\xc2\xa0$";
$total = $fmt->parseCurrency($num, $curr);
;
?>

		   <!-- LAYOUT CARTÃO --> 
           <span id="payment-status"></span>
           <div id="retorno_cartao"></div> 
           <form id="form-checkout" style="width:50%; margin:0 auto;">
           <input type="hidden" name="transactionAmountCartao" id="checkout_transactionAmount" value="10.00"/>
           <input type="hidden" name="paymentMethodId" id="checkout_paymentMethodId" value="credit_card"/>
           <input type="hidden" name="description" id="checkout_description"/>
           <select  name="issuer" id="checkout_issuer" style="display:none;" required="required" class="form-control"></select>
           <select class="docTypeCartao" id="checkout_identificationType" name="identificationType" type="text"></select> 
           
           <input type="tel" placeholder="Número" name="cardNumber" id="checkout_cardNumber" required="required" autocomplete="off" />
           <br>
            <input type="text" placeholder="Nome" name="cardholderName" id="checkout_cardholderName" required="required"/>
           <br>
           
         <input type="tel"  name="identificationNumber" id="checkout_identificationNumber" maxlength="11"  required="required" placeholder="CPF" /> 
    
 <br>
 <input type="text" id="checkout_cardholderEmail" name="checkout_cardholderEmail" required><br>
        <input type="tel" name="cardExpirationMonth" id="checkout_cardExpirationMonth" placeholder="Mes" required="required">
     <br>
  <input type="tel" placeholder="Ano" name="cardExpirationYear" id="checkout_cardExpirationYear" required="required"  >
                  
      <input type="tel" name="securityCode" id="checkout_securityCode"  required="required" maxlength="4" placeholder="Código de Segurança"  autocomplete="off" />
       <br>
                <select type="text" name="installments" id="checkout_installments" required="required"></select>
                <br>
     <button type="submit" class="buy-button btn btn-success" id="pagamento" style="background: #009c4a !important;"> FINALIZAR COMPRA</button>
                
                </div>
                <p align="center">
                <progress value="0" class="progress-bar">Carregando...</progress>
                </p>
               </form> 
            
             </div>
</div>
<?php $email = "gssena@outlook.com";?>

<!-- DEPOIS TIRAR DAQUI E CRIAR UM ARQUIVO JS -->
 <script src="https://sdk.mercadopago.com/js/v2"></script>
 <script>
    //Public Key -- mudar o APP_USR-af87efc0-521d-4de8 pra chave public key do adm 
    const mp = new MercadoPago('APP_USR-968f8b3b-6447-4df2-b2f6-a921f96f4616', {
    locale: 'pt-BR',
    })
    
    //função de pegar os dados do cartão
    function loadCardForm(){
    const productCost = document.getElementById('checkout_transactionAmount').value;
    const productDescription = document.getElementById('checkout_description').innerText;
    
    const cardForm = mp.cardForm({
    amount: productCost,
    autoMount: true,
    form: {
    id: "form-checkout",
    cardholderName: {
      id: "checkout_cardholderName",
      placeholder: "Nome Cartão",
    },
    email: {
      id: "checkout_cardholderEmail",
      placeholder: "Email",
    },
    cardNumber: {
      id: "checkout_cardNumber",
      placeholder: "Número Cartão",
    },
    cardExpirationMonth: {
      id: "checkout_cardExpirationMonth",
      placeholder: "Mês Cartão",
    },
    cardExpirationYear: {
      id: "checkout_cardExpirationYear",
      placeholder: "Ano Cartão",
    },
    securityCode: {
      id: "checkout_securityCode",
      placeholder: "Código Segurança",
    },
    installments: {
      id: "checkout_installments",
      placeholder: "Parcelas",
    },
    identificationType: {
      id: "checkout_identificationType",
      placeholder: "Tipo de documento",
    },
    identificationNumber: {
      id: "checkout_identificationNumber",
      placeholder: "CPF",
    },
    issuer: {
      id: "checkout_issuer",
      placeholder: "Banco emissor",
    },
    paymentMethodId: {
        id: "checkout_paymentMethodId",
        placeholder: "Tipo do Pagamento",
    },
  },
  
    callbacks: {
    onFormMounted: error => {
      if (error) return console.warn("Erro ao montar Formulário: ", error);
      //console.log("Formulário Montado");
      
    },
    
    onFormUnmounted: error => {
                if (error) return console.warn('Form Unmounted handling error: ', error)
                console.log('Form unmounted')
            },
            onIdentificationTypesReceived: (error, identificationTypes) => {
                if (error) return console.warn('identificationTypes handling error: ', error)
                console.log('Identification types available: ', identificationTypes)
            },
       onPaymentMethodsReceived: (error, paymentMethods) => {
                if (error) return console.warn('paymentMethods handling error: ', error)
                console.log('Payment Methods available: ', paymentMethods)
            },
            onInstallmentsReceived: (error, installments) => {
                if (error) return console.warn('installments handling error: ', error)
                console.log('Installments available: ', installments)
            },
            onCardTokenReceived: (error, token) => {
                if (error) return console.warn('Token handling error: ', error)
                console.log('Token available: ', token)
            },
      onSubmit: event => {
      event.preventDefault();
      
      const {
        paymentMethodId: payment_method_id,
        issuerId: issuer_id,
        cardholderEmail: email,
        amount,
        token,
        installments,
        identificationNumber,
        identificationType,
      } = cardForm.getCardFormData();

      fetch("process_payment.php",  {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
       
        body: JSON.stringify({
          token,
          issuer_id,
          payment_method_id,
          transaction_amount: Number(amount),
          installments: Number(installments),
          description: productDescription,
          payer: {
            email:"<?=$email;?>",
            identification: {
              type: identificationType,
              number: identificationNumber,
            },
          },
        }),
      })
        .then(response => {
                    return response.json();
                })
                .then(result => {
                    document.getElementById("payment-status").innerText = result.status;
                    document.getElementById("retorno_cartao").innerText = result.message;
                })
                .catch(error => {
                    console.log("Dados Incorretos\n"+JSON.stringify(error));
                    alert("Dados Incorretos");
                });;
    },
    
    onFetching: (resource) => {
      //console.log("Fetching resource: ", resource);
      
      const progressBar = document.querySelector(".progress-bar");
      progressBar.removeAttribute("value");

      return () => {
        progressBar.setAttribute("value", "0");
      };
    },
  },
});
};
       //chama a função de carregar os dados do cartão
        loadCardForm();

</script>


			
	<!-- LAYOUT PIX -->
         
         <div id="retorno_pix"></div>
    <!-- formulário pix -->
         <form  onsubmit="return false" method="POST" id="pix_form"> 
         <input class="form-control" id="email_pix" name="email_pix" type="hidden" value="gssena@outlook.com" required="required"/>
    <!-- <label for="docType">Tipo de documento</label> -->
         
         <!-- <select class="form-control docTypePix" required="required" id="docType" name="docType" style="display:none;"></select>
         --> <input class="form-control" required="required" class="form-control" value="03575638594" id="docNumber_pix" name="docNumber_pix" type="hidden"/>
         <input type="hidden" name="transactionAmount_pix" id="transactionAmount_pix" value="100" />
         <input type="hidden" name="paymentMethodId_pix" id="paymentMethodId_pix" value="pix"/>
         <input type="hidden" name="description_pix" id="description_pix" value=""/>
         <input type="hidden" name="pid" id="pid" value="2" readonly="readonly">
         <input type="hidden" name="id_lojista_pix" id="id_lojista_pix" value="1" />
         </form> 
         </div>
    <!-- fim do formulário -->
         
  	
			<!-- LAYOUT BOLETO -->
           
            <!-- formulário boleto -->
             <div id="retorno_boleto"></div>
             <form onsubmit="return false" method="POST" id="boleto_form">  
             <input class="form-control" required="required" id="nome" name="nome" type="hidden" value="Gustavo "/>
             <input class="form-control" id="sobrenome" name="sobrenome" type="hidden" value="Sena"/>
             <input class="form-control" id="cep" name="cep" type="hidden" value="44620000"/>
             <input class="form-control" id="rua" name="rua" type="hidden" value="Rua da Nação"/>
             <input class="form-control" id="bairro" name="bairro" type="hidden" value="Salgadinho"/>
             <input class="form-control" id="cidade" name="cidade" type="hidden" value="Baixa Grande"/>
             <input class="form-control" id="estado" name="estado" type="hidden" value="Ba"/>
             <input class="form-control" required="required" id="email" name="email" type="hidden" value="gssena@outlook.com" required="required"/>
             <select class="form-control docTypeBoleto" required="required" id="identificationType" name="identificationType" type="hidden" style="display:none;">
                <option value="CPF">CPF</option>
             </select>
             <input class="form-control" required="required" class="form-control" value="03575638594" id="docNumber" name="docNumber" data-checkout="docNumber" type="hidden"/>
             <input type="hidden" name="transactionAmount" id="transactionAmount" value="100" />
             <input type="hidden" name="paymentMethodId" id="paymentMethodId" value="bolbradesco"/>
             <input type="hidden" name="description" id="description"/>
             <input type="hidden" name="id_lojista" id="id_lojista" value="1" />
             <p align="center">
             <button type="submit" class="buy-button btn" id="btn_boleto" style="background: #9e0a22 !important; color:white; width: 40%;">GERAR BOLETO</button>
             <span id="resposta_boleto"></span>
             </br>
             <span id="compra_segura" style='color:gray; margin-left: 30%;'>Compra Segura  <img src="../../img/BOLETO_Cadeado_Compra_Segura.png" width="12" height="15" alt="segura"/></span>
              </form>
              
              <br>
              <p align="center">
             <button type="submit" class="buy-button btn" id="btn_pix" style="background: green !important; color:white; width: 40%;">GERAR PIX</button>
            </div>
			<!-- FIM BOLETO -->
			</div>
			
			</div>
		<script>
						 $(document).ready(function(){
						     
				       $(document).on('click', '#btn_boleto', function(){
				        $('#btn_boleto').html("Gerando...");
                       $.ajax({
                       url: 'bol.php',
                       method: 'POST',
                       data: $('#boleto_form').serialize(),
                       success:function(data){
                       $('#retorno_boleto').html(data);
                       $('#btn_boleto').html("GERAR NOVAMENTE...");
                       }
                       });
				       });
                       
                    
                      $(document).on('click', '#btn_pix', function(){
                      $('#btn_pix').html("Gerando...");
				        var dados = $('#pix_form').serialize();
				        alert(dados);
                       $.ajax({
                       url: 'pix.php',
                       method: 'POST',
                       data: $('#pix_form').serialize(),
                       success:function(data){
                       $('#retorno_pix').html(data);
                       }
                       });
                    }); 
						 });
						</script>
	
 
			</div> 
		
	</body>
</html>
