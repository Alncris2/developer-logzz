// COLOCAR EM PRODUÇÃO 
var u = location.protocol + "//" + window.location.hostname + "/seguro.dropexpress";

$(document).ready(function ($) {
 
    //Checkout Form Submit
    $('#checkoutForm').submit(function () {
        $('#SubmitButton').attr('disabled', 'disabled');
        var u = location.protocol + "//" + window.location.hostname + "/seguro.dropexpress";
  
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
            }).fail(function (data) {
            	Swal.fire({
            		title: "Erro Interno!",
            		text: "Tente novamente mais tarde. O suporte já foi informado e isso será corrigido.",
            		icon: 'error',
            	});
                $('#SubmitButton').removeAttr('disabled');
        });

        return false;
    });

});

//Checkout Form Submit
$('#mbsCheckoutForm').submit(function () {
    $('#SubmitButton').attr('disabled', 'disabled');
    var u = location.protocol + "//" + window.location.hostname + "/seguro.dropexpress";

    // Captura os dados do formulÃ¡rio
    var mbsCheckoutForm = document.getElementById('mbsCheckoutForm');

    // InstÃ¢ncia o FormData passando como parÃ¢metro o formulÃ¡rio
    var formData = new FormData(mbsCheckoutForm);

    // Envia O FormData atravÃ©s da requisiÃ§Ã£o AJAX
    $.ajax({
        url: u + "/ajax/checkout-mbs-ajax.php",
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
                    window.location.href = url;
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
        }).fail(function (data) {
            Swal.fire({
                title: "Erro Interno!",
                text: "Tente novamente mais tarde. O suporte já foi informado e isso será corrigido.",
                icon: 'error',
            }).then((value) => {
                $('#SubmitButton').removeAttr("disabled");
            });
    });

    return false; 
});


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

//Realiza Callback com os dados dados retornados pela viacep
function meu_callback(conteudo) {
	if (!("erro" in conteudo)) {
		//Atualiza os campos com os valores.
		document.getElementById('rua').value = (conteudo.logradouro);
		document.getElementById('bairro').value = (conteudo.bairro);
		document.getElementById('cidade').value = (conteudo.localidade);
		document.getElementById('uf').value = (conteudo.uf);

		var locale = conteudo.localidade;

        $("#data-pedido").prop("disabled", false); 

		//Identifica a localidade do CEP via PHP/AJAX
		$.ajax({
			url: "../includes/classes/IdentifyLocale.php",
			type: "GET",
			data: { locale },
			dataType: 'json',
			processData: true,
			contentType: false,
			success: function (feedback) {
				generate_checkout(feedback.in_stock, feedback.locale_id);
			}
		});
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

//Exibe o checkout com base na localidade
function generate_checkout(in_stock, locale_id) {

	$('#in-stock-checkout').addClass('d-none');
	$('#no-stock-checkout').addClass('d-none');
	$('#delivery-resp-msg').addClass('d-none');

	if (in_stock > 0) {
		$('#in-stock-checkout').removeClass('d-none');
		$('#delivery-resp-msg').removeClass('d-none');
		$('#submit-btn').text("Concluir Pedido");
		$('#hdn-inpt-action').val("done-order");
	} else {
		$('#no-stock-checkout').removeClass('d-none');
		$('#submit-btn').text("Prosseguir para Pagamento");
		$('#hdn-inpt-action').val("proceed-to-checkout");
	}
}


function limpa_formulário_cep() {
	//Limpa valores do formulário de cep.
	document.getElementById('rua').value = ("");
	document.getElementById('bairro').value = ("");
	document.getElementById('cidade').value = ("");
	document.getElementById('uf').value = ("");
}

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
};

$('#aplicar-cupom').click(function () {
    var u = location.protocol + "//" + window.location.hostname + "/seguro.dropexpress";

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


// pickadate configuração e bloqueio de dias
$(document).ready(function () {
    $("#data-pedido-wrapper").click(function () {
        getDeliveryDays();
    });
});

function getDeliveryDays() {
    let picker = $(".datepicker-checkout").pickadate("picker");
    picker.set("disable", [1, 2, 3, 4, 5, 6, 7]);
    var u = location.protocol + "//" + window.location.hostname + "/seguro.dropexpress";

    let action = "get-delivery-days";
    let uf = $("#uf").val();
    let cidade = $("#cidade").val();
    let produto = $("#product").val();

    // $("#data-pedido").attr("disabled", true);
    $("#data-pedido").val("");

    if (uf === "" && cidade === "") {        
        Swal.fire({
            title: "Cidade e/ou Estado não infomados",
            text: "Por favor preencha os campos de CEP, Estado e Cidade para conseguir marcar a data de entrega",
            icon: "warning",
        });
        return;
    }
    // Busca os dias de delivery via ajax
    $.ajax({
        url: u + "/ajax/checkout-ajax.php",
        type: "GET",
        data: {
            action,
            uf,
            cidade,
            produto
        },
        dataType: "json",
        processData: true,
        contentType: false,
        beforeSend: function(){
            display_loader();
        } ,
        complete: function() {
            display_loader(false);
        }, 
        success: function (feedback) {
            
            if(feedback.status !== 1){
                Swal.fire({
                    title: feedback.title,
                    text: feedback.msg,
                    icon: feedback.type
                });
                return 
            } 

            if (feedback.status == 1 && !feedback.delivery_days) { 
                Swal.fire({
                    text: "",
                    title: "Tivemos um problema, atualize a página ou tente realizar a compra mais tarde.",
                    icon: "warning",
                });                
                return
            } 

            picker.set("enable", JSON.parse(feedback.delivery_days));
            $("#data-pedido").focus();
        },
    });
}

(function ($) {
    "use strict";
    // checa a hora atual
    let hours = new Date().getHours();
    //date picker classic default
    $(".datepicker-checkout").pickadate({
        firstDay: 0,
        // se for depois de 21h só permite marcar a entrega para 2 dias no futuro
        // antes das 21 pode marcar no dia seguinte
        min: hours >= 21 ? +2 : +1,
        max: +7,
        disable: [1, 7],
    });

    $("#data-pedido").val("");
})(jQuery);


display_loader = (verify = true) => {
    if (verify) {
      $("#preloader").css("display", "flex");
      $("#preloader").css("background-color", "hsl(0 0% 100% / 40%)");
      $("#preloader .sk-three-bounce").css("background-color", "unset");
      $("#preloader").css("z-index", "1000");
      return "atualizando...";
    }
    $("#preloader").css("display", "none");
    $("#preloader").css("z-index", "unset");
    $("#preloader").css("background-color", "white");
    $("#preloader .sk-three-bounce").css("background-color", "white");
    return "liberado...";
}