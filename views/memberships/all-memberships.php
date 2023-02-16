<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
include(dirname(__FILE__) . '/../../includes/classes/StarRating.php');
session_name(SESSION_NAME);
session_start();

# Verifica se o usuário está logado
if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}

$page_title = "Minhas Afiliações | Logzz";
$has_submenu_product = 'active';
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');
?>
<div class="container-fluid">
    <div class="row">
        <?php
        # Armazena o ID do usuário logado.
        $membership_affiliate_id = $_SESSION['UserID'];

        # Busca as afiliações pendentes no banco de dados.
        $get_active_memberships = $conn->prepare("SELECT * FROM memberships as m INNER JOIN products as p ON m.membership_product_id = p.product_id WHERE m.membership_affiliate_id = :membership_affiliate_id AND m.membership_status = 'ATIVA' AND p.product_membership_available = 'sim'");
        $get_active_memberships->execute(array('membership_affiliate_id' => $membership_affiliate_id));

        # Lista as afiliações.
        if ($get_active_memberships->rowCount() != 0) {
            while ($row = $get_active_memberships->fetch()) {
                $product_id = $row['product_id'];
                $product_name = $row['product_name'];
                $product_price = $row['product_price'];
                $product_image = $row['product_image'];
                $product_rating = $row['product_rating'];
                $product_max_commission = $row['product_max_price'] * ($row['product_commission'] / 100);
                $memberships_hotcode = $row['memberships_hotcode'];
                $product_code = $row['product_code'];

        ?>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="new-arrival-product">
                                <div class="new-arrivals-img-contnent" style="max-height: 200px;">
                                    <img class="img-fluid" src="<?php echo SERVER_URI . '/uploads/imagens/produtos/' . $product_image; ?>" alt="">
                                </div>
                                <div class="new-arrival-content text-center mt-3">
                                    <h4><a href="<?php echo SERVER_URI; ?>/produtos/afiliacoes/<?php echo $memberships_hotcode . "/" . $product_id; ?>"><?php echo $product_name; ?></a></h4>
                                    <p>Produto: <span class="item"><?php echo $product_code; ?></span> </p>
                                    <ul class="star-rating">
                                        <?php
                                        $rate = new StarRating();
                                        $stars = $rate->markupFromRate($product_rating);
                                        ?>
                                        <li><i class="<?php echo $stars[0]; ?>"></i></li>
                                        <li><i class="<?php echo $stars[1]; ?>"></i></li>
                                        <li><i class="<?php echo $stars[2]; ?>"></i></li>
                                        <li><i class="<?php echo $stars[3]; ?>"></i></i></i></li>
                                        <li><i class="<?php echo $stars[4]; ?>"></i></i></i></li>
                                    </ul>
                                    <span class="text-center text-muted"><small>Comissão de até</small><br></span>
                                    <span class="price">R$ <?php echo number_format($product_max_commission, 2, ',', ''); ?></p></span>
                                    <a href="<?php echo SERVER_URI; ?>/produtos/afiliacoes/<?php echo $memberships_hotcode . "/" . $product_id; ?>" type="button" class="btn btn-block btn-rounded btn-outline-success">Obter Links</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            }
        } else {
            # Mensagem genérica caso todos os dados estejam zerados.
            ?>
            <div class="alert alert-success solid fade show mb-2 m-auto">
                <i class="fa fa-info-circle" aria-hidden="true"></i><strong> Você ainda não tem afiliações ativas.</strong> Veja suas <a href="<?php echo SERVER_URI . '/produtos/solicitacoes/'; ?>" class="badge badge-sm light badge-success ml-1">Solicitações Enviadas</a> ou <a href="<?php echo SERVER_URI . '/loja/'; ?>" class="badge badge-sm light badge-success ml-1">Afilie-se Agora</a> a um novo produto.
            </div>
        <?php
        }
        ?>

    </div>
</div>


<?php

require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>