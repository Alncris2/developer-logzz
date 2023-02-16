<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID'])) || $_SESSION['UserPlan'] < 5) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}

$page_title = "Usuários | Logzz";
$subscriber_page = true;
$select_datatable_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

$get_all_users = $conn->prepare('SELECT users.user__id, full_name, email, user_code, user_plan, user_plan_tax, subscriptions.user_plan_shipping_tax, active FROM users INNER JOIN subscriptions ON users.user__id = subscriptions.user__id ');
$get_all_users->execute();

$get_all_users = $conn->prepare('SELECT users.user__id, full_name, email, user_code, user_plan, user_plan_tax, subscriptions.user_plan_shipping_tax, active FROM users INNER JOIN subscriptions ON users.user__id = subscriptions.user__id ');
$get_all_users->execute();

?>
<div class="container-fluid">
    <!-- row -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Todos os Usuários</h4>
                    <div class="d-flex mb-3">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-rounded btn-outline-success mr-2" data-toggle="dropdown" aria-expanded="true"><i class="fas fa-download scale2 mr-2"></i> Exportar</button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#" id="export-to-csv"><i class="fas fa-file-csv scale2 mr-2"></i></i> CSV</a>
                                <a class="dropdown-item" href="#" id="export-to-xlsx"><i class="fas fa-file-excel scale2 mr-2"></i> Excel</a>
                                <a class="dropdown-item" href="#" id="export-to-pdf"><i class="fas fa-file-pdf scale2 mr-2"></i> PDF</a>
                            </div>
                        </div>
                        <a href="<?php echo SERVER_URI; ?>/usuarios/novo/" class="btn btn-rounded btn-success mr-2">
                            <span class="btn-icon-left text-success"><i class="fa fa-plus color-success"></i></span>
                            Novo Usuário
                        </a>
                        <button type="button" class="btn btn-rounded btn-success filter-btn" class="btn btn-success text-nowrap"><i class="fas fa-sliders-h scale2 mr-2" aria-hidden="true"></i>Filtros</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive"> 
                        <table id="users-list" class="table card-table display dataTablesCard table-sm" data-page-length='25' data-order='[[0, "asc"]]'>
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Usuário [Cód.]</th>
                                    <th>Email</th>
                                    <th>Telefone</th>
                                    <th>Plano</th>
                                    <th>Taxa</th>
                                    <th>Entrega</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">

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
                                        <label for="">por Nome</label>
                                        <input type="text" class="form-control mb-2" name="usuario" placeholder="Nome do Usuário">
                                    </div>
                                    <div class="form-group">
                                        <label for="">por Email</label>
                                        <input type="text" class="form-control mb-2" name="email" placeholder="Email do Usuário">
                                    </div>

                                    <div class="form-group">
                                        <label for="">por Plano</label> 
                                        <select class="form-control default-select" id="select-plano-assinante">
                                            <option selected disabled>Todos</option>
                                            <option value="5">Hero</option>
                                            <option value="3">Gold</option>
                                            <option value="2">Silver</option>
                                            <option value="1">Bronze</option>
                                            <option value="4">Personalizado</option>
                                            <option value="6">Operador Logístico</option>
                                        </select>
                                        <input type="hidden" id="text-plano-assinante" name="plan" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="">por Documento</label>
                                        <input type="text" class="form-control mb-2 documento" name="documento" placeholder="Documento do Usuário">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-block btn-success text-nowrap mt-2"><i class="fas fa-sliders-h" aria-hidden="true"></i> Aplicar Filtros</button>
                                <a href="<?= SERVER_URI . '/usuarios/' ?>" class="btn btn-block mt-2">Limpar Filtros</a>
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