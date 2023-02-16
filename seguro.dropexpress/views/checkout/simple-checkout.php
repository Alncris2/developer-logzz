<?php
require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();  
 
if (isset($_GET['url'])) {
    $url = addslashes($_GET['url']);

    $sale_data = $conn->prepare('SELECT * FROM sales WHERE sale_url = :sale_url');
    $sale_data->execute(array('sale_url' => $url));

    if ($sale_data->rowCount() != 0) {
        while ($row = $sale_data->fetch()) {
            $sale_name = $row['sale_name'];
            $sale_quantity = $row['sale_quantity'];
            $sale_price = $row['sale_price'];
            $sale_status = $row['sale_status'];
            $sale_id = $row['sale_id'];
            $product_id = $row['product_id'];
            $sale_freight = $row['sale_freight'];
            $name_checkout = $row['type_checkout'];
            @$fb_pixel = $row['sale_fb_pixel'];
            @$tiktok_pixel = $row['sale_tiktok_pixel'];
            @$google_aw = $row['sale_google_aw'];
            @$google_ua = $row['sale_google_ua'];
            @$meta_pixel_facebook_api = $row['meta_pixel_facebook_api'];
        }
    } else {
        header("Location: ../pagina-nao-encontrada");
        exit;
    }
}

$product_data = $conn->prepare('SELECT * FROM products WHERE product_id = :product_id');
$product_data->execute(array('product_id' =>  $product_id));

while ($row = $product_data->fetch()) {
    $product_name = $row['product_name'];
    $user_id = $row['user__id'];
    $product_code = $row['product_code']; 
    $product_price = $row['product_price'];
    $product_description = $row['product_description'];
    $product_image = $row['product_image'];
    $product_id = $row['product_id'];
}

$image_filetype_array = explode('.', $product_image);
$filetype = strtolower(end($image_filetype_array));

$isVideo = in_array($filetype, ['mp4', 'mkv']);

# busca as imagens associadas ao produto
$product_images_data = $conn->prepare('SELECT * FROM products_images WHERE product_id = :product_id');
$product_images_data->execute(array('product_id' =>  $product_id));

$product_images = [];
while ($row = $product_images_data->fetch()) {
    $product_images[] = $row['product_image'];
}

// contabilizar vizualização na pagina
$update_views = $conn->prepare("UPDATE custom_checkout AS cc SET cc.checkout_views = cc.checkout_views +1 WHERE cc.name_checkout = :checkout_name AND cc.user__id = :user__id");
$update_views->execute(['checkout_name' => $name_checkout, 'user__id' => $user_id]);

$cookie_sale_name= '_slid' . $product_code;
$cookie_name = '_mbsid' . $product_code;       
if (isset($_COOKIE[$cookie_name])) {         
    header("Location: ". CHECKOUT_URI . '/pay?a='. $_COOKIE[$cookie_name] .'&s='. $sale_id );  
    exit;        
}  

// $cookie_name = '_mbsid' . $product_code;   
// # Verifica se a comissão é por Primeiro Clique
// if ($product_membership_type == 'primeiroclique') {

//     # Se for por primeiro clique,
//     # verifica se já há um cookie anteriormente instalado.
//     if (isset($_COOKIE[$cookie_name])) {
//         # Se houver um cookie anterior, atribui a comissão
//         # ao afiliado a quem ele pertence.
//         $_SESSION['_mbsid'] = $_COOKIE[$cookie_name];
//     } else {
//         # Se não houver um cookie
//         # cria os cookies e atribui a comissão ao afiliado atual (a aquem pertence o link).
//         $_SESSION['_mbsid'] = $membership_hotcode;
//         setcookie($cookie_name, $membership_hotcode, time() + ($product_cookie_time * 86400));
//     }
// }
//     # Se a comissão não for por primeiro clique (for por último),
//     # cria os cookies e atribui a comissão ao afiliado atual (a aquem pertence o link).
// else {
//     # Cria os COOKIES.
//     $_SESSION['_mbsid'] = $membership_hotcode;
//     setcookie($cookie_name, $membership_hotcode, time() + ($product_cookie_time * 86400));
// }  

// require __DIR__ . '/vendor/autoload.php';   


if (!(empty($fb_pixel))) {
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
	  <img height='1' width='1' style='display:none' src='https://www.facebook.com/tr?id=" . $fb_pixel . "&ev=PageView&noscript=1'/>
	  </noscript>";
}

if (isset($google_ua) && !(empty($google_ua))) {
    $google_ua_code = '
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=' . $google_ua . '"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag(' . "'js'" . ', new Date());

        gtag(' . "'config'" . ",'" . $google_ua . "'" . ');
    </script>';
}

if (isset($google_aw) && !(empty($google_aw))) {
    $google_aw_code = '
    <!-- Global site tag (gtag.js) - Google Ads: ' . $google_aw . ' -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=' . $google_aw . '"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag(' . "'js'" . ', new Date());

        gtag(' . "'config'" . ",'" . $google_aw . "'" . ');
    </script>';
}

if (isset($tiktok_pixel) && !(empty($tiktok_pixel))) {
    $tiktok_pixel_code = '
    <script>
    !function (w, d, t) {
      w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++
)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};n=document.createElement("script");n.type="text/javascript",n.async=!0,n.src=i+"?sdkid="+e+"&lib="+t;e=document.getElementsByTagName("script")[0];e.parentNode.insertBefore(n,e)};
    
      ttq.load(' . "'" . $tiktok_pixel . "'" . ');
      ttq.page();
    }(window, document, ' . "'" . "ttq" . "'" . ');
</script>';
}

$page_title =  ucwords($product_name) . " | Checkout";
$simple_checkout = true;

require_once(dirname(__FILE__) . '/../../includes/layout/fullwidth/fullwidth-header.php');


/* PEGAR INFORMAÇÕES DO CHECKOUT */
$get_checkout = $conn->prepare("SELECT * FROM custom_checkout WHERE user__id = :user__id AND name_checkout = :name_checkout");
$get_checkout->execute(['user__id' => $user_id, 'name_checkout' => $name_checkout]);
$checkout = $get_checkout->fetch(\PDO::FETCH_ASSOC);

