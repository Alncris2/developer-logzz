<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID'])) || $_SESSION['UserPlan'] != 5) {
  header('Location: ' . SERVER_URI . '/login');
}


$page_title = "Expedição | Logzz";
$postback_page = true; // Quando TRUE, insere o arquivo js/postbacks.js no rodapé da página.
$dispatch_page = $select_datatable_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

$products = $conn->prepare("SELECT product_name FROM products");
$products->execute();
$products_row = $products->fetchAll();

$users = $conn->prepare("SELECT full_name FROM users");
$users->execute();
$user_row = $users->fetchAll();

// PEGAR VERIFICAÇÕES ATIVAS
$get_dispatche_list = $conn->prepare('SELECT * FROM integration_notazz');
$get_dispatche_list->execute();


$get_product_list = $conn->prepare('SELECT * FROM products WHERE user__id = :user__id AND product_trash = 0 AND status = 1');
$get_product_list->execute(array('user__id' => $_SESSION['UserID']));

//PEGAR O NOME DO PRODUTO PARA VIEW


if (isset($_SESSION['userCode'])) {
  // PEGAR DADOS DO USUARIO SELECIONADO NA PAGINA DE EXPEDIÇÃO
  $stmt = $conn->prepare("SELECT * FROM users WHERE user_code = :user");
  $stmt->execute([':user' => $_SESSION['userCode']]);

  $user = $stmt->fetch();
}

if (isset($_SESSION['productCode'])) {
  // PEGAR DADOS DO USUARIO SELECIONADO NA PAGINA DE EXPEDIÇÃO 
  $get_product = $conn->prepare('SELECT * from products WHERE product_code = :code');
  $get_product->execute([':code' => $_SESSION['productCode']]);

  $product = $get_product->fetch();
}

// PEGAR VERIFICAÇÕES ATIVAS
$get_dispatche_list = $conn->prepare('SELECT * FROM integration_notazz');
$get_dispatche_list->execute();

// PEGAR TODOS OS PRODUTOS
$get_products_list = $conn->prepare('SELECT product_name, product_code, product_id FROM products');
$get_products_list->execute();

// PEGAR TODOS OS PRODUTOS
$get_products_name = $conn->prepare('SELECT product_name FROM products WHERE product_id = ?');
$get_products_name->execute(array(120));

// PEGAR TODOS OS USUARIOS
$get_users_list = $conn->prepare('SELECT full_name, user_code, user__id FROM users ORDER BY full_name ASC');
$get_users_list->execute();


