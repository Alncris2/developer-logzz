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

$get_product_list = $conn->prepare('SELECT * FROM products WHERE user__id = :user__id AND product_trash = 0 AND status = 1');
$get_product_list->execute(array('user__id' => $_SESSION['UserID']));

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
$get_dispatche_list = $conn->prepare('SELECT * FROM tiny_dispatches WHERE dispatche_region_id = 5 ORDER BY id DESC');
$get_dispatche_list->execute();

// PEGAR TODOS OS PRODUTOS
$get_products_list = $conn->prepare('SELECT product_name, product_code, product_id FROM products');
$get_products_list->execute();

// PEGAR TODOS OS USUARIOS
$get_users_list = $conn->prepare('SELECT full_name, user_code, user__id FROM users ORDER BY full_name ASC');
$get_users_list->execute();

// PEGAR TODAS AS UF'S
$get_region_ufs_list = $conn->prepare('SELECT * FROM ufs ORDER BY uf_string ASC');
$get_region_ufs_list->execute();

?>
<style>
    span.select2-selection--multiple{
        height: auto;
    }
</style>
<div class="container-fluid">
    <!-- row -->
    <div class="row">
        <div class="col-xl-12 col-xxl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Tiny - Sudeste</h4>
                </div>
                <div class="card-body">
                    <form id="IntegrationTiny" action="/api/v1/integrationTiny" method="POST">
                        <div class="row">
                            <div class="col-lg-12 mb-2">
                                <input type="hidden" name="action" value="new-integration-tiny-sudeste">
                                <div class="form-group">
                                    <label class="text-label">Nome da Integração<i class="req-mark">*</i></label>
                                    <input type="text" name="integration-name" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="text-label">Chave de API<i class="req-mark">*</i></label>
                                    <input type="text" name="integration-unique-key" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="text-label">Receber Pedido das UFs<i class="req-mark">*</i></label>
                                    <select class="bling-uf-list" name="tiny-uf-list" multiple="multiple" required>
                                        <option><small>Selecionar todos as UFs</small></option>
                                        <?php if ($get_region_ufs_list->rowCount() != 0): ?>
                                            <?php while ($row = $get_region_ufs_list->fetch()): ?>
                                                <?php 
                                                    $uf_string = $row['uf_string'];
                                                    $uf = $row['uf'];
                                                ?>
                                                <option value="<?php echo $uf;  ?>"><?php echo utf8_encode($uf_string);  ?></option>
                                            <?php endwhile; ?>
                                        <?php endif; ?>
                                    </select>
                                    <input type="hidden" id="bling-uf-list-text" name="tiny-uf-list-text" value="" required>
                                </div>
                                 <?php if(isset($_SESSION['integration_url'])): ?>
                                    <input type="hidden" name="integration_url" value="<?= $_SESSION['integration_url'] ?>"/>
                                <?php endif; ?>
                                <div class="form-group">
                                    <label class="text-label">Para os Usuários<i class="req-mark">*</i></label>
                                    <?php if(!isset($_SESSION['userCode'])): ?>
                                        <select class="bling-users-list" name="tiny-users-list" multiple="multiple" required>
                                            <?php if ($get_users_list->rowCount() != 0): ?>
                                                <?php while ($row = $get_users_list->fetch()): ?>
                                                    <?php 
                                                        $full_name = $row['full_name'];
                                                        $user__id = $row['user__id'];
                                                        $user_code = $row['user_code'];
                                                    ?>
                                                    <option value="<?php echo $user__id;  ?>"><?php echo $full_name . " <small>[" . $user_code . "]</small>";  ?></option>
                                                <?php endwhile; ?>
                                            <?php endif; ?>
                                        </select>
                                        
                                        <input type="hidden" id="bling-users-list-text" name="tiny-users-list-text" value="" required>
                                    <?php else: ?>
                                       <input type="text" id="tiny-users-text" class="form-control" name="tiny-users-list-text" value="<?= $user['full_name']; ?> [<?= $user['user_code']; ?>]" readonly>
                                    <?php endif; ?>
                                </div>
                                
                               <div class="form-group">
                                    <?php if(isset($_SESSION['productCode'])): ?>
                                        <!-- PEGAR DADOS DO USUARIO SELECIONADO NA PAGINA DE EXPEDIÇÃO -->
                                        <label class="text-label">Para os produtos<i class="req-mark">*</i></label>
                                        <input type="text" name="tiny-products-list-text" class="form-control" value="<?= $product['product_name'] . "[" . $product['product_code'] . "]"?>" readonly>
                                    <?php else: ?>
                                            <!-- SELECT DE TODOS OS PRODUTOS -->
                                            <label class="text-label">Para os produtos<i class="req-mark">*</i></label>
                                            
                                            <select id="" class="tiny-product-list" name="tiny-products-list" multiple="multiple" required>
                                                <?php if ($get_products_list->rowCount() != 0): ?>
                                                    <?php  while($row = $get_products_list->fetch()): ?>
                                                        <?php 
                                                            $full_name = $row['product_name'];
                                                            $user__id = $row['product_id'];
                                                            $user_code = $row['product_code'];
                                                        ?>
                                                        <option value="<?php echo $user__id;  ?>"><?php echo $full_name . " <small>[" . $user_code . "]</small>";  ?></option>
                                                    <?php endwhile; ?>
                                                <?php endif; ?>
                                            </select>
                                            <input type="hidden" id="tiny-products-list-text" name="tiny-products-list-text" value="" required>
                                    <?php endif;?>
                            
                                </div>
                            </div>
                        </div>
                        <button type="submit" id="SubmitButton" class="btn btn-success mb-3"><i class="fas fa-compress-arrows-alt"></i> Criar Nova Integração</button>
                </div>
                </form>
            </div>
        </div>

        <div class="col-xl-12 col-xxl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Integrações Ativas - Região Sudeste</h4>
                </div>
                <div class="card-body">
                        <div class="row">
                            <div class="col-sm-12 mb-2">
                                <?php if ($get_dispatche_list->rowCount() != 0): ?>
                                    <div class="table-responsive accordion__body--text">
                                        <table class="table table-responsive-md" id="dispatches-datatable">
                                            <thead>
                                                <tr>
                                                    <th class=" col-md-2">Integração</th>
                                                    <th class=" col-md-2">Produtos</th>
                                                    <th class=" col-md-3">UFs</th>
                                                    <th class=" col-md-3">Usuários</th>
                                                    <th class=" col-md-1">Status</th>
                                                    <th class=" col-md-1">Ações</th> 
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($row = $get_dispatche_list->fetch()): ?>
                                                    <!-- VERIFICAR STATUS DA INTEGRAÇÃO -->
                                                    <?php  
                                                        $get_status = $conn->prepare("SELECT status FROM integrations WHERE integration_url = :integration_url");
                                                        $get_status->execute(['integration_url' => $row['url_integration']]);
                                                        $status = $get_status->fetch(\PDO::FETCH_ASSOC)['status'];
                                                    ?>
                                                    <tr>
                                                        <td class=""><?php echo $row['nome_integracao']; ?></td>
                                                        <td class=""><?php echo $row['products_ids'] ?></td>
                                                        <td class=""><?php echo str_replace(",",", ", $row['ufs']); ?></td>
                                                        <td class=""><?php echo $row['users_id'] ?></td>
                                                        <td class="text-center">
                                                            <?php if($status == 1): ?>
                                                                <span class="badge badge-xs badge-success mb-1">Ativa</span>
                                                            <?php else: ?>
                                                                <span class="badge badge-xs badge-danger mb-1">Inativa</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-center">
                                                          <style>
                                                            .delete_integration{
                                                              cursor: pointer;
                                                            }
                                                          </style>
                                                          <a title="Atualizar Integração" href="" class="mr-2 update_status" id="<?php echo $row['id'];?>" data-url="<?= $row['url_integration']?>">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="14px" fill="#343a40aa">  <path d="M32 176h370.8l-57.38 57.38c-12.5 12.5-12.5 32.75 0 45.25C351.6 284.9 359.8 288 368 288s16.38-3.125 22.62-9.375l112-112c12.5-12.5 12.5-32.75 0-45.25l-112-112c-12.5-12.5-32.75-12.5-45.25 0s-12.5 32.75 0 45.25L402.8 112H32c-17.69 0-32 14.31-32 32S14.31 176 32 176zM480 336H109.3l57.38-57.38c12.5-12.5 12.5-32.75 0-45.25s-32.75-12.5-45.25 0l-112 112c-12.5 12.5-12.5 32.75 0 45.25l112 112C127.6 508.9 135.8 512 144 512s16.38-3.125 22.62-9.375c12.5-12.5 12.5-32.75 0-45.25L109.3 400H480c17.69 0 32-14.31 32-32S497.7 336 480 336z"/></svg>
                                                          </a>  
                                                          <a title="Deletar Integração" data-url="<?= $row['url_integration']?>" class="delete_integration" id="<?php echo $row['id'];?>" value="<?php echo $row['id'];?>"><i class="fa fa-trash"></i></a>
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
</div>
</div>

