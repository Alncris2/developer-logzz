<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID'])) || $_SESSION['UserPlan'] < 5) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}

$page_title = "Conquistas | Logzz";
$subscriber_page = true;
$select_datatable_page = true; 
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

?>
<style>
    #filtersList {
        overflow-y: scroll;
    }

    #filtersList::-webkit-scrollbar {
        background-color: #EEEEEE;
        width: 7px;
        height: 7px;
    }

    #filtersList::-webkit-scrollbar-track {
        border-radius: 40px;
        background-color: #F5F5F5;
    }

    #filtersList::-webkit-scrollbar-thumb {
        border-radius: 40px;
        background-color: #2fde91;
    }  

    .chatbox {
        width: 430px;
    }

    .chatbox .chatbox-close {
        right: 430px;
    }
    
    
    @media only screen and (max-width: 576px) {
        .chatbox {
            width: 100vw;
        }
    }

</style>
<div class="container-fluid">
    <!-- row -->
    <div class="d-flex flex-wrap mb-2 align-items-center justify-content-between">
        <div class="mb-3 mr-3">            
            <h6 id="result" class="fs-16 text-black font-w600 mb-0"></h6>   
        </div>
        <div class="event-tabs mb-3 mr-3">
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Todos os Usuários</h4>
                    <div class="d-flex mb-3">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-rounded btn-outline-success mr-2" data-toggle="dropdown" aria-expanded="true"><i class="fas fa-download scale2 mr-2"></i> Exportar</button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#" id="export-to-csv"><i class="fas fa-file-csv scale2 mr-2"></i></i> CSV</a>
                                <a class="dropdown-item" href="#" id="export-to-xlsx"><i class="fas fa-file-excel scale2 mr-2"></i> Excel</a>
                                <a class="dropdown-item" href="#" id="export-to-pdf"><i class="fas fa-file-pdf scale2 mr-2"></i> PDF</a>
                            </div>
                        </div>
                        <button type="button" class="btn btn-rounded btn-success filter-btn" class="btn btn-success text-nowrap"><i class="fas fa-sliders-h scale2 mr-2" aria-hidden="true"></i>Filtros</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive"> 
                        <table id="conquest-list" class="table card-table display dataTablesCard table-sm" data-page-length='25' data-order='[[0, "asc"]]'>
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Usuário [Cód.]</th>
                                    <th>Comissão R$</th> 
                                    <th>Última venda</th>
                                    <th>Nível</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <div class="card-footer">

                </div>
            </div>
        </div>
    </div>

    <div class="chatbox"> 
        <div class="chatbox-close"></div>
        <div class="col-xl-12" style="height: 100vh;">
            <div class="card">
                <div class="mt-4 center text-center ">
                    <h4 class="card-title">Filtros</h4>
                </div> 
                <div class="card-body" id="filtersList">
                    <div class="row">
                        <div class="col-lg-12 mb-2">
                            <form id="filter-form">
                                <div class="mb-3">
                                    <div class="form-group">
                                        <label>por Comissão</label>
                                        <input name="comissao-min" value="<?php echo @addslashes($_GET['data-inicio']); ?>" placeholder="de X ..." class="form-control money mb-2" id="comissao-min">
                                        <input name="comissao-max" value="<?php echo @addslashes($_GET['data-final']); ?>" placeholder=".. até Y" class="form-control money" id="comissao-max">
                                    </div>
                                     

                                    <div class="form-group">
                                        <label>por Última venda </label>
                                        <input name="data-inicio" value="<?php echo @addslashes($_GET['data-inicio']); ?>" placeholder="Do dia ..." class="datepicker-default form-control picker__input mb-2" id="datepicker" readonly="" aria-haspopup="true" aria-expanded="false" aria-readonly="false" aria-owns="datepicker_root">
                                        <input name="data-final" value="<?php echo @addslashes($_GET['data-final']); ?>" placeholder=".. ao dia" class="datepicker-default form-control picker__input" id="datepicker" readonly="" aria-haspopup="true" aria-expanded="false" aria-readonly="false" aria-owns="datepicker_root">

                                        <div class="picker" id="datepicker_root" aria-hidden="true">
                                            <div class="picker__holder" tabindex="-1">
                                                <div class="picker__frame">
                                                    <div class="picker__wrap">
                                                        <div class="picker__box">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div> 
                                    </div>


                                    <div class="form-group">
                                        <label for="">por Nível</label> 
                                        <select class="form-control default-select" id="select-nivel-assinante">
                                            <option selected value="">Todos</option>  
                                            <option value="1">Sem Nível</option>
                                            <option value="2">Bronze</option>
                                            <option value="3">Silver</option>
                                            <option value="4">Gold</option>
                                            <option value="5">Diamond</option>
                                            <option value="6">Black</option>
                                            <option value="7">Hero</option>
                                            <option value="8">Legend</option>
                                            <option value="9">Bronze</option>
                                        </select>
                                        <input type="hidden" id="text-nivel-assinante" name="level" value="" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="">por Nome</label>
                                        <input type="text" class="form-control mb-2" name="usuario" placeholder="Nome do Usuário">
                                    </div>

                                    <div class="form-group">
                                        <label for="">por Código</label>
                                        <input type="text" class="form-control mb-2" name="codigo" placeholder="Código do Usuário">
                                    </div>
                                </div> 
                                <button type="submit" class="btn btn-block btn-success text-nowrap mt-2"><i class="fas fa-sliders-h" aria-hidden="true"></i> Aplicar Filtros</button>
                                <a href="<?= SERVER_URI . '/conquistas/' ?>" class="btn btn-block mt-2">Limpar Filtros</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>

