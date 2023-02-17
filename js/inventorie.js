var dataTable;

jQuery(document).ready(function() {

    var reserv = $("#select-ship-product").html();
    $("#select-ship-product").selectpicker();

    $("#local-content-tab .list-group-item").each(function(index) {
        if ($(this).attr('class').indexOf('active') != -1) {
            var operation_id = this.getAttribute('id');
            var ship_locale = this.getAttribute('ship_locale');
            initializeDataTablesSettings(operation_id, ship_locale);
        }
    });

    $('#local-tab').click(function() {
        $("#local-content-tab .list-group-item").each(function(index) {
            if ($(this).attr('class').indexOf('active') != -1) {
                var operation_id = this.getAttribute('id');
                var ship_locale = this.getAttribute('ship_locale');
                initializeDataTablesSettings(operation_id, ship_locale);
            }
        });
    });

    $('#distribution-tab').click(function() {
        $("#distribution-content-tab .list-group-item").each(function(index) {
            if ($(this).attr('class').indexOf('active') != -1) {
                var operation_id = this.getAttribute('id');
                var ship_locale = this.getAttribute('ship_locale');
                initializeDataTablesSettings(operation_id, ship_locale);
            }
        });
    });

    $('.list-group-item').click(function() {
        var operation_id = this.getAttribute('id');
        var ship_locale = this.getAttribute('ship_locale');
        initializeDataTablesSettings(operation_id, ship_locale);
    });

    $('.list-group-item').click(function() {
        var operation_id = this.getAttribute('id');
        var ship_locale = this.getAttribute('ship_locale');
        initializeDataTablesSettings(operation_id, ship_locale);
    });

    $('.filter-btn').on('click', function() {
        $('.chatbox').addClass('active');
    });

    $('.chatbox-close').on('click', function() {
        $('.chatbox').removeClass('active');
    });

    $("#select-user").change(function() {
        var multipleValues = $("#select-user").val();
        $("#select-user-val").val(multipleValues);

        // POLULAR SELECT "PRODUCTS" DE ACORDO COM OS PRODUTOS DOS USUARIOS SELECIONADO
        let conditionWhere = `AND user__id = ${multipleValues}`;
        const query = conditionWhere;

        var productSelect = $('#sselect-product');
        $('#select-product').val(null).trigger('change');

        //AJAX PARA PEGAR OPTIONS DE ACORDO COM USUARIOS
        const URL = "/api/v1/getProductsForSpecificUsers";
        const formData = new FormData();
        formData.append('query', query);

        if (multipleValues == '') {
            $("#select-product").selectpicker('destroy');
            $('#select-product').empty();
            $("#select-product").html(reserv);
            $("#select-product").selectpicker();
        } else {
            $.ajax({
                url: URL,
                type: "POST",
                data: formData,
                dataType: 'json',
                processData: false,
                contentType: false,
                beforeSend: function(){
                    display_loader();
                } ,
                complete: function() {
                    display_loader(false);
                },
                error: function(pam, pam2, pam3) {
                    console.log(pam, pam2, pam3);
                },
                success: function(data) {
                    $("#select-product").selectpicker('destroy');
                    $("#select-product option").remove();
                    if (data.length != 0) {
                        $.each(data, function(index, value) {
                            var newOption = new Option(value.product_name, value.product_id, false, false);
                            $('#select-product').append(newOption).trigger('change');
                        });
                    }
                    $("#select-product").selectpicker();
                }
            });
        }
    });

    $("#select-ship-name").change(function() {
        var multipleValues = $("#select-ship-name").val();
        $("#text-ship-name").val(multipleValues);

        // POLULAR SELECT "PRODUCTS" DE ACORDO COM OS PRODUTOS DOS USUARIOS SELECIONADO
        let conditionWhere = `AND user__id = ${multipleValues}`;
        const query = conditionWhere;

        var productSelect = $('#select-ship-product');
        $('#select-ship-product').val(null).trigger('change');

        //AJAX PARA PEGAR OPTIONS DE ACORDO COM USUARIOS
        const URL = "/api/v1/getProductsForSpecificUsers";
        const formData = new FormData();
        formData.append('query', query);

        if (multipleValues == '') {
            $("#select-ship-product").selectpicker('destroy');
            $('#select-ship-product').empty();
            $("#select-ship-product").html(reserv);
            $("#select-ship-product").selectpicker();
        } else {
            $.ajax({
                url: URL,
                type: "POST",
                data: formData,
                dataType: 'json',
                processData: false,
                contentType: false,
                beforeSend: function(){
                    display_loader();
                } ,
                complete: function() {
                    display_loader(false);
                },
                error: function(pam, pam2, pam3) {
                    console.log(pam, pam2, pam3);
                },
                success: function(data) {
                    $("#select-ship-product").selectpicker('destroy');
                    $("#select-ship-product option").remove();
                    if (data.length != 0) {
                        var newOption = new Option("Todos os produtos", "", true, true);
                        $('#select-ship-product').append(newOption).trigger('change');
                        $.each(data, function(index, value) {
                            var newOption = new Option(value.product_name, value.product_id, false, false);
                            $('#select-ship-product').append(newOption).trigger('change');
                        });
                    }
                    $("#select-ship-product").selectpicker();
                }
            });
        }
    });

    $("#select-product").change(function() {
        var multipleValues = $("#select-product").val();
        $("#select-product-val").val(multipleValues);
    });

    $("#qtd").change(function() {
        var multipleValues = $("#qtd").val();
        $("#qtd-user-text").val(multipleValues);
    });

    //Mostrar tab local
    $('#local-tab').click(function() {
        $('#local-content-tab').show();
        $('#distribution-content-tab').hide();
    });

    //Mostrar tab Distribuição
    $('#distribution-tab').click(function() {
        $('#local-content-tab').hide();
        $('#distribution-content-tab').show();
    });
});

