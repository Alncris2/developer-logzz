<?php

require_once(dirname(__FILE__) . '/../includes/config.php');
require (dirname(__FILE__) . '/../includes/classes/RandomStrGenerator.php');
session_name(SESSION_NAME);
session_start();

# Verifica o recebimento do ID via GET
if (!(isset($_GET['id']))){
    exit;
}
    
    $product_id = trim($_GET['id']);
    if (!preg_match("/^[(0-9)]*$/", $product_id)) {
        $feedback = array('type' => 'warning', 'status' => 0, 'title' => 'Há algo errado aqui...', 'msg' => "Experimente atualizar a página e tentar novamente.");
        echo json_encode($feedback);
        exit;
    }
    
    # Variáveis Internas
    $membership_id              = 0;
    $membership_affiliate_id    = $_SESSION['UserID'];
    $membership_product_id      = $product_id;

    # Verifica se a afiliação automática está ativada.
    $get_product_info = $conn->prepare("SELECT product_auto_membership, user__id FROM products WHERE product_id = :product_id");
    $get_product_info->execute(array('product_id' => $product_id));
    $product_info = $get_product_info->fetch();
    $membership_producer_id = $product_info['user__id'];

    # Verifica se Produtor e Afiliado são o mesmo usuário
    if ($product_info['user__id'] == $membership_affiliate_id) {
        $feedback = array('type' => 'warning', 'status' => 0, 'title' => 'Este produto é seu', 'msg' => "Você não pode se afiliar a um produto que você é o produtor.");
        echo json_encode($feedback);
        exit;
    }

    # membership_id, membership_affiliate_id, membership_producer_id, membership_product_id, memberships_hotcode, membership_status

    # Verifica já existe uma afiliação ATIVA ou PENDENTE do mesmo usuário para o mesmo produto
    $verify_membership = $conn->prepare("SELECT membership_id, membership_status FROM memberships WHERE membership_product_id = :membership_product_id AND membership_affiliate_id = :membership_affiliate_id");
    $verify_membership->execute(array('membership_product_id' => $membership_product_id, 'membership_affiliate_id' => $membership_affiliate_id));

    if ($verify_membership->rowCount() > 0) {
        $membership_info = $verify_membership->fetch();

        if ($membership_info['membership_status'] == "ATIVA"){
            $title = "Afiliação Indisponível";
            $msg = "Você já é Afiliado deste produto.";
        } else if ($membership_info['membership_status'] == "PENDENTE") {
            $title = "Afiliação Indisponível";
            $msg = "Você já tem uma solicitação de afiliação aguardando aprovação do produtor.";
        } else {
            $title = "Afiliação Indisponível";
            $msg = "Você não pode se afiliar a este produto no momento.";
        }

        $feedback = array('type' => 'warning', 'title' => $title, 'msg' => $msg);
        echo json_encode($feedback);
        exit;
    }

    # Define o status da nova afiliação
    if ($product_info['product_auto_membership'] == 'sim'){
        $membership_status = 'ATIVA';
        $title = "Solicitação Aprovada!";
        $msg = "Você já pode começar a promover este produto.";
        $url = SERVER_URI . "/produtos/afiliacoes/";
    } else {
        $membership_status =  'PENDENTE';
        $title = "Solicitação Enviada!";
        $msg = "Você receberá um email quando o produtor aceitar.";
        $url = SERVER_URI . "/produtos/solicitacoes/";
    }
    
    # Gera o Hotcode de 6 caracteres do Afiliado
    $memberships_hotcode = new RandomStrGenerator();
    $memberships_hotcode = strtoupper($memberships_hotcode->onlyLetters(6));

    $verify_unique_hotcode = $conn->prepare('SELECT * FROM memberships WHERE memberships_hotcode = :memberships_hotcode');
    $verify_unique_hotcode->execute(array('memberships_hotcode' => $memberships_hotcode));

    # Verifica se o HotCode é único
    if (!($verify_unique_hotcode->rowCount() == 0)) {
        do {
            $memberships_hotcode = new RandomStrGenerator();
            $memberships_hotcode = $memberships_hotcode->onlyLetters(6);

            $verify_unique_hotcode = $conn->prepare('SELECT * FROM memberships WHERE memberships_hotcode = :memberships_hotcode');
            $verify_unique_hotcode->execute(array('memberships_hotcode' => $memberships_hotcode));
        } while ($stmt->rowCount() != 0);
    }

    # Cria a Afiliação
    $new_membership = $conn->prepare("INSERT INTO memberships (membership_id, membership_affiliate_id, membership_producer_id, membership_product_id, memberships_hotcode, membership_status) VALUES (:membership_id, :membership_affiliate_id, :membership_producer_id, :membership_product_id, :memberships_hotcode, :membership_status)");

    $new_membership->execute(array('membership_id' => $membership_id, 'membership_affiliate_id' => $membership_affiliate_id, 'membership_producer_id' => $membership_producer_id, 'membership_product_id' => $membership_product_id, 'memberships_hotcode' => $memberships_hotcode, 'membership_status' => $membership_status));

    $feedback = array('type' => 'success', 'msg' => $msg, 'title' => $title, 'url' => $url);
	echo json_encode($feedback);
	exit;

?>