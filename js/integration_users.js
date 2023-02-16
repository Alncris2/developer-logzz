function saveDataInSessionAndRedirectToTinyIntegration(element) {
    let userCode = element.dataset.usercode;
    let productCode = element.dataset.productcode;
    let url = element.dataset.url;

    $.ajax({
        url: '/api/v1/save-data-in-session',
        dataType: "json",
        data: { "userCode": userCode, "productCode": productCode, "integration_url": url },
        beforeSend: function(){
            display_loader();
        } ,
        complete: function() {
            display_loader(false);
        },
        success: function(data) {
            if (data.status == "ok") {
                window.location.href = "/integracoes/expedicao/";
                return;
            }
            console.log(data.status)
        }
    });

}

function deleteIntegration(element) {
    const idToDeleteSolicitation = element.dataset.idtodelete;
    const url = element.dataset.url;

    Swal.fire({
        title: 'Deseja deletar essa solicitação?',
        text: 'Essa integração será apagada',
        icon: 'warning',
        showCancelButton: true,
    }).then(function(isConfirm) {
        if (isConfirm.isConfirmed) {
            $.ajax({
                type: "POST",
                url: '/api/v1/delete-solicitation-of-integration',
                dataType: "json",
                data: { "idToDelete": idToDeleteSolicitation, "url": url },
                beforeSend: function(){
                    display_loader();
                } ,
                complete: function() {
                    display_loader(false);
                },
                success: function(data) {
                    Swal.fire({
                        title: 'Integração deletada com sucesso!',
                        icon: 'success',
                        showCancelButton: true,
                    });
                    if (data.status == "ok") {
                        dataTable.ajax.reload();
                    }
                }
            });
        }
    });
}

var dataTable;

$(document).ready(function($) {
    dataTable = $('#orders-list-users').DataTable({
        searching: false,
        processing: true,
        select: false,
        lengthChange: true,
        dom: 'Bfrtip',
        buttons: [
            'csv', 'excel', 'pdf',
            {
                extend: 'pdf',
                orientation: 'landscape',
                title: "Pedidos - DropExpress",
                footer: true
            }
        ],
        'serverSide': true,
        "processing": true,
        'serverMethod': 'post',
        "ajax": {
            url: '/ajax/list-datatable-ajax/integation-user-ajax.php',
            type: "POST",
            data: {
                filter_data: function() {
                    // Salva os dados do usuario que foi selecionado em uma sessão e redireciona para pagina de escolher qual região será integrado 
                    $('.fs-16 text-black font-w600 mb-0').html('');
                    return $('#filter-form').serialize();
                }

            },
            beforeSend: function(){
                display_loader();
            } ,
            complete: function(data) {
                display_loader(false);
                total = data['responseJSON'];
                if (total.filter) {
                    $("#result").html('Exibindo ' + total.recordsTotal + ' pedidos de acordo com seus filtros.');
                } else {
                    $("#result").html('Exibindo ' + total.recordsTotal + ' pedidos de integração no total.');
                }
            },
        },
        "pageLength": 10,
        "iDisplayLength": 10,
        paging: true,
        "columns": [
            { data: "data" },
            { data: "product" },
            { data: "user" },
            { data: "platform" },
            { data: "stock" },
            { data: "status" },
            { data: "action" },
        ],
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "language": {
            "lengthMenu": "_MENU_ envios por página",
            "zeroRecords": "Sem resultados para sua busca",
            "info": "Página _PAGE_ de _PAGES_",
            "processing": "Carregando pedidos...",
            "infoEmpty": "Nenhuma oferta para exibir aqui.",
            "search": "Filtrar",
            "paginate": {
                "next": "Próximo",
                "previous": "Anterior"
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