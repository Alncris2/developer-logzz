<?php
error_reporting(-1);            
ini_set('display_errors', 1); 
require dirname(__FILE__) . "/../includes/config.php";
session_name(SESSION_NAME);
session_start();

# Verifica se o envio do form foi via POST
if (!(isset($_POST['action']))){
    $feedback = array('title' => "Erro", 'type' => 'warning', 'msg' => 'Experimente atualizar a página e reiniciar a criação da oferta.', 'title' => "Erro de Comunicação");
    echo json_encode($feedback);
    exit;
}

# Variáveis internas
$user__id = $_SESSION['UserID'];

# Recebe a trata os inputs
$product_id = addslashes($_POST['product']);
if (!preg_match("/^[(0-9) ]*$/", $product_id)) {
    $feedback = array('title' => "Erro", 'type' => 'warning', 'msg' => 'Experimente atualizar a página e reiniciar a criação da oferta.', 'title' => "Erro de Comunicação");
    echo json_encode($feedback);
    exit;
}

$page_id = addslashes($_POST['page']);
if (!preg_match("/^[(0-9) ]*$/", $page_id)) {
    $feedback = array('title' => "Erro", 'type' => 'warning', 'msg' => 'Experimente atualizar a página e reiniciar a criação da oferta.', 'title' => "Erro de Comunicação");
    echo json_encode($feedback);
    exit;
}

$membership_hotcode = addslashes($_POST['membership']);
if (!preg_match("/^[0-9A-Za-z' ]*$/", $membership_hotcode)) {
    $feedback = array('title' => "Erro", 'type' => 'warning', 'msg' => 'Experimente atualizar a página e reiniciar a criação da oferta.', 'title' => "Erro de Comunicação");
    echo json_encode($feedback);
    exit;
}

$facebook_pixel = addslashes($_POST['meta-pixel-facebook']);
if (!(empty($_POST['meta-pixel-facebook'])) && !preg_match("/^[0-9A-Za-z']*$/", $facebook_pixel)) {
    $feedback = array('title' => "Confira os dados", 'type' => 'warning', 'msg' => 'O Facebook Pixel que você informou é inválido.');
    echo json_encode($feedback);
    exit;
}

$facebook_api = addslashes($_POST['meta-pixel-facebook-api']);
if (!(empty($facebook_api)) && !preg_match("/^[0-9A-Za-z']*$/", $facebook_api)) {
    $feedback = array('title' => "Confira os dados", 'type' => 'warning', 'msg' => 'O Token da API de Conversões do Facebook que você informou tem caracteres inválidos.');
    echo json_encode($feedback);
    exit;
}

$google_ua = addslashes($_POST['meta-google-analytics']);
if (!(empty($google_ua)) && !preg_match("/^[0-9A-Za-z-']*$/", $google_ua)) {
    $feedback = array('title' => "Confira os dados", 'type' => 'warning', 'msg' => 'O ID do Google Analytics que você informou tem caracteres inválidos.');
    echo json_encode($feedback);
    exit;
}

$google_aw = addslashes($_POST['meta-google-ads-id']);
if (!(empty($google_aw)) && !preg_match("/^[0-9A-Za-z-']*$/", $google_aw)) {
    $feedback = array('title' => "Confira os dados", 'type' => 'warning', 'msg' => 'O ID de Conversão Google Ads que você informou tem caracteres inválidos.');
    echo json_encode($feedback);
    exit;
}

$tiktok_pixel = addslashes($_POST['meta-tiktok-pixel']);
if (!(empty($tiktok_pixel)) && !preg_match("/^[0-9A-Za-z-']*$/", $tiktok_pixel)) {
    $feedback = array('title' => "Confira os dados", 'type' => 'warning', 'msg' => 'Tiktok Pixel que você informou é inválido.');
    echo json_encode($feedback);
    exit;
}

