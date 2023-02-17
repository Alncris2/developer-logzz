<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');

session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}

if ($_SESSION['UserPlan'] != 6) {
    header('Location: ' . SERVER_URI . '/pedidos/lista');
    exit;
}

$page_title = "Pedidos | Logzz";
$sidebar_expanded = false;
$orders_operator_page = $select_datatable_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

//Busca o nível do usuário com base no ID
$user__id = $_SESSION['UserID'];


# Busca os produtos dos pedidos
$get_product_list = $conn->prepare('SELECT DISTINCT products.* FROM products INNER JOIN orders ON products.product_id = orders.product_id INNER JOIN local_operations_orders ON local_operations_orders.order_id = orders.order_id WHERE product_trash = 0 AND local_operations_orders.operation_id = :operation_id');
$get_product_list->execute(array('operation_id' => $operation_id));

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

    .btn.option {
        padding: 1rem 1rem;
        border-radius: 1.25rem;
        font-weight: 500;
        font-size: .8rem;
        display: flex;
        color: #495057;
        align-items: center;
        justify-content: center;
    }

    .input-group input {
        width: 47%; 
        border: 0;
    } 
    .input-group button {
        width: 53%;
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

    .picker__footer {
        display: flex
    }

</style>
<div class="container-fluid" style="overflow-x: hidden;">
    <div id="no-orders" class="alert alert-success solid fade show mb-3 d-none">
        <i class="fa fa-info-circle" aria-hidden="true"></i><strong> Você ainda não fez a sua primeira venda.</strong> Assim que ela acontecer, todos os dados aparecerão aqui.
    </div>
    <div class="d-flex flex-wrap mb-2 align-items-center justify-content-between">
        <div class="mb-3 mr-3">            
            <h6 id="result" class="fs-16 text-black font-w600 mb-0"></h6>
            <span class="fs-14"><b>Operador Logístico</b></span>
        </div>
        <div class="event-tabs mb-3 mr-3">
        </div>
        <div class="d-flex mb-3">
            <button type="button" class="btn btn-rounded btn-outline-success mr-2" data-toggle="dropdown" aria-expanded="true">
                <i class="fa fa-truck-moving scale2 mr-2" aria-hidden="true"></i>&nbsp; Expedir  
            </button>
            <div class="dropdown-menu">
                <a class="dropdown-item export-orders-truck" href="<?= SERVER_URI .'/pedidos/expedicao-local-a/' ?>" target="_blanck">
                    A4
                </a>
                <a class="dropdown-item export-orders-truck" href="<?= SERVER_URI .'/pedidos/expedicao-local-t/' ?>" target="_blanck">
                    Térmica 
                </a>
            </div>
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

    <div class="row ">
        <div class="col-xl-12" style="padding-left: 5px;">
            <div class="tab-content">
                <div id="All" class="tab-pane active fade show">
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table id="orders-list-operator-dt" class="table card-table display dataTablesCard" data-page-length='50' data-order='[[0, "desc"]]'>
                            <thead>
                                <tr>
                                    
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalConfirmResponsability" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="min-width: 700px;" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assumir responsabilidade por pedido</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h4>Gostaria de assumir responsabildade pelo pedido?</h4>
                    <p>O pedido será atribuido a você, e não ficará disponível a outros operadores.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger light" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="confirm-resp-button">Assumir Responsabilidade</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="chatbox">
        <div class="chatbox-close"></div>
        <div class="col-xl-12">
            <div class="card" id="filters" style="overflow-y: scroll;height: 100vh;">
                <div class="mt-4 center text-center ">
                    <h4 class="card-title">Filtros</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12 mb-2">
                            <form id="filter-form">
                                <div class="mb-3">
                                    <p class="mb-1"><small>por Data</small></p>
                                    <input type="hidden" class="form-control mb-2" name="filtro" value="operador">
                                    
                                    <div class="input-group form-control mb-2">
                                        <input name="data-inicio" value="<?php echo @addslashes($_GET['data-inicio']); ?>" placeholder="Do dia ..." class="datepicker-default picker__input" id="datepicker" readonly="" aria-haspopup="true" aria-expanded="false" aria-readonly="false" aria-owns="datepicker_root">
                                        <button type="button" disabled class="btn btn-outline-dark btn-sm modify-time px-1 h-100"">Apartir de 
                                            <input type="time" class="bg-transparent border-0" name="time-inicio" id="time-inicio" value="00:00">
                                        </button>
                                    </div>

                                   <div class="input-group form-control mb-2"> 
                                        <input name="data-final" value="<?php echo @addslashes($_GET['data-final']); ?>" placeholder=".. ao dia" class="datepicker-default picker__input" id="datepicker" readonly="" aria-haspopup="true" aria-expanded="false" aria-readonly="false" aria-owns="datepicker_root">
                                        <button type="button" disabled class="btn btn-outline-dark btn-sm modify-time px-1 h-100">Até de 
                                            <input type="time" class="bg-transparent border-0" name="time-final" id="time-final" value="23:59">
                                        </button>                                        
                                    </div>

                                    <div class="form-group">
                                        <select class="segment-select" name="reference-data" id="reference-data">
                                            <option value="agendamento">Pedido</option>
                                            <option value="entrega">Entrega</option>
                                            <option value="reembolso">Reemb.</option>
                                        </select>
                                    </div>
                                    
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
                                    <p class="mb-1"><small>por Cliente</small></p>
                                    <input type="text" class="form-control mb-2" name="nome-cliente" value="<?php echo @addslashes($_GET['nome-cliente']); ?>" placeholder="Nome do Cliente">
                                    
                                    <div class="form-group">
                                        <label class="text-label">por Produto</label>
                                        <select id="select-ship-product" class="d-block default-select" multiple="multiple" data-live-search="true">
                                            <option value="" selected>Todos</option>
                                            <?php
                                            while ($prodcut = $get_product_list->fetch()) {
                                            ?>
                                                <option value="<?php echo $prodcut['product_name']; ?>">
                                                    <?php if (strlen($prodcut['product_name']) > 25) {
                                                        echo substr($prodcut['product_name'], 0, 25) . "...";
                                                    } else {
                                                        echo $prodcut['product_name'];
                                                    } ?>
                                                </option>
                                            <?php
                                            }
                                            ?>
                                        </select>
                                        <input type="hidden" id="text-ship-product" name="produto" value="<?php echo @addslashes($_GET['produto']); ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label class="mb-1">por Status</label>
                                        <select class="form-control default-select" id="select-filter-status-id" multiple="multiple">
                                            <option value="" selected>Todos</option>
                                            <option value="1" <?php if (!(empty(@$_GET['status'])) && $_GET['status'] == 1) echo "selected" ?>>Agendada</option>
                                            <option value="3" <?php if (!(empty(@$_GET['status'])) && $_GET['status'] == 3) echo "selected" ?>>Atrasada</option>
                                            <option value="6" <?php if (!(empty(@$_GET['status'])) && $_GET['status'] == 6) echo "selected" ?>>Cancelada</option>
                                            <option value="5" <?php if (!(empty(@$_GET['status'])) && $_GET['status'] == 5) echo "selected" ?>>Frustrada</option>
                                            <option value="4" <?php if (!(empty(@$_GET['status'])) && $_GET['status'] == 4) echo "selected" ?>>Completa</option>
                                            <option value="2" <?php if (!(empty(@$_GET['status'])) && $_GET['status'] == 2) echo "selected" ?>>Reagendada</option>
                                            <option value="10" <?php if (!(empty(@$_GET['status'])) && $_GET['status'] == 10) echo "selected" ?>>Reembolsado</option>
                                            <option value="11" <?php if (!(empty(@$_GET['status'])) && $_GET['status'] == 11) echo "selected" ?>>Confirmado</option>
                                            <option value="12" <?php if (!(empty(@$_GET['status'])) && $_GET['status'] == 12) echo "selected" ?>>Em Aberto</option> 
                                            <option value="13" <?php if (!(empty(@$_GET['status'])) && $_GET['status'] == 13) echo "selected" ?>>Indisponível</option>
                                        </select>
                                        <input type="hidden" id="text-filter-status-id" name="status" value="" required>
                                    </div>

                                    <p class="mb-1 mt-2"><small>por WhatsApp</small></p>
                                    <input type="text" class="form-control mb-2" name="numero-cliente-produto" value="<?php echo @addslashes($_GET['numero-cliente-produto']); ?>" placeholder="Número do Cliente">

                                    <p class="mb-1"><small>por Responsavel</small></p>
                                    <select class="form-control default-select" id="select-filter-resp-id">
                                        <option selected value="">Todos</option>
                                        <option value="voce">Você</option>
                                        <option value="indef">Indefinido</option>
                                    </select>
                                    <input type="hidden" id="text-filter-resp-id" name="responsavel" value="">

                                </div>
                                <button type="submit" class="btn btn-block btn-success text-nowrap mt-2"><i class="fas fa-sliders-h" aria-hidden="true"></i> Aplicar Filtros</button>
                                <a href="<?php echo SERVER_URI; ?>/pedidos/" class="btn btn-block mt-2">Limpar Filtros</a>
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

<div class="modal fade" id="unavailableOrderModal" tabindex="-1" aria-labelledby="unavailableOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="unavailableForm" class="modal-content">
            <div class="modal-header text-center">
                <h5 class="modal-title w-100 text-center" id="unavailableOrderModalLabel">Justificativa de Indisponibilidade</h5>
            </div>

            <div class="modal-body">
                <input type="hidden" id="unavailable_status" name="status">
                <input type="hidden" id="unavailable_id" name="id">

                <div class="form-check mb-2">
                    <input class="form-check-input mt-2" type="radio" name="status_description" id="statusDescription1" value="Entregador responsável pelo pedido não pôde realizar" required>
                    <label class="form-check-label" for="unavailableDescription1">
                        Entregador responsável pelo pedido não pôde realizar
                    </label>
                </div>

                <div class="form-check mb-2">
                    <input class="form-check-input mt-2" type="radio" name="status_description" id="statusDescription2" value="Atraso na reposição de estoque">
                    <label class="form-check-label" for="unavailableDescription2">
                        Atraso na reposição de estoque
                    </label>
                </div> 

                <div class="form-check mb-2">
                    <input class="form-check-input mt-2" type="radio" name="status_description" id="statusDescription6" value="0">
                    <label class="form-check-label" for="unavailableDescription6">
                        Outro
                    </label>
                </div>

                <div class="form-group">
                    <input type="text" id="unavailableDescription" class="form-control" name="other_description" maxlength="150" style="display: none;">
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Voltar</button>
                <button type="submit" class="btn btn-primary">Confirmar</button>
            </div>
        </form>
    </div>
</div>

<script>

    $('#select-filter-status-id').change(function() {
        $.each($("#select-filter-status-id option:selected"), function() {
            if($(this).val() !== ''){
                $('#select-filter-status-id option').filter('[value=""]').removeAttr("selected") 
                $('#select-filter-status-id').selectpicker('refresh');  
            } 
        }); 
    })

    var dataTable;

    var checkDevice = () =>  {
        if (navigator.userAgent.match(/Android/i)
            || navigator.userAgent.match(/webOS/i)
            || navigator.userAgent.match(/iPhone/i)
            || navigator.userAgent.match(/iPad/i)
            || navigator.userAgent.match(/iPod/i)
            || navigator.userAgent.match(/BlackBerry/i)
            || navigator.userAgent.match(/Windows Phone/i)
        ) {
            return true;
        }
        else {
            return false;
        }
    }

    if (checkDevice() == true) {
        $.fn.DataTable.ext.pager.numbers_length = 5;
    }


    $(document).ready(function($) {
        var u = location.protocol + "//" + window.location.hostname;

        $("#select-filter-status-id").on("click", () => {  
            $("#select-filter-status-id option:selected").each(function() {
                if($(this).val() != ''){
                    $('#select-filter-status-id option[text=Todos]').prop('selected', false);
                } 
            });
        });

        $columns = [
            { data: "order",            title: "Pedido",        class: "col-md-1" }, // Pedido 0
            { data: "client",           title: "Cliente",       class: "col-md-3" }, // Cliente 1
            { data: "email",            title: "Email",         class: "d-none" }, // Email cliente 2
            { data: "document",         title: "Documento",     class: "d-none" }, // Email cliente 3
            { data: "product",          title: "Produto",       class: "col-md-2" }, // Produto 4
            { data: "sale",             title: "Oferta.",       class: "d-none" }, // Oferta 5
            { data: "quantity",         title: "Qnt.",          class: "col-md-1 text-center" }, // Quantidade de vendas 6
            { data: "deadline",         title: "Entrega.",      class: "col-md-1" }, // Data da entrega 7
            { data: "billing",          title: "Fatur. (R$)",   class: "col-md-1" }, // Faturamento 8
            { data: "payment",          title: "Pgto.",         class: "d-none" }, // Forma de pagamento 9
            { data: "parcel",           title: "Parcelas",      class: "d-none" }, // Parcelas da forma de pagamento 10
            { data: "addressRoad",      title: "End.",          class: "d-none" }, // Endereço 11
            { data: "addressNum",       title: "Núm.",          class: "d-none" }, // Número 12
            { data: "addressDistrict",  title: "Bairro",        class: "d-none" }, // Bairro 13
            { data: "addressCity",      title: "Cidade",        class: "d-none" }, // Cidade 14
            { data: "addressState",     title: "UF",            class: "d-none" }, // Estado 15
            { data: "afiliate",         title: "Afiliado.",     class: "d-none" }, // Afiliado  16
            { data: "commissinAfi",     title: "Comis. Afi.",   class: "d-none" }, // Comissão Afiliado 17
            { data: "taxAfi",           title: "Tax Afi.",      class: "d-none" }, // Taxa Afiliado 18
            { data: "produtor",         title: "Produtor",      class: "d-none" }, // Produtor produto 19
            { data: "commissinProd",    title: "Comis. Prod.",   class: "d-none" }, // Comissão Produtor 20
            { data: "taxprod",          title: "Tax Prod.",     class: "d-none" }, // Taxa Produtor 21
            { data: "responsible",      title: "Resp.",         class: "col-md-1" }, // 22
            { data: "commissinOp",      title: "Faturamento",   class: "col-md-1" }, // Comissão Operador 23
            { data: "taxmaq",           title: "Tax Máq.",      class: "col-md-1" }, // Lucro   24
            { data: "status",           title: "Stat.",         class: "here-update-badge px-1" }, // Status 25
            { data: "options",          targets: 'no-sort' },   // 26
            { data: "number",           title: "Telefone",      class: "d-none"}, //27.                      
            { data: "cep",              title: "Cep",           class: "d-none"}, //28             
            { data: "addressComplement",title: "Complemento",   class: "d-none"} //29 
        ];

        $columnsIndex = [0, 1, 27, 2, 3, 4, 5, 6, 7, 8, 9, 10, 28, 11, 12, 29, 13, 14, 15, 25, 16, 17, 18, 19, 20, 21, 22, 23, 24];
        $columnsIndexPDF = [0, 1, 27, 4, 6, 7, 8, 22, 23, 24, 25]; 

        dataTable = $('#orders-list-operator-dt').DataTable({
            searching: false,
            processing: true,
            select: false,
            lengthChange: true,
            dom: 'Bfrtip',
            buttons: [{
                    extend: 'csv',
                    text: 'csv',
                    titleAttr: 'CSV',
                    filename: "Pedidos - Logzz",
                    action: newexportaction,
                    bom: true,
                    charset: 'utf-8',
                    exportOptions: {
                        columns: $columnsIndex,
                    }
                },
                {
                    extend: 'excel',
                    text: 'excel',
                    titleAttr: 'XLS',
                    className: 'ExcelExport',
                    filename: "Pedidos - Logzz",
                    title: "Pedidos - Logzz",
                    action: newexportaction,
                    exportOptions: {
                        columns: $columnsIndex,
                        modifier: {
                            order: 'applied',
                            page: 'all'
                        }
                    }
                },
                {
                    extend: 'pdf',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    text: 'pdf',
                    titleAttr: 'PDF',
                    title: "Pedidos - Logzz",
                    action: newexportaction,
                    exportOptions: {
                        columns: $columnsIndexPDF,
                        modifier: {
                            search: 'applied',
                            order: 'applied',
                            page: 'all'
                        }
                    },
                }
            ],
            'serverSide': true,
            "processing": true,
            'serverMethod': 'post',
            "ajax": {
                url: u + '/ajax/list-datatable-ajax/order-operator-ajax.php',
                type: "POST",
                dataType: "JSON",
                data: {
                    filter_data: function() { return $('#filter-form').serialize(); },
                },
                complete: function(data) {
                    total = data['responseJSON'];
                    if (total.filter) {
                        $("#result").html('Exibindo ' + total.recordsFiltered + ' pedidos de acordo com seus filtros.');
                    } else {
                        if (total.recordsFiltered == 0) {
                            $("#no-sale").removeClass('d-none');
                            $("#no-sale").addClass('d-block');
                        } else {
                            $("#no-sale").removeClass('d-block');
                            $("#no-sale").addClass('d-none');
                        }
                        $("#result").html(total.recordsFiltered + ' Pedidos no total.');
                    }
                },
            },
            paging: true,
            "columns": $columns,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            "language": {
                "processing": "Atualizando listagem...",
                "lengthMenu": "_MENU_ usúarios por página",
                "zeroRecords": "Sem dados para mostrar",
                "info": "Página _PAGE_ de _PAGES_",
                "infoEmpty": "Nenhuma usúario para exibir aqui.",
                "search": "Filtrar",
                "paginate": {
                    "next": checkDevice() ? "<i class='fa fa-arrow-right'></i>" : 'Próximo',
                    "previous": checkDevice() ? "<i class='fa fa-arrow-left'></i>" : 'Anterior'
                },
                "infoFiltered": "(filtrando de _MAX_ envios, no total.)"
            },
        });

        $('#filter-form').submit(function(e) {
            e.preventDefault();
            dataTable.ajax.reload();
            $(".chatbox").removeClass('active');
        });
    });

    function orderDropdown(element) {

        console.log(element.closest);

        var button = $(element.closest("button"));
        current_parent = button.parent();

        var tr = button.closest("tr");
        current_resp_text = tr[0].querySelector(".resp-text");

        if (!current_parent.children(".dropdown-menu").length) {
            current_order_id = $(button).data('order');
            ordernum = $(button).data('ordernum');
            operation_id = $(button).data('operation');
            $("#modalConfirmResponsability").modal("show");
        }
    };

</script>