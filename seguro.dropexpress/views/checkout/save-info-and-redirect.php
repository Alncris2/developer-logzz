<?php
error_reporting(-1);            
ini_set('display_errors', 1);
require_once (dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start(); 

    # Verifica se recebeu as variáveis via GET
    if (!(isset($_GET['a'])) || !(isset($_GET['p']))){
        header("Location: ../pagina-nao-encontrada");
        exit;
    }

    # Trata as variáveis recebidas
    $product_id = $_GET['p'];
    if (!preg_match("/^[(0-9)]*$/", $product_id)) {
        exit;
    }

    $membership_hotcode = $_GET['a'];
    if (!preg_match("/^[(A-Z)]*$/", $membership_hotcode)) {
        exit;
    }

    $page_id = $_GET['ps'];
    if (!preg_match("/^[(0-9)]*$/", $page_id)) {
        exit;
    }

    # Busca as informações do produto.
    $product_data = $conn->prepare('SELECT product_name, user__id, product_code, product_price, product_description, product_image, product_id, product_cookie_time, product_membership_type, product_sale_page FROM products WHERE product_id = :product_id');
    $product_data->execute(array('product_id' =>  $product_id));

    while($row = $product_data->fetch()) {
        $product_name = $row['product_name'];
        $product_code = $row['product_code'];
        $product_id = $row['product_id'];
        $product_cookie_time = $row['product_cookie_time'];
        $product_membership_type = $row['product_membership_type'];
        $product_redirect_link = $row['product_sale_page'];
    }  

    $stmt = $conn->prepare('SELECT page_url FROM pages_sales WHERE (page_product_id = :product_id AND page_id = :page_id)');
    $stmt->execute(array('product_id' => $product_id, 'page_id' => $page_id ));
    if($page = $stmt->fetch()) {
        $product_redirect_link = $page['page_url'];
    }

    $arr_cookie_options = array (
        'expires' => time() + ($product_cookie_time * 86400),
        'path' => '/',
        'domain' => ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false, // leading dot for compatibility or use subdomain $_SERVER['']
        'secure' => true,     // or false
    );
 
    # Cria cookie para saber quantas vezes a pessoa entrou nesse produto 
    $cookie_amount_come= '_acid' . $product_code;    
    if(isset($_COOKIE[$cookie_amount_come])){ 
        # Atualiza os COOKIES.   
        $amount_come = $_COOKIE[$cookie_amount_come];
        $_SESSION[$cookie_amount_come] = $_SESSION[$cookie_amount_come] + 1;       
        setcookie($cookie_amount_come, $_COOKIE[$cookie_amount_come] + 1, $arr_cookie_options);
    } else {
        # Cria os COOKIES.  
        $_SESSION[$cookie_amount_come] = 1;      
        setcookie($cookie_amount_come, 1, $arr_cookie_options);
    }
 
    $cookie_name = '_mbsid' . $product_code;    
    # Verifica se a comissão é por Primeiro Clique
    if ($product_membership_type == 'primeiroclique') {

        # Se for por primeiro clique,
        # verifica se já há um cookie anteriormente instalado.
        if (isset($_COOKIE[$cookie_name])) {
            # Se houver um cookie anterior, atribui a comissão
            # ao afiliado a quem ele pertence.
            $_SESSION[$cookie_name] = $_COOKIE[$cookie_name];
        } else {
            # Se não houver um cookie
            # cria os cookies e atribui a comissão ao afiliado atual (a aquem pertence o link).
            $_SESSION[$cookie_name] = $membership_hotcode;
            setcookie($cookie_name, $membership_hotcode, $arr_cookie_options);
        }
    }
        # Se a comissão não for por primeiro clique (for por último),
        # cria os cookies e atribui a comissão ao afiliado atual (a aquem pertence o link).
    else {
        
        # Cria os COOKIES.
        $_SESSION[$cookie_name] = $membership_hotcode;
        setcookie($cookie_name, $membership_hotcode, $arr_cookie_options); 
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

    if (!(empty($fb_pixel))) {
        echo "<script>
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
          </script>
          <noscript> 
          <img height='1' width='1' style='display:none' src='https://www.facebook.com/tr?id=" . $fb_pixel . "&ev=PageView&noscript=1'/>
          </noscript>";
    }

	if(isset($google_ua) && !(empty($google_ua))){
		echo '
		<!-- Global site tag (gtag.js) - Google Analytics -->
		<script async src="https://www.googletagmanager.com/gtag/js?id=' . $google_ua . '"></script>
		<script>
			window.dataLayer = window.dataLayer || [];
			function gtag(){dataLayer.push(arguments);}
			gtag(' . "'js'" .', new Date());

			gtag(' . "'config'" .",'" . $google_ua . "'" . ');
		</script>';
	}

	if(isset($google_aw) && !(empty($google_aw))){
		echo '
		<!-- Global site tag (gtag.js) - Google Ads: ' . $google_aw . ' -->
		<script async src="https://www.googletagmanager.com/gtag/js?id=' . $google_aw . '"></script>
		<script>
			window.dataLayer = window.dataLayer || [];
			function gtag(){dataLayer.push(arguments);}
			gtag(' . "'js'" .', new Date());

			gtag(' . "'config'" .",'" . $google_aw . "'" . ');
		</script>';
	}

	if(isset($tiktok_pixel) && !(empty($tiktok_pixel))){
		echo '
		<script>
		!function (w, d, t) {
		  w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++
        )ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};n=document.createElement("script");n.type="text/javascript",n.async=!0,n.src=i+"?sdkid="+e+"&lib="+t;e=document.getElementsByTagName("script")[0];e.parentNode.insertBefore(n,e)};
            
            ttq.load(' . "'" . $tiktok_pixel . "'" . ');
            ttq.page();
            }(window, document, ' . "'" . "ttq" . "'" . ');
        </script>';
	}

    header("Location: ". $product_redirect_link);   
     
?>  