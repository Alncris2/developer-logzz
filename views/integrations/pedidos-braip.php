<?php

require_once (dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}


$page_title = "Importação de Pedidos Braip | Logzz";
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

$get_product_list = $conn->prepare('SELECT * FROM products WHERE user__id = :user__id');
$get_product_list->execute(array('user__id' => $_SESSION['UserID']));

 // VERIFICA SE TEM UM TOKEN SALVO
 $get_token_braip = $conn->prepare('SELECT * FROM tokens WHERE id_user_token	= :user_id');
 $get_token_braip->execute(array('user_id' => $_SESSION['UserID']));
 
 if($get_token_braip->rowCount() == 1){
     $token = $get_token_braip->fetch()['id_token'];
 }
 
 // VERIFICAR SE TEVE UMA REQUISIÇÃO POST FEITA
 if(isset($_POST['token'])){
     
    //VERIFICAR SE JÁ POSSUI UM TOKEN SALVO
    if($token){
        
        // update 
        $get_token_braip = $conn->prepare('UPDATE tokens SET id_token = :token WHERE id_user_token = :user_id');
        $get_token_braip->execute(array(
            'user_id' => $_SESSION['UserID'],
            'token' => $_POST['token']
        ));
        
        $token = $_POST['token'];
    }else{
        
        // insert
        $get_token_braip = $conn->prepare('INSERT INTO tokens (id_token, id_user_token) VALUES (:token ,:user_id)');
        $get_token_braip->execute(array(
            'user_id' => $_SESSION['UserID'],
            'token' => $_POST['token']
        ));
        
         $token = $_POST['token'];
    }
 }
?>
<div class="container-fluid">
  <!-- row -->
  <div class="row">
    <div class="col-xl-12 col-xxl-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">Importações de pedidos Braip</h4>
        </div>
        <div class="card-body">
        <form id="IntegrationBraip" action="<?= SERVER_URI . "/integracoes/pedidos/braip/"?>" method="POST">
              <div class="row">
                  <div class="col-lg-12 mb-2">
                      <input type="hidden" name="action" value="new-integration-braip"> 
                      <div class="form-group">
                        <label class="text-label">Token da sua conta Braip<i class="req-mark">*</i></label>
                        <input type="text" name="token" class="form-control" value="<?= $token ? $token : ""?>" required>
                      </div>
                      <input type="hidden" id="text-integration-product" name="integration-product-id" value="" required>
                  </div>
                </div>
                <button type="submit" id="SubmitButton" class="btn btn-success mb-3"><i class="fas fa-save btn-loading-icon-change"></i> Salvar token Braip</button>
                <hr class="mb-3 mt-3 postback-url-genereted d-none">
                <label class="text-label postback-url-genereted d-none">URL de Retorno</label>
                <div class="input-group postback-url-genereted d-none">
                  <input type="text" class="form-control" name="url-postback" id="url-postback" disabled>
                  <div class="input-group-append">
                    <a href="#" class="input-group-text" id="url-postback-copy"><i class="fas fa-copy"></i>&nbsp;&nbsp;Copiar URL</a>
                  </div>
                </div>
                <small class="text-center text-muted mt-2 postback-url-genereted d-none" style="display: block; max-width: 600px;float: none;margin: 0 auto;">Copie essa URL. Depois, acesse seu ambiente da Braip com seu usuário e senha, vá em <b>Configurações</b> > <b>Postback</b> > <b>Nova configuração</b>, e crie uma configuração com este link. <a href="<?php echo SERVER_URI; ?>/ajuda/integracoes/braip" target="_blank"> Precisa de Ajuda?</small>
                </div>
        </form> 
        </div>
      </div>
    </div>
    <!-- <div class="col-xl-12 col-xxl-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">Integrações em Andamento</h4>
        </div>
        <div class="card-body">
        </div>
      </div>
    </div> -->
  </div>
</div>
<?php 

    
 
    
?>
<?php
    require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>