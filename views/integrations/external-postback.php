<?php

require_once (dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}

$orders_page = $select_datatable_page = true;
$page_title = "Integrações Postback | Logzz";
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');
$user__id = $_SESSION['UserID'];

// pegar integrações ativas 
$get_user_integrations_list = $conn->prepare('SELECT * FROM integrations WHERE integration_user_id = :user__id');
$get_user_integrations_list->execute(array('user__id' => $user__id));

?>
<div class="container-fluid">
  <div class="alert alert-primary solid fade show mb-3">
      <div class="d-flex align-items-center">
          <i class="flaticon-381-warning-1" aria-hidden="true" style="font-size:30px;"></i>
          <div class="ml-3 d-flex flex-wrap align-items-center justify-content-between w-100">
              <div>
                  <strong>Integre com sua plataforma atual para reduzir custos e automatizar sua logística com a Logzz.</strong>
              </div>
              <div>
                  <button type="button" class="btn btn-light" data-toggle="modal" data-target="#SuporteOnlineModal">
                      Quero entender melhor &nbsp;&nbsp; <i class="fas fa-comment-alt"></i>
                  </button>
              </div>
          </div>
      </div>
  </div>
    
  <!-- row -->
  <div class="row">
  <div class="col-xl-12 col-xxl-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">Integrações Disponíveis</h4>
        </div>
        <div class="card-body">
          <a href="monetizze/" class="intg-btn" id="intg-btn-monetizze" style="background: url('<?php echo SERVER_URI; ?>/images/integrations/logos/monetizze.png');"></a>
          <a href="braip/" class="intg-btn" id="intg-btn-braip" style="background: url('<?php echo SERVER_URI; ?>/images/integrations/logos/braip.png');"></a>
        </div>
      </div>
    </div>
  <?php if ($get_user_integrations_list->rowCount() != 0): ?>
      <div class="col-xl-12 col-xxl-12">
        <div class="card">
            <div class="card-header">
              <h4 class="card-title">Suas Integrações</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive" style="overflow-x: visible;">
                    <table id="orders-list" class="table card-table display dataTablesCard" data-page-length='20' data-order='[[0, "desc"]]'>
                        <thead>
                            <tr>
                                <th class="col-md-2">Nome</th>
                                <th class="col-md-2">Plataforma</th>
                                <th class="col-md-2">Produto</th>
                                <th class="col-md-4">URL de Retorno</th>
                                <th class="col-md-3">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $id = 1; ?>
                        <?php while ($integration = $get_user_integrations_list->fetch()): ?>
                            <tr>
                                <td><?php echo $integration['integration_name']; ?></td>
                                <td><?php echo ucfirst($integration['integration_platform']); ?></td>
                                <td><?php echo ucfirst($integration['integration_product_name']); ?></td>
                                <td>
                                    <small> <?php echo $integration['integration_url']; ?>  </small>    <br>
                                    <input type="text" class="d-none url-return-to-copy"  id="url-return-to-copy-<?php echo $id; ?>" value="<?php echo $integration['integration_url']; ?>">
                                    <small><a href="#" class="link-url-return-to-copy" data-id="<?php echo $id; ?>" id="link-url-return-to-copy"><i class="fas fa-clipboard"></i> Copiar URL</a></small>
                                </td>
                                <td>
                                    <?php if ($integration['status'] == '1'): ?>
                                        <span data-toggle="tooltip" data-placement="top" title="Integração Ativa" class="badge badge-sm light badge-primary"><i class="far fa-check-circle"></i></span>
                                    <?php else: ?>
                                        <span data-toggle="tooltip" data-placement="top" title="Integração Inativa" class="badge badge-sm light badge-warning"><i class="fa fa-ban"></i></span>
                                    <?php endif; ?>
                                    
                                    <a href="<?php echo SERVER_URI . "/integracoes/postback/" . $integration['integration_platform'] . "/?i=" . $integration['integration_id']; ?>&a=u" title="Editar Integração" class=""><i class="fas fa-pencil-alt"></i></a>&nbsp;
                                    <a title="Desativar Integração" class="update-integration-status" data-id="<?php echo $integration['integration_id']; ?>" href="#"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php $id = $id + 1;?>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
             
            </div>
          </div>
        </div>
    <?php endif; ?>
  </div>
</div>

<div class="modal fade" id="SuporteOnlineModal" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="text-center p-3" style="background-color:#0b352b; border-top-right-radius: 1.15rem;  border-top-left-radius: 1.15rem;">
            <div class="profile-photo">
                <img src="<?php echo SERVER_URI . "/uploads/perfis/gerente-de-contas-hugo.png" ?>" width="100" class="img-fluid rounded-circle" alt="">
            </div>
            <h3 class="mt-3 mb-1 text-white">Hugo</h3>
            <p class="text-white m-auto">Gerente de Contas - Logzz</p>
            </div>
            <label class="tex-center m-auto pt-3"><a href="mailto:hugo@Logzz.com.br" target="_blank" class="">hugo@Logzz.com.br</a href=""></label>

            <div class="card-footer border-0 mt-0">					
            <a href="https://api.whatsapp.com/send?phone=556293351785" target="_blank" class="btn btn-primary btn-lg btn-block">
                <i class="fab fa-whatsapp"></i> (62) 9 9335-1785
            </a>
        </div>
    </div>
</div>

<?php
    require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>