<script>
    $(document).ready(function ($) {
    var u = location.protocol + "//" + window.location.hostname;
    var dataTable;

    $("#select-nivel-assinante").change(function () {
        var multipleValues = $("#select-nivel-assinante").val();
        $("#text-nivel-assinante").val(multipleValues);
    });

    dataTable = $("#conquest-list").DataTable({    
        searching: false,
        paging: true,
        processing: true,
        select: true,
        lengthChange: true,
        dom: "Bfrtip",
        buttons: [
            {
                extend: "csv",
                text: "csv",
                titleAttr: "CSV",
                filename: "Conquistas - Logzz",
                action: newexportaction,
                bom: true,
                charset: "utf-8", 
            },
            {
                extend: "excel",
                text: "excel",
                titleAttr: "XLS",
                className: "ExcelExport",
                filename: "Conquistas - Logzz",
                title: "Conquistas - Logzz",
                action: newexportaction,
                exportOptions: {
                    modifier: {
                        order: "applied",
                        page: "all",
                    },
                },
            },
            {
                extend: "pdf",
                orientation: "landscape",
                pageSize: "A4",
                text: "pdf",
                titleAttr: "PDF",
                title: "Usuários - Logzz",
                action: newexportaction,
                exportOptions: {
                    modifier: {
                        search: "applied",
                        order: "applied",
                        page: "all",
                    },
                },
            },
        ],
        serverSide: true,
        processing: true,
        serverMethod: "post", 
        ajax: {
            url: u + "/ajax/list-datatable-ajax/conquest-list-ajax.php",
            type: "POST",
            dataType: "JSON",
            data: {
                filter_data: function () {
                    return $("#filter-form").serialize();
                },
            },              
            complete: function (data) {
                
                total = data['responseJSON'];
                if (total.filter) { 
                    $("#result").html('Exibindo ' + total.recordsFiltered + ' usuários de acordo com seus filtros. <br> Comissões com o filtro ' + total.total);
                } else {
                    if (total.recordsFiltered == 0) {
                        $("#no-user").removeClass('d-none');
                        $("#no-user").addClass('d-block'); 
                    } else {
                        $("#no-user").removeClass('d-block');
                        $("#no-user").addClass('d-none');
                    } 
                    $("#result").html(total.recordsFiltered + ' usuários no total. <br> Total de comissões ' + total.total);
                }  
                display_loader(false);
            },
        },
        paging: true,
        columns: [
            { data: "date",         class: 'text-center'    }, //data da criação
            { data: "name",         class: ''               }, //nome usuario
            { data: "comission",    class: 'text-center'    }, //comissão usuario
            { data: "last_deal",    class: 'text-center'    }, //Ultima venda usuario
            { data: "level_user",   class: 'text-center'    }, //Nível usuario    
        ],
        ordering: true,
        info: true,
        autoWidth: false,
        responsive: true,
        language: {
            processing: "Atualizando listagem...",
            zeroRecords: "Sem resultados para sua busca",
            info: "Página _PAGE_ de _PAGES_",
            infoFiltered: "(filtrando de _MAX_ usúarios, no total.)",
            paginate: {
                next: "Próximo",
                previous: "Anterior",
            },
            infoFiltered: "(filtrando de _MAX_ usúarios, no total.)",
        },
    });

    $("#filter-form").submit(function (e) {
        e.preventDefault();
        display_loader(true);
        dataTable.ajax.reload();
        $(".chatbox").removeClass("active");
    });
});

</script>