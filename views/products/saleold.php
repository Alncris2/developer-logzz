
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

    $stmt = $conn->prepare('SELECT * FROM sales as s INNER JOIN products AS p ON p.product_id = s.product_id WHERE s.product_id = :product_id AND (s.sale_id = :sale_id AND p.user__id = :user__id)');
    $stmt->execute(array('product_id' => $product_id, 'sale_id' => $sale_id, 'user__id' => $user__id));

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
    } else {
    }

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
                                        <label class="text-label">Comissão Personalizada (%)</label> <i class="fas fa-info-circle" id="helper" data-toggle="modal" data-target="#exampleModalCenter"></i>
                                        <div class="custom-control custom-switch" id="ponteiro1">
                                            <input type="checkbox" onchange="switch1()" class="custom-control-input" id="customSwitches1">
                                            <label class="custom-control-label" for="customSwitches1"> Sim</label>
                                        </div>
                                        <input value="<?php echo @$custom_commission[0]; ?>" type="number" id="comissao-personalizada" name="comissao-personalizada" class="form-control d-none">
                                    </div>
                            <?php 
                            } ?>

                            <div class="form-group">
                                <label class="text-label">Quantidade de Itens<i class="req-mark">*</i></label>
                                <input value="<?php echo $sale_quantity; ?>" type="number" name="quantidade-oferta" class="form-control" required>
                            </div>

                            <!--<div class="form-group">-->
                            <!--    <label class="text-label">Página de Upsell</label>-->
                            <!--    <input value="<?php //echo $url_upsell; ?>" type="text" name="url-upsell" class="form-control">-->
                            <!--</div>-->
                            <div class="form-group">
                                <label class="text-label">Redirecionar para URL externa em caso do cliente em local sem estoque?<i class="req-mark">*</i> </label><i class="fas fa-info-circle" id="helper2" data-toggle="modal" data-target="#exampleModalCenter2"></i> 
                                
                                <div class="custom-control custom-switch"  id="ponteiro2">
                                    <input type="checkbox" onchange="switch2()" class="custom-control-input" id="customSwitches2">
                                    <label class="custom-control-label" for="customSwitches2"> Sim</label>
                                </div>
                                <input value="<?php echo $sale_quantity; ?>" type="number" id="urlredirect" name="urlredirect" class="form-control d-none" required>
                            </div>
                            <div class="form-group">
                                <label class="text-label">Deseja cobrar pelo frete nesta oferta?</label>
                                
                                <div class="custom-control custom-switch" id="ponteiro3">
                                    <input type="checkbox" onchange="switch3()" class="custom-control-input" id="customSwitches3">
                                    <label class="custom-control-label" for="customSwitches3"> Sim</label>
                                </div>
                                <input value="<?php echo $sale_quantity; ?>" type="number" id="valorfrete"  name="quantidade-oferta" class="form-control d-none" required>
                            </div>
                            <div class="form-group">
                                <label class="text-label">Deseja redirecionar o cliente para uma nova oferta após concluir o pedido?</label>
                                <div class="custom-control custom-switch" id="ponteiro4">
                                    <input type="checkbox" onchange="switch4()" class="custom-control-input" id="customSwitches4">
                                    <label class="custom-control-label" for="customSwitches4"> Sim</label>
                                </div>
                                <input value="<?php echo $sale_quantity; ?>" type="number" id="aprovaredirect" name="quantidade-oferta" class="form-control d-none" required>
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
                                <a href="<?php echo CHECKOUT_URI . '/pay/' . $sale_url; ?>" target="_blank"><small style="color: blue"><?php echo CHECKOUT_URI . '/pay/' . $sale_url; ?></small></a>
                                <input type="text" class="d-none" id="url-checkout-to-copy" value="<?php echo CHECKOUT_URI . '/pay/' . $sale_url; ?>">
                                <br>
                                <small><a href="#" id="link-url-checkout-to-copy"><i class="fas fa-clipboard"></i> Copiar URL</a></small>
                                <br>
                                <br>

                                <label class="text-label">URL de Compra com 1 Clique</label><br>
                                <a href="<?php echo CHECKOUT_URI . '/pay/' . $sale_url . '/1clique'; ?>" target="_blank"><small style="color: blue"><?php echo CHECKOUT_URI . '/pay/' . $sale_url . '/1clique'; ?></small></a>
                                <input type="text" class="d-none" id="url-one-clique-to-copy" value="<?php echo CHECKOUT_URI . '/pay/' . $sale_url . '/1clique'; ?>">
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
                                <input value="" type="text" name="meta-google-analytics" class="form-control" placeholder="UA-000000-X">
                            </div>

                            <div class="form-group">
                                <label class="text-label">ID de Conversão Google Ads</label>
                                <input value="" type="text" name="meta-google-ads-id" class="form-control" placeholder="AW-XXXXXXXXXXXXXXXXX">
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <button type="submit" class="btn btn-success">Salvar Tudo</button>
            <a href="<?php echo SERVER_URI . "/produto/" . $product_id; ?>" class="btn btn-warning light">Voltar</a>
        </form>
    </div>

<script>
    function switch1(){
        $("#comissao-personalizada").removeClass("d-none");
        $("#ponteiro1").addClass("d-none");
    };
    function switch2(){
        $("#urlredirect").removeClass("d-none");
        $("#ponteiro2").addClass("d-none");
    };
    function switch3(){
        $("#valorfrete").removeClass("d-none");
        $("#ponteiro3").addClass("d-none");
    };
   function switch4(){
        $("#aprovaredirect").removeClass("d-none");
        $("#ponteiro4").addClass("d-none");
    };
</script>





<!-- Modal -->
<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="helper" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document" style="width:700px;">
    <div class="modal-content" style="">
      <div class="modal-body">
        <h6 class="text-justify">Deseja configurar uma comissão para essa oferta, diferente da padrão do produto ?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal">Fechar</button
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModalCenter2" tabindex="-1" role="dialog" aria-labelledby="helper2" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document" style="width:700px;">
    <div class="modal-content" style="">
      <div class="modal-body">
        <h6 class="text-justify">Caso ative essa opção, iremos identificar a localização do seu cliente antes que a página de pedido seja aberta, redirecionando-o para uma URL externa em caso de insdisponibilidade de estoque onde ele esteja.</p>
        </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal">Fechar</button
      </div>
    </div>
  </div>
</div>
<?php
}
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>