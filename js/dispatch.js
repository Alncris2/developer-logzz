$(document).ready(function ($) {

    var u = "https://" + window.location.hostname;
    
    
    $('.bling-uf-list').select2();
    
    $('.tiny-product-list').select2();
    
     $(".tiny-product-list").change(function () {
        var multipleValues = $(".tiny-product-list").val();
        $("#tiny-products-list-text").val(multipleValues);
    });
    
    $(".bling-uf-list").change(function () {
        var multipleValues = $(".bling-uf-list").val();
        $("#bling-uf-list-text").val(multipleValues);
    });

    $('.bling-users-list').select2();

    $(".bling-users-list").change(function () {
        var multipleValues = $(".bling-users-list").val();
        $("#bling-users-list-text").val(multipleValues);
        
        // POLULAR SELECT "PRODUCTS" DE ACORDO COM OS PRODUTOS DOS USUARIOS SELECIONADO
        console.log(multipleValues);
        
        let conditionWhere = "";
        $.each( multipleValues, function( index, value ){
            index === 0 ? conditionWhere = `WHERE user__id = ${value}` : conditionWhere += ` AND user__id = ${value}`;
        });
        
        const query = conditionWhere;
    
        $('.tiny-product-list').find('option').remove().end();
        
        //AJAX PARA PEGAR OPTIONS DE ACORDO COM USUARIOS
        
        const URL = "/api/v1/getProductsForSpecificUsers";
        const formData = new FormData();
        formData.append('query', query);
        
        $.ajax({
            url: URL,
			type: "POST",
			data: formData,
			dataType: 'json',
			processData: false,
			contentType: false,
			error: function(pam,pam2,pam3){
			    console.log(pam,pam2,pam3);
			},
        	success: function (data) {
        	    $.each(data, function(index, value){
        	        $('.tiny-product-list').append(`<option value=${value.product_id}>${value.product_name} [${value.product_code}]</option>`);
   			        console.log(value);
        	    });
			}
		});
    });

    // Integration Tiny form Submit
	$('#IntegrationTiny').submit(function (e) {

        e.preventDefault();
		// Captura os dados do formulário
		const IntegrationBling = document.getElementById('IntegrationBling');

		// Instância o FormData passando como parâmetro o formulário
		var formData = new FormData(IntegrationTiny);

        const URL = "/ajax/new-tiny-integration.php";
        
        $.ajax({
            url: URL,
			type: "POST",
			data: formData,
			dataType: 'json',
			processData: false,
			contentType: false,
        	success: function (data) {
        	    if(data.status == 1){
        	       Swal.fire({
						title: "Integração Criada!",
						icon: 'success',
					}).then((value) => {
						document.location.reload(true);
					});
					
					return;
        	    }
        	    Swal.fire({
                    title: 'Erro!',
					text: data.msg,
					icon: 'warning',
				});
				
			}
		});
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
