<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

$user__id =$_SESSION['UserID'];

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
}

$page_title = "Listagem de Contas Bancárias | Logzz";
$shop_page = true;
$profile_page = true;
$select_datatable_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

?>
<style>
    .overflow-drop {
        overflow-y: scroll;
    }

    .overflow-drop::-webkit-scrollbar {
        background-color: #EEEEEE;
        width: 7px;
        height: 7px;
    }

    .overflow-drop::-webkit-scrollbar-track {
        border-radius: 40px;
        background-color: #F5F5F5;
    }

    .overflow-drop::-webkit-scrollbar-thumb {
        border-radius: 40px;
        background-color: #2fde91;
    }
</style>

    <div class="container-fluid">
        <!-- row -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Todas as Contas Bancárias</h4>
                        <div class="d-flex mb-3">
                            <button type="button" class="btn btn-rounded btn-success filter-btn" class="btn btn-success text-nowrap">
                                <i class="fas fa-sliders-h scale2 mr-2" aria-hidden="true"></i>Filtros
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="overflow-drop" style="overflow-y: auto;">
                            <table id="all-accounts-list-dt" class="table card-table display dataTablesCard" data-page-length='25' data-order='[[8, "asc"]]'>
                                <thead>
                                </thead>
                                <tbody id="customers"></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="chatbox">
        <div class="chatbox-close"></div>
        <div class="col-xl-12">
            <div class="card">
                <div class="mt-4 center text-center ">
                    <h4 class="card-title">Filtros</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12 mb-2">
                            <form id="filter-form">
                                <div class="mb-3">
                                    <div class="form-group">
                                        <label class="text-label">por Usuário</label>
                                        <input type="text" class="form-control mb-2" name="usuario" value="" placeholder="Busca por nome">
                                    </div>
                                    <div class="form-group">
                                        <label class="text-label">por Banco</label>
                                        <input type="text" class="form-control mb-2" name="banco" value="" placeholder="Busca por banco">
                                    </div>
                                    <div class="form-group">
                                        <label class="text-label">por Chave Pix</label>
                                        <input type="text" class="form-control mb-2" name="chavepix" value="" placeholder="Busca por chave pix">
                                    </div>
                                    <div class="form-group">
                                        <label class="text-label">por Status</label>
                                        <select class="form-control default-select" id="select-filter-status-id">
                                            <option selected value="">Todos os status</option>
                                            <option value="3">Aprovada</option>
                                            <option value="2">Pendente</option>
                                            <option value="1">Recusado</option>
                                        </select>
                                        <input type="hidden" id="text-filter-status-id" name="status" value="" required>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-block btn-success text-nowrap mt-2"><i class="fas fa-sliders-h" aria-hidden="true"></i> Aplicar Filtros</button>
                                <a href="<?php echo SERVER_URI; ?>/usuarios/contas-bancarias/" class="btn btn-block mt-2">Limpar Filtros</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ModalReprovarConta" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header center text-center d-block">
                <h5 class="modal-title center text-center">Reprovar Conta Bancária</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-12 m-auto">
                                <p class="mb-3 h4 font-weight-thin d-block text-center" style="width: 100%;">Justificativa</p>
                                <textarea id="justification-textarea" class="form-control" id="justificativa" placeholder="Informe ao usuário o motivo da reprovação..." required=""></textarea>
                            </div>
                        </div>
                        <a class="btn btn-success btn-block mt-2 mb-4" id="disapprove-bank-account-submit" data-id=""><i class="fas fa-times"></i> Reprovar Conta bancária</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>

<script>
/* AJAX */
$(document).ready(function($) {
    var dataTable;
    dataTable = $('#all-accounts-list-dt').DataTable({
        searching: false,
        paging: false,
        processing: true,
        select: true,
        lengthChange: true,
        'serverSide': true,
        "processing": true,
        'serverMethod': 'post',
        "ajax": {
            url: u + '/ajax/list-datatable-ajax/account-list-ajax.php',
            type: "POST",
            dataType: "JSON",
            data: {
                filter_data: function() { return $('#filter-form').serialize(); },
            }
        },
        paging: true,
        "columns": [
            {   data: "date",
                class: "center text-center col-1",
                title: "Solicitação"
            },
            {   data: "user",
                class: "center text-center col-2",
                title: "Usuário"
            },
            {   data: "bank",
                class: "center text-center col-1",
                title: "Banco"
            },
            {   data: "agency",
                class: "center text-center col-1",
                title: "Agência"
            }, 
            {   data: "account",
                class: "center text-center col-1",
                title: "Conta"
            },
            {   data: "type",
                class: "center text-center col-1",
                title: "Tipo"
            },
            {   data: "keypix",
                class: "center text-center col-2",
                title: "Chave PIX"
            },
            {   data: "document",
                class: "center text-center col-1",
                title: "Doc."
            },
            {   data: "status",
                class: "center text-center col-1",
                title: "Status"
            },
        ],
        createdRow: function(row, data, dataIndex) {
            if (data.active == 0) {
                $(row).addClass("table-active");
            }
        },
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "language": {
            "processing": "Atualizando listagem...",
            "zeroRecords": "Sem resultados para sua busca",
            "info": "Página _PAGE_ de _PAGES_",
            "infoFiltered": "(filtrando de _MAX_ usúarios, no total.)",
            "paginate": {
                "next": "Próximo",
                "previous": "Anterior"
            },
            "infoFiltered": "(filtrando de _MAX_ usúarios, no total.)",
        }
    });

    $('#filter-form').submit(function(e) {
        e.preventDefault();
        dataTable.ajax.reload();
        $(".chatbox").removeClass('active');
    });
});
function approveBankAccount(element) {
        event.preventDefault();

        var id = element.getAttribute('data-id');
        var action = element.getAttribute('data-action');
        var url = u + "/ajax/change-bank-account-status.php";

        var btnID = "#" + element.getAttribute('data-btn-id');

        $.ajax({
            url: url,
            type: "GET",
            data: { action, id},
            dataType: 'json',
            processData: true,
            contentType: false,
            success: function (feedback) {
                toastr.success(feedback.msg, feedback.title, {
                    timeOut: 1500,
                    closeButton: !0,
                    debug: !1,
                    newestOnTop: !0,
                    progressBar: !0,
                    positionClass: "toast-top-right",
                    preventDuplicates: !0,
                    onclick: null,
                    showDuration: "400",
                    hideDuration: "1000",
                    extendedTimeOut: "1000",
                    showEasing: "swing",
                    hideEasing: "linear",
                    showMethod: "fadeIn",
                    hideMethod: "fadeOut",
                    tapToDismiss: !0
                });
            }
        }).then(() => {
            $(btnID).html('');
            $(btnID).removeClass();
            $(btnID).attr('class', 'btn btn-success dropdown-toggle btn-xs');
            $(btnID).html('Aprovada');
        });
        
    };

    function disapproveBankAccount(element) {
        event.preventDefault();
        var id = element.getAttribute('data-id');
        $('#disapprove-bank-account-submit').removeAttr('data-id');
        $('#disapprove-bank-account-submit').attr('data-id', id);
    };

</script>