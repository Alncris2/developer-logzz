var u = "https://" + window.location.hostname;

$(document).ready(function ($) {

	//Checkout Form Submit
	$('#checkoutForm').submit(function () {
		$('#SubmitButton').attr('disabled', 'disabled');

		// Captura os dados do formulário
		var checkoutForm = document.getElementById('checkoutForm');

		// Instância o FormData passando como parâmetro o formulário
		var formData = new FormData(checkoutForm);

        // Envia O FormData através da requisição AJAX
        $.ajax({
            url: u + "/ajax/checkout-ajax.php",
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
            success: function (feedback) {
                if (feedback.status == 1) {
                    var url = feedback.url;
                    Swal.fire({
                        title: "Pedido Finalizado!",
                        icon: 'success',
                    }).then((value) => {
                        window.open(url, '_self');
                    });
                } else {
                    Swal.fire({
                        title: "Algo está errado!",
                        text: feedback.msg,
                        icon: 'warning',
                    }).then((value) => {
                        $('#SubmitButton').removeAttr("disabled");
                    });
                }
            }
		// }).fail(function (data) {
		// 	Swal.fire({
		// 		title: "Erro Interno!",
		// 		text: "Tente novamente mais tarde. O suporte já foi informado e isso será corrigido.",
		// 		icon: 'error',
		// 	});
        //     $('#SubmitButton').attr('disabled', 'off');
		});
        
		return false;
	});

});

//Checkout Form Submit
$('#mbsCheckoutForm').submit(function () {
    $('#SubmitButton').attr('disabled', 'disabled');

    // Captura os dados do formulÃ¡rio
    var mbsCheckoutForm = document.getElementById('mbsCheckoutForm');

    // InstÃ¢ncia o FormData passando como parÃ¢metro o formulÃ¡rio
    var formData = new FormData(mbsCheckoutForm);

    // Envia O FormData atravÃ©s da requisiÃ§Ã£o AJAX
    $.ajax({
        url: "../ajax/checkout-mbs-ajax.php",
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
        success: function (feedback) {
            if (feedback.status == 1) {
                var url = feedback.url;
                Swal.fire({
                    title: "Pedido Finalizado!",
                    icon: 'success',
                    }).then((value) => {
                    	window.open(url, '_self');
                });
            } else {
                Swal.fire({
                    title: "Algo está errado!",
                    text: feedback.msg,
                    icon: 'warning',
                }).then((value) => {
                    $('#SubmitButton').removeAttr("disabled");
                });
            }
        }
    // }).fail(function (data) {
    //     Swal.fire({
    //         title: "Erro Interno!",
    //         text: "Tente novamente mais tarde. O suporte já foi informado e isso será corrigido.",
    //         icon: 'error',
    //     }).then((value) => {
    //         $('#SubmitButton').removeAttr("disabled");
    //     });
    });

    return false;
});

function limpa_formulário_cep() {
	//Limpa valores do formulário de cep.
	document.getElementById('rua').value = ("");
	document.getElementById('bairro').value = ("");
	document.getElementById('cidade').value = ("");
	document.getElementById('uf').value = ("");
}

function meu_callback(conteudo) {
	if (!("erro" in conteudo)) {
		//Atualiza os campos com os valores.
		document.getElementById('rua').value = (conteudo.logradouro);
		document.getElementById('bairro').value = (conteudo.bairro);
		document.getElementById('cidade').value = (conteudo.localidade);
		document.getElementById('uf').value = (conteudo.uf);
	} //end if.
	else {
		//CEP não Encontrado.
		limpa_formulário_cep();
		Swal.fire({
			title: "CEP Não encontrado!",
			text: "Certifique-se de informar corretamente seu CEP.",
			icon: 'error',
		});
	}
}

//Show address fiels after CEP field value change.
$(document).ready(function ($) {
	$('#CEP').focusout(function () {
		$('#div-rua').removeClass('d-none');
		$('#div-bairro').removeClass('d-none');
		$('#div-cidade').removeClass('d-none');
		$('#div-uf').removeClass('d-none');
		$('#div-numero').removeClass('d-none');
		$('#div-referencia').removeClass('d-none');
		$('#div-numero').removeClass('d-none');
	});
});

function pesquisacep(valor) {

	//Nova variável "cep" somente com dígitos.
	var cep = valor.replace(/\D/g, '');

	//Verifica se campo cep possui valor informado.
	if (cep != "") {

		//Expressão regular para validar o CEP.
		var validacep = /^[0-9]{8}$/;

		//Valida o formato do CEP.
		if (validacep.test(cep)) {

			//Preenche os campos com "..." enquanto consulta webservice.
			document.getElementById('rua').value = "...";
			document.getElementById('bairro').value = "...";
			document.getElementById('cidade').value = "...";
			document.getElementById('uf').value = "...";

			//Cria um elemento javascript.
			var script = document.createElement('script');

			//Sincroniza com o callback.
			script.src = 'https://viacep.com.br/ws/' + cep + '/json/?callback=meu_callback';

			//Insere script no documento e carrega o conteúdo.
			document.body.appendChild(script);

		} //end if.
		else {
			//cep é inválido.
			limpa_formulário_cep();
			Swal.fire({
				title: "CEP Inválido!",
				text: "Informe corretamente seu CEP.",
				icon: 'error',
			});
		}
	} //end if.
	else {
		//cep sem valor, limpa formulário.
		limpa_formulário_cep();
	}

}

$('#aplicar-cupom').click(function () {

    event.preventDefault();

    var coupon = $('#cupom-pedido').val();
    var sale = $('#sale').val();
    var orignal_value = $('#final-price').val();


    var url = u + "/ajax/aplly-coupon-ajax.php";

    // Envia O FormData através da requisição AJAX
    $.ajax({
        url: url,
        type: "GET",
        data: { coupon, sale },
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
            if (feedback.status == 1) {
                Swal.fire({
                    title: "Cupom Aplicado!",
                    text: feedback.msg,
                    icon: 'success',
                })

                var discounted_value = (orignal_value * feedback.discount).toFixed(2);
                var discount_string = "<small><s>R$ " + orignal_value + "</s></small> " + discounted_value;


                $('#show-final-price').html(discount_string);

            } else {
                Swal.fire({
                    title: feedback.title,
                    text: feedback.msg,
                    icon: 'warning',
                });

                $('#show-final-price').html("");
                $('#show-final-price').html("R$ " + orignal_value);
            }

        }
    });
});


//Mask set up
$('.phone').mask("(00) 00000-0000");
$('.date').mask("00/00/0000");
$('.cep').mask("00000-000");
$('.cpf').mask("000.000.000-00");