?>
<div class="container-fluid">
  <!-- row -->
  <div class="row">
    <div class="col-xl-12 col-xxl-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">Integração com a Notazz</h4>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-lg-12 mb-2">
              <input type="hidden" name="action" value="new-integration-tiny-sudeste">
              <div class="form-group">
                <label class="text-label">Nome da Integração<i class="req-mark">*</i></label>
                <input type="text" name="integration-name" class="form-control" id="integration-name" required>
              </div>
              <div class="form-group">
                <label class="text-label">Chave de API<i class="req-mark">*</i></label>
                <input type="text" name="integration-unique-key" class="form-control" id="integration-unique-key" required>
              </div>
              <div class="form-group">
                <label class="text-label">Deseja emitir notas fiscais para clientes que<i class="req-mark">*</i></label>
                <select class="bling-uf-list" multiple="multiple" id="client_note" required>
                  <option select disabled>Selecione...</option>
                  <option value="status COMPLETO">Receberem seus produtos com pagamento físico nas operações locais (status COMPLETO)</option>
                  <option value="status ENVIADO"> receberem seus produtos despachados pelo centro de distribuição, de pedidos importados de plataformas externas (status ENVIADO)</option>
                </select>
                <input type="hidden" id="bling-uf-list-text" name="tiny-uf-list-text" value="" required>
              </div>
              <div class="form-group">
                <label class="text-label">Para os Usuários<i class="req-mark">*</i></label>
                <?php if (!isset($_SESSION['userCode'])) : ?>
                  <select class="bling-users-list" name="tiny-users-list" id="user" multiple="multiple" required>
                    <?php if ($get_users_list->rowCount() != 0) : ?>
                      <?php while ($row = $get_users_list->fetch()) : ?>
                        <?php
                        $full_name = $row['full_name'];
                        $user__id = $row['user__id'];
                        $user_code = $row['user_code'];
                        ?>
                        <option value="<?php echo $user__id;  ?>"><?php echo $full_name . " <small>[" . $user_code . "]</small>";  ?></option>

                      <?php endwhile; ?>
                    <?php endif; ?>
                  </select>
                  <input type="hidden" id="user-id" value="<?php echo $_SESSION['UserID'];  ?>">
                  <input type="hidden" id="bling-users-list-text" name="tiny-users-list-text" value="" required>
                <?php else : ?>
                  <input type="text" id="tiny-users-text" class="form-control" name="tiny-users-list-text" value="<?= $user['full_name']; ?> [<?= $user['user_code']; ?>]" readonly>
                <?php endif; ?>
              </div>

              <div class="form-group">
                <?php if (isset($_SESSION['productCode'])) : ?>
                  <!-- PEGAR DADOS DO USUARIO SELECIONADO NA PAGINA DE EXPEDIÇÃO -->
                  <label class="text-label">Para os produtos<i class="req-mark">*</i></label>
                  <input type="text" name="tiny-products-list-text" class="form-control" value="<?= $product['product_name'] . "[" . $product['product_code'] . "]" ?>" readonly>
                <?php else : ?>
                  <!-- SELECT DE TODOS OS PRODUTOS -->
                  <label class="text-label">Para os produtos<i class="req-mark">*</i></label>

                  <select class="tiny-product-list" id="product" name="tiny-products-list" multiple="multiple" required>
                    <?php if ($get_products_list->rowCount() != 0) : ?>
                      <?php while ($row = $get_products_list->fetch()) : ?>
                        <?php
                        $full_name = $row['product_name'];
                        $user__id = $row['product_id'];
                        $user_code = $row['product_code'];
                        ?>
                        <option value="<?php echo $full_name . " <small>[" . $user_code . "]</small>";  ?>" id="product-name">
                          <?php echo $full_name . " <small>[" . $user_code . "]</small>";  ?>
                        </option>
                      <?php endwhile; ?>
                    <?php endif; ?>
                  </select>
                  <input type="hidden" id="tiny-products-list-text" name="tiny-products-list-text" value="" required>
                <?php endif; ?>
              </div>
              <?php if (isset($_SESSION['integration_url'])) : ?>
                <input type="hidden" name="integration_url" value="<?= $_SESSION['integration_url'] ?>" />
              <?php endif; ?>
            </div>
          </div>
          <button type="submit" id="SubmitButton" class="btn btn-success mb-3"><i class="fas fa-compress-arrows-alt"></i> Criar Nova Integração</button>
        </div>

      </div>
    </div>


  </div>

</div>
</div>


<?php require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php'); ?>

<script>
  $('#SubmitButton').on('click', function() {

    var user = document.getElementById("integration-name").value;
    var re = /^[A-Za-záàâãéèêíïóôõöúçñÁÀÂÃÉÈÍÏÓÔÕÖÚÇÑ ']+$/;

    if (re.test(user)) {
      var nameAdd = $('#integration-name').val();
      var keyAdd = $('#integration-unique-key').val();
      var noteAdd = $('#client_note').val();
      var productAdd = $('#product').val();

      var productNameAdd = $('#product-name').val();

      var userAdd = $('#user').val();

      $.ajax({
        url: "/ajax/notazz.php",
        type: "post",
		    dataType: 'json',
        data: {
          nameSend: nameAdd,
          keySend: keyAdd,
          noteSend: noteAdd,
          productSend: productAdd,
          userSend: userAdd
        },
        success: function({type, title, icon, msg}) {
          Swal.fire({type: type, title: title, icon: icon, text: msg}).then((result) => {
            window.location.href = "/integracoes/nota-fiscal/";
          })
        }
      });
      return;
    } 

    Swal.fire({
      title: "Integração!",
      text: "Nome da integração inválido",
      icon: 'warning'
    });

  });

  $('.delete_notazz_integration').each(function() {

    $(this).on("click", (function(element) {
      var id = element.id;

      console.log(element);
      return;

      Swal.fire({
        title: "Deletar!",
        text: "Tem certeza que deseja deletar esta integração?",
        showCancelButton: true,
        denyCancelText: 'Cancelar',
        icon: 'warning'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: "/ajax/delete-notazz-integration.php",
            type: "POST",
            data: {
              idSend: id
            },
            success: function(status, data) {
              Swal.fire({
                title: "Sucesso!",
                text: "Integração deletada com sucesso!",
                showCancelButton: true,
                denyCancelText: 'Cancelar',
                icon: 'success'
              }).then(response => {
                document.location.reload(true);
              })
            }
          });
        }
      })

    }))

  });
</script>