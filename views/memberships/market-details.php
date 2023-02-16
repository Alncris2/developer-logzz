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
$select_datatable_page = $shop_page = true;
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

    # verifica se a imagem principal é um video ou imagem
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

?>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.css" integrity="sha512-UTNP5BXLIptsaj5WdKFrkFov94lDx+eBvbKyoe1YAfjeRPC+gT5kyZ10kOHCfNZqEui1sxmqvodNUx3KbuYI/A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>

        .carousel-indicators li {
            box-sizing: unset;
            flex: unset;
            width: unset;
            height: unset;
            margin-right: unset;
            margin-left: unset;
            text-indent: unset;
            cursor: unset;
            background-clip: unset;
            border-top: unset;
            border-bottom: unset;
            opacity: .5; 
            transition: opacity 0.6s ease;
        } 

        .owl-carousel .owl-nav.disabled+.owl-dots {
            margin-top: 10px
        }

        .carousel-indicators li {
            display: inline-block;
            zoom: 1
        }

        .carousel-indicators li {
            width: 15px;
            height: 15px;
            margin: 5px 7px;
            background: #2fde91;
            display: block;
            -webkit-backface-visibility: visible;
            transition: opacity .2s ease;
            border-radius: 30px;
            -o-border-radius: 30px;
            -ms-border-radius: 30px;
            -moz-border-radius: 30px;
            -webkit-border-radius: 30px;
        }

        .carousel-indicators li.active span,
        .carousel-indicators li:hover span {
            background: #2fde91;
        }


        .carousel-inner .item {
            height: 100%;
        }

        .carousel-inner {
            min-height: 300px;
            display: flex;
            align-items: center; 
        }
        

        .flex-control-nav {
            bottom: unset;
        }

        .nav-tabs .nav-link {
            background: transparent;
        }

        .fake-paginate {
            background: #2BC155 !important;
            color: #fff !important;
            margin: 0 10px;
            border: 0px solid #2BC155 !important;
            border-radius: 1.25rem;
        }

        .fake-paginate.active {

            background: #fff !important;
            color: #2BC155 !important;
            border: 2px solid #2BC155 !important;
        }
    </style>
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
                                    <div id="produto-galeria" class="carousel slide" data-ride="carousel">
                                        <ol class="carousel-indicators">
                                            <li data-target="#produto-galeria" data-slide-to="0" class="active"></li>
                                            <?php for ($i = 0; $i < count($product_images); $i++) { 
                                                if ($product_images[$i] !== '') { ?> 
                                                    <li data-target="#produto-galeria" data-slide-to="<?= $i + 1 ?>"></li>
                                                <?php }
                                            } ?>
                                        </ol>
                                        <div class="carousel-inner">
                                            <div class="carousel-item active item">
                                                <?php if ($isVideo) : ?>
                                                    <div style="height: 20rem; display: flex;">
                                                        <video class="w-100" src="<?php echo SERVER_URI . '/uploads/imagens/produtos/' . $product_image ?>" controls></video>
                                                    </div>
                                                <?php else : ?>
                                                    <img src="<?php echo SERVER_URI . '/uploads/imagens/produtos/' . $product_image ?>" alt="<?php echo $product_name; ?>" alt="Gallery image 1" class="ecommerce-gallery-main-img my-auto active w-100 rounded" />
                                                <?php endif ?>
                                            </div>
                                            <?php foreach ($product_images as $image_name) : ?>
                                                <?php if ($image_name !== '') : ?>
                                                    <div class="carousel-item item">
                                                        <img src="<?php echo SERVER_URI . '/uploads/imagens/produtos/' . $image_name ?>" alt="<?php echo $product_name; ?>" alt="Gallery image 1" class="ecommerce-gallery-main-img my-auto active w-100 rounded" />
                                                    </div>
                                                <?php endif ?>
                                            <?php endforeach ?>
                                        </div>
                                        <a class="carousel-control-prev" type="button" data-target="#produto-galeria" data-slide="prev">
                                            <i class="fa fa-3x fa-angle-left text-primary"></i>
                                        </a>
                                        <a class="carousel-control-next" type="button" data-target="#produto-galeria" data-slide="next">
                                        <i class="fa fa-3x fa-angle-right text-primary"></i> 
                                        </a>
                                    </div>
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

            <?php

            $get_sales_quantity = $conn->prepare('SELECT sale_quantity FROM sales WHERE product_id = :product_id AND (sale_trashed = 0 AND sale_shop_visibility = 1) GROUP BY sale_quantity ORDER BY sale_quantity ASC');
            $get_sales_quantity->execute(array('product_id' => $product_id));

            if ($get_sales_quantity->rowCount() != 0) {
                $quantities = $get_sales_quantity->fetchALL(); ?>

                <div class="col-md-12 d-flex align-items-center justify-content-between flex-wrap">
                    <div class="row">
                        <ul class="nav mb-3" id="nav-tab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link disabled">Ofertas por quantidade:</a>
                            </li>
                            <?php for ($aux = 0; $aux < count($quantities); $aux++) { ?>
                                <li class="nav-item cursor-pointer" role="presentation">
                                    <button type="button" class="mb-2 fake-paginate nav-link <?= $aux == 0 ? 'active' : '' ?> cursor-pointer" href="" id="quantity-<?= $quantities[$aux]['sale_quantity'] ?>-tab" data-toggle="tab" data-target="#quantity-<?= $quantities[$aux]['sale_quantity'] ?>" role="tab" aria-controls="quantity-<?= $quantities[$aux]['sale_quantity'] ?>" aria-selected="<?= $aux == 0 ? 'true' : 'false' ?>">
                                        <?= $quantities[$aux]['sale_quantity'] ?>
                                    </button>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>

            <?php
            } ?>

            <div class="col-xl-12 col-xxl-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Ofertas</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <div class="tab-content" id="tabContent">
                                <?php
                                if ($get_sales_quantity->rowCount() != 0) {
                                    for ($aux = 0; $aux < count($quantities); $aux++) { ?>
                                        <div class="tab-pane fade <?= $aux == 0 ? 'show active' : '' ?>" id="quantity-<?= $quantities[$aux]['sale_quantity'] ?>" role="tabpanel" aria-labelledby="quantity-<?= $quantities[$aux]['sale_quantity'] ?>-tab">
                                            <div class="table-responsive">
                                                <table class="table table-responsive-md hotlinks" id="hotlinks-<?= $quantities[$aux]['sale_quantity'] ?>">
                                                    <thead>
                                                        <tr>
                                                            <th class="text-center">Descrição</th>
                                                            <th class="text-center">Quant.</th>
                                                            <th class="text-center">Preço</th>
                                                            <th class="text-center">Comissão (R$)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $product_id = addslashes($_GET['detalhe']);
                                                        $get_sales_list = $conn->prepare('SELECT * FROM sales WHERE product_id = :product_id AND (sale_trashed = 0 AND sale_shop_visibility = 1) AND sale_quantity = :quantity ORDER BY sale_price DESC');
                                                        $get_sales_list->execute(array('product_id' => $product_id, 'quantity' => $quantities[$aux]['sale_quantity']));
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
                                                                } ?>

                                                                <tr>
                                                                    <td class="text-center"><strong><?php echo $sale_name  ?></strong></td>
                                                                    <td class="text-center"><?php echo $sale_quantity; ?></td>
                                                                    <td class="text-center">R$ <?php echo number_format($sale_price, 2, ',', '');  ?></td>

                                                                    <td class="text-center"><strong>R$ <?php echo number_format(($sale_price * ($sale_commission / 100)), 2, ',', ''); ?></strong></td>
                                                                </tr>
                                                            <?php
                                                            }
                                                        } else { ?>
                                                            <tr>
                                                                <td class="text-center" colspan="5">Este produto ainda não possui Ofertas.</td>
                                                            </tr> <?php
                                                                } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    <?php
                                    }
                                } else { ?>
                                    <div class="table-responsive">
                                        <table class="table table-responsive-md" id="hotlinks">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">Oferta</th>
                                                    <th class="text-center">Quant.</th>
                                                    <th class="text-center">Preço (R$)</th>
                                                    <th class="text-center">Comissão (R$)</th>
                                                    <th class="text-center">Link</th>
                                                    <th class="text-center">#</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="text-center" colspan="5">Não foi possível gerar os links. Aguarde alguns momentos ou entre em contato com o produtor.</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php
                                } ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>


        <?php

    }
    require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
        ?>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js" integrity="sha512-bPs7Ae6pVvhOSiIcyUClR7/q2OAsRiovw4vAkX+zJbw3ShAeeqezq50RIIcIURq7Oa20rW2n2q+fyXBNcU9lrw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

        <script>
            let carrousel = $('.sync1').owlCarousel({
                loop: true,
                margin: 10,
                autoplay: true,
                autoplaySpeed: 3000,
                responsiveRefreshRate: true,
                responsiveClass: true,
                rtl: true,
                responsive: {
                    0: {
                        items: 1,
                        nav: true
                    }
                },
                navText: ['<i class="catch fa fa-angle-right"></i>', '<i class="catch fa fa-angle-left"></i>'],
            });

            $(document).ready(function($) {
                $("#hotlinks").DataTable({
                    searching: false,
                    paging: false,
                    select: false,
                    lengthChange: false

                });

                $('.sync1').trigger('refresh.owl.carousel');
            });
        </script>