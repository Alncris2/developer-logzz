<?php
// error_reporting(-1);            
// ini_set('display_errors', 1); 
require_once(dirname(__FILE__) . '/../../includes/config.php');
include(dirname(__FILE__) . '/../../includes/classes/StarRating.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}

$product_page = true;
$select_datatable_page = true;
$sale_page = true;
$page_title = "Produtos | Logzz";

require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');


/**
 * 
 * Exibe página de Detalhes do Produto,
 * caso um ID de produto válido e pertecente ao
 * usuário logado seja passado via GET.
 * 
 * 
 */

if (isset($_GET['detalhe'])) {
    $product_id = intval(addslashes($_GET['detalhe']));
    $user__id = $_SESSION['UserID'];

    # Busca os detalhes do produto cujo ID foi passado via GET.
    $stmt = $conn->prepare('SELECT * FROM products WHERE product_id = :product_id AND (user__id = :user__id AND product_trash = 0)');
    $stmt->execute(array('product_id' => $product_id, 'user__id' => $user__id));

    if ($stmt->rowCount() != 0) {
        while ($row = $stmt->fetch()) {
            $product_code                               = $row['product_code'];
            $product_name                               = $row['product_name'];
            $product_status                             = $row['status'];
            $product_price                              = $row['product_price'];
            $product_weight                             = $row['product_weight'];
            $product_type_packaging                     = $row['type_packaging'];
            $product_refuse                             = $row['last_recuse_text'];
            $product_description                        = $row['product_description'];
            $product_image                              = $row['product_image'];
            $product_categories = $generic_multselect_preload = $row['product_categories'];
            $product_sale_page                          = $row['product_sale_page'];
            $product_warranty_time                      = $row['product_warranty_time'];
            $product_membership_available               = $row['product_membership_available'];
            $product_shop_visibility                    = $row['product_shop_visibility'];
            $product_commission                         = $row['product_commission'];
            $product_auto_membership                    = $row['product_auto_membership'];
            $product_membership_type                    = $row['product_membership_type'];
            $product_cookie_time                        = $row['product_cookie_time'];
        }
    } else {
        # Redireciona para a página padrão com todos os produtos do usuário logado,
        # caso haja algum problema com o ID passado via GET.
        echo "<script>window.location.assign('" . SERVER_URI . "/meus-produtos/')</script>";
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

    // ofertas desse produto
    $stmt = $conn->prepare("SELECT * FROM sales AS s WHERE s.product_id = :product_id AND s.sale_trashed = 0 AND s.sale_product_name = '#ProductName'");
    $stmt->execute(['product_id' => $product_id]);
    $sales_this_product = $stmt->fetchAll(\PDO::FETCH_ASSOC);


    // checkout de cada oferta
    $stmt = $conn->prepare("SELECT * FROM custom_checkout AS cc WHERE cc.checkout_id_product = :product_id or cc.checkout_id_product IS NULL AND cc.user__id = :user__id");
    $stmt->execute(['product_id' => $product_id, 'user__id' => $user__id]);
    $checkouts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

 ?>
    <style>
        .dinamicColorBox {
            width: 100%;
            height: 8px;
            border-radius: 5px;
            background-color: #c8ffe6;
        }

        .dinamicColorBoxText {
            width: 100%;
            height: 8px;
            border-radius: 5px;
            background-color: #3d4465;
        }

        .dinamicColor {
            width: 100%;
            height: 8px;
            border-radius: 5px;
            background-color: #20c997;
        }

        .dinamicColor-text {
            width: 100%;
            height: 8px;
            border-radius: 5px;
            background-color: #FF4847;
        }

        .actives {
            border-bottom: 1px solid #3a3b42;
            width: 25%;
        }

        #fileBtn {
            display: none;
        }

        #btn-file {
            cursor: pointer;
            width: 20%;
            background: #6466dd;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            border-color: #6466dd;
            height: 50px;
        }

        .options-btn .btn {

            border-radius: 0;
            height: 50px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        #inputTag {
            display: none;
        }

        #box-file {
            cursor: pointer;
            border: 1px dashed #ccc;
            width: 80%;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 350px;
        }

        .container {
            display: block;
            position: relative;
            display: flex;
            top: -25px;
            align-items: center;
            padding-left: 35px;
            cursor: pointer;
            font-size: 15px;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }


        .container input {
            opacity: 0;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 0;
            width: 0;
        }


        .checkmark {
            position: absolute;
            top: 8px;
            left: 0;
            height: 15px;
            width: 15px;
            background-color: #eee;
        }


        .container:hover input~.checkmark {
            background-color: #ccc;
        }


        .container input:checked~.checkmark {
            background-color: #34bfa3;
        }


        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
        }


        .container input:checked~.checkmark:after {
            display: block;
        }


        .container .checkmark:after {
            left: 5px;
            top: 1px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 3px 3px 0;
            -webkit-transform: rotate(45deg);
            -ms-transform: rotate(45deg);
            transform: rotate(45deg);
        }

        #input-file-now {
            display: none;
        }

        .file-upload-wrapper {
            cursor: pointer;
            border: 1px dashed #ccc;
            width: 80%;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 350px;
        }

        #imagemSuperior {
            height: 150px;
        }

        #imagemLateral {
            height: 500px;
        }

        .drop-zone {
            padding: 0.625rem;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font: 500 1.563rem "Quicksand", sans-serif;
            cursor: pointer;
            color: #cccccc;
            border: 1px dashed #ccc;
            flex-wrap: wrap;
        }

        .drop-zone--over {
            border-style: solid;
        }

        .drop-zone__input {
            display: none;
        }

        .drop-zone__thumb {
            width: 100%;
            height: 100%;
            overflow: hidden;
            background-color: #cccccc;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .drop-zone__thumb::after {
            content: attr(data-label);
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 5px 0;
            color: #ffffff;
            background: rgba(0, 0, 0, 0.75);
            font-size: 14px;
            text-align: center;
        }

        .hide {
            display: none;
        }

        .show-helper {
            display: flex;
        }

        #helper-hover:hover .helper-style {
            display: flex;
        }

        .note-editor {
            border-radius: 1.25rem;
            background: #fff;
            border: 1px solid #d4d4d4;
            color: #495057;
            height: auto;
        }

        .note-editable {
            border-radius: 1.25rem !important;
            background-color: #F4F5F9 !important;
            margin: 1rem;
        }

        .note-statusbar {
            border-radius: 0 0 50px 50px;
        }

        .imagem-opcional .remove-image {
            background-color: hsl(0 0% 100% / 59%);
            position: absolute;
            display: none;
        }

        .imagem-opcional:hover .remove-image {
            display: flex;
        }

        .formato {
            font-size: 65%;
            color: #d3d3d3;
        }

        .fake-paginate {
            background: #2BC155 !important;
            color: #fff !important;
            margin: 0 10px;
            border: 0px solid #2BC155;
            border-radius: 1.25rem;
        }

        .fake-paginate.active {
            background: #fff !important;
            color: #2BC155 !important;
            border: 2px solid #2BC155;
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
    <div class="container-fluid">
        <?php if ($product_status == 0) : ?>
            <div class="alert alert-warning solid fade show mb-3" style="background-color:#ff9900; border:1px solid #ff9900;">
                <div class="d-flex align-items-center">
                    <i class="flaticon-381-time" aria-hidden="true" style="font-size:30px;"></i>
                    <div class="ml-3">
                        <strong> Seu produto está sob revisão.</strong> <br>
                        Assim que aprovado você poderá cadastrar ofertas e criar cupons.
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($product_status == 2) : ?>
            <div class="alert  text-white bg-danger solid fade show mb-3" style="">
                <div class="d-flex align-items-center">
                    <i class="flaticon-381-time" aria-hidden="true" style="font-size:30px;"></i>
                    <div class="ml-3">
                        <strong> Seu produto foi recusado.</strong> <br>
                        Revise os dados do produto e tente novamente. <br>
                    </div>
                </div>
                <button class="mt-3 btn btn-light btn-rounded fs-10" href="" style="" data-toggle="modal" data-target="#refusalReason">Ver motivo da recusa</button>
            </div>
        <?php endif; ?>
        <!-- row -->
        <div class="row">
            <div class="col-xl-12 col-xxl-12">
                <div class="card">
                    <div class="card-header d-flex align-items-start">
                        <h4 class="card-title">Detalhes do Produto</h4>
                        <?php if ($product_status == 0) : ?>
                            <div class="d-flex align-items-center justify-content-center">
                                <span style="width:15px; height:15px; border-radius:100%; background-color:#ff9900;"></span>
                                <span class="ml-2">Aguardando aprovação</span>
                            </div>
                        <?php elseif ($product_status == 1) : ?>
                            <div class="d-flex align-items-center justify-content-center">
                                <span style="width:15px; height:15px; border-radius:100%; background-color:#20c997;"></span>
                                <span class="ml-2">Aprovado</span>
                            </div>
                        <?php else : ?>
                            <div class="d-flex align-items-center justify-content-center">
                                <span style="width:15px; height:15px; border-radius:100%; background-color:#FF4847;"></span>
                                <span class="ml-2">Recusado</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div id="smartwizard" class="form-wizard order-create">
                            <div class="row">
                                <div class="col-lg-4 mb-2">
                                    <div role="tabpanel" class="tab-pane fade show active" id="first">
                                        <?php if ($isVideo) : ?>
                                            <video id="product-video" class="w-100" src="<?php echo SERVER_URI . '/uploads/imagens/produtos/' . $product_image ?>" controls></video>
                                            <img id="product-image" class="img-fluid w-100" style="display: none;" id="product-image">
                                        <?php else : ?>
                                            <img id="product-image" src="<?php echo SERVER_URI . '/uploads/imagens/produtos/' . $product_image ?>" alt="<?php echo $product_name; ?>" alt="Gallery image 1" class="ecommerce-gallery-main-img active w-100" />
                                            <video id="product-video" class="w-100" style="display: none;" controls></video>
                                        <?php endif ?>
                                    </div>

                                    <div class="form-group mt-4 text-center">
                                        <button type="button" id="btn-product-image" class="btn btn-success">Imagem ou Vídeo Principal</button>
                                        <p class="formato">png/jpeg/mp4/mkv/gif/webp <br /> Tamanho máximo: foto 5mb | video 5mb</p>
                                    </div>

                                    <div role="tabpanel" class="tab-pane fade show active pt-4">
                                        <div class="row">

                                            <?php for ($aux = 0; $aux < 9; $aux++) { ?>
                                                <div class="col-4 mb-2 imagem-opcional p-0">
                                                    <div class="w-100 h-100 justify-content-center align-items-center remove-image">
                                                        <a class="btn-remove-image btn btn-link" data-index="<?= $aux ?>">
                                                            <i class="fa fa-times fa-3x"></i>
                                                        </a>
                                                    </div>

                                                    <!-- <div class="position-relative">
                                                        <button class="btn btn-success position-absolute btn-delete-image"><i class="fa fa-times"></i></button>                                
                                                    </div> -->
                                                    <?php if ($product_images[$aux]) : ?>
                                                        <img class="img-fluid p-2 product-images" style="cursor: pointer;" src="<?php echo SERVER_URI . '/uploads/imagens/produtos/' . $product_images[$aux] ?>" name="<?= $product_images[$aux] ?>">
                                                    <?php else : ?>
                                                        <img class="img-fluid p-2 product-images" style="cursor: pointer;" src="<?php echo SERVER_URI; ?>/images/product/placeholder.jpg">
                                                    <?php endif ?>
                                                </div>
                                            <?php } ?>
                                        </div>
                                        <div class="form-group mt-4 text-center">
                                            <button type="button" id="btn-product-images" class="btn btn-success">Imagens Secundárias</button>
                                            <p class="formato">png/jpeg/webp <br>Máximo: 9 imagens </p>
                                        </div>
                                    </div>


                                </div>
                                <div class="col-lg-8 mb-2">
                                    <div class="alert alert-danger alert-dismissible fade show submit-feedback-negative d-none">
                                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="mr-2">
                                            <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon>
                                            <line x1="15" y1="9" x2="9" y2="15"></line>
                                            <line x1="9" y1="9" x2="15" y2="15"></line>
                                        </svg>
                                        <p class="submit-feedback d-inline"></p>
                                        <button type="button" class="close h-100" data-dismiss="alert" aria-label="Close"><span><i class="fa fa-close"></i></span>
                                        </button>
                                    </div>

                                    <div class="alert alert-success alert-dismissible fade show submit-feedback-positive d-none">
                                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="mr-2">
                                            <polyline points="9 11 12 14 22 4"></polyline>
                                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                                        </svg>
                                        <p class="submit-feedback d-inline"></p>
                                        <button type="button" class="close h-100" data-dismiss="alert" aria-label="Close"><span><i class="fa fa-close"></i></span>
                                        </button>
                                    </div>

                                    <form id="UpdateProductForm" action="atualizar-produto" method="POST">
                                        <input type="hidden" id="ActionInput" name="action" value="update-product">
                                        <input type="hidden" name="produto" value="<?php echo $product_id; ?>">

                                        <input type="file" name="product-image" id="input-file-product-image" style="display: none;" accept=".png, .jpg, .jpeg, .gif, .jfif, .webp, .mp4, .mkv">
                                        <input multiple type="file" name="product-images[]" id="input-file-product-images" style="display: none;" accept=".png, .jpg, .jpeg, .jfif">

                                        <div class="form-group">
                                            <label class="text-label">Nome do Produto<i class="req-mark">*</i></label>
                                            <input value="<?php echo $product_name; ?>" type="text" name="nome-produto" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="text-label">Descrição<i class="req-mark">*</i></label>
                                            <textarea name="descricao-produto" id="descricao-produto" class="form-control" required rows="4"><?php echo $product_description; ?></textarea>
                                        </div>  
                                        <div class="form-group">   
                                            <label class="text-label">Preço de Custo R$<i class="req-mark">*</i></label>   
                                            <input type="text" name="preco-produto" onKeyPress="return(MascaraMoeda(this,'.',',',event))" class="form-control" placeholder="R$ 99,90" value="<?php echo number_format($product_price, 2, ',', '.'); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label class="text-label">Categorias<i class="req-mark">*</i></label>
                                            <select class="categoria-produto-select multselect_preload" name="categoria-produto-select" multiple="multiple">
                                                <?php
                                                $get_prod_cats_list = $conn->prepare('SELECT * FROM products_categories ORDER BY prod_cat_id ASC');
                                                $get_prod_cats_list->execute();

                                                if ($get_prod_cats_list->rowCount() != 0) {
                                                    while ($row = $get_prod_cats_list->fetch()) {
                                                        $prod_cat_name = $row['prod_cat_name'];
                                                        $prod_cat_id = $row['prod_cat_id'];
                                                ?>
                                                        <option value="<?php echo $prod_cat_id;  ?>"><?php echo $prod_cat_name;  ?></option>
                                                <?php
                                                    }
                                                }
                                                ?>
                                            </select>
                                            <input type="hidden" id="categoria-produto-select-text" class="multselect_preload_text" name="categoria-produto-select-text" value="<?php echo $product_categories; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label class="text-label">Página de Vendas<i class="req-mark">*</i></label>
                                            <input type="text" name="pagina-vendas-produto" class="form-control" style="text-transform: lowercase;" placeholder="https://seudominio.com/produto" value="<?php echo $product_sale_page; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label class="text-label">Tempo de garantia (dias)<i class="req-mark">*</i></label>
                                            <input type="text" name="garantia-produto" class="form-control" placeholder="00 dias" value="<?php echo $product_warranty_time; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label class="text-label">Peso do produto (kg)<i class="req-mark">*</i></label>
                                            <input type="text" name="weight-product" class="form-control weight" placeholder="0.000" value="<?= $product_weight ?>" onkeypress="return event.charCode >= 48 && event.charCode <= 57" required />
                                        </div>

                                        <div class="form-group">
                                            <label class="text-label">Tipo de embalagem
                                                <i class="req-mark">*</i>
                                                <i class="fas fa-info-circle" style="color:#ccc;" data-toggle="tooltip" data-placement="top" title="Comprimento x Largura x Altura"></i>
                                            </label>
                                            <select class="form-control" name="kind-packing">
                                                <option selected disabled required>Selecione uma embalagem</option>
                                                <option value="1" <?php if ($product_type_packaging == 1) echo 'selected' ?>>Caixa 16cm x 11cm x 6cm (Pequena)</option>
                                                <option value="2" <?php if ($product_type_packaging == 2) echo 'selected' ?>>Caixa 24cm x 15cm x 10cm (Média)</option>
                                                <option value="3" <?php if ($product_type_packaging == 3) echo 'selected' ?>>Caixa 50cm x 33cm x 20cm (Grande)</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <button type="submit" id="SubmitButton" class="btn btn-success">Atualizar Produto</button>
                                            <?php if ($product_status == 2) : ?>
                                                <button type="button" id="newSaleAprovation" class="btn btn-secondary" data-idUser="<?= $_GET['detalhe']; ?>">Realizar novo pedido de aprovação</button>
                                            <?php endif; ?>
                                            <?php if ($_SESSION['UserPlan'] == 5) { ?>
                                                <a href="<?php echo SERVER_URI . "/produto/" . $product_id . "/excluir/"; ?>" id="DeleteProductLink" class="btn btn-warning light" data-id="<?php echo $product_id; ?>">Excluir Este Produto</a>
                                            <?php } ?>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($product_status == 1) : ?>   
            <div id="product-accordion" class="accordion accordion-with-icon">

            <?php
                $get_sales_quantity = $conn->prepare('SELECT sale_quantity FROM sales WHERE product_id = :product_id AND sale_trashed = 0 GROUP BY sale_quantity ORDER BY sale_quantity ASC');
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
            <?php } ?>

                <!-- Sales Accordion -->
                <div class="card accordion__item">
                    <div class="card-header collapsed" data-toggle="collapse" data-target="#sales-collapse" aria-expanded="false">
                        <h4 class="card-title"><i style="color: #777777" class="fas fa-angle-down"></i>&nbsp;Ofertas</h4>
                        <button type="button" data-toggle="modal" data-target="#ModalCriarOferta" class="btn btn-rounded btn-success btn-sm" <?php if ($product_status != 1) : ?> disabled <?php endif; ?>>
                            <span class="btn-icon-left text-success">
                                <i class="fa fa-plus color-success"></i>
                            </span>
                            Criar Oferta
                        </button>
                    </div>
                    <div id="sales-collapse" class="card-bodyaccordion__body collapse"  data-parent="#product-accordion">
                        <?php
                            if ($get_sales_quantity->rowCount() != 0) {   

                                echo '<div class="tab-content" id="tabContent">';

                                for ($aux = 0; $aux < count($quantities); $aux++) { ?>
                                    <div class="tab-pane fade <?= $aux == 0 ? 'show active' : '' ?>" id="quantity-<?= $quantities[$aux]['sale_quantity'] ?>" role="tabpanel" aria-labelledby="quantity-<?= $quantities[$aux]['sale_quantity'] ?>-tab">
                                        <div class="table-responsive accordion__body--text">
                                        <table class="table table-responsive-md hotlinks" id="hotlinks-<?= $quantities[$aux]['sale_quantity'] ?>">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center col-md-1">#</th>
                                                        <th class="text-center col-md-5">Nome</th>
                                                        <th class="text-center col-md-1">Quant.</th>
                                                        <th class="text-center col-md-2">Preço (R$)</th>
                                                        <th class="text-center col-md-1"><i class="fa fa-info-circle" aria-hidden="true" data-toggle="tooltip" data-placement="left" title="Indica se a oferta estará visível ou não para afiliados"></i><strong></th>
                                                        <th class="text-center col-md-1"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php

                                                    $get_sales_list = $conn->prepare('SELECT * FROM sales WHERE product_id = :product_id AND sale_trashed = 0 AND sale_quantity = :quantity ORDER BY sale_price DESC');
                                                    $get_sales_list->execute(array('product_id' => $product_id, 'quantity' => $quantities[$aux]['sale_quantity']));
                                                    if ($get_sales_list->rowCount() != 0) {
                                                        $s = 1;
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
                                                            } ?>

                                                            <tr>
                                                                <td class="text-center"><strong><?php echo $s++;  ?></strong></td>
                                                                <td class="text-center"><?php echo $sale_name;  ?></td>
                                                                <td class="text-center"><?php echo $sale_quantity;  ?></td>
                                                                <td class="text-center">R$ <?php echo number_format($sale_price, 2, ',', ''); ?></td>
                                                                <td class="text-center">
                                                                    <div class="custom-control custom-switch">
                                                                        <input data-sid="<?php echo $sale_id; ?>" data-pid="<?php echo $product_id; ?>" type="checkbox" class="custom-control-input switch-privacidade-oferta" id="privacidade-oferta-<?php echo $sale_id; ?>" <?php if ($row['sale_shop_visibility'] == 1) {
                                                                                                                                                                                                                                                                                    echo "checked";
                                                                                                                                                                                                                                                                                } ?>>
                                                                        <label class="custom-control-label" for="privacidade-oferta-<?php echo $sale_id; ?>"></label>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <a href="<?php echo SERVER_URI; ?>/oferta/?p=<?php echo $product_id; ?>&s=<?php echo $sale_id; ?>&a=u" title="Editar Oferta" class="btn btn-primary btn-xs sharp mr-1" style=" float: left;"><i class="fas fa-pencil-alt"></i></a>

                                                                    <a title="Desativar Oferta" class="btn btn-danger btn-xs sharp update-sale-status" data-sid="<?php echo $sale_id; ?>" data-pid="<?php echo $product_id; ?>" href="#"><i class="fa fa-trash"></i></a>
                                                                </td>
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
                                echo '</div>';
                            } else { ?>
                                <div class="table-responsive">
                                    <table class="table table-responsive-md">
                                        <thead> 
                                            <tr>
                                                <th class="text-center col-md-1">#</th>
                                                <th class="text-center col-md-5">Nome</th>
                                                <th class="text-center col-md-1">Quant.</th>
                                                <th class="text-center col-md-2">Preço (R$)</th>
                                                <th class="text-center col-md-1"><i class="fa fa-info-circle" aria-hidden="true" data-toggle="tooltip" data-placement="left" title="Indica se a oferta estará visível ou não para afiliados"></i><strong></th>
                                                <th class="text-center col-md-1"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="text-center" colspan="5">Este produto ainda não possui Ofertas.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <?php
                            } 
                        ?>
                    </div>
                </div>

                <!-- Coupons Accordion -->
                <div class="card accordion__item">
                    <div class="card-header collapsed" data-toggle="collapse" data-target="#coupons-collapse" aria-expanded="false">
                        <h4 class="card-title"><i style="color: #777777" class="fas fa-angle-down"></i>&nbsp;Cupons</h4>
                        <?php
                        if ($get_sales_quantity->rowCount() != 0) {
                        ?>
                            <button type="button" data-toggle="modal" data-target="#ModalCriarCupom" class="btn btn-rounded btn-success btn-sm"><span class="btn-icon-left text-success"><i class="fa fa-plus color-success"></i></span>Criar Cupom</button>
                        <?php
                        }
                        ?>
                    </div>
                    <div id="coupons-collapse" class="card-bodyaccordion__body collapse" data-parent="#product-accordion">
                        <div class="table-responsive accordion__body--text">
                            <table class="table table-responsive-md" id="coupons-datatable">
                                <thead>
                                    <tr>
                                        <th class="text-center col-md-1">#</th>
                                        <th class="text-center col-md-5">Cupom</th>
                                        <th class="text-center col-md-1">Desconto (%)</th>
                                        <th class="text-center col-md-2">Status</th>
                                        <th class="text-center col-md-2"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $product_id = addslashes($_GET['detalhe']);
                                    $get_coupons_list = $conn->prepare('SELECT * FROM coupons WHERE coupon_product_id = :product_id ORDER BY coupon_id DESC');
                                    $get_coupons_list->execute(array('product_id' => $product_id));

                                    if ($get_coupons_list->rowCount() != 0) {
                                        $s = 1;
                                        while ($row = $get_coupons_list->fetch()) {
                                            $coupon_string        = $row['coupon_string'];
                                            $coupon_linked_sales  = $row['coupon_linked_sales'];
                                            $coupon_percent       = $row['coupon_percent'];

                                            if ($row['coupon_trashed'] == 0) {
                                                $btn_classes = "light badge-success";
                                                $status_string = "Ativo";
                                                $data_status = 1;
                                            } else {
                                                $btn_classes = "light badge-warning";
                                                $status_string = "Inativo";
                                                $data_status = 0;
                                            }
                                    ?>
                                            <tr>
                                                <td class="text-center"><strong><?php echo $s;  ?></strong></td>
                                                <td class="text-center" style="text-transform: uppercase;"><?php echo $coupon_string; ?></td>
                                                <td class="text-center"><?php echo $coupon_percent . "%"; ?></td>
                                                <!-- <td class="text-center"><?php echo $coupon_linked_sales;  ?></td> -->
                                                <td class="text-center"><span class="badge badge-xs <?php echo $btn_classes; ?> mb-1"><?php echo $status_string; ?></span></td>
                                                <td>
                                                    <div class="dropdown">
                                                        <a href="<?php echo SERVER_URI; ?>/cupom/?p=<?php echo $product_id; ?>&cupom=<?php echo $coupon_string; ?>&a=u" title="Editar Cupom" class="btn btn-rounded btn btn-success btn-xs sharp"><i class="fas fa-pencil-alt"></i></a>
                                                        <a title="Desativar Cupom" class="btn btn-rounded btn btn-danger btn-xs sharp delete-coupon-status" data-cupom="<?php echo $coupon_string; ?>" data-produto="<?php echo $product_id; ?>" data-status="<?php echo $data_status; ?>" href="#"><i class="fa fa-trash"></i></a>
                                                        <!-- <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="<?php echo SERVER_URI; ?>/oferta/?p=<?php echo $product_id; ?>&s=<?php echo $sale_id; ?>&a=u">Editar</a>
                                                        <a class="dropdown-item" href="<?php echo SERVER_URI; ?>/oferta/?p=<?php echo $product_id; ?>&s=<?php echo $sale_id; ?>&a=d">Excluir</a>
                                                        </div> -->
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php
                                            $s = $s + 1;
                                        }
                                    } else {
                                        ?>
                                        <tr>
                                            <td class="text-center" colspan="5">Este produto ainda não possui Cupons.</td>
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
        <?php endif; ?>

        <!-- Memberships Accordion -->
        <div class="card accordion__item">
            <div class="card-header collapsed" data-toggle="collapse" data-target="#membership-collapse" aria-expanded="false">
                <h4 class="card-title"><i style="color: #777777" class="fas fa-angle-down"></i>&nbsp;Afiliação</h4>
            </div>
            <div id="membership-collapse" class="card-bodyaccordion__body collapse" data-parent="#product-accordion">
                <div class="table-responsive accordion__body--text">

                    <form id="UpdateMembershipConfigForm" action="salvar-configuracoes" method="POST">
                        <div class="custom-control custom-switch mb-3">
                            <input type="hidden" id="ActionInput" name="action" value="update-membership-config">
                            <input type="hidden" name="produto" value="<?php echo $product_id; ?>">
                            <input type="checkbox" class="custom-control-input" id="produto-disponivel-afiliacao" <?php if ($product_membership_available == "sim") {
                                                                                                                        echo "checked";
                                                                                                                    } ?>>
                            <label class="custom-control-label" for="produto-disponivel-afiliacao">Disponível para Afiliados</label>
                            <input type="hidden" id="disponivel-afiliacao" name="disponivel-afiliacao" value="<?php echo $product_membership_available; ?>">
                        </div>
                        <div class="custom-control custom-switch mb-3 campos-disponivel-afiliacao <?php if ($product_membership_available != "sim") {
                                                                                                        echo "d-none";
                                                                                                    } ?>">
                            <input type="checkbox" class="custom-control-input" id="produto-visivel-afiliacao" <?php if ($product_shop_visibility == "sim") {
                                                                                                                    echo "checked";
                                                                                                                } ?>>
                            <label class="custom-control-label" for="produto-visivel-afiliacao">Vísível na loja</label>
                            <input type="hidden" id="visivel-afiliacao" name="visivel-afiliacao" value="<?php echo $product_shop_visibility; ?>">
                            <i><small class="text-muted d-block" id="visivel-afiliacao-caption"><?php if ($product_shop_visibility == 'nao') {
                                                                                                    echo "O produto só será visto por afiliados convidados.";
                                                                                                } else {
                                                                                                    echo "O produto será exibido para todos os afiliados.";
                                                                                                }  ?></small></i>
                        </div>
                        <div class="form-group campos-disponivel-afiliacao <?php if ($product_membership_available != "sim") {
                                                                                echo "d-none";
                                                                            } ?>">
                            <label class="text-label">Comissão Padrão (%)<i class="req-mark">*</i></label>
                            <input type="text" name="comissao-produto" class="form-control" placeholder="" value="<?php echo @$product_commission; ?>">
                        </div>
                        <div class="custom-control custom-switch mb-3 campos-disponivel-afiliacao <?php if ($product_membership_available != "sim") {
                                                                                                        echo "d-none";
                                                                                                    } ?>">
                            <input type="checkbox" class="custom-control-input" id="produto-afiliacao-automatica" <?php if (@$product_auto_membership == "sim") {
                                                                                                                        echo "checked";
                                                                                                                    } ?>>
                            <label class="custom-control-label" for="produto-afiliacao-automatica">Afiliação Automatica</label>
                            <input type="hidden" id="afiliacao-automatica" name="afiliacao-automatica" value="<?php echo @$product_auto_membership; ?>">
                            <i><small class="text-muted d-block" id="afiliacao-automatica-caption">
                                    <?php if (@$product_auto_membership == "sim") {
                                        echo "Os afiliados terão suas solicitações aprovadas automaticamente.";
                                    } else {
                                        echo "Os afiliados precisarão aguardar aprovação.";
                                    } ?>
                                </small></i>
                        </div>
                        <div class="form-group campos-disponivel-afiliacao <?php if ($product_membership_available != "sim") {
                                                                                echo "d-none";
                                                                            } ?>">
                            <label class="text-label">Tipo de Afiliação<i class="req-mark">*</i></label>
                            <select id="tipo-afiliacao-select" class="d-block default-select">
                                <option disabled>Selecione...</option>
                                <option <?php if (@$product_membership_type == "primeiroclique") {
                                            echo "selected";
                                        } ?> value="primeiroclique">Primeiro Clique</option>
                                <option <?php if (@$product_membership_type == "ultimoclique") {
                                            echo "selected";
                                        } ?> value="ultimoclique">Último Clique</option>
                            </select>
                            <input type="hidden" id="tipo-afiliacao" name="tipo-afiliacao" value="<?php if (@$product_membership_type != null && !(empty(@$product_membership_type))) {
                                                                                                        echo $product_membership_type;
                                                                                                    } else {
                                                                                                        echo $product_membership_type;
                                                                                                    }; ?>">
                        </div>
                        <div class="form-group campos-disponivel-afiliacao <?php if ($product_membership_available != "sim") {
                                                                                echo "d-none";
                                                                            } ?>">
                            <label class="text-label">Tempo de Cookie (dias)<i class="req-mark">*</i></label>
                            <input type="text" name="tempo-cookie-produto" class="form-control" placeholder="" value="<?php echo @$product_cookie_time; ?>">
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-success">Salvar Configurações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Members List Accordion -->
        <?php
        # A lista de afiliados só é exibida se o produto
        # estiver marcado como "Disponível para afiliação"
        if ($product_membership_available == "sim") {
            $product_id = addslashes($_GET['detalhe']);
            $get_sales_list = $conn->prepare('SELECT * FROM sales WHERE product_id = :product_id AND sale_trashed = 0 ORDER BY sale_id DESC');
            $get_sales_list->execute(array('product_id' => $product_id)); ?>

            <div class="card accordion__item">
                <div class="card-header collapsed" data-toggle="collapse" data-target="#members-list-collapse" aria-expanded="false">
                    <h4 class="card-title"><i style="color: #777777" class="fas fa-angle-down"></i>&nbsp;Afiliados</h4>
                    <?php
                    if ($get_sales_list->rowCount() != 0) {
                    ?>

                    <?php
                    }
                    ?>
                </div>

                <div id="members-list-collapse" class="card-bodyaccordion__body collapse" data-parent="#product-accordion">
                    <div class="table-responsive accordion__body--text">
                        <!-- <button type="button" class="btn btn-rounded btn-success btn-sm copy-member-invite-link-btn" id="copy-member-invite-link-btn" data-link="<?php echo SERVER_URI . "/loja/convite/" . $product_code;  ?>"><span class=" btn-icon-left text-success"><i class="fa fa-plus color-success"></i></span>Link de Convite</button> -->
                        <div class="d-flex mb-3">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm  btn-rounded btn-outline-success mr-2" data-toggle="dropdown" aria-expanded="true"><i class="fas fa-download mr-2"></i> Exportar</button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="#" id="export-to-csv"><i class="fas fa-file-csv scale2 mr-2"></i> CSV</a>
                                    <a class="dropdown-item" href="#" id="export-to-xlsx"><i class="fas fa-file-excel scale2 mr-2"></i> Excel</a>
                                    <a class="dropdown-item" href="#" id="export-to-pdf"><i class="fas fa-file-pdf scale2 mr-2"></i> PDF</a>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-rounded btn-success copy-member-invite-link-btn" id="copy-member-invite-link-btn" data-link="<?php echo SERVER_URI . "/loja/convite/" . $product_code;  ?>"><i class="fas fa-link mr-2" aria-hidden="true"></i>Link de Convite</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-responsive-md" id="members-list">
                                <thead>
                                    <tr>
                                        <th class="text-center col-md-1">Código</th>
                                        <th class="text-center col-md-4">Nome</th>
                                        <th class="text-center col-md-3">Email</th>
                                        <th class="text-center col-md-2">WhatsApp</th>
                                        <th class="text-center col-md-1"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $product_id = addslashes($_GET['detalhe']);
                                    $get_membership_list = $conn->prepare('SELECT * FROM memberships AS m INNER JOIN users AS u ON m.membership_affiliate_id = u.user__id WHERE membership_product_id = :product_id AND membership_status = "ATIVA" ORDER BY membership_start DESC');
                                    $get_membership_list->execute(array('product_id' => $product_id));
                                    if ($get_membership_list->rowCount() != 0) {
                                        while ($row = $get_membership_list->fetch()) {
                                            $user_fullname = $row['full_name'];
                                            $user_email = $row['email'];
                                            $user_phone = $row['user_phone'];
                                            $membership_hotcode = $row['memberships_hotcode'];
                                            $user_code = $row['user_code'];
                                    ?>
                                            <tr>
                                                <td class="text-center">
                                                    [<a href="#" data-toggle="tooltip" data-placement="top" title="" class="copy-user-code" data-code="<?php echo $user_code; ?>" data-original-title="Clique para copiar"><?php echo $user_code; ?></a>]
                                                </td>
                                                <td class="text-center">
                                                    <?php echo $user_fullname; ?>
                                                </td>
                                                <td class="text-center"><small><?php echo $user_email;  ?></small></td>
                                                <td class="text-center"><?php echo $user_phone; ?></td>
                                                <td>
                                                    <div class="col-2 col-sm-2 px-0 d-flex align-self-center align-items-center">
                                                        <div class="text-center">
                                                            <div class="d-flex">
                                                                <a href="<?php echo SERVER_URI . "/produto/afiliado/" . $membership_hotcode; ?>" title="Detalhes" data-id="<?php echo $membership_hotcode; ?>" data-status="1" class="update-membership-status btn btn-primary btn-xs sharp mr-1"><i class="fas fa-pencil-alt"></i></a>
                                                                <a href="#" title="Remover Solicitação" data-id="<?php echo $membership_hotcode; ?>" data-status="0" class="update-membership-status btn btn-danger btn-xs sharp"><i class="fas fa-minus"></i></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                    <?php
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <?php
        }  ?>

            
        <div class="card accordion__item">
            <div class="card-header collapsed" data-toggle="collapse" data-target="#checkout-collapse" aria-expanded="false">
                <h4 class="card-title"><i style="color: #777777" class="fas fa-angle-down"></i>&nbsp;Checkouts</h4>
                <button type="button" data-toggle="modal" data-target="#ModalCriarOfertaPersonalizada" class="btn btn-rounded btn-success btn-sm" <?php if ($product_status != 1) : ?> disabled <?php endif; ?>>
                    <span class="btn-icon-left text-success">
                        <i class="fa fa-plus color-success"></i>
                    </span>
                    Criar Checkout
                </button>
            </div>
            <div id="checkout-collapse" class="card-bodyaccordion__body collapse" data-parent="#product-accordion">
                <div class="table-responsive accordion__body--text">
                    <table class="table table-responsive-md" id="checkout-datatable">
                        <thead>
                            <tr>
                                <th class="text-center fs-14">Tipo do Checkout</th>
                                <th class="text-center fs-14">Nome</th>
                                <th class="text-center fs-14">Quantidade de Visitas</th>
                                <th class="text-center fs-14">Quantidade de Conclusões</th>
                                <th class="text-center fs-14">Taxa de Conversão</th>
                                <th class="text-center fs-14">Acões</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php foreach ($checkouts as $checkout) : ?>
                                <tr>
                                    <td class="text-center"><?= $checkout['isCustom'] == 0 ? 'Checkout Padrão' : 'Checkout Personalizado' ?></td>
                                    <td class="text-center"><?= $checkout['name_checkout'] ?></td>
                                    <td class="text-center"><?= $checkout['checkout_views'] ?></td>
                                    <td class="text-center"><?= $checkout['checkout_sales'] ?></td>
                                    <td class="text-center"><?= $checkout['checkout_sales'] > 0 ? number_format(($checkout['checkout_sales'] / $checkout['checkout_views']) * 100, 2, ',', '.') : 0     ?>%</td>
                                    <td class="text-center">
                                        <?php if ($checkout['isCustom'] == 0) : ?>
                                            <!-- NÃO É POSSIVÉL EDITAR CHECKOUT PADRÃO -->
                                            <button class="btn btn-danger btn-xs sharp mr-1" data-toggle="tooltip" data-placement="top" title="Não é possível editar o checkout padrão.">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        <?php else : ?>
                                            <!-- SEM BOTÕES DE EDITAR POR ENQUANTO -->
                                            <button class="btn btn-success btn-xs sharp mr-1 open-modal-edit" alt="<?= $checkout['checkout_id'] ?>" data-toggle="tooltip" data-placement="top" title="Editar checkout">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="#fff" width="12px">
                                                    <path d="M421.7 220.3L188.5 453.4L154.6 419.5L158.1 416H112C103.2 416 96 408.8 96 400V353.9L92.51 357.4C87.78 362.2 84.31 368 82.42 374.4L59.44 452.6L137.6 429.6C143.1 427.7 149.8 424.2 154.6 419.5L188.5 453.4C178.1 463.8 165.2 471.5 151.1 475.6L30.77 511C22.35 513.5 13.24 511.2 7.03 504.1C.8198 498.8-1.502 489.7 .976 481.2L36.37 360.9C40.53 346.8 48.16 333.9 58.57 323.5L291.7 90.34L421.7 220.3zM492.7 58.75C517.7 83.74 517.7 124.3 492.7 149.3L444.3 197.7L314.3 67.72L362.7 19.32C387.7-5.678 428.3-5.678 453.3 19.32L492.7 58.75z" />
                                                </svg>
                                            </button>
                                            <button class="btn btn-danger btn-xs sharp mr-1 delete-checkout" alt="<?= $checkout['checkout_id'] ?>" title="Deletar checkout">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pages Accordion -->
        <div class="card accordion__item">
            <div class="card-header collapsed" data-toggle="collapse" data-target="#pages-collapse" aria-expanded="false">
                <h4 class="card-title"><i style="color: #777777" class="fas fa-angle-down"></i>&nbsp; Páginas de vendas</h4>
                <button type="button" data-toggle="modal" data-target="#ModalCriarPagina" class="btn btn-rounded btn-success btn-sm"><span class="btn-icon-left text-success"><i class="fa fa-plus color-success"></i></span>Criar Página</button>
            </div>
            <div id="pages-collapse" class="card-bodyaccordion__body collapse" data-parent="#product-accordion">
                <div class="table-responsive accordion__body--text">
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
                                <td class="text-center">Principal</td>
                                <td>
                                    <a class="btn-link btn-sm copy-hotcode-btn" style="text-transform: lowercase;" data-link="<?php echo $product_sale_page ?>"><?php echo $product_sale_page; ?></a>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-danger btn-xs sharp">
                                        <i class="fa fa-ban"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php
                                $get_pages_list = $conn->prepare('SELECT page_id, page_name, page_url FROM pages_sales WHERE page_product_id = :product_id');
                                $get_pages_list->execute(array('product_id' => $product_id));

                                
                                while ($page = $get_pages_list->fetch()) { ?>
                                    <tr>
                                        <td class="text-center"><?= $page['page_name'] ?></td>
                                        <td>
                                            <a class="btn-link btn-sm copy-hotcode-btn" style="text-transform: lowercase;" data-link="<?= $page['page_url'] ?>"><?= $page['page_url'] ?></a>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-success btn-xs sharp update-page" data-id="<?= $page['page_id'] ?>"><i class="fa fa-pen-alt"></i></button>
                                            <button class="btn btn-danger btn-xs sharp delete-page" data-id="<?= $page['page_id'] ?>"><i class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card accordion__item">
            <div class="card-header collapsed" data-toggle="collapse" data-target="#estoque-collapse" aria-expanded="false">
                <h4 class="card-title"><i style="color: #777777" class="fas fa-angle-down"></i>&nbsp;Estoques à Pronta Entrega</h4>
            </div>
            <div id="estoque-collapse" class="card-bodyaccordion__body collapse" data-parent="#product-accordion">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th class="text-center text-muted">Localidade</th>
                            <th class="text-center text-muted">Estoque Atual</th>
                            <th class="text-center text-muted"></th>
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
                            $get_inventory = $conn->prepare('SELECT inventory_quantity, inventory_id FROM inventories  WHERE inventory_product_id = :product_id AND inventory_locale_id = :inventory_locale_id ORDER BY inventory_id DESC');

                            $get_inventory->execute(array('product_id' => $product_id, 'inventory_locale_id' => $tab_list_iten['inventory_locale_id']));

                            $inventory = $get_inventory->fetch();

                            $inventory_quantity = $inventory['inventory_quantity'];
                            $inventory_id = $inventory['inventory_id'];

                            array_push($arrItems, $tab_list_iten['inventory_locale_id']);
                        ?>
                            <tr>
                                <td class="text-black text-center"><b><?php echo $tab_list_iten['operation_name']; ?><b></td>
                                <td class="text-center text-black"><b><?php echo $inventory_quantity; ?></b> unidades</td>
                                <td class="text-center">
                                    <a class="get-days-available btn btn-success btn-sm" 
                                        data-inventory="<?= $inventory_id ?>" 
                                        data-operation="<?= $tab_list_iten['operation_id'] ?>" 
                                        data-product="<?= $product_id ?>"
                                        data-toggle="tooltip" 
                                        data-placement="top" 
                                        title="Personalize os dias que o produto poderá ser entregue nas suas respectivas operações.">
                                        <i class="fa fa-pencil-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($product_status == 2) : ?>
            <!-- Modal -->
            <div class="modal fade" id="refusalReason" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Motivo da recusa do produto</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p class="text-wrap"><?= $product_refuse ?></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" data-dismiss="modal">Fechar</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>


        <div class="modal fade" id="ModalCriarOferta" style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Criar Oferta para "<?php echo $product_name; ?>"</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="AddSaleForm" action="nova-oferta" method="POST">
                            <input type="hidden" id="ActionInput" name="action" value="create-oferta">
                            <input type="hidden" name="produto" value="<?php echo $product_id; ?>">
                            <div class="form-group">
                                <label class="text-label">Nome da Oferta<i class="req-mark">*</i></label>
                                <input id="sale-name-input" type="text" name="nome-oferta" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="text-label">URL Amigável</label>
                                <input id="url-friedly-input" type="text" name="url-oferta" class="form-control" placeholder="Gerada automaticamente">
                            </div>
                            <div class="form-group">
                                <label class="text-label">Preço<i class="req-mark">*</i></label>
                                <input type="text" name="preco-oferta" class="form-control money" required="">
                            </div>
                            <?php
                            # O campo Comissão Personalizada só será exibido
                            # estiver marcado como "Disponível para afiliação"
                            if ($product_membership_available == "sim") {
                                $get_sale_custom_commission = $conn->prepare('SELECT meta_value FROM sales_meta WHERE sale_id = :sale_id AND meta_key = "custom_commission"');
                                $get_sale_custom_commission->execute(array('sale_id' => $sale_id));
                                $custom_commission = $get_sale_custom_commission->fetch();
                            ?>
                                <div class="form-group">
                                    <label class="text-label">Comissão Personalizada (%)<i class="req-mark">*</i>
                                        <a href="#">
                                            <i class="fas fa-info-circle helperlink" id="helper" data-toggle="tooltip" data-placement="top" title="" data-original-title="Caso ative essa opção, iremos configurar uma comissão para essa oferta, diferente da padrão do produto"></i>
                                        </a>
                                    </label>
                                    <div class="custom-control custom-switch mb-3">
                                        <input type="checkbox" class="custom-control-input" id="produto-estoque-label-comission">
                                        <label class="custom-control-label" id="produto-estoque-comission" for="produto-estoque-label-comission">&nbsp; <?php echo "Não" ?></label>
                                    </div>
                                    <input type="number" id="disponivel-estoque-comission" name="comissao-personalizada" minlength="1" maxlength="99" step="0.01" value="<?php if (@$custom_commission['meta_value'] !== null) echo $custom_commission['meta_value'] ?>" class="form-control" style="<?php echo "display:none;" ?>" placeholder="Insira o valor da comissão personalizada em %">
                                </div>
                            <?php
                            } ?>
                            <div class="form-group">
                                <label class="text-label">Quantidade<i class="req-mark">*</i></label>
                                <input type="number" name="quantidade-oferta" class="form-control" required>
                            </div>
                            <div class="custom-control custom-switch mb-3">
                                <input type="checkbox" class="custom-control-input" id="privacidade-oferta" checked>
                                <label class="custom-control-label" for="privacidade-oferta">&nbsp;Oferta Visível na Loja</label>
                                <input type="hidden" id="privacidade-oferta-text" name="privacidade-oferta" value="1">
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger light" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Criar Oferta</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!---Checkout Personalizada--->
        <div class="modal fade" id="ModalCriarOfertaPersonalizada" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" style="width:80%;" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Criação de checkout personalizado</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="AddCheckout" method="POST">
                            <input type="hidden" id="add-action" name="add-action" value="create-checkout">
                            <input type="hidden" name="produto" value="<?php echo $product_id; ?>">
                            <div class="form-group">
                                <label class="text-label">Nome do Checkout<i class="req-mark">*</i></label>
                                <input type="text" class="form-control" name="name-checkout" id="name-checkout">
                            </div>

                            <div class="form-group">
                                <label class="text-label">Botões de Suporte</label>
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" name="suport-button" id="select-whatsapp">
                                    <label class="custom-control-label" id="select-whatsapp-lbl" for="select-whatsapp">&nbsp; Não</label>
                                </div>
                                <div id="support" style="display:none;">
                                    <label for="select-whatsapp-input"> WhatsApp </label>
                                    <input type="text" id="select-whatsapp-input" name="select-whatsapp-input" class="form-control whats mb-1" placeholder="+55 99 99999-9999">

                                    <label for="select-email">Email </label>
                                    <input type="text" id="select-email" name="select-email" class="form-control email" placeholder="seuemail@dominio.com">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="text-label">Contador</label>

                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" name="select-counter" id="select-counter">
                                    <label class="custom-control-label" id="select-counter-lbl" for="select-counter">&nbsp; Não</label>
                                </div>

                                <div id="components-counter" class="d-none">
                                    <div>
                                        <p class="mb-0">Cor de fundo</p>
                                        <label for="color-bg-input" class="dinamicColor" style="cursor:crosshair;"> </label>
                                        <input type="color" id="color-bg-input" name="color-bg-input" value="#c66751" class="d-block" style="border-color: transparent;background-color: transparent; opacity:0; width:0; height:0;" />
                                    </div>
                                    <div>
                                        <p class="mb-0">Cor do texto</p>
                                        <label for="color-text-input" class="dinamicColor-text" style="cursor:crosshair;"> </label>
                                        <input type="color" id="color-text-input" name="color-text-input" value="#ffffff" class="d-block" style="border-color: transparent;background-color: transparent; opacity:0; width:0; height:0;" />
                                    </div>

                                    <div>
                                        <label for="text-counter">Texto</label>
                                        <input type="text" class="form-control" name="text-counter" id="text-counter">
                                        <span class="small">
                                            Obs: se não informado, será utilizado o texto padrão "A condição especial terminará em: "
                                        </span>
                                    </div>
                                    <div class="mt-1">
                                        <label for="text-counter">Timer</label>
                                        <input type="text" id="select-time-input" name="select-time-input" class="form-control time mb-1" placeholder="00:00">
                                        <span class="small">
                                            Obs: se não informado, será utilizado o tempo padrão 05:00 (5 minutos)
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="text-label">Cor</label>
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" name="select-color" id="select-color">
                                    <label class="custom-control-label" id="select-color-lbl" for="select-color">&nbsp; Não</label>
                                </div>

                                <div id="components-color" class="d-none">
                                    <div>
                                        <p class="mb-0">Cor de fundo de exibição do produto</p>
                                        <label for="color-bg-box" class="dinamicColorBox" style="cursor:crosshair;"> </label>
                                        <input type="color" id="color-bg-box" name="color-bg-box" value="#c8ffe6" class="d-block" style="border-color: transparent;background-color: transparent; opacity:0; width:0; height:0;" />
                                    </div>
                                    <div>
                                        <p class="mb-0">Cor do texto de exibição do produto</p>
                                        <label for="color-bg-boxText" class="dinamicColorBoxText" style="cursor:crosshair;"> </label>
                                        <input type="color" id="color-bg-boxText" name="color-bg-boxText" value="#3d4465" class="d-block" style="border-color: transparent;background-color: transparent; opacity:0; width:0; height:0;" />
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="text-label">Notificações</label>
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" name="select-notification" id="select-notification">
                                    <label class="custom-control-label" id="select-notification-lbl" for="select-notification">&nbsp; Não</label>
                                </div>

                                <div id="components-notification" class="d-none">
                                    <p>Alertas disponiveis</p>
                                    <div class="d-flex" style="margin-bottom:20px;">
                                        <label class="container"><b data-text="1">1</b> &nbsp; Pessoa(s) estão comprando <?= $product_name ?> nesse momento.
                                            <input type="checkbox" name="checkbox1">
                                            <span class="checkmark"></span>
                                        </label>
                                        <div class="input-notification-quantity">
                                            <input class="form-control" onkeypress="return onlynumber();" data-id="min-quantity" data-lbl="1" style="height:40px;" type="number" value="1">
                                            <p style="font-size:13px;color:#a8a7a7;">Quantidade minima</p>
                                        </div>
                                    </div>

                                    <div class="d-flex" style="margin-bottom:20px;">
                                        <label class="container"><b data-text="2">1 </b> &nbsp; Pessoa(s) compraram <?= $product_name ?> essa semana.
                                            <input type="checkbox" name="checkbox2">
                                            <span class="checkmark"></span>
                                        </label>

                                        <div class="input-notification-quantity">
                                            <input class="form-control" onkeypress="return onlynumber();" style="height:40px;" data-id="min-quantity" data-lbl="2" type="number" value="1">
                                            <p style="font-size:13px;color:#a8a7a7;">Quantidade minima</p>
                                        </div>
                                    </div>

                                    <div class="d-flex" style="margin-bottom:20px;">
                                        <label class="container"><b data-text="3">1 </b>&nbsp; Pessoa(s) compraram <?= $product_name ?> nos ultimos 30 minutos.
                                            <input type="checkbox" name="checkbox3">
                                            <span class="checkmark"></span>
                                        </label>

                                        <div class="input-notification-quantity">
                                            <input class="form-control" onkeypress="return onlynumber();" data-id="min-quantity" data-lbl="3" style="height:40px;" type="number" value="1">
                                            <p style="font-size:13px;color:#a8a7a7;">Quantidade minima</p>
                                        </div>
                                    </div>

                                    <div class="d-flex" style="margin-bottom:20px;">
                                        <label class="container"><b data-text="4">1 </b>&nbsp; Pessoa(s) compraram <?= $product_name ?> hoje.
                                            <input type="checkbox" name="checkbox4">
                                            <span class="checkmark"></span>
                                        </label>

                                        <div class="input-notification-quantity">
                                            <input class="form-control" onkeypress="return onlynumber();" data-id="min-quantity" data-lbl="4" style="height:40px;" type="number" value="1">
                                            <p style="font-size:13px;color:#a8a7a7;">Quantidade minima</p>
                                        </div>
                                    </div>

                                    <div class="d-flex" style="margin-bottom:20px;">
                                        <label class="container"><b data-text="5">1 </b> &nbsp; Pessoa(s) compraram <?= $product_name ?> na ultima hora.
                                            <input type="checkbox" name="checkbox5">
                                            <span class="checkmark"></span>
                                        </label>

                                        <div class="input-notification-quantity">
                                            <input class="form-control" onkeypress="return onlynumber();" data-id="min-quantity" data-lbl="5" style="height:40px;" type="number" value="1">
                                            <p style="font-size:13px;color:#a8a7a7;">Quantidade minima</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Inserção do campo e-mail no checkout -->
                            <div class="form-group">
                                <label class="text-label">Pedir e-mail do cliente</label> <i class="fas fa-info-circle helperlink" id="helper" data-toggle="tooltip" data-placement="top" title="" data-original-title="Caso ative essa opção, será solicitado o e-mail do cliente assim que finalizar a compra"></i>
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" alt="#components-email-request" class="custom-control-input approve-personalization" name="email-request-client" id="email-request-client">
                                    <label class="custom-control-label" id="email-request-client-lbl" for="email-request-client">&nbsp; Não</label>
                                </div>
                            </div>
                            <!-- Fim da div e-mail -->

                            <div class="form-group">
                                <label class="text-label">Checkout exclusivo do produto <span class="text-primary"><?= $product_name ?></span></label> <i class="fas fa-info-circle helperlink" id="helper" data-toggle="tooltip" data-placement="top" title="" data-original-title="Caso ative essa opção, este checkout sera exclusivo para o produto selecionado."></i>
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" alt="#components-checkout-exclusive" class="custom-control-input approve-personalization" name="checkout-exclusive" id="checkout-exclusive">
                                    <label class="custom-control-label" id="checkout-exclusive-lbl" for="checkout-exclusive">&nbsp; Não</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="text-label">Imagem da capa</label>
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" class="custom-control-input" name="select-banner" id="select-banner">
                                    <label class="custom-control-label" id="select-banner-lbl" for="select-banner">&nbsp; Não</label>
                                </div>

                                <div id="components-banner" class="d-none">
                                    <div class="selects-container d-flex flex-column justify-content-start">
                                        <ul class="nav nav-tabs d-flex justify-content-start" style="width:100%;">
                                            <li id="edit-desktop" style="width:27%;" class="mr-5 actives text-align-left">
                                                <a data-toggle="tab" href="#edit-menu1">
                                                    <svg style="width:16px;margin-right:3px;" fill="#494a51" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
                                                        <!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. -->
                                                        <path d="M528 0h-480C21.5 0 0 21.5 0 48v320C0 394.5 21.5 416 48 416h192L224 464H152C138.8 464 128 474.8 128 488S138.8 512 152 512h272c13.25 0 24-10.75 24-24s-10.75-24-24-24H352L336 416h192c26.5 0 48-21.5 48-48v-320C576 21.5 554.5 0 528 0zM512 288H64V64h448V288z" />
                                                    </svg>
                                                    Layout desktop
                                                </a>
                                            </li>

                                            <li class="ml-4 d-flex align-items-center justify-content-start" id="edit-mobile">
                                                <a data-toggle="tab" href="#edit-menu2">
                                                    <svg style="width:10px;margin-right:3px;" fill="#494a51" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
                                                        <!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. -->
                                                        <path d="M320 0H64C37.5 0 16 21.5 16 48v416C16 490.5 37.5 512 64 512h256c26.5 0 48-21.5 48-48v-416C368 21.5 346.5 0 320 0zM240 447.1C240 456.8 232.8 464 224 464H159.1C151.2 464 144 456.8 144 448S151.2 432 160 432h64C232.8 432 240 439.2 240 447.1zM304 384h-224V64h224V384z" />
                                                    </svg>
                                                    Layout mobile
                                                    <span tabindex="0" data-toggle="tooltip" data-placement="right" title="Caso não sejam cadastradas imagens no layout mobile, as imagens do desktop serão usadas">
                                                        <svg id="helper-hover" class="ml-1" fill="#898b96" style="width:15px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                                            <!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. -->
                                                            <path d="M256 0C114.6 0 0 114.6 0 256s114.6 256 256 256s256-114.6 256-256S397.4 0 256 0zM256 400c-18 0-32-14-32-32s13.1-32 32-32c17.1 0 32 14 32 32S273.1 400 256 400zM325.1 258L280 286V288c0 13-11 24-24 24S232 301 232 288V272c0-8 4-16 12-21l57-34C308 213 312 206 312 198C312 186 301.1 176 289.1 176h-51.1C225.1 176 216 186 216 198c0 13-11 24-24 24s-24-11-24-24C168 159 199 128 237.1 128h51.1C329 128 360 159 360 198C360 222 347 245 325.1 258z" />
                                                        </svg>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="tab-content mt-3">
                                        <div id="edit-menu1" class="tab-pane fade active show">
                                            <p>Imagem de capa</p>
                                            <div class="labels d-flex">
                                                <div class="col-lg-8 box-banner d-flex flex-column w-100">
                                                    <div id="SuperiorDesktop" class="drop-zone d-lg-flex d-sm-none d-none" style="height: 150px;">
                                                        <span class="drop-zone__prompt w-100">Arraste e solte os arquivos aqui</span>
                                                        <input type="file" name="imagemSuperiorDesktop" id="imagemSuperiorDesktop" class="drop-zone__input" accept="image/*">
                                                    </div>

                                                    <div class="btnsImagem options-btn d-flex mt-2 w-100">
                                                        <button type="button" alt="#SuperiorDesktop" style="border:1px solid #adbaca; cursor: default;" class="btn btn-outline-secondary w-50" disabled>Imagem superior</button>
                                                        <button type="button" alt="#SuperiorDesktop" class="remover-imagem btn btn-danger">Remover</button>
                                                        <button type="button" alt="#SuperiorDesktop" class="chamar-imagem btn btn-primary">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="#fff" style="width:20px;margin-right:5px;" viewBox="0 0 576 512">
                                                                <path d="M572.6 270.3l-96 192C471.2 473.2 460.1 480 447.1 480H64c-35.35 0-64-28.66-64-64V96c0-35.34 28.65-64 64-64h117.5c16.97 0 33.25 6.742 45.26 18.75L275.9 96H416c35.35 0 64 28.66 64 64v32h-48V160c0-8.824-7.178-16-16-16H256L192.8 84.69C189.8 81.66 185.8 80 181.5 80H64C55.18 80 48 87.18 48 96v288l71.16-142.3C124.6 230.8 135.7 224 147.8 224h396.2C567.7 224 583.2 249 572.6 270.3z" />
                                                            </svg>
                                                            Procurar
                                                        </button>
                                                    </div>
                                                    <p style="font-size:15px;">Tamanho de imagem máximo permitido 975x365px</p>

                                                    <div class="btnsImagem options-btn d-flex mt-2 w-100">
                                                        <button type="button" alt="#LateralDesktop" style="border:1px solid #adbaca; cursor: default;" disabled class="btn btn-outline-secondary w-50">Imagem Lateral</button>
                                                        <button type="button" alt="#LateralDesktop" class="remover-imagem btn btn-danger">Remover</button>
                                                        <button type="button" alt="#LateralDesktop" class="chamar-imagem btn btn-primary">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="#fff" style="width:20px;margin-right:5px;" viewBox="0 0 576 512">
                                                                <path d="M572.6 270.3l-96 192C471.2 473.2 460.1 480 447.1 480H64c-35.35 0-64-28.66-64-64V96c0-35.34 28.65-64 64-64h117.5c16.97 0 33.25 6.742 45.26 18.75L275.9 96H416c35.35 0 64 28.66 64 64v32h-48V160c0-8.824-7.178-16-16-16H256L192.8 84.69C189.8 81.66 185.8 80 181.5 80H64C55.18 80 48 87.18 48 96v288l71.16-142.3C124.6 230.8 135.7 224 147.8 224h396.2C567.7 224 583.2 249 572.6 270.3z" />
                                                            </svg>
                                                            Procurar
                                                        </button>
                                                    </div>
                                                    <p style="font-size:15px;">Tamanho de imagem máximo permitido 450x975px</p>
                                                </div>
                                                <div id="LateralDesktop" class="drop-zone d-lg-flex d-sm-none d-none" style="width: 200px; height: 500px;">
                                                    <span class="drop-zone__prompt w-100">Arraste e solte os arquivos aqui</span>
                                                    <input type="file" name="imagemLateralDesktop" id="imagemLateralDesktop" class="drop-zone__input" accept="image/*">
                                                </div>
                                            </div>
                                        </div>

                                        <div id="edit-menu2" class="tab-pane fade">
                                            <p>Imagem de capa</p>
                                            <div class="labels d-flex">
                                                <div class="col-lg-12 box-banner d-flex flex-column w-100">
                                                    <div id="SuperiorMobile" class="drop-zone d-lg-flex d-sm-none d-none" style="height: 150px;">
                                                        <span class="drop-zone__prompt w-100">Arraste e solte os arquivos aqui</span>
                                                        <input type="file" name="imagemSuperiorMobile" id="imagemSuperiorMobile" class="drop-zone__input" accept="image/*">
                                                    </div>

                                                    <div class="btnsImagem options-btn d-flex mt-2 w-100">
                                                        <button type="button" alt="#SuperiorMobile" style="border:1px solid #adbaca; cursor: default;" class="btn btn-outline-secondary w-100" disabled>Imagem superior</button>
                                                        <button type="button" alt="#SuperiorMobile" class="remover-imagem btn btn-danger">Remover</button>
                                                        <button type="button" alt="#SuperiorMobile" class="chamar-imagem btn btn-primary">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="#fff" style="width:20px;margin-right:5px;" viewBox="0 0 576 512">
                                                                <path d="M572.6 270.3l-96 192C471.2 473.2 460.1 480 447.1 480H64c-35.35 0-64-28.66-64-64V96c0-35.34 28.65-64 64-64h117.5c16.97 0 33.25 6.742 45.26 18.75L275.9 96H416c35.35 0 64 28.66 64 64v32h-48V160c0-8.824-7.178-16-16-16H256L192.8 84.69C189.8 81.66 185.8 80 181.5 80H64C55.18 80 48 87.18 48 96v288l71.16-142.3C124.6 230.8 135.7 224 147.8 224h396.2C567.7 224 583.2 249 572.6 270.3z" />
                                                            </svg>
                                                            Procurar
                                                        </button>
                                                    </div>
                                                    <p style="font-size:15px;">Tamanho de imagem máximo permitido 975x365px</p>

                                                    <div class="btnsImagem options-btn d-flex mt-2 w-100">
                                                        <button type="button" alt="#InferiorMobile" style="border:1px solid #adbaca; cursor: default;" disabled class="btn btn-outline-secondary w-100">Imagem Inferior</button>
                                                        <button type="button" alt="#InferiorMobile" class="remover-imagem btn btn-danger">Remover</button>
                                                        <button type="button" alt="#InferiorMobile" class="chamar-imagem btn btn-primary">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="#fff" style="width:20px;margin-right:5px;" viewBox="0 0 576 512">
                                                                <path d="M572.6 270.3l-96 192C471.2 473.2 460.1 480 447.1 480H64c-35.35 0-64-28.66-64-64V96c0-35.34 28.65-64 64-64h117.5c16.97 0 33.25 6.742 45.26 18.75L275.9 96H416c35.35 0 64 28.66 64 64v32h-48V160c0-8.824-7.178-16-16-16H256L192.8 84.69C189.8 81.66 185.8 80 181.5 80H64C55.18 80 48 87.18 48 96v288l71.16-142.3C124.6 230.8 135.7 224 147.8 224h396.2C567.7 224 583.2 249 572.6 270.3z" />
                                                            </svg>
                                                            Procurar
                                                        </button>
                                                    </div>
                                                    <p style="font-size:15px;">Tamanho de imagem máximo permitido 975x365px</p>

                                                    <div id="InferiorMobile" class="drop-zone d-lg-flex d-sm-none d-none" style="height: 150px;">
                                                        <span class="drop-zone__prompt w-100">Arraste e solte os arquivos aqui</span>
                                                        <input type="file" name="imagemInferiorMobile" id="imagemInferiorMobile" class="drop-zone__input" accept="image/*">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger light" data-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-success">Criar Checkout</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="ModalEditaOfertaPersonalizada" tabindex="-1" role="dialog" aria-labelledby="EditaOfertaPersonalizadaLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" style="width:80%;" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edição de checkout personalizado</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="EditCheckout" method="POST">
                            <input type="hidden" name="edit-action" value="edition-checkout">
                            <input type="hidden" name="edit-produto" value="<?php echo $product_id ?>">
                            <input type="hidden" name="checkout-name" value="">
                            <input type="hidden" name="edit-id-checkout">
                            <div class="form-group">
                                <label class="text-label">Nome do Checkout</label>
                                <input type="text" class="form-control" name="edit-name-checkout" id="edit-name-checkout">
                            </div>

                            <div class="form-group">
                                <label class="text-label">Botões de Suporte</label>
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" alt="#edit-components-support" class="custom-control-input approve-personalization" name="edit-suport-button" id="edit-select-whatsapp">
                                    <label class="custom-control-label" id="edit-select-whatsapp-lbl" for="edit-select-whatsapp">&nbsp; Não</label>
                                </div>
                                <div id="edit-components-support" class="d-none">
                                    <label for="select-whatsapp-input"> WhatsApp </label>
                                    <input type="text" id="edit-select-whatsapp-input" name="edit-select-whatsapp-input" class="form-control whats mb-1" placeholder="+55 99 99999-9999">

                                    <label for="select-email">Email </label>
                                    <input type="text" id="edit-select-email" name="edit-select-email" class="form-control email" placeholder="seuemail@dominio.com">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="text-label">Contador</label>

                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" alt="#edit-components-counter" alt="#components-counter" class="custom-control-input approve-personalization" name="edit-select-counter" id="edit-select-counter">
                                    <label class="custom-control-label" id="edit-select-counter-lbl" for="edit-select-counter">&nbsp; Não</label>
                                </div>

                                <div id="edit-components-counter" class="d-none">
                                    <div>
                                        <p class="mb-0">Cor de fundo</p>
                                        <label for="edit-color-bg-input" data-edit="counter" class="dinamicColor" style="cursor:crosshair;"> </label>
                                        <input type="color" id="edit-color-bg-input" name="edit-color-bg-input" value="#c66751" class="d-block" style="border-color: transparent;background-color: transparent; opacity:0; width:0; height:0;" />
                                    </div>
                                    <div>
                                        <p class="mb-0">Cor do texto</p>
                                        <label for="edit-color-text-input" data-edit="counterText" class="dinamicColor-text" style="cursor:crosshair;"> </label>
                                        <input type="color" id="edit-color-text-input" name="edit-color-text-input" value="#ffffff" class="d-block" style="border-color: transparent;background-color: transparent; opacity:0; width:0; height:0;" />
                                    </div>

                                    <div>
                                        <label for="text-counter">Texto</label>
                                        <input type="text" class="form-control" name="edit-text-counter" id="edit-text-counter">
                                        <span class="small">
                                            Obs: se não informado, será utilizado o texto padrão "A condição especial terminará em: "
                                        </span>
                                    </div>
                                    <div class="mt-1">
                                        <label for="text-counter">Timer</label>
                                        <input type="text" id="edit-select-time-input" name="edit-select-time-input" class="form-control time mb-1" placeholder="00:00">
                                        <span class="small">
                                            Obs: se não informado, será utilizado o tempo padrão 05:00 (5 minutos)
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="text-label">Cor</label>
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" alt="#edit-components-color" class="custom-control-input approve-personalization" name="edit-select-color" id="edit-select-color">
                                    <label class="custom-control-label" id="edit-select-color-lbl" for="edit-select-color">&nbsp; Não</label>
                                </div>

                                <div id="edit-components-color" class="d-none">
                                    <div>
                                        <p class="mb-0">Cor do campo de exibição do produto</p>
                                        <label for="edit-color-bg-box" class="dinamicColorBox" data-edit="edit-color-bg-box" style="cursor:crosshair;"> </label>
                                        <input type="color" id="edit-color-bg-box" name="edit-color-bg-box" value="#c66751" class="d-block" style="border-color: transparent;background-color: transparent; opacity:0; width:0; height:0;" />
                                    </div>

                                    <div>
                                        <p class="mb-0">Cor do texto de exibição do produto</p>
                                        <label for="edit-color-bg-boxText" class="dinamicColorBoxText" data-edit="edit-color-bg-boxText" style="cursor:crosshair;"> </label>
                                        <input type="color" id="edit-color-bg-boxText" name="edit-color-bg-boxText" value="#3d4465" class="d-block" style="border-color: transparent;background-color: transparent; opacity:0; width:0; height:0;" />
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="text-label">Notificações</label>
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" alt="#edit-components-notification" class="custom-control-input approve-personalization" name="edit-select-notification" id="edit-select-notification">
                                    <label class="custom-control-label" id="edit-select-notification-lbl" for="edit-select-notification">&nbsp; Não</label>
                                </div>

                                <div id="edit-components-notification" class="d-none">
                                    <p>Alertas disponiveis</p>
                                    <div class="d-flex" style="margin-bottom:20px;">
                                        <label class="container"><b data-edit-text="1">1 </b> &nbsp; Pessoa(s) estão comprando <?= $product_name ?> nesse momento.
                                            <input type="checkbox" name="edit-checkbox1">
                                            <span class="checkmark"></span>
                                        </label>
                                        <div class="input-notification-quantity">
                                            <input class="form-control" onkeypress="return onlynumber();" data-edit-lbl="1" data-id="edit-min-quantity" style="height:40px;" type="number" value="1">
                                            <p style="font-size:13px;color:#a8a7a7;">Quantidade minima</p>
                                        </div>
                                    </div>

                                    <div class="d-flex" style="margin-bottom:20px;">
                                        <label class="container"><b data-edit-text="2">1 </b> &nbsp; Pessoa(s) compraram <?= $product_name ?> essa semana.
                                            <input type="checkbox" name="edit-checkbox2">
                                            <span class="checkmark"></span>
                                        </label>

                                        <div class="input-notification-quantity">
                                            <input class="form-control" onkeypress="return onlynumber();" data-edit-lbl="2" style="height:40px;" data-id="edit-min-quantity" type="number" value="1">
                                            <p style="font-size:13px;color:#a8a7a7;">Quantidade minima</p>
                                        </div>
                                    </div>

                                    <div class="d-flex" style="margin-bottom:20px;">
                                        <label class="container"><b data-edit-text="3">1 </b> &nbsp; Pessoa(s) compraram <?= $product_name ?> nos ultimos 30 minutos.
                                            <input type="checkbox" name="edit-checkbox3">
                                            <span class="checkmark"></span>
                                        </label>

                                        <div class="input-notification-quantity">
                                            <input class="form-control" onkeypress="return onlynumber();" data-edit-lbl="3" data-id="edit-min-quantity" style="height:40px;" type="number" value="1">
                                            <p style="font-size:13px;color:#a8a7a7;">Quantidade minima</p>
                                        </div>
                                    </div>

                                    <div class="d-flex" style="margin-bottom:20px;">
                                        <label class="container"><b data-edit-text="4">1 </b> &nbsp; Pessoa(s) compraram <?= $product_name ?> hoje.
                                            <input type="checkbox" name="edit-checkbox4">
                                            <span class="checkmark"></span>
                                        </label>

                                        <div class="input-notification-quantity">
                                            <input class="form-control" onkeypress="return onlynumber();" data-id="edit-min-quantity" data-edit-lbl="4" style="height:40px;" type="number" value="1">
                                            <p style="font-size:13px;color:#a8a7a7;">Quantidade minima</p>
                                        </div>
                                    </div>

                                    <div class="d-flex" style="margin-bottom:20px;">
                                        <label class="container"><b data-edit-text="5">1 </b> &nbsp; Pessoa(s) compraram <?= $product_name ?> na ultima hora.
                                            <input type="checkbox" name="edit-checkbox5">
                                            <span class="checkmark"></span>
                                        </label>

                                        <div class="input-notification-quantity">
                                            <input class="form-control" onkeypress="return onlynumber();" data-id="edit-min-quantity" data-edit-lbl="5" style="height:40px;" type="number" value="1">
                                            <p style="font-size:13px;color:#a8a7a7;">Quantidade minima</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Edição do campo e-mail -->
                            <div class="form-group">
                                <label class="text-label">Pedir e-mail do cliente</label> <i class="fas fa-info-circle helperlink" id="helper" data-toggle="tooltip" data-placement="top" title="" data-original-title="Caso ative essa opção, será solicitado o e-mail do cliente assim que finalizar a compra"></i>
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" alt="#edit-components-email-request" class="custom-control-input approve-personalization" name="edit-email-request-client" id="edit-email-request-client">
                                    <label class="custom-control-label" id="edit-email-request-client-lbl" for="edit-email-request-client">&nbsp; Não</label>
                                </div>
                            </div>
                            <!-- Fim da div e-mail -->

                            <div class="form-group">
                                <label class="text-label">Checkout exclusivo do produto <span class="text-primary"><?= $product_name ?></span></label> <i class="fas fa-info-circle helperlink" id="helper" data-toggle="tooltip" data-placement="top" title="" data-original-title="Caso ative essa opção, este checkout sera exclusivo para o produto selecionado."></i>
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" alt="#components-edit-checkout-exclusive" class="custom-control-input approve-personalization" name="edit-checkout-exclusive" id="edit-checkout-exclusive">
                                    <label class="custom-control-label" id="edit-checkout-exclusive-lbl" for="edit-checkout-exclusive">&nbsp; Não</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="text-label">Imagem da capa</label>
                                <div class="custom-control custom-switch mb-3">
                                    <input type="checkbox" alt="#edit-components-banner" class="custom-control-input approve-personalization" name="edit-select-banner" id="edit-select-banner">
                                    <label class="custom-control-label" id="edit-select-banner-lbl" for="edit-select-banner">&nbsp; Não</label>
                                </div>
                                <div id="edit-components-banner" class="d-none">
                                    <div class="selects-container d-flex flex-column justify-content-start">
                                        <ul class="nav nav-tabs d-flex justify-content-start" style="width:100%;">
                                            <li id="desktop" style="width:27%;" class="mr-5 actives text-align-left">
                                                <a data-toggle="tab" href="#menu1">
                                                    <svg style="width:16px;margin-right:3px;" fill="#494a51" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
                                                        <!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. -->
                                                        <path d="M528 0h-480C21.5 0 0 21.5 0 48v320C0 394.5 21.5 416 48 416h192L224 464H152C138.8 464 128 474.8 128 488S138.8 512 152 512h272c13.25 0 24-10.75 24-24s-10.75-24-24-24H352L336 416h192c26.5 0 48-21.5 48-48v-320C576 21.5 554.5 0 528 0zM512 288H64V64h448V288z" />
                                                    </svg>
                                                    Layout desktop
                                                </a>
                                            </li>
                                            <li class="ml-4 d-flex align-items-center justify-content-start" id="mobile">
                                                <a data-toggle="tab" href="#menu2">
                                                    <svg style="width:10px;margin-right:3px;" fill="#494a51" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
                                                        <!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. -->
                                                        <path d="M320 0H64C37.5 0 16 21.5 16 48v416C16 490.5 37.5 512 64 512h256c26.5 0 48-21.5 48-48v-416C368 21.5 346.5 0 320 0zM240 447.1C240 456.8 232.8 464 224 464H159.1C151.2 464 144 456.8 144 448S151.2 432 160 432h64C232.8 432 240 439.2 240 447.1zM304 384h-224V64h224V384z" />
                                                    </svg>
                                                    Layout mobile
                                                    <span tabindex="0" role="button" data-toggle="tooltip" data-placement="right" title="Caso não sejam cadastradas imagens no layout mobile, as imagens do desktop serão usadas">
                                                        <svg id="helper-hover" class="ml-1" fill="#898b96" style="width:15px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                                            <!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. -->
                                                            <path d="M256 0C114.6 0 0 114.6 0 256s114.6 256 256 256s256-114.6 256-256S397.4 0 256 0zM256 400c-18 0-32-14-32-32s13.1-32 32-32c17.1 0 32 14 32 32S273.1 400 256 400zM325.1 258L280 286V288c0 13-11 24-24 24S232 301 232 288V272c0-8 4-16 12-21l57-34C308 213 312 206 312 198C312 186 301.1 176 289.1 176h-51.1C225.1 176 216 186 216 198c0 13-11 24-24 24s-24-11-24-24C168 159 199 128 237.1 128h51.1C329 128 360 159 360 198C360 222 347 245 325.1 258z" />
                                                        </svg>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="tab-content mt-3">
                                        <div id="menu1" class="tab-pane fade active show">
                                            <p>Imagem de capa</p>
                                            <div class="labels d-flex">
                                                <div class="col-lg-8 box-banner d-flex flex-column w-100">
                                                    <div id="edit-SuperiorDesktop" class="drop-zone d-lg-flex d-sm-none d-none" style="height: 150px;">
                                                        <span class="drop-zone__prompt w-100">Arraste e solte os arquivos aqui</span>
                                                        <input type="file" name="edit-imagemSuperiorDesktop" id="edit-imagemSuperiorDesktop" class="drop-zone__input" accept="image/*">
                                                    </div>

                                                    <div class="btnsImagem options-btn d-flex mt-2 w-100">
                                                        <button type="button" alt="#edit-SuperiorDesktop" style="border:1px solid #adbaca; cursor: default;" class="btn btn-outline-secondary w-50" disabled>Imagem superior</button>
                                                        <button type="button" alt="#edit-SuperiorDesktop" class="remover-imagem btn btn-danger">Remover</button>
                                                        <button type="button" alt="#edit-SuperiorDesktop" class="chamar-imagem btn btn-primary">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="#fff" style="width:20px;margin-right:5px;" viewBox="0 0 576 512">
                                                                <path d="M572.6 270.3l-96 192C471.2 473.2 460.1 480 447.1 480H64c-35.35 0-64-28.66-64-64V96c0-35.34 28.65-64 64-64h117.5c16.97 0 33.25 6.742 45.26 18.75L275.9 96H416c35.35 0 64 28.66 64 64v32h-48V160c0-8.824-7.178-16-16-16H256L192.8 84.69C189.8 81.66 185.8 80 181.5 80H64C55.18 80 48 87.18 48 96v288l71.16-142.3C124.6 230.8 135.7 224 147.8 224h396.2C567.7 224 583.2 249 572.6 270.3z" />
                                                            </svg>
                                                            Procurar
                                                        </button>
                                                    </div>
                                                    <p style="font-size:15px;">Tamanho de imagem máximo permitido 975x365px</p>

                                                    <div class="btnsImagem options-btn d-flex mt-2 w-100">
                                                        <button type="button" alt="#edit-LateralDesktop" style="border:1px solid #adbaca; cursor: default;" disabled class="btn btn-outline-secondary w-50">Imagem Lateral</button>
                                                        <button type="button" alt="#edit-LateralDesktop" class="remover-imagem btn btn-danger">Remover</button>
                                                        <button type="button" alt="#edit-LateralDesktop" class="chamar-imagem btn btn-primary">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="#fff" style="width:20px;margin-right:5px;" viewBox="0 0 576 512">
                                                                <path d="M572.6 270.3l-96 192C471.2 473.2 460.1 480 447.1 480H64c-35.35 0-64-28.66-64-64V96c0-35.34 28.65-64 64-64h117.5c16.97 0 33.25 6.742 45.26 18.75L275.9 96H416c35.35 0 64 28.66 64 64v32h-48V160c0-8.824-7.178-16-16-16H256L192.8 84.69C189.8 81.66 185.8 80 181.5 80H64C55.18 80 48 87.18 48 96v288l71.16-142.3C124.6 230.8 135.7 224 147.8 224h396.2C567.7 224 583.2 249 572.6 270.3z" />
                                                            </svg>
                                                            Procurar
                                                        </button>
                                                    </div>
                                                    <p style="font-size:15px;">Tamanho de imagem máximo permitido 450x975px</p>
                                                </div>
                                                <div id="edit-LateralDesktop" class="drop-zone d-lg-flex d-sm-none d-none" style="width: 200px; height: 500px;">
                                                    <span class="drop-zone__prompt w-100">Arraste e solte os arquivos aqui</span>
                                                    <input type="file" name="edit-imagemLateralDesktop" id="edit-imagemLateralDesktop" class="drop-zone__input" accept="image/*">
                                                </div>
                                            </div>
                                        </div>
                                        <div id="menu2" class="tab-pane fade">
                                            <p>Imagem de capa</p>
                                            <div class="labels d-flex">
                                                <div class="col-lg-12 box-banner d-flex flex-column w-100">
                                                    <div id="edit-SuperiorMobile" class="drop-zone d-lg-flex d-sm-none d-none" style="height: 150px;">
                                                        <span class="drop-zone__prompt w-100">Arraste e solte os arquivos aqui</span>
                                                        <input type="file" name="edit-imagemSuperiorMobile" id="edit-imagemSuperiorMobile" class="drop-zone__input" accept="image/*">
                                                    </div>

                                                    <div class="btnsImagem options-btn d-flex mt-2 w-100">
                                                        <button type="button" alt="#edit-SuperiorMobile" style="border:1px solid #adbaca; cursor: default;" class="btn btn-outline-secondary w-100" disabled>Imagem superior</button>
                                                        <button type="button" alt="#edit-SuperiorMobile" class="remover-imagem btn btn-danger">Remover</button>
                                                        <button type="button" alt="#edit-SuperiorMobile" class="chamar-imagem btn btn-primary">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="#fff" style="width:20px;margin-right:5px;" viewBox="0 0 576 512">
                                                                <path d="M572.6 270.3l-96 192C471.2 473.2 460.1 480 447.1 480H64c-35.35 0-64-28.66-64-64V96c0-35.34 28.65-64 64-64h117.5c16.97 0 33.25 6.742 45.26 18.75L275.9 96H416c35.35 0 64 28.66 64 64v32h-48V160c0-8.824-7.178-16-16-16H256L192.8 84.69C189.8 81.66 185.8 80 181.5 80H64C55.18 80 48 87.18 48 96v288l71.16-142.3C124.6 230.8 135.7 224 147.8 224h396.2C567.7 224 583.2 249 572.6 270.3z" />
                                                            </svg>
                                                            Procurar
                                                        </button>
                                                    </div>
                                                    <p style="font-size:15px;">Tamanho de imagem máximo permitido 975x365px</p>

                                                    <div class="btnsImagem options-btn d-flex mt-2 w-100">
                                                        <button type="button" alt="#edit-InferiorMobile" style="border:1px solid #adbaca; cursor: default;" disabled class="btn btn-outline-secondary w-100">Imagem Inferior</button>
                                                        <button type="button" alt="#edit-InferiorMobile" class="remover-imagem btn btn-danger">Remover</button>
                                                        <button type="button" alt="#edit-InferiorMobile" class="chamar-imagem btn btn-primary">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="#fff" style="width:20px;margin-right:5px;" viewBox="0 0 576 512">
                                                                <path d="M572.6 270.3l-96 192C471.2 473.2 460.1 480 447.1 480H64c-35.35 0-64-28.66-64-64V96c0-35.34 28.65-64 64-64h117.5c16.97 0 33.25 6.742 45.26 18.75L275.9 96H416c35.35 0 64 28.66 64 64v32h-48V160c0-8.824-7.178-16-16-16H256L192.8 84.69C189.8 81.66 185.8 80 181.5 80H64C55.18 80 48 87.18 48 96v288l71.16-142.3C124.6 230.8 135.7 224 147.8 224h396.2C567.7 224 583.2 249 572.6 270.3z" />
                                                            </svg>
                                                            Procurar
                                                        </button>
                                                    </div>
                                                    <p style="font-size:15px;">Tamanho de imagem máximo permitido 975x365px</p>

                                                    <div id="edit-InferiorMobile" class="drop-zone d-lg-flex d-sm-none d-none" style="height: 150px;">
                                                        <span class="drop-zone__prompt w-100">Arraste e solte os arquivos aqui</span>
                                                        <input type="file" name="edit-imagemInferiorMobile" id="edit-imagemInferiorMobile" class="drop-zone__input" accept="image/*">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger light" data-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-success">Editar Checkout</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="ModalCriarCupom" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Criar Cupom para "<?php echo $product_name; ?>"</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="AddCouponForm" action="novo-cupom" method="POST">
                            <input type="hidden" id="ActionInput" name="action" value="create-coupon">
                            <input type="hidden" name="produto" value="<?php echo $product_id; ?>">
                            <div class="form-group">
                                <label class="text-label">Texto do Cupom <i class="req-mark">*</i></label>
                                <input type="text" name="texto-cupom" style="text-transform: uppercase;" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label class="text-label">Ofertas <i class="req-mark">*</i></label>
                                <select class="ofertas-vinculadas-mult-select" name="ofertas-vinculadas[]" multiple="multiple">
                                    <?php
                                    $product_id = $_GET['detalhe'];
                                    $stmt = $conn->prepare('SELECT * FROM sales WHERE product_id = :product_id AND sale_trashed = 0 ORDER BY sale_id DESC');
                                    $stmt->execute(array('product_id' => $product_id));

                                    if ($stmt->rowCount() != 0) {
                                        $s = 1;
                                        while ($row = $stmt->fetch()) {
                                            $sale_name = $row['sale_name'];
                                            $sale_id = $row['sale_id'];
                                    ?>
                                            <option value="<?php echo $sale_id;  ?>"><?php echo $sale_name;  ?></option>
                                    <?php
                                        }
                                    }
                                    ?>
                                </select>
                                <input type="hidden" id="ofertas-vinculadas-mult-select-text" name="ofertas-vinculadas-mult-select-text" value="">
                            </div>
                            <div class="form-group">
                                <label class="text-label">% de Desconto<i class="req-mark">*</i></label>
                                <input type="text" name="porcentagem-cupom" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="text-label">Limite de Usos</i></label>
                                <input type="number" name="quantidade-cupom" class="form-control" required>
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger light" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Criar Cupom</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="ModalCriarPagina" style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Criar página de venda para "<?php echo $product_name; ?>"</h5>
                        <button type="button" class="close" data-dismiss="modal"><i class="fa fa-times"></i></button>
                    </div>
                    <form id="AddPageForm">
                        <div class="modal-body">
                            <input type="hidden" id="action" name="action" value="create-page">
                            <input type="hidden" name="produto" value="<?php echo $product_id; ?>">
                            <div class="form-group">
                                <label class="text-label">Nome <i class="req-mark">*</i></label>
                                <input type="text" name="nome-page" id="nome-page" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label class="text-label">Link <i class="req-mark">*</i></label>
                                <input type="url" name="link-page" id="link-page" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger light" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success">Criar Página</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="ModalAtualizarPagina" style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Atualizar página de venda</h5>
                        <button type="button" class="close" data-dismiss="modal"><i class="fa fa-times"></i></button>
                    </div>
                    <form id="UpdatePageForm">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="update-page">
                            <input type="hidden" name="produto" value="<?php echo $product_id; ?>">
                            <input type="hidden" name="id-page" id="id-page-up" value="">
                            <div class="form-group">
                                <label class="text-label">Nome <i class="req-mark">*</i></label>
                                <input type="text" name="nome-page" id="nome-page-up" class="form-control" required  value="">
                            </div>

                            <div class="form-group">
                                <label class="text-label">Link <i class="req-mark">*</i></label>
                                <input type="url" name="link-page" id="link-page-up" class="form-control" required  value="">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger light" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success">Atualizar Página</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="ModalCustomDays" tabindex="-1" role="dialog" aria-labelledby="ModalCustomDaysTitle" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <form id="UpdateDeliveryDaysProduct">
                        <div class="modal-header">
                            <h5 class="modal-title" id="ModalCustomDaysTitle">Selecione o dia</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <label class="text-label">
                                Disponibilizar entregas em quais dias da semana:<i class="req-mark">*</i>
                            </label>
                            <div class="form-group">
                                <select class="delivery-days-select" id="delivery-days" name="delivery-days[]" multiple="multiple" required>
                                </select>
                            </div>
                            <input type="hidden" id="inventory_product_id" name="inventory_product_id">
                            <input type="hidden" id="inventory_id_select" name="inventory_id_select">
                            <input type="hidden" id="product_id_select" name="product_id_select">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success">Salvar disponibilidades</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php
    /**
     * 
     * Exibe página de Padrão, com todos os produtos do usuário.
     * 
     * 
     */            
} else { ?>
    <div class="container-fluid">
        <!-- Add Order -->
        <div class="row">
            <?php
            @session_start();
            $user__id = $_SESSION['UserID'];

            # Lista os produtos do usuário.
            $stmt = $conn->prepare('SELECT product_id, product_name, product_code, product_image, product_rating, status FROM products WHERE user__id = :user__id AND product_trash = 0 ORDER BY product_id');
            $stmt->execute(array('user__id' => $user__id));

            if ($stmt->rowCount() != 0) {
                while ($row = $stmt->fetch()) {
                    $product_id = $row['product_id'];
                    $product_name = $row['product_name'];
                    $product_status = $row['status'];
                    $product_code = $row['product_code'];
                    $product_image = $row['product_image'];
                    $product_rating = $row['product_rating'];
            ?>
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="new-arrival-product">
                                    <div class="new-arrivals-img-contnent" style="max-height: 200px;">
                                        <?php if (in_array(strtolower(end(explode('.', $product_image))), ['mp4', 'mkv'])) : ?>
                                            <video class="w-100" src="<?php echo SERVER_URI . '/uploads/imagens/produtos/' . $product_image ?>" controls></video>
                                        <?php else : ?>
                                            <img src="<?php echo SERVER_URI . '/uploads/imagens/produtos/' . $product_image ?>" alt="<?php echo $product_name; ?>" alt="Gallery image 1" class="ecommerce-gallery-main-img active w-100" />
                                        <?php endif ?>
                                    </div>
                                    <div class="new-arrival-content text-center mt-3">
                                        <h4><a href="<?php echo SERVER_URI; ?>/produto/<?php echo $product_id; ?>"><?php echo $product_name; ?></a></h4>
                                        <p>Código: <span class="item"><?php echo $product_code; ?></span> </p>
                                        <ul class="star-rating">
                                            <?php
                                            $rate = new StarRating();
                                            $stars = $rate->markupFromRate($product_rating);
                                            ?>
                                            <li><i class="<?php echo $stars[0]; ?>"></i></li>
                                            <li><i class="<?php echo $stars[1]; ?>"></i></li>
                                            <li><i class="<?php echo $stars[2]; ?>"></i></li>
                                            <li><i class="<?php echo $stars[3]; ?>"></i></li>
                                            <li><i class="<?php echo $stars[4]; ?>"></i></li>
                                        </ul>
                                        <a href="<?php echo SERVER_URI; ?>/produto/<?php echo $product_id; ?>" type="button" class="btn btn-rounded btn-outline-success btn-block mt-2"><i class="fa fa-cogs" aria-hidden="true"></i>&nbsp;&nbsp;Mais Detalhes</a>
                                        <?php if ($product_status == 0) : ?>
                                            <div class="d-flex align-items-center justify-content-center mt-2">
                                                <span style="width:15px; height:15px; border-radius:100%; background-color:#ff9900;"></span>
                                                <span class="ml-2">Pendente</span>
                                            </div>
                                        <?php elseif ($product_status == 1) : ?>
                                            <div class="d-flex align-items-center justify-content-center mt-2">
                                                <span style="width:15px; height:15px; border-radius:100%; background-color:#20c997;"></span>
                                                <span class="ml-2">Aprovado</span>
                                            </div>
                                        <?php else : ?>
                                            <div class="d-flex align-items-center justify-content-center mt-2">
                                                <span style="width:15px; height:15px; border-radius:100%; background-color:#FF4847;"></span>
                                                <span class="ml-2">Recusado</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                }
            } else {
                # Exibe mensagem genérica, caso o usuário não tenha produtos cadastrados/afiliados.
                ?>
                <div class="alert alert-success solid fade show m-auto">
                    <i class="fa fa-info-circle" aria-hidden="true"></i><strong> Você ainda não tem produtos.</strong> Cadastre ou se afilie a um produto agora mesmo, para vê-lo aqui. <a href="<?php if ($_SESSION['UserPlanString'] == 'Afiliado') {
                                                                                                                                                                                                        echo SERVER_URI . "/mercado/";
                                                                                                                                                                                                    } else {
                                                                                                                                                                                                        echo SERVER_URI . "/produto/novo/";
                                                                                                                                                                                                    } ?>" class="badge badge-sm light badge-success ml-1"><?php if ($_SESSION['UserPlanString'] == 'Afiliado') {
                                                                                                                                                                                                                                                                echo "Afiliar Agora";
                                                                                                                                                                                                                                                            } else {
                                                                                                                                                                                                                                                                echo "Cadastrar Agora ";
                                                                                                                                                                                                                                                            } ?></a>
                </div>
            <?php
            }
            ?>

        </div>
    </div>

    <? 
} ?>



