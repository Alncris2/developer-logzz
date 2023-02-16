<?php

require dirname(__FILE__) . "/../includes/config.php";

session_name(SESSION_NAME);
session_start();

# Verifica o envio do form via POST
if (isset($_POST['action']) && $_POST['action'] == 'update-membership-config') {

    # Variáveis internas
    $product_id = addslashes($_POST['produto']);

    # Recebe a trata os inputs
    if (isset($_POST['disponivel-afiliacao']) && $_POST['disponivel-afiliacao'] == 'sim') {

        $product_membership_available = addslashes($_POST['disponivel-afiliacao']);
        if (!preg_match("/^[a-z]*$/", $product_membership_available)) {
            $feedback = array('status' => 0, 'msg' => 'Confira os dados do produto. [0]', 'title' => "Algo está errado");
            echo json_encode($feedback);
            exit;
        }

        $product_shop_visibility = addslashes($_POST['visivel-afiliacao']);
        if (!preg_match("/^[a-z]*$/", $product_shop_visibility)) {
            $feedback = array('status' => 0, 'msg' => 'Confira os dados do produto. [1]', 'title' => "Algo está errado");
            echo json_encode($feedback);
            exit;
        }

        $product_commission = addslashes($_POST['comissao-produto']);
        if (!preg_match("/^[0-9]*$/", $product_commission)) {
            $feedback = array('status' => 0, 'msg' => 'Confira os dados do produto. [2]', 'title' => "Algo está errado");
            echo json_encode($feedback);
            exit;
        }

        $product_auto_membership = addslashes($_POST['afiliacao-automatica']);
        if (!preg_match("/^[a-z]*$/", $product_auto_membership)) {
            $feedback = array('status' => 0, 'msg' => 'Confira os dados do produto. [3]', 'title' => "Algo está errado");
            echo json_encode($feedback);
            exit;
        }

        $product_cookie_time = addslashes($_POST['tempo-cookie-produto']);
        if (!preg_match("/^[0-9]*$/", $product_cookie_time)) {
            $feedback = array('status' => 0, 'msg' => 'Confira os dados do produto. [4]', 'title' => "Algo está errado");
            echo json_encode($feedback);
            exit;
        }

        $product_membership_type = addslashes($_POST['tipo-afiliacao']);
        if (!preg_match("/^[a-z]*$/", $product_membership_type)) {
            $feedback = array('status' => 0, 'msg' => 'Confira os dados do produto. [5]', 'title' => "Algo está errado");
            echo json_encode($feedback);
            exit;
        }
    } else {
        $product_membership_available = 'nao';
        $product_shop_visibility = 'nao';
        $product_commission = 0;
        $product_cookie_time = 0;
        $product_auto_membership = 'nao';
        $product_membership_type = 'nenhum';
    }



    # Prepara a query para atualizar as infos de afiliação do produto.
    $stmt = $conn->prepare('UPDATE products SET product_membership_available = :product_membership_available, product_shop_visibility = :product_shop_visibility, product_commission = :product_commission, product_auto_membership = :product_auto_membership, product_membership_type = :product_membership_type, product_cookie_time = :product_cookie_time WHERE product_id = :product_id');

    try {
        $stmt->execute(array('product_id' => $product_id, 'product_membership_available' => $product_membership_available, 'product_shop_visibility' => $product_shop_visibility, 'product_commission' => $product_commission, 'product_auto_membership' => $product_auto_membership, 'product_membership_type' => $product_membership_type, 'product_cookie_time' => $product_cookie_time));

        $url = SERVER_URI . "/produto/" . $product_id;

        $feedback = array('status' => 1, 'title' => 'Configurações Atualizadas!', 'product_id' => $product_id, 'url' => $url);

        # Atualiza os valores de COMMISSION_MAX e COMMISSION_MIN do produto.
        $max_commission = $conn->prepare('SELECT MAX(sale_price) FROM sales WHERE product_id = :product_id AND (sale_shop_visibility = 1 AND sale_trashed = 0)');
        $max_commission->execute(array('product_id' => $product_id));
        $max_commission = $max_commission->fetch();

        if ($max_commission[0] == null) {
            $product_max_price = 0;
        } else {
            $product_max_price = $max_commission[0];
        }

        $min_commission = $conn->prepare('SELECT MIN(sale_price) FROM sales WHERE product_id = :product_id AND (sale_shop_visibility = 1 AND sale_trashed = 0)');
        $min_commission->execute(array('product_id' => $product_id));
        $min_commission = $min_commission->fetch();
        $product_min_price = $min_commission[0];

        if ($max_commission[0] == null) {
            $product_min_price = 0;
        } else {
            $product_min_price = $min_commission[0];
        }


        if ($min_commission > 0 and $max_commission > 0) {
            $update_comissiona_range = $conn->prepare('UPDATE products SET product_max_price = :product_max_price, product_min_price = :product_min_price WHERE product_id = :product_id');
            $update_comissiona_range->execute(array('product_max_price' => $product_max_price, 'product_min_price' => $product_min_price, 'product_id' => $product_id));
        }
    } catch (PDOException $e) {
        $error = 'ERROR: ' . $e->getMessage();
        $feedback = array('status' => '0', 'msg' => $error);
    }

    echo json_encode($feedback);
} else {
    $feedback = array('status' => 0, 'msg' => 'Algo está errado! Atualize a página e tente novamente.');
    echo json_encode($feedback);
    exit;
}

?>