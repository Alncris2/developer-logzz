<?php

require_once (dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();


if (!(isset($_SESSION['UserID'])) || !(isset($_SESSION['UserPlan'])) || !(isset($_SESSION['UserEmail']))) {
    
    header('Location: ' . SERVER_URI . '/login');

} else {
    $get_user_infos = $conn->prepare('SELECT * FROM users WHERE user__id = :user__id');
    $get_user_infos->execute(array('user__id' => $_SESSION['UserID']));
    
    if ($get_user_infos->rowCount() != 0){

    while($row = $get_user_infos->fetch()) {
        $user_name              = $row['full_name'];
        $user_email             = $row['email'];
        $user_phone             = $row['user_phone'];
        $user_plan              = $row['user_plan'];
        $company_name			= $row['company_name'];
        $company_doc			= $row['company_doc'];
        $company_bank			= $row['company_bank'];
        $company_agency			= $row['company_agency'];
        $company_account		= $row['company_account'];
        $company_account_type	= $row['company_account_type'];
        $company_type           = $row['company_type'];
        $company_pix_key        = $row['company_pix_key'];
    }

    }
} 

$signup_page = true;
$page_title =  "Complete seu Cadastro";
require_once (dirname(__FILE__) . '/../../includes/layout/fullwidth/fullwidth-header.php');

?>

<div class="col-md-6">
        
  <div class="authincation-content">
        <div class="row no-gutters">
            <div class="col-xl-12">
                <div class="auth-form">
                    <div class="text-center mb-3">
                    <img src="<?php echo SERVER_URI; ?>/images/logo-full-white.png" alt="">
                  </div>
                  <h4 class="text-center mb-4 text-white">Complete seu Cadastro</h4>
                    <form action="<?php SERVER_URI . "/novo-assinante/"; ?>" id="CompleteRegistrationForm" method="POST">
                        <input type="hidden" class="form-control" name="action" placeholder="" value="complete-registration">
                        <div class="form-group">
                            <label class="mb-1 text-white">Nome Completo</label>
                            <input type="text" class="form-control" name="nome-assinante" id="name" placeholder="" value="<?php echo @$user_name; ?>">
                        </div>
                        <div class="form-group">
                            <label class="mb-1 text-white">Email</label>
                            <input type="email" class="form-control" name="email-assinante" id="email" placeholder="" value="<?php echo @$user_email; ?>">
                        </div>
                        <div class="form-group">
                            <label class="mb-1 text-white">WhatsApp</label>
                            <input type="text" class="form-control whats" name="whatsapp-assinante" id="whatsapp" placeholder="(99) 9 9999-9999" value="<?php echo @$user_phone; ?>">
                        </div>
                        <div class="text-center mt-4">
                            <a href="#" class="btn bg-white text-primary btn-block" id="signup-continue" style="font-size: 0.96em;">Continuar</a href="#">
                        </div>

                        <div class="d-none" id="signup-step-2">
                            <input type="checkbox" id="account-type-switch" <?php if ($company_type == "juridica"){ echo "checked"; } ?>>
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
                        <input type="hidden" class="form-control" name="pessoa" id="account-type-text" value="<?php if (empty($company_type) || $company_type == NULL || $company_type == "") { echo "fisica"; } else { echo $company_type; } ?>">
                            
                            <?php if ($company_type == "juridica"){ ?>
                            <div class="form-group" id="razao-social-txt">
                                <label class="mb-1 text-white">Razão Social</label>
                                <input type="text" class="form-control" name="razao-social" id="razao-social" placeholder="" value="<?php echo @$user_name; ?>">
                            </div>
                            <div class="form-group">
                                <label class="mb-1 text-white" id="company-doc-label">CNPJ</label>
                                <input type="text" class="form-control cnpj" name="documento" id="documento" placeholder="" value="<?php echo @$company_doc; ?>">
                            </div>
                            <?php }  else { ?>
                            <div class="form-group d-none" id="razao-social-txt">
                                <label class="mb-1 text-white">Razão Social</label>
                                <input type="text" class="form-control" name="razao-social" id="razao-social" placeholder="" value="<?php echo @$user_name; ?>">
                            </div>
                            <div class="form-group">
                                <label class="mb-1 text-white" id="company-doc-label">CPF</label>
                                <input type="text" class="form-control cpf" name="documento" id="documento" placeholder="" value="<?php echo @$company_doc; ?>">
                            </div>
                            <?php } ?>
                            <div class="text-center mt-4">
                                <button type="submit" class="btn bg-white text-primary btn-block" style="font-size: 0.96em;">Completar Cadastro</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
	require_once (dirname(__FILE__) . '/../../includes/layout/fullwidth/fullwidth-footer.php');
?>
