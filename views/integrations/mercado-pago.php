<?php

require_once (dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}

$page_title = "Integração Mercado Pago | Logzz";
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

?>
<div class="container-fluid">
  <!-- row -->
  <div class="row">
    <div class="col-xl-12 col-xxl-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">Integração com o Mercado Pago</h4>
        </div>
        <div class="card-body">
        <form id="IntegracaoNotazz" action="completar" method="POST">
            <div class="row">
                  <div class="col-lg-12 mb-2">
                      <input type="hidden" name="action" value="new-subscriber"> 
                      <div class="form-group">
                        <label class="text-label">Nome da Integração<i class="req-mark">*</i></label>
                        <input type="text" name="nome" class="form-control" required>
                      </div>
                      <div class="form-group">
                        <label class="text-label">Chave API<i class="req-mark">*</i></label>
                        <input type="text" name="token" class="form-control" required>
                      </div>
                      <div class="form-group">
                        <label class="text-label">Chave de Criptografia<i class="req-mark">*</i></label>
                        <input type="text" name="token" class="form-control" required>
                      </div>
                      <div class="form-group">
                        <label class="text-label">Descrição na Fatura<i class="req-mark">*</i></label>
                        <textarea name="token" class="form-control" ></textarea>
                      </div>
                      <div class="form-group">
                        <label class="text-label">Prazo Pix<i class="req-mark">*</i></label>
                        <input type="text" name="token" class="form-control" required>
                      </div>
                      <div class="form-group">
                        <label class="text-label">Prazo Boleto (dias)<i class="req-mark">*</i></label>
                        <input type="text" name="token" class="form-control" required>
                      </div>
                      <div class="form-group">
                        <label class="text-label">Instruções Boleto<i class="req-mark">*</i></label>
                        <textarea name="token" class="form-control" ></textarea>
                      </div>
                  </div>
                </div>
                <!-- <button type="submit" id="SubmitButton" class="btn btn-success">Criar Integração</button> -->
                <a href="#" class="btn btn-success">Criar Integração</a>
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
    require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>