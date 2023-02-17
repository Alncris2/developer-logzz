<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
include(dirname(__FILE__) . '/../../includes/classes/StarRating.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}

$page_title = "Detalhes do Produto | Logzz";
$shop_page = true;
$hasnt_submenu_shop = 'active';
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');


# Página de detalhes do produto.
if (isset($_GET['detalhe'])) {
    $product_id = intval(addslashes($_GET['detalhe']));
    $user__id = $_SESSION['UserID'];

    $stmt = $conn->prepare('SELECT * FROM products WHERE (product_id = :product_id AND product_trash = 0) AND (product_membership_available = :product_membership_available AND product_shop_visibility = :product_shop_visibility)');
    $stmt->execute(array('product_id' => $product_id, 'product_membership_available' => 'sim', 'product_shop_visibility' => 'sim'));

    if ($stmt->rowCount() != 0) {
        while ($row = $stmt->fetch()) {
            $product_name = $row['product_name'];
            $product_price = $row['product_price'];
            $product_description = $row['product_description'];
            $product_image = $row['product_image'];
            $product_rating = $row['product_rating'];
            $product_sale_page = $row['product_sale_page'];
            $product_categories = $row['product_categories'];
            $product_membership_type = $row['product_membership_type'];
            $product_commission = $row['product_commission'];
            $product_max_price = $row['product_max_price'];
            $product_min_price = $row['product_min_price'];
            $product_max_commission = $row['product_max_price'] * ($row['product_commission'] / 100);
            $product_cookie_time = $row['product_cookie_time'];
            $product_warranty_time = $row['product_warranty_time'];
            $product_auto_membership = $row['product_auto_membership'];
        }
    } else {
        echo "<script>window.location.assign('" . SERVER_URI . "/pagina-nao-encontrada/')</script>";
        exit;
    }

?>

    <div class="container-fluid">
        <!-- row -->
        <div class="row">
            <div class="col-xl-12 col-xxl-12">
                <div class="card">
                    <div class="card-body pt-5 pb-5">
                        <div class="row">
                            <div class="col-xl-3 col-lg-6  col-md-6 col-xxl-5 ">
                                <!-- Tab panes -->
                                <div class="tab-content">
                                    <img class="img-fluid" src="<?php echo SERVER_URI . "/uploads/imagens/produtos/" . $product_image; ?>" alt="">
                                </div>
                            </div>
                            <!--Tab slider End-->
                            <div class="col-xl-9 col-lg-6  col-md-6 col-xxl-7 col-sm-12">
                                <div class="product-detail-content">
                                    <!--Product details-->
                                    <div class="new-arrival-content pr">
                                        <h3><?php echo $product_name; ?></h3>
                                        <div class="comment-review star-rating">
                                            <ul>
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
                                        </div>
                                        <div class="d-table mb-3">
                                            <span class="text-center text-muted"><small>Você ganhará até </small><br></span>
                                            <p class="price float-left d-block">R$ <?php echo number_format($product_max_commission, 2, ',', ''); ?></p>
                                            <span class="text-center text-muted"><small><br>por cada venda.</small><br></span>
                                        </div>

                                        <div class="d-table mb-2">
                                            <p class="">
                                                <span class="text-muted"><small>Página de Vendas</small><br></span>
                                                <a href="<?php echo $product_sale_page; ?>" target="_blank"><?php echo $product_sale_page; ?></a>
                                            </p>
                                        </div>

                                        <div class="d-table mb-2">
                                            <p class="">
                                                <span class="text-muted"><small>Percentual de Comissão</small><br></span>
                                                <?php echo $product_commission; ?>%
                                            </p>
                                        </div>

                                        <div class="d-table mb-2">
                                            <p class="">
                                                <span class="text-muted"><small>Preço</small><br></span>
                                                <?php if ($product_max_price > $product_min_price) {
                                                    echo "<b>R$ " . number_format($product_min_price, 2, ',', '') . "</b> a <b>R$ " . number_format($product_max_price, 2, ',', '') . "</b>";
                                                } else {
                                                    echo "<b>R$ " . number_format($product_max_price, 2, ',', '') . "</b>";
                                                } ?>
                                            </p>
                                        </div>

                                        <div class="d-table mb-2">
                                            <p class="">
                                                <span class="text-muted"><small>Tipo de Comissionamento</small><br></span>
                                                <?php
                                                if ($product_membership_type == 'ultimoclique') {
                                                    echo "Último Clique";
                                                } else {
                                                    echo "Primeiro Clique";
                                                }
                                                ?>
                                            </p>
                                        </div>

                                        <div class="d-table mb-2">
                                            <p class="">
                                                <span class="text-muted"><small>Tempo de Cookie</small><br></span>
                                                <?php echo $product_cookie_time . " dias";  ?>
                                            </p>
                                        </div>

                                        <div class="d-table mb-2">
                                            <p class="">
                                                <span class="text-muted"><small>Tipo de Garantia</small><br></span>
                                                <?php echo $product_warranty_time . " dias"; ?>
                                            </p>
                                        </div>

                                        <div class="d-table mb-2">
                                            <p class="">
                                                <span class="text-muted"><small>Descrição do Produto</small><br></span>
                                                <?php echo $product_description; ?>
                                            </p>
                                        </div>

                                        <div class="d-table mb-2">
                                            <p>Categorias: &nbsp;&nbsp;
                                                <?php
                                                $category = explode(',', $product_categories);
                                                $prod_cat_id = $category[0];

                                                $get_category_name = $conn->prepare('SELECT prod_cat_name FROM products_categories WHERE prod_cat_id = :prod_cat_id');
                                                $get_category_name->execute(array('prod_cat_id' => $prod_cat_id));

                                                if ($get_category_name->rowCount() > 0) {
                                                    $category_name = $get_category_name->fetch();
                                                    $category_name = $category_name[0];
                                                } else {
                                                    $category_name = "Sem Categoria";
                                                }
                                                ?>
                                                <span class="badge badge-success light"><?php echo $category_name ?></span>
                                            </p>
                                        </div>
                                        <div class="shopping-cart mt-3">
                                            <button class="btn btn-primary" id="new-membership-btn" data-id="<?php echo $product_id; ?>" href="<?php echo SERVER_URI . "/afiliacao/"; ?>">
                                                <?php
                                                if ($product_auto_membership == 'sim') {
                                                    echo "Afiliar-se Agora";
                                                } else {
                                                    echo "Solicitar Afiliação";
                                                }
                                                ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-12 col-xxl-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Ofertas</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-responsive-md">
                                <thead>
                                    <tr>
                                        <th class="text-center col-md-3">Descrição</th>
                                        <th class="text-center col-md-3">Preço</th>
                                        <th class="text-center col-md-3">Sua comissão (R$)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $product_id = addslashes($_GET['detalhe']);
                                    $get_sales_list = $conn->prepare('SELECT * FROM sales WHERE product_id = :product_id AND (sale_trashed = 0 AND sale_shop_visibility = 1) ORDER BY sale_id DESC');
                                    $get_sales_list->execute(array('product_id' => $product_id));
                                    
                                    
                                    if ($get_sales_list->rowCount() != 0) {
                                        while ($row = $get_sales_list->fetch()) {

                                            $sale_name = $row['sale_name'];
                                            $sale_quantity = $row['sale_quantity'];
                                            $sale_price = $row['sale_price'];
                                            $sale_status = $row['sale_status'];
                                            $sale_id = $row['sale_id'];

                                                # Verifica se existe comissão personalizada para essa oferta
                                            $verify_custom_commision = $conn->prepare('SELECT meta_value FROM sales_meta WHERE sale_id = :sale_id AND meta_key = "custom_commission"');
                                            $verify_custom_commision->execute(array('sale_id' => $sale_id));

                                            if ($verify_custom_commision->rowCount() == 1) {
                                                $custom_commision = $verify_custom_commision->fetch();
                                                $sale_commission = $custom_commision['meta_value'];
                                            } else {
                                                $sale_commission = $product_commission;
                                            }
                                    ?>
                                            <tr>
                                                <td class="text-center"><strong><?php echo $sale_name  ?></strong></td>
                                                <td class="text-center">R$ <?php echo number_format($sale_price, 2, ',', '');  ?></td>
                                            
                                                <td class="text-center"><strong>R$ <?php echo number_format(($sale_price * ($sale_commission / 100)), 2, ',', ''); ?></strong></td>
                                            </tr>

                                        <?php 
                                        }
                                    } else {
                                        ?>
                                        <tr>
                                            <td class="text-center" colspan="5">Este produto ainda não possui Ofertas.</td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>


<?php

}
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>