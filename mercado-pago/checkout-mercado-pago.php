<?php
/*
require_once ('includes/config.php');

if (isset($_GET['url'])){
  $url = addslashes($_GET['url']);

  $sale_data = $conn->prepare('SELECT * FROM sales WHERE sale_url = :sale_url');
  $sale_data->execute(array('sale_url' => $url));
  
  if ($sale_data->rowCount() != 0){
    while($row = $sale_data->fetch()) {
      $sale_name = $row['sale_name'];
      $sale_quantity = $row['sale_quantity'];
      $sale_price = $row['sale_price'];
      $sale_status = $row['sale_status'];
      $sale_id = $row['sale_id'];
      $product_id = $row['product_id'];
	  @$fb_pixel = $row['sale_fb_pixel'];
	  @$meta_pixel_facebook_api = $row['meta_pixel_facebook_api'];
  } 
}else {
	header ("Location: ../pagina-nao-encontrada");
	exit;
  }
}

$product_data = $conn->prepare('SELECT * FROM products WHERE product_id = :product_id');
$product_data->execute(array('product_id' =>  $product_id));

while($row = $product_data->fetch()) {
  $product_name = $row['product_name'];
  $product_price = $row['product_price'];
  $product_description = $row['product_description'];
  $product_image = $row['product_image'];
  $product_id = $row['product_id'];
  }








// require __DIR__ . '/vendor/autoload.php';

// use FacebookAds\Api;
// use FacebookAds\Logger\CurlLogger;
// use FacebookAds\Object\ServerSide\ActionSource;
// use FacebookAds\Object\ServerSide\Content;
// use FacebookAds\Object\ServerSide\CustomData;
// use FacebookAds\Object\ServerSide\DeliveryCategory;
// use FacebookAds\Object\ServerSide\Event;
// use FacebookAds\Object\ServerSide\EventRequest;
// use FacebookAds\Object\ServerSide\UserData;



// if (isset($meta_pixel_facebook_api) && !(empty($meta_pixel_facebook_api))){

// 			$access_token = $meta_pixel_facebook_api;
// 			$pixel_id = $fb_pixel;

// 			$api = Api::init(null, null, $access_token);
// 			$api->setLogger(new CurlLogger());

// 			$user_data = (new UserData())
// 				->setEmails(array('joe@eg.com'))
// 				->setPhones(array('12345678901', '14251234567'))
// 				// It is recommended to send Client IP and User Agent for Conversions API Events.
// 				->setClientIpAddress($_SERVER['REMOTE_ADDR'])
// 				->setClientUserAgent($_SERVER['HTTP_USER_AGENT'])
// 				->setFbc('fb.1.1554763741205.AbCdEfGhIjKlMnOpQrStUvWxYz1234567890')
// 				->setFbp('fb.1.1558571054389.1098115397');

// 			$content = (new Content())
// 				->setProductId($product_name)
// 				->setQuantity(1)
// 				->setDeliveryCategory(DeliveryCategory::HOME_DELIVERY);

// 			$custom_data = (new CustomData())
// 				->setContents(array($content))
// 				->setCurrency('brl')
// 				->setValue($product_price);

// 			$event = (new Event())
// 				->setEventName('Purchase')
// 				->setEventTime(time())
// 				->setEventSourceUrl('http://localhost/dashboard.dropexpress/checkout/MinoxidilComFatorDeCrescimento')
// 				->setUserData($user_data)
// 				->setCustomData($custom_data)
// 				->setActionSource(ActionSource::WEBSITE);

// 			$events = array();
// 			array_push($events, $event);

// 			$request = (new EventRequest($pixel_id))
// 				->setEvents($events);
// 			$response = $request->execute();
// 			print_r($response);
// }


	
	if(!(empty($fb_pixel))){
		$fb_pixel_purchase = "<script>
		!function(f,b,e,v,n,t,s)
		{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
		n.callMethod.apply(n,arguments):n.queue.push(arguments)};
		if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
		n.queue=[];t=b.createElement(e);t.async=!0;
		t.src=v;s=b.getElementsByTagName(e)[0];
		s.parentNode.insertBefore(t,s)}(window, document,'script',
		'https://connect.facebook.net/en_US/fbevents.js');
		fbq('init', '" . $fb_pixel . "');
		fbq('track', 'PageView');
		fbq('track', 'InitiateCheckout');
	  </script>
	  <noscript>
		<img height='1' width='1' style='display:none' 
			 src='https://www.facebook.com/tr?id=" . $fb_pixel . "&ev=PageView&noscript=1'/>
	  </noscript>";
	}
*/

        session_name(SESSION_NAME);
				@session_start();
				$checkout_transactionAmount = $_SESSION['checkout_transactionAmount'];