// 

$urlImage = $checkout['imageSideDesktop'] !== null && $checkout['imageSideDesktop'] !== '' ? SERVER_URI . "/uploads/imagens/checkout/" . $checkout['imageSideDesktop'] : false;
$urlMobileImage = $checkout['imagebottonMobile'] !== null && $checkout['imagebottonMobile'] !== '' ? SERVER_URI . "/uploads/imagens/checkout/" . $checkout['imagebottonMobile'] : false;
$urlTopImage = $checkout['imageTopDesktop'] !== null && $checkout['imageTopDesktop'] !== '' ? SERVER_URI . "/uploads/imagens/checkout/" . $checkout['imageTopDesktop'] : false;
$urlTopMobileImage = $checkout['imageTopMobile'] !== null && $checkout['imageTopMobile'] !== '' ? SERVER_URI . "/uploads/imagens/checkout/" . $checkout['imageTopMobile'] : false;
 



?>


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flexslider/2.7.2/flexslider.min.css" integrity="sha512-c7jR/kCnu09ZrAKsWXsI/x9HCO9kkpHw4Ftqhofqs+I2hNxalK5RGwo/IAhW3iqCHIw55wBSSCFlm8JP0sw2Zw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
    .card {
        height: auto;
    }

    #toast-container>.toast {
        background-image: none !important;
        background-color: #2BC155;
        color: #fff;
        font-weight: 400;
        padding: 15px 15px 15px 15px !important;
    }

    #toast-container>.toast-success:before {
        content: "";
    }

    :focus {
        outline: 0
    }

    *,
    *:after,
    *:before {
        -webkit-box-sizing: border-box;
        -moz-box-sizing: border-box;
        box-sizing: border-box;
    }

    /*=====================================
    = OwlCarousel css
    =====================================*/
    /**
    * Owl Carousel v2.3.4
    * Copyright 2013-2018 David Deutsch
    * Licensed under: SEE LICENSE IN https://github.com/OwlCarousel2/OwlCarousel2/blob/master/LICENSE
    */
    .owl-carousel,
    .owl-carousel .owl-item {
        -webkit-tap-highlight-color: transparent;
        position: relative
    }

    .owl-carousel {
        overflow: hidden;
        display: none;
        width: 100%;
        z-index: 1
    }

    .owl-carousel .owl-stage {
        position: relative;
        -ms-touch-action: pan-Y;
        touch-action: manipulation;
        -moz-backface-visibility: hidden
    }

    .owl-carousel .owl-stage:after {
        content: ".";
        display: block;
        clear: both;
        visibility: hidden;
        line-height: 0;
        height: 0
    }

    .owl-carousel .owl-stage-outer {
        position: relative;
        overflow: hidden;
        -webkit-transform: translate3d(0, 0, 0)
    }

    .owl-carousel .owl-item,
    .owl-carousel .owl-wrapper {
        -webkit-backface-visibility: hidden;
        -moz-backface-visibility: hidden;
        -ms-backface-visibility: hidden;
        -webkit-transform: translate3d(0, 0, 0);
        -moz-transform: translate3d(0, 0, 0);
        -ms-transform: translate3d(0, 0, 0)
    }

    .owl-carousel .owl-item {
        min-height: 1px;
        float: left;
        -webkit-backface-visibility: hidden;
        -webkit-touch-callout: none
    }

    .owl-carousel .owl-item img {
        display: block;
        width: 100%
    }

    .owl-carousel .owl-dots.disabled,
    .owl-carousel .owl-nav.disabled {
        display: none
    }

    .no-js .owl-carousel,
    .owl-carousel.owl-loaded {
        display: block
    }

    .owl-carousel .owl-dot,
    .owl-carousel .owl-nav .owl-next,
    .owl-carousel .owl-nav .owl-prev {
        cursor: pointer;
        -webkit-user-select: none;
        -khtml-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none
    }

    .owl-carousel .owl-nav button,
    .owl-carousel .owl-dots button {
        background: 0 0;
        color: inherit;
        border: none;
        padding: 0;
        font: inherit
    }

    .owl-carousel.owl-loading {
        opacity: 0;
        display: block
    }

    .owl-carousel.owl-hidden {
        opacity: 0
    }

    .owl-carousel.owl-refresh .owl-item {
        visibility: hidden
    }

    .owl-carousel.owl-drag .owl-item {
        -ms-touch-action: pan-y;
        touch-action: pan-y;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none
    }

    .owl-carousel.owl-grab {
        cursor: move;
        cursor: grab
    }

    .owl-carousel.owl-rtl {
        direction: rtl
    }

    .owl-carousel.owl-rtl .owl-item {
        float: right
    }

    .owl-carousel .animated {
        animation-duration: 1s;
        animation-fill-mode: both
    }

    .owl-carousel .owl-animated-in {
        z-index: 0
    }

    .owl-carousel .owl-animated-out {
        z-index: 1
    }

    .owl-carousel .fadeOut {
        animation-name: fadeOut
    }

    @keyframes fadeOut {
        0% {
            opacity: 1
        }

        100% {
            opacity: 0
        }
    }

    .owl-height {
        transition: height .5s ease-in-out
    }

    .owl-carousel .owl-item .owl-lazy {
        opacity: 0;
        transition: opacity .4s ease
    }

    .owl-carousel .owl-item .owl-lazy:not([src]),
    .owl-carousel .owl-item .owl-lazy[src^=""] {
        max-height: 0
    }

    .owl-carousel .owl-item img.owl-lazy {
        transform-style: preserve-3d
    }

    .owl-carousel .owl-video-wrapper {
        position: relative;
        height: 100%;
        background: #000
    }

    .owl-carousel .owl-video-play-icon {
        position: absolute;
        height: 80px;
        width: 80px;
        left: 50%;
        top: 50%;
        margin-left: -40px;
        margin-top: -40px;
        background: url(owl.video.play.png) no-repeat;
        cursor: pointer;
        z-index: 1;
        -webkit-backface-visibility: hidden;
        transition: transform .1s ease
    }

    .owl-carousel .owl-video-play-icon:hover {
        -ms-transform: scale(1.3, 1.3);
        transform: scale(1.3, 1.3)
    }

    .owl-carousel .owl-video-playing .owl-video-play-icon,
    .owl-carousel .owl-video-playing .owl-video-tn {
        display: none
    }

    .owl-carousel .owl-video-tn {
        opacity: 0;
        height: 100%;
        background-position: center center;
        background-repeat: no-repeat;
        background-size: contain;
        transition: opacity .4s ease
    }

    .owl-carousel .owl-video-frame {
        position: relative;
        z-index: 1;
        height: 100%;
        width: 100%
    }

    /** owl.theme.default.min.css**/
    .owl-bar,
    .owl-carousel .owl-dots,
    .owl-carousel .owl-nav {
        text-align: center;
        -webkit-tap-highlight-color: transparent
    }

    .owl-carousel .owl-nav {
        margin-top: 10px
    }

    .owl-carousel .owl-nav [class*=owl-] {
        color: #FFF;
        font-size: 14px;
        margin: 5px;
        padding: 4px 7px;
        background: #D6D6D6;
        display: inline-block;
        cursor: pointer;
    }

    .owl-carousel .owl-nav .disabled {
        opacity: .5;
        cursor: default
    }

    .owl-carousel .owl-nav.disabled+.owl-dots {
        margin-top: 10px
    }

    .owl-carousel .owl-dots .owl-dot {
        display: inline-block;
        zoom: 1
    }

    .owl-carousel .owl-dots .owl-dot span {
        width: 10px;
        height: 10px;
        margin: 5px 7px;
        background: #D6D6D6;
        display: block;
        -webkit-backface-visibility: visible;
        transition: opacity .2s ease;
        border-radius: 30px;
        -o-border-radius: 30px;
        -ms-border-radius: 30px;
        -moz-border-radius: 30px;
        -webkit-border-radius: 30px;
    }

    .owl-carousel .owl-dots .owl-dot.active span,
    .owl-carousel .owl-dots .owl-dot:hover span {
        background: #2fde91;
    }

    .owl-bar .button {
        font-size: 35px;
        margin: 0 5px;
        color: #d6d6d6;
        cursor: pointer;
        line-height: 1em;
    }

    .owl-bar .button:hover,
    .owl-bar .button.active {
        color: #f79029
    }

    /** owl.my.theme **/
    /*----- buttons-----------*/
    .owl-carousel .owl-nav {
        margin-top: 0;
        text-align: center;
    }

    .owl-carousel .owl-nav button {
        box-shadow: none !important;
    }

    .owl-carousel .owl-nav button.owl-prev {
        right: 0;
    }

    .owl-carousel .owl-nav button.owl-next {
        left: 0;
    }

    .owl-carousel .owl-nav [class*=owl-] {
        background: none;
        background-color: transparent !important;
        z-index: 9;
        position: absolute;
        margin: 0;
        top: 50%;
        overflow: hidden;
    }

    .owl-carousel .owl-nav button .fa {
        position: relative;
        overflow: hidden;
        width: 40px;
        height: 40px;
        line-height: 40px;
        border: 0;
        /*background-color: #f79029;
        
        */
        box-shadow: none;
        color: #2fde91;
        font-size: 50px;
        text-align: center;
    }

    /*-----transition:all 0.5s---------*/
    .owl-carousel .owl-nav button .fa {
        -webkit-transition: all 0.5s;
        -moz-transition: all 0.5s;
        -ms-transition: all 0.5s;
        -o-transition: all 0.5s;
        transition: all 0.5s;
    }

    /*-----translateY(-50%)---------*/
    .owl-carousel .owl-nav button {
        -webkit-transform: translateY(-50%);
        -moz-transform: translateY(-50%);
        -ms-transform: translateY(-50%);
        -o-transform: translateY(-50%);
        transform: translateY(-50%);
    }

    /*-----translate-X--(-300px)---------*/
    .owl-carousel i.fa.fa-angle-left {
        -webkit-transform: translateX(-300px);
        -moz-transform: translateX(-300px);
        -o-transform: translateX(-300px);
        -ms-transform: translateX(-300px);
        t transform: translateX(-300px);
        transform: translateX(-300px);
    }

    /*-----translate-X--(300px)---------*/
    .owl-carousel i.fa.fa-angle-right {
        -webkit-transform: translateX(300px);
        -moz-transform: translateX(300px);
        -o-transform: translateX(300px);
        -ms-transform: translateX(300px);
        transform: translateX(300px);
    }

    /*-----translate-Y--(0)---------*/
    .owl-carousel:hover .owl-nav button .fa,
    .owl-carousel .owl-nav button:focus .fa,
    .owl-carousel .owl-nav button:hover .fa:after {
        -webkit-transform: translateY(0);
        -moz-transform: translateY(0);
        -ms-transform: translateY(0);
        -o-transform: translateY(0);
        transform: translateY(0);
    }

    /*-----border-radius--(100%)---------*/
    .owl-carousel .owl-nav button .fa {
        border-radius: 100%;
        -o-border-radius: 100%;
        -ms-border-radius: 100%;
        -moz-border-radius: 100%;
        -webkit-border-radius: 100%;
    }

    /*-----box-shadow--(prim)--------
    .owl-carousel .owl-nav button .fa {
         -webkit-box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.26);
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.26);
        -moz-box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.26);
        -o-box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.26);
        -ms-box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.26);
    } */


    #sync1 .item {
        /* background: #1ccacd;
        padding: 80px 0px; */
        margin: 5px;
        color: #FFF;
        border-radius: 3px;
        text-align: center;
    }

    #sync2 .item {
        /* background: #1ccacd;
        padding: 10px 0px; */
        margin: 1px;
        color: #FFF;
        border-radius: 3px;
        text-align: center;
        cursor: pointer;
    }

    #sync2 .item h1 {
        font-size: 18px;
    }

    /* #sync2 .current .item {
        background: #f79029;
    } */

    .owl-theme .owl-nav {
        /*default owl-theme theme reset .disabled:hover links */
    }

    #sync1.owl-theme {
        position: relative;
    }

    #sync1.owl-carousel.owl-drag .owl-item {
        height: 17rem;

    }

    #sync1-mobile.owl-carousel.owl-drag .owl-item {
        height: 22rem;

    }

    #sync2 .item {
        height: 50px
    }

    .owl-carousel.owl-drag .owl-item .item {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100%;
    }

    .sync1 div.owl-stage {
        margin-bottom: 1rem;
    }

    .sync1 .item img {
        object-fit: cover;
        height: 22rem;
        width: 100%;
    }

    .flex-control-nav {
        bottom: unset;
    }
