<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}

$product_page = true;
$select_datatable_page = true;
$page_title = "Cadastrar Produto | Logzz";
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

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

    .btn-delete-image {
        top: -15px;
        right: -15px;
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

</style>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
<div class="container-fluid">
    <!-- row -->
    <div class="row">
        <div class="col-xl-12 col-xxl-12">
            <div class="card">
                <div class="card-body pt-5">
                    <div id="smartwizard" class="form-wizard order-create mt-3">
                        <div class="row">
                            <div class="col-lg-4 mb-2">
                                <div role="tabpanel" class="tab-pane fade show active">
                                    <img class="img-fluid w-100" id="product-image" src="<?php echo SERVER_URI; ?>/images/product/placeholder.jpg">
                                    <video class="w-100" style="display: none;" id="product-video" controls></video>
                                    <div class="form-group mt-4 text-center">
                                        <button type="button" id="btn-product-image" class="btn btn-success">Imagem ou Vídeo Principal</button>
                                        <p class="formato">png/jpeg/mp4/mkv/gif/webp <br/> Tamanho máximo: foto 5mb | video 5mb</p>
                                    </div>
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
                                                <img class="img-fluid p-2 product-images" style="cursor: pointer;" src="<?php echo SERVER_URI; ?>/images/product/placeholder.jpg" alt="">
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

                                <div class="alert alert-danger fade show submit-feedback-negative d-none">
                                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="mr-2">
                                        <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon>
                                        <line x1="15" y1="9" x2="9" y2="15"></line>
                                        <line x1="9" y1="9" x2="15" y2="15"></line>
                                    </svg>
                                    <p class="submit-feedback d-inline"></p>
                                    </button>
                                </div>  

                                <div class="alert alert-success fade show submit-feedback-positive d-none">
                                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="mr-2">
                                        <polyline points="9 11 12 14 22 4"></polyline>
                                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                                    </svg>
                                    <p class="submit-feedback d-inline"></p>
                                    </button>
                                </div>
                                <form id="AddProductForm" action="novo-produto" method="POST">
                                    <input type="hidden" id="ActionInput" name="action" value="new-product">
                                    <input type="file" name="product-image" id="input-file-product-image" style="display: none;" accept=".png, .jpg, .jpeg, .gif, .jfif, .webp, .mp4, .mkv">
                                    <input multiple type="file" name="product-images[]" id="input-file-product-images" style="display: none;" accept=".png, .jpg, .jpeg, .jfif">
                                    <div class="form-group">
                                        <label class="text-label">Nome do Produto<i class="req-mark">*</i></label>
                                        <input type="text" name="nome-produto" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label class="text-label">Descrição<i class="req-mark">*</i></label>
                                        <textarea name="descricao-produto" id="descricao-produto" class="form-control" rows="4"></textarea>
                                    </div>
                                    <div class="form-group">   
                                        <label class="text-label">Preço de Custo R$<i class="req-mark">*</i></label> 
                                        <input type="text" name="preco-produto" onKeyPress="return(MascaraMoeda(this,'.',',',event))" class="form-control" placeholder="R$ 99,90">
                                    </div>
                                    <div class="form-group">
                                        <label class="text-label">Categorias<i class="req-mark">*</i></label>
                                        <select class="categoria-produto-select" name="categoria-produto-select" multiple="multiple">
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
                                        <input type="hidden" id="categoria-produto-select-text" name="categoria-produto-select-text" value="">
                                    </div>
                                    <div class="form-group">
                                        <label class="text-label">Página de Vendas<i class="req-mark">*</i></label>
                                        <input type="url" name="pagina-vendas-produto" class="form-control" placeholder="https://seudominio.com/produto">
                                    </div>
                                    <div class="form-group">
                                        <label class="text-label">Tempo de garantia (dias)<i class="req-mark">*</i></label>
                                        <input type="text" name="garantia-produto" class="form-control" placeholder="00 dias">
                                    </div>

                                    <div class="form-group">
                                        <label class="text-label">Peso do produto (kg)<i class="req-mark">*</i></label>
                                        <input type="text" name="weight-product" class="form-control weight" placeholder="0.000" onkeypress="return event.charCode >= 48 && event.charCode <= 57" required />
                                    </div>

                                    <div class="form-group">
                                        <label class="text-label">Tipo de embalagem
                                            <i class="req-mark">*</i>
                                            <i class="fas fa-info-circle" style="color:#ccc;" data-toggle="tooltip" data-placement="top" title="Comprimento x Largura x Altura"></i>
                                        </label>
                                        <select class="form-control" name="kind-packing">
                                            <option selected disabled required>Selecione uma embalagem</option>
                                            <option value="1">Caixa 16cm x 11cm x 6cm (Pequena)</option>
                                            <option value="2">Caixa 24cm x 15cm x 10cm (Média)</option>
                                            <option value="3">Caixa 50cm x 33cm x 20cm (Grande)</option>
                                        </select>
                                    </div>

                                    <div class="form-group mt-4">
                                        <button type="submit" id="SubmitButton" class="btn btn-success">Cadastrar Produto</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php'); ?>
<!-- <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script> -->
<script src="<?php echo SERVER_URI ?>/js/summernote-pt-BR.min.js?v=12" type="text/javascript"></script>
<script>
    // alert("www")
    $('#descricao-produto').summernote({
       
        lang: 'pt-BR',
    });
    
    $(document).ready(function($) {
        $('#btn-product-image').click(function() {
            document.getElementById('input-file-product-image').click();
        });
    });


    // Carrega a imagem selecionada no elemento <img>
    $("#input-file-product-image").on("change", function() {
        var files = !!this.files ? this.files : [];
        if (!files.length || !window.FileReader) return;

        if (/^image/.test(files[0].type) || /^video/.test(files[0].type)) {
            var reader = new FileReader();
            reader.readAsDataURL(files[0]);

            reader.onload = function() {  
                if (/^image/.test(files[0].type)) {
                    $("#product-image").show();
                    $("#product-image").attr('src', this.result);
                    $("#product-video").hide();
                } else {
                    $("#product-image").hide();
                    $("#product-video").attr('src', this.result);
                    $("#product-video").show();
                }
            }
        }
    });

    $('.btn-remove-image').on('click', function() {
        let imageDefault = u + '/images/product/placeholder.jpg';
        let index = $(this).data('index');
        var files = $('#input-file-product-images')[0].files; 
        var fileBuffer = new DataTransfer();
        
        for (let i = 0; i < files.length; i++) {
            if (index !== i)
            fileBuffer.items.add(files[i]);
        }
        
        $('#input-file-product-images')[0].files = fileBuffer.files;
        
        var product_images = $(".product-images");

        for (let i = 0; i < product_images.length; i++) {
            const element = product_images[i];
            element.src = imageDefault;
        }

        loadImages(fileBuffer.files);

    });

    // Abre o input parar carregar várias imagens
    let oldFiles;
    $(document).ready(function($) {
        $('#btn-product-images').click(function() {
            oldFiles = $('#input-file-product-images')[0].files;
            document.getElementById('input-file-product-images').click();
        });
    });

    // Carrega as imagens selecionadas nos elementos <img class="product-images">
    $("#input-file-product-images").on("change", function() {
        var files = !!this.files ? this.files : [];
        
        let fileBuffer = new DataTransfer();
        
        for (let i = 0; i < oldFiles.length; i++) {
            fileBuffer.items.add(oldFiles[i]);   
        }
        
        for (let i = 0; i < files.length; i++) {
            fileBuffer.items.add(files[i]);
        }
        
        if (fileBuffer.files.length <= 9) {
            $('#input-file-product-images')[0].files = fileBuffer.files;
            loadImages(files);
        } else  {
            $('#input-file-product-images')[0].files = oldFiles;
            Swal.fire({
                title: "Algo está errado",
                text: "O número máximo de imagens é 9",
                icon: 'error',
            });
        }

    });

    function loadImages( files ) {
        let imageDefault = u + '/images/product/placeholder.jpg';
        
        if (!files.length || !window.FileReader) return;

        var product_images = $(".product-images");

        let product_images_filtered = []
        for (let i = 0; i < product_images.length; i++) {
            if (product_images[i].src === imageDefault) {
                product_images_filtered.push(product_images[i]);
            }
        }

        for (let i = 0; i < files.length; i++) {
            const element = product_images_filtered[i];     
        
            if (/^image/.test(files[i].type)) {
                var reader = new FileReader();
                reader.readAsDataURL(files[i]);

                reader.onload = function() {
                    element.src = this.result;
                }
            }
        }
    }
    
</script>



