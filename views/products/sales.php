<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
}

$page_title = "Editar Oferta | Logzz";
$sale_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');


//INICIO PRIMEIRA CONDICIONAL - Exibe página de detalhes.
if (isset($_GET['p']) && isset($_GET['s'])) {
    $product_id = intval(addslashes($_GET['p']));
    $sale_id = intval(addslashes($_GET['s']));
    $user__id = $_SESSION['UserID'];

    //$stmt = $conn->prepare('SELECT * FROM sales WHERE sale_id = :sale_id');
    $stmt = $conn->prepare('SELECT * FROM sales as s INNER JOIN products AS p ON p.product_id = s.product_id WHERE s.product_id = :product_id AND (s.sale_id = :sale_id)');
    $stmt->execute(array('product_id' => $product_id, 'sale_id' => $sale_id));
    $sale = $stmt->fetch();
    
    $sale_name = $sale['sale_name'];
    $sale_quantity = $sale['sale_quantity'];
    $sale_price = $sale['sale_price'];
    $sale_status = $sale['sale_status'];
    $sale_url = $sale['sale_url'];
    $name_checkout = $sale['type_checkout'];
    if($sale['sale_freight'] == 0) {
        $sale_freight = null;
    } else {
        $sale_freight = $sale['sale_freight'];
    };
    @$fb_pixel = $sale['sale_fb_pixel'];
    @$tiktok_pixel = $sale['sale_tiktok_pixel'];
    @$google_aw = $sale['sale_google_aw'];
    @$google_ua = $sale['sale_google_ua'];
    @$meta_pixel_facebook_api = $sale['meta_pixel_facebook_api'];
    @$url_upsell = $sale['url_upsell'];
    @$one_click_url = $sale['one_click_url'];
    $product_membership_available = $sale['product_membership_available'];
    
    if ($stmt->rowCount() != 0) {
        while ($row = $stmt->fetch()) {
            $sale_name = $row['sale_name'];
            $sale_quantity = $row['sale_quantity'];
            $sale_price = $row['sale_price'];
            $sale_status = $row['sale_status'];
            $sale_url = $row['sale_url'];
            @$fb_pixel = $row['sale_fb_pixel'];
            @$meta_pixel_facebook_api = $row['meta_pixel_facebook_api'];
            @$url_upsell = $row['url_upsell'];
            @$one_click_url = $row['one_click_url'];
            $product_membership_available = $row['product_membership_available'];
        }
    } 

    $get_all_checkouts = $conn->prepare("SELECT * FROM custom_checkout WHERE user__id = :user__id");
    $get_all_checkouts->execute(['user__id' => $user__id]);
    $all_checkouts = $get_all_checkouts->fetchAll(\PDO::FETCH_ASSOC);

?>

    <div class="container-fluid">
        <!-- row -->
        <form id="UpdateSaleForm" action="update-sale" method="POST">
            <div class="row">
                <div class="col-xl-6 col-xxl-6">
                    <div class="card">

                        <div class="card-header">
                            <h4 class="card-title">Detalhes da Oferta</h4>
                        </div>
            
                        <div class="card-body">

                            <input type="hidden" name="sale_id" value="<?php echo $sale_id; ?>">
                            <input type="hidden" name="product" value="<?php echo $product_id; ?>">
                            <input type="hidden" name="url-one-clique" value="<?php echo CHECKOUT_URI . '/pay/' . $sale_url . '/1clique'; ?>">
                            <input type="hidden" id="ActionInput" name="action" value="update-sale">

                            <div class="form-group">
                                <label class="text-label">Nome da Oferta<i class="req-mark">*</i></label>
                                <input value="<?php echo $sale_name; ?>" id="edit-sale-name-input" type="text" name="nome-oferta" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label class="text-label">URL Amigável<i class="req-mark">*</i></label>
                                <input value="<?php echo $sale_url; ?>" id="url-friedly-input" type="text" name="url-oferta" class="form-control" required>
                            </div>


                            <div class="form-group">
                                <label class="text-label">Preço (R$)<i class="req-mark">*</i></label>
                                <input type="text" name="preco-oferta" class="form-control money" value="<?php echo number_format($sale_price, 2, ',', ''); ?>">
                            </div>
                            <?php
                            # O campo Comissão Personalizada só será exibido
                            # estiver marcado como "Disponível para afiliação"
                            if ($product_membership_available == "sim") {
                                $get_sale_custom_commission = $conn->prepare('SELECT meta_value FROM sales_meta WHERE sale_id = :sale_id AND meta_key = "custom_commission"');
                                $get_sale_custom_commission->execute(array('sale_id' => $sale_id));
                                $custom_commission = $get_sale_custom_commission->fetch();
                            ?>
                                 <div class="form-group">
                                    <label class="text-label" >Comissão Personalizada (%)<i class="req-mark">*</i> 
                                        <a href="#">
                                            <i class="fas fa-info-circle helperlink" id="helper" data-toggle="tooltip" data-placement="top" title="" data-original-title="Caso ative essa opção, iremos configurar uma comissão para essa oferta, diferente da padrão do produto"></i>
                                        </a>
                                    </label> 
                                    <div class="custom-control custom-switch mb-3">
                                        <input type="checkbox" class="custom-control-input" id="produto-estoque-label-comission" <?php if($custom_commission['meta_value'] != "0") echo 'checked'?>>
                                        <label class="custom-control-label" id="produto-estoque-comission" for="produto-estoque-label-comission">&nbsp; <?php if($custom_commission['meta_value'] != "0"){ echo 'Sim'; }else{ echo "Não"; }?></label>
                                    </div>
                                    <input type="number" id="disponivel-estoque-comission" name="comissao-personalizada" minlength="1" maxlength="99" step="0.01" value="<?php  echo $custom_commission['meta_value']?>" class="form-control" style="<?php if($custom_commission['meta_value'] != "0"){ echo 'display:block;'; }else{ echo "display:none;"; }?>"
                                        placeholder="Insira o valor da comissão personalizada em %">
                                </div>
                            <?php
                            } ?>
                            <div class="form-group">
                                <label class="text-label">Quantidade de Itens<i class="req-mark">*</i></label>
                                <input value="<?php echo $sale_quantity; ?>" type="number" name="quantidade-oferta" class="form-control" required>
                            </div>
                            
                            <div class="form-group d-none">
                                <label class="text-label" >Redirecionar para URL externa em caso do cliente em local sem estoque?<i class="req-mark">*</i> 
                                    <a href="#">
                                        <i class="fas fa-info-circle helperlink" id="helper2" data-toggle="tooltip" data-placement="top" title="" data-original-title="Caso ative essa opção, iremos identificar a localização do seu cliente antes que a página de pedido seja aberta, redirecionando-o para uma URL externa em caso de insdisponibilidade de estoque onde ele esteja."></i>
                                    </a>
                                </label> 
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="produto-estoque-label" <?php if($sale['sale_url_redirect_or'] != "") echo 'checked'?>>
                                    <label class="custom-control-label" id="produto-estoque" for="produto-estoque-label">&nbsp; <?php if($sale['sale_url_redirect_or'] != null){ echo 'Sim'; }else{ echo "Não"; }?></label>
                                </div>
                                <input type="text" id="disponivel-estoque" name="disponivel-estoque" class="form-control" value="<?php if($sale['sale_url_redirect_or'] != null) echo $sale['sale_url_redirect_or']?>" style="<?php if($sale['sale_url_redirect_or'] !== null){ echo 'display:block;'; }else{ echo "display:none;"; }?>" 
                                placeholder="Insira o URL de redirecionamento para fora do raio de alcance">
                            </div>
                            <div class="form-group">
                                <label class="text-label" >Deseja cobrar pelo frete nesta oferta?<i class="req-mark">*</i></label> 
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="produto-estoque-label-frete" <?php if($sale['sale_freight'] != 0 && $sale['sale_freight'] != null) echo 'checked'?>>
                                    <label class="custom-control-label" id="produto-estoque-frete" for="produto-estoque-label-frete">&nbsp;  <?php if($sale['sale_freight'] != 0 && $sale['sale_freight'] != null){ echo 'Sim'; }else{ echo "Não"; }?></label>
                                </div>
                                <input type="text" onKeyPress="return(MascaraMoeda(this,'.',',',event))" id="disponivel-frete" name="disponivel-frete" value="<?php if($sale['sale_freight'] != 0 && $sale['sale_freight'] != null) echo number_format($sale['sale_freight'], 2, ',', '')?>" class="form-control" style="<?php if($sale['sale_freight'] != 0 && $sale['sale_freight'] != null){ echo 'display:block;'; }else{ echo "display:none;"; }?>"  
                                placeholder="Insira o preço único cobrado para essa oferta, em R$">
                            </div>
                            
                            <div class="form-group">
                                <label class="text-label" >Deseja redirecionar o cliente para uma nova oferta após concluir o pedido?<i class="req-mark">*</i> 
                                    <a href="#">
                                        <i class="fas fa-info-circle" id="helper3" data-toggle="tooltip" data-placement="top" title="" data-original-title="Clique aqui para aprender a configurar um upsell, permitindo que seu cliente agende mais um produto no mesmo pedido com apenas um clique."></i>
                                    </a>
                                </label> 
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="produto-estoque-label-oferts" <?php if($sale['url_upsell'] != null) echo 'checked'?>>
                                    <label class="custom-control-label" id="produto-estoque-oferts" for="produto-estoque-label-oferts">&nbsp; <?php if($sale['url_upsell'] != null){ echo 'Sim'; }else{ echo "Não"; }?></label>
                                </div>                                
                                <input type="text" id="disponivel-oferts" name="url-upsell" class="form-control" value="<?php echo @$sale['url_upsell']; ?>" style="<?php if($sale['url_upsell'] !== null){ echo 'display: ;';} else{  echo "display:none;"; }?>" 
                                placeholder="Informe a URL Upsell">
                            </div>

                            <div class="form-group">
                                <label class="text-label" >Checkout personalizado para essa oferta<i class="req-mark">*</i> 
                                </label> 
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" id="checkout-select" <?php if($name_checkout !== "CHECKOUT_PADRÃO") echo 'checked' ?>>
                                    <label class="custom-control-label" id="checkout-select-lbl" for="checkout-select">&nbsp; <?= $name_checkout !== "CHECKOUT_PADRÃO" ? 'Sim' : 'Não'?></label>
                                </div> 
                                <div id="components-checkout" class="<?php if($name_checkout == "CHECKOUT_PADRÃO") echo 'd-none' ?>">
                                  <select class="d-block default-select" name="checkout" data-live-search="true">
                                    <?php foreach($all_checkouts as $checkout): ?>
                                        <option 
                                            value="<?= $checkout['name_checkout']?>"
                                            <?= $name_checkout == $checkout['name_checkout'] ? 'selected' : ''?>
                                        >
                                            <?= $checkout['name_checkout'] == "CHECKOUT_PADRÃO" ? 'Checkout Padrão' : 'Checkout Personalizado -' . " " . strtolower($checkout['name_checkout']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                  </select>
                                </div>                                                             
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 col-xxl-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Detalhes do Checkout</h4>
                        </div>
                        <div class="card-body">
                            <input type="hidden" name="produto" value="<?php echo $product_id; ?>">
                            <div class="form-group">
                                <label class="text-label">URL de Checkout</label><br>
                                <a href="<?php echo CHECKOUT_URI . 'pay/' . $sale_url; ?>" target="_blank"><small style="color: blue"><?php echo CHECKOUT_URI . 'pay/' . $sale_url; ?></small></a>
                                <input type="text" class="d-none" id="url-checkout-to-copy" value="<?php echo CHECKOUT_URI . 'pay/' . $sale_url; ?>">
                                <br>
                                <small><a href="#" id="link-url-checkout-to-copy"><i class="fas fa-clipboard"></i> Copiar URL</a></small>
                                <br>
                                <br>

                                <label class="text-label">URL de Compra com 1 Clique</label><br>
                                <a href="<?php echo CHECKOUT_URI . 'pay/' . $sale_url . '/1clique'; ?>" target="_blank"><small style="color: blue"><?php echo CHECKOUT_URI . 'pay/' . $sale_url . '/1clique'; ?></small></a>
                                <input type="text" class="d-none" id="url-one-clique-to-copy" value="<?php echo CHECKOUT_URI . 'pay/' . $sale_url . '/1clique'; ?>">
                                <br>
                                <small><a href="#" id="link-url-one-clique-to-copy"><i class="fas fa-clipboard"></i> Copiar URL</a></small>
                                <br>
                            </div>

                            <div class="form-group">
                                <label class="text-label">Pixel do Facebook</label>
                                <input value="<?php echo @$fb_pixel; ?>" type="text" name="meta-pixel-facebook" class="form-control" placeholder="Apenas ID do Pixel">
                            </div>

                            <div class="form-group">
                                <label class="text-label">Token da API de Conversões do Facebook</label>
                                <input value="<?php echo @$meta_pixel_facebook_api; ?>" type="text" name="meta-pixel-facebook-api" class="form-control" placeholder="EAA5XRa7tZAMABABBLD4ljf8JAfbbfkHg7hWmbhtfA8LGyw4CWpKF1ZAk6FsnnCP1ZCUumXspz38NAcUYmWELLkH7">
                            </div>
                            
                            <div class="form-group">
                                <label class="text-label">Google Analytics</label>
                                <input value="<?php echo @$google_ua; ?>" type="text" name="meta-google-analytics" class="form-control" placeholder="UA-000000-X">
                            </div>

                            <div class="form-group">
                                <label class="text-label">ID de Conversão Google Ads</label>
                                <input value="<?php echo @$google_aw; ?>" type="text" name="meta-google-ads-id" class="form-control" placeholder="AW-XXXXXXXXXXXXXXXXX">
                            </div>

                            <div class="form-group">
                                <label class="text-label">Tiktok Pixel</label>
                                <input value="<?php echo @$tiktok_pixel; ?>" type="text" name="meta-tiktok-pixel" class="form-control" placeholder="Apenas ID do Pixel">
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-success">Salvar Tudo</button>
            <a href="<?php echo SERVER_URI . "/produto/" . $product_id; ?>" class="btn btn-warning light">Voltar</a>
        </form>
    </div>

<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="helper" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document" style="width:700px;">
    <div class="modal-content" style="">
      <div class="modal-body">
        <h6 class="text-justify"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<?php
}
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>
