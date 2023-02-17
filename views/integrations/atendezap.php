<?php
error_reporting(-1);            
ini_set('display_errors', 1);   
require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID'])) || $_SESSION['UserPlan'] != 5) {
    header('Location: ' . SERVER_URI . '/login');
}


$page_title = "Integração com a Atendezap | Logzz";
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');


$get_integrations = $conn->prepare('SELECT * FROM atendezap_integration WHERE az_level LIKE "owner" AND az_name NOT LIKE "new_user" ');
$get_integrations->execute();

$get_integration_user = $conn->prepare('SELECT * FROM atendezap_integration WHERE az_level LIKE "owner" AND az_name LIKE "new_user" LIMIT 1');
$get_integration_user->execute();

$word = new \NumberFormatter('en-us', \NumberFormatter::SPELLOUT);  
$word->format(1);   

?>
<div class="container-fluid">
    <!-- row -->
    <div class="text-right mb-3">
        <button type="button" class="btn btn-rounded btn-success text-nowrap" data-toggle="modal" data-target="#ModalAddTriggerAtendeZap">
            <i class="fas fa-plus scale2 mr-2" aria-hidden="true"></i>
            Adicionar Disparo
        </button>
    </div> 

<div id="integration-accordion" class="accordion accordion-with-icon">

    <?php while($new_user = $get_integration_user->fetch()){ ?> 
    <!-- WebHook Disparos para Novos Usuários --> 
    <div class="card accordion__item">
        <div class="card-header collapsed" data-toggle="collapse" data-target="#welcome-collapse" aria-expanded="false">
            <h4 class="card-title"><i style="color: #777777" class="fas fa-angle-down"></i>&nbsp;Disparos para Novos Usuários</h4>
            <button type="button" class="btn btn-rounded btn-danger btn-sm disabled">
                <i class="fa fa-trash-alt"></i> 
            </button>
        </div>
        <div id="welcome-collapse" class="card-bodyaccordion__body collapse" data-parent="#integration-accordion">
            <div class="accordion__body--text">
                <form id="updateWebhookNewUsers">
                    <div class="row">
                        <div class="col-lg-12 mb-2">
                            <input type="hidden" name="action" value="update-integration-az">
                            <input type="hidden" name="level" value="<?= $new_user['az_level'] ?>">                                
                            <input type="hidden" name="id" value="<?= $new_user['az_id'] ?>">                                                            
                            <input type="hidden" name="name" value="<?= $new_user['az_name'] ?>">  
                            <div class="form-group">
                                <label class="text-label">Chave de API<i class="req-mark">*</i></label>
                                <input type="text" name="key" class="form-control" id="company-key-newuser" value="<?= $new_user['az_key'] ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="text-label">Link WebHook<i class="req-mark">*</i></label>
                                <input type="text" name="link" class="form-control" id="company-link-newuser" value="<?= $new_user['az_webhook'] ?>" required>
                            </div>
                        </div>
                    </div>
                    <button type="submit" data-form="updateWebhookNewUsers" class="btn btn-success mb-3 update-integration"><i class="fas fa-compress-arrows-alt"></i> Salvar alteração</button>
                </form>
            </div>
        </div>
    </div>
    <!-- END WebHook Disparos para Novos Usuários -->    
    <?php } ?> 

    <?php while($row = $get_integrations->fetch()){ 
        $row['az_status']++; ?>  
        
    <div class="card accordion__item"> 
        <div class="card-header collapsed" data-toggle="collapse" data-target="#<?= $word->format($row['az_id']) ?>-collapse" aria-expanded="false">
            <h4 class="card-title"><i style="color: #777777" class="fas fa-angle-down"></i>&nbsp;<?= $row['az_name'] ?></h4> 
            <button type="button" class="btn btn-rounded btn-danger btn-sm delete-integration" data-id-integration="<?= $row['az_id'] ?>">
                <i class="fa fa-trash-alt"></i>     
            </button>
        </div> 
        <div id="<?= $word->format($row['az_id']) ?>-collapse" class="card-bodyaccordion__body collapse" data-parent="#integration-accordion">
            <div class="accordion__body--text">
                <form id="updateWebhook_<?= $row['az_id'] ?>"> 
                    <div class="row">
                        <div class="col-lg-12 mb-2">                            
                            <input type="hidden" name="action" value="update-integration-az"> 
                            <input type="hidden" name="level" value="<?= $row['az_level'] ?>">                                
                            <input type="hidden" name="id" value="<?= $row['az_id'] ?>"> 
                            <div class="form-group">
                                <label class="text-label">Nome da Companhia<i class="req-mark">*</i></label>
                                <input type="text" name="name" class="form-control" id="company-name-<?= $row['az_id'] ?>" value="<?= $row['az_name'] ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="text-label">Chave de API<i class="req-mark">*</i></label>
                                <input type="text" name="key" class="form-control" id="company-key" value="<?= $row['az_key'] ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="text-label">Link WebHook<i class="req-mark">*</i></label>
                                <input type="text" name="link" class="form-control" id="company-link-<?= $row['az_id'] ?>" value="<?= $row['az_webhook'] ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="text-label">Status<i class="req-mark">*</i></label>
                                <select class="form-control default-select" name="status" id="status-<?= $row['az_id'] ?>" required>
                                    <option value="" disabled selected hidden>Selecione um status</option>
                                    <option value="1" <?= $row['az_status'] == 1 ? 'selected' : '' ?>>Agendada</option>
                                    <option value="2" <?= $row['az_status'] == 2 ? 'selected' : '' ?>>Reagendada</option>
                                    <option value="3" <?= $row['az_status'] == 3 ? 'selected' : '' ?>>Atrasada</option>
                                    <option value="4" <?= $row['az_status'] == 4 ? 'selected' : '' ?>>Completa</option>
                                    <option value="5" <?= $row['az_status'] == 5 ? 'selected' : '' ?>>Frustrada</option>
                                    <option value="6" <?= $row['az_status'] == 6 ? 'selected' : '' ?>>Cancelada</option>
                                    <option value="7" <?= $row['az_status'] == 7 ? 'selected' : '' ?>>Á Enviar</option>
                                    <option value="8" <?= $row['az_status'] == 8 ? 'selected' : '' ?>>Enviando</option>
                                    <option value="9" <?= $row['az_status'] == 9 ? 'selected' : '' ?>>Enviado</option>
                                    <option value="10" <?= $row['az_status'] == 10 ? 'selected' : '' ?>>Reembolsado</option>
                                    <option value="11" <?= $row['az_status'] == 11 ? 'selected' : '' ?>>Confirmado</option>
                                    <option value="12" <?= $row['az_status'] == 12 ? 'selected' : '' ?>>Expirado</option> 
                                </select>
                            </div>
                        </div> 
                    </div>
                    <button type="submit" data-form="updateWebhook_<?= $row['az_id'] ?>" class="btn btn-success mb-3 update-integration"><i class="fas fa-compress-arrows-alt"></i> Salvar alteração</button>
                </form>
            </div>
        </div>
    </div>

    <?php } ?> 