$page_title =  "Checkout";
 require_once('includes/layout/fullwidth/fullwidth-header.php');  ?>


<div class="container-fluid" style="">
	<div class="page-titles">
		<ol class="breadcrumb">
			<!-- <li class="breadcrumb-item active"><a href="javascript:void(0)">Checkout</a></li><li class="breadcrumb-item"><a href="javascript:void(0)">
			<?php echo  $sale_name; ?></a></li> -->
		</ol>
	</div>
	<div class="row">
		<div class="col-xl-12" style="max-width: 1000px;margin: 0 auto;">
			<div class="card">
				<div class="card-header d-block">
					<h4 align="center" class="card-title">Finalize Sua Compra!<p>
<br>

             <button type="submit" class="buy-button btn" id="btn_pix" style="background: green !important; color:white; width: 40%;">
              <img src="./images/pix.png" style="width: 40px; height: 40px;">
             GERAR PIX</button>

				
    <button type="submit" class="buy-button btn" id="btn_boleto" style="background: #9e0a22 !important; color:white; width: 40%;">
<img src="./images/boleto.png" style="width: 40px; height: 30px; border-radius: 10px;">
    GERAR BOLETO</button>



<div type="submit" class="buy-button btn" id="btn_boleto" style="background: #ddd !important; color:black; width: 40%;">
<img src="./images/cartao.png" style="width: 40px; height: 30px; border-radius: 10px;">
    CARTÃO ABAIXO</div>            
    	</div>

</h4>
       
    
 <!--   
    <nav id="menu">
  <input type="image" src="../images/cartao.png" style="width: 50px; height: 50px;" onclick="fAbreGuiascartao()" />
  <ul id="guiascartao">
    <li>Guia 1</li>
    <li>Guia 2</li>
    <li>Guia 3</li>
  </ul>
</nav>

<nav id="menu">
  <input type="image" src="../images/boleto.png" style="width: 50px; height: 50px;" onclick="fAbreGuiasboleto()" />
  <ul id="guiasboleto">
    <li>Guia 1</li>
    <li>Guia 2</li>
    <li>Guia 3</li>
  </ul>
</nav>
    
    </p></div>
  </div>
    <script type="text/javascript"> 

      function fAbreGuiaspix() {
  var vGuias = document.getElementById('guiaspix');
  if (vGuias.style.display == "block") { // se vGuias estiver escondido, exiba-o 
    vGuias.style.display = "none";
  } else { // se vGuias for exibido, esconda-o
    vGuias.style.display = "block";
  }
}

function fAbreGuiascartao() {
  var vGuias = document.getElementById('guiascartao');
  if (vGuias.style.display == "block") { // se vGuias estiver escondido, exiba-o 
    vGuias.style.display = "none";
  } else { // se vGuias for exibido, esconda-o
    vGuias.style.display = "block";
  }
}

function fAbreGuiasboleto() {
  var vGuias = document.getElementById('guiasboleto');
  if (vGuias.style.display == "block") { // se vGuias estiver escondido, exiba-o 
    vGuias.style.display = "none";
  } else { // se vGuias for exibido, esconda-o
    vGuias.style.display = "block";
  }
}
      </script>
-->

            <!-- aba pagamento -->
            
          
    
      <!-- FORMATANDO O VALOR -->

       <!-- LAYOUT CARTÃO --> 
           <span id="payment-status"></span>
           <div id="retorno_cartao"></div> 
           <form id="form-checkout" style="width:50%; margin:0 auto;">
           <input type="hidden" name="transactionAmountCartao" id="checkout_transactionAmount" value="<?php echo $checkout_transactionAmount; ?>"/>
           <input type="hidden" name="paymentMethodId" id="checkout_paymentMethodId" value="credit_card"/>
           <input type="hidden" name="description" id="checkout_description"/>
           <select  name="issuer" id="checkout_issuer" style="display:none;" required="required" class="form-control"></select>
           <select class="form-control docTypeCartao" id="checkout_identificationType" name="identificationType" type="text"></select> 
           <br>
           <input type="tel" class="form-control" placeholder="Número do Cartão" name="cardNumber" id="checkout_cardNumber" required="required" autocomplete="off" />
           <br>
            <input type="text" class="form-control" placeholder="Nome no Cartão" name="cardholderName" id="checkout_cardholderName" required="required"/>
           <br>
           
         <input type="tel" class="form-control"  name="identificationNumber" id="checkout_identificationNumber" maxlength="11"  required="required" placeholder="CPF" /> 
    
 <br>
 <input class="form-control" placeholder="Seu E-mail" type="text" id="checkout_cardholderEmail" name="checkout_cardholderEmail" required>
        <br>
        <input class="form-control"  type="tel" name="cardExpirationMonth" id="checkout_cardExpirationMonth" placeholder="Mes" required="required">
     <br>
     <input class="form-control"  type="tel" name="securityCode" id="checkout_securityCode"  required="required" maxlength="4" placeholder="Código de Segurança"  autocomplete="off" />
                 <br> 
      

      <input class="form-control"  type="tel" placeholder="Ano" name="cardExpirationYear" id="checkout_cardExpirationYear" required="required"  >
  
       <br>
                <select class="form-control" type="text" name="installments" id="checkout_installments" required="required"></select>
                <br>
     <button type="submit" class="form-control btn btn-success" id="pagamento" style="background: #009c4a !important; color:white"> FINALIZAR COMPRA</button>
                
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
              <span id="resposta_boleto"></span>
             </br>
              </form>
              
              <br>



              
             <p>