<?php unset($_SESSION['userCode']); unset($_SESSION['productCode']); unset($_SESSION['integration_url']); ?>
<?php require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php'); ?>

<!-- ENVIAR PARA ARQUIVO .JS POSTERIORMENTE -->
<script>
$('.delete_integration').each(function(){
    
    $(this).on("click",(function(){

      const id = ($(this)[0].id);
      const url_integration = $(this).data('url');

      Swal.fire({
        title: "Deletar!",
        text: "Tem certeza que deseja deletar esta integração?",
        showCancelButton: true,
        denyCancelText: 'Cancelar',  
        icon: 'warning'
        
        }).then((result) => {
        
            if (result.isConfirmed) {
                $.ajax({
                    url:"/ajax/status-tiny-integration.php",
                    type:"POST",
                    dataType: 'json',
                    data:{
                        status: 'delete',
                        url: url_integration,
                        idSend:id
                    },
                    success:function({type, msg, title}){
                        Swal.fire({
                            title: title,
                            text: msg,
                            icon: type
                        }).then(response => {
                            document.location.reload();
                        })
                    }
                });
            }

        })

    })) 
   
});


$('.bling-uf-list').on("select2:select", function (e) { 
    var data = e.params.data.text;
    if(data=='Selecionar todos as UFs'){
        $(".bling-uf-list > option").prop("selected","selected");
        $(".bling-uf-list").trigger("change");
    }
});

$('.update_status').each(function(){
    $(this).on("click", (e) => {
        e.preventDefault();

        const url_integration = $(this).data('url');
       

        Swal.fire({
            title: "Atualizar status!",
            text: "Tem certeza que deseja atualizar o status dessa integração?",
            showCancelButton: true,
            denyCancelText: 'Cancelar',  
            icon: 'warning'  
        }).then(({isConfirmed}) => {
            if(isConfirmed){
                $.ajax({
                    url:"/ajax/status-tiny-integration.php",
                    type:"POST",
                    dataType: 'json',
                    data:{
                        status: 'update',
                        url: url_integration
                    },
                    success:function({type, msg, title}){
                        Swal.fire({
                            title: title,
                            text: msg,
                            icon: type
                        }).then(response => {
                            document.location.reload();
                        })
                    }
                })
            }
        })  
    })
})

</script>

