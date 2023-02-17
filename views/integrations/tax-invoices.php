<?php

require_once (dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
}

$page_title = "Nota Fiscal | Logzz";
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

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

$is_empty = $conn->prepare('SELECT * FROM integration_notazz');
$is_empty->execute();


$get_product_list = $conn->prepare('SELECT * FROM products WHERE user__id = :user__id AND product_trash = 0');
$get_product_list->execute(array('user__id' => $_SESSION['UserID']));

//PEGAR O NOME DO PRODUTO PARA VIEW


if(isset($_SESSION['userCode'])){
    // PEGAR DADOS DO USUARIO SELECIONADO NA PAGINA DE EXPEDIÇÃO
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_code = :user");
    $stmt->execute([':user' => $_SESSION['userCode']]);
    
    $user = $stmt->fetch();
}

if(isset($_SESSION['productCode'])){
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
          <h4 class="card-title">Integrações Disponíveis</h4>
        </div>
      
        <div class="card-body">
          <a href="notazz/" class="intg-btn" id="intg-btn-enotas" style="background: url('data:image/png;base64,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')"></a>  
        </div>
      </div> 
    </div>
    <?php
        $verify = $is_empty->fetchAll();
        if(!empty($verify)){
    ?>

<div class="col-xl-12 col-xxl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Suas Integrações</h4>
                </div>
                <div class="card-body">
                        <div class="row">
                            <div class="col-sm-12 mb-2">
                                <?php if ($get_dispatche_list->rowCount() != 0): ?>
                                    <div class="table-responsive accordion__body--text">
                                        <table class="table table-responsive-md" id="dispatches-datatable">
                                            <thead>
                                                <tr>
                                                    <th class="col-md-3">Integração</th>
                                                    <th class="col-md-2">Nota</th>
                                                    <th class="col-md-2">Usuarios</th>
                                                    <th class="col-md-2">Produtos</th>
                                                    <th class="col-md-2">Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($row = $get_dispatche_list->fetch()): ?>
                                                    <tr>
                                                        <td class=""><?php echo $row['name_integration']; ?></td>
                                                        <td class=""><a style="cursor: pointer;" data-toggle="tooltip" data-placement="top" title="<?php

                                                         if($row['issuance_of_invoice']=='status COMPLETO'){
                                                            echo "Receberam seus produtos com pagamento físico nas operações locais";
                                                        }
                                                         else if ($row['issuance_of_invoice'] == 'status ENVIADO'){
                                                            echo "Receberam seus produtos despachados pelo centro de distribuição, de pedidos importados de plataformas externas";
                                                        }else{
                                                             echo 'Receberam seus produtos com pagamento físico nas operações locais   -   Receberam seus produtos despachados pelo centro de distribuição, de pedidos importados de plataformas externas'; 
                                                        }

                                                         ?>"> <?php echo ucwords(strtolower($row['issuance_of_invoice'])); ?></a></td> 
                                                        <td class=""><?php echo $row['qtd_users'] ?></td>
                                                        <td class=""><?php echo $row['product_name'] ?></td>
                                                        <td class="">    
                                                            <a title="Deletar Integração" class="btn delete_notazz_integration" id="<?php echo $row['integration_id'];?>" value="<?php echo $row['integration_id'];?>"><i class="fa fa-trash"></i></a>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                </div>
            </div>
        </div>

  </div>
    
  <?php }?>
</div>


<?php
    require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>

<script>
  
  $('.delete_notazz_integration').each(function(){
    
    $(this).on("click",(function(){

      var id = ($(this)[0].id);
      console.log($(this)[0].id);

      Swal.fire({
        title: "Deletar!",
        text: "Tem certeza que deseja deletar esta integração?",
        showCancelButton: true,
        denyCancelText: 'Cancelar',  
        icon: 'warning'
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url:"/ajax/delete-notazz-integration.php",
            type:"POST",
            data:{ 
              idSend:id
            },
            success:function(status,data){
              Swal.fire({
                title: "Sucesso!",
                text: "Integração deletada com sucesso!",
                showCancelButton: true,
                denyCancelText: 'Cancelar',  
                icon: 'success'
              }).then((result) => {
                document.location.reload(true);
              })
            }
          });
        }  
      }) 
    })) 
   
});
</script>