<!--<button class="btn btn-success btn-lg btn-block MT-1" type="submit" name="action">Concluir Pedido</button>
                
<br>

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



                 </div>
          </div>
        </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
    require_once('includes/layout/fullwidth/fullwidth-footer.php');
?>
  

<!--aqui termina o código do checkout mercado pago -->




					<!--			<div class="row mt-1">
									<div class="col-md-8 mb-3">
										<input name="data-pedido" value="Data" class="datepicker-default form-control picker__input" id="data-pedido" readonly="" aria-haspopup="true" aria-expanded="false" aria-readonly="false" aria-owns="datepicker_root" placeholder="dia / mês / ano">
										<!-- <input type="text" class="form-control" id="hidden-date-field" name="hidden-date-field" placeholder="Apartament, Bloco, etc" value=""> -->
						<!--			</div>
									<div class="col-md-4 mb-3">
										<select  class="d-block default-select" name="periodo-pedido" required>
											<option value="" disabled selected>Horário</option>
											<option value="manha">Manhã</option>
											<option value="tarde">Tarde</option>
										</select>
									</div>
									<div class="picker" id="datepicker_root" aria-hidden="true">
										<div class="picker__holder" tabindex="-1">
											<div class="picker__frame">
												<div class="picker__wrap">
													<div class="picker__box">
														<div class="picker__header">
															<div class="picker__month">Setembro</div>
															<div class="picker__year">2021</div>
															<div class="picker__nav--prev" data-nav="-1" role="button" aria-controls="datepicker_table" title="Previous month"></div>
															<div class="picker__nav--next" data-nav="1" role="button" aria-controls="datepicker_table" title="Next month"></div>
														</div>
														<table class="picker__table" id="datepicker_table" role="grid" aria-controls="datepicker" aria-readonly="true">
															<thead>
																<tr>
																	<th class="picker__weekday" scope="col" title="Sunday">Sun</th>
																	<th class="picker__weekday" scope="col" title="Monday">Mon</th>
																	<th class="picker__weekday" scope="col" title="Tuesday">Tue</th>
																	<th class="picker__weekday" scope="col" title="Wednesday">Wed</th>
																	<th class="picker__weekday" scope="col" title="Thursday">Thu</th>
																	<th class="picker__weekday" scope="col" title="Friday">Fri</th>
																	<th class="picker__weekday" scope="col" title="Saturday">Sat</th>
																</tr>
															</thead>
															<tbody>
																<tr>
																	<td role="presentation">
																		<div class="picker__day picker__day--outfocus" data-pick="1630206000000" role="gridcell" aria-label="29 August, 2021">29</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--outfocus" data-pick="1630292400000" role="gridcell" aria-label="30 August, 2021">30</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--outfocus" data-pick="1630378800000" role="gridcell" aria-label="31 August, 2021">31</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--infocus picker__day--today picker__day--highlighted" data-pick="1630465200000" role="gridcell" aria-label="1 Setembro, 2021" aria-activedescendant="true">1</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--infocus" data-pick="1630551600000" role="gridcell" aria-label="2 Setembro, 2021">2</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--infocus" data-pick="1630638000000" role="gridcell" aria-label="3 Setembro, 2021">3</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--infocus" data-pick="1630724400000" role="gridcell" aria-label="4 Setembro, 2021">4</div>
																	</td>
																</tr>
																<tr>
																	<td role="presentation">
																		<div class="picker__day picker__day--infocus" data-pick="1630810800000" role="gridcell" aria-label="5 Setembro, 2021">5</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--infocus" data-pick="1630897200000" role="gridcell" aria-label="6 Setembro, 2021">6</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--infocus" data-pick="1630983600000" role="gridcell" aria-label="7 Setembro, 2021">7</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--infocus" data-pick="1631070000000" role="gridcell" aria-label="8 Setembro, 2021">8</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--infocus" data-pick="1631156400000" role="gridcell" aria-label="9 Setembro, 2021">9</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--infocus" data-pick="1631242800000" role="gridcell" aria-label="10 Setembro, 2021">10</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--infocus" data-pick="1631329200000" role="gridcell" aria-label="11 Setembro, 2021">11</div>
																	</td>
																</tr>
																<tr>
																	<td role="presentation">
																		<div class="picker__day picker__day--infocus" data-pick="1631415600000" role="gridcell" aria-label="12 Setembro, 2021">12</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--infocus" data-pick="1631502000000" role="gridcell" aria-label="13 Setembro, 2021">13</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--infocus" data-pick="1631588400000" role="gridcell" aria-label="14 Setembro, 2021">14</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--infocus" data-pick="1631674800000" role="gridcell" aria-label="15 Setembro, 2021">15</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--infocus" data-pick="1631761200000" role="gridcell" aria-label="16 Setembro, 2021">16</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--infocus" data-pick="1631847600000" role="gridcell" aria-label="17 Setembro, 2021">17</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--infocus" data-pick="1631934000000" role="gridcell" aria-label="18 Setembro, 2021">18</div>
																	</td>
																</tr>
																<tr>
																	<td role="presentation">
																		<div class="picker__day picker__day--infocus" data-pick="1632020400000" role="gridcell" aria-label="19 Setembro, 2021">19</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--infocus" data-pick="1632106800000" role="gridcell" aria-label="20 Setembro, 2021">20</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--infocus" data-pick="1632193200000" role="gridcell" aria-label="21 Setembro, 2021">21</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--infocus" data-pick="1632279600000" role="gridcell" aria-label="22 Setembro, 2021">22</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--infocus" data-pick="1632366000000" role="gridcell" aria-label="23 Setembro, 2021">23</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--infocus" data-pick="1632452400000" role="gridcell" aria-label="24 Setembro, 2021">24</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--infocus" data-pick="1632538800000" role="gridcell" aria-label="25 Setembro, 2021">25</div>
																	</td>
																</tr>
																<tr>
																	<td role="presentation">
																		<div class="picker__day picker__day--infocus" data-pick="1632625200000" role="gridcell" aria-label="26 Setembro, 2021">26</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--infocus" data-pick="1632711600000" role="gridcell" aria-label="27 Setembro, 2021">27</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--infocus" data-pick="1632798000000" role="gridcell" aria-label="28 Setembro, 2021">28</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--infocus" data-pick="1632884400000" role="gridcell" aria-label="29 Setembro, 2021">29</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--infocus" data-pick="1632970800000" role="gridcell" aria-label="30 Setembro, 2021">30</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--outfocus" data-pick="1633057200000" role="gridcell" aria-label="1 Outubro, 2021">1</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--outfocus" data-pick="1633143600000" role="gridcell" aria-label="2 Outubro, 2021">2</div>
																	</td>
																</tr>
																<tr>
																	<td role="presentation">
																		<div class="picker__day picker__day--outfocus" data-pick="1633230000000" role="gridcell" aria-label="3 Outubro, 2021">3</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--outfocus" data-pick="1633316400000" role="gridcell" aria-label="4 Outubro, 2021">4</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--outfocus" data-pick="1633402800000" role="gridcell" aria-label="5 Outubro, 2021">5</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--outfocus" data-pick="1633489200000" role="gridcell" aria-label="6 Outubro, 2021">6</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--outfocus" data-pick="1633575600000" role="gridcell" aria-label="7 Outubro, 2021">7</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--outfocus" data-pick="1633662000000" role="gridcell" aria-label="8 Outubro, 2021">8</div>
																	</td>
																	<td role="presentation">
																		<div class="picker__day picker__day--outfocus" data-pick="1633748400000" role="gridcell" aria-label="9 Outubro, 2021">9</div>
																	</td>
																</tr>
															</tbody>
														</table>
														<div class="picker__footer">
															<button class="picker__button--today" type="button" data-pick="1630465200000" disabled="" aria-controls="datepicker">Today</button>
															<button class="picker__button--clear" type="button" data-clear="1" disabled="" aria-controls="datepicker">Clear</button>
															<button class="picker__button--close" type="button" data-close="true" disabled="" aria-controls="datepicker">Close</button>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<hr class="mb-2">
								<button class="btn btn-success btn-lg btn-block MT-1" type="submit" name="action">Concluir Pedido</button>
								<small class="text-center text-muted mt-2" style="display: block;">Atenção! Caso o entregador chegue ao local do pedido e você não fique com a mercadoria, <b>será cobrada uma taxa de R$ 15,00</b>. Ao clicar no botão acima você estará atestando que está ciente e de acordo.</small>
						</div>
					</div>
				</form>
				</div>
			</div>
		</div>
	</div>
</div>-->

<?php
    require_once('includes/layout/fullwidth/fullwidth-footer.php');
?>
	