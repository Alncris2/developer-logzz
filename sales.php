<?php

require_once ('includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
}

$page_title = "Editar Oferta | Logzz";
require_once('includes/layout/default/default-header.php');


//INICIO PRIMEIRA CONDICIONAL - Exibe página de detalhes.
if (isset($_GET['p']) && isset($_GET['s'])){
    $product_id = intval(addslashes($_GET['p']));
    $sale_id = intval(addslashes($_GET['s']));
    $user__id = $_SESSION['UserID'];

    $stmt = $conn->prepare('SELECT * FROM sales WHERE product_id = :product_id AND sale_id = :sale_id');
    $stmt->execute(array('product_id' => $product_id, 'sale_id' => $sale_id));
    
    if ($stmt->rowCount() != 0){
        while($row = $stmt->fetch()) {
            $sale_name = $row['sale_name'];
            $sale_quantity = $row['sale_quantity'];
            $sale_price = $row['sale_price'];
            $sale_status = $row['sale_status'];
            $sale_url = $row['sale_url'];
            @$fb_pixel = $row['sale_fb_pixel'];
            @$meta_pixel_facebook_api = $row['meta_pixel_facebook_api'];
            @$url_upsell = $row['url_upsell'];
            @$one_click_url = $row['one_click_url'];
        } 
      } else {
        header('Location: ' . SERVER_URI . '/meus-produtos');
    }

?>

<div class="container-fluid"><!-- row -->
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
            <input type="hidden" name="url-one-clique" value="<?php echo SERVER_URI . '/checkout/' . $sale_url . '/1clique'; ?>">
            <input type="hidden" id="ActionInput" name="action" value="update-sale">
            
            <div class="form-group">
              <label class="text-label">Nome da Oferta<i class="req-mark">*</i></label>
              <input value="<?php echo $sale_name; ?>" id="edit-sale-name-input" type="text" name="nome-oferta" class="form-control" required>
            </div>
            
            <div class="form-group">
              <label class="text-label">URL Amigável<i class="req-mark">*</i></label>
              <input value="<?php echo $sale_url;?>" id="url-friedly-input" type="text" name="url-oferta" class="form-control" required>
            </div>


            <div class="form-group">
              <label class="text-label">Preço<i class="req-mark">*</i></label>
              <input type="text" name="preco-oferta" class="form-control money" value="<?php echo number_format($sale_price, 2, ',', ''); ?>">
            </div>
                          
            <div class="form-group">
              <label class="text-label">Quantidade de Itens<i class="req-mark">*</i></label>
              <input value="<?php echo $sale_quantity; ?>"  type="number" name="quantidade-oferta" class="form-control" required>
            </div>

            <div class="form-group">
              <label class="text-label">Página de Upsell</label>
              <input value="<?php echo $url_upsell;?>" type="text" name="url-upsell" class="form-control">
            </div>

            <div class="form-check form-check-inline">
              <label class="form-check-label">
                <input type="checkbox" class="form-check-input" value="1" checked="checked" name="status-oferta"> Oferta Ativa
              </label>
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
                <label class="text-label">URL de Checkout</label>
                <a href="<?php echo SERVER_URI . '/checkout/' . $sale_url; ?>" target="_blank"><small style="color: blue"><?php echo SERVER_URI . '/checkout/' . $sale_url; ?></small></a>
                <input type="text" class="d-none" id="url-checkout-to-copy" value="<?php echo SERVER_URI . '/checkout/' . $sale_url; ?>">
                <br>
                <small><a href="#" id="link-url-checkout-to-copy"><i class="fas fa-clipboard"></i> Copiar URL</a></small></label>
                <br>
                <br>

                <label class="text-label">URL de Compra com 1 Clique
                <a href="<?php echo SERVER_URI . '/checkout/' . $sale_url . '/1clique'; ?>" target="_blank"><small style="color: blue"><?php echo SERVER_URI . '/checkout/' . $sale_url . '/1clique'; ?></small></a>
                <input type="text" class="d-none" id="url-one-clique-to-copy" value="<?php echo SERVER_URI . '/checkout/' . $sale_url . '/1clique'; ?>">
                <br>
                <small><a href="#" id="link-url-one-clique-to-copy"><i class="fas fa-clipboard"></i> Copiar URL</a></small></label>
                <br>
              </div>

              <div class="form-group">
                <label class="text-label">Pixel do Facebook</label>
                <input value="<?php echo @$fb_pixel; ?>"  type="text" name="meta-pixel-facebook" class="form-control" placeholder="Apenas ID do Pixel">
              </div>

              <div class="form-group">
                <label class="text-label">Token da API de Conversões do Facebook</label>
                <input value="<?php echo @$meta_pixel_facebook_api; ?>"  type="text" name="meta-pixel-facebook-api" class="form-control" placeholder="EAA5XRa7tZAMABABBLD4ljf8JAfbbfkHg7hWmbhtfA8LGyw4CWpKF1ZAk6FsnnCP1ZCUumXspz38NAcUYmWELLkH7">
              </div>

              <!-- <div class="form-group">
                <label class="text-label">Google Tag Manager</label>
                <input value="" type="text" name="meta-tag-manager" class="form-control">
              </div> -->

              <div class="form-group">
                <label class="text-label">Google Analytics</label>
                <input value=""  type="text" name="meta-google-analytics" class="form-control" placeholder="UA-000000-X">
              </div>

              <div class="form-group">
                <label class="text-label">ID de Conversão Google Ads</label>
                <input value=""  type="text" name="meta-google-ads-id" class="form-control" placeholder="AW-XXXXXXXXXXXXXXXXX">
              </div>
          </div>
        </div>
      </div>

    </div>
    <button type="submit" class="btn btn-success">Salvar Tudo</button>
  </form>
</div>

<?php
}
    require_once('includes/layout/default/default-footer.php');
?>