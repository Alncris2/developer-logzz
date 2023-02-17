
$(document).ready(function ($) {
    
    // ABRIR MODAL HELPER PARA "Localidade de centros de distribuição" SELECT
    $("#helper").click(function() {
        Swal.fire({
            icon: 'info',
            width: '50%',
            text: 'Tendo seu produto em estoque nesse centro de distribuição você poderá utilizar o Dropexpress como logística tradicional',
            confirmButtonText: 'Certo'
        })
    });
    
    // ABRIR MODAL HELPER PARA "Localidade de centros de distribuição" SELECT
    $("#helperLocal").click(function() {
        Swal.fire({
            icon: 'info',
            width: '50%',
            text: 'Tendo seu produto em estoque nessa listagem você poderá oferecer entrega em 1 dia, agendamento de entrega em 1 dia com pagamento físico direto com o nosso time de entregadores',
            confirmButtonText: 'Certo'
        })
    });
    
    // APARECER INPUT DE TIPO DE LOCALIDADE DE ACORDO COM OPÇÃO ESCOLHIDA
    $('#select-locale-center').on('change', function(){
        const valueSelected = $(this).val();
        
        $('.appearOrNot').each(function(){
           $(this).addClass('d-none'); 
        });
        
        // console.log($(`[data-idCenter="${valueSelected}"]`));
        
        $(`[data-idCenter="${valueSelected}"]`).removeClass('d-none');
        $(`[data-idCenter="${valueSelected}"]`).addClass('activeLocale');
        
        console.log($('.activeLocale #select-ship-locale'));
        
    });
    
    $('.btn-copy-address').click(function () {

        event.preventDefault();

        var copyText = this.getAttribute('data-text');
        navigator.clipboard.writeText(copyText);

        toastr.success(copyText, "Endereço Copiado!", {
            timeOut: 4000,
            closeButton: !0,
            debug: !1,
            newestOnTop: !0,
            progressBar: !0,
            positionClass: "toast-top-right",
            preventDuplicates: !0,
            onclick: null,
            showDuration: "400",
            hideDuration: "1000",
            extendedTimeOut: "1000",
            showEasing: "swing",
            hideEasing: "linear",
            showMethod: "fadeIn",
            hideMethod: "fadeOut",
            tapToDismiss: !0
        })

        return false;
    });


    $('#ReportShipForm').submit(function (e) {
        
        e.preventDefault();
        
        var select_product = $('#select-ship-product').val();

        if (select_product == null) {
            Swal.fire({
                title: "Esqueceu alguma coisa?",
                text: "Confira se você selecionou o Produto e Localidade.",
                icon: 'warning',
            })
            return false;
        }

        // Captura os dados do formulário
        var ReportShipForm = document.getElementById('ReportShipForm');

        // Instância o FormData passando como parâmetro o formulário
        var formData = new FormData(ReportShipForm);

        // Envia O FormData através da requisição AJAX
        $.ajax({
            url: "../../ajax/add-report-invetory-ship-ajax.php",
            type: "POST",
            data: formData,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function (feedback) {
                if (feedback.status > 0) {
                    Swal.fire({
                        title: "Sucesso!",
                        text: "Solicitação de Envio Relizada!",
                        icon: 'success',
                    }).then((value) => {
                        window.location.assign('../envios-realizados/');
                    });
                } else {
                    Swal.fire({
                        title: "Erro!",
                        text: feedback.msg,
                        icon: 'error',
                    })
                }
            }
        });

        return false;
    });

});