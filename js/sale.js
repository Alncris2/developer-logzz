$(document).ready(function($) {

    var u = location.protocol + "//" + window.location.hostname;

    $('.maskPercent').mask('99.99%', {reverse: true})

    //ATIVA INPUT URL REDIRECT CASO PRODUTO NÃƒO TENHA ESTOQUE
    $('#produto-estoque').on('click', function() {
        if ($('#produto-estoque-label').is(":checked") === false) {
            $('#produto-estoque').html('&nbsp; Sim');
            $('#disponivel-estoque').show();
            $('[name="disponivel-estoque"]').attr('required', 'true');
        } else {
            $('#produto-estoque').html('&nbsp; Não');
            $('#disponivel-estoque').hide();
            $('[name="disponivel-estoque"]').val(null);
            $('[name="disponivel-estoque"]').removeAttr('required');
        }
    });

    //ATIVA INPUT COBRAR POR FRETE
    $('#produto-estoque-frete').on('click', function() {
        if ($('#produto-estoque-label-frete').is(":checked") === false) {
            $('#produto-estoque-frete').html('&nbsp; Sim');
            $('#disponivel-frete').show();
            $('[name="disponivel-frete"]').val("0,00");
            $('[name="disponivel-frete"]').attr('required', 'true');
        } else {
            $('#produto-estoque-frete').html('&nbsp; Não');
            $('#disponivel-frete').hide();
            $('[name="disponivel-frete"]').val(null);
            $('[name="disponivel-frete"]').removeAttr('required');
        }
    });

    //ATIVA INPUT URL REDIRECT APÃ“S CONCLUIR COMPRA 
    $('#produto-estoque-oferts').on('click', function() {
        if ($('#produto-estoque-label-oferts').is(":checked") === false) {
            $('#produto-estoque-oferts').html('&nbsp; Sim');
            $('#disponivel-oferts').show();
            $('[name="disponivel-oferts"]').attr('required', 'true');
        } else {
            $('#produto-estoque-oferts').html('&nbsp; Não');
            $('#disponivel-oferts').hide();
            $('[name="disponivel-oferts"]').val(null);
            $('[name="disponivel-oferts"]').removeAttr('required');
        }
    });

    //ATIVA INPUT ComissÃ£o Personalizada
    $('#produto-estoque-comission').on('click', function() {
        if ($('#produto-estoque-label-comission').is(":checked") === false) {
            $('#produto-estoque-comission').html('&nbsp; Sim');
            $('#disponivel-estoque-comission').show();
            $('[name="disponivel-estoque-comission"]').attr('required', 'true');
        } else {
            $('#produto-estoque-comission').html('&nbsp; Não');
            $('#disponivel-estoque-comission').hide();
            $('[name="disponivel-estoque-comission"]').val("0");
            $('#disponivel-estoque-comission').val("0");
            $('[name="disponivel-estoque-comission"]').removeAttr('required');
        }
    });
  // BOTÕES DE SUPORTE
  $('#select-whatsapp-lbl').on('click', function() {
    if ($('#select-whatsapp').is(":checked") === false) {
        $('#select-whatsapp-lbl').html('&nbsp; Sim'); // trocar label do botão para "sim"
        $('#support').show();
    } else {
        $('#select-whatsapp-lbl').html('&nbsp; Não');   
        $('#support').hide();

        $('[name="select-whatsapp-input"]').val(null);
        $('[name="select-email"]').val(null);
    }
});


$('#select-counter-lbl').on('click', function() {
    if ($('#select-counter').is(":checked") === false) {

        $('#select-counter-lbl').html('&nbsp; Sim'); // trocar label do botão para "sim"
        $('#components-counter').removeClass('d-none'); 
    } else {
        $('#select-counter-lbl').html('&nbsp; Não'); // trocar label do botão para "sim"
        $('#components-counter').addClass('d-none'); 
    }
});

$('#select-color-lbl').on('click', function() {
    if ($('#select-color').is(":checked") === false) {
        $('#select-color-lbl').html('&nbsp; Sim'); // trocar label do botão para "sim"
        $('#components-color').removeClass('d-none'); // 
    } else {
        $('#select-color-lbl').html('&nbsp; Não'); // trocar label do botão para "sim"
        $('#components-color').addClass('d-none'); // 
    }
});

$('#select-notification-lbl').on('click', function() {
    if ($('#select-notification').is(":checked") === false) {

        $('#select-notification-lbl').html('&nbsp; Sim'); // trocar label do botão para "sim"
        $('#components-notification').removeClass('d-none'); 
    } else {
        $('#select-notification-lbl').html('&nbsp; Não'); // trocar label do botão para "sim"
        $('#components-notification').addClass('d-none'); 
    }
});

$('#select-banner-lbl').on('click', function() {
    if ($('#select-banner').is(":checked") === false) {
  
        $('#select-banner-lbl').html('&nbsp; Sim'); // trocar label do botão para "sim"
        $('#components-banner').removeClass('d-none'); 
    } else {
        $('#select-banner-lbl').html('&nbsp; Não'); // trocar label do botão para "sim"
        $('#components-banner').addClass('d-none'); 
    }
  });

  $('#checkout-select-lbl').on('click', function() {
    if ($('#checkout-select').is(":checked") === false) {

        $('#checkout-select-lbl').html('&nbsp; Sim'); // trocar label do botão para "sim"
        $('#components-checkout').removeClass('d-none'); 

    } else {

        $('#checkout-select-lbl').html('&nbsp; Não'); // trocar label do botão para "sim"
        $('#components-checkout').addClass('d-none'); 
        $('[name="checkout"]').val('CHECKOUT_PADRÃO').change();
    }
  });

          
    //ABRIR MODAL 1
    $("#helper").click(function() {
        $("#exampleModalCenter").modal('show');
        $("#exampleModalCenter .text-justify").html("Deseja configurar uma comissão para essa oferta, diferente da padrão do produto ?");

    });

    //ABRIR MODAL 2
    $("#helper2").click(function() {
        $("#exampleModalCenter").modal('show');
        $("#exampleModalCenter .text-justify").html("Caso ative essa opção, iremos identificar a localização do seu cliente antes que a página de pedido seja aberta, redirecionando-o para uma URL externa em caso de insdisponibilidade de estoque onde ele esteja.");
    });

    //ABRIR MODAL 3
    $("#helper3").click(function() {
        $("#exampleModalCenter").modal('show');
        $("#exampleModalCenter .text-justify").html("<b>REDIRECIONAR PARA UMA PÁGINA COM UM PASSO A PASSO, EX:</b><p>1° passo: você precisará criar uma página no seu domínio, para realizar uma nova oferta, conforme o modelo de referência abaixo (MÍDIA EM IMAGEM MOSTRANDO UMA PÁGINA DE UPSELL)</p><p>2° passo: na tela de edição na sua oferta, clique para copiar a URL de compra com 1 clique (MÍDIA EM IMAGEM MOSTRANDO A TELA COM ESSA OPÇÃO)</p><p>3° passo: insira essa URL como URL de redirecionamento no botão de confirmação de compra da sua página de upsell</p><p>4° passo: ainda na tela de edição da sua oferta, marque a opção ''sim'' item de redirecionamento após conclusão do pedido.</p><p>Após isso, insira no campo a URL da página que possui o botão com a URL de compra com 1 clique configurado</p><p>Salve ou atualize sua oferta, e pronto, em 4 passos você poderá aumentar em até 3x sua lucratividade com essa funcionalidade poderosa oferecida pela Drop Express</p>");
    });


    $('#privacidade-oferta').change(function() {

        var switche = document.getElementById('privacidade-oferta');

        if ($(switche).is(':checked')) {
            $('#privacidade-oferta-text').val('1');
        } else {
            $('#privacidade-oferta-text').val('0');
        }
    });

    $('.switch-privacidade-oferta').change(function() {

        var sid = this.getAttribute('data-sid');
        var pid = this.getAttribute('data-pid');
        var url = u + "/ajax/update-sale-privacy.php";
        var action = 'update-sale-privacy';

        if ($(this).is(':checked')) {

            var status = 1;

            $.ajax({
                url: url,
                type: "GET",
                data: { pid, sid, status, action },
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
                        title: 'Status da Oferta Alterado',
                        text: 'Essa oferta está visível p/ afiliados',
                        icon: 'success',
                    });
                }
            });
        } else {

            var status = 0;

            $.ajax({
                url: url,
                type: "GET",
                data: { pid, sid, status, action },
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
                        title: 'Status da Oferta Alterado',
                        text: 'Essa oferta não está visível p/ afiliados',
                        icon: 'success',
                    });
                }
            })
        }
    });

    $('#UpdateSaleForm').submit(function() {

        // Captura os dados do formulário
        var UpdateSaleForm = document.getElementById('UpdateSaleForm');

        // Instância o FormData passando como parâmetro o formulário
        var formData = new FormData(UpdateSaleForm);

        var url = u + "/ajax/update-sale-ajax.php";

        // Envia O FormData através da requisição AJAX
        $.ajax({
            url: url,
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
                Swal.fire({
                    title: feedback.title,
                    text: feedback.msg,
                    icon: feedback.type,
                }).then((value) => {
                    if (feedback.type == 'success') {
                        document.location.reload(true);
                    }
                });
            }
        });

        return false;
    });

    $('.salvar-oferta-personalizada').click(function() {

        var sale_id = this.getAttribute('data-id');
        var hotcode = this.getAttribute('data-hotcode');
        var input = document.getElementById(sale_id);
        var value = input.value;

        if (value == "" || value < 1) {
            return false;
        }

        var feedback_msg = value + " para este afiliado.";

        var url = u + "/ajax/add-custom-commission.php";

        $.ajax({
            url: url,
            type: "GET",
            data: { value, sale_id, hotcode },
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
                    title: 'Comissão Personalizada Cadastrada!',
                    text: feedback_msg,
                    icon: 'success',
                });
            }
        });

        return false;

    });

    $('.excluir-oferta-personalizada').click(function() {

        var sale_id = this.getAttribute('data-id');
        var hotcode = this.getAttribute('data-hotcode');

        var input = document.getElementById(sale_id);

        $(input).val("");

        var feedback_msg = "Sem comissão personalizada para este afiliado.";

        var url = u + "/ajax/delete-custom-commission.php";

        $.ajax({
            url: url,
            type: "GET",
            data: { sale_id, hotcode },
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
                    title: 'Comissão Personalizada Removida!',
                    text: feedback_msg,
                    icon: 'success',
                });
            }
        });

        return false;

    });

    $('#sales-datatable').DataTable({
        paging: true,
        searching: true,
        select: true,
        lengthChange: false,
        "language": {
            "lengthMenu": "_MENU_ ofertas por página",
            "zeroRecords": "Sem resultados para sua busca",
            "info": "Página _PAGE_ de _PAGES_",
            "infoEmpty": "Nenhuma oferta para exibir aqui.",
            "search": "Filtrar",
            "paginate": {
                "next": "Próximo",
                "previous": "Anterior"
            },
            "infoFiltered": "(filtrando de _MAX_ ofertas, no total.)"
        }
    });

});