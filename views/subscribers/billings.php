<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID'])) || $_SESSION['UserPlan'] < 5) {
    header('Location: ' . SERVER_URI . '/login');
}

$user__id = $_SESSION['UserID'];

$stmt = $conn->prepare('SELECT * FROM billings AS b INNER JOIN users AS u ON b.user__id = u.user__id WHERE billing_request IS NOT NULL AND (b.billing_type != "REPASSE" AND billing_type != "COBRANCA") ORDER BY b.billing_request DESC');
$stmt->execute();

$users_search_list = $conn->prepare("SELECT DISTINCT full_name FROM users AS u INNER JOIN billings AS b ON u.user__id = b.user__id WHERE billing_request IS NOT NULL");
$users_search_list->execute();

$billing_history = true;
$select_datatable_page = true;
$page_title = "Movimentações | Logzz";

require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

$filter_result = array();

if (isset($_GET['filtro']) && $_GET['filtro'] == 'ativo') {

    if (!empty($_GET['data-inicio']) || !empty($_GET['data-final'])) {
        # Filtro por DATA
        $filter_data_result = array();

        if (!(empty($_GET['data-inicio']))) {
            $data_inicio_ = pickerDateFormate($_GET['data-inicio']);
            $data_inicio_ = explode(" ", $data_inicio_);
            $data_inicio = $data_inicio_[0] . " 00:00:00";
        } else {
            $data_inicio = '2010-01-01';
        }

        if (!(empty($_GET['data-final']))) {
            $data_final_ = pickerDateFormate($_GET['data-final']);
            $data_final_ = explode(" ", $data_final_);
            $data_final = $data_final_[0] . " 23:59:59";
        } else {
            $data_final = date('Y-m-d') . " 23:59:59";
        }

        $data_ids = $conn->prepare('SELECT billing_id FROM billings WHERE billing_request BETWEEN :data_inicio AND :data_final');
        $data_ids->execute(array('data_inicio' => $data_inicio, 'data_final' => $data_final));

        while ($data_id = $data_ids->fetch()) {
            array_push($filter_data_result, $data_id['billing_id']);
        }

        $filter_result = $filter_data_result;
    }


    //Filtro por NOME DO USUARIO
    if (!(empty($_GET['nome-usuario']))) {
        $filter_name_result = array();

        $nome_usuario = '%' . addslashes($_GET['nome-usuario']) . '%';

        $nome_usuario_ids = $conn->prepare('SELECT billing_id FROM billings AS b INNER JOIN users AS u ON b.user__id = u.user__id WHERE u.full_name LIKE :nome_cliente');
        $nome_usuario_ids->execute(array('nome_cliente' => $nome_usuario));

        while ($nome_usuario_id = $nome_usuario_ids->fetch()) {
            array_push($filter_name_result, $nome_usuario_id['billing_id']);
        }

        if (empty($filter_result)) {
            $filter_result = $filter_name_result;
        } else {
            $filter_result = array_intersect($filter_name_result, $filter_result);
        }
    }

    //Filtro por TIPO
    if (!(empty($_GET['tipo']))) {
        $filter_type_result = array();

        $tipo = $_GET['tipo'];

        switch ($tipo) {
            case "usuario":
                $tipo_billings_ids = $conn->prepare('SELECT billing_id FROM billings AS b INNER JOIN subscriptions AS s ON b.user__id = s.user__id WHERE (s.user_plan = 1 OR s.	user_plan = 2 OR s.	user_plan = 3) AND (b.billing_request IS NOT NULL)');
                break;
            case "administrador":
                $tipo_billings_ids =  $conn->prepare('SELECT billing_id FROM billings AS b INNER JOIN subscriptions AS s ON b.user__id = s.user__id WHERE s.user_plan = 5');
                break;
            case "operador logistico":
                $tipo_billings_ids =  $conn->prepare('SELECT billing_id FROM billings AS b INNER JOIN subscriptions AS s ON b.user__id = s.user__id WHERE s.user_plan = 4');
                break;
        }
        $tipo_billings_ids->execute();

        while ($tipo_billing_id = $tipo_billings_ids->fetch()) {
            array_push($filter_type_result, $tipo_billing_id['billing_id']);
        }

        if (empty($filter_result)) {
            $filter_result = $filter_type_result;
        } else {
            $filter_result = array_intersect($filter_type_result, $filter_result);
        }
    }


    //Filtro por STATUS
    if (!(empty($_GET['status']))) {
        $filter_status_result = array();

        $status = addslashes($_GET['status']);

        if ($status == "liberado") {
            $status_ids = $conn->prepare('SELECT billing_id FROM billings WHERE billings.billing_request IS NOT NULL AND billings.billing_released IS NOT NULL');
        } else if ($status == "pendente") {
            $status_ids = $conn->prepare('SELECT billing_id FROM billings WHERE billings.billing_request IS NOT NULL AND billings.billing_released IS NULL');
        }

        $status_ids->execute();

        while ($status_id = $status_ids->fetch()) {
            array_push($filter_status_result, $status_id['billing_id']);
        }

        if (empty($filter_result)) {
            $filter_result = $filter_status_result;
        } else {
            $filter_result = array_intersect($filter_status_result, $filter_result);
        }
    }

    # Filtro Por Descrição
    if (!empty($_GET['description'])) {

        $filter_description_result = array();

        $description_ids = $conn->prepare('SELECT billing_id FROM billings WHERE billing_type = :billing_type');
        $description_ids->execute(array('billing_type' => $_GET['description']));

        while ($description_id = $description_ids->fetch()) {
            array_push($filter_description_result, $description_id['billing_id']);
        }

        $filter_result = array_intersect($filter_result, $filter_description_result);
    }
}