</div>

<!-- Modal -->
<div class="modal fade" id="ModalAddTriggerAtendeZap" tabindex="-1" aria-labelledby="ModalAddTriggerAtendeZapLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ModalAddTriggerAtendeZapLabel">Adicionar Disparo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="new-integration-az">
                <div class="modal-body">                    
                    <input type="hidden" name="action" value="new-integration-az">                     
                    <input type="hidden" name="level" value="owner">  
                    <div class="form-group">
                        <label class="text-label">Nome da Companhia<i class="req-mark">*</i></label>
                        <input type="text" name="name" class="form-control" id="add-company-name" required>
                    </div>
                    <div class="form-group">
                        <label class="text-label">Chave de API<i class="req-mark">*</i></label>
                        <input type="text" name="key" class="form-control" id="add-company-key" required>
                    </div>
                    <div class="form-group">
                        <label class="text-label">Link WebHook<i class="req-mark">*</i></label>
                        <input type="text" name="link" class="form-control" id="add-company-key" required>
                    </div>
                    <div class="form-group">
                        <label class="text-label">Status<i class="req-mark">*</i></label>
                        <select class="form-control default-select atende-status-list" name="status" id="add-company-status-list" required>
                            <option value="" disabled selected hidden>Selecione um status</option> 
                                <option value="1">Agendada</option>
                                <option value="2">Reagendada</option>
                                <option value="3">Atrasada</option>
                                <option value="4">Completa</option>
                                <option value="5">Frustrada</option>
                                <option value="6">Cancelada</option>
                                <option value="7">Á Enviar</option>
                                <option value="8">Enviando</option>
                                <option value="9">Enviado</option>
                                <option value="10">Reembolsado</option>
                                <option value="11">Confirmado</option>
                                <option value="12">Expirado</option> 
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Adicionar</button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
</div>




