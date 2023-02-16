<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID'])) || $_SESSION['UserPlan'] != 5) {
    header('Location: ' . SERVER_URI . '/login');
}

$page_title = "Expedição | Logzz";
$users_integrations = true;  // inclui o arquivo integration_users.js 
$orders_page = $select_datatable_page = true;

require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

$get_product_list = $conn->prepare('SELECT * FROM products WHERE product_trash = 0 AND status = 1 ORDER BY product_name ASC');
$get_product_list->execute();

$get_all_users = $conn->prepare('SELECT users.user__id, full_name, user_code, active FROM users INNER JOIN subscriptions ON users.user__id = subscriptions.user__id WHERE active = 1 ORDER BY full_name ASC');
$get_all_users->execute();

?>
<div class="container-fluid">
    <div class="d-flex flex-wrap mb-2 align-items-center justify-content-between">
        <div class="mb-3 mr-3">   
            <h6 id="result" class="fs-16 text-black font-w600 mb-0"></h6>
        </div>
        <div class="event-tabs mb-3 mr-3">
        </div>
        <div class="d-flex mb-3">
           <button type="button" class="btn btn-rounded btn-success filter-btn" class="btn btn-success text-nowrap"><i class="fas fa-sliders-h scale2 mr-2" aria-hidden="true"></i>Filtros</button>
        </div>
    </div>
    <div class="row ">
        <div class="col-xl-12" style="padding-left: 5px;">
            <div class="tab-content">
                <div id="All" class="tab-pane active fade show">
                    <div class="table-responsive" style="overflow-x: visible;">
                        <table id="orders-list-users" class="table card-table display dataTablesCard" data-page-length='20' data-order='[[0, "desc"]]'>
                            <thead>
                                <tr>
                                    <th class="col-md-1">Data.</th>
                                    <th class="col-md-2">Produto.</th>
                                    <th class="col-md-2">Usuário.</th>
                                    <th class="col-md-1">Plataforma.</th>
                                    <th class="col-md-2">
                                        Estoque CD 1  
                                        <button class="ml-2 badge badge-xs light badge-dark" data-toggle="tooltip" data-placement="top" title="Centro de distribuição - São Paulo">
                                            <i class="fa fa-question"></i>
                                        </button>
                                    </th>
                                    <th class="col-md-1">Status</th>
                                    <th class="col-md-1">Ações</th>
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
                                <div class="form-group">
                                    <p class="mb-1">por Data</p>
                                    <input type="hidden" class="form-control mb-2" name="filtro" value="ativo">
                                    <input name="data-inicio" placeholder="Do dia ..." class="datepicker-default form-control picker__input mb-2" id="datepicker" readonly="" aria-haspopup="true" aria-expanded="false" aria-readonly="false" aria-owns="datepicker_root">
                                    <input name="data-final" placeholder=".. ao dia" class="datepicker-default form-control picker__input" id="datepicker" readonly="" aria-haspopup="true" aria-expanded="false" aria-readonly="false" aria-owns="datepicker_root">
                                    <div class="picker" id="datepicker_root" aria-hidden="true">
                                        <div class="picker__holder" tabindex="-1">
                                            <div class="picker__frame">
                                                <div class="picker__wrap">
                                                    <div class="picker__box">
                                                        <div class="picker__header">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="text-label">por Usuário</label>
                                    <select id="select-ship-name" class="d-block default-select" data-live-search="true">
                                        <option selected value="">Todos os Usúarios</option>
                                        <?php if ($get_all_users->rowCount() != 0): ?>
                                            <?php while ($row = $get_all_users->fetch()): ?>
                                                <?php 
                                                    $full_name = $row['full_name'];
                                                    $user__id = $row['user__id'];
                                                    $user_code = $row['user_code'];
                                                ?>
                                                <option value="<?php echo $user__id;  ?>">
                                                    <?php if (strlen($full_name) > 20) {
                                                        echo substr($full_name, 0, 20) . "...";
                                                    } else {
                                                        echo $full_name;
                                                    } echo " <small>[" . $user_code . "]</small>";  ?>
                                                </option>
                                            <?php endwhile; ?>
                                        <?php endif; ?>
                                    </select>
                                    <input type="hidden" id="text-ship-name" name="id-cliente" value="" required>
                                </div>
                                <div class="form-group">
                                    <label class="text-label">por Produto</label>
                                    <select id="select-ship-product" class="d-block default-select" data-live-search="true">
                                            <option selected value="">Todos os Produto</option>
                                            <?php while ($product = $get_product_list->fetch()): ?>
                                                <option value="<?php echo $product['product_id']; ?>">
                                                    <?php if (strlen($product['product_name']) > 30): ?>
                                                        <?= substr($product['product_name'], 0, 30) . "..."; ?>
                                                    <?php else: ?>
                                                        <?= $product['product_name']; ?>
                                                    <?php endif; ?>
                                                </option>
                                            <?php endwhile; ?>
                                    </select>
                                    <input type="hidden" id="text-ship-product" name="produto" value="" required>
                                </div>
                                <div class="form-group">
                                <label class="text-label">por Status</label>
                                <select class="form-control default-select" id="select-filter-status-id">
                                    <option selected value="">Todos</option>
                                    <option value="1" <?php if($_GET['status'] == 1) echo 'selected' ?>>Pendente</option>
                                    <option value="2" <?php if($_GET['status'] == 2) echo 'selected' ?>>Integrado</option>
                                </select>
                                <input type="hidden" id="text-filter-status-id" name="status" value="" required>
                                </div>
                                <div class="form-group">
                                    <label class="text-label">por Plataforma</label>
                                    <select id="" class="form-control default-select" name="platform">
                                        <option selected value="">Todos</option>
                                        <option value="Braip" <?php if($_GET['platform'] == 'Braip') echo 'selected' ?>>Braip</option>
                                        <option value="Monetizze" <?php if($_GET['platform'] == 'Monetizze') echo 'selected' ?>>Monetizze</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-block btn-success text-nowrap mt-2"><i class="fas fa-sliders-h" aria-hidden="true"></i> Aplicar Filtros</button>
                            <a href="<?php echo SERVER_URI; ?>/integracoes/usuarios-pendentes/" class="btn btn-block mt-2">Limpar Filtros</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php'); ?>
