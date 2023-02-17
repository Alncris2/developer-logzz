<?php
require_once (dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (isset($_SESSION['UserID']) && isset($_SESSION['UserEmail'])) {
    header('Location: ' . SERVER_URI . '/pedidos/dashboard/');
} else {
    session_destroy();
}

# Login
if (!(empty($_POST['email']))) {
    $email = addslashes($_POST['email']);

    $search_email = $conn->prepare('SELECT user__id FROM users WHERE email = :email LIMIT 1');
    $search_email->execute(array('email' => $email));
    
    if ($search_email->rowCount() == 1){
        while($row = $search_email->fetch()) {
           //header('Location: ' . SERVER_URI . '/esqueci-minha-senha?sucesso');
           header('Location: ' . SERVER_URI . '/sendmail/reset_password/' . $row['user__id']);
        } 
    } else {
        header('Location: ' . SERVER_URI . '/esqueci-minha-senha?sucesso&email=' . $email);
    }
}

# Reset Password Link
if(isset($_GET['e']) && isset($_GET['m'])){

    $search_email = $conn->prepare('SELECT * FROM password_reset_links WHERE reset_link_user = :reset_link_user AND reset_link_email = :reset_link_email LIMIT 1');
    $search_email->execute(array('reset_link_user' => $_GET['e'], 'reset_link_email' =>$_GET['m']));
    
    if ($search_email->rowCount() == 1){
        while($row = $search_email->fetch()) {
           //header('Location: ' . SERVER_URI . '/esqueci-minha-senha?sucesso');
           //echo "Recuperar senha do usuário " . $_GET['e'] . ".";
           @session_start();
           $_SESSION['UserChangePasswordID'] = $row['reset_link_user_id'];
           $context = "reset_form";
        } 
    } else {
        header('Location: ' . SERVER_URI . '/esqueci-minha-senhaa');
    }

}

$page_title = "Recuperar Senha | Logzz";
$page_description = "Ofereça seus produtos com entrega em 1 dia e pagamento no ato e veja suas vendas explodirem!";
$password_page = true;
require_once (dirname(__FILE__) . '/../../includes/layout/fullwidth/fullwidth-header.php');

?>
<div class="col-md-6">
    <?php
    if (isset($_GET['sucesso'])){
    echo '<div class="alert alert-success alert-dismissible fade show text-center">
    <strong>Confira seu email!</strong><br>Você receberá um link para redefinição da senha.<br><br>Redirecionando para página de login...
    <div id="progress-bar"><div class="bar"></div></div></div>
    <script id="password-reset-redirect">setTimeout(function(){ document.location.assign("' . SERVER_URI .'/login?email='. @$_GET['email'] .'") }, 5500);</script>';
    }?>
  <div class="authincation-content" style="padding-bottom: 30px;">
      <div class="row no-gutters">
          <div class="col-xl-12">
              <?php

                if (@$context == 'reset_form') {

              ?>
              <div class="auth-form">
                  <div class="text-center mb-3">
                    <a href="#"><img src="<?php echo SERVER_URI; ?>/images/logo-full-white.png" alt=""></a>
                  </div>
                  <h4 class="text-center mb-4 text-white">Recupere seu Acesso</h4>
                  <form id="ResetPasswordForm" action="<?php echo SERVER_URI; ?>/esqueci-minha-senha" method="POST">
                      <div class="form-group">
                          <input name="senha" type="password" autofocus class="form-control" placeholder="Digite a Nova Senha" required>
                      </div>
                      <div class="form-group">
                          <input name="confirma-senha" type="password" class="form-control" placeholder="Confirme a Nova Senha" required>
                      </div>
                      <div class="text-center">
                          <button type="submit" class="btn bg-white text-primary btn-block" style="font-size: 0.96em;">Alterar Senha</button>
                      </div>
                  </form>
                  <div class="new-account mt-3 text-center">
                    <small>
                        <p class="text-white float-right">Ainda não é membro? <a class="text-white" href="https://app.logzz.com.br/#ofertas" target="_blank"><b>Abra sua conta!</b></a></p>
                    </small>
                  </div>
              </div>
                <?php

                } else {

                ?>
              <div class="auth-form">
                  <div class="text-center mb-3">
                    <a href="#"><img src="<?php echo SERVER_URI; ?>/images/logo-full-white.png" alt=""></a>
                  </div>
                  <h4 class="text-center mb-4 text-white">Recupere seu Acesso</h4>
                  <form action="<?php echo SERVER_URI; ?>/esqueci-minha-senha" method="POST">
                      <div class="form-group">
                          <input name="email" type="email" autofocus class="form-control" placeholder="Digite seu Email" required>
                      </div>
                      <div class="text-center">
                          <button type="submit" class="btn bg-white text-primary btn-block" style="font-size: 0.96em;">Recuperar Acesso</button>
                      </div>
                  </form>
                  <div class="new-account mt-3 text-center">
                    <small>
                        <p class="text-white float-right">Ainda não é membro? <a class="text-white" href="https://app.logzz.com.br/#ofertas" target="_blank"><b>Abra sua conta!</b></a></p>
                    </small>
                  </div>
              </div>
                <?php

                }

                ?>
          </div>
      </div>
  </div>
</div>

<?php
	require_once (dirname(__FILE__) . '/../../includes/layout/fullwidth/fullwidth-footer.php');
?>

