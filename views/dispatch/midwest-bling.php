<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID'])) || $_SESSION['UserPlan'] != 5) {
    header('Location: ' . SERVER_URI . '/login');
}


$page_title = "Expedição | Logzz";
$postback_page = true; // Quando TRUE, insere o arquivo js/postbacks.js no rodapé da página.
$dispatch_page = $select_datatable_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

$get_product_list = $conn->prepare('SELECT * FROM products WHERE user__id = :user__id AND product_trash = 0 AND status = 1');
$get_product_list->execute(array('user__id' => $_SESSION['UserID']));

?>
<div class="container-fluid">
    <!-- row -->
    <div class="row">
        <div class="col-xl-12 col-xxl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Bling - Centro Oeste</h4>
                </div>
                <div class="card-body">
                    <form id="IntegrationBling" action="bling-centro-oeste" method="POST">
                        <div class="row">
                            <div class="col-lg-12 mb-2">
                                <input type="hidden" name="action" value="new-integration-bling-oeste">
                                <div class="form-group">
                                    <label class="text-label">Nome da Integração<i class="req-mark">*</i></label>
                                    <input type="text" name="integration-name" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="text-label">Chave de API<i class="req-mark">*</i></label>
                                    <input type="text" name="integration-unique-key" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="text-label">Receber Pedido das UFs<i class="req-mark">*</i></label>
                                    <select class="bling-uf-list" name="bling-uf-list" multiple="multiple">
                                        <?php
                                        $get_region_ufs_list = $conn->prepare('SELECT * FROM ufs ORDER BY uf_string ASC');
                                        $get_region_ufs_list->execute();

                                        if ($get_region_ufs_list->rowCount() != 0) {
                                            while ($row = $get_region_ufs_list->fetch()) {
                                                $uf_string = $row['uf_string'];
                                                $uf = $row['uf'];
                                        ?>
                                                <option value="<?php echo $uf;  ?>"><?php echo utf8_encode($uf_string);  ?></option>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </select>
                                    <input type="hidden" id="bling-uf-list-text" name="bling-uf-list-text" value="">
                                </div>
                                <div class="form-group">
                                    <label class="text-label">Para os Usuários<i class="req-mark">*</i></label>
                                    <select class="bling-users-list" name="bling-users-list" multiple="multiple">
                                        <?php
                                        $get_users_list = $conn->prepare('SELECT full_name, user_code, user__id FROM users ORDER BY full_name ASC');
                                        $get_users_list->execute();

                                        if ($get_users_list->rowCount() != 0) {
                                            while ($row = $get_users_list->fetch()) {
                                                $full_name = $row['full_name'];
                                                $user__id = $row['user__id'];
                                                $user_code = $row['user_code'];
                                        ?>
                                                <option value="<?php echo $user__id;  ?>"><?php echo $full_name . " <small>[" . $user_code . "]</small>";  ?></option>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </select>
                                    <input type="hidden" id="bling-users-list-text" name="bling-users-list-text" value="">
                                </div>
                            </div>
                        </div>
                        <button type="submit" id="SubmitButton" class="btn btn-success mb-3"><i class="fas fa-compress-arrows-alt"></i> Criar Nova Integração</button>
                </div>
                </form>
            </div>
        </div>

        <div class="col-xl-12 col-xxl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Integrações Ativas - Região Centro-Oeste</h4>
                </div>
                <div class="card-body">
                        <div class="row">
                            <div class="col-sm-12 mb-2">
                            <?php
                                
                                $get_dispatche_list = $conn->prepare('SELECT dispatche_name, dispatche_ufs, dispatche_users FROM bling_dispatches WHERE dispatche_region_id = 4 ORDER BY dispatche_id DESC');
                                $get_dispatche_list->execute();

                                if ($get_dispatche_list->rowCount() != 0) {

                            ?>
                            <div class="table-responsive accordion__body--text">
                                <table class="table table-responsive-md" id="dispatches-datatable">
                                            <thead>
                                                <tr>
                                                    <th class="text-center col-md-3">Integração</th>
                                                    <th class="text-center col-md-2">UFs</th>
                                                    <th class="text-center col-md-2">Usuários</th>
                                                    <th class="text-center col-md-2">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                    while ($row = $get_dispatche_list->fetch()) {
                                                ?>
                                                        <tr>
                                                            <td class="text-center"><?php echo $row['dispatche_name']; ?></td>
                                                            <td class="text-center"><?php echo str_replace(",",", ", $row['dispatche_ufs']); ?></td>
                                                            <td class="text-center"><?php echo $row['dispatche_users'] ?></td>
                                                            <td class="text-center"><span class="badge badge-xs badge-success mb-1">Ativa</span></td>
                                                        </tr>
                                                    <?php
                                                    }
                                                    ?>
                                            </tbody>
                                        </table>
                                </div>
                                <?php
                                                    }
                                                    ?>
                            </div>
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