</style>

<?php if ($checkout['counter_active'] == 1) : ?>
    <div class="timer-container container-fluid d-flex justify-content-center" style="background:<?= $checkout['checkout_bg_counter'] ?>;height:65px;width:100%;">
        <div class="text-center d-flex justify-content-center align-items-center">
            <div class="mr-2" style="color:<?= $checkout['checkout_text_counter']; ?>;">
                <?= $checkout['checkout_counter_lblText']; ?>
            </div>
            <div class="mr-2 d-flex justify-content-center align-items-center">
                <span class="" style="font-size:22px; color:<?= $checkout['checkout_text_counter'] ?>;" id="timer"><?= $checkout['time_counter'] ?></span> &nbsp;&nbsp;
                <svg style="width:16px;" fill="<?= $checkout['checkout_text_counter'] ?>" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="<?= $checkout['checkout_text_counter'] ?>">
                    <!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. -->
                    <path d="M256 512C114.6 512 0 397.4 0 256C0 114.6 114.6 0 256 0C397.4 0 512 114.6 512 256C512 397.4 397.4 512 256 512zM232 256C232 264 236 271.5 242.7 275.1L338.7 339.1C349.7 347.3 364.6 344.3 371.1 333.3C379.3 322.3 376.3 307.4 365.3 300L280 243.2V120C280 106.7 269.3 96 255.1 96C242.7 96 231.1 106.7 231.1 120L232 256z" />
                </svg>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="row" style="width:100%;margin: 0 auto; <?= $checkout['counter_active'] == 1 ? 'margin-top: -48px;' : '' ?> ">
    <div class="main-container <?= $urlImage ? 'col-lg-8' : 'col-lg-12'; ?> col-md-12">
        <div class="col-lg-8 mt-5" style="max-width: 1000px; <?= $urlImage ? 'margin-left: 0;' : 'margin: 0 auto;'; ?>">

            <?php if ($urlTopImage) : ?>
                <div class="d-none d-lg-block col-lg-12" style="padding:0;">
                    <img class="img-fluid" src="<?= $urlTopImage ?>" style="border-bottom-left-radius: 15px;border-bottom-right-radius: 15px;" />
                </div>
            <?php endif; ?>

            <?php if ($urlTopMobileImage) : ?>
                <div class="d-block d-lg-none d-sm-block">
                    <img class="img-fluid" src="<?= $urlTopMobileImage ?>" style="border-bottom-left-radius: 15px;border-bottom-right-radius: 15px;" />
                </div>
            <?php endif; ?>

            <?php if (!$urlTopMobileImage && $urlTopImage) : ?>
                <div class="d-block d-lg-none d-sm-block">
                    <img class="img-fluid" src="<?= $urlTopImage ?>" style="border-bottom-left-radius: 15px;border-bottom-right-radius: 15px;" />
                </div>
            <?php endif; ?>

            <div class="col-lg-4 col-md-12 col-md-4 mt-4 d-lg-none d-sm-block d-md-block">
                <div class="row">
                    <div class="media align-items-center justify-content-center w-100">
                        <!-- <img class="img-fluid" style="margin: 0 auto;width: 100%;border-radius: 100%;max-width: 170px;z-index: 2;margin-bottom: 10px;" src="<?php echo SERVER_URI . '/uploads/imagens/produtos/' . $product_image ?>" alt="<?php echo $product_name; ?>"> -->
                        <div class="sync1 owl-carousel owl-theme w-100">
                            <div class="item">
                                <?php if ($isVideo) : ?>
                                    <div style="height: 20rem; display: flex;">
                                        <video class="w-100" src="<?php echo SERVER_URI . '/uploads/imagens/produtos/' . $product_image ?>" controls></video>
                                    </div>
                                <?php else : ?>
                                    <img src="<?php echo SERVER_URI . '/uploads/imagens/produtos/' . $product_image ?>" alt="<?php echo $product_name; ?>" alt="Gallery image 1" class="ecommerce-gallery-main-img active w-100 rounded" />
                                <?php endif ?>
                            </div>

                            <?php foreach ($product_images as $image_name) : ?>
                                <?php if ($image_name !== '') : ?>
                                    <div class="item">
                                        <img src="<?php echo SERVER_URI . '/uploads/imagens/produtos/' . $image_name ?>" alt="<?php echo $product_name; ?>" alt="Gallery image 1" class="ecommerce-gallery-main-img active w-100 rounded" />
                                    </div>
                                <?php endif ?>
                            <?php endforeach ?>
                        </div>
                    </div>
                    <div class="row w-100 my-4 ">
                        <div class="col-8 d-flex flex-column justify-content-center">
                            <h5 class="my-0"> <?php echo  $product_name; ?> </h5>
                            <small> <?php echo $sale_quantity . " unidade(s)"; ?> </small>
                        </div>
                        <div class="col-4 text-right p-0">
                            <h5>R$ <?php echo number_format($sale_price, 2, ',', '.'); ?></h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-block text-center text-md-left">
                    <h4 class="card-title">Finalize Seu Pedido!</h4>
                    <p class="mb-0 subtitle d-none d-md-block">O pagamento será feito no ato da entrega!</p>
                </div>
                <div class="card-body">
                    <form id="checkoutForm" action="<?php echo SERVER_URI; ?>">
                        <!-- <?php //echo SERVER_URI 
                                ?> -->
                        <div class="row">
                            <div class="col-lg-4 col-md-12 col-md-4 mb-4 order-md-1 mb-4 d-sm-none d-lg-block d-none" style="background: <?= $checkout['checkout_box_color'] !== "" ? $checkout['checkout_box_color'] : '#c8ffe6' ?>;padding: 20px 10px;border-radius: 10px;">
                                <div class="media align-items-center w-100 d-block mb-4">
                                    <div class="sync1 owl-carousel owl-theme w-100">
                                        <div class="item">
                                            <?php if ($isVideo) : ?>
                                                <div style="height: 20rem; display: flex;">
                                                    <video class="w-100" src="<?php echo SERVER_URI . '/uploads/imagens/produtos/' . $product_image ?>" controls></video>
                                                </div>
                                            <?php else : ?>
                                                <img src="<?php echo SERVER_URI . '/uploads/imagens/produtos/' . $product_image ?>" alt="<?php echo $product_name; ?>" alt="Gallery image 1" class="img-fluid rounded" />
                                            <?php endif ?>
                                        </div>

                                        <?php foreach ($product_images as $image_name) : ?>
                                            <?php if ($image_name !== '') : ?>
                                                <div class="item">
                                                    <img src="<?php echo SERVER_URI . '/uploads/imagens/produtos/' . $image_name ?>" alt="<?php echo $product_name; ?>" alt="Gallery image 1" class="img-fluid rounded" />
                                                </div>
                                            <?php endif ?>
                                        <?php endforeach ?>
                                    </div>
                                </div>

                                <!-- <h4 class="d-flex justify-content-between align-items-center mb-3"><span class="text-muted">Resumo do Pedido</span><span class="badge badge-primary badge-pill text-white">3</span></h4> -->
                                <ul class="list-group mb-3 d-sm-none d-lg-block d-none">
                                    <li class="list-group-item d-flex justify-content-between lh-condensed">
                                        <div>
                                            <h6 class="my-0" style="color:<?= $checkout['checkout_box_text_color']; ?>!important;"> <?php echo  $product_name; ?> </h6>
                                            <small class="text-muted" style="color:<?= $checkout['checkout_box_text_color']; ?>!important;"> <?php echo $sale_quantity . " unidade(s)"; ?> </small>
                                        </div>
                                        <span class="text-muted" style="color:<?= $checkout['checkout_box_text_color']; ?>!important; float: left;">R$ <?php echo number_format($sale_price, 2, ',', '.'); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between lh-condensed">
                                        <div>
                                            <h6 class="my-0" style="color:<?= $checkout['checkout_box_text_color']; ?>!important;"> Frete</h6>
                                            <small class="text-muted"></small>
                                        </div>
                                        <span class="text-muted" style="color:<?= $checkout['checkout_box_text_color']; ?>!important;">R$ <?php echo number_format($sale_freight, 2, ',', '') ?></span>
                                    </li>
                                    <!-- <li class="list-group-item d-flex justify-content-between active"><div class="text-white"><h6 class="my-0 text-white">Cupom de Desconto</h6><small>EXAMPLECODE</small></div><span class="text-white">-$5</span></li> -->
                                    <li class="list-group-item d-flex justify-content-between lh-condensed">
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" id="cupom-pedido" name="cupom-pedido" placeholder="Cupom de Desconto" style="padding: 5px 10px;border: none;margin: auto -4px;border-radius: 5px 0px 0px 5px;">
                                            <div class="input-group-append">
                                                <span id="aplicar-cupom" class="input-group-text" style="border-radius: 0px 5px 5px 0px;cursor:pointer;">Aplicar</span>
                                            </div>
                                        </div>

                                        <input type="hidden" value="<?php echo number_format($sale_price + $sale_freight, 2, '.', ''); ?>" name="final-price" id="final-price">
                                        <input type="hidden" value="<?php echo number_format($sale_price + $sale_freight, 2, '.', ''); ?>" name="final-price-wd" id="final-price-wd">
                                        <input type="hidden" value="<?php echo $sale_id; ?>" name="sale" id="sale">
                                        <input type="hidden" value="done-order" name="action">
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>
                                            <b style="color:<?= $checkout['checkout_box_text_color']; ?>!important;">Total (R$)</b>
                                        </span>
                                        <strong id="show-final-price" style="color:<?= $checkout['checkout_box_text_color']; ?>!important;">R$ <?php if ($sale_freight != null) {
                                                                                                                                                    echo number_format($sale_price + $sale_freight, 2, ',', '.');
                                                                                                                                                } else {
                                                                                                                                                    echo number_format($sale_price, 2, ',', '.');
                                                                                                                                                } ?></strong>
                                    </li>
                                    <?php if ($checkout['support_active'] == 1) : ?>
                                        <li class="d-flex justify-content-center flex-column" style="margin-top: 50px;">
                                            <p class="text-center" style="color:<?= $checkout['checkout_box_text_color']; ?>!important;">Opções de suporte</p>
                                            <div class="d-flex align-items-center justify-content-center">
                                                <?php if ($checkout['support_email'] !== "") : ?>
                                                    <a href="mailto:<?= $checkout['support_email'] ?>" id="email-link" title="Email">
                                                        <svg style="width:25px;margin-right:20px;" xmlns="http://www.w3.org/2000/svg" fill="<?= $checkout['checkout_box_text_color']; ?>" viewBox="0 0 512 512">
                                                            <path d="M464 64C490.5 64 512 85.49 512 112C512 127.1 504.9 141.3 492.8 150.4L275.2 313.6C263.8 322.1 248.2 322.1 236.8 313.6L19.2 150.4C7.113 141.3 0 127.1 0 112C0 85.49 21.49 64 48 64H464zM217.6 339.2C240.4 356.3 271.6 356.3 294.4 339.2L512 176V384C512 419.3 483.3 448 448 448H64C28.65 448 0 419.3 0 384V176L217.6 339.2z" />
                                                        </svg>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ($checkout['support_whatsapp'] !== "") : ?>
                                                    <a id="whatsapp-link" href="https://api.whatsapp.com/send?phone=55<?= str_replace([" ", "-", "(", ")"], "", $checkout['support_whatsapp']) ?>" title="WhastApp">
                                                        <svg style="width:25px;" xmlns="http://www.w3.org/2000/svg" fill="<?= $checkout['checkout_box_text_color']; ?>" viewBox="0 0 448 512">
                                                            <path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z" />
                                                        </svg>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                                <!-- <form><div class="input-group"><input type="text" class="form-control" placeholder="Promo code"><div class="input-group-append"><button type="submit" class="btn btn-primary">Redeem</button></div></div></form> -->
                            </div>
                            <div class="col-lg-8 col-md-12 order-md-2" style="padding-left: 30px;">
                                <!-- <h4 class="mb-3">Detalhes do Entrega</h4> -->
                                <div class="row">
                                    <div class="col-md-12 mb-3" style=>
                                        <label for="nome-pedido">Nome Completo</label>
                                        <input type="text" class="form-control" id="nome-pedido" name="nome-pedido" placeholder="" required>
                                        <input type="hidden" value="<?php echo $url; ?>" name="url_checkout">
                                    </div>
                                    <?php if ($checkout['request_email_client'] == 1) : ?>
                                        <div class="col-md-12 mb-3" style=>
                                            <label for="email-pedido">E-mail</label>
                                            <input type="email" class="form-control" id="email-pedido" name="email-pedido" placeholder="" required>
                                        </div>
                                    <?php endif; ?>
                                    <div class="col-md-4 mb-3" style=>
                                        <label for="whatsapp-pedido">WhatsApp</label>
                                        <input type="text" class="form-control phone" id="whatsapp-pedido" name="whatsapp-pedido" placeholder="(99) 9 9999-9999" value="" required>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label for="cep-pedido">CPF/CNPJ</label>
                                        <input type="text" class="form-control cpfCnpj" id="documento-pedido" name="documento-pedido" placeholder="000.000.000-00" value="" required>
                                    </div>
                                    <div class="col-md-4 mb-2"> 
                                        <label for="cep-pedido">CEP</label>
                                        <input onblur="pesquisacep(this.value);" type="text" class="form-control cep" id="CEP" id="cep-pedido" name="cep-pedido" placeholder="Apenas Números" value="" required>
                                    </div> 
                                </div>   
                                <hr class="mb-4">
                                <div class="row">
                                    <div class=" col-md-8 mb-3" id="div-rua">
                                        <label for="address">Endereço</label>
                                        <input type="text" class="form-control" id="rua" name="endereco-pedido" placeholder="Rua, Avenida..." required readonly>
                                    </div>
                                    <div class=" col-md-4 mb-3" id="div-numero">
                                        <label for="numero">Número</label>
                                        <input type="text" class="form-control" id="numero" name="numero-pedido" placeholder="" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="  col-md-4 mb-2" id="div-bairro">
                                        <label for="bairro-pedido">Bairro</label>
                                        <input type="text" class="form-control" id="bairro" name="bairro-pedido" placeholder="" value="" required readonly>
                                    </div>
                                    <div class="  col-md-4 mb-3" id="div-cidade">
                                        <label for="cidade-pedido">Cidade</label>
                                        <input type="text" class="form-control" id="cidade" name="cidade-pedido" placeholder="" value="" required readonly>
                                    </div>
                                    <div class="col-md-4 mb-2 " id="div-uf">
                                        <label for="estado-pedido">Estado</label>
                                        <input type="text" name="estado-pedido" class="form-control" id="uf" placeholder="" value="" required readonly>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 mb-3" id="div-referencia">
                                        <label for="referencia-pedido">Complemento <small>(opcional)</small></label>
                                        <input type="text" name="referencia-pedido" class="form-control" id="referencia-pedido" placeholder="Apartamento, Bloco, etc." value="">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <hr class="mb-4">
                                </div>

                                <div class="row">
                                    <div class="form-gruop col-md-12">
                                        <label for="data-pedido">Selecione a Data e o Período para recebimento:</label>
                                        <div class="w-100 mb-3"  id="data-pedido-wrapper">
                                            <input name="data-pedido" value="Data" class="datepicker-checkout form-control picker__input" id="data-pedido" readonly="" aria-haspopup="true" aria-expanded="false" aria-readonly="false" aria-owns="datepicker_root" placeholder="dia / mês / ano" disabled>
                                        </div>
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
                                    <small class="text-center text-muted mb-3 fs-12 px-3" style="display: block;">O pedido será entregue no seu endereço das 8h às 18h, escolha um dia que você (ou alguém) esteja disponível durante este período para receber (entregamos no seu trabalho).</small>
                                </div>

                                <hr class="mb-2">

                                <div class="row w-100 mx-0">
                                    <div class="col-lg-4 col-md-12 col-md-4 my-4 d-lg-none d-sm-block d-md-block" style="background: <?= $checkout['checkout_box_color'] !== "" ? $checkout['checkout_box_color'] : '#c8ffe6' ?>;padding: 20px 10px;border-radius: 10px;">
                                        <ul class="list-group mb-3">
                                            <li class="list-group-item d-flex justify-content-between lh-condensed">
                                                <div>
                                                    <h6 class="my-0" style="color:<?= $checkout['checkout_box_text_color']; ?>!important;">Frete</h6>
                                                    <small class="text-muted" style="color:<?= $checkout['checkout_box_text_color']; ?>!important;"></small>
                                                </div>
                                                <span class="text-muted" style="color:<?= $checkout['checkout_box_text_color']; ?>!important;">R$ <?php echo number_format($sale_freight, 2, ',', '') ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between lh-condensed">
                                                <div class="input-group mb-3">
                                                    <input type="text" class="form-control" id="cupom-pedido" name="cupom-pedido" placeholder="Cupom de Desconto" style="padding: 5px 10px;border: none;margin: auto -4px;border-radius: 5px 0px 0px 5px;">
                                                    <div class="input-group-append">
                                                        <span id="aplicar-cupom" class="input-group-text" style="border-radius: 0px 5px 5px 0px;cursor:pointer;">Aplicar</span>
                                                    </div>
                                                </div>

                                                <input type="hidden" value="<?php echo number_format($sale_price + $sale_freight, 2, '.', ''); ?>" name="final-price" id="final-price">
                                                <input type="hidden" value="<?php echo number_format($sale_price + $sale_freight, 2, '.', ''); ?>" name="final-price-wd" id="final-price-wd">
                                                <input type="hidden" value="<?php echo $product_id; ?>" name="product" id="product">
                                                <input type="hidden" value="<?php echo $sale_id; ?>" name="sale" id="sale">
                                                <input type="hidden" value="done-order" name="action">
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span>
                                                    <b style="color:<?= $checkout['checkout_box_text_color']; ?>!important;"> Total (R$)</b>
                                                </span>
                                                <strong id="show-final-price" style="color:<?= $checkout['checkout_box_text_color']; ?>!important;">R$ <?php if ($sale_freight != null) {
                                                                                                                                                            echo number_format($sale_price + $sale_freight, 2, ',', '.');
                                                                                                                                                        } else {
                                                                                                                                                            echo number_format($sale_price, 2, ',', '.');
                                                                                                                                                        } ?></strong>
                                            </li>
                                        </ul>
                                    </div>
                                    <hr class="mb-2 d-block d-md-none">

                                    <button class="btn btn-success btn-lg btn-block MT-1" type="submit" id="SubmitButton"> Concluir Pedido</button>

                                    <p class="mb-0 subtitle text-center w-100">O pagamento será feito no ato da entrega!</p>

                                    <small class="text-center text-muted mt-2" style="display: block;">Atenção! Caso o entregador chegue ao local do pedido e você não fique com a mercadoria, <b>será cobrada uma taxa de R$20,00</b>. Ao clicar no botão você estará atestando que está ciente e de acordo.</small>
                                </div>

                                
                                <ul class="d-lg-none d-sm-block">
                                    <?php if ($checkout['support_active'] == 1) : ?>
                                        <li class="d-flex justify-content-center flex-column" style="margin-top: 10px;">
                                            <p class="text-center"><b>Opções de suporte</b></p>
                                            <div class="d-flex align-items-center justify-content-center">
                                                <?php if ($checkout['support_email'] !== "") : ?>
                                                    <a href="mailto:<?= $checkout['support_email'] ?>" id="email-link" title="Email">
                                                        <svg style="width:25px;margin-right:20px;" xmlns="http://www.w3.org/2000/svg" fill="#1c3c2d" viewBox="0 0 512 512">
                                                            <path d="M464 64C490.5 64 512 85.49 512 112C512 127.1 504.9 141.3 492.8 150.4L275.2 313.6C263.8 322.1 248.2 322.1 236.8 313.6L19.2 150.4C7.113 141.3 0 127.1 0 112C0 85.49 21.49 64 48 64H464zM217.6 339.2C240.4 356.3 271.6 356.3 294.4 339.2L512 176V384C512 419.3 483.3 448 448 448H64C28.65 448 0 419.3 0 384V176L217.6 339.2z" />
                                                        </svg>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ($checkout['support_whatsapp'] !== "") : ?>
                                                    <a id="whatsapp-link" href="https://api.whatsapp.com/send?phone=55<?= str_replace([" ", "-", "(", ")"], "", $checkout['support_whatsapp']) ?>" title="WhastApp">
                                                        <svg style="width:25px;" xmlns="http://www.w3.org/2000/svg" fill="#1c3c2d" viewBox="0 0 448 512">
                                                            <path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z" />
                                                        </svg>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($urlMobileImage) : ?>
                <div class="col-lg-4 col-md-12 col-sm-12 col-12  d-lg-none text-center p-0">
                    <img class="img-fluid" src="<?= $urlMobileImage ?>" style="border-top-left-radius: 15px;border-top-right-radius: 15px;" />
                </div>
            <?php endif; ?>

            <?php if (!$urlMobileImage && $urlImage) : ?>
                <div class="col-lg-4 col-md-12 col-sm-12 col-12 d-lg-none text-center p-0" style="margin-left:-0px;">
                    <img class="img-fluid" src="<?= $urlImage ?>" style="border-bottom-left-radius: 15px;border-bottom-right-radius: 15px; height: calc(100% - 1.875rem);" />
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($urlImage) : ?>
        <div class="col-lg-4 col-md-12 col-sm-12 col-12 mt-5 d-none d-lg-block text-center p-0">
            <img class="img-fluid" src="<?= $urlImage ?>" style="border-bottom-left-radius: 15px;border-bottom-right-radius: 15px; height: calc(100% - 1.875rem);" />
        </div>
    <?php endif; ?>


    <!-- NOTIFICAÇÕES PERSONALIZADAS -->
    <?php
    $sumAllNotify = 0; // soma de todas as notificações que são por padrão "0" se for 0 logo não existe notificação para essa oferta '-'
    $sumAllNotify = $checkout['checkout_not1'] + $checkout['checkout_not2'] + $checkout['checkout_not3'] + $checkout['checkout_not4'] + $checkout['checkout_not5'];
    ?>

    <input type="hidden" id="notifyActive" value="<?= $sumAllNotify ?>">
    <input type="hidden" id="nameOfProduct" value="<?= $product_name ?>">

    <input type="hidden" id="checkout_not1" value="<?= $checkout['checkout_not1'] ?>">
    <input type="hidden" id="checkout_not2" value="<?= $checkout['checkout_not2'] ?>">
    <input type="hidden" id="checkout_not3" value="<?= $checkout['checkout_not3'] ?>">
    <input type="hidden" id="checkout_not4" value="<?= $checkout['checkout_not4'] ?>">
    <input type="hidden" id="checkout_not5" value="<?= $checkout['checkout_not5'] ?>">

    <input type="hidden" id="checkout_minQtd1" value="<?= $checkout['checkout_min_quantity1'] ?>">
    <input type="hidden" id="checkout_minQtd2" value="<?= $checkout['checkout_min_quantity2'] ?>">
    <input type="hidden" id="checkout_minQtd3" value="<?= $checkout['checkout_min_quantity3'] ?>">
    <input type="hidden" id="checkout_minQtd4" value="<?= $checkout['checkout_min_quantity4'] ?>">
    <input type="hidden" id="checkout_minQtd5" value="<?= $checkout['checkout_min_quantity5'] ?>">