try {

    $get_membership_meta_pixel = $conn->prepare('SELECT meta_id FROM memberships_meta WHERE (membership_hotcode = :membership_hotcode AND product_id = :product_id AND meta_page = :meta_page) AND meta_key = "fb_pixel"');
    $get_membership_meta_pixel->execute(array('product_id' => $product_id, 'membership_hotcode' => $membership_hotcode, 'meta_page' => $page_id));
    if ($get_membership_meta_pixel->rowCount() == 0) {
        $set_membership_meta_pixel = $conn->prepare('INSERT INTO memberships_meta (meta_id, membership_hotcode, product_id, meta_key, meta_value, sale_id, meta_page) VALUES (:meta_id, :membership_hotcode, :product_id, "fb_pixel", :meta_value, 0, :meta_page)');
        $set_membership_meta_pixel->execute(array('meta_id' => 0, 'membership_hotcode' => $membership_hotcode, 'product_id' => $product_id, 'meta_value' => $facebook_pixel, 'meta_page' => $page_id));
    } else {
        $meta_id_pixel = $get_membership_meta_pixel->fetch();
        $meta_id_pixel = $meta_id_pixel['meta_id'];
        $set_membership_meta_pixel = $conn->prepare('UPDATE memberships_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
        $set_membership_meta_pixel->execute(array('meta_value' => $facebook_pixel, 'meta_id' => $meta_id_pixel));
    }

    $get_membership_meta_fb_capi = $conn->prepare('SELECT meta_id FROM memberships_meta WHERE (membership_hotcode = :membership_hotcode AND product_id = :product_id AND meta_page = :meta_page) AND meta_key = "fb_capi"');
    $get_membership_meta_fb_capi->execute(array('product_id' => $product_id, 'membership_hotcode' => $membership_hotcode, 'meta_page' => $page_id));
    if ($get_membership_meta_fb_capi->rowCount() == 0) {
        $set_membership_meta_fb_capi = $conn->prepare('INSERT INTO memberships_meta (meta_id, membership_hotcode, product_id, meta_key, meta_value, sale_id, meta_page) VALUES (:meta_id, :membership_hotcode, :product_id, "fb_capi", :meta_value, 0, :meta_page)');
        $set_membership_meta_fb_capi->execute(array('meta_id' => 0, 'membership_hotcode' => $membership_hotcode, 'product_id' => $product_id, 'meta_value' => $facebook_api, 'meta_page' => $page_id));
    } else {
        $meta_id_fb_capi = $get_membership_meta_fb_capi->fetch();
        $meta_id_fb_capi = $meta_id_fb_capi['meta_id'];
        $set_membership_meta_fb_capi = $conn->prepare('UPDATE memberships_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
        $set_membership_meta_fb_capi->execute(array('meta_value' => $facebook_api, 'meta_id' => $meta_id_fb_capi));
    }

    $get_membership_meta_google_ua = $conn->prepare('SELECT meta_id FROM memberships_meta WHERE (membership_hotcode = :membership_hotcode AND product_id = :product_id AND meta_page = :meta_page) AND meta_key = "google_ua"');
    $get_membership_meta_google_ua->execute(array('product_id' => $product_id, 'membership_hotcode' => $membership_hotcode, 'meta_page' => $page_id));
    if ($get_membership_meta_google_ua->rowCount() == 0) {
        $set_membership_meta_google_ua = $conn->prepare('INSERT INTO memberships_meta (meta_id, membership_hotcode, product_id, meta_key, meta_value, sale_id, meta_page) VALUES (:meta_id, :membership_hotcode, :product_id, "google_ua", :meta_value, 0, :meta_page)');
        $set_membership_meta_google_ua->execute(array('meta_id' => 0, 'membership_hotcode' => $membership_hotcode, 'product_id' => $product_id, 'meta_value' => $google_ua, 'meta_page' => $page_id));
    } else {
        $meta_id_google_ua = $get_membership_meta_google_ua->fetch();
        $meta_id_google_ua = $meta_id_google_ua['meta_id'];
        $set_membership_meta_google_ua = $conn->prepare('UPDATE memberships_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
        $set_membership_meta_google_ua->execute(array('meta_value' => $google_ua, 'meta_id' => $meta_id_google_ua));
    }

    $get_membership_meta_google_aw = $conn->prepare('SELECT meta_id FROM memberships_meta WHERE (membership_hotcode = :membership_hotcode AND product_id = :product_id AND meta_page = :meta_page) AND meta_key = "google_aw"');
    $get_membership_meta_google_aw->execute(array('product_id' => $product_id, 'membership_hotcode' => $membership_hotcode, 'meta_page' => $page_id));
    if ($get_membership_meta_google_aw->rowCount() == 0) {
        $set_membership_meta_google_aw = $conn->prepare('INSERT INTO memberships_meta (meta_id, membership_hotcode, product_id, meta_key, meta_value, sale_id, meta_page) VALUES (:meta_id, :membership_hotcode, :product_id, "google_aw", :meta_value, 0, :meta_page)');
        $set_membership_meta_google_aw->execute(array('meta_id' => 0, 'membership_hotcode' => $membership_hotcode, 'product_id' => $product_id, 'meta_value' => $google_aw, 'meta_page' => $page_id));
    } else {
        $meta_id_google_aw = $get_membership_meta_google_aw->fetch();
        $meta_id_google_aw = $meta_id_google_aw['meta_id'];
        $set_membership_meta_google_aw = $conn->prepare('UPDATE memberships_meta SET meta_value = :meta_value WHERE meta_id = :meta_id'); 
        $set_membership_meta_google_aw->execute(array('meta_value' => $google_aw, 'meta_id' => $meta_id_google_aw));
    }

    $get_membership_meta_tiktok_pixel = $conn->prepare('SELECT meta_id FROM memberships_meta WHERE (membership_hotcode = :membership_hotcode AND product_id = :product_id AND meta_page = :meta_page) AND meta_key = "tiktok_pixel"');
    $get_membership_meta_tiktok_pixel->execute(array('product_id' => $product_id, 'membership_hotcode' => $membership_hotcode, 'meta_page' => $page_id));
    if ($get_membership_meta_tiktok_pixel->rowCount() == 0) {
        $set_membership_meta_tiktok_pixel = $conn->prepare('INSERT INTO memberships_meta (meta_id, membership_hotcode, product_id, meta_key, meta_value, sale_id, meta_page) VALUES (:meta_id, :membership_hotcode, :product_id, "tiktok_pixel", :meta_value, 0, :meta_page)');
        $set_membership_meta_tiktok_pixel->execute(array('meta_id' => 0, 'membership_hotcode' => $membership_hotcode, 'product_id' => $product_id, 'meta_value' => $tiktok_pixel, 'meta_page' => $page_id));
    } else {
        $meta_id_tiktok_pixel = $get_membership_meta_tiktok_pixel->fetch();
        $meta_id_tiktok_pixel = $meta_id_tiktok_pixel['meta_id'];
        $set_membership_meta_tiktok_pixel = $conn->prepare('UPDATE memberships_meta SET meta_value = :meta_value WHERE meta_id = :meta_id');
        $set_membership_meta_tiktok_pixel->execute(array('meta_value' => $tiktok_pixel, 'meta_id' => $meta_id_tiktok_pixel));
    }

    $feedback = array('title' => "Informações Atualizadas", 'type' => 'success',);
    echo json_encode($feedback);
    exit;

} catch (PDOException $e) {

    $error = 'ERROR: ' . $e->getMessage();
    $feedback = array('title' => "Erro Interno", 'type' => 'warning', 'msg' => 'Não foi possível atualizar os dados', 'error' => $error); 
    echo json_encode($feedback);
    exit;

}


