<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
}

$page_title = "Editar Oferta | Logzz";
$shop_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

if (isset($_GET['product']) && isset($_GET['sale']) && isset($_GET['membership'])) {

    $membership_hotcode = addslashes($_GET['membership']);
    $product_id = intval(addslashes($_GET['product']));
    $sale_id = intval(addslashes($_GET['sale']));
    $user__id = $_SESSION['UserID'];

    # Confere a Afiliação, e aborta, caso ela não seja identificada
    # ou os parâmetros (product, user) não coincidam.

    $verify_membership = $conn->prepare("SELECT membership_affiliate_id, membership_product_id FROM memberships WHERE memberships_hotcode = :memberships_hotcode AND membership_status = 'ATIVA'");
    $verify_membership->execute(array('memberships_hotcode' => $membership_hotcode));

    if ($verify_membership->rowCount() < 1) {
        exit;
    } else {
        $info = $verify_membership->fetch();
        $membership_affiliate_id = $info['membership_affiliate_id'];
        $membership_product_id = $info['membership_product_id'];
        #$membership_hotcode = $info['membership_hotcode'];

        if ($membership_affiliate_id != $user__id || $membership_product_id != $product_id) {
            exit;
        }
    }

    $get_membership_meta_pixel = $conn->prepare('SELECT meta_value FROM memberships_meta WHERE (membership_hotcode = :membership_hotcode AND sale_id = :sale_id) AND meta_key = "fb_pixel"');
    $get_membership_meta_pixel->execute(array('membership_hotcode' => $membership_hotcode, 'sale_id' => $sale_id));
    @$fb_pixel = $get_membership_meta_pixel->fetch();
    @$fb_pixel = $fb_pixel['0'];

    $get_membership_meta_fb_capi = $conn->prepare('SELECT meta_value FROM memberships_meta WHERE (membership_hotcode = :membership_hotcode AND sale_id = :sale_id) AND meta_key = "fb_capi"');
    $get_membership_meta_fb_capi->execute(array('membership_hotcode' => $membership_hotcode, 'sale_id' => $sale_id));
    @$fb_capi = $get_membership_meta_fb_capi->fetch();
    @$fb_capi = $fb_capi['0'];

    $get_membership_meta_google_ua = $conn->prepare('SELECT meta_value FROM memberships_meta WHERE (membership_hotcode = :membership_hotcode AND sale_id = :sale_id) AND meta_key = "google_ua"');
    $get_membership_meta_google_ua->execute(array('membership_hotcode' => $membership_hotcode, 'sale_id' => $sale_id));
    @$google_ua = $get_membership_meta_google_ua->fetch();
    @$google_ua = $google_ua['0'];

    $get_membership_meta_google_aw = $conn->prepare('SELECT meta_value FROM memberships_meta WHERE (membership_hotcode = :membership_hotcode AND sale_id = :sale_id) AND meta_key = "google_aw"');
    $get_membership_meta_google_aw->execute(array('membership_hotcode' => $membership_hotcode, 'sale_id' => $sale_id));
    @$google_aw = $get_membership_meta_google_aw->fetch();
    @$google_aw = $google_aw['0'];

    $get_membership_meta_tiktok_pixel = $conn->prepare('SELECT meta_value FROM memberships_meta WHERE (membership_hotcode = :membership_hotcode AND sale_id = :sale_id) AND meta_key = "tiktok_pixel"');
    $get_membership_meta_tiktok_pixel->execute(array('membership_hotcode' => $membership_hotcode, 'sale_id' => $sale_id));
    @$tiktok_pixel = $get_membership_meta_tiktok_pixel->fetch();
    @$tiktok_pixel = $tiktok_pixel['0'];

    $get_sales_info = $conn->prepare('SELECT sale_name, sale_quantity, sale_price FROM sales WHERE product_id = :product_id AND sale_id = :sale_id');
    $get_sales_info->execute(array('product_id' => $product_id, 'sale_id' => $sale_id));

    if ($get_sales_info->rowCount() != 0) {
        while ($row = $get_sales_info->fetch()) {
            $sale_name = $row['sale_name'];
            $sale_quantity = $row['sale_quantity'];
            $sale_price = $row['sale_price'];
        }
    } else {
    }

?>

    <div class="container-fluid">
        <!-- row -->
        <form id="MembershipSaleUpdate" action="update-sale" method="POST">
            <div class="row">

                <div class="col-xl-6 col-xxl-6">
                    <div class="card">

                        <div class="card-header">
                            <h4 class="card-title">Detalhes da Oferta</h4>
                        </div>

                        <div class="card-body">

                            <input type="hidden" name="sale" value="<?php echo $sale_id; ?>">
                            <input type="hidden" name="product" value="<?php echo $product_id; ?>">
                            <input type="hidden" name="membership" value="<?php echo $membership_hotcode; ?>">
                            <input type="hidden" name="action" value="update-membership-sale">

                            <div class="form-group">
                                <label class="text-label">Nome da Oferta</label>
                                <label type="text" class="form-control" style="line-height: 1.7rem;"><?php echo $sale_name; ?></label>
                            </div>

                            <div class="form-group">
                                <label class="text-label">Quantidade de Itens</label>
                                <label class="form-control" style="line-height: 1.7rem;"><?php echo $sale_quantity; ?></label>
                            </div>

                            <div class="form-group">
                                <label class="text-label">Preço (R$)</label>
                                <label class="form-control" style="line-height: 1.7rem;"><?php echo number_format($sale_price, 2, ',', ''); ?></label>
                            </div>
                            <!-- <div class="form-group">
                                <label class="text-label">Comissãoo</label>
                                <input type="text" class="form-control money" value="<?php #echo number_format(($sale_price * ($product_commission / 100)), 2, ',', ''); 
                                                                                        ?>" disabled>
                            </div> -->

                        </div>

                    </div>
                </div>

                <div class="col-xl-6 col-xxl-6">
                    <div class="card">

                        <div class="card-header">
                            <h4 class="card-title">Detalhes do Checkout</h4>
                        </div>

                        <div class="card-body">
                            <div class="form-group">
                                <label class="text-label">Facebook Pixel</label>
                                <input value="<?php echo @$fb_pixel; ?>" type="text" name="meta-pixel-facebook" class="form-control" placeholder="Apenas ID do Pixel">
                            </div>

                            <div class="form-group">
                                <label class="text-label">Token da API de Conversões do Facebook</label>
                                <input value="<?php echo @$fb_capi; ?>" type="text" name="meta-pixel-facebook-api" class="form-control" placeholder="EAA5XRa7tZAMABABBLD4ljf8JAfbbfkHg7hWmbhtfA8LGyw4CWpKF1ZAk6FsnnCP1ZCUumXspz38NAcUYmWELLkH7">
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
            <a href="<?php echo SERVER_URI; ?>/produtos/afiliacoes/<?php echo $membership_hotcode . "/" . $product_id; ?>" class="btn btn-warning light">Voltar</a>
        </form>
    </div>

<?php
}
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>