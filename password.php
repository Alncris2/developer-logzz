<?php
require_once ('includes/config.php');
session_name(SESSION_NAME);
session_start();

if (isset($_SESSION['UserID']) && isset($_SESSION['UserEmail'])) {
    header('Location: ' . SERVER_URI . '/pedidos/dashboard/');
} else {
    session_destroy();
}

// Verifica se houve POST e se o usuário ou a senha é(são) vazio(s)
if (!(empty($_POST['email']))) {
    $email = addslashes($_POST['email']);

    $stmt = $conn->prepare('SELECT * FROM users WHERE email = :email');
    $stmt->execute(array('email' => $email));
    
    if ($stmt->rowCount() == 1){
        while($row = $stmt->fetch()) {
           header('Location: ' . SERVER_URI . '/esqueci-minha-senha?sucesso');
        } 
    } else {
        header('Location: ' . SERVER_URI . '/esqueci-minha-senha?erro');
    }
}

$page_title = "Recuperar Senha | Logzz";
$page_description = "Ofereça seus produtos com entrega em 1 dia e pagamento no ato e veja suas vendas explodirem!";
require_once('includes/layout/fullwidth-header.php');

?>
<div class="col-md-6">
    <?php
    if (isset($_GET['erro']) || isset($_GET['sucesso'])){
    echo '<div class="alert alert-danger alert-dismissible fade show">
    <svg viewBox="0 0 24 24" width="24 " height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
    <!--<strong>Confira seu email!</strong>--> Enviamos uma nova senha para o email vinculado à sua conta. Clique aqui para <a href="login">fazer login</a> na sua conta.
    </div>';
    } ?>
  <div class="authincation-content" style="padding-bottom: 30px;">
      <div class="row no-gutters">
          <div class="col-xl-12">
              <div class="auth-form">
                  <div class="text-center mb-3">
                    <a href="#"><img src="<?php echo SERVER_URI; ?>/images/logo-full.png" alt=""></a>
                  </div>
                  <h4 class="text-center mb-4 text-white">Recupere seu acesso!</h4>
                  <form action="<?php echo SERVER_URI; ?>/esqueci-minha-senha" method="POST">
                      <div class="form-group">
                          <input name="email" type="email" class="form-control" placeholder="Digite seu Email" required>
                      </div>
                      <div class="text-center">
                          <button type="submit" class="btn bg-white text-primary btn-block">Recuperar senha</button>
                      </div>
                  </form>
                  <div class="new-account mt-3 text-center">
                    <small>
                        <p class="text-white float-right">Ainda não é membro? <a class="text-white" href="https://dropexpress.com.br/#ofertas" target="_blank"><b>Abra sua conta!</b></a></p>
                    </small>
                  </div>
              </div>
          </div>
      </div>
  </div>
</div>