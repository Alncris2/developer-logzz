<?php
//   error_reporting(-1);            
// ini_set('display_errors', 1);    
require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();


$user__id = $_SESSION['UserID']; 

$get_notification_list = $conn->prepare("SELECT n.*, u.full_name FROM notifications AS n LEFT JOIN users u ON u.user__id = n.user__id");
$get_notification_list->execute();

$get_shooting_notification_list = $conn->prepare("SELECT * FROM shooting_notification AS sn");
$get_shooting_notification_list->execute(); 

$users_search_list = $conn->prepare("SELECT full_name, user__id FROM users AS u WHERE u.active = 1");
$users_search_list->execute();
$users = $users_search_list->fetchAll();

$billing_history = true;
$select_datatable_page = true;
$page_title = "Central de Notificações | Logzz";

require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

?>
<div class="container-fluid">
    <!-- row -->
    <div class="row">
        <div class="col-xl-12 col-xxl-12 ml-3 row"> 
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="historic-tab" data-toggle="tab" data-target="#historic" type="button" role="tab" aria-controls="historic" aria-selected="false">Historico</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="management-tab" data-toggle="tab" data-target="#management" type="button" role="tab" aria-controls="management" aria-selected="true">Automação</button>
                </li>
            </ul>
        </div>
        
        <div class="col-lg-12">
            <div class="card">
                <div class="tab-content" id="myTabContent"> 
                    <div class="tab-pane fade show active" id="historic" role="tabpanel" aria-labelledby="historic-tab">
                        <div class="card-header align-items-center justify-content-end">
                            <button type="button" class="btn btn-rounded btn-outline-success mr-2 shoot-notification" data-toggle="modal" data-target="#ShootNotificationModal" aria-expanded="true">
                                <i class="fa fa-bell scale2 mr-2"></i> Disparar Notificação
                            </button>                           
                        </div>
                        <div class="card-body">

                            <div class="table-responsive">
                                <table id="assinantes" class="table card-table display dataTablesCard table-sm" data-page-length='25' data-order='[[0, "desc"]]'>
                                    <thead>    
                                        <tr>
                                            <th>#</th>                                    
                                            <th>Usuário</th> 
                                            <th class="text-center">Ícone</th>
                                            <th>Texto</th>                                    
                                            <th class="text-center">Visto</th>   
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = $get_notification_list->fetch()) { ?>
                                            <tr> 
                                                <td><?php echo date_format(date_create($row['created_at']), 'd/m/y - H:i') ?></td>
                                                <td><?php echo $row['user__id'] ? $row['full_name'] : 'Todos' ?></td>
                                                <td class="text-center"><i class="<?php echo $row['notification_icon'] ?> fa-2x text-primary"></i></td>  
                                                <td>
                                                    <?php if (strlen($row['notification_context']) > 30) {
                                                        echo substr($row['notification_context'], 0, 30) . "...";
                                                    } else {
                                                        echo $row['notification_context'];
                                                    } ?>
                                                </td> 
                                                <td class="<?php echo $row['notification_read_date'] ? 'text-left' : 'text-center' ?>">
                                                    <?php echo $row['notification_read_date'] ? date_format(date_create($row['user__id']), 'd/m/y - H:i') : '-' ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="management" role="tabpanel" aria-labelledby="management-tab">
                        <div class="card-header align-items-center justify-content-end">
                            <a type="button" class="btn btn-rounded btn-outline-success mr-2 new-notification" data-toggle="modal" data-target="#AddNotificationModal" aria-expanded="true">
                                <i class="fa fa-bell scale2 mr-2"></i> Adicionar Automação
                            </a>
                            <!-- <button type="button" class="btn btn-rounded btn-outline-success mr-2" data-toggle="dropdown" aria-expanded="true"><i class="fas fa-download scale2 mr-2"></i> Nova Notificação</button> -->
                        </div>
                        <div class="card-body">
                
                            <div class="table-responsive">
                                <table class="table card-table display dataTablesCard table-sm" data-page-length='10' data-order='[[0, "asc"]]'>
                                    <thead>    
                                        <tr>                           
                                            <th>Ação</th>
                                            <th class="text-center">Mensagem</th> 
                                            <th class="text-center">Status</th>                
                                            <th class="text-center">#</th>  
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = $get_shooting_notification_list->fetch()) { ?>
                                            <tr>
                                                <td>
                                                    <?php if (strlen($row['shooting_message']) > 30) {
                                                        echo substr($row['shooting_message'], 0, 30) . "...";
                                                    } else {
                                                        echo $row['shooting_message'];
                                                    } ?>
                                                </td> 
                                                <td class="text-center">
                                                    <?php 
                                                        switch($row['shooting_action']){
                                                            case 1: {
                                                                echo 'Pedido Agendado';
                                                                break;
                                                            }
                                                            case 2: {
                                                                echo 'Pedido Completo';
                                                                break;
                                                            }
                                                        }
                                                    ?>
                                                    
                                                </td>
                                                <td class="text-center">
                                                    <?= $row['shooting_status'] == 1 ? '<span class="badge badge-sm light badge-success"><i class="far fa-clock"></i> Ativo</span>' : '<span class="badge badge-sm light badge-danger"><i class="fa-ban fa"></i> Desabilitado</span>' ?>
                                                </td>
                                                <td class="text-center"> 
                                                    <a type="button" class="btn btn-danger btn-xs sharp delete-shooting" data-id="<?= $row['id_shooting'] ?>">
                                                        <i class="fa fa-trash"></i> 
                                                    </a>
                                                    <!--<a type="button" class="btn btn-success btn-sm sharp"><i class="fa fa-pen"></i></a>-->
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>                
                </div>
            </div>
        </div> 
    </div>


    <div class="modal fade" id="ShootNotificationModal" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar uma notificação manual</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <i class="fa fa-times" aria-hidden="false"></i>
                    </button>
                </div>
                <form id="ShootNotificationForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="Shoot-notification"> 
                        <div class="form-group">
                            <label for="usuarios" class="form-label">Usuários</label>
                            <select id="select-users-id" class="default-select form-control d-block" multiple="multiple" data-live-search="true">                             
                                <option value="" selected>Todos</option>
                                <?php foreach ($users as $user) { ?> 
                                    <option value="<?php echo $user['user__id']; ?>">
                                        <?php if (strlen($user['full_name']) > 25) {
                                            echo substr($user['full_name'], 0, 25) . "...";
                                        } else {
                                            echo $user['full_name'];
                                        } ?>
                                    </option>
                                <?php } ?>  
                            </select>
                            <input type="hidden" id="text-select-users-id" name="usuarios" value="" required>
                        </div>   
                        
                        <div class="form-group">
                            <label for="icon" class="form-label">Ícone</label>
                            <input type="text" class="form-control" name="icon" id="icon">
                        </div> 
                        
                        <div class="form-group">
                            <label for="texto" class="form-label">Texto</label>
                            <textarea class="form-control" name="texto" id="texto" rows="3"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <p class="form-label">Programar Disparo: </p>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="imediato" name="tipoproramacao" class="custom-control-input"  value="imediato">
                                <label class="custom-control-label" for="imediato">Imediato</label>
                            </div>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="programado" name="tipoproramacao" class="custom-control-input"  value="programado">
                                <label class="custom-control-label" for="programado">Programado</label>
                            </div>
                        </div>
                        
                        <div id="components-date" class="d-none">
                            <div class="row">
                                <div class="form-group col-6">
                                    <label for="datepicker">Data da conclusão</label>
                                    <input name="data" placeholder=".. ao dia" require class="datepicker-default form-control picker__input mb-2" id="datepicker" readonly="" aria-haspopup="true" aria-expanded="false" aria-readonly="false" aria-owns="datepicker_root">
                                    <div class="picker" id="datepicker_root" aria-hidden="true">
                                        <div class="picker__holder" tabindex="-1">
                                            <div class="picker__frame">
                                                <div class="picker__wrap">
                                                    <div class="picker__box">
                                                        <div class="picker__header">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-6">
                                    <label for="datepicker">Hora da conclusão</label>
                                    <input type="time" name="hours" placeholder="horas" require class="form-control mb-2">
                                </div>
                            </div>
                        </div>
    
                        <div class="form-group">
                            <label for="link" class="form-label">Link de redirecionamento</label>
                            <input type="url" class="form-control" name="link" id="link"> 
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success btn-lg btn-block mt-4">Enviar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="AddNotificationModal" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar uma notificação manual</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <i class="fa fa-times" aria-hidden="false"></i>
                    </button>
                </div>
                <form id="NewNotificationForm">
                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" name="action" value="new-notification"> 
                            <div class="form-group col-md-12 mb-3">
                                <label class="text-label">Ação:</label>
                                <select id="select-action-notification" name="acao" class="d-block default-select" required>
                                    <option disabled selected hidden>Selecione a Ação</option>                                                
                                    <option value="1">Pedido Agendado</option>
                                    <option value="2">Pedido Completo</option> 
                                </select>
                            </div>

                            <div class="form-group col-md-12 mb-3">
                                <label class="text-label">Mensagem:</label>
                                <textarea id="text-message-notification" name="mensagem" class="form-control" rows="3" required></textarea>
                            </div>
                            <div class="form-group col-md-12">
                                <label class="text-label">Status:</label>
                                <select id="select-status-notification" name="status" class="d-block default-select" required>
                                    <option disabled selected>Selecione o Status</option>                                                
                                    <option value="2">Ativo</option>
                                    <option value="1">Inativo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success btn-lg btn-block">Criar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
<?php
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>

<script>
    $(document).ready(function() {

        $('.shoot-notification').on('click', function() {
            $('#ShootNotificationForm').trigger("reset");
            $("#usuarios").selectpicker("refresh");  
        });

        $('.new-notification').on('click', function() {
            $('#NewNotificationForm').trigger("reset");
            $(".default-select").selectpicker("refresh");  
        });

        $("#select-users-id").change(function () {
            var multipleValues = $("#select-users-id").val();
            $("#text-select-users-id").val(multipleValues); 
        }); 

        $('#ShootNotificationForm').submit(function() {    
            let ShootNotificationForm = document.getElementById('ShootNotificationForm');

            // Instância o FormData passando como parâmetro o formulário
            let formData = new FormData(ShootNotificationForm);

            let url = u + "/ajax/new-notification.php"; 

            $.ajax({
                url: url,
                type: "POST",
                data: formData,
                dataType: "json",
                processData: false,
                contentType: false,
                beforeSend: function(){
                    display_loader();
                    $('#ShootNotificationModal').modal("toggle"); 
                } ,
                complete: function() {
                    display_loader(false);
                },
                success: function (feedback) {
                    Swal.fire({
                        title: feedback.title,
                        text: feedback.msg,
                        icon: feedback.type,
                    }).then((value) => {
                        if (feedback.type == "success") {
                            document.location.reload(true);
                        } else { 
                            $('#ShootNotificationModal').modal("toggle"); 
                        }
                    });
                },
            }).fail(function (data) {
                Swal.fire({
                    title: "Erro de Conexão",
                    text: "Não foi possível disparar a notificação. atualize a página e tente novamente!",
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonText: 'Atualizar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                    customClass: {
                        confirmButton: 'ml-2 btn btn-success',  
                        cancelButton: 'btn btn-danger',
                    },                    
                    buttonsStyling: false
                }).then((value) => {
                    document.location.reload(true);
                });
            });
            
            return false;
        });

        $('#NewNotificationForm').submit(function() {    
            let NewNotificationForm = document.getElementById('NewNotificationForm');

            // Instância o FormData passando como parâmetro o formulário
            let formData = new FormData(NewNotificationForm);

            let url = u + "/ajax/new-shooting-notification.php"; 

            $.ajax({
                url: url,
                type: "POST",
                data: formData,
                dataType: "json",
                processData: false,
                contentType: false,
                beforeSend: function(){
                    display_loader();
                    $('#AddNotificationModal').modal("toggle"); 
                } ,
                complete: function() {
                    display_loader(false);
                },
                success: function (feedback) {
                    Swal.fire({
                        title: feedback.title,
                        text: feedback.msg,
                        icon: feedback.type,
                    }).then((value) => {
                        if (feedback.type == "success") {
                            document.location.reload(true);
                        } else { 
                            $('#AddNotificationModal').modal("toggle"); 
                        }
                    });
                },
            }).fail(function (data) {
                Swal.fire({
                    title: "Erro de Conexão",
                    text: "Não foi possível criar o notificação. atualize a página e tente novamente!",
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonText: 'Atualizar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                    customClass: {
                        confirmButton: 'ml-2 btn btn-success',  
                        cancelButton: 'btn btn-danger',
                    },                    
                    buttonsStyling: false
                }).then((value) => {
                    document.location.reload(true);
                });
            });
            
            return false;
        });
        
        
        $('.delete-shooting').click(function() {
            
            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success ml-2',
                    cancelButton: 'btn btn-danger'
                },
                buttonsStyling: false
            })
            
            swalWithBootstrapButtons.fire({
                title: 'Deletar Disparo',
                text: "Você tem certeza que deseja deletar esse disparo ?",
                icon: 'warning',
                showCancelButton: true,
                    confirmButtonText: 'Deletar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    let url = u + "/ajax/new-shooting-notification.php";  
                    
                    let formData = new FormData();
                    formData.append('id_shooting', $(this).data('id'));
                    formData.append('action', 'delete-notification');
                    
                    $.ajax({
                        url: url,
                        type: "POST",
                        data: formData,
                        dataType: "json",
                        processData: false,
                        contentType: false,
                        beforeSend: function(){
                            display_loader();
                        } ,
                        complete: function() {
                            display_loader(false);
                        },
                        success: function (feedback) {
                            Swal.fire({
                                title: feedback.title,
                                text: feedback.msg,
                                icon: feedback.type,
                            }).then((value) => {
                                if (feedback.type == "success") {
                                    document.location.reload(true);
                                } else { 
                                }
                            });
                        },
                    }).fail(function (data) {
                        Swal.fire({
                            title: "Erro de Conexão",
                            text: "Não foi possível deletar a automação. atualize a página e tente novamente!",
                            icon: 'error',
                            showCancelButton: true,
                            confirmButtonText: 'Atualizar',
                            cancelButtonText: 'Cancelar',
                            reverseButtons: true,
                            customClass: {
                                confirmButton: 'ml-2 btn btn-success',  
                                cancelButton: 'btn btn-danger',
                            },                    
                            buttonsStyling: false
                        }).then((value) => {
                            document.location.reload(true);
                        });
                    });
                }
            });
        });

        //ATIVA INPUT COBRAR POR FRETE
        $('.custom-radio').on('click', function() {
            if ($('#programado').is(":checked") === true) {
                $('#components-date').removeClass('d-none'); 
            } else {
                $('#components-date').addClass('d-none');
            }
        });

    });
</script>