?>
<div class="container-fluid">
    <div class="d-flex flex-wrap mb-2 align-items-center justify-content-end">

        
    </div>
    <!-- row -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Todas as Solicitações</h4> 
                    <div class="d-flex mb-3">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-rounded btn-outline-success mr-2" data-toggle="dropdown" aria-expanded="true"><i class="fas fa-download scale2 mr-2"></i> Exportar</button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#" id="export-to-csv"><i class="fas fa-file-csv scale2 mr-2"></i></i> CSV</a>
                                <a class="dropdown-item" href="#" id="export-to-xlsx"><i class="fas fa-file-excel scale2 mr-2"></i> Excel</a>
                                <a class="dropdown-item" href="#" id="export-to-pdf"><i class="fas fa-file-pdf scale2 mr-2"></i> PDF</a>
                            </div>
                        </div>
                        <button type="button" class="btn btn-rounded btn-success filter-btn" class="btn btn-success text-nowrap"><i class="fas fa-sliders-h scale2 mr-2" aria-hidden="true"></i>Filtros</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive"> 
                        <table id="assinantes" class="table card-table display billing-dataTables table-sm" data-page-length='25' data-order='[[0, "desc"]]'> 
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Nome</th>
                                    <th>Tipo de perfil</th>
                                    <th>Descrição</th>
                                    <th>Valor</th>
                                    <th>Taxa</th>
                                    <th>Líquido</th>
                                    <th>Status</th>
                                    <th style="text-align: center;">Comprovante</th>
                                </tr>
                            </thead>
                            <tbody id="customers">
                                <?php
                                while ($billing_info = $stmt->fetch()) {
                                    if (isset($_GET['filtro']) && $_GET['filtro'] == 'ativo') {
                                        if (!(in_array($billing_info['billing_id'], $filter_result))) {
                                            continue;
                                        }
                                    }
                                    if ($billing_info['billing_type'] == "REPASSE") {
                                        continue;
                                    }
                                ?> 
                                    <tr>      
                                        <td data-order="<?php echo date(strtotime($billing_info['billing_request']));  ?>"><?php echo date_format(date_create($billing_info['billing_request']), 'd/m/y - H:i'); ?></td>
                                        <td><?php echo $billing_info['full_name']; ?></td>
                                        <td>
                                            <?php
                                            $get_user_plan = $conn->prepare('SELECT user_plan FROM subscriptions WHERE user__id = :user__id');
                                            $get_user_plan->execute(array('user__id' => $billing_info["user__id"]));

                                            $user_plan = $get_user_plan->fetch();
                                            $user_plan = $user_plan['user_plan'];
                                            if ($user_plan == 6) {
                                                echo "Operador";
                                            } else if ($user_plan > 0 && $user_plan < 5) {
                                                echo "Usuário";
                                            } else if ($user_plan == 5) {
                                                echo "Administrador";
                                            }
                                            ?>
                                        <td>
                                            <?php
                                            $billing_type = $billing_info["billing_type"];

                                            echo $billing_type;
                                            ?>
                                        </td>
                                        <td><?php
                                            echo "<span style='color: #ff2929;'>". ($billing_info['billing_value_full'] < 0 ? "" : '+') ." R$ " . number_format($billing_info['billing_value_full'], 2, ',', '.') . "</span>";

                                            ?>
                                        </td>

                                        <?php if ($billing_info['billing_tax'] > 0) : ?>
                                            <td style="text-align: center; color: #2bc155;"><?= "+ R$ " . number_format($billing_info['billing_tax'], 2, ",", "."); ?></td>
                                        <?php else : ?>
                                            <td style="text-align: center; color: #7e7e7e;"><?= "R$ " . number_format($billing_info['billing_tax'], 2, ",", "."); ?></td>
                                        <?php endif ?>

                                        <td><?php
                                            echo "<span style='color: #ff2929;'>- R$ " . number_format($billing_info['billing_value'], 2, ',', '.') . "</span>";

                                            ?>
                                        </td>

                                        <td><?php if ($billing_info['billing_released'] == null) {
                                            ?>
                                                <a href="<?php echo SERVER_URI; ?>/usuarios/saque/pendente/<?php echo $billing_info['billing_id']; ?>" class="" data-id="<?php echo $billing_info['billing_id']; ?>" title="Liberar Saque"><span class="badge badge-sm light badge-yellow"><i class="far fa-clock"></i> PENDENTE</span></a>
                                            <?PHP
                                            } else if ($billing_info['billing_type'] == "REPASSE" && $billing_info['billing_status'] == 1) {
                                            ?>
                                                <a href="<?php echo SERVER_URI; ?>/operadores/repasses" class="" data-id="<?php echo $billing_info['billing_id']; ?>"><span class="badge badge-sm light badge-danger"><i class="far fa-times-circle"></i> REPROVADO</span></a>
                                            <?PHP
                                            } else {
                                            ?>
                                                <a href="<?php echo SERVER_URI; ?>/usuarios/saque/liberado/<?php echo $billing_info['billing_id']; ?>" class="" data-id="<?php echo $billing_info['billing_id']; ?>" title="Ver Detalhes"><span class="badge badge-sm light badge-primary"><i class="far fa-check-circle"></i> LIBERADO</span></a>
                                            <?php
                                            } ?>
                                        </td>
                                        <td style="text-align: center;">
                                        <?php if($billing_info['billing_type'] == "MANUAL") { ?>
                                                <a title="Ver justificativa" class="<?= $billing_info['billing_proof'] == NULL && $billing_info['billing_proof'] == '' ? '' : 'open-justification' ?>"  data-text="<?= $billing_info['billing_proof'] ?>" >
                                                    <i class="fa fa-eye<?= $billing_info['billing_proof'] == NULL && $billing_info['billing_proof'] == '' ? '-slash' : '' ?>"></i>
                                                </a>                                                     
                                        <?php } else { ?>
                                                <a title="Ver comprovante de trasnferência" <?= $billing_info['billing_proof'] == NULL ? "href='#'" :  "href='" . SERVER_URI . "/uploads/saques/comprovantes/" . $billing_info['billing_proof'] . "' target='_blank'" ?> >
                                                    <i class="fa fa-eye<?= $billing_info['billing_proof'] == NULL ? '-slash' : '' ?>"></i>
                                                </a>
                                        <?php } ?>
                                        </td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
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
                            <form method="GET">
                                <div class="mb-3">
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
                                    <div class="form-group">
                                        <label class="text-label"><small>por Nome</small></label>
                                        <select id="select-ship-name" class="d-block default-select" data-live-search="true">
                                            <option selected value="">Todos os Usuários</option>
                                            <?php
                                            while ($user_name = $users_search_list->fetch()) {
                                            ?>
                                                <option value="<?php echo $user_name["full_name"]; ?>"><?php echo $user_name["full_name"]; ?></option>
                                            <?php
                                            }
                                            ?>
                                        </select>
                                        <input type="hidden" id="text-ship-name" name="nome-usuario" value="">
                                    </div>
                                    <div class="form-group">
                                        <label class="text-label">por Tipo</label>
                                        <select id="select-ship-tipo" class="d-block default-select">
                                            <option disabled selected>Nome do Tipo</option>
                                            <option value="usuario">Usuário</option>
                                            <option value="administrador">Administrador</option>
                                            <option value="operador logistico">Operador Logístico</option>
                                        </select>
                                        <input type="hidden" id="text-ship-tipo" name="tipo" value="" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="mb-">por Descrição</label>
                                        <select class="form-control default-select" id="select-filter-description-id" name="description">
                                            <option value="" selected>Todos</option>
                                            <option value="saque" <?php if (!(empty(@$_GET['descricao'])) && $_GET['descricao'] == "saque") echo "selected" ?>>Saque</option>
                                            <option value="antecipacao" <?php if (!(empty(@$_GET['descricao'])) && $_GET['descricao'] == "antecipacao") echo "selected" ?>>Antecipação</option>
                                            <option value="cobranca" <?php if (!(empty(@$_GET['descricao'])) && $_GET['descricao'] == "cobranca") echo "selected" ?>>Cobrança</option>
                                            <option value="assinatura" <?php if (!(empty(@$_GET['descricao'])) && $_GET['descricao'] == "assinatura") echo "selected" ?>>Assinatura</option>
                                            <option value="assinatura" <?php if (!(empty(@$_GET['descricao'])) && $_GET['descricao'] == "manual") echo "selected" ?>>Manual</option>
                                        </select>
                                    </div>

                                    <p class="mb-1">por Status</p>
                                    <select class="form-control default-select" id="select-filter-status-id">
                                        <option selected disabled>Todos</option>
                                        <optgroup label="Saque">
                                            <option value="liberado">Liberado</option>
                                            <option value="pendente">Pendente</option>
                                            <option value="cancelado">Cancelado</option>
                                        </optgroup>
                                        <optgroup label="Cobrança">
                                            <option value="concluido">Concluído</option>
                                            <option value="pendente">Pendente</option>
                                            <option value="atrasado">Atrasado</option>
                                        </optgroup>
                                    </select>
                                    <input type="hidden" id="text-filter-status-id" name="status" value="" required>
                                </div>
                                <button type="submit" class="btn btn-block btn-success text-nowrap mt-2"><i class="fas fa-sliders-h" aria-hidden="true"></i> Aplicar Filtros</button>
                                <a href="<?php echo SERVER_URI; ?>/usuarios/saques" class="btn btn-block mt-2">Limpar Filtros</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="JustificativaModal" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Justificativa de alteração nos valores</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <i class="fa fa-times" aria-hidden="false"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group my-4" id="text-justification">
                        
                    </div>                
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success btn-lg btn-block mt-4" data-dismiss="modal">Entendido</button>
                </div>
            </div>
        </div>
    </div>

</div>
<?php
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>

<script>
    $(document).ready(function() {
        var u = location.protocol + "//" + window.location.hostname;

        $('.open-justification').on('click', function() {
            $('#text-justification').html($(this).data('text'));
            $('#JustificativaModal').modal('toggle'); 
        });
    });
</script>