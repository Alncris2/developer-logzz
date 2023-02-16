<?php

$comAcentos = array('à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ü', 'ú', 'ÿ', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'O', 'Ù', 'Ü', 'Ú');
$semAcentos = array('a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'y', 'A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U');

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
$get_locale_list = $conn->prepare('SELECT * FROM locales WHERE locale_active = 1 AND type_locale = 0');
$get_locale_list->execute();

#Lista de Localidades para pegar o ID e listar os estoques
$get_locale_list2 = $conn->prepare('SELECT * FROM locales WHERE locale_active = 1 AND type_locale = 0');
$get_locale_list2->execute();

?>
<div class="container-fluid">
    <!-- row -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Todas as operações locais</h4>
                    <a href="<?php echo SERVER_URI; ?>/operacoes/nova/" class="btn btn-rounded btn-success"><span class="btn-icon-left text-success"><i class="fa fa-plus color-success"></i>
                        </span>Nova Operação</a>
                </div>
                <div class="card-body">
                    <div class="basic-list-group">
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="list-group mb-4 " id="list-tab" role="tablist">
                                    <?php
                                    # Lista de Localidades
                                    while ($tab_list_iten = $get_locale_list->fetch()) {
                                    ?>
                                        <a class="list-group-item list-group-item-action mb-1 <?php if ($tab_list_iten['locale_id'] == 1) {
                                            echo "active";
                                        } ?>" id="<?php echo $tab_list_iten['locale_id']; ?>" data-toggle="list" href="#list-<?php echo $tab_list_iten['locale_id']; ?>" role="tab" aria-selected="true" style="padding: 10px 24px; border-radius: 10px;"><?php echo $tab_list_iten['locale_name']; ?>
                                        </a>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="col-lg-9">
                                <div class="tab-content" id="nav-tabContent">
                                    <?php
                                    while ($tab_list_iten = $get_locale_list2->fetch()) {
                                        $inventory_locale_id = $tab_list_iten['locale_id'];
                                    ?>
                                        <div class="tab-pane fade <?php if ($tab_list_iten['locale_id'] == 1) {
                                                                        echo "active show";
                                                                    } ?>" id="list-<?php echo $tab_list_iten['locale_id']; ?>">
                                            <h4 class="mb-4">Lista de Alcance de <?php echo $tab_list_iten['locale_name']; ?></h4>
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
                                                            $get_range_list = $conn->prepare('SELECT meta_value FROM locales_meta WHERE locale_id = :locale_id AND meta_key = "locale_range" ORDER BY meta_value');
                                                            $get_range_list->execute(array('locale_id' => $inventory_locale_id));
                                                            $get_range_list->fetch();
                                                            
                                                            foreach($get_range_list->fetchAll(\PDO::FETCH_ASSOC) as $range){
                                                                echo '<label class="text-center text-muted">' . $range['meta_value'] . '</label><br>';
                                                            }
                                                           
                                                            $clean_address = str_replace('<br>', '&#013;', $tab_list_iten['locale_address']);
                                                            
                                                        ?>
                                                        </td>
                                                        <td style="vertical-align: top;color: #5e5e5e;" class="fs-16"><small class="text-muted">Endereço de Envio: <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="Endereço do destinatário dessa respectiva localidade, para envio do seu estoque."></i></small> <br> <?php echo utf8_encode($tab_list_iten['locale_address']); ?>
                                                            <br><a href="#" class="btn btn-light btn-sm btn-copy-address text-muted mt-2" data-text="<?php echo utf8_encode($clean_address); ?>">Copiar Endereço</a>
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
            </div>
        </div>
    </div>
</div>
<?php require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php'); ?>
