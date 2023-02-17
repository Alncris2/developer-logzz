<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
include(dirname(__FILE__) . '/../../includes/classes/StarRating.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
} else if ($_SESSION['UserPlan'] != 5){
    header('Location: ' . SERVER_URI . '/meus-produtos/');
}

$product_page = $sale_page = true;   
$select_datatable_page = true;
$page_title = "Todo os Produtos | Logzz";           
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

    # Busca os detalhes do produto cujo ID foi passado via GET.
    $stmt = $conn->prepare('SELECT * FROM products WHERE product_id = :product_id AND product_trash = 0');
    $stmt->execute(array('product_id' => $_GET['detalhe']));

    if ($stmt->rowCount() != 0) {
        while ($row = $stmt->fetch()) {
            $product_id                                 = $row['product_id'];
            $product_code                               = $row['product_code'];
            $product_name                               = $row['product_name'];
            $product_status                             = $row['status'];
            $product_price                              = $row['product_price'];
            $product_weight                             = $row['product_weight'];
            $product_type_packaging                     = $row['type_packaging'];
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

    $get_name_productor = "SELECT full_name FROM users u INNER JOIN products p WHERE p.product_id = :product_id AND p.user__id = u.user__id";
    $get_name_productor_query = $conn->prepare($get_name_productor);
    $get_name_productor_query->execute([
        'product_id' => $product_id
    ]);

    $name_productor = $get_name_productor_query->fetch()['full_name'];
    ?>

    <style>
        .note-editor{
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
      <!-- row -->
        <div class="row">
            <div class="col-xl-12 col-xxl-12">
                <div class="card">
                    <div class="card-header d-flex align-items-start">
                        <div class="d-flex flex-column">
                            <h4 class="card-title">Detalhes do Produto</h4>
                            <p>Produtor: <?= $name_productor?></p>
                        </div>
                        <div class="row">
                            <?php if($product_status == 0): ?>
                                <div class="d-flex align-items-center justify-content-center">
                                    <span style="width:15px; height:15px; border-radius:100%; background-color:#ff9900;"></span>
                                    <span class="ml-2">Aguardando aprovação</span>
                                </div>
                            <?php elseif($product_status == 1): ?>
                                <div class="d-flex align-items-center justify-content-center">
                                    <span style="width:15px; height:15px; border-radius:100%; background-color:#20c997;"></span>
                                    <span class="ml-2">Aprovado</span>
                                </div>
                            <?php else: ?>
                                <div class="d-flex align-items-center justify-content-center">
                                    <span style="width:15px; height:15px; border-radius:100%; background-color:#FF4847;"></span>
                                    <span class="ml-2">Recusado</span>
                                </div>
                            <?php endif; ?>
                            <?php if ($_SESSION['UserPlan'] == 5): ?>
                                <div class="ml-3" style="float: right;z-index: 999;margin-right: 20px;" class="dropdown text-sans-serif position-static"><button class="btn btn-success tp-btn-light sharp" type="button" id="order-dropdown-0" data-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="true"><span><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="18px" height="18px" viewBox="0 0 24 24" version="1.1">
                                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                    <rect x="0" y="0" width="24" height="24"></rect>
                                                    <circle fill="#000000" cx="5" cy="12" r="2"></circle>
                                                    <circle fill="#000000" cx="12" cy="12" r="2"></circle>
                                                    <circle fill="#000000" cx="19" cy="12" r="2"></circle>
                                                </g>
                                            </svg></span></button>
                                    <div class="dropdown-menu dropdown-menu-right border py-0" aria-labelledby="order-dropdown-0" x-placement="top-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(825px, 168px, 0px);">
                                        <div class="py-2">
                                            <a class="dropdown-item update-product-status" data-status="2" href="#"  data-toggle="modal" data-target="#modalRecuseProduct">Recusar produto</a>
                                            <a class="dropdown-item aprove-product-status" data-status="1" data-id="<?= $_GET['detalhe']; ?>" href="#">Aprovar produto</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="smartwizard" class="form-wizard order-create">
                            <div class="row">
                                <div class="col-lg-4 mb-2">
                                    <div role="tabpanel" class="tab-pane fade show active" id="first">
                                        <!-- <img class="img-fluid" style="cursor: pointer;" id="product-image" src="<?php echo SERVER_URI; ?>/uploads/imagens/produtos/<?php echo $product_image; ?>" alt=""> -->
                                        <?php if ($isVideo) : ?>
                                            <video id="product-video" class="w-100" src="<?php echo SERVER_URI . '/uploads/imagens/produtos/' . $product_image ?>" controls></video>
                                            <img id="product-image" class="img-fluid w-100" style="display: none;" id="product-image">
                                        <?php else : ?>
                                            <img id="product-image" src="<?php echo SERVER_URI . '/uploads/imagens/produtos/' . $product_image ?>" alt="<?php echo $product_name; ?>" alt="Gallery image 1" class="ecommerce-gallery-main-img active w-100" />
                                            <video id="product-video" class="w-100" style="display: none;" controls></video>
                                        <?php endif ?> 

                                        <div class="form-group mt-4 text-center">
                                            <button type="button" id="btn-product-image" class="btn btn-success">Imagem ou Vídeo Principal</button>
                                            <p class="formato">png/jpeg/mp4/mkv/gif/webp <br/> Tamanho máximo: foto 5mb | video 5mb</p>
                                        </div>
                                    
                                        <div role="tabpanel" class="tab-pane fade show active pt-4">
                                            <div class="row">

                                                <?php for($aux = 0; $aux < 9; $aux++) { ?>
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
                                                        <img class="img-fluid p-2 product-images" style="cursor: pointer;" src="<?php echo SERVER_URI . '/uploads/imagens/produtos/' . $product_images[$aux]?>" name="<?=$product_images[$aux]?>">
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
                                        <input multiple type="file" name="product-images[]" id="input-file-product-images" style="display: none;" accept=".png, .jpg, .jpeg, .gif, .jfif, .webp">
                                        
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
                                            <input 
                                                type="text" 
                                                name="weight-product" 
                                                class="form-control weight" 
                                                placeholder="0.000" 
                                                value="<?= $product_weight ?>"
                                                onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                                                required
                                            />
                                        </div>
                                    
                                        <div class="form-group">
                                            <label class="text-label">Tipo de embalagem
                                                <i class="req-mark">*</i> 
                                                <i class="fas fa-info-circle" style="color:#ccc;"data-toggle="tooltip" data-placement="top" title="Comprimento x Largura x Altura"></i>
                                            </label>
                                            <select class="form-control" name="kind-packing">
                                                <option selected disabled required>Selecione uma embalagem</option>
                                                <option value="1" <?php if($product_type_packaging == 1) echo 'selected' ?>>Caixa 16cm x 11cm x 6cm (Pequena)</option>
                                                <option value="2" <?php if($product_type_packaging == 2) echo 'selected' ?>>Caixa 24cm x 15cm x 10cm (Média)</option>
                                                <option value="3" <?php if($product_type_packaging == 3) echo 'selected' ?>>Caixa 50cm x 33cm x 20cm  (Grande)</option>
                                            </select>
                                        </div>
                                    
                                        <div class="form-group">
                                            <button type="submit" id="SubmitButton" class="btn btn-success">Atualizar Produto</button>
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

        <div id="product-accordion" class="accordion accordion-with-icon">
            
            <?php if($product_status == 1):

                $get_sales_quantity = $conn->prepare('SELECT sale_quantity FROM sales WHERE product_id = :product_id AND (sale_trashed = 0) GROUP BY sale_quantity ORDER BY sale_quantity ASC');
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
                        <button 
                            type="button"  
                            data-toggle="modal" 
                            data-target="#ModalCriarOferta" 
                            class="btn btn-rounded btn-success btn-sm"
                            <?php if($product_status != 1): ?>
                                disabled
                            <?php endif; ?>
                        >
                            <span class="btn-icon-left text-success">
                                <i class="fa fa-plus color-success"></i>
                            </span>
                            Criar Oferta
                        </button>
                    </div>
                    <div id="sales-collapse" class="card-bodyaccordion__body collapse" data-parent="#product-accordion">
                        <div class="table-responsive accordion__body--text">
                            <?php if ($get_sales_quantity->rowCount() != 0) {   
                                echo '<div class="tab-content" id="tabContent">'; 
                                    for ($aux = 0; $aux < count($quantities); $aux++) { ?>
                                        <div class="tab-pane fade <?= $aux == 0 ? 'show active' : '' ?>" id="quantity-<?= $quantities[$aux]['sale_quantity'] ?>" role="tabpanel" aria-labelledby="quantity-<?= $quantities[$aux]['sale_quantity'] ?>-tab">
                                            <table class="table table-responsive-md hotlinks">
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
                                                    $product_id = addslashes($_GET['detalhe']);
                                                    $get_sales_list = $conn->prepare("SELECT * FROM sales WHERE product_id = :product_id AND sale_quantity = :quantity AND sale_product_name = '#ProductName' AND sale_trashed = 0 ORDER BY sale_id DESC");
                                                    $get_sales_list->execute(array('product_id' => $product_id, 'quantity' => $quantities[$aux]['sale_quantity']));
                                                    if ($get_sales_list->rowCount() != 0) {
                                                        $s = 1;
                                                        while ($row = $get_sales_list->fetch()) {
                                                            $sale_name = $row['sale_name'];
                                                            $sale_quantity = $row['sale_quantity'];
                                                            $sale_price = $row['sale_price'];
                                                            $sale_status = $row['sale_status'];
                                                            $sale_id = $row['sale_id']; ?>

                                                            <tr>
                                                                <td class="text-center"><strong><?php echo $s;  ?></strong></td>
                                                                <td class="text-left"><?php echo $sale_name;  ?></td>
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
                                                            <?php $s = $s + 1;
                                                        }
                                                    } else { ?>
                                                        <tr>
                                                            <td class="text-center" colspan="5">Este produto ainda não possui Ofertas.</td>
                                                        </tr>
                                                        <?php
                                                    } ?>
                                                </tbody>
                                            </table>
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
                            } ?>
                        </div>
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

                <!-- Pages Accordion -->
                <div class="card accordion__item">
                    <div class="card-header collapsed" data-toggle="collapse" data-target="#pages-collapse" aria-expanded="false">
                        <h4 class="card-title"><i style="color: #777777" class="fas fa-angle-down"></i>&nbsp; Páginas de vendas</h4>
                        <button type="button" data-toggle="modal" data-target="#ModalCriarPagina" class="btn btn-rounded btn-success btn-sm"><span class="btn-icon-left text-success"><i class="fa fa-plus color-success"></i></span>Criar Página</button>
                    </div>
                    <div id="pages-collapse" class="card-bodyaccordion__body collapse" data-parent="#product-accordion">
                        <div class="table-responsive accordion__body--text">
                            <table id="product-page" class="table table-responsive-md hotlinks" data-page-length="3">
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
                                                    <button class="btn btn-danger btn-xs sharp delete-page" data-id="<?= $page['page_id'] ?>"><i class="fa fa-trash"></i></button>
                                                    <button class="btn btn-success btn-xs sharp update-page" data-id="<?= $page['page_id'] ?>"><i class="fa fa-pen-alt"></i></button>
                                                </td>
                                            </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
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
            } ?>

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
        </div>

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
                                $custom_commission = $get_sale_custom_commission->fetch(); ?>
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


        <div class="modal fade" id="ModalCriarCupom" style="display: none;" aria-hidden="true">
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

        <!-- Modal -->
        <div class="modal fade" id="modalRecuseProduct" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Recusar produto</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="updateToRecuse" action="">
                    <div class="modal-body">
                    <label for="recuse">Informe ao usúario o motivo da recusa na solicitação de aprovação desse produto! <i class="req-mark">*</i></label>
                    <textarea class="form-control" id="" name="text-recuse" rows="7" maxlength="600" required></textarea>               
                    <input type="hidden" name="status" value="2">
                    <input type="hidden" name="id" value="<?= $_GET['detalhe']; ?>">                   
                    </div>
                    <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
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
     * Exibe página de Padrão, com todos os produtos.
     * 
     * 
     */
} else {

    if (isset($_GET['filtro']) && $_GET['filtro'] == 'ativo') {
    
        # Seleciona todos os produtos
        $filter_result = array();
    
        $get_all_products = $conn->prepare('SELECT product_id FROM products WHERE product_trash = 0 ORDER BY product_rating DESC');
        $get_all_products->execute();
    
        while ($product_id = $get_all_products->fetch()) {
            array_push($filter_result, $product_id['product_id']);
        }

            # Filtro por PRODUTOR
        if (!(empty($_GET['status']))) {   
            $filter_status_result = array();

            $status = addslashes($_GET['status'] - 1); 

            $status_ids = $conn->prepare('SELECT product_id FROM products WHERE product_trash = 0 AND status = :status');
            $status_ids->execute(array('status' => $status));  

            while ($status_id = $status_ids->fetch()) {
                array_push($filter_status_result, $status_id['product_id']); 
            }

            $filter_result = array_intersect($filter_result, $filter_status_result);
        }

        # Filtro por PRODUTOR
        if (!(empty($_GET['busca']))) {   
            $filter_name_result = array();

            $name = "%". addslashes($_GET['busca']) ."%"; 

            $product_name_ids = $conn->prepare('SELECT product_id FROM products WHERE product_trash = 0 AND product_name LIKE :product_name');
            $product_name_ids->execute(array('product_name' => $name));

            while ($product_name_id = $product_name_ids->fetch()) {
                array_push($filter_name_result, $product_name_id['product_id']); 
            }

            $filter_result = array_intersect($filter_result, $filter_name_result);
        }

        # Filtro por PRODUTOR
        if (!(empty($_GET['produtor']))) {   
            $filter_produtor_result = array();

            $produtor = addslashes($_GET['produtor']); 

            $produtor_ids = $conn->prepare('SELECT product_id FROM products WHERE product_trash = 0 AND user__id = :user__id');
            $produtor_ids->execute(array('user__id' => $produtor));

            while ($produtor_id = $produtor_ids->fetch()) {
                array_push($filter_produtor_result, $produtor_id['product_id']); 
            }

            $filter_result = array_intersect($filter_result, $filter_produtor_result);
        }

        $total = count($filter_result);
    } else {

        # Total de produtos que serão exibidos
        $get_products_total = $conn->prepare('SELECT COUNT(*) FROM products WHERE product_trash = 0 ');
        $get_products_total->execute();  

        $total = $get_products_total->fetch();
        $total = $total[0];
    }
    
    $per_page = $total > 0 ? 12 : 0;

    $pages = $total > 0 ? ceil($total / $per_page) : 1;

    $page = min($pages, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array(
        'options' => array(
            'default'   => 1,
            'min_range' => 1,
            'max_range' => $total,
        ),
    )));

    $offset = ($page * $per_page) - $per_page;
    $limit = $offset + $per_page;
    if ($limit > $total) {
        $limit = $total;
    }

    if (!(isset($_GET['filtro']) && $_GET['filtro'] == 'ativo')) {
        # Texto de Navegação Padrão
        $breadcumb = "Exibindo produtos <b>" . ($offset + 1) . "</b> a <b>" . $limit . "</b> de <b>" . $total . "</b> no total";
    } else {

        $breadcumb = "Filtros Ativos:&nbsp; ";

        if (!(empty(@$_GET['nome']))) {
            $breadcumb .= '<span class="badge badge-success light">Nome: <b>' . addslashes($_GET['nome']) . '</b></span>';
        }

        if (!(empty(@$_GET['status']))) {
            switch($_GET['status']){
                case 1: 
                    $status = "Pendente";
                break;
                case 2:
                    $status = "Aprovado";
                break;
                case 3:
                    $status = "Reprovado";
                break;
                default: 
                    $status = 'Status informado inexistente';
            } 

            $breadcumb .= '<span class="badge badge-success light">Status: <b>' . $status . '</b></span>';
        }

        if (!(empty(@$_GET['produtor']))) {
            $get_product_produtor = $conn->prepare('SELECT full_name FROM users WHERE user__id = :user__id LIMIT 1');
            $get_product_produtor->execute(array('user__id' => $produtor));

            $produtor_string = $get_product_produtor->fetch();
            $produtor_string = $produtor_string[0];

            $breadcumb .= '<span class="badge badge-success light">Produtor: <b>' . $produtor_string . '</b></span>';
        }
    }

    if (isset($_GET['filtro']) && $_GET['filtro'] == 'ativo') {
        $result = "'" . implode("','", $filter_result) . "'";  
        
        // BUSCA TODOS OS PRODUTOS NORMALMENTE
        $stmt = $conn->prepare("SELECT product_id, product_name, product_code, product_image, product_rating, status FROM products WHERE product_trash = 0 AND product_id IN ($result) ORDER BY product_id DESC LIMIT $offset, $per_page");
        $stmt->execute();  
  
    } else {
        // BUSCA TODOS OS PRODUTOS NORMALMENTE
        $stmt = $conn->prepare("SELECT product_id, product_name, product_code, product_image, product_rating, status FROM products WHERE product_trash = 0 ORDER BY product_id DESC LIMIT $offset, $per_page");
        $stmt->execute();
    }

    $num_orders = $total;

    ?>
    <div class="container-fluid">
        <div class="d-flex flex-wrap mb-2 align-items-center justify-content-between">
            <div class="mb-3 mr-3">
                <h6 class="fs-14 text-muted mb-0"><?php echo $breadcumb; ?></h6>
                <!-- <h6 class="fs-16 text-black font-w600 mb-0"><?php echo $num_orders;  ?> Produtos.</h6> -->
            </div>
            <div class="event-tabs mb-3 mr-3">
            </div>
            <div class="d-flex mb-3">
               <button type="button" class="btn btn-rounded btn-success filter-btn" class="btn btn-success text-nowrap"><i class="fas fa-sliders-h scale2 mr-2" aria-hidden="true"></i>Filtros</button>
            </div>
        </div>
        <!-- Add Order -->
        <div class="row">
            <?php

            if ($stmt->rowCount() != 0) {
                while ($row = $stmt->fetch()) {
                    $product_id = $row['product_id'];
                    $product_status = $row['status'];
                    $product_name = $row['product_name'];
                    $product_code = $row['product_code'];
                    $product_image = $row['product_image'];
                    $product_rating = $row['product_rating'];


                    # verifica se a imagem principal é um video ou imagem
                    $image_filetype_array = explode('.', $product_image);
                    $filetype = strtolower(end($image_filetype_array));

                    $isVideo = in_array($filetype, ['mp4', 'mkv']);

             ?>
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="new-arrival-product">
                                    <div class="new-arrivals-img-contnent" style="max-height: 200px;">
                                        <!-- <img class="img-fluid" src="<?php echo SERVER_URI . '/uploads/imagens/produtos/' . $product_image; ?>" alt=""> -->
                                        <?php if ($isVideo) : ?>
                                            <video id="product-video" class="w-100" src="<?php echo SERVER_URI . '/uploads/imagens/produtos/' . $product_image ?>" controls></video>
                                            <img id="product-image" class="img-fluid w-100" style="display: none;" id="product-image">
                                        <?php else : ?>
                                            <img id="product-image" src="<?php echo SERVER_URI . '/uploads/imagens/produtos/' . $product_image ?>" alt="<?php echo $product_name; ?>" alt="Gallery image 1" class="ecommerce-gallery-main-img active w-100" />
                                            <video id="product-video" class="w-100" style="display: none;" controls></video>
                                        <?php endif ?> 
                                    </div>
                                    <div class="new-arrival-content text-center mt-3">
                                        <h4><a href="<?php echo SERVER_URI; ?>/produto/<?php echo $product_id; ?>"><?php echo $product_name; ?></a></h4>
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
                                        <a href="<?php echo SERVER_URI; ?>/produtos/todos/<?php echo $product_id; ?>" type="button" class="btn btn-rounded btn-outline-success btn-block mt-2"><i class="fa fa-cogs" aria-hidden="true"></i>&nbsp;&nbsp;Mais Detalhes</a>
                                        <?php if($product_status == 0): ?>
                                            <div class="d-flex align-items-center justify-content-center mt-2">
                                                <span style="width:15px; height:15px; border-radius:100%; background-color:#ff9900;"></span>
                                                <span class="ml-2">Pendente</span>
                                            </div>
                                        <?php elseif($product_status == 1): ?>
                                            <div class="d-flex align-items-center justify-content-center mt-2">
                                                <span style="width:15px; height:15px; border-radius:100%; background-color:#20c997;"></span>
                                                <span class="ml-2">Aprovado</span>
                                            </div>
                                        <?php else: ?>
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
                <div class="col-md-12 mt-5 text-center d-flex flex-column align-items-center justify-content-center">
                  <p>
                    Nenhum produto encontrado com os filtros
                  </p>
                  <a href="<?= SERVER_URI ?>/produtos/todos/" class="text-center d-block mt-3 btn text-nowrap" style="background-color:#fff; color:#212121;">Limpar filtros</a>
                </div>
            <?php
            }
            ?>
        </div>
        <?php if($stmt->rowCount() != 0): ?>
          <?php
          if ($pages >= 2) {
          ?>
              <div class="row">
                  <div class="col-md-12">
                      <nav style="display: flex; justify-content: center;">
                          <ul class="pagination pagination-sm pagination-circle">
                              <?php
                              // PAGINAÇÃO SEM FILTRO FILTRO
                              if ($page > 1) { ?>
                                      <li class="page-item page-indicator">
                                          <a title="Página Anterior" class="page-link" href="<?php echo SERVER_URI . "/produtos/todos/?filtro=ativo&busca=".$_GET['busca']."&status=".$_GET['status']."&produtor=".$_GET['produtor']."&page=" . @($page - 1); ?>">
                                              <i class="fa fa-chevron-left"></i></a>
                                      </li>
                              <?php } 
                              $p = 1;
                              while ($p <= $pages) { ?>
                                  <li class="page-item <?php if ($p == $page) { echo 'active'; } ?>">
                                      <a title="Ir para a página <?php echo $p; ?>" class="page-link" href="<?php echo SERVER_URI . "/produtos/todos/?filtro=ativo&busca=".$_GET['busca']."&status=".$_GET['status']."&produtor=".$_GET['produtor']."&page=" . $p; ?>"><?php echo $p; ?></a>
                                  </li>
                              <?php
                                  $p = $p + 1;
                              }
                              if ($page < $pages) { ?>
                                  <li class="page-item page-indicator">
                                      <a title="Próxima Página" class="page-link" href="<?php echo SERVER_URI . "/produtos/todos/?filtro=ativo&busca=".$_GET['busca']."&status=".$_GET['status']."&produtor=".$_GET['produtor']."&page=" . @($page + 1); ?>">
                                          <i class="fa fa-chevron-right"></i>
                                      </a>
                                  </li>
                              <?php } 
                              ?> 
                          </ul>
                      </nav>
                  </div>
              </div>
          <?php
          }
          ?>
        <?php endif; ?>
    </div>
    <div class="chatbox">
        <div class="chatbox-close"></div>
        <div class="col-xl-12">
            <div class="card">
                <div class="mt-4 center text-center ">
                    <h4 class="card-title">Filtros</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12 mb-2">
                            <form method="GET">
                                <div class="mb-3">
                                    <input type="hidden" class="form-control mb-2" name="filtro" value="ativo">
                                    <div class="form-group">
                                        <p class="mb-1">por Nome do produto</p>
                                        <input type="text" class="form-control mb-2" name="busca" value="<?php echo @addslashes($_GET['busca']); ?>" placeholder="Nome do produto">
                                    </div>
                                    <div class="form-group">
                                        <label class="text-label">por Status</label>
                                        <select class="form-control default-select" id="select-filter-status-id" name="status">
                                            <option selected value="">Todos</option>
                                            <option <?php if(isset($_GET['status']) && $_GET['status'] == '1') echo 'selected' ?> value="1">Pendente</option>
                                            <option <?php if(isset($_GET['status']) && $_GET['status'] == '2') echo 'selected' ?> value="2">Aprovado</option>
                                            <option <?php if(isset($_GET['status']) && $_GET['status'] == '3') echo 'selected' ?> value="3">Reprovado</option>
                                        </select>
                                    </div>
                                    <div class="form-group">  
                                        <label class="text-label">Por Produtor </label>   
                                        <select id="select-ship-name" class="d-block default-select" name="produtor" data-live-search="true" tabindex="-98">
                                            <option value="" selected>Todos</option>     
                                            <?php
                                                $get_users_list = $conn->prepare('SELECT full_name, user_code, us.user__id FROM users us WHERE active = 1 AND (SELECT COUNT(*) AS qtdproducts FROM products pd WHERE pd.user__id = us.user__id) > 0 ORDER BY full_name ASC');
                                                $get_users_list->execute();                                            

                                                while ($row = $get_users_list->fetch()) {
                                                    if (strlen($row['full_name']) > 25) {
                                                        $full_name = substr($row['full_name'], 0, 25) . "...";
                                                    } else {
                                                        $full_name =  $row['full_name'];  
                                                    }                                           
                                                    $user__id = $row['user__id'];
                                                    $user_code = $row['user_code'];
                                                    ?>
                                                        <option <?= !(empty(@$_GET['produtor'])) && $_GET['produtor'] == $row["user__id"] ? "selected" : '' ?> 
                                                        value="<?php echo $user__id;  ?>"><?php echo ucwords($full_name) . " <small>[" . $user_code . "]</small>";  ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-block btn-success text-nowrap mt-2"><i class="fas fa-sliders-h" aria-hidden="true"></i> Aplicar Filtros</button>
                                <a href="<?php echo SERVER_URI; ?>/produtos/todos/" class="btn btn-block mt-2">Limpar Filtros</a>
                            </form>
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