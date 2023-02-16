/* AJAX */
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


$(document).ready(function ($) {

    var u = location.protocol + "//" + window.location.hostname;
    const userPlan = $('#user_plan').val();
    
    $(".segment-select").Segment();

    jQuery('.filter-btn').on('click', function () {
        jQuery('.chatbox').addClass('active');
    });
    jQuery('.chatbox-close').on('click', function () {
        jQuery('.chatbox').removeClass('active');
    });

    $("#select-filter-status-id").change(function () {
        var multipleValues = $("#select-filter-status-id").val();
        $("#text-filter-status-id").val(multipleValues);
    });

    $("#select-operations").change(function () {
        var multipleValues = $("#select-operations").val();
        $("#text-select-operations").val(multipleValues);
    });

    $("#select-operators").change(function () {
        var multipleValues = $("#select-operators").val();
        $("#text-select-operators").val(multipleValues);
    });

    $("#select-affiliates").change(function () {
        var multipleValues = $("#select-affiliates").val();
        $("#text-select-affiliates").val(multipleValues);
    });

    $("#select-producers").change(function () {
        var multipleValues = $("#select-producers").val();
        $("#text-select-producers").val(multipleValues);
    });

    // Deletar solicitação de integração
    $('.deleteSolicitation').each(function () {
        $(this).on('click', function () {
            const idToDeleteSolicitation = $(this)[0].dataset.idtodelete;
            const url = $(this)[0].dataset.url;
            Swal.fire({
                title: 'Deseja deletar essa solicitação?',
                text: 'Essa integração será apagada',
                icon: 'warning',
                showCancelButton: true,
            }).then(function (isConfirm) {
                if (isConfirm.isConfirmed) {
                    $.ajax({
                        type: "POST",
                        url: '/api/v1/delete-solicitation-of-integration',
                        dataType: "json",
                        data: { "idToDelete": idToDeleteSolicitation, "url": url },
                        success: function (data) {
                            if (data.status == "ok") {
                                dataTable.ajax.reload();
                            }
                        }
                    });
                }
            });
        });
    });


    if (userPlan == 5) {
        $columns = [
            { data: "order",            title: "Pedido",        class: "col-md-1" }, // Pedido 0
            { data: "client",           title: "Cliente",       class: "col-md-2" }, // Cliente 1
            { data: "email",            title: "Email",         class: "d-none" }, // Email cliente (Vizivel apenas no excel) 2
            { data: "document",         title: "Documento",     class: "d-none" }, // Email cliente (Vizivel apenas no excel) 3
            { data: "product",          title: "Produto",       class: "col-md-2" }, // Produto 4
            { data: "sale",             title: "Oferta.",       class: "d-none" }, // Oferta (Vizivel apenas no excel) 5
            { data: "quantity",         title: "Qnt.",          class: "col-md-1 text-center" }, // Quantidade de vendas 6 
            { data: "deadline",         title: "Entrega.",      class: "col-md-1" }, // Data da entrega 7
            { data: "billing",          title: "Fatur. (R$)",   class: "col-md-1" }, // Faturamento 8
            { data: "payment",          title: "Pgto.",         class: "d-none" }, // Forma de pagamento (Vizivel apenas no excel) 9 
            { data: "parcel",           title: "Parcelas",      class: "d-none" }, // Parcelas da forma de pagamento (Vizivel apenas no excel) 10
            { data: "expenses",         title: "Despesas (R$)", class: "col-md-1" }, // Despesas 11
            { data: "addressRoad",      title: "End.",          class: "d-none" }, // Endereço (Vizivel apenas no excel) 12
            { data: "addressNum",       title: "Núm.",          class: "d-none" }, // Número (Vizivel apenas no excel) 13
            { data: "addressDistrict",  title: "Bairro",        class: "d-none" }, // Bairro (Vizivel apenas no excel) 14
            { data: "addressCity",      title: "Cidade",        class: "d-none" }, // Cidade (Vizivel apenas no excel) 15
            { data: "addressState",     title: "UF",            class: "d-none" }, // Estado (Vizivel apenas no excel) 16
            { data: "afiliate",         title: "Afiliado.",     class: "d-none" }, // Afiliado (Vizivel apenas no excel)  17           
            { data: "commissinAfi",     title: "Comis. Afi.",   class: "d-none" }, // Comissão Afiliado (Vizivel apenas no excel) 18
            { data: "taxAfi",           title: "Tax Afi.",      class: "d-none" }, // Taxa Afiliado (Vizivel apenas no excel) 19
            { data: "produtor",         title: "Produtor",      class: "d-none" }, // Produtor produto (Vizivel apenas no excel) 20
            { data: "commissinProd",    title: "Comis. Prod.",  class: "d-none" }, // Comissão Produtor (Vizivel apenas no excel) 21
            { data: "taxprod",          title: "Tax Prod.",     class: "d-none" }, // Taxa Afiliado (Vizivel apenas no excel) 22
            { data: "operator",         title: "Operador.",     class: "d-none" }, // Nome operador (Vizivel apenas no excel) 24
            { data: "commissinOp",      title: "Comis. Op.",    class: "d-none" }, // Comissão Operador (Vizivel apenas no excel) 23
            { data: "taxmaq",           title: "Tax Máq.",      class: "d-none" }, // Lucro   25
            { data: "profit",           title: "Lucro.",        class: "col-md-1" }, // Lucro 26
            { data: "freight",          title: "Frete",         class: "d-none" }, // Frete cobrado pelo Usúario (Vizivel apenas no excel) 27
            { data: "taxfreight",       title: "Tax. Entrega",  class: "d-none" }, // Entrega cobrado pela plataforma (Vizivel apenas no excel) 28
            { data: "status",           title: "Stat.",         class: "here-update-badge px-1" }, // Status 29
            { data: "options",          targets: 'no-sort',     searchable: false,     sortable: false }, // 30
            { data: "number",           title: "Telefone",      class: "d-none"},   // 31
            { data: "cep",              title: "Cep",           class: "d-none"},   // 32            
            { data: "addressComplement",title: "Complemento",   class: "d-none"},   // 33
            { data: "CMV",              title: "CMV",           class: "d-none"},   // 34            
            { data: "date_remp",        title: "Data Reembolso",class: "d-none"}    // 35
        ];

        $columnsIndex = [0, 1, 31, 2, 3, 4, 5, 6, 7, 8, 34, 9, 10, 32, 12, 13, 33, 14, 15, 16, 29, 35, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28];
        $columnsIndexPDF = [0, 1, 31, 4, 6, 7, 8, 9, 12, 27, 28]; 
    } else {
        $columns = [
            { data: "order",            title: "Pedido",        class: "col-md-1" }, //Pedido. 0
            { data: "client",           title: "Cliente",       class: "col-md-2" }, //Cliente.1
            { data: "email",            title: "Email",         class: "d-none" }, // Email cliente (Vizivel apenas no excel). 2
            { data: "document",         title: "Documento",     class: "d-none" }, // Documento cliente (Vizivel apenas no excel). 3
            { data: "product",          title: "Produto",       class: "col-md-2" }, //Produto. 4   
            { data: "sale",             title: "Oferta.",       class: "d-none" }, // Oferta (Vizivel apenas no excel). 5
            { data: "quantity",         title: "Qnt.",          class: "col-md-1 text-center" }, // . 6
            { data: "deadline",         title: "Entreg. (R$)",  class: "col-md-1" }, //Entreg . 7
            { data: "billing",          title: "Fatur. (R$)",   class: "col-md-1" }, //Fatur. 8
            { data: "payment",          title: "Pgto.",         class: "d-none" }, // Forma de pagamento (Vizivel apenas no excel) . 9
            { data: "parcel",           title: "Parcelas",      class: "d-none" }, // Parcelas da forma de pagamento (Vizivel apenas no excel) . 10
            { data: "taxprod",          title: "Tax Prod.",     class: "d-none" }, //Taxa . 11
            { data: "taxuser",          title: "Taxa (R$)",     class: "col-md-1" }, //Taxa . 12
            { data: "addressRoad",      title: "End.",          class: "d-none" }, // Endereço (Vizivel apenas no excel) . 13
            { data: "addressNum",       title: "Núm.",          class: "d-none" }, // Número (Vizivel apenas no excel). 14
            { data: "addressDistrict",  title: "Bairro.",       class: "d-none" }, // Bairro (Vizivel apenas no excel). 15
            { data: "addressCity",      title: "Cidade.",       class: "d-none" }, // Cidade (Vizivel apenas no excel). 16
            { data: "addressState",     title: "UF.",           class: "d-none" }, // Estado (Vizivel apenas no excel). 17
            { data: "afiliate",         title: "Afiliado.",     class: "d-none" }, // Afiliado (Vizivel apenas no excel)  .   18
            { data: "commissinAfi",     title: "Comis. Afi.",   class: "d-none" }, // Comissão Afiliado (Vizivel apenas no excel) .  19
            { data: "taxAfi",           title: "Tax Afi.",      class: "d-none" }, // Taxa Afiliado (Vizivel apenas no excel 10
            { data: "produtor",         title: "Produtor",      class: "d-none" }, // Produtor produto (Vizivel apenas no excel). 21
            { data: "commissinProd",    title: "Comis. Prod.",  class: "d-none" }, // Comissão Produtor (Vizivel apenas no excel). 22
            { data: "taxfreight",       title: "Entreg. (R$)",  class: "col-md-1" }, // Entrega cobrado pela plataforma (Vizivel apenas no excel) . 23 - 25
            { data: "commissin",        title: "Comis. (R$)",   class: "col-md-1" }, // Comissão Produtor (Vizivel apenas no excel). 24
            { data: "freight",          title: "Frete (R$)",    class: "d-none" }, //Entreg            . 25
            { data: "status",           title: "Stat.",         class: "col-md-1 here-update-badge px-1" }, //Stat. 26
            { data: "options",          targets: 'no-sort',     searchable: false,     sortable: false }, // 27
            { data: "number",           title: "Telefone",      class: "d-none"},   // 28      
            { data: "cep",              title: "Cep",           class: "d-none"},   // 29
            { data: "addressComplement",title: "Complemento",   class: "d-none"},   // 30
            { data: "CMV",              title: "CMV",           class: "d-none"},   // 31         
            { data: "date_remp",        title: "Data Reembolso",class: "d-none"}    // 32
        ];

        $columnsIndex = [0, 1, 28, 2, 3, 4, 5, 6, 7, 31, 8, 9, 10, 29, 13, 14, 30, 15, 16, 26, 32, 18, 19, 20, 21, 22, 11, 25, 23];
        $columnsIndexPDF = [0, 1, 28, 4, 6, 7, 8, 9, 12, 25, 26];
    }



    dataTable = $('#orders-list-ajax').DataTable({
        searching: false,
        processing: true,
        select: false,
        lengthChange: true,
        dom: 'Bfrtip', 
        'iDisplayLength': 3,
        buttons: [
            {
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
            url: u + '/ajax/list-datatable-ajax/order-ajax.php',
            type: "POST",
            dataType: "JSON",
            data: {
                filter_data: function () { return $('#filter-form').serialize(); },
            },
            complete: function (data) {
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

    $('#filter-form').submit(function (e) {
        let stateObj = { id: "100" };
        window.history.pushState(stateObj, "Pedidos | Logzz", "/pedidos/?" + $('#filter-form').serialize());

        e.preventDefault();
        dataTable.ajax.reload();
        $(".chatbox").removeClass('active');
    });
});

(function( $ ){
    $.fn.extend({
        Segment: function ( ) {
            $(this).each(function (){
                var self = $(this);
                var onchange = self.attr('onchange');
                var wrapper = $("<div>",{class: "input-group"});
                $(this).find("option").each(function (){
                    var option = $("<span>",{class: 'btn form-control option' ,onclick:onchange,text: $(this).text(),value: $(this).val()});
                    if ($(this).is(":selected")){
                        option.addClass("bg-dark active");
                    }
                    wrapper.append(option);
                });
                wrapper.find("span.option").click(function (){
                    wrapper.find("span.option").removeClass("bg-dark active");
                    $(this).addClass("bg-dark active");
                    self.val($(this).attr('value'));
                });
                $(this).after(wrapper);
                $(this).hide();
            });
        }
    });
})(jQuery);

$(".export-orders-truck").on("click", function(e) {
    e.preventDefault();

    var form = document.createElement("form");
    form.target = "_blank";
    form.method = "POST";
    form.method = "POST";
    form.action = $(this).attr('href'); 
    form.style.display = "none";

    var mapInput = document.createElement("input");
    mapInput.type = "text";
    mapInput.name = "filter_data";
    mapInput.value = $('#filter-form').serialize();
    form.appendChild(mapInput);

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
});

//Update order status from /pedidos in ADM account.
function deleteOrderLink(element) {
    event.preventDefault();
    var order = element.getAttribute('data-id');

    Swal.fire({
        title: 'Tem certeza?',
        text: "Isso apagará todos os dados do pedido, incluindo upsell.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#2BC155',
        cancelButtonColor: '#FF6D4D',
        confirmButtonText: 'Sim, deletar',
        cancelButtonText: 'Não, cancelar'
    }).then((result) => {
        if (result.isConfirmed) {

            var url = u + "/ajax/delete-order.php";

            $.ajax({
                url: url,
                type: "GET",
                data: { order },
                dataType: 'json',
                processData: true,
                contentType: false,
                success: function (feedback) {
                    Swal.fire({
                        title: feedback.title,
                        text: feedback.msg,
                        icon: feedback.type,
                    }).then((value) => {
                        if (feedback.type == 'success') {
                            dataTable.ajax.reload();
                        }
                    });
                }
            });
        }
    });
    return false;
}; 

function updateOrderStatus(element) {
    event.preventDefault();
    let status_string = element.getAttribute('data-statusname'); 
    let status = element.getAttribute('data-status');
    let id = element.getAttribute('data-id');

    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
          confirmButton: 'btn btn-success', 
          cancelButton: 'btn btn-danger mr-2' 
        },
        buttonsStyling: false
    })

    swalWithBootstrapButtons.fire({
        title: 'Tem certeza ?',
        text: "você tem certeza que deseja mudar o pedido para o status " + status_string,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim, confirmar',
        cancelButtonText: 'Não, cancelar',
        reverseButtons: true  
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: u + "/update-order-status.php",
                type: "GET",
                data: { status, id },
                dataType: 'json',
                processData: true,
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
                        if (feedback.type == 'success') {
                            dataTable.ajax.reload();
                        }
                    });
                }
            }).fail(function (data) {
                Swal.fire({
                    title: "Erro de Conexão",
                    text: "Quando a página atulizar, tente mudar o status do pedido novamente.",
                    icon: 'warning',
                }).then((value) => {
                    dataTable.ajax.reload();
                });
            });
        } else if (
            /* Read more about handling dismissals below */
            result.dismiss === Swal.DismissReason.cancel
        ) {
            swalWithBootstrapButtons.fire(
                'Operação cancelada',
                'Status não foi alterado.',
                'error'
            )
        }
    })

    // Envia os parâmetro para o PHP via AJAX
    
    return false;
};

