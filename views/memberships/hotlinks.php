<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
include(dirname(__FILE__) . '/../../includes/classes/StarRating.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}


$page_title = "Links de Afiliado | Logzz";
$hasnt_submenu_shop = 'active'; 
$select_datatable_page = $shop_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');


# Página de detalhes do produto.
if (isset($_GET['membership'])) {
    
    $user__id = $_SESSION['UserID'];
    $membership_hotcode = addslashes($_GET['membership']);
    $product_id = addslashes($_GET['product']);

    # Verifica se a Afiliação é existente e ativa
    $verfify_membership = $conn->prepare('SELECT membership_status FROM memberships WHERE memberships_hotcode = :memberships_hotcode AND membership_product_id = :membership_product_id');
    $verfify_membership->execute(array('memberships_hotcode' => $membership_hotcode, 'membership_product_id' => $product_id));
    $membership_status = $verfify_membership->fetch()['membership_status'];


    if (!($membership_status == 'ATIVA')) {
        echo '<script>window.location.assign("' . SERVER_URI . '/conteudo-nao-encontrado");</script>';
        exit;
    }

    $stmt = $conn->prepare('SELECT * FROM products WHERE (product_id = :product_id AND product_trash = 0) AND (product_membership_available = :product_membership_available)');
    $stmt->execute(array('product_id' => $product_id, 'product_membership_available' => 'sim'));

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

        a, p {
            word-break: break-word
        }

        .nav-tabs .nav-link {
            border-bottom: unset;
        }

        .nav-tabs .nav-link.active {
            background-color: #fff;
        }

        div.links .tab-card:not(.active) {
            display: none;
        }
    </style>
    <div class="container-fluid">
        <!-- row -->
        <div class="row">
            <div class="col-xl-12 col-xxl-12">
                <div class="card">
                        <!-- <div class="card-header">
                        <h4 class="card-title">Detalhes do Produto</h4>
                        </div> -->
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

                                        <div class="d-table mb-2" style="word-break: break-word;">
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

                                                if ($get_category_name->rowCount()  > 0) {
                                                    $category_name = $get_category_name->fetch();
                                                    $category_name = $category_name[0];
                                                } else {
                                                    $category_name = "Sem Categoria";
                                                }
                                                ?>
                                                <span class="badge badge-success light"><?php echo $category_name; ?></span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-12 col-xxl-12 ml-3 row"> 
                <li class="nav-item">
                    <a class="nav-link disabled">Seus links</a>
                </li>
                <ul class="nav nav-tabs links">
                    <li class="nav-item cursor-pointer">
                        <a class="nav-link active cursor-pointer" href="#links-agendamento" id="local-tab" data-toggle="tab" role="tab" aria-controls="local" aria-selected="true">Checkouts de Agendamento</a>
                    </li> 
                    <li class="nav-item cursor-pointer">
                        <a class="nav-link cursor-pointer" href="#pagina-venda" id="distribuit-tab" data-toggle="tab" role="tab" aria-controls="distribution" aria-selected="false">Páginas de vendas</a>
                    </li>
                </ul>
            </div> 

            <div class="col-xl-12 col-xxl-12 links" >                
                <div class="tab-card card active" id="links-agendamento">
                    <div class="card-header">
                        <h4 class="card-title">Links de Afiliado</h4>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="tabContent">
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
                            } 

                            if($get_sales_quantity->rowCount() != 0) { 
                                for ($aux = 0; $aux < count($quantities); $aux++) { ?>
                                    <div class="tab-pane fade <?= $aux == 0 ? 'show active' : '' ?>" id="quantity-<?= $quantities[$aux]['sale_quantity'] ?>" role="tabpanel" aria-labelledby="quantity-<?= $quantities[$aux]['sale_quantity'] ?>-tab">
                                        <div class="table-responsive"> 
                                            <table class="table table-responsive-md hotlinks" id="hotlinks-<?= $quantities[$aux]['sale_quantity'] ?>">
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
                                                    <?php

                                                    $get_sales_list = $conn->prepare('SELECT * FROM sales WHERE product_id = :product_id AND (sale_trashed = 0 AND sale_shop_visibility = 1) AND sale_quantity = :quantity ORDER BY sale_price DESC');
                                                    $get_sales_list->execute(array('product_id' => $product_id, 'quantity' => $quantities[$aux]['sale_quantity']));
                                                    if ($get_sales_list->rowCount() != 0) {
                                                        while ($row = $get_sales_list->fetch()) {
                                                            $sale_name = $row['sale_name'];
                                                            $sale_quantity = $row['sale_quantity'];
                                                            $sale_price = $row['sale_price'];
                                                            $sale_status = $row['sale_status'];
                                                            $sale_freight = $row['sale_freight'];
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

                                                            //PEGAR COMISSÃO PERSONALIZADA PARA O AFILIADO
                                                            $hotcode = addslashes($_GET['membership']);
                                                            $meta_key = "custom_commission_" . $hotcode;

                                                            $custom_comission = $conn->prepare('SELECT * FROM sales_meta WHERE sale_id = :sale_id AND meta_key = :meta_key');
                                                            $custom_comission->execute(array('sale_id' => $sale_id, 'meta_key' => $meta_key));
                                                            $custom_comission_data = $custom_comission->fetch(\PDO::FETCH_ASSOC);

                                                            // CALCULO DE COMISSÃO DO PRODUTO
                                                            if ($custom_comission->rowCount() > 0 && $custom_comission_data['meta_value'] !== null) { // comissão personalizada para esse afiliado. 
                                                                $commision = ($sale_price + $sale_freight) * ($custom_comission_data['meta_value'] / 100);
                                                                $type_comission = 0;
                                                            } elseif ($sale_commission > 0) { // comissão da oferta
                                                                $commision = ($sale_price + $sale_freight) * ($sale_commission / 100); // caso tenha comissão personalizada na oferta. 
                                                                $type_comission = 1;
                                                            } else { // comissão padrão do produto
                                                                $commision = ($sale_price + $sale_freight) * ($product_commission / 100); // caso tenha comissão personalizada na oferta. 
                                                                $type_comission = 2;
                                                            } ?>
                                                            <tr>
                                                                <td class="text-center"><?php echo $sale_name;  ?></td>
                                                                <td class="text-center"><?php echo $sale_quantity; ?></td>
                                                                <td class="text-center">R$ <?php echo number_format($sale_price + $sale_freight, 2, ',', '');  ?></td>
                                                                <td class="text-center">
                                                                    R$ <?php echo number_format($commision, 2, ',', ''); ?>
                                                                    <?php if ($type_comission == 0) : ?>
                                                                        <span class="ml-1 text-info" style="cursor:pointer;" data-toggle="tooltip" data-placement="top" title='Você possui comissão personalizada para essa oferta'>
                                                                            <i class="fas fa-info-circle" style="font-size:14px;"></i>
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td class="text-center"><small><a href="#" class="copy-hotcode-btn" data-link="<?php echo CHECKOUT_URI . "pay/?a=" . $membership_hotcode . "&s=" . $sale_id;  ?>"><?php echo CHECKOUT_URI . "pay/?a=" . $membership_hotcode . "&s=" . $sale_id;  ?></a></small></td>
                                                                <td><a href="<?php echo SERVER_URI; ?>/produtos/afiliacoes/<?php echo $membership_hotcode; ?>/oferta/<?php echo $product_id; ?>/<?php echo $sale_id; ?>" title="Editar Oferta" class="btn btn-primary btn-xs sharp mr-1" style=" float: left;"><i class="fas fa-pencil-alt"></i></a></td>
                                                            </tr>
                                                            <?php
                                                        }
                                                    } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <?php 
                                } 
                            } else { ?>
                                <div class="table-responsive">
                                        <table class="table table-responsive-md hotlinks">
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
 
                <div class="tab-card card" id="pagina-venda">
                    <div class="card-header">
                        <h4 class="card-title">Página de vendas</h4>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="tabContent">
                            <table id="product-page" class="table table-responsive-md" data-page-length="3">
                                <thead>
                                    <tr>
                                        <th class="text-center text-muted">Nome</th>
                                        <th class="text-muted">Link</th>
                                        <th class="text-muted"></th>
                                    </tr>
                                </thead>  
                                <tbody>
                                    <tr>  
                                        <td class="text-center col-2">Principal</td>
                                        <td class="text-left col-9">
                                            <a class="btn-link btn-sm copy-hotcode-btn" style="text-transform: lowercase;" data-link="<?php echo CHECKOUT_URI . "redirect/?a=" . $membership_hotcode .'&p='. $product_id .'&ps=0' ?>"><?php echo CHECKOUT_URI . "redirect/?a=" . $membership_hotcode .'&p='. $product_id .'&ps=0' ?></a>
                                        </td>
                                        <td  class="text-center">
                                            <a href="<?php echo SERVER_URI .'/produtos/afiliacoes/'. $membership_hotcode .'/redirect/'. $product_id .'/0' ?>" title="Editar Pixel" class="btn btn-primary btn-xs sharp mr-1">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php
                                        $get_pages_list = $conn->prepare('SELECT page_id, page_name, page_url FROM pages_sales WHERE page_product_id = :product_id');
                                        $get_pages_list->execute(array('product_id' => $product_id));
        
                                        
                                        while ($page = $get_pages_list->fetch()) { ?>
                                            <tr>
                                                <td class="text-center"><?= $page['page_name'] ?></td>
                                                <td>
                                                    <a class="btn-link btn-sm copy-hotcode-btn" style="text-transform: lowercase;" data-link="<?php echo CHECKOUT_URI . "redirect/?a=" . $membership_hotcode .'&p='. $product_id .'&ps='. $page['page_id'] ?>"><?php echo CHECKOUT_URI . "redirect/?a=" . $membership_hotcode .'&p='. $product_id .'&ps='. $page['page_id'] ?></a>
                                                </td>
                                                <td  class="text-center">
                                                    <a href="<?php echo SERVER_URI .'/produtos/afiliacoes/'. $membership_hotcode .'/redirect/'. $product_id .'/'. $page['page_id'] ?>" title="Editar Pixel" class="btn btn-primary btn-xs sharp mr-1">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </a>
                                                </td> 
                                            </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div> 

            <div class="col-xl-12 col-xxl-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Estoque disponível em</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center text-muted">Operação Local</th>
                                    <th class="text-center text-muted">Lista de Alcance <i class="fa fa-info-circle" aria-hidden="true" data-toggle="tooltip" data-placement="left" title="" data-original-title="É a lista de todas as cidades onde o recebimento com pagamento na entrega pode ser feito."></i></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                # Lista as Localidades para as Tabs
                                $get_locale_list = $conn->prepare('SELECT * FROM local_operations as L INNER JOIN inventories AS I ON inventory_locale_id = operation_id WHERE (inventory_product_id = :product_id AND inventory_quantity > 0) AND ship_locale = 0');
                                $get_locale_list->execute(array('product_id' => $product_id));

                               
                                $arrItems = [];
                                while ($tab_list_iten = $get_locale_list->fetch(\PDO::FETCH_ASSOC)) {

                                    # Busca a quantidade de estoque do produto na localidade.
                                    $get_inventory = $conn->prepare('SELECT inventory_quantity FROM inventories WHERE inventory_product_id = :product_id AND inventory_locale_id = :inventory_locale_id ORDER BY inventory_id DESC');

                                    $get_inventory->execute(array('product_id' => $product_id, 'inventory_locale_id' => $tab_list_iten['inventory_locale_id']));

                                    $inventory = $get_inventory->fetch();

                                    $inventory = $inventory['inventory_quantity'];

                                    array_push($arrItems, $tab_list_iten['inventory_locale_id']);
                                ?>
                                    <tr>
                                        <td class="text-black text-center"><b><?php echo $tab_list_iten['operation_name']; ?><b></td>
                                        <td class="text-center"><a href="#" data-toggle="modal" data-target="#ModalListaDeAlcance" data-id="<?php echo $tab_list_iten['inventory_locale_id']; ?>" class="btn btn-xs btn-success solid ml-3 locale-list-btn"><i class="fas fa-map-marked-alt"></i>&nbsp; Ver Lista</a></td>
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

    <div class="modal fade" id="ModalListaDeAlcance" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header center text-center d-block">
                    <h5 class="modal-title">Lista de Alcance de <strong id="locale-name"></strong> </h5>
                    <button type="button" class="close" data-dismiss="modal"><span>×</span>
                    
                    </button>
                </div>
                <div class="modal-body" id="range-list">

                </div>
            </div>
        </div>
    </div>

<?php

}
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>
    <script> 
        $(document).ready(function($) {
            $(".hotlinks").DataTable({
                searching: false,
                paging: false,
                select: true,
                lengthChange: false
            });  

            $('#product-page').DataTable({ 
                searching: false, 
                paging: true,
                select: false,
                lengthChange: false,
            });   

 
            $("ul.links > .nav-item > .nav-link").on('click', function() {      
                console.log($(this).attr('href'));  
                if( $(this).attr('href') == "#links-agendamento" ){
                    $("#links-agendamento").addClass('active');
                    $("#pagina-venda").removeClass('active');
                } 
                if( $(this).attr('href') == "#pagina-venda" ) {
                    $("#links-agendamento").removeClass('active');
                    $("#pagina-venda").addClass('active'); 
                } 
            });

            const triggerTabList = document.querySelectorAll('.nav-tabs a')
                triggerTabList.forEach(triggerEl => {
                const tabTrigger = new bootstrap.Tab(triggerEl)

                triggerEl.addEventListener('click', event => {
                    event.preventDefault()
                    tabTrigger.show()
                })
            })
        });
    </script>