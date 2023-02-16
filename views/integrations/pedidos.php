<?php

require_once (dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}

$page_title = "Importações de pedidos | Logzz";
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

?>
<div class="container-fluid">
  <!-- row -->
  <div class="row">
  <div class="col-xl-12 col-xxl-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">Importações Disponíveis  
            <div class="helper">
                 <div id="helper" data-toggle="modal" data-target="#exampleModalCenter" class="bg-secondary text-light d-flex justify-content-center align-items-center" style="margin-top:-22px; margin-left:247px; width:20px; height:20px;border-radius:100%;font-size:9px;">
                    <i class="fas fa-info"></i>
                </div>
            </div>
          </h4>
        </div>
        <div class="card-body">
          <a href="monetizze/" class="intg-btn" id="intg-btn-monetizze" style="background: url('<?php echo SERVER_URI; ?>/images/integrations/logos/monetizze.png');"></a>
          <a href="<?= SERVER_URI . "/integracoes/pedidos/braip/"?>" class="intg-btn" id="intg-btn-braip" style="background: url('<?php echo SERVER_URI; ?>/images/integrations/logos/braip.png');"></a>
        </div>
      </div>
    </div>
  <?php

        $user__id = $_SESSION['UserID'];
        
        $get_user_integrations_list = $conn->prepare('SELECT * FROM integrations WHERE integration_user_id = :user__id');
        $get_user_integrations_list->execute(array('user__id' => $user__id));

        $get_user_integrations_list->execute();

        if ($get_user_integrations_list->rowCount() != 0){
  ?>
  <div class="col-xl-12 col-xxl-12">
    <div class="card">
        <div class="card-header">
          <h4 class="card-title">Suas Integrações</h4>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table id="external-postback-list" class="table card-table display dataTablesCard table-sm" data-page-length='25' data-order='[[5, "asc"]]'>
              <thead>
                <tr>
                  <!-- <th class="col-md-1">#</th> -->
                  <th class="col-md-2">Nome</th>
                  <th class="col-md-2">Plataforma</th>
                  <th class="col-md-2">Produto</th>
                  <th class="col-md-1">URL de Retorno</th>
                  <th class="col-md-4">
                </tr>
              </thead>
              <tbody>
                <?php
                    $id = 1;
                    while ($integration = $get_user_integrations_list->fetch()) {
                ?>
                <tr>
                  <!-- <td><?php echo $id; ?></td> -->
                  <td><?php echo $integration['integration_name']; ?></td>
                  <td><?php echo ucfirst($integration['integration_platform']); ?></td>
                  <td><?php echo ucfirst($integration['integration_product_name']); ?></td>
                  <td>
                    <small><?php echo $integration['integration_url']; ?></small><br>
                    <input type="text" class="d-none url-return-to-copy"  id="url-return-to-copy-<?php echo $id; ?>" value="<?php echo $integration['integration_url']; ?>">
                    <small><a href="#" class="link-url-return-to-copy" data-id="<?php echo $id; ?>" id="link-url-return-to-copy"><i class="fas fa-clipboard"></i> Copiar URL</a></small>
                  </td>
                  <td><?php 
                    if ($integration['integration_status'] == 'active'){
                      echo '<span data-toggle="tooltip" data-placement="top" title="Integração Ativa" class="badge badge-sm light badge-primary"><i class="far fa-check-circle"></i></span>';
                    } else {
                      echo '<span data-toggle="tooltip" data-placement="top" title="Integração Inativa" class="badge badge-sm light badge-warning"><i class="far fa-ban"></i></span>';
                    }
                    ?>
                  <a href="<?php echo SERVER_URI . "/integracoes/postback/" . $integration['integration_platform'] . "/?i=" . $integration['integration_id']; ?>&a=u" title="Editar Integração" class=""><i class="fas fa-pencil-alt"></i></a>
                    <a title="Desativar Integração" class="update-integration-status" data-id="<?php echo $integration['integration_id']; ?>" href="#"><i class="fa fa-trash"></i></a></th></td>
                </tr>
                <?php
                    $id = $id + 1;
                    }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <?php
        }
    ?>
    <!-- <div class="col-xl-12 col-xxl-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">Integrações em Andamento</h4>
        </div>
        <div class="card-body">
          
        </div>
      </div>
    </div> -->
    <!-- Modal -->
<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document" style="width:700px;">
    <div class="modal-content" style="">
      <div class="modal-body">
        <h6 class="text-justify">Integre com plataformas externas caso queira importar os pedidos e realizar somente a expedição através da plataforma Drop Express.</p>
        <h6 class="text-justify">Basta ter estoque disponível para a que os pedidos sejam expedidos e a integração feita conforme a documentação disponibilizada em cada item da listagem de plataformas disponíveis abaixo.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal">Fechar</button
      </div>
    </div>
  </div>
</div>
  </div>
</div>

<?php
    require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>