function setIdAndStatus(element) {
    event.preventDefault();
    var status = element.getAttribute('data-status');
    var id = element.getAttribute('data-id');
    var target = element.getAttribute('data-target');

    $(target).find("input[name='id']").val(id);
    $(target).find("input[name='status']").val(status); 
}

$(document).ready(function($) {
    $('#cancelationForm').find("input[name='status_description']").change( function() { 
        if($(this).val() == 0) { 
            $("input[name='other_description']").show();
            $("input[name='other_description']").prop("required", true);
            $("input[name='other_description']").focus();
        } else {
            $("input[name='other_description']").hide();
            $("input[name='other_description']").prop("required", false);
        }
    });

    $('#unavailableForm').find("input[name='status_description']").change( function() { 
        if($(this).val() == 0) { 
            $("input[name='other_description']").show();
            $("input[name='other_description']").prop("required", true);
            $("input[name='other_description']").focus();
        } else {
            $("input[name='other_description']").hide();
            $("input[name='other_description']").prop("required", false);
        }
    });

    $("#cancelationForm").submit(function() {
        event.preventDefault(); 

        var formdata = new FormData(this);

        var id = formdata.get('id');
        var status = formdata.get('status');
        var status_description = formdata.get('status_description') == '0' ?
            formdata.get('other_description') :
            formdata.get('status_description')


        console.log(status, id, status_description);

        // Envia os parâmetro para o PHP via AJAX
        $.ajax({
            url: u + "/update-order-status.php",
            type: "GET",
            data: {
                status,
                id,
                status_description
            },
            dataType: 'json',
            processData: true,
            contentType: false,
            beforeSend: function(){
                display_loader();
                $('#cancelOrderModal').modal('toggle');  
            } ,
            complete: function() {
                display_loader(false);
            },
            success: function(feedback) {
                Swal.fire({
                    title: feedback.title,
                    text: feedback.msg,
                    icon: feedback.type,
                }).then((value) => {
                    if (feedback.type == 'success') {
                        dataTable.ajax.reload();
                    }
                });
                $('#cancelOrderModal').modal('hide');
                $("#otherDescription").val('');
            }
        }).fail(function(data) {
            Swal.fire({
                title: "Erro de Conexão",
                text: "Quando a página atulizar, tente mudar o status do pedido novamente.",
                icon: 'warning',
            }).then((value) => {
                dataTable.ajax.reload();
            });
        });
        return false;

    });


    $("#unavailableForm").submit(function() {
        event.preventDefault(); 

        var formdata = new FormData(this);

        var id = formdata.get('id');
        var status = formdata.get('status');
        var status_description = formdata.get('status_description') == '0' ? formdata.get('other_description') : formdata.get('status_description')

        // Envia os parâmetro para o PHP via AJAX
        $.ajax({
            url: u + "/update-order-status.php",
            type: "GET",
            data: {
                status,
                id,
                status_description
            },
            dataType: 'json',
            processData: true,
            contentType: false,
            beforeSend: function(){
                display_loader();
            } ,
            complete: function() {
                display_loader(false);
            },
            success: function(feedback) {
                Swal.fire({
                    title: feedback.title,
                    text: feedback.msg,
                    icon: feedback.type,
                }).then((value) => {
                    if (feedback.type == 'success') {
                        dataTable.ajax.reload(); 
                        $('#unavailableForm').each(function(){
                            this.reset();
                        });
                    } else {
                        $('#unavailableOrderModal').modal('toggle');
                    }
                });
            }
        }).fail(function(data) {
            Swal.fire({
                title: "Erro de Conexão",
                text: "Quando a página atulizar, tente mudar o status do pedido novamente.",
                icon: 'warning',
            });
        });
        return false;

    });
});