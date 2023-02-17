<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
}

$page_title = "Editar Redirect | Logzz";
$shop_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');
$PAGE_REFERER = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : SERVER_URI . "/produtos/afiliacoes";

if (isset($_GET['product']) && isset($_GET['membership']) && isset($_GET['page'])) {

    $membership_hotcode = addslashes($_GET['membership']);
    $product_id = intval(addslashes($_GET['product']));
    $page_id = intval(addslashes($_GET['page']));
    $user__id = $_SESSION['UserID'];

    $stmt = $conn->prepare('SELECT * FROM products WHERE (product_id = :product_id AND product_trash = 0) AND (product_membership_available = :product_membership_available)');
    $stmt->execute(array('product_id' => $product_id, 'product_membership_available' => 'sim'));

    if ($stmt->rowCount() != 0) {
        while ($row = $stmt->fetch()) {
            $product_name = $row['product_name'];
            $product_sale_page = $row['product_sale_page'];
            $product_cookie_time = $row['product_cookie_time'];
            switch($row['product_membership_type']) {
                case 'primeiroclique': {
                    $product_membership_type = 'Primeiro Clique';
                    break;
                }
                case 'ultimoclique': {
                    $product_membership_type = 'Último Clique';
                    break;
                }
                default: {
                    $product_membership_type = 'Último Clique';
                    break;
                }
            }
        }
    } else {
        header("location: ". $PAGE_REFERER);  
        exit;
    }

    $stmt = $conn->prepare('SELECT page_name FROM pages_sales WHERE (page_product_id = :product_id AND page_id = :page_id)');
    $stmt->execute(array('product_id' => $product_id, 'page_id' => $page_id ));
    if($page = $stmt->fetch()) {
        $product_page_name = $page['page_name'];
    } else {
        $product_page_name = 'Principal';
    }

    # Confere a Afiliação, e aborta, caso ela não seja identificada
    # ou os parâmetros (product, user) não coincidam.

    $verify_membership = $conn->prepare("SELECT membership_affiliate_id, membership_product_id FROM memberships WHERE memberships_hotcode = :memberships_hotcode AND membership_status = 'ATIVA'");
    $verify_membership->execute(array('memberships_hotcode' => $membership_hotcode));

    if ($verify_membership->rowCount() < 1) {
        header("location: ". $PAGE_REFERER);  
        exit;
    }

    $info = $verify_membership->fetch();
    $membership_affiliate_id = $info['membership_affiliate_id'];
    $membership_product_id = $info['membership_product_id'];
    #$membership_hotcode = $info['membership_hotcode'];

    if ($membership_affiliate_id != $user__id || $membership_product_id != $product_id) {
        header("location: ". $PAGE_REFERER);  
        exit;
    }

    $get_membership_meta_pixel = $conn->prepare('SELECT meta_value, meta_key FROM memberships_meta WHERE (membership_hotcode = :membership_hotcode AND product_id = :product_id AND meta_page = :meta_page)');
    $get_membership_meta_pixel->execute(array('membership_hotcode' => $membership_hotcode, 'product_id' => $product_id, 'meta_page' => $page_id));

    while($pixel = $get_membership_meta_pixel->fetch()){        
 
        if($pixel['meta_key'] === 'fb_pixel'){ 
            $fb_pixel = $pixel['meta_value'];
            continue;
        }
        if($pixel['meta_key'] === 'fb_capi'){
            $fb_capi = $pixel['meta_value'];
            continue;
        }
        if($pixel['meta_key'] === 'google_ua'){
            $google_ua = $pixel['meta_value'];
            continue;
        }
        if($pixel['meta_key'] === 'google_aw'){
            $google_aw = $pixel['meta_value'];
            continue;
        }
        if($pixel['meta_key'] === 'tiktok_pixel'){
            $tiktok_pixel = $pixel['meta_value']; 
            continue;
        } 
    }
?>

    <div class="container-fluid">
        <!-- row -->
        <form id="MembershipRedirectUpdate" action="update-redirect" method="POST">

            <div class="row">
                <div class="col-xl-6 col-xxl-6">
                    <div class="card">                        
                        <div class="card-header">
                            <h4 class="card-title">Detalhes do Link</h4>
                        </div>

                        <div class="card-body">
                            <div class="form-group">
                                <label class="text-label">Produto</label>     
                                <input type="text" class="form-control bg-light" disabled value="<?php echo $product_name; ?>">
                            </div>   
 
                            <div class="form-group">
                                <label class="text-label">Link redirect</label>  
                                <input type="text" value="<?php echo CHECKOUT_URI . "redirect/?a=" . $membership_hotcode .'&p='. $product_id .'&ps='. $page_id ?>" type="text" name="product_url" class="form-control copy-hotcode-btn bg-light" data-link="<?php echo CHECKOUT_URI . "redirect/?a=" . $membership_hotcode .'&p='. $product_id .'&ps='. $page_id ?>" readonly> 
                            </div> 

                            <div class="form-group"> 
                                <label class="text-label">Página de Vendas</label> 
                                <input type="text" class="form-control bg-light" disabled value="<?php echo $product_page_name; ?>">
                            </div>

                            <div class="form-group">
                                <label class="text-label">Tipo de Comissionamento</label> 
                                <input type="text" class="form-control bg-light" disabled value="<?php echo $product_membership_type; ?>">
                            </div>

                            <div class="form-group"> 
                                <label class="text-label">Tempo de Cookie</label>  
                                <input type="text" class="form-control bg-light" disabled value="<?php echo $product_cookie_time . ($product_cookie_time == 1 ? ' dia' : ' dias') ?>">
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
                            <input type="hidden" name="page" value="<?php echo $page_id; ?>">
                            <input type="hidden" name="product" value="<?php echo $product_id; ?>">
                            <input type="hidden" name="membership" value="<?php echo $membership_hotcode; ?>">
                            <input type="hidden" name="action" value="update-membership-sale"> 

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