</div>

<?php require_once(dirname(__FILE__) . '/../../includes/layout/fullwidth/fullwidth-footer.php'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js" integrity="sha512-bPs7Ae6pVvhOSiIcyUClR7/q2OAsRiovw4vAkX+zJbw3ShAeeqezq50RIIcIURq7Oa20rW2n2q+fyXBNcU9lrw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flexslider/2.7.2/jquery.flexslider-min.js" integrity="sha512-BmoWLYENsSaAfQfHszJM7cLiy9Ml4I0n1YtBQKfx8PaYpZ3SoTXfj3YiDNn0GAdveOCNbK8WqQQYaSb0CMjTHQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    $(document).ready(function() {
        $('.sync1').owlCarousel({
            items: 1,
            nav: true,
            loop: true,
            margin: 10,
            autoplay: true,
            autoplaySpeed: 3000,
            responsiveRefreshRate: true,
            responsiveClass: true,
            navText: ['<i class="catch fa fa-angle-right"></i>', '<i class="catch fa fa-angle-left"></i>'],
        })
    });


    $(document).ready(function($) {
        $('.cpfcnpj').mask('000.000.000-00', {
            onKeyPress: function(cpfcnpj, e, field, options) {
                const masks = ['000.000.000-000', '00.000.000/0000-00'];
                const mask = (cpfcnpj.length > 14) ? masks[1] : masks[0];
                $('.cpfcnpj').mask(mask, options);
            }
        })
    });

    $(window).on('load', function() {
        const timer = $('#timer').html();
        const minutes_count = (timer.split(':'))[0];
        const seconds_count = (timer.split(':'))[1];

        var dt = new Date();
        dt.setMinutes(dt.getMinutes() + parseInt(minutes_count));
        dt.setSeconds(dt.getSeconds() + parseInt(seconds_count));

        // Set the date we're counting down to
        var countDownDate = new Date(dt).getTime();

        // Update the count down every 1 second
        var x = setInterval(function() {

            // Get today's date and time
            var now = new Date().getTime();

            // Find the distance between now and the count down date
            var distance = countDownDate - now;

            // Time calculations for days, hours, minutes and seconds
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);


            if (distance > 0) {
                $('#timer').html(("0000" + minutes).slice(-2) + ":" + ("0000" + seconds).slice(-2));
            } else {
                $('#timer').html("00:00");
            }

        }, 1000);
    });

    const totalNotify = $('#notifyActive').val();

    if (totalNotify > 0) {

        const nameOfProduct = $('#nameOfProduct').val();

        let arrayNotification = [];
        const arrayNotificationValues = {
            thisMoment: {
                value: $('#checkout_not1').val(),
                minQtd: $('#checkout_minQtd1').val(),
            },
            thisWeek: {
                value: $('#checkout_not2').val(),
                minQtd: $('#checkout_minQtd3').val()
            },
            last30M: {
                value: $('#checkout_not3').val(),
                minQtd: $('#checkout_minQtd4').val()
            },
            today: {
                value: $('#checkout_not4').val(),
                minQtd: $('#checkout_minQtd4').val()
            },
            lastHour: {
                value: $('#checkout_not5').val(),
                minQtd: $('#checkout_minQtd5').val()
            }
        };


        Object.keys(arrayNotificationValues).forEach((key, index) => {
            if (arrayNotificationValues[key].value == 1) {
                arrayNotification.push({
                    key: {
                        'value': arrayNotificationValues[key].value,
                        'firstTextNotification': firstTextNotification(key),
                        'lastTextNotification': lastTextNotify(key),
                        'minimumQuantity': arrayNotificationValues[key].minQtd
                    }
                });
            }
        })



        let numberNotification = 0;
        // incrementar valor no quantidade minima 
        let actualMinimumQuantity = Object.values(arrayNotification)[0].key.minimumQuantity;

        setTimeout(() => notify(Object.values(arrayNotification)[0], 2000, nameOfProduct, 0, actualMinimumQuantity), 1000); // [0]

        Object.values(arrayNotification)[0].key.minimumQuantity = parseInt(actualMinimumQuantity) + 1;

        setInterval(() => {
            ++numberNotification;
            if (numberNotification > Object.values(arrayNotification).length - 1) {
                numberNotification = 0
            }

            // incrementar valor no quantidade minima 
            let actualMinimumQuantity = Object.values(arrayNotification)[numberNotification].key.minimumQuantity;
            Object.values(arrayNotification)[numberNotification].key.minimumQuantity = parseInt(actualMinimumQuantity) + 1;

            notify(Object.values(arrayNotification)[numberNotification], 5500, nameOfProduct, numberNotification, actualMinimumQuantity);

        }, 10000);

    }

    function firstTextNotification(keyObject) {
        switch (keyObject) {
            case 'thisMoment':
                return 'Pessoas estão comprando'
                break;
            case 'thisWeek':
                return 'Pessoas compraram'
                break;
            case 'last30M':
                return 'Pessoas compraram'
                break;
            case 'today':
                return 'Pessoas compraram'
                break;
            case 'lastHour':
                return 'Pessoas compraram'
                break;
        }
    }

    function lastTextNotify(keyObject) {
        switch (keyObject) {
            case 'thisMoment':
                return 'nesse momento.'
                break;
            case 'thisWeek':
                return 'essa semana.'
                break;
            case 'last30M':
                return 'nos ultimos 30 minutos.'
                break;
            case 'today':
                return 'hoje.'
                break;
            case 'lastHour':
                return 'na ultima hora.'
                break;
        }
    }


    function notify(ArrayNotifications, timeout, nameOfProduct = "", indexOfNotify = 0, actualMinimumQuantity = 1) {

        let randomNumber = actualMinimumQuantity

        const textNotification = randomNumber + " " + ArrayNotifications.key.firstTextNotification + " " + nameOfProduct + " " + ArrayNotifications.key.lastTextNotification;
        toastr.clear();
        toastr.success(textNotification, "Notificação", {
            timeOut: timeout,
            iconv: false,
            closeButton: !0,
            debug: !1,
            newestOnTop: !0,
            progressBar: 0,
            positionClass: "toast-bottom-right",
            preventDuplicates: 1,
            onclick: null,
            showDuration: "400",
            hideDuration: "1000",
            extendedTimeOut: "1000",
            showEasing: "swing",
            hideEasing: "linear",
            showMethod: "fadeIn",
            hideMethod: "fadeOut",
            tapToDismiss: !0,
            iconClasses: {
                success: 'fa-solid fa-cart-shopping',
            },
        });

    }
</script>