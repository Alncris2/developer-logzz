<?php

require_once ('../../includes/config.php');

session_name(SESSION_NAME);
session_start();

# Verifica se o usuário já está logado
if (isset($_SESSION['UserID']) && isset($_SESSION['UserPlan']) && isset($_SESSION['UserEmail'])) {

    # Usuário logado, verifica se o cadastro dele está completo
    header('Location: ' . SERVER_URI . '/usuario/contrato/');
    exit;

}

$params = false;
if(isset($_GET['fcm_token'])){
    $user_token_google = trim($_GET['fcm_token']);
    $params = "?fcm_token=$user_token_google";
             
}

# Verifica se houve requisição POST e se os campos de usuário ou senha foi enviado vazio. jLUnBgFcqp 
if (isset($_POST) AND (!(empty($_POST['email'])) OR !(empty($_POST['password'])))) {
    $email = addslashes($_POST['email']);
    $password = sha1($_POST['password']);

    $check_email = $conn->prepare('SELECT user_password, user__id FROM users WHERE email = :email');
    $check_email->execute(array('email' => $email));
    $details = $check_email->fetch();
    @$user_password = $details['user_password'];
    @$user__id = $details['user__id'];

    if ($user_password != $password){
        header('Location: ' . SERVER_URI . '/login?erro');
        exit;
    }
    
        $get_user_infos = $conn->prepare('SELECT * FROM subscriptions INNER JOIN users ON users.user__id = subscriptions.user__id WHERE users.user__id = :user__id');
        $get_user_infos->execute(array('user__id' => $user__id));

        while($row = $get_user_infos->fetch()) {
            # Verifica se o perfil do usuário está ativo
            if($row['active'] != 1){
                header('Location: ' . SERVER_URI . '/login?accesso-negado');
                exit;
            }
            

            # Cria as variáveis de sessão do usuário
            $_SESSION['UserID'] = $row['user__id'];
            $_SESSION['UserFullName'] = $row['full_name'];
            $_SESSION['UserEmail'] = $row['email'];
            $_SESSION['UserPlan'] = $row['user_plan'];
            $_SESSION['UserPlanTax'] = $row['user_plan_tax'];
            $_SESSION['UserPlanExternalTax'] = $row['user_external_gateway_tax'];
            $_SESSION['UserPlanString'] =  userPlanString($row['user_plan']);
            $_SESSION['UserPlanShipTax'] = $row['user_plan_shipping_tax'];
            $_SESSION['UserPaymentTerm'] = $row['user_payment_term'];
            
            
            if(isset($_GET['fcm_token'])){
                $user_token_google = trim($_GET['fcm_token']);
                $query = $conn->prepare('UPDATE users set user_token_google = :user_token_google where user__id = :user__id');
                $query->execute([
                    'user_token_google' => $user_token_google,
                    'user__id' => $row['user__id']
                ]);
                
            }

            require_once ('../../includes/check-full-registration.php');

            header('Location: ' . SERVER_URI . '/m/dashboard/');
        } 
}

$page_title = "Login | App Logzz";
$page_description = "Ofereça seus produtos com entrega em 1 dia e pagamento no ato e veja suas vendas explodirem!";
require_once('../../includes/layout/fullwidth/fullwidth-header.php');

?>

<style>
    @media only screen and (max-width: 756px) {
        .password {
            text-align: center;  
        }
    }

    @media only screen and (min-width: 757px) {
        .password {
            float: left;  
        } 
    }
</style>

<div class="w-100">
    <?php
    if (isset($_GET['erro'])){
    echo '<div class="alert alert-danger alert-dismissible fade show">
    <svg viewBox="0 0 24 24" width="24 " height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
    <strong>Erro!</strong> Login ou senha incorretos.
    </div>';
    } else if (isset($_GET['accesso-negado'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show">
    <svg viewBox="0 0 24 24" width="24 " height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
    <strong>Erro!</strong> Você não tem permissão para acessar o Dashboard.
    </div>';
    } ?>
  <div class="authincation-content">
      <div class="align-items-center d-flex no-gutters row" style="height: 100vh;">
          <div class="accordion col-md-12">
              <div class="auth-form">
                  <div class="text-center mb-3">
                    <a href="#"><img src="<?php echo SERVER_URI; ?>/images/logo-full-white.png" alt=""></a>
                  </div>
                  <h4 class="text-center mb-4 text-white">Acesse a sua conta!</h4>
                  <form action="<?php echo SERVER_URI; ?><?php if($params){echo $params; } ?>" method="POST">
                      
                      <div class="form-group">
                          <input name="email" type="email" class="form-control" placeholder="Email" required value="<?php echo @$_GET['email']; ?>">
                      </div>
                      <div class="form-group">
                          <input name="password" type="password" class="form-control" placeholder="Senha" required>
                      </div>
                      <div class="text-center">
                          <button type="submit" class="btn bg-white text-primary btn-block" style="font-size: 0.96em;">Fazer Login</button> 
                      </div>
                  </form>
                  <div class="new-account mt-3 text-center">
                    <small>
                        <a class="text-white password" href="<?php echo SERVER_URI; ?>/esqueci-minha-senha">Esqueceu sua senha?</a>
                    </small>  
                  </div>
              </div>
          </div>
      </div>
  </div>
</div>

<?php
	require_once (dirname(__FILE__) . '/footer.php');
?>