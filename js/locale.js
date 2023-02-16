
$(document).ready(function ($) {

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


    $('#ReportShipForm').submit(function () {

        var select_product = $('#select-ship-product').val();
        var select_locale = $('#select-ship-locale').val();

        if (select_product == null || select_locale == null) {
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
            beforeSend: function(){
                display_loader();
            } ,
            complete: function() {
                display_loader(false);
            },
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