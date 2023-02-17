<?php

require_once (dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
  header('Location: ' . SERVER_URI . '/login');
  exit;
}

$ship_user_id = $_SESSION['UserID'];

$page_title = "Listagem de Centros de Distribuição | Logzz";
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

#Lista de centros de distribuição
$get_locale_center_list = $conn->prepare('SELECT * FROM center_locales');
$get_locale_center_list->execute();
$tab_list_iten = $get_locale_center_list->fetchAll()

?>

<div class="container-fluid">
    <!-- row -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title"></h4>
                </div>
                <div class="card-body">
                    <div class="basic-list-group">
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="list-group mb-4" id="list-tab" role="tablist">
                                    <?php for($aux = 0; $aux < count($tab_list_iten); $aux++) { ?>
                                    <a class="list-group-item list-group-item-action mb-1 <?php if ($aux == 0) echo "active"; ?>"
                                        id="<?php echo $tab_list_iten[$aux]['center_id']; ?>" data-toggle="list" href="#list-<?php echo $tab_list_iten[$aux]['center_id']; ?>" role="tab" aria-selected="true" style="padding: 10px 24px; border-radius: 10px;">
                                        <?= $tab_list_iten[$aux]['center_name']; ?>
                                    </a>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="col-lg-9">
                                <div class="tab-content" id="nav-tabContent">
                                    <?php for($aux = 0; $aux < count($tab_list_iten); $aux++) { ?>
                                        <div class="tab-pane fade<?php if ($aux == 0) echo " active show"; ?>" id="list-<?php echo $tab_list_iten[$aux]['center_id']; ?>">
                                            <h4 class="mb-4">Centro de distribuição - <?php echo ucwords($tab_list_iten[$aux]['center_name']) ?></h4>
                                            <table class="table table-responsive">
                                                <thead>
                                                    <tr>
                                                        <th class="col-md-5"></th>
                                                        <th class="col-md-7"></th>
                                                    </tr>
                                                </thead>
                                                <tbody> 
                                                    <tr>
                                                        <td style="vertical-align: top;color: #5e5e5e;" class="fs-16"><small class="text-muted">Endereço <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="Endereço do destinatário dessa respectiva localidade, para envio do seu estoque."></i></small><br> 
                                                            <?= $tab_list_iten[$aux]['storage_address_copy']; ?>
                                                            <br><a href="" class="btn btn-light btn-sm btn-copy-address text-muted mt-2" data-text="<?= str_replace("<br>", "", $tab_list_iten[$aux]['storage_address_copy']) ?>">Copiar Endereço</a>
                                                        </td>
                                                        <td style="vertical-align: top;">    
                                                            <div class="alert alert-secondary alert-dismissible fade show mt-4" style="color: #3a3a3a; background-color: #e2e2e2; border-color: #d7d7d7; padding:16px 24px!important;">
                                                                <small><i class="fas fa-info-circle"></i>
                                                                    Uma vez que você possua estoque nesse centro de distribuição, você poderá usá-lo como ponto de expedição via correios e transportadoras, ao integrar com sua plataforma de origem e importar seus pedidos para que o Logzz se responsabilize pela logística.</small>
                                                            </div>
                                                        </td>
                                                        <td style="vertical-align: top;">
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php } ?>                                
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

<?php
    require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>
