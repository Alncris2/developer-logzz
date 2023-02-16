<?php

require_once (dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}


$page_title = "Integração Monetizze | Logzz";
$postback_page = true; // Quando TRUE, insere o arquivo js/postbacks.js no rodapé da página.
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

$get_product_list = $conn->prepare('SELECT * FROM products WHERE user__id = :user__id AND product_trash = 0 AND status = 1');
$get_product_list->execute(array('user__id' => $_SESSION['UserID']));

if(isset($_GET['e'])){
    $get_integration_list = $conn->prepare('SELECT * FROM integrations WHERE integration_id = :integration_id');
    $get_integration_list->execute(array('integration_id' => $_GET['e']));
    $integration = $get_integration_list->fetch();
}
?>
<div class="container-fluid">
  <!-- row -->
  <div class="row">
    <div class="col-xl-12 col-xxl-12">
      <div class="card">
        <div class="card-header">
            <h4 class="card-title">
                Integração com a Monetizze
                <a href="<?php echo SERVER_URI; ?>/ajuda/integracao-monetizze/" target="_blank">
                    <i class="fas fa-info-circle" style="color:#ccc;"data-toggle="tooltip" data-placement="top" title="Clique aqui para ver um passo a passo da integração"></i>
                </a>
            </h4>
        </div>
        <div class="card-body">
        <?php if(!isset($_GET['e'])): ?>
        <form id="IntegrationMonetizze" action="<?= SERVER_URI . "/integracoes/ajax/postback/monetizze/"?>" method="POST">
              <div class="row">
                  <div class="col-lg-12 mb-2">
                      <input type="hidden" name="action" value="new-integration-monetizze"> 
                      <input type="hidden" name="integration_user_id" value="<?= $_SESSION['UserID']; ?>"> 
                      <div class="form-group">
                        <label class="text-label">Nome da Integração<i class="req-mark">*</i></label>
                        <input type="text" name="integration-name" class="form-control" required>
                      </div>
                      <div class="form-group">
                        <label class="text-label">Chave Única<i class="req-mark">*</i></label>
                        <input type="text" name="integration-unique-key" class="form-control" required data-toggle="tooltip" data-placement="left" title="Cole a Chave Única da sua conta da Monetizze">
                      </div>
                      <div class="form-group">
                        <label class="text-label">Token da sua conta monetizze<i class="req-mark">*</i></label>
                        <input type="text" name="integration-token-key" class="form-control" required data-toggle="tooltip" data-placement="left" title="Cole o X_CONSUMER_KEY da sua conta da Monetizze">
                      </div>
                      <div class="form-group">
                          <label class="text-label">Produto<i class="req-mark">*</i></label>
                          <select id="select-integration-product" name="integration-product-id" class="d-block default-select" data-toggle="tooltip" data-placement="left" title="Cole a Chave Única da sua conta da Monetizze">
                              <option disabled selected>Selecione um Produto para Integrar</option>
                              <?php
                                  while($prodcut = $get_product_list->fetch()) {
                              ?>
                              <option value="<?php echo $prodcut['product_id']; ?>"><?php if (strlen($prodcut['product_name']) > 30) { echo substr($prodcut['product_name'], 0, 30) . "..."; } else {echo $prodcut['product_name']; } ?></option>
                              <?php
                                  }
                              ?>
                          </select>
                      </div>
                  </div>
                </div>
                <button type="submit" id="SubmitButton" class="btn btn-success mb-3"><i class="fas fa-link btn-loading-icon-change"></i> Gerar URL de Postback</button>
                <div id="postback-url-genereted" class="d-none">
                    <hr class="mb-3 mt-3 postback-url-genereted d-flex">
                    <label class="text-label postback-url-genereted d-flex">URL de Retorno</label>
                    <div class="input-group postback-url-genereted d-flex">
                      <input type="text" class="form-control" name="url-postback" id="url-postback" value="<?= $_SESSION['postbackUrl']; ?>" disabled >
                      <div class="input-group-append">
                        <a href="" class="input-group-text" id="url-postback-copy"><i class="fas fa-copy"></i>&nbsp;&nbsp;Copiar URL</a>
                      </div>
                    </div>
                    <small class="mt-1 text-center text-muted mt-2 postback-url-genereted" style="">Copie essa URL. Depois, acesse seu ambiente da monetizze com seu usuário e senha, vá em <b>Configurações</b> > <b>Postback</b> > <b>Nova configuração</b>, e crie uma configuração com este link. <a href="<?php echo SERVER_URI; ?>/ajuda/integracao-monetizze/" target="_blank"> Precisa de Ajuda?</small>
                </div>
                </div>
        </form> 
        <?php else: ?>
            <form id="EditIntegration" action="<?= SERVER_URI . "/integracoes/ajax/postback/braip-edit"?>" method="POST">
              <div class="row">
                  <div class="col-lg-12 mb-2">
                      <input type="hidden" name="integration_id" value="<?= $integration['integration_id']; ?>"> 
                      <div class="form-group">
                        <label class="text-label">Nome da Integração<i class="req-mark">*</i></label>
                        <input type="text" name="integration-name" class="form-control" value="<?= $integration['integration_name'] ?>"required>
                      </div>
                      <div class="form-group">
                        <label class="text-label">Chave Única<i class="req-mark">*</i></label>
                        <input type="text" name="integration-unique-key" class="form-control" value="<?= $integration['integration_keys']; ?>" required data-toggle="tooltip" data-placement="left" title="Cole a Chave Única da sua conta da Braip">
                      </div>
                      <div class="form-group">
                          <label class="text-label">Produto<i class="req-mark">*</i></label>
                          <select disabled id="select-integration-product" name="integration-product-id" class="d-block default-select" data-toggle="tooltip" data-placement="left" title="Selecione um produto para integrar">
                              <option disabled selected>Selecione um Produto para Integrar</option>
                              <?php while($prodcut = $get_product_list->fetch()): ?>
                                <option value="<?php echo $prodcut['product_id']; ?>" <?php if($integration['integration_product_id'] == $prodcut['product_id']) echo 'selected'; ?>>
                                    <?php 
                                        if (strlen($prodcut['product_name']) > 30) {
                                            echo substr($prodcut['product_name'], 0, 30) . "..."; 
                                        } else {
                                            echo $prodcut['product_name']; 
                                        } 
                                    ?>
                                </option>
                              <?php endwhile; ?>
                            
                          </select>
                      </div>
                  </div>
                </div>
                <button type="submit" id="SubmitButtonEdit" class="btn btn-success mb-3"><i class="fas fa-save btn-loading-icon-change"></i> &nbsp;&nbsp; Editar Integração</button>
                
                <hr class="mb-3 mt-3 postback-url-genereted d-flex">
                <label class="text-label postback-url-genereted d-flex">URL de Retorno</label>
                <div class="input-group postback-url-genereted d-flex">
                  <input type="text" class="form-control" name="url-postback" id="url-postback" value="<?= $integration['integration_url'] ?>" disabled >
                  <div class="input-group-append">
                    <a href="" class="input-group-text" id="url-postback-copy"><i class="fas fa-copy"></i>&nbsp;&nbsp;Copiar URL</a>
                  </div>
                </div>
                <small class="mt-1 text-center text-muted mt-2 postback-url-genereted" style="">Copie essa URL. Depois, acesse seu ambiente da Braip com seu usuário e senha, vá em <b>Configurações</b> > <b>Postback</b> > <b>Nova configuração</b>, e crie uma configuração com este link. <a href="<?php echo SERVER_URI; ?>/ajuda/integracao-braip/" target="_blank"> Precisa de Ajuda?</small>
                </div>
            </form>
        <?php endif; ?>
        
        </div>
      </div>
    </div>
  </div>
</div>

<?php
    require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>