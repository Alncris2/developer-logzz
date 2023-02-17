<?php

require_once (dirname(__FILE__) . '/../../includes/config.php');

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

$simple_checkout = true;

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





$page_title =  "Checkout";
require_once (dirname(__FILE__) . '/../../includes/layout/fullwidth/fullwidth-header.php');

?>

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
					<h4 class="card-title">Finalize Seu Pedido!</h4>
					<!-- <p class="mb-0 subtitle">O pagamento será feito no ato da entrega!</p> -->
        		</div>
				<div class="card-body">
				<form id="checkoutForm" action="<?php echo SERVER_URI; ?>">
					<div class="row">
						<div class="col-lg-4 col-md-4 order-md-1 mb-4" style="background: #c8ffe6;padding: 20px 10px;border-radius: 10px;">
							<div class="media align-items-center" style="justify-content: center;">
								<img class="img-fluid" style="margin: 0 auto;width: 100%;border-radius: 100%;max-width: 170px;z-index: 2;margin-bottom: 10px;" src="
									<?php echo SERVER_URI; ?>/uploads/imagens/produtos/
									<?php echo $product_image; ?>" alt="<?php  echo $product_name; ?>">
							</div>
							<!-- <h4 class="d-flex justify-content-between align-items-center mb-3"><span class="text-muted">Resumo do Pedido</span><span class="badge badge-primary badge-pill text-white">3</span></h4> -->
							<ul class="list-group mb-3">
								<li class="list-group-item d-flex justify-content-between lh-condensed">
									<div>
										<h6 class="my-0"> <?php echo  $sale_name; ?> </h6>
										<small class="text-muted"> <?php  echo $product_name; ?> </small>
									</div>
									<span class="text-muted" style="float: left;">R$ <?php echo number_format($sale_price, 2,',', '.'); ?></span>
								</li>
								<li class="list-group-item d-flex justify-content-between lh-condensed">
									<div>
										<h6 class="my-0">Frete</h6>
										<small class="text-muted"></small>
									</div>
									<span class="text-muted">R$ 0,00</span>
								</li>
								<!-- <li class="list-group-item d-flex justify-content-between active"><div class="text-white"><h6 class="my-0 text-white">Cupom de Desconto</h6><small>EXAMPLECODE</small></div><span class="text-white">-$5</span></li> -->
								<li class="list-group-item d-flex justify-content-between lh-condensed">
									<div class="input-group mb-3">
										<input type="text" class="form-control" id="cupom-pedido" name="cupom-pedido" placeholder="Cupom de Desconto" style="padding: 5px 10px;border: none;margin: auto -4px;border-radius: 5px 0px 0px 5px;">
										<div class="input-group-append">
										<span id="aplicar-cupom" class="input-group-text" style="border-radius: 0px 5px 5px 0px;cursor:pointer;">Aplicar</span>
										</div>
									</div>
									
									<input type="hidden" value="<?php echo number_format($sale_price, 2,'.', ''); ?>" name="final-price" id="final-price">
									<input type="hidden" value="<?php echo number_format($sale_price, 2,'.', ''); ?>" name="final-price-wd" id="final-price-wd">
									<input type="hidden" value="<?php echo $sale_id; ?>" name="sale" id="sale">
									<input type="hidden" value="done-order" name="action" id="hdn-inpt-action">
								</li>
								<li class="list-group-item d-flex justify-content-between">
									<span>
										<b>Total (R$)</b>
									</span>
									<strong id="show-final-price">R$ <?php echo number_format($sale_price, 2,',', '.'); ?></strong>
								</li>
							</ul>
							<!-- <form><div class="input-group"><input type="text" class="form-control" placeholder="Promo code"><div class="input-group-append"><button type="submit" class="btn btn-primary">Redeem</button></div></div></form> -->
						</div>
						<div class="col-lg-8 col-md-8 order-md-2" style="padding-left: 30px;">
							<!-- <h4 class="mb-3">Detalhes do Entrega</h4> -->
								<div class="row">
									<div class="col-md-12 mb-3" style=>
										<label for="nome-pedido">Nome Completo</label>
										<input type="text" class="form-control" id="nome-pedido" name="nome-pedido" placeholder="">
										<input type="hidden" value="<?php echo $url; ?>" name="url_checkout">
									</div>
									<div class="col-md-7 mb-3" style=>
										<label for="whatsapp-pedido">WhatsApp</label>
										<input type="text" class="form-control phone" id="whatsapp-pedido" name="whatsapp-pedido"  placeholder="(99) 9 9999-9999" value="">
									</div>
									<div class="col-md-5 mb-2">
										<label for="cep-pedido">CEP</label>
										<input onblur="pesquisacep(this.value);" type="text" class="form-control cep" id="CEP" id="cep-pedido" name="cep-pedido" placeholder="Apenas Números">
									</div>
								</div>
								<!-- <hr class="mb-4"> -->
								<div class="row d-none" id="in-stock-checkout">
									<p class="mb-2 text-muted w-100 text-center"><small>O pagamento será feito no ato da entrega!</small></p>
									<div class="col-md-8 mb-3" id="div-rua">
										<label for="address">Endereço</label>
										<input type="text" class="form-control" id="rua" name="endereco-pedido" placeholder="Rua, Avenida...">
									</div>
									<div class="col-md-4 mb-3" id="div-numero">
										<label for="numero">Número</label>
										<input type="text" class="form-control" id="numero" name="numero-pedido" placeholder="" >
									</div>
									<div class="col-md-4 mb-2" id="div-bairro">
										<label for="bairro-pedido">Bairro</label>
										<input type="text" class="form-control" id="bairro" name="bairro-pedido" placeholder="" value="" >
									</div>
									<div class="col-md-4 mb-3" id="div-cidade">
										<label for="cidade-pedido">Cidade</label>
										<input type="text" class="form-control" id="cidade" name="cidade-pedido" placeholder="" value="" >
									</div>
									<div class="col-md-4 mb-2" id="div-uf">
										<label for="estado-pedido">Estado</label>
										<input type="text" name="estado-pedido" class="form-control" id="uf"  placeholder="" value="" >
									</div>
									<div class="col-md-12 mb-3" id="div-referencia">
										<label for="referencia-pedido">Complemento</label>
										<input type="text" name="referencia-pedido" class="form-control" id="referencia-pedido" placeholder="Apartamento, Bloco, etc." value="">
									</div>
									<div class="col-md-12 mt-2">
										<p class="mb-1">Selecione a Data e o Período para recebimento</p>
									</div>
									<div class="col-md-8 mb-3">
										<input name="data-pedido" value="Data" class="datepicker-default form-control picker__input" id="data-pedido" readonly="" aria-haspopup="true" aria-expanded="false" aria-readonly="false" aria-owns="datepicker_root" placeholder="dia / mês / ano">
										<!-- <input type="text" class="form-control" id="hidden-date-field" name="hidden-date-field" placeholder="Apartament, Bloco, etc" value=""> -->
									</div>
									<div class="col-md-4 mb-3">
										<select  class="d-block default-select" name="periodo-pedido" >
											<option value="" disabled selected>Período</option>
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
									
								
								<div class="row d-none" id="no-stock-checkout">
									
									<div class="col-md-8 mb-3" id="div-rua">
										<label for="address">Endereço</label>
										<input type="text" class="form-control rua" id="rua2" name="endereco-pedido-cc" placeholder="Rua, Avenida...">
									</div>
									<div class="col-md-4 mb-3" id="div-numero">
										<label for="numero">Número</label>
										<input type="text" class="form-control" id="numero2" name="numero-pedido-cc" placeholder="">
									</div>
									<div class="col-md-4 mb-2" id="div-bairro">
										<label for="bairro-pedido-cc">Bairro</label>
										<input type="text" class="form-control bairro" id="bairro2" name="bairro-pedido-cc" placeholder="" value="">
									</div>
									<div class="col-md-4 mb-3" id="div-cidade">
										<label for="cidade-pedido-cc">Cidade</label>
										<input type="text" class="form-control cidade" id="cidade2" name="cidade-pedido-cc" placeholder="" value="">
									</div>
									<div class="col-md-4 mb-2" id="div-uf">
										<label for="estado-pedido-cc">Estado</label>
										<input type="text" name="estado-pedido-cc" class="form-control uf"  placeholder="" value="" id="uf2">
									</div>
									<div class="col-md-12 mb-3" id="div-referencia">
										<label for="referencia-pedido-cc">Complemento</label>
										<input type="text" name="referencia-pedido-cc" class="form-control" id="referencia-pedido2" placeholder="Apartamento, Bloco, etc." value="">
									</div>
									
									<!-- <p class="mb-3 w-100 text-center">Pagamento</p>				
									
									<div class="col-md-12 mb-3 text-center" style="display: flex; flex-wrap: wrap; align-content: center; justify-content: center;">
										<div class="custom-control custom-radio mb-2 mr-3" style="float: left;">
											<input id="card" value="card" name="paymentMethod" type="radio" class="custom-control-input" checked="" required="">
											<label class="custom-control-label" for="card">Crédito</label>
										</div>
										<div class="custom-control custom-radio mb-2 mr-3" style="float: left;">
											<input id="pix" value="pix" name="paymentMethod" type="radio" class="custom-control-input" required="">
											<label class="custom-control-label" for="pix">PIX</label>
										</div>
										<div class="custom-control custom-radio mb-2 mr-3" style="float: left;">
											<input id="billet" value="billet" name="paymentMethod" type="radio" class="custom-control-input" required="">
											<label class="custom-control-label" for="billet">Boleto</label>
										</div>
									</div>

									<div class="col-md-6 mb-3">
										<label for="cc-name">Nome do Titular</label>
										<input type="text" class="form-control" id="cc-name" name="cc-name" placeholder="" required="">
									</div>
									<div class="col-md-6 mb-3">
										<label for="cc-number">Número do Cartão</label>
										<input type="text" class="form-control" id="cc-number" name="cc-number" placeholder="" required="">
									</div>
									
									<div class="col-md-3 mb-3">
										<label for="cc-expiration">Validade</label>
										<input type="text" class="form-control cc-expiration" id="cc-expiration" name="cc-expiration" placeholder="" required="">
									</div>
									<div class="col-md-3 mb-3">
										<label for="cc-expiration">CVV</label>
										<input type="text" class="form-control cc-cvv" id="cc-cvv" name="cc-cvv" placeholder="" required="">
									</div>
									<div class="col-md-6 mb-3">
										<label for="cc-cpf">CPF do Titular</label>
										<input type="text" class="form-control cpf" id="cc-cpf" name="cc-cpf" placeholder="" required="">
									</div> -->
								</div>

								<button class="btn btn-success btn-lg btn-block mt-3" type="submit" name="action" id="submit-btn">Concluir Pedido</button>
								<small class="text-center text-muted mt-2 d-none" id="delivery-resp-msg" style="display: block;">Atenção! Caso o entregador chegue ao local do pedido e você não fique com a mercadoria, <b>será cobrada uma taxa de R$ 15,00</b>. Ao clicar no botão acima você estará atestando que está ciente e de acordo.</small>
						</div>
					</div>
				</form>
				</div>
			</div>
		</div>
	</div>
</div>

<?php
    //require_once ('/../../includes/layout/fullwidth/fullwidth-footer.php');
	require_once (dirname(__FILE__) . '/../../includes/layout/fullwidth/fullwidth-footer.php');
?>
