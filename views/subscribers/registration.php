<?php

require_once (dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

$signup_page = true;
$page_title =  "Cria sua Conta";

require_once (dirname(__FILE__) . '/../../includes/layout/fullwidth/fullwidth-header.php');

?>

<div class="col-md-6">
        
  <div class="authincation-content" style="padding-bottom: 30px;">
        <div class="row no-gutters">
            <div class="col-xl-12">
                <div class="auth-form">
                    <div class="text-center mb-3">
                    <img src="<?php echo SERVER_URI; ?>/images/logo-full-white.png" alt="">
                  </div>
                  <h4 class="text-center mb-4 text-white">Crie sua Conta!</h4>
                    <form action="<?php SERVER_URI . "/novo-assinante/"; ?>" id="SignupForm" method="POST">
                        <?php if(isset($_GET['invite'])) { ?>
                            <input type="hidden" class="form-control" name="invite" placeholder="" value="<?php echo @addslashes($_GET['invite']); ?>">
                        <?php } ?>
                        <input type="hidden" class="form-control" name="action" placeholder="" value="new-registration">
                        <div class="form-group">
                            <label class="mb-1 text-white">Nome Completo</label>
                            <input type="text" class="form-control" name="nome-assinante" id="name" placeholder="" value="">
                        </div>
                        <div class="form-group">
                            <label class="mb-1 text-white">Email</label>
                            <input type="email" class="form-control" name="email-assinante" id="email" placeholder="" value="">
                        </div>
                        <div class="form-group">
                            <label class="mb-1 text-white">WhatsApp</label>
                            <input type="text" class="form-control whats" name="whatsapp-assinante" id="whatsapp" placeholder="(99) 9 9999-9999" value="">
                        </div>
                        <div class="text-center mt-4">
                            <a href="#" class="btn bg-white text-primary btn-block" id="signup-continue" style="font-size: 0.96em;">Continuar</a href="#">
                        </div>

                        <div class="d-none" id="signup-step-2">
                            <input type="checkbox" id="account-type-switch" checked>
                            <div class="app">
                            <div class="account-type-body">
                                <div class="account-type-content">
                                    <label class="account-type-switch" for="account-type-switch">
                                    <div class="toggle"></div>
                                    <div class="names">
                                        <p class="light">Pessoa Física</p>
                                        <p class="dark">Pessoa Jurídica</p>
                                    </div>
                                    </label>
                                </div>
                                </div>
                            </div>
                            <input type="hidden" class="form-control" name="pessoa" id="account-type-text" value="">
                            <div class="form-group" id="razao-social-txt">
                                <label class="mb-1 text-white">Razão Social</label>
                                <input type="text" class="form-control" name="razao-social" id="razao-social" placeholder="" value="">
                            </div>
                            <div class="form-group">
                                <label class="mb-1 text-white" id="company-doc-label">CNPJ</label>
                                <input type="text" class="form-control cnpj" name="documento" id="documento" placeholder="" value="">
                            </div>
                            <div class="form-group d-none" id="razao-social-txt">
                                <label class="mb-1 text-white">Razão Social</label>
                                <input type="text" class="form-control" name="razao-social" id="razao-social" placeholder="" value="">
                            </div>
                            <div class="text-center mt-4">
                                <button type="submit" class="btn bg-white text-primary btn-block" style="font-size: 0.96em;">Completar Cadastro</button>
                            </div>
                        </div>
                    </form>
                    <div class="new-account mt-3">
                        
                    </div>
                    <div class="new-account mt-3 text-center">
                    <small>
                        <p class="text-white float-right">Já tem uma conta? <b><a class="text-white" href="<?php echo SERVER_URI; ?>/login">Faça login!</a></b></p>
                    </small>
                  </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
	require_once (dirname(__FILE__) . '/../../includes/layout/fullwidth/fullwidth-footer.php');
?>
