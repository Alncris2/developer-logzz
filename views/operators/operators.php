
<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID'])) || $_SESSION['UserPlan'] < 5) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}

$page_title = "Operadores Logísticos | Logzz";
$operator_page = true;
$select_datatable_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

$stmt = $conn->prepare('SELECT user__id, full_name, email, user_code, operation_name, active FROM users AS u INNER JOIN logistic_operator AS l ON l.user_id = u.user__id INNER JOIN local_operations AS op ON op.operation_id = l.local_operation;');
$stmt->execute();

?>
<div class="container-fluid">
    <!-- row -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Todos os Operadores</h4>
                    <a href="<?php echo SERVER_URI; ?>/operadores/novo" class="btn btn-rounded btn-success"><span class="btn-icon-left text-success"><i class="fa fa-plus color-success"></i>
                        </span>Novo Operador</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="operator-list" class="table card-table display dataTablesCard table-sm" data-page-length='25' data-order='[[1, "asc"]]'>
                            <thead>
                                <tr>
                                    <th>Usuário</th>
                                    <th>Email</th>
                                    <th>Operação Local</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $id = 1;
                                while ($row = $stmt->fetch()) {
                                ?>
                                    <tr <?php if ($row['active'] == 0) {
                                            echo 'class="table-active"';
                                        } ?>>
                                        <td style="padding: 10px 10px;" class="col-md-4 "><?php echo $row['full_name'] . ' <small>[<a href="#" data-toggle="tooltip" data-placement="top" title="Clique para copiar" class="copy-user-code" data-code="' . $row['user_code'] . '">' . $row['user_code'] . '</a>]</small>';  ?></td>
                                        <td style="padding: 10px 10px;" class="col-md-4 "><?php echo $row['email']; ?></td>
                                        <td style="padding: 10px 10px;" class="col-md-2 "><?php echo $row['operation_name'];  ?></td>
                                        <td style="padding: 10px 10px;" class="text-center">
                                            <?php if ($row['active'] != 0) { ?>
                                                <a href="<?php echo SERVER_URI; ?>/operador/<?php echo $row['user_code']; ?>/" title="Alterar Dados" class="btn btn-primary btn-xs sharp mr-1"><i class="fas fa-pencil-alt"></i></a>
                                            <?php } else {
                                                echo '<span style="font-size: 0.6em;padding: 1px 5px;" class="badge badge-pill badge-secondary">Inativo</span>';
                                            } ?>
                                        </td>
                                    </tr>
                                <?php
                                    $id = $id + 1;
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
</div>
<?php
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>
