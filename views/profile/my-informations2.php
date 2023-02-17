<?php
require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
  header('Location: ' . SERVER_URI . '/login');
  exit;
}

$select_datatable_page = true;
$profile_page = true;
$page_title = "Minhas Informações | Logzz";
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

$user_id = $_SESSION['UserID'];
$user_full_name = $_SESSION['UserFullName'];
$user_email = $_SESSION['UserEmail'];

$stmt = $conn->prepare('SELECT users.company_doc,users.user_phone FROM users WHERE user__id = :user_id');
$stmt->execute(array('user_id' => $user_id));

if($stmt->rowCount() != 0) {
  while ($row = $stmt->fetch()) {
	$company_doc = $row['company_doc'];
	$user_phone = $row['user_phone'];
  }
}
?>
<div class="container-fluid">
  <div class="row">
	<div class="col-lg-12">
	  <div class="card">
	  <div div class="card-header">
		  <h4 class="card-title">Dados do usuário</h4>
		</div>
		<div class="">
		<div class=" card-body">
		  <div class="row">
			<div class="form-group col-12">
			  <label>Nome</label>
			  <input class="form-control" readonly disabled style="cursor: not-allowed;" type="text" value="<?php echo $user_full_name; ?>"/>
			</div>
		  </div>
		  <div class="row">
			<div class="form-group col-6">
			  <label>E-mail</label>
			  <input class="form-control" readonly disabled style="cursor: not-allowed;" type="text" value="<?php echo $user_email; ?>"/>
			</div>
			<div class="form-group col-6">
			  <label>Documento</label>
			  <input class="form-control" readonly disabled style="cursor: not-allowed;" type="text" value="<?php echo $company_doc; ?>"/>
			</div>
			</div>
		  </div>
		</div>
	  </div>
	</div>
  </div>

  <div class="row">
	<div class="col-lg-6">
	  <div class="card">
		<div div class="card-header">
		  <h4 class="card-title">Contato</h4>
		</div>
		<div class="card-body mb-2">
		<h4>Celular</h4>
			<p class="fs-14">Insira um telefone <strong>válido</strong> pois ele será usado para <strong>validação de segurança</strong> 
				durante procedimentos na plataforma, como <strong>solicitação de saque</strong> entre outros</p>
			<form id="ChangePhoneForm" action="mudar-celular" method="POST">
				<div class="form-group">
					<input type="hidden" id="ActionInput1" name="action" value="mudar-celular">
					<input class="form-control whats w-100" placeholder="(99) 9 9999-9999" name="celular" type="text" value="<?php echo $user_phone; ?>" maxlength="16">
				</div>
				<div id="phoneCodeVerification" class="form-group"></div>
				<div class="form-group">
					<button type="submit" class="btn btn-rounded btn-success mt-1">
						<i class="fas fa-save mr-2"></i>Salvar celular
					</button>
			  	</div>
			</form>
		</div>
		</div>
	  </div>

	  <div class="col-lg-6">
		<div class="card">
		  <div class="card-header">
			<h4 class="card-title">Segurança</h4>
		  </div>
		  <div class="card-body">
			  <h4>Senha</h4>
			  <p class="card-text fs-14">
				  É recomendável usar uma senha forte que você não esteja usando em nenhum outro lugar.
			  </p>
			  <form id="ChangePasswordForm" action="mudar-senha" method="POST">
				<input type="hidden" id="ActionInput2" name="action" value="mudar-senha">
				<div class="form-group">
					<label>Senha atual</label>
					<input class="form-control" type="password" name="senha-atual" autocomplete="off"/>
				</div>
				<div class="form-group">
					<label>Nova senha</label>
					<input class="form-control" type="password" name="nova-senha" autocomplete="off"/>
				</div>
				<div class="form-group">
					<label>Confirmar senha</label>
					<input class="form-control" type="password" name="nova-senha-conf" autocomplete="off"/>
				</div>
				<div class="form-group"></div>
				<div id="codeVerification" class="form-group col-6 row"></div>
				<button type="submit" class="btn btn-rounded btn-success">
				<i class="fas fa-save mr-2"></i>Alterar Senha</button>
				</div>
			  </form>
		  </div>
		  <div class="card-footer">
		  </div>
		</div>
	  </div>
	</div>
  
</div>



<?php
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>