function initializeDataTablesSettings(operation_id, ship_locale) {

    let nameTable = ship_locale == 0 ? '#orders-list-products' : '#orders-list';

    if ($.fn.dataTable.isDataTable('#orders-list-products')) {
        $(nameTable).DataTable().clear();
        $(nameTable).DataTable().destroy();
        $(nameTable).empty();
        // re Add CSS to table
        $(nameTable).addClass("usa-table views-table views-view-table cols-8 sticky-enabled sticky-table");
        $(nameTable).css("width", "100%");
    }

    dataTable = $(nameTable).DataTable({
        searching: false,
        processing: true,
        retrieve: true,
        select: true,
        lengthChange: false,
        'serverSide': true,
        "processing": true,
        'serverMethod': 'post',
        "ajax": {
            url: u + '/ajax/list-datatable-ajax/products-orders-ajax.php',
            type: "POST",
            dataType: "JSON",
            data: {
                filter_data: function() { return $('#filter-form').serialize(); },
                operation_id: operation_id,
                ship_locale: ship_locale,
            },
            beforeSend: function(){
                display_loader();
            } ,
            complete: function() {
                display_loader(false);
            },
            complete: function(data) {
                total = data['responseJSON'];
                if (total.filter) {
                    $("#on-filter").removeClass('d-none');
                    $("#on-filter").addClass('d-block');
                    $("#result").html(total.filterText);
                } else {
                    $("#on-filter").removeClass('d-block');
                    $("#on-filter").addClass('d-none');
                    $("#result").html(total.filterText);
                }
            },
        },
        drawCallback: function() {
            var page_min = 7;
            var $api = this.api();
            var pages = $api.page.info().pages;
            var rows = $api.data().length;

            // Tailor the settings based on the row count
            if (pages === 1) {
                // With this current length setting, not more than 1 page, hide pagination
                $('.dataTables_paginate').css("display", "none");
            } else {
                // SHow everything
                $('.dataTables_paginate').css("display", "block");
            }
        },
        paging: true,
        "columns": [{
                data: "product",
                title: "Produto",
                class: "col-md-4 text-left",
            }, //Pedido
            {
                data: "inventory",
                title: "Estoque Atual",
                class: "col-md-3 text-left",
            }, //Cliente
            {
                data: "shipping",
                title: "Último Envio",
                class: "col-md-3 text-left",
            }, //Produto
            {
                data: "quantity",
                title: "Quant. Últ. Envio",
                class: "col-md-2 text-left",
            }, 
            {
                data: "price",
                title: "Valor Total",
                class: "col-md-2 text-left",
            }, //Entreg
        ],
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

    $(nameTable).addClass("usa-table views-table views-view-table cols-8 sticky-enabled sticky-table");
    $(nameTable).css("width", "100%");
}

jQuery(document).ready(function() {
    $('#filter-form').submit(function(e) {
        e.preventDefault();
        dataTable.ajax.reload();
        $(".chatbox").removeClass('active');
    });
});

$('#cadOrRemoveLocale').submit(function(e) {

    // Captura os dados do formulário
    var ReleaseLocaleForm = document.getElementById('cadOrRemoveLocale');

    // Instância o FormData passando como parâmetro o formulário
    var formData = new FormData(ReleaseLocaleForm);

    console.log($(this).find("input[required='true']"));

    $(this).find("input[required='true']").each(function(e) {
        console.log($(this));
        if (!$(this).val()) {
            Swal.fire({
                title: 'Campos inválidos!',
                text: 'Preencha todos os campos antes de continuar',
                icon: 'error',
            }).then((value) => {

            });
            return false;
        } else {
            Swal.fire({
                text: 'Você confirma essa solicitação?',
                showCancelButton: true,
                icon: 'warning',
            }).then((value) => {
                if (value.isConfirmed) {
                    // Envia O FormData através da requisição AJAX
                    $.ajax({
                        url: u + "/ajax/add-locale-qtd-ajax.php",
                        type: "POST",
                        data: formData,
                        dataType: 'json',
                        processData: false,
                        contentType: false,
                        beforeSend: function(){
                            display_loader();
                        } ,
                        complete: function() {
                            display_loader(false);
                        },
                        success: function(feedback) {
                            //console.log(feedback);
                            $('#modalAddOrRemove').modal('toggle');
                            Swal.fire({
                                title: feedback.title,
                                text: feedback.msg,
                                icon: feedback.type,
                            }).then((value) => {
                                if (value.isConfirmed) {
                                    dataTable.ajax.reload();
                                } else {
                                    $('#modalAddOrRemove').modal('toggle');
                                }
                            });
                        }
                    })
                }
            })
        }
    });
    return false;

});