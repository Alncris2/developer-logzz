$(document).ready(function ($) {

    var u = location.protocol + "//" + window.location.hostname;

    $('.bling-uf-list').select2();
    $(".bling-uf-list").change(function () {
        var multipleValues = $(".bling-uf-list").val();
        $("#bling-uf-list-text").val(multipleValues);
    });

    $('.bling-users-list').select2();

    $(".bling-users-list").change(function () {
        var multipleValues = $(".bling-users-list").val();
        $("#bling-users-list-text").val(multipleValues);
    });

    // Integration Bling form Submit
	$('#IntegrationBling').submit(function () {

		// Captura os dados do formulário
		var IntegrationBling = document.getElementById('IntegrationBling');

		// Instância o FormData passando como parâmetro o formulário
		var formData = new FormData(IntegrationBling);

        var url = u + "/ajax/new-bling-integration.php";

        // Envia O FormData através da requisição AJAX
        $.ajax({
            url: url,
			type: "POST",
			data: formData,
			dataType: 'json',
			processData: false,
			contentType: false,
			success: function (feedback) {
				if (feedback.status == 1) {
					Swal.fire({
						title: "Integração Criada!",
						text: feedback.msg,
						icon: 'success',
					}).then((value) => {
						document.location.reload(true);
					});
				} else {
					Swal.fire({
                        title: feedback.title,
						text: feedback.msg,
						icon: 'warning',
					});
				}

			}
		});

		return false;
	});

	$('#dispatches-datatable').DataTable({
		searching: true,
		paging: true,
		select: false,
		lengthChange: false,
		"language": {
            "lengthMenu": "_MENU_ envios por página",
            "zeroRecords": "Sem resultados para sua busca",
            "info": "Página _PAGE_ de _PAGES_",
            "infoEmpty": "Nenhuma oferta para exibir aqui.",
            "search": "Filtrar",
            "paginate": {
                "next": ">>",
            "previous": "<<"
            },
            "infoFiltered": "(filtrando de _MAX_ envios, no total.)"
        }
	});


});
