<?php
error_reporting(-1);            
ini_set('display_errors', 1);
require_once(dirname(__FILE__) . '/../../includes/config.php');
include(dirname(__FILE__) . '/../../includes/classes/StarRating.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}

$page_title = "Loja | Logzz";
$shop_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

# Verfica se os filtros estão ativos
# Verfica se os filtros estão ativos
if (isset($_GET['filtro']) && $_GET['filtro'] == 'ativo') {
    
    # Seleciona todos os produtos
    $filter_result = array();

    $get_all_shop_products = $conn->prepare('SELECT product_id FROM products WHERE (product_membership_available = "sim" AND product_shop_visibility = "sim") AND product_trash = 0 AND status = 1 ORDER BY product_rating DESC');
    $get_all_shop_products->execute();

    while ($product_id = $get_all_shop_products->fetch()) {
        array_push($filter_result, $product_id['product_id']);
    }


    # Filtro por NOME
    if (!(empty($_GET['nome']))) {
        $filter_name_result = array();

        $nome = '%' . addslashes($_GET['nome']) . '%';

        $nome_ids = $conn->prepare('SELECT product_id FROM products WHERE (product_membership_available = "sim" AND product_shop_visibility = "sim") AND (product_trash = 0 AND product_name LIKE :nome AND status = 1)');
        $nome_ids->execute(array('nome' => $nome));

        while ($nome_id = $nome_ids->fetch()) {
            array_push($filter_name_result, $nome_id['product_id']);
        }

        $filter_result = array_intersect($filter_result, $filter_name_result);
    }
  

    # Filtro por CODIGO
    if (!(empty($_GET['codigo']))) {
        $filter_code_result = array();

        $code = addslashes($_GET['codigo']);

        $code_ids = $conn->prepare('SELECT product_id FROM products WHERE (product_membership_available = "sim" AND product_shop_visibility = "sim") AND (product_trash = 0 AND product_code = :code)');
        $code_ids->execute(array('code' => $code));

        while ($code_id = $code_ids->fetch()) {
            array_push($filter_code_result, $code_id['product_id']);
        }

        $filter_result = array_intersect($filter_result, $filter_code_result);
    }

    # Filtro por CATEGORIA
    if (!(empty($_GET['categoria']))) {
        $filter_category_result = array();

        $category = addslashes($_GET['categoria']);

        $category_ids = $conn->prepare('SELECT product_id FROM products WHERE (product_membership_available = "sim" AND product_shop_visibility = "sim") AND (product_trash = 0 AND product_categories = :category) AND status = 1');
        $category_ids->execute(array('category' => $category));

        while ($category_id = $category_ids->fetch()) {            
            array_push($filter_category_result, $category_id['product_id']);
        }

        $filter_result = array_intersect($filter_result, $filter_category_result);
    }

    # Filtro por OPERAÇÃO LOCAL
    if (!(empty($_GET['operacao']))) {   
        $filter_operacao_result = array();

        $operacao = addslashes($_GET['operacao']);

        $operacao_ids = $conn->prepare('SELECT product_id FROM products LEFT JOIN inventories ON inventory_product_id = product_id WHERE inventory_locale_id = :inventory_locale_id AND ship_locale = 0 AND (product_membership_available = "sim" AND product_shop_visibility = "sim") AND (product_trash = 0 AND status = 1 AND inventory_quantity > 0)');
        $operacao_ids->execute(array('inventory_locale_id' => $operacao));

        while ($operacao_id = $operacao_ids->fetch()) {
            array_push($filter_operacao_result, $operacao_id['product_id']); 
        }

        $filter_result = array_intersect($filter_result, $filter_operacao_result);
    }

    # Filtro por PRODUTOR
    if (!(empty($_GET['produtor']))) {   
        $filter_produtor_result = array();

        $produtor = addslashes($_GET['produtor']); 

        $produtor_ids = $conn->prepare('SELECT product_id FROM products WHERE (product_membership_available = "sim" AND product_shop_visibility = "sim") AND (product_trash = 0 AND status = 1 AND user__id = :user__id)');
        $produtor_ids->execute(array('user__id' => $produtor));

        while ($produtor_id = $produtor_ids->fetch()) {
            array_push($filter_produtor_result, $produtor_id['product_id']); 
        }

        $filter_result = array_intersect($filter_result, $filter_produtor_result);
    }
}

?>
<div class="container-fluid">
    <div class="row">
        <?php

        if (isset($_GET['filtro']) && $_GET['filtro'] == 'ativo') {

            # Total de produtos que serão exibidos,
            # calculados considerando os filtros.
            $total = count($filter_result);
        } else {

            # Total de produtos que serão exibidos
            $get_shop_products_total = $conn->prepare('SELECT COUNT(*) FROM products WHERE (product_membership_available = "sim" AND product_shop_visibility = "sim") AND product_trash = 0 AND status = 1');
            $get_shop_products_total->execute();

            $total = $get_shop_products_total->fetch();
            $total = $total[0];
        }

        $per_page = 12;

        $pages = ceil($total / $per_page);

        $page = min($pages, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array(
            'options' => array(
                'default'   => 1,
                'min_range' => 1,
                'max_range' => $total,
            ),
        )));


        $offset = ($page * $per_page) - $per_page;
        if ($offset < 0) {
            $offset = 0;
        }
        $limit = $offset + $per_page;
        if ($limit > $total) {
            $limit = $total;
        }

        if (!(isset($_GET['filtro']) && $_GET['filtro'] == 'ativo')) {
            # Texto de Navegação Padrão
            $breadcumb = "Exibindo produtos <b>" . ($offset + 1) . "</b> a <b>" . $limit . "</b> de <b>" . $total . "</b> no total";
        } else {

            $breadcumb = "Filtros Ativos:&nbsp; ";

            if (!(empty(@$_GET['codigo']))) {
                $breadcumb .= '<span class="badge badge-success light">Código: <b>' . $code . '</b></span>';
            }

            if (!(empty(@$_GET['nome']))) {
                $breadcumb .= '<span class="badge badge-success light">Nome: <b>' . addslashes($_GET['nome']) . '</b></span>';
            }

            if (!(empty(@$_GET['categoria']))) {
                $get_product_category = $conn->prepare('SELECT prod_cat_name FROM products_categories WHERE prod_cat_id = :category LIMIT 1');
                $get_product_category->execute(array('category' => $category));

                $category_string = $get_product_category->fetch();
                $category_string = $category_string[0];

                $breadcumb .= '<span class="badge badge-success light">Categoria: <b>' . $category_string . '</b></span>';
            }

            if (!(empty(@$_GET['operacao']))) {
                $get_product_operacao = $conn->prepare('SELECT operation_name FROM local_operations WHERE operation_id = :operacao LIMIT 1');
                $get_product_operacao->execute(array('operacao' => $operacao));

                $operacao_string = $get_product_operacao->fetch();
                $operacao_string = $operacao_string[0];

                $breadcumb .= '<span class="badge badge-success light">Operação local: <b>' . $operacao_string . '</b></span>';
            }

            if (!(empty(@$_GET['produtor']))) {

                # SELECT full_name, user_code, user__id FROM users WHERE active = 1 ORDER BY full_name ASC
                $get_product_produtor = $conn->prepare('SELECT full_name FROM users WHERE user__id = :user__id LIMIT 1');
                $get_product_produtor->execute(array('user__id' => $produtor));

                $produtor_string = $get_product_produtor->fetch();
                $produtor_string = $produtor_string[0];

                $breadcumb .= '<span class="badge badge-success light">Produtor: <b>' . $produtor_string . '</b></span>';
            }
        }


        

        if (!(isset($_GET['filtro']) && $_GET['filtro'] == 'ativo')) {
            $get_shop_products_list = $conn->prepare('SELECT * FROM products WHERE (product_membership_available = "sim" AND product_shop_visibility = "sim") AND product_trash = 0 AND status = 1 ORDER BY product_rating DESC LIMIT :offset, :limit');
            $get_shop_products_list->bindParam(':limit', $per_page, PDO::PARAM_INT);
            $get_shop_products_list->bindParam(':offset', $offset, PDO::PARAM_INT);
            $get_shop_products_list->execute();
        } else {
            $result = "'" . implode("','", $filter_result) . "'";
            $get_shop_products_list = $conn->prepare("SELECT * FROM products WHERE (product_membership_available = 'sim' AND product_shop_visibility = 'sim') AND product_trash = 0 AND status = 1 AND product_id IN ( $result ) ORDER BY product_rating DESC LIMIT :offset, :limit");
            $get_shop_products_list->bindParam(':limit', $per_page, PDO::PARAM_INT);
            $get_shop_products_list->bindParam(':offset', $offset, PDO::PARAM_INT);
            $get_shop_products_list->execute();
        }

        ?>
        <div class="col-md-12">
            <div class="d-flex flex-wrap mb-2 align-items-center justify-content-between">
                <div class="mb-3 mr-3">
                    <h6 class="fs-14 text-muted mb-0"><?php echo $breadcumb; ?></h6>
                </div>
                <div class="event-tabs mb-3 mr-3">
                </div>
                <div class="d-flex mb-3">
                    <button type="button" class="btn btn-rounded btn-success filter-btn" class="btn btn-success text-nowrap"><i class="fas fa-sliders-h scale2 mr-2" aria-hidden="true"></i>Filtros</button>
                </div>
            </div>
        </div>
        <?php
        if ($get_shop_products_list->rowCount() != 0 && $total >= 1) { 
            while ($row = $get_shop_products_list->fetch()) { 

                $product_id = $row['product_id'];
                $product_name = $row['product_name'];
                $product_price = $row['product_price'];
                $product_image = $row['product_image'];
                $product_rating = $row['product_rating'];
                $product_max_commission = $row['product_max_price'] * ($row['product_commission'] / 100);

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
                                    <span class="text-center text-muted"><small>Comissão de até</small><br></span>
                                    <span class="price">R$ <?php echo number_format($product_max_commission, 2, ',', ''); ?></p></span>
                                    <a href="<?php echo SERVER_URI; ?>/loja/produto/<?php echo $product_id; ?>" type="button" class="btn btn-block btn-rounded btn-outline-success">Mais Detalhes</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            }
        } else if (isset($_GET['filtro']) && $_GET['filtro'] == 'ativo') {
            ?>
            <div class="alert alert-success w-100 solid fade show mb-3">
                <i class="fa fa-info-circle" aria-hidden="true"></i><strong> Nenhum resultado. </strong>Revise seus filtros.
            </div>
        <?php
        } else {
        ?>
            <div class="alert alert-success w-100 solid fade show mb-3">
                <i class="fa fa-info-circle" aria-hidden="true"></i><strong> Estamos preparando os produtos para afiliação.</strong> Em breve eles estarão disponíveis aqui.
            </div>
        <?php
        }
        ?>
    </div>
    <?php
    if ($pages >= 2) {
    ?>
        <div class="row">
            <div class="col-md-12">
                <nav style="display: flex; justify-content: center;">
                    <ul class="pagination pagination-sm pagination-circle">
                        <?php
                        if ($page > 1) {
                        ?>
                            <li class="page-item page-indicator">
                                <a title="Página Anterior" class="page-link" href="<?php echo SERVER_URI . "/loja/?page=" . @($page - 1); ?>">
                                    <i class="fa fa-chevron-left"></i></a>
                            </li>
                        <?php
                        }
                        $p = 1;

                        while ($p <= $pages) {
                        ?>
                            <li class="page-item <?php if ($p == $page) {
                                                        echo 'active';
                                                    } ?>"><a title="Ir para a página <?php echo $p; ?>" class="page-link" href="<?php echo SERVER_URI . "/loja/?page=" . $p; ?>"><?php echo $p; ?></a>
                            </li>
                        <?php
                            $p = $p + 1;
                        }
                        if ($page < $pages) {
                        ?>
                            <li class="page-item page-indicator">
                                <a title="Próxima Página" class="page-link" href="<?php echo SERVER_URI . "/loja/?page=" . @($page + 1); ?>">
                                    <i class="fa fa-chevron-right"></i></a>
                            </li>
                        <?php
                        }
                        ?>
                    </ul>
                </nav>
            </div>
        </div>
    <?php
    }
    ?>