<?php
    require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>
<!-- <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script> -->
<script src="<?php echo SERVER_URI ?>/js/summernote-pt-BR.min.js?v=12" type="text/javascript"></script>

<script>
    $(document).ready(function() { 
        $('#product-page').DataTable({ 
            searching: false, 
            paging: true,
            select: false,
            lengthChange: false,
        });    
        
        
        $('.delete-page').click(function() {    
            
            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success ml-2',
                    cancelButton: 'btn btn-danger'
                },
                buttonsStyling: false,            
                reverseButtons: true,
            })

            swalWithBootstrapButtons.fire({
                title: 'Deletar Página',
                text: "Você tem certeza que deseja excluir essa página de venda ?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Deletar',
                cancelButtonText: 'Cancelar',
            }).then((result) => {
                if (result.isConfirmed) {
                    
                    let page_id = $(this).data('id');
                    let url = u + "/ajax/add-page-product.php"; 

                    let formData = new FormData();
                    formData.append('page_id', page_id);
                    formData.append('action', 'delete-page');

                    $.ajax({
                        url: url,
                        type: "POST",
                        data: formData,
                        dataType: "json",
                        processData: false,
                        contentType: false,
                        beforeSend: function(){
                            display_loader();
                        } ,
                        complete: function() {
                            display_loader(false);
                        },
                        success: function (feedback) {
                            swalWithBootstrapButtons.fire({
                                title: feedback.title,
                                text: feedback.msg,
                                icon: feedback.type,
                            }).then((value) => {
                                if (feedback.type == "success") {
                                    document.location.reload(true);
                                }
                            });
                        },
                    }).fail(function (data) {
                        swalWithBootstrapButtons.fire({
                            title: "Erro de Conexão",
                            text: "Não foi possível excluir a página de venda. atualize a página e tente novamente!",
                            icon: 'error',
                            showCancelButton: true,
                            confirmButtonText: 'Atualizar',
                            cancelButtonText: 'Cancelar',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                document.location.reload(true);
                            }
                        });
                    });                    
                } else if (
                    /* Read more about handling dismissals below */
                    result.dismiss === Swal.DismissReason.cancel
                ) {
                    swalWithBootstrapButtons.fire(
                    'Cancelado',
                    'Operação cancelada :)',
                    'error'
                    )
                }
            })
        })

        $('.update-page').click(function() {    

            let page_id = $(this).data('id');
            let url = u + "/ajax/add-page-product.php"; 

            let formData = new FormData();
            formData.append('page_id', page_id);
            formData.append('action', 'get-page');

            $.ajax({
                url: url,
                type: "POST",
                data: formData,
                dataType: "json",
                processData: false,
                contentType: false,
                beforeSend: function(){
                    display_loader();
                } ,
                complete: function() {
                    display_loader(false);
                },
                success: function (feedback) {
                    if (feedback.type == "success") {
                        let dados = feedback.data;
                        $('#id-page-up').val(dados.page_id);
                        $('#nome-page-up').val(dados.page_name);
                        $('#link-page-up').val(dados.page_url);

                        $('#ModalAtualizarPagina').modal("toggle");
                    } else {
                        swalWithBootstrapButtons.fire({
                            title: feedback.title,
                            text: feedback.msg,
                            icon: feedback.type,
                        })
                    }
                },
            }).fail(function (data) {
                swalWithBootstrapButtons.fire({
                    title: "Erro de Conexão",
                    text: "Atualize a página e tente novamente!",
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonText: 'Atualizar',
                    cancelButtonText: 'Cancelar',
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.location.reload(true);
                    }
                });
            });   
        })
    
        $('#AddPageForm').submit(function() {   
    
            let AddPageForm = document.getElementById('AddPageForm');
    
            // Instância o FormData passando como parâmetro o formulário
            let formData = new FormData(AddPageForm);
    
            let url = u + "/ajax/add-page-product.php"; 
    
            $.ajax({
                url: url,
                type: "POST",
                data: formData,
                dataType: "json",
                processData: false,
                contentType: false,
                beforeSend: function(){
                    display_loader();
                    $('#ModalCriarPagina').modal("toggle"); 
                } ,
                complete: function() {
                    display_loader(false);
                },
                success: function (feedback) {
                    Swal.fire({
                        title: feedback.title,
                        text: feedback.msg,
                        icon: feedback.type,
                    }).then((value) => {
                        if (feedback.type == "success") {
                            document.location.reload(true);
                        } else { 
                            $('#ModalCriarPagina').modal("toggle"); 
                        }
                    });
                },
            }).fail(function (data) {
                Swal.fire({
                    title: "Erro de Conexão",
                    text: "Não foi possível cadastrar página de venda. atualize a página e tente novamente!",
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonText: 'Atualizar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                    customClass: {
                        confirmButton: 'ml-2 btn btn-success',  
                        cancelButton: 'btn btn-danger',
                    },                    
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.location.reload(true);
                    }
                });
            });
            
            return false;
        });

        $('#UpdatePageForm').submit(function() {   
    
            let UpdatePageForm = document.getElementById('UpdatePageForm');

            // Instância o FormData passando como parâmetro o formulário
            let formData = new FormData(UpdatePageForm);

            let url = u + "/ajax/add-page-product.php"; 

            $.ajax({
                url: url,
                type: "POST",
                data: formData,
                dataType: "json",
                processData: false,
                contentType: false,
                beforeSend: function(){
                    display_loader();
                    $('#ModalAtualizarPagina').modal("toggle"); 
                } ,
                complete: function() {
                    display_loader(false);
                },
                success: function (feedback) {
                    Swal.fire({
                        title: feedback.title,
                        text: feedback.msg,
                        icon: feedback.type,
                    }).then((value) => {
                        if (feedback.type == "success") {
                            document.location.reload(true);
                        } else { 
                            $('#ModalAtualizarPagina').modal("toggle"); 
                        }
                    });
                },
            }).fail(function (data) {
                Swal.fire({
                    title: "Erro de Conexão",
                    text: "Não foi possível atualizar a página de venda. atualize a página e tente novamente!",
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonText: 'Atualizar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                    customClass: {
                        confirmButton: 'ml-2 btn btn-success',  
                        cancelButton: 'btn btn-danger',
                    },                    
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.location.reload(true);
                    }
                });
            });
            
            return false;
        });
    });
</script>