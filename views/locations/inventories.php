<?php
// error_reporting(-1);              
// ini_set('display_errors', 1);    
require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start(); 

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}

$user_id = $_SESSION['UserID'];  

$page_title = "Meus Estoques | Logzz";
$select_datatable_page = true;

// FALSE PORQUE ESTA COM PROBLEMA 
$inventories_page = false; 

require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

#Lista as Localidades para as Tabs
$get_locale_list = $conn->prepare('SELECT * FROM local_operations WHERE operation_deleted = 0 ');
$get_locale_list->execute();

#Lista de centros de distribuição
$get_locale_center_list = $conn->prepare('SELECT * FROM center_locales');
$get_locale_center_list->execute();


// MODAL DE ACRESENTAR OU REMOVER ESTOQUES
$all_users_query = "SELECT user__id, user_code, full_name FROM users";
$stmt = $conn->prepare($all_users_query);
$stmt->execute();
$code_and_name_users = $stmt->fetchAll();

$all_products_query = "SELECT product_id, product_code, product_name FROM products WHERE `status` = 1 AND product_trash = 0 ";
$stmt = $conn->prepare($all_products_query);
$stmt->execute();
$products = $stmt->fetchAll();


// PEGAR OPERAÇÕES LOCAIS 
$get_locales = $conn->prepare('SELECT operation_name, operation_id FROM local_operations WHERE operation_deleted = 0');
$get_locales->execute();
$locales = $get_locales->fetchAll();


// PEGAR CENTROS DE DISTRIBUIÇÃO 
$get_locales_center = $conn->prepare('SELECT locale_name, locale_id, center_id FROM locales LEFT JOIN center_locales ON center_name = locale_name WHERE type_locale = 1');
$get_locales_center->execute();
$locales_center = $get_locales_center->fetchAll();

// FILTROS

$verify_if_have_any_filter_active = !(empty($_GET['id-cliente'])) || !(empty($_GET['produto']));
// PEGAR TODOS OS USUARIOS
$get_users_list = $conn->prepare('SELECT full_name, user_code, user__id FROM users WHERE active = 1 ORDER BY full_name ASC');
$get_users_list->execute();

// PEGAR TODOS OS PRODUTOS
if ($_SESSION['UserPlan'] == 5) {
    $get_products_list = $conn->prepare('SELECT product_name, product_code, product_id FROM products WHERE status = 1 AND product_trash = 0');
    $get_products_list->execute();
} else {
    $get_products_list = $conn->prepare('SELECT product_name, product_code, product_id FROM products WHERE status = 1 AND product_trash = 0 AND user__id = :user__id');
    $get_products_list->execute(array('user__id' => $user_id));  
} 

?>