</div>

<div class="chatbox">
    <div class="chatbox-close"></div>
    <div class="col-xl-12">
        <div class="card">
            <div class="mt-4 center text-center ">
                <h4 class="card-title">Filtros</h4>
            </div>
            <div class="card-body">
                <div id="smartwizard" class="form-wizard order-create">
                    <div class="row">
                        <div class="col-lg-12 mb-2">
                            <form id="ShopFilter" action="" method="GET">
                                <input type="hidden" id="ActionInput" name="filtro" value="ativo">
                                <div class="form-group">
                                    <label class="text-label"><small>por Código</small></label>
                                    <input type="text" name="codigo" placeholder="Código do Produto" value="<?php echo @$code; ?>" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label class="text-label"><small>por Nome</small></label>
                                    <input type="text" name="nome" value="<?php echo @addslashes($_GET['nome']); ?>" placeholder="Nome do Produto" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label class="text-label"><small>Categoria</small></label>
                                    <select class="categoria-produto dropdown bootstrap-select d-block default-select dropup" name="categoria" data-live-search="true">
                                        <option value="" selected>Todas</option>
                                        <?php
                                        $get_prod_cats_list = $conn->prepare('SELECT * FROM products_categories ORDER BY prod_cat_id ASC');
                                        $get_prod_cats_list->execute();

                                        if ($get_prod_cats_list->rowCount() != 0) {
                                            while ($row = $get_prod_cats_list->fetch()) {
                                                $prod_cat_name = $row['prod_cat_name'];
                                                $prod_cat_id = $row['prod_cat_id'];
                                        ?>
                                                <option <?php if (!(empty(@$_GET['categoria'])) && $_GET['categoria'] == $prod_cat_id) {
                                                            echo "selected";
                                                        } ?> value="<?php echo $prod_cat_id;  ?>"><?php echo $prod_cat_name;  ?></option>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="text-label"><small>por Disponibilidade de estoque</small></label>
                                    <select id="select-operations" class="d-block default-select" name="operacao" data-live-search="true">
                                        <option id="all-operations-option" value="" selected>Todas</option>
                                        <?php
                                        $get_local_operations = $conn->prepare("SELECT * FROM local_operations WHERE operation_active = 1");
                                        $get_local_operations->execute();

                                        while ($operation = $get_local_operations->fetch()) { ?>
                                            <option <?= !(empty(@$_GET['operacao'])) && $_GET['operacao'] == $operation["operation_id"] ? "selected" : '' ?> 
                                            value="<?= $operation["operation_id"] ?>"><?= $operation["operation_name"] ?></option>
                                        <?php } ?>

                                        ?>
                                    </select>
                                </div>

                                <!-- Filtor por produtor -->
                                <!-- <div class="form-group">
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
                                </div> -->

                                <div class="form-group">
                                    <button type="submit" id="SubmitButton" class="btn btn-block btn-success"><i class="fas fa-sliders-h scale2 mr-2" aria-hidden="true"></i>Filtrar</button>
                                    <a href="<?php echo SERVER_URI; ?>/loja" class="btn btn-block mt-2">Limpar Filtros</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>