<?php /*

require_once ('includes/config.php');

session_name(SESSION_NAME);
session_start();


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
?>


<?php
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


					<button type="submit" class="buy-button btn" id="btn_pix" style="background: green !important; color:white; width: 40%;">
              <img src="./images/pix.png" style="width: 35px; height: 35px;">
             GERAR PIX</button>

        
    <button type="submit" class="buy-button btn" id="btn_boleto" style="background: #9e0a22 !important; color:white; width: 40%;">
<img src="./images/boleto.png" style="width: 60px; height: 35px; border-radius: 10px;">
    GERAR BOLETO</button>



<div type="submit" class="buy-button btn" id="btn_boleto" style="background: #ddd !important; color:black; width: 40%;">
<img src="./images/cartao.png" style="width: 60px; height: 35px; border-radius: 10px;">
    CARTÃO ABAIXO</div>            
      </div>

</h4>
<div align="center">
//ATENÇÃO TROCAR URL APÓS INTEGRAÇÃO
<span class="endereco" data-endereco="<?php echo 'http://localhost/dropexpress/checkout-pag-seguro.php;' ?>"></span>
        <span id="msg"></span>
       <form name="formPagamento" action="" id="formPagamento">
           
            <input type="hidden" name="paymentMethod" id="paymentMethod" value="creditCard">

            <input type="hidden" name="receiverEmail" id="receiverEmail" value="gssena@outlook.com">

            <input type="hidden" name="currency" id="currency" value="BRL">

            <!--<input type="hidden" name="extraAmount" id="extraAmount" value="">-->

            <!--<input type="hidden" name="itemId1" id="itemId1" value="0001">

            <input type="hidden" name="itemDescription1" id="itemDescription1" value="Curso de PHP Orientado a Objetos">

            <input type="hidden" name="itemAmount1" id="itemAmount1" value="600.00">

            <input type="hidden" name="itemQuantity1" id="itemQuantity1" value="1">-->

            <input type="hidden" name="notificationURL" id="notificationURL" value="">

            <input type="hidden" name="reference" id="reference" value="1">

            <input type="hidden" name="amount" id="amount" value="10.00">

            <input type="hidden" name="noIntInstalQuantity" id="noIntInstalQuantity" value="2">

            <h2>Dados do Cartão</h2>
            <label>Número do cartão</label>
            <input class="form-control" style="width:50%" type="text" name="numCartao" id="numCartao" required> 
            <span class="bandeira-cartao"></span><br>

            <label>CVV do cartão</label>
            <input class="form-control" style="width:10%" type="text" name="cvvCartao" id="cvvCartao" maxlength="3" value="123" required><br>

            <input type="hidden" name="bandeiraCartao" id="bandeiraCartao">

            <label>Mês de Validade</label>
            <input class="form-control" style="width:10%" type="text" name="mesValidade" id="mesValidade" maxlength="2" value="12" required><br><

            <label>Ano de Validade</label>
            <input class="form-control" style="width:20%" type="text" name="anoValidade" id="anoValidade" maxlength="4" value="2030" required><br>

            <label>Quantidades de Parcelas</label>
            <select class="form-control" style="width:50%" name="qntParcelas" id="qntParcelas" class="select-qnt-parcelas">
                <option value="">Selecione</option>
            </select><br>

            <input type="hidden" name="valorParcelas" id="valorParcelas">

            <label>CPF do dono do Cartão</label>
            <input class="form-control" style="width:50%" type="text" name="creditCardHolderCPF" id="creditCardHolderCPF" placeholder="CPF sem traço" value="22111944785" required><br>

            <label>Nome no Cartão</label>
            <input class="form-control" style="width:50%" type="text" name="creditCardHolderName" id="creditCardHolderName" placeholder="Nome igual ao escrito no cartão" value="Jose Comprador" required><br>

            <input type="hidden" name="tokenCartao" id="tokenCartao">

            <input type="hidden" name="hashCartao" id="hashCartao">

            <h2>Endereço do dono do cartão</h2>

            <label>Logradouro</label>
            <input class="form-control" style="width:50%" type="text" name="billingAddressStreet" id="billingAddressStreet" placeholder="Av. Rua" value="Av. Brig. Faria Lima" required><br>

            <label>Número</label>
            <input class="form-control" style="width:50%" type="text" name="billingAddressNumber" id="billingAddressNumber" placeholder="Número" value="1384" required><br>

            <label>Complemento</label>
            <input class="form-control" style="width:50%" type="text" name="billingAddressComplement" id="billingAddressComplement" placeholder="Complemento" value="5o andar"><br>

            <label>Bairro</label>
            <input class="form-control" style="width:50%" type="text" name="billingAddressDistrict" id="billingAddressDistrict" placeholder="Bairro" value="Jardim Paulistano"><br>

            <label>CEP</label>
            <input class="form-control" style="width:50%" type="text" name="billingAddressPostalCode" id="billingAddressPostalCode" placeholder="CEP sem traço" value="01452002" required><br>

            <label>Cidade</label>
            <input class="form-control" style="width:50%" type="text" name="billingAddressCity" id="billingAddressCity" placeholder="Cidade" value="Sao Paulo" required><br>

            <label>Estado</label>
            <input class="form-control" style="width:50%" type="text" name="billingAddressState" id="billingAddressState" placeholder="Sigla do Estado" value="SP" required><br>

            <input type="hidden" name="billingAddressCountry" id="billingAddressCountry" value="BRL">

            <h2>Dados do Comprador</h2>

            <label>Nome</label>
            <input class="form-control" style="width:50%" type="text" name="senderName" id="senderName" placeholder="Nome completo" value="Jose Comprador" required><br>

            <label>Data de Nascimento</label>
            <input class="form-control" style="width:50%" type="text" name="creditCardHolderBirthDate" id="creditCardHolderBirthDate" placeholder="Data de Nascimento. Ex: 12/12/1912" value="27/10/1987" required><br>

            <label>CPF</label>
            <input class="form-control" style="width:50%" type="text" name="senderCPF" id="senderCPF" placeholder="CPF sem traço" value="22111944785" required><br>

            <label>Telefone</label>
            <input class="form-control" style="width:50%" type="text" name="senderAreaCode" id="senderAreaCode" placeholder="DDD" value="11" required><br>

            <input class="form-control" style="width:50%" type="text" name="senderPhone" id="senderPhone" placeholder="Somente número" value="56273440" required><br>

            <label>E-mail</label>
            <input class="form-control" style="width:50%" type="email" name="senderEmail" id="senderEmail" placeholder="E-mail do comprador" value="c66860546910556664625@sandbox.pagseguro.com.br" required><br>

            <h2>Endereço de Entrega</h2>
            <input class="form-control" style="width:50%" type="hidden" name="shippingAddressRequired" id="shippingAddressRequired" value="true">

            <label>Logradouro</label>
            <input class="form-control" style="width:50%" type="text" name="shippingAddressStreet" id="shippingAddressStreet" placeholder="Av. Rua" value="Av. Brig. Faria Lima"><br>

            <label>Número</label>
            <input class="form-control" style="width:50%" type="text" name="shippingAddressNumber" id="shippingAddressNumber" placeholder="Número" value="1384"><br>

            <label>Complemento</label>
            <input class="form-control" style="width:50%" type="text" name="shippingAddressComplement" id="shippingAddressComplement" placeholder="Complemento" value="5o andar"><br>

            <label>Bairro</label>
            <input class="form-control" style="width:50%" type="text" name="shippingAddressDistrict" id="shippingAddressDistrict" placeholder="Bairro" value="Jardim Paulistano"><br>

            <label>CEP</label>
            <input class="form-control" style="width:50%" type="text" name="shippingAddressPostalCode" id="shippingAddressPostalCode" placeholder="CEP sem traço" value="01452002"><br>

            <label>Cidade</label>
            <input class="form-control" style="width:50%" type="text" name="shippingAddressCity" id="shippingAddressCity" placeholder="Cidade" value="Sao Paulo"><br>

            <label>Estado</label>
            <input class="form-control" style="width:50%" type="text" name="shippingAddressState" id="shippingAddressState" placeholder="Sigla do Estado" value="SP"><br>

            <input type="hidden" name="shippingAddressCountry" id="shippingAddressCountry" value="BRL">

            <label >Frete</label>
            <input type="radio" name="shippingType" value="1"> PAC
            <input type="radio" name="shippingType" value="2"> SEDEX
            <input type="radio" name="shippingType" value="3" checked> Sem frete<br><br>

            <label>Valor Frete</label>
            <input class="form-control" style="width:50%" type="text" name="shippingCost" id="senderCPF" placeholder="Preço do frete. Ex: 2.10" value="0.00"><br>         

            <input class="form-control btn btn-success" style="width:50%; background:#009c4a; color:white;" type="submit" name="btnComprar" id="btnComprar" value="Comprar">
        </form>


        <div class="bandeira-cartao"></div>
        <div class="meio-pag"></div></div>
 //ATENÇÃO TROCAR URL APÓS INTEGRAÇÃO      
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://stc.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.directpayment.js" type="text/javascript"></script>
<script type="text/javascript">
  

var amount = $('#amount').val();
//var amount = "600.00";
pagamento();

function pagamento() {

    //Endereco padrão do projeto
    var endereco = jQuery('.endereco').attr("data-endereco");
    $.ajax({

        //URL completa do local do arquivo responsável em buscar o ID da sessão TROCAR URL APÓS INTEGRAÇÃO
        url: "https://inforarte.com.br/pagseguro/controllerid.php",
        type: 'POST',
        dataType: 'json',
        success: function (retorno) {

            //ID da sessão retornada pelo PagSeguro
            PagSeguroDirectPayment.setSessionId(retorno.id);
        },
        complete: function (retorno) {
            listarMeiosPag();
        }
    });
}

function listarMeiosPag() {
    PagSeguroDirectPayment.getPaymentMethods({
        amount: amount,
        success: function (retorno) {

            //Recuperar as bandeiras do cartão de crédito
            $('.meio-pag').append("<div>Cartão de Crédito</div>");
            $.each(retorno.paymentMethods.CREDIT_CARD.options, function (i, obj) {
                $('.meio-pag').append("<span class='img-band'><img src='https://stc.pagseguro.uol.com.br" + obj.images.SMALL.path + "'></span>");
            });

            //Recuperar as bandeiras do boleto
            $('.meio-pag').append("<div>Boleto</div>");
            $('.meio-pag').append("<span class='img-band'><img src='https://stc.pagseguro.uol.com.br" + retorno.paymentMethods.BOLETO.options.BOLETO.images.SMALL.path + "'><span>");

            //Recuperar as bandeiras do débito online
            $('.meio-pag').append("<div>Débito Online</div>");
            $.each(retorno.paymentMethods.ONLINE_DEBIT.options, function (i, obj) {
                $('.meio-pag').append("<span class='img-band'><img src='https://stc.pagseguro.uol.com.br" + obj.images.SMALL.path + "'></span>");
            });
        },
        error: function (retorno) {
            // Callback para chamadas que falharam.
        },
        complete: function (retorno) {
            // Callback para todas chamadas.
            //recupTokemCartao();
        }
    });
}

//Receber os dados do formulário, usando o evento "keyup" para receber sempre que tiver alguma alteração no campo do formulário
$('#numCartao').on('keyup', function () {

    //Receber o número do cartão digitado pelo usuário
    var numCartao = $(this).val();

    //Contar quantos números o usuário digitou
    var qntNumero = numCartao.length;

    //Validar o cartão quando o usuário digitar 6 digitos do cartão
    if (qntNumero == 6) {

        //Instanciar a API do PagSeguro para validar o cartão
        PagSeguroDirectPayment.getBrand({
            cardBin: numCartao,
            success: function (retorno) {
                $('#msg').empty();

                //Enviar para o index a imagem da bandeira TROCAR URL APÓS INTEGRAÇÃO
                var imgBand = retorno.brand.name;
                $('.bandeira-cartao').html("<img src='https://stc.pagseguro.uol.com.br/public/img/payment-methods-flags/42x20/" + imgBand + ".png'>");
                $('#bandeiraCartao').val(imgBand);
                recupParcelas(imgBand);
            },
            error: function (retorno) {

                //Enviar para o index a mensagem de erro
                $('.bandeira-cartao').empty();
                $('#msg').html("Cartão inválido");
            }
        });
    }
});

//Recuperar a quantidade de parcelas e o valor das parcelas no PagSeguro
function recupParcelas(bandeira) {
    var noIntInstalQuantity = $('#noIntInstalQuantity').val();
    PagSeguroDirectPayment.getInstallments({
        
        //Valor do produto
        amount: amount,

        //Quantidade de parcelas sem juro        
        maxInstallmentNoInterest: noIntInstalQuantity,

        //Tipo da bandeira
        brand: bandeira,
        success: function (retorno) {
            $.each(retorno.installments, function (ia, obja) {
                $.each(obja, function (ib, objb) {

                    //Converter o preço para o formato real com JavaScript
                    var valorParcela = objb.installmentAmount.toFixed(2).replace(".", ",");

                    //Acrecentar duas casas decimais apos o ponto
                    var valorParcelaDouble = objb.installmentAmount.toFixed(2);
                    //Apresentar quantidade de parcelas e o valor das parcelas para o usuário no campo SELECT
                    $('#qntParcelas').show().append("<option value='" + objb.quantity + "' data-parcelas='" + valorParcelaDouble + "'>" + objb.quantity + " parcelas de R$ " + valorParcela + "</option>");
                });
            });
        },
        error: function (retorno) {
            // callback para chamadas que falharam.
        },
        complete: function (retorno) {
            // Callback para todas chamadas.
        }
    });
}

//Enviar o valor parcela para o formulário
$('#qntParcelas').change(function () {
    $('#valorParcelas').val($('#qntParcelas').find(':selected').attr('data-parcelas'));
});

//Recuperar o token do cartão de crédito
$("#formPagamento").on("submit", function (event) {
    event.preventDefault();

    PagSeguroDirectPayment.createCardToken({
        cardNumber: $('#numCartao').val(), // Número do cartão de crédito
        brand: $('#bandeiraCartao').val(), // Bandeira do cartão
        cvv: $('#cvvCartao').val(), // CVV do cartão
        expirationMonth: $('#mesValidade').val(), // Mês da expiração do cartão
        expirationYear: $('#anoValidade').val(), // Ano da expiração do cartão, é necessário os 4 dígitos.
        success: function (retorno) {
            $('#tokenCartao').val(retorno.card.token);
        },
        error: function (retorno) {
            // Callback para chamadas que falharam.
        },
        complete: function (retorno) {
            // Callback para todas chamadas.
            recupHashCartao();
        }
    });
});

//Recuperar o hash do cartão TROCAR A URL APÓS PASSAR PRA ON-LINE
function recupHashCartao() {
    PagSeguroDirectPayment.onSenderHashReady(function (retorno) {
        if (retorno.status == 'error') {
            console.log(retorno.message);
            return false;
        } else {
            $("#hashCartao").val(retorno.senderHash);
            var dados = $("#formPagamento").serialize();
            console.log(dados);
            
            var endereco = jQuery('.endereco').attr("data-endereco");
            console.log(endereco);
            $.ajax({
                method: "POST",
                url: "https://inforarte.com.br/pagseguro/proc_pag.php",
                data: dados,
                dataType: 'json',
                success: function(retorna){
                    console.log("Sucesso " + JSON.stringify(retorna));                    
                },
                error: function(retorna){
                    console.log("Erro");
                }
            });
        }
    });
}


</script>

<?php
    require_once('includes/layout/fullwidth/fullwidth-footer.php');
?>
	<