<div class="container-fluid">
    <!-- row -->
    <div class="row">
        <div class="col-lg-12">
            <p id="on-filter" class="d-none">Filtros ativos:
                <span id="result"></span>      
            </p>
            <div class="d-flex flex-wrap mb-2 align-items-center justify-content-between">
                <div class="mb-3 mr-3">            
                    <h6 id="volume" class="fs-16 text-black font-w600 mb-0"></h6>   
                </div>
                <div class="event-tabs mb-3 mr-3">
                </div>
            </div>
            <div class="card">
                <div class="card-header pb-0" style="border-bottom: none;">
                    <div class="col-md-12 d-flex align-items-center justify-content-end flex-wrap"> <!-- justify-content-between --> 
                        <div class="row d-none"> 
                            <li class="nav-item">
                                <a class="nav-link disabled">Estoque por</a>
                            </li>
                            <ul class="nav nav-tabs">
                                <li class="nav-item cursor-pointer">
                                    <a class="nav-link active cursor-pointer" href="" id="local-tab" data-toggle="tab" role="tab" aria-controls="local" aria-selected="true">Operação Local</a>
                                </li>
                                <li class="nav-item cursor-pointer">
                                    <a class="nav-link cursor-pointer" href="" id="distribution-tab" data-toggle="tab" role="tab" aria-controls="distribution" aria-selected="false">Centro de Distribuição</a>
                                </li>
                            </ul>
                        </div>
                        <div class="row mt-1">
                                <?php if ($_SESSION['UserPlan'] == 5) : ?>
                                <button type="button" class="btn btn-rounded btn-success mb-1" class="btn btn-success text-nowrap" vv data-toggle="modal" data-target="#modalAddOrRemove">
                                    <span class="btn-icon-left text-success">
                                        <i class="fa fa-plus color-success scale2" aria-hidden="true"></i>
                                    </span>
                                    Acresentar ou Remover Estoques</button>
                                    <?php endif; ?>
                                <button type="button" class="btn btn-rounded btn-secondary filter-btn ml-1" class="btn btn-secondary text-nowrap">
                                    <i class="fas fa-sliders-h scale2 mr-2" aria-hidden="true"></i>Filtros
                                </button>
                            </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="local-content-tab" style="display: block">
                        <div class="basic-list-group">
                            <div class="row">
                                <div class="col-lg-4 col-xl-3">
                                    <div class="list-group mb-4 " id="list-tab-location" role="tablist">
                                        <!-- Lista de Localidades-->
                                        <?php if($_SESSION['UserPlan'] < 5): ?>
                                            <?php while ($tab_list_iten = $get_locale_list->fetch()) : ?>
                                                <!-- VERIFICAR  SE ESSA OPERAÇÃO ESTÁ INATIVA E SE ESSE USUÁRIO AINDA TEM ESTOQUES NELA -->
                                                <?php 
                                                    $verify_inactive = $conn->prepare("SELECT operation_active FROM local_operations WHERE operation_id = :operation_id");
                                                    $verify_inactive->execute(['operation_id' => $tab_list_iten['operation_id']]);

                                                    $is_inactive = $verify_inactive->fetch(\PDO::FETCH_ASSOC)['operation_active'];

                                                    if($is_inactive == 0){ // OPERAÇÃO INATIVA ENTÃO VERIFICAR SE EXISTE ESTOQUE DESSE USUÁRIO NELA
                                                        // PEGAR ID DOS PRODUTOS PARA MONTAR META KEY
                                                        $get_products = $conn->prepare('SELECT inventory_product_id FROM inventories WHERE inventory_locale_id = :inventory_locale_id AND inventory_meta LIKE :user__id AND inventory_meta LIKE :operation_id'); 
                                                        $get_products->execute([
                                                            'inventory_locale_id' => $tab_list_iten['operation_id'],
                                                            'user__id' => $_SESSION['UserID']."%",
                                                            'operation_id' => "%".$tab_list_iten['operation_id']
                                                        ]);

                                                        $products_id = $get_products->fetchAll(\PDO::FETCH_ASSOC);

                                                        if($get_products->rowCount() > 0){
                                                            $metas = [];
                                                            foreach($products_id as $product_id){ // GERAR META KEY 
                                                                $meta_key_inventory = $_SESSION['UserID']."-".$product_id['inventory_product_id']."-".$tab_list_iten['operation_id'];
        
                                                                array_push($metas, $meta_key_inventory);
                                                            }
        
                                                            $str = "'" .implode("','", $metas  ) . "'";

                                                            $verify_inventory = $conn->prepare("SELECT SUM(inventory_quantity) AS total FROM inventories WHERE inventory_meta IN (".$str.") AND ship_locale = 0");
                                                            $verify_inventory->execute([]);
        
                                                            $total_rows = $verify_inventory->fetch()['total'];
                                                        }else{
                                                            $total_rows = 0;
                                                        }
                                                    }
                                                ?>
                                                <?php if($is_inactive == 0): ?>
                                                    <?php if($total_rows !== 0): ?>
                                                        <a class="list-group-item list-group-item-action mb-1 
                                                        <?php if ($tab_list_iten['operation_id'] == 1) echo "active"; ?>" ship_locale="0" id="<?php echo $tab_list_iten['operation_id']; ?>" data-toggle="list" href="#list-<?php echo $tab_list_iten['operation_id']; ?>" role="tab" aria-selected="true" style="padding: 10px 24px; border-radius: 10px;">
                                                            <?= $tab_list_iten['operation_name']; ?> (inativo) 
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php else: ?>
                                                        <a class="list-group-item list-group-item-action mb-1 
                                                        <?php if ($tab_list_iten['operation_id'] == 1) echo "active"; ?>" ship_locale="0" id="<?php echo $tab_list_iten['operation_id']; ?>" data-toggle="list" href="#list-<?php echo $tab_list_iten['operation_id']; ?>" role="tab" aria-selected="true" style="padding: 10px 24px; border-radius: 10px;">
                                                            <?= $tab_list_iten['operation_name']; ?>
                                                        </a>
                                                <?php endif; ?>
                                            <?php endwhile; ?>

                                            <?php else: ?>
                                                <?php while ($tab_list_iten = $get_locale_list->fetch()) : ?>
                                                    <!-- VERIFICAR SE A OPERAÇÃO FOI DELETADA (NÃO EXITEM MAIS PRODUTOS DE NENHUM USUÁRIO) -->
                                                    <?php
                                                        $verify_deleted = $conn->prepare("SELECT operation_deleted, operation_active FROM local_operations WHERE operation_id = :operation_id");
                                                        $verify_deleted->execute(['operation_id' => $tab_list_iten['operation_id']]);
    
                                                        $all = $verify_deleted->fetch(\PDO::FETCH_ASSOC);
                                                        $is_deleted = $all['operation_deleted'];
                                                        $is_inactive = $all['operation_active'];

                                                    ?>
                                                    <?php if($is_deleted == 0): ?>
                                                        <a class="list-group-item list-group-item-action mb-1
                                                            <?php if ($tab_list_iten['operation_id'] == 1) echo "active"; ?>" ship_locale="0" id="<?php echo $tab_list_iten['operation_id']; ?>" data-toggle="list" href="#list-<?php echo $tab_list_iten['operation_id']; ?>" role="tab" aria-selected="true" style="padding: 10px 24px; border-radius: 10px;">
                                                            <?= $tab_list_iten['operation_name']; ?> 
                                                            <?php if($is_inactive == 0): ?>
                                                                (inativo)
                                                            <?php endif; ?>
                                                        </a>
                                                    <?php endif; ?>
                                                <?php endwhile; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-lg-8 col-xl-9">
                                    <div class="tab-content" id="nav-tabContent">
                                        <div class="col-xl-12" style="padding-left: 5px;">
                                            <div class="tab-content">
                                                <div id="All" class="tab-pane active fade show">
                                                    <div class="table-responsive" style="overflow-x: visible;">
                                                        <table id="orders-list-products" class="table card-table display dataTablesCard dataTable-inventories" data-page-length='7' data-order='[[0, "desc"]]'>
                                                            <thead>
                                                                <tr>
                                                                    <th class="col-md-4 text-left" style="padding: 10px 10px;padding-left: 15px">Produto</th>
                                                                    <th class="col-md-3 text-left" style="padding: 10px 10px;">Estoque Atual</th>
                                                                    <th class="col-md-3 text-left" style="padding: 10px 10px;">Último Envio</th>
                                                                    <th class="col-md-2 text-left" style="padding: 10px 10px;">Quant. Últ. Envio</th>
                                                                    <th class="col-md-2 text-left" style="padding: 10px 10px;">Valor Total</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody></tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="distribution-content-tab" style="display: none">
                        <div class="row">
                            <div class="col-lg-4 col-xl-3 ">
                                <div class="list-group mb-4 " id="list-tab-distribution" role="tablist">
                                    <!-- Lista de Localidades-->
                                    <?php while ($tab_list_iten = $get_locale_center_list->fetch()) { ?>
                                        <a class="list-group-item list-group-item-action mb-1
                                            <?php if ($tab_list_iten['center_id'] == "1") echo "active"; ?>" id="<?php echo $tab_list_iten['center_id']; ?>" ship_locale="1" data-toggle="list" href="#list-center-<?php echo $tab_list_iten['center_id']; ?>" role="tab" aria-selected="true" style="padding: 10px 24px; border-radius: 10px;">
                                            <?= $tab_list_iten['center_name']; ?>
                                        </a>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="col-lg-8 col-xl-9">
                                <div class="tab-content" id="nav-tabContent">
                                    <h4 class="mb-4">Seus produtos em <?php echo $tab_list_iten['center_id']; ?></h4>
                                    <div class="col-xl-12" style="padding-left: 5px;">
                                        <div class="tab-content">
                                            <div id="All" class="tab-pane active fade show">
                                                <div class="table-responsive" style="overflow-x: visible;">
                                                    <table id="orders-list" class="table card-table display dataTablesCard" data-page-length='7' data-order='[[0, "desc"]]'>
                                                        <thead>
                                                            <tr>
                                                                <th class="col-md-4 text-left" style="padding: 10px 10px;  padding-left: 15px">Produto</th>
                                                                <th class="col-md-3 text-left" style="padding: 10px 10px;">Estoque Atual</th>
                                                                <th class="col-md-3 text-left" style="padding: 10px 10px;">Último Envio</th>
                                                                <th class="col-md-2 text-left" style="padding: 10px 10px;">Quant. Últ. Envio</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php 
if ($_SESSION['UserPlan'] == 5) { ?>
    <!-- Modal -->
    <div class="modal fade" id="modalAddOrRemove" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Acresentar ou Remover Estoques</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="cadOrRemoveLocale" action="">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="select-user">Selecione o Usuário<i class="req-mark">*</i></label>
                            <select name="select-user" id="select-user" class="default-select d-block" data-live-search="true" tabindex="-98">
                                <option value="" selected>Todos os usuários</option>
                                <?php foreach ($code_and_name_users as $user) : ?>
                                    <option value="<?= $user['user__id'] ?>">
                                        <?php if (strlen($user['full_name']) > 20) : ?>
                                            <?= substr($user['full_name'], 0, 20) . "..."; ?>
                                        <?php else : ?>
                                            <?= ucwords($user['full_name']); ?>
                                        <?php endif; ?>
                                        <small>[<?= $user['user_code'] ?>]</small>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" id="select-user-val" name="select-user-val" required="true" />
                        </div>
                        <div class="form-group">
                            <label for="select-user">Selecione o Produto<i class="req-mark">*</i></label>
                            <select id="select-product" class="default-select d-block" data-live-search="true" tabindex="-98">
                                <option disabled selected>Selecione um produto<i class="req-mark">*</i></option>
                                <?php foreach ($products as $product) : ?>
                                    <option value="<?= $product['product_id'] ?>">
                                        <?php if (strlen($product['product_name']) > 29) : ?>
                                            <?=   json_decode(json_encode(ucwords(strtolower(substr($product['product_name'], 0, 29)) . "...")))  ; ?>
                                        <?php else : ?>
                                            <?= json_decode(json_encode(ucwords(strtolower($product['product_name'])))); ?>
                                        <?php endif; ?>
                                        <small>[<?= $product['product_code'] ?>]</small>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" id="select-product-val" name="select-product-val" required="true" />
                        </div>
                        <div class="form-group">
                            <label for="select-qtd">Quantidade<i class="req-mark">*</i></label>
                            <input type="text" id="qtd" placeholder="Informe a quantidade" name="qtd-user" class="form-control" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                            <input type="hidden" name="qtd-user-text" id="qtd-user-text" class="form-control" required="true">
                        </div>

                        <div class="form-group">
                            <label class="text-label">Tipo de localidade<i class="req-mark">*</i></label>
                            <select id="select-operations" name="localidade" class="d-block default-select" data-live-search="true" tabindex="-98">
                                <option disabled selected>Selecione o tipo de localidade</option>
                                <!-- <optgroup label="Centro de Distribuição">
                                    <?php foreach ($locales_center as $locale_center) : ?>
                                        <option value="<?= $locale_center['center_id'] ?>+CD"> <?= $locale_center['locale_name']; ?> </option>
                                    <?php endforeach; ?>
                                </optgroup> --> 
                                <optgroup label="Operação Local">
                                    <?php foreach ($locales as $locale) : ?>
                                        <option value="<?= $locale['operation_id'] ?>+OPL"> <?= $locale['operation_name']; ?> </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                            <input type="hidden" id="text-select-operations" name="select-locale-val" required="true" />
                        </div>

                        <div class="form-group">
                            <label class="text-label">Oque deseja fazer?<i class="req-mark">*</i></label>
                            <div>
                                <input type="radio" id="sum" name="typeOperation" value="SUM" checked>
                                <label for="sum">Acresencentar essa quantidade</label>
                            </div>
                            <div>
                                <input type="radio" id="subtract" name="typeOperation" value="SUB">
                                <label for="subtract">Subtrair essa quantidade</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn light btn-warning" data-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-success">Concluir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php 
} ?>
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
                            <form id="filter-form">
                                <div class="mb-3">
                                    <?php if ($_SESSION['UserPlan'] == 5) : ?>
                                        <div class="form-group">
                                            <label class="text-label">Para os Usuários<i class="req-mark">*</i></label>
                                            <select id="select-ship-name" class="bling-users-list default-select d-block" data-live-search="true" tabindex="-98">
                                                <?php if ($get_users_list->rowCount() != 0) : ?>
                                                    <option value="" selected>Todos os usuários</option>
                                                    <?php while ($row = $get_users_list->fetch()) : ?>
                                                        <?php
                                                        
                                                        if (strlen($row['full_name']) > 30) :
                                                            $full_name = ucwords(substr($row['full_name'], 0, 30)) . "..."; 
                                                        else : 
                                                            $full_name = ucwords($row['full_name']);
                                                        endif; 

                                                        $user__id = $row['user__id'];
                                                        $user_code = $row['user_code'];
                                                        ?>
                                                        <option value="<?php echo $user__id;  ?>"><?php echo ucwords($full_name) . " <small>[" . $user_code . "]</small>";  ?></option>
                                                    <?php endwhile; ?>
                                                <?php endif; ?>
                                            </select>
                                            <input type="hidden" id="text-ship-name" name="id-cliente" value="" required>
                                        </div>
                                    <?php endif; ?>

                                    <div class="form-group">
                                        <!-- SELECT DE TODOS OS PRODUTOS -->
                                        <label class="text-label">Para os produtos<i class="req-mark">*</i></label>

                                        <select id="select-ship-product" class="product-list d-block" data-live-search="true" tabindex="-98">
                                            <?php if ($get_products_list->rowCount() != 0) : ?>
                                                <option value="" selected>Todos os produtos</option>
                                                <?php while ($row = $get_products_list->fetch()) : ?>
                                                    <?php 
                                                    
                                                    if (strlen($row['product_name']) > 30) :
                                                        $full_name = ucwords(substr($row['product_name'], 0, 30)) . "..."; 
                                                    else : 
                                                        $full_name = ucwords($row['product_name']);
                                                    endif; 

                                                    $user__id = $row['product_id'];
                                                    $user_code = $row['product_code'];
                                                    ?>
                                                    <option value="<?php echo $user__id;  ?>"><?php echo ucwords($full_name) . " <small>[" . $user_code . "]</small>";  ?></option>
                                                <?php endwhile; ?>
                                            <?php endif; ?>
                                        </select>
                                        <input type="hidden" id="text-ship-product" name="produto" value="" required>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-block btn-success text-nowrap mt-2"><i class="fas fa-sliders-h" aria-hidden="true"></i> Aplicar Filtros</button>
                                <a href="<?php echo SERVER_URI; ?>/localidades/manusear-estoque/" class="btn btn-block mt-2">Limpar Filtros</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
 
<?php 
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>

<script>
    var dataTable;

    jQuery(document).ready(function() {

        var reserv = $("#select-ship-product").html();
        $("#select-ship-product").selectpicker();

        $("#local-content-tab .list-group-item").each(function(index) {
            if ($(this).attr('class').indexOf('active') != -1) {
                var operation_id = this.getAttribute('id');
                var ship_locale = this.getAttribute('ship_locale');
                initializeDataTablesSettings(operation_id, ship_locale);
            }
        });

        $('#local-tab').click(function() {
            $("#local-content-tab .list-group-item").each(function(index) {
                if ($(this).attr('class').indexOf('active') != -1) {
                    var operation_id = this.getAttribute('id');
                    var ship_locale = this.getAttribute('ship_locale');
                    initializeDataTablesSettings(operation_id, ship_locale);
                }
            });
        });

        $('#distribution-tab').click(function() {
            $("#distribution-content-tab .list-group-item").each(function(index) {
                if ($(this).attr('class').indexOf('active') != -1) {
                    var operation_id = this.getAttribute('id');
                    var ship_locale = this.getAttribute('ship_locale');
                    initializeDataTablesSettings(operation_id, ship_locale);
                }
            });
        });

        $('.list-group-item').click(function() {
            var operation_id = this.getAttribute('id');
            var ship_locale = this.getAttribute('ship_locale');
            initializeDataTablesSettings(operation_id, ship_locale);
        });

        $('.list-group-item').click(function() {
            var operation_id = this.getAttribute('id');
            var ship_locale = this.getAttribute('ship_locale');
            initializeDataTablesSettings(operation_id, ship_locale);
        });

        $('.filter-btn').on('click', function() {
            $('.chatbox').addClass('active');
        });

        $('.chatbox-close').on('click', function() {
            $('.chatbox').removeClass('active');
        });

        $("#select-user").change(function() {
            var multipleValues = $("#select-user").val();
            $("#select-user-val").val(multipleValues);

            // POLULAR SELECT "PRODUCTS" DE ACORDO COM OS PRODUTOS DOS USUARIOS SELECIONADO
            let conditionWhere = `AND user__id = ${multipleValues}`;
            const query = conditionWhere;

            var productSelect = $('#sselect-product');
            $('#select-product').val(null).trigger('change');

            //AJAX PARA PEGAR OPTIONS DE ACORDO COM USUARIOS
            const URL = "/api/v1/getProductsForSpecificUsers";
            const formData = new FormData();
            formData.append('query', query);

            if (multipleValues == '') {
                $("#select-product").selectpicker('destroy');
                $('#select-product').empty();
                $("#select-product").html(reserv);
                $("#select-product").selectpicker();
            } else {
                $.ajax({
                    url: URL,
                    type: "POST",
                    data: formData,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    error: function(pam, pam2, pam3) {
                        console.log(pam, pam2, pam3);
                    },
                    success: function(data) {
                        $("#select-product").selectpicker('destroy');
                        $("#select-product option").remove();
                        if (data.length != 0) {
                            $.each(data, function(index, value) {
                                var newOption = new Option(value.product_name, value.product_id, false, false);
                                $('#select-product').append(newOption).trigger('change');
                            });
                        }
                        $("#select-product").selectpicker();
                    }
                });
            }
        });

        $("#select-ship-name").change(function() {
            var multipleValues = $("#select-ship-name").val();
            $("#text-ship-name").val(multipleValues);

            // POLULAR SELECT "PRODUCTS" DE ACORDO COM OS PRODUTOS DOS USUARIOS SELECIONADO
            let conditionWhere = `AND user__id = ${multipleValues}`;
            const query = conditionWhere;

            var productSelect = $('#select-ship-product');
            $('#select-ship-product').val(null).trigger('change');

            //AJAX PARA PEGAR OPTIONS DE ACORDO COM USUARIOS
            const URL = "/api/v1/getProductsForSpecificUsers";
            const formData = new FormData();
            formData.append('query', query);

            if (multipleValues == '') {
                $("#select-ship-product").selectpicker('destroy');
                $('#select-ship-product').empty();
                $("#select-ship-product").html(reserv);
                $("#select-ship-product").selectpicker();
            } else {
                $.ajax({
                    url: URL,
                    type: "POST",
                    data: formData,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    error: function(pam, pam2, pam3) {
                        console.log(pam, pam2, pam3);
                    },
                    success: function(data) {
                        $("#select-ship-product").selectpicker('destroy');
                        $("#select-ship-product option").remove();
                        if (data.length != 0) {
                            var newOption = new Option("Todos os produtos", "", true, true);
                            $('#select-ship-product').append(newOption).trigger('change');
                            $.each(data, function(index, value) {
                                var newOption = new Option(value.product_name, value.product_id, false, false);
                                $('#select-ship-product').append(newOption).trigger('change');
                            });
                        }
                        $("#select-ship-product").selectpicker();
                    }
                });
            }
        });

        $("#select-product").change(function() {
            var multipleValues = $("#select-product").val();
            $("#select-product-val").val(multipleValues);
        });

        $("#qtd").change(function() {
            var multipleValues = $("#qtd").val();
            $("#qtd-user-text").val(multipleValues);
        });

        //Mostrar tab local
        $('#local-tab').click(function() {
            $('#local-content-tab').show();
            $('#distribution-content-tab').hide();
        });

        //Mostrar tab Distribuição
        $('#distribution-tab').click(function() {
            $('#local-content-tab').hide();
            $('#distribution-content-tab').show();
        });
    }); 

    function initializeDataTablesSettings(operation_id, ship_locale) {

        let nameTable = ship_locale == 0 ? '#orders-list-products' : '#orders-list';

        if ($.fn.dataTable.isDataTable('#orders-list-products')) {
            $(nameTable).DataTable().clear();
            $(nameTable).DataTable().destroy();
            $(nameTable).empty();
            // re Add CSS to table
            $(nameTable).addClass("usa-table views-table views-view-table cols-8 sticky-enabled sticky-table");
            $(nameTable).css("width", "100%");
        }

        dataTable = $(nameTable).DataTable({
            searching: false,
            processing: true,
            retrieve: true,
            select: true,
            lengthChange: false,
            'serverSide': true,
            "processing": true,
            'serverMethod': 'post',
            "ajax": {
                url: u + '/ajax/list-datatable-ajax/products-orders-ajax.php',
                type: "POST",
                dataType: "JSON",
                data: {
                    filter_data: function() { return $('#filter-form').serialize(); },
                    operation_id: operation_id,
                    ship_locale: ship_locale,
                },
                complete: function(data) {
                    total = data['responseJSON']; 
                    if (total.filter) {  
                        $("#volume").html('Volume total com filtro: ' + total.t_volume + ' <br> Valor total com o filtro: ' + total.t_price);
                        $("#on-filter").removeClass('d-none');
                        $("#on-filter").addClass('d-block');
                        $("#result").html(total.filterText);
                    } else {
                        if (total.recordsFiltered == 0) {
                            $("#no-filter").removeClass('d-none');
                            $("#no-filter").addClass('d-block'); 
                        } else {
                            $("#no-filter").removeClass('d-block');
                            $("#no-filter").addClass('d-none');
                        }                                      
                        $("#result").html(total.filterText);
                        $("#volume").html('Volume total: ' + total.t_volume + '<br> Valor total: ' + total.t_price);
                    } 
                    display_loader(false); 
                },
            },
            drawCallback: function() {
                var page_min = 7;
                var $api = this.api();
                var pages = $api.page.info().pages;
                var rows = $api.data().length;

                // Tailor the settings based on the row count
                if (pages === 1) { 
                    // With this current length setting, not more than 1 page, hide pagination
                    $('.dataTables_paginate').css("display", "none");
                } else {
                    // SHow everything
                    $('.dataTables_paginate').css("display", "block");
                }
            },
            paging: true,
            "columns": [{
                    data: "product",
                    title: "Produto",
                    class: "col-md-4 text-left",
                }, //Pedido
                {
                    data: "inventory",
                    title: "Estoque Atual",
                    class: "col-md-3 text-left",
                }, //Cliente
                {
                    data: "shipping",
                    title: "Último Envio",
                    class: "col-md-3 text-left",
                }, //Produto
                {
                    data: "quantity",
                    title: "Quant. Últ. Envio",
                    class: "col-md-2 text-left",
                }, 
                {
                    data: "price",
                    title: "Valor Total",
                    class: "col-md-2 text-left",
                }, //Entreg
            ],
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            "language": {
                "processing": "Atualizando listagem...",
                "zeroRecords": "Sem resultados para sua busca",
                "info": "Página _PAGE_ de _PAGES_",
                "infoFiltered": "(filtrando de _MAX_ usúarios, no total.)",
                "paginate": {
                    "next": "Próximo",
                    "previous": "Anterior"
                },
                "infoFiltered": "(filtrando de _MAX_ usúarios, no total.)",
            }
        });

        $(nameTable).addClass("usa-table views-table views-view-table cols-8 sticky-enabled sticky-table");
        $(nameTable).css("width", "100%");
    }

    jQuery(document).ready(function() {
        $('#filter-form').submit(function(e) {
            e.preventDefault();
            dataTable.ajax.reload();
            $(".chatbox").removeClass('active');
        });
    });

    $('#cadOrRemoveLocale').submit(function(e) {

        // Captura os dados do formulário
        var ReleaseLocaleForm = document.getElementById('cadOrRemoveLocale');

        // Instância o FormData passando como parâmetro o formulário
        var formData = new FormData(ReleaseLocaleForm);

        console.log($(this).find("input[required='true']"));

        $(this).find("input[required='true']").each(function(e) {
            console.log($(this));
            if (!$(this).val()) {
                Swal.fire({
                    title: 'Campos inválidos!',
                    text: 'Preencha todos os campos antes de continuar',
                    icon: 'error',
                }).then((value) => {

                });
                return false;
            } else {
                Swal.fire({
                    text: 'Você confirma essa solicitação?',
                    showCancelButton: true,
                    icon: 'warning',
                }).then((value) => {
                    if (value.isConfirmed) {
                        // Envia O FormData através da requisição AJAX
                        $.ajax({
                            url: u + "/ajax/add-locale-qtd-ajax.php",
                            type: "POST",
                            data: formData,
                            dataType: 'json',
                            processData: false,
                            contentType: false,
                            success: function(feedback) {
                                //console.log(feedback);
                                $('#modalAddOrRemove').modal('toggle');
                                Swal.fire({
                                    title: feedback.title,
                                    text: feedback.msg,
                                    icon: feedback.type,
                                }).then((value) => {
                                    if (value.isConfirmed) {
                                        dataTable.ajax.reload();
                                    } else {
                                        $('#modalAddOrRemove').modal('toggle');
                                    }
                                });
                            }
                        })
                    }
                })
            }
        });
        return false;

    });
</script>