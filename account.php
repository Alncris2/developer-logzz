<?php
require_once ('includes/config.php');

session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login?url=' . $_SERVER['REQUEST_URI']);
    exit;
}

$page_title =  "Minha Conta | Logzz";
require_once('includes/layout/default/default-header.php');

$user__id = $_SESSION['UserID'];

$stmt = $conn->prepare('SELECT * FROM users WHERE user__id = :user__id');
$stmt->execute(array('user__id' => $user__id));
    
if ($stmt->rowCount() != 0){
  while($row = $stmt->fetch()) {
    $user_name              = $row['full_name'];
    $user_email             = $row['email'];
    $user_plan              = $row['user_plan'];
    $user_avatar            = $row['user_avatar'];
    $user__id 				= $_SESSION['UserID'];
    $company_name		    = $row['company_name'];
    $company_doc			= $row['company_doc'];
    $company_bank		    = $row['company_bank'];
    $company_agency		    = $row['company_agency'];
    $company_account		= $row['company_account'];
    $company_account_type	= $row['company_account_type'];
    $company_type           = $row['company_type'];
    $company_pix_key        = $row['company_pix_key'];
  } 
} else {
  exit;
}

    # Faturamento
    $faturamento = $conn->prepare('SELECT SUM(order_final_price) FROM orders WHERE order_status < 4 AND user__id = :user__id');
    $faturamento->execute(array('user__id' => $user__id));
    $faturamento = $faturamento->fetch();

    # Entregas
    $entregas = $conn->prepare('SELECT COUNT(*) FROM orders WHERE order_status = 3 AND user__id = :user__id');
    $entregas->execute(array('user__id' => $user__id));
    
    $entregas = $entregas->fetch();

    # Pedidos
    $pedidos = $conn->prepare('SELECT COUNT(*) FROM orders WHERE user__id = :user__id');
    $pedidos->execute(array('user__id' => $user__id));
     
    $pedidos = $pedidos->fetch();
    
    # Produtos
    $produtos = $conn->prepare('SELECT product_id FROM products WHERE product_trash = 0 AND user__id = :user__id');
    $produtos->execute(array('user__id' => $user__id));
    
    $produtos = $produtos->rowCount();

    # Valor a liberar
    $pending_commissions = $conn->prepare('SELECT SUM(order_liquid_value) AS total_value FROM orders WHERE (user__id = :user__id AND order_commission_released = 0) AND order_status = 3');
    $pending_commissions->execute(array('user__id' => $user__id));
    $pending_commissions = $pending_commissions->fetch();

    if ($pending_commissions['0'] == null) {
        $pending_commissions = 0;

    } else {
        $pending_commissions = $pending_commissions[0];
    }

    # Valor Disponível p/ Saque
    $saque = $conn->prepare('SELECT SUM(billing_value) FROM billings WHERE user__id = :user__id AND (billing_request IS NULL AND billing_released IS NULL)');
    $saque->execute(array('user__id' => $user__id));

    $saque = $saque->fetch();
    if ($saque['0'] == null){
      $saque = 0;
      $saque_btn_disable = true;

      $saque_solicitado = $conn->prepare('SELECT * FROM billings WHERE user__id = :user__id AND billing_request = 1');
      $saque_solicitado->execute(array('user__id' => $user__id));

      if ($saque_solicitado->rowCount() != 0){
          $saque_btn_disable = true;
          $saque_btn_text    = "Solicitação em Andamento";
      }
      
    } else {
      $saque = $saque[0];
    }


$stats = array(
  'produtos_cadastrados' => $produtos,
  'pedidos_recebidos' => $pedidos[0],
  'entregas_realizadas' => $entregas[0],
  'valor_total' => "R$ " . number_format($faturamento['0'], 2, ',', '.'),
  'disponivel_saque' => "R$ " .  number_format($saque, 2, ',', '.'),
  'a_liberar' => "R$ " .  number_format($pending_commissions, 2, ',', '.')
);

?>
  

<!-- row -->
<div class="container-fluid">
  <!-- row -->
  <div class="row">
    <div class="col-lg-12">
      <div class="profile card card-body px-3 pt-3 pb-0">
        <div class="profile-head">
          <div class="photo-content">
            <div class="cover-photo" style="background: url(<?php echo SERVER_URI; ?>/uploads/imagens/usuarios/<?php echo strtolower($_SESSION['UserPlanString']); ?>-plan-cover.jpg)"></div>
          </div>
          <div class="profile-info">
            <div class="profile-photo">
              <img src="<?php echo SERVER_URI; ?>/uploads/imagens/usuarios/<?php echo $user_avatar; ?>" class="img-fluid rounded-circle" alt="">
            </div>
            <div class="profile-details">
              <div class="profile-name px-3 pt-2">
                <h4 class="text-primary mb-0"><?php echo $user_name; ?></h4>
                <p><small><?php echo 'Usuário ' . $_SESSION['UserPlanString']; ?></small></p>
              </div>
              <div class="profile-email px-2 pt-2">
                <h4 class="text-muted mb-0"><?php echo $user_email; ?></h4>
                <p><small>Email</small></p>
              </div>
              <div class="dropdown ml-auto">
              <a href="minha-conta/solicitar-saque/" data-action="billing-request" class="mt-3 btn btn-success billing-request">Solicitar Saque</a>
                  <a href="https://dropexpress.com.br/#ofertas" target="_blank" class="mt-3 btn btn-primary">Fazer Upgrade</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-xl-12">
      <div class="card">
        <div class="card-body">
          <div class="profile-statistics">
            <div class="text-center">
              <div class="row">
                <div class="col">
                  <h2 class="m-b-0"><?php echo $stats['produtos_cadastrados']; ?></h2><span style="font-size: 0.8em;">Produtos<br>Cadastrados</span>
                </div>
                <div class="col">
                    <h2 class="m-b-0"><?php echo $stats['pedidos_recebidos']; ?></h2><span style="font-size: 0.8em;">Pedidos<br>Recebidos</span>
                </div>  
                <div class="col">
                  <h2 class="m-b-0"><?php echo $stats['entregas_realizadas']; ?></h2><span style="font-size: 0.8em;">Entregas<br>Realizadas</span>
                </div>
                <div class="col">
                  <h2 class="m-b-0"><?php echo $stats['valor_total']; ?></h2><span style="font-size: 0.8em;">Valor Total<br>em Vendas</span>
                </div>
                <div class="col">
                  <h2 class="m-b-0"><?php echo $stats['a_liberar']; ?></h2><span style="font-size: 0.8em;">Valor<br>a Liberar</span>
                </div>
                <div class="col">
                  <h2 class="m-b-0"><?php echo $stats['disponivel_saque']; ?></h2><span style="font-size: 0.8em;">Disponível<br>para Saque</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-12">
      <div class="card">
        <div class="card-body">
          <div class="profile-statistics">
            <div class="text-center">
              <div class="row">
                <div class="col">
                  <h2 class="m-b-0 user-plan-details"><?php echo ucwords(strtolower($_SESSION['UserPlanString'])); ?></h2><span class="text-black">SEU PLANO</span>
                </div>
                <div class="col">
                    <h2 class="m-b-0 user-plan-details"><?php echo "R$ 97,00" ; ?></h2><span class="text-black">MENSALIDADE</span>
                </div>  
                <div class="col">
                  <h2 class="m-b-0 user-plan-details"><?php echo "R$ "  .  number_format($_SESSION['UserPlanShipTax'], 2, ',', ''); ?></h2><span class="text-black">ENTREGA</span>
                </div>
                <div class="col">
                  <h2 class="m-b-0 user-plan-details"><?php echo number_format(($_SESSION['UserPlanTax'] * 100), 2, ',', '') . "%"; ?></h2><span class="text-black">TAXA</span>
                </div>
                <div class="col">
                  <h2 class="m-b-0 user-plan-details"><?php echo  $_SESSION['UserPaymentTerm'] . " dias"; ?></h2><span class="text-black">PRAZO</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-12">
      <div class="card">
        <div class="card-header d-block">
          <h4 class="card-title">Dados para Pagamento</h4> <div class="form-group mb-0">
              </div>
          <p class="m-0 subtitle"></p>
        </div>
        <div class="card-body">
        <div class="pt-3">
               <div class="settings-form">
                      <form id="SubscriberPayInfos" action="dados-para-pagamento" method="POST">
                      <input required type="hidden" name="action" value="update-pay-infos"> 
                        <div class="form-group">
                            <label>Tipo de Conta<i class="req-mark">*</i></label><br>
                            <label class="radio-inline mr-3">
                              <input required type="radio" value="fisica" name="tipo-de-pessoa" <?php if ($company_type == 'fisica') { echo "checked";} ?>> Pessoa Física
                            </label>
                            <label class="radio-inline mr-3">
                              <input required type="radio" value="juridica" name="tipo-de-pessoa" <?php if ($company_type == 'juridica') { echo "checked";} ?>> Pessoa Jurídica
                            </label>
                        </div> 
                        <div class="form-group">
                          <label>Nome Completo / Razão Social<i class="req-mark">*</i></label>
                          <input required type="text" name="assinante-nome-completo" placeholder="" class="form-control" value="<?php echo $company_name; ?>">
                        </div>
                        <div class="form-group">
                          <label>Documento (CPF/CNPJ)<i class="req-mark">*</i></label>
                          <input required type="text" name="assinante-documento" placeholder="" class="form-control" value="<?php echo $company_doc; ?>">
                        </div>
                        <div class="form-row">
                          <div class="form-group col-md-4">
                            <label>Código do Banco<i class="req-mark">*</i></label>
                            <input required type="text" name="assinante-banco" placeholder="" class="form-control" value="<?php echo $company_bank; ?>">
                          </div>
                          <div class="form-group col-md-8">
                            <label>Agência<i class="req-mark">*</i></label>
                            <input required type="text" name="assinante-agencia" placeholder="" class="form-control" value="<?php echo $company_agency; ?>">
                          </div>
                        </div>
                        <div class="form-row">
                          <div class="form-group col-md-6">
                            <label>Conta<i class="req-mark">*</i></label>
                            <input required type="text" name="assinante-conta" class="form-control" value="<?php echo $company_account; ?>">
                          </div>
                          <div class="form-group col-md-6">
                            <label>Tipo de Conta<i class="req-mark">*</i></label>
                            <select class="form-control default-select" id="select-tipo-conta">
                              <option disabled <?php if (!isset($company_account_type)) { echo "selected";} ?>>Selecione...</option>
                              <option value="corrente" <?php if ($company_account_type == 'corrente') { echo "selected";} ?>>Corrente</option>
                              <option value="poupanca" <?php if ($company_account_type == 'poupanca') { echo "selected";} ?>>Poupança</option>
                              <option value="pagamento" <?php if ($company_account_type == 'pagamento') { echo "selected";} ?>>Pagamento</option>
                            </select>
                            <input required type="hidden" id="text-tipo-conta" name="assinante-tipo-conta" class="form-control" value="<?php echo $company_account_type; ?>">
                          </div>
                        </div>
                        <div class="form-group">
                          <label>Chave Pix</label>
                          <input type="text" name="chave-pix" placeholder="" class="form-control" value="<?php echo $company_pix_key; ?>">
                        </div>
                        <button class="btn btn-primary" type="submit">Salvar Alterações</button>
                      </form>
                    </div>
                  </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
    require_once('includes/layout/default/default-footer.php');
?>