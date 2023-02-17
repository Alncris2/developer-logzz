<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}

$page_title = "Listagem de operações locais | Logzz";
$locale_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

#Lista as Localidades para as Tabs
$get_operations_list = $conn->prepare('SELECT * FROM local_operations WHERE operation_active = 1');
$get_operations_list->execute();

#Lista de Localidades para pegar o ID e listar os estoques
$get_operations_details = $conn->prepare('SELECT * FROM local_operations WHERE operation_active = 1');
$get_operations_details->execute();

?>

<div class="container-fluid">
    <!-- row -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Todas as operações locais</h4>
                    <?php if($_SESSION['UserPlan'] == 5) { ?>
                    <a href="<?php echo SERVER_URI; ?>/operacoes/nova/" class="btn btn-rounded btn-success"><span class="btn-icon-left text-success"><i class="fa fa-plus color-success"></i>
                        </span>Nova Operação</a>
                    <?php } ?>
                </div>
                <div class="card-body">
                    <div class="basic-list-group">
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="list-group mb-4 " id="list-tab" role="tablist">
                                    <?php
                                    # Lista de Localidades
                                    while ($tab_list_item = $get_operations_list->fetch()) {
                                    ?>
                                        <a class="list-group-item list-group-item-action mb-1 
                                        <?php if ($tab_list_item['operation_id'] == 1) {
                                            echo "active";
                                        } ?>" id="<?php echo $tab_list_item['operation_id']; ?>" data-toggle="list" href="#list-<?php echo $tab_list_item['operation_id']; ?>" role="tab" aria-selected="true" style="padding: 10px 24px; border-radius: 10px;"><?php echo $tab_list_item['operation_name']; ?>
                                        </a>
                                    <?php
                                }
                                    ?>
                                </div>
                            </div>
                            <div class="col-lg-9">
                                <div class="tab-content" id="nav-tabContent">
                                    <?php
                                    while ($tab_list_item = $get_operations_details->fetch()) {
                                    ?>
                                        <div class="tab-pane fade <?php if ($tab_list_item['operation_id'] == 1) {
                                                                        echo "active show";
                                                                    } ?>" id="list-<?php echo $tab_list_item['operation_id']; ?>">
                                            <h4 class="mb-4">Lista de Alcance de <?php echo $tab_list_item['operation_name']; ?></h4>

                                            <?php if($_SESSION['UserPlan'] == 5) { ?>
                                            <a style="margin: 0 !important;" href="<?php echo SERVER_URI; ?>/operacoes/<?php echo $tab_list_item['operation_id']; ?>/" title="Alterar Dados" class="btn btn-primary btn-xs mr-1 ml-3"><i class="fas fa-pencil-alt mr-2"></i>Editar</a>
                                            <?php } ?>

                                            <table class="table table-responsive">
                                                <thead>
                                                    <tr>
                                                        <th class="col-md-5"></th>
                                                        <th class="col-md-7"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td style="vertical-align: top;">
                                                            <?php
                                                            # Obtém a Lista de Alcance
                                                            $get_range_list = $conn->prepare('SELECT city FROM operations_locales l INNER JOIN local_operations o ON l.operation_id=o.operation_id WHERE l.operation_id = :operation_id');
                                                            $get_range_list->execute(array('operation_id' => $tab_list_item["operation_id"]));

                                                            $range_list = '';
                                                            while ($range_list_item = $get_range_list->fetch()) {
                                                                $range_list .= '<label class="text-center text-muted">' . $range_list_item['city'] . '</label><br>';
                                                            }

                                                            echo $range_list;
                                                            $full_address = explode("<br>", $tab_list_item['storage_address']);
                                                            array_splice( $full_address, 1, 0, $tab_list_item["telefone"] . "<br>" . $tab_list_item["destinatary_doc"] );  
                                                            $address_string = implode("<br>", $full_address);

                                                            $clean_address = str_replace('<br>', '&#013;', $address_string);

                                                            ?>
                                                        </td>
                                                        <td style="vertical-align: top;color: #5e5e5e; line-height: 2em;" class="fs-16"><small class="text-muted">Endereço de Envio: <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="Endereço do destinatário dessa respectiva localidade, para envio do seu estoque."></i></small> <br> <?php echo $address_string; ?>
                                                            <br><a href="#" class="btn btn-light btn-sm btn-copy-address text-muted mt-2" data-text="<?php echo $clean_address; ?>">Copiar Endereço</a>
                                                            <div class="alert alert-secondary alert-dismissible fade show mt-4" style="color: #3a3a3a; background-color: #e2e2e2; border-color: #d7d7d7;">
                                                                <small><i class="fas fa-info-circle"></i>
                                                                    Uma vez que você possua estoque nessa operação local, você poderá oferecer pagamento direto ao entregador para essa listagem de regiões.</small>
                                                            </div>
                                                        </td>
                                                    </tr>
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
                <div class="card-footer">
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php'); ?>