<?php require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php'); ?>

<script>
    jQuery(document).ready(function() { 

        $(document).on("submit", "form", function (e) {
            var oForm = $(this);
            var formId = oForm.attr("id");
            var firstValue = oForm.find("input").first().val();
            alert("Form '" + formId + " is being submitted, value of first input is: " + firstValue);
            // Do stuff 
            return false; 
        }); 


        $('.update-integration').click(function() { 
            event.preventDefault();  

            // Captura os dados do formulário
            console.log($(this).data('form')); 
            form = $(this).data('form'); 
            var ReleaseBillingForm = document.getElementById(form);

            // Instância o FormData passando como parâmetro o formulário
            var formData = new FormData(ReleaseBillingForm);

            $.ajax({
                url: u + "/ajax/new-atendezap-integration.php", 
                type: "POST",
                data: formData,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function(feedback) {
                    Swal.fire({
                        title: feedback.title,
                        text: feedback.msg,
                        icon: feedback.type,
                    }).then((value) => {                            
                        if (feedback.type == 'success') {
                            window.location.reload(true);
                        }
                    });
                }
            }).fail(function (data) {
                Swal.fire({
                    title: "Erro #RB001",
                    text: "Não foi possível atualizar dos dados. atualize a página e tente novamente!",
                    icon: 'error',
                });
            });
            return false;
        })

        $('#new-integration-az').submit( function() { 
            event.preventDefault();  

            // Captura os dados do formulário
            var ReleaseBillingForm = document.getElementById('new-integration-az');

            // Instância o FormData passando como parâmetro o formulário
            var formData = new FormData(ReleaseBillingForm);

            $.ajax({
                url: u + "/ajax/new-atendezap-integration.php", 
                type: "POST",
                data: formData,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function(feedback) {
                    Swal.fire({
                        title: feedback.title,
                        text: feedback.msg,
                        icon: feedback.type,
                    }).then((value) => {                            
                        if (feedback.type == 'success') {
                            window.location.reload(true);
                        }
                    });
                }
            }).fail(function (data) {
                Swal.fire({
                    title: "Erro #RB001",
                    text: "Não foi possível criar integração. atualize a página e tente novamente!",
                    icon: 'error',
                });
            });
            return false;
        })

        $('.delete-integration').click(function() { 

            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success ml-2',
                    cancelButton: 'btn btn-danger'
                },
                buttonsStyling: false
            })

            swalWithBootstrapButtons.fire({
                title: 'Você tem certeza ?',
                text: "Essa ação não é reversível.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sim',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
                }).then((result) => {
                if (result.isConfirmed) {

                    // Captura os dados do formulário
                    id_integration = $(this).data('id-integration'); 

                    var formData = new FormData();
                    formData.append("id-integration", id_integration);
                    formData.append("action", 'delete-integration-az');

                    $.ajax({
                        url: u + "/ajax/new-atendezap-integration.php?id=" + id_integration,  
                        type: "POST",
                        data: formData,
                        dataType: 'json',
                        processData: false,
                        contentType: false,
                        success: function(feedback) {
                            swalWithBootstrapButtons.fire({
                                title: feedback.title,
                                text: feedback.msg,
                                icon: feedback.type,
                            }).then((value) => {                            
                                if (feedback.type == 'success') {
                                    window.location.reload(true);
                                }
                            });
                        }
                    }).fail(function (data) {
                        Swal.fire({
                            title: "Erro #RB001",
                            text: "Não foi possível deletar a integração. atualize a página e tente novamente!",
                            icon: 'error',
                        });
                    });
                } else if (
                    /* Read more about handling dismissals below */
                    result.dismiss === Swal.DismissReason.cancel
                ) {
                    swalWithBootstrapButtons.fire( 'Cancelado', '', 'error' )
                }
            })

            
        })
    });
</script>