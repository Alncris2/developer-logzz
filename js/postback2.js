/* Postback AJAX */
$(document).ready(function ($) {
    
    // $('#IntegrationMonetizze').submit(function () {
    //     var integration_product_id = $("#select-integration-product").val();
    //     if (integration_product_id === null) {
    //         Swal.fire({
    //             title: "Selecione um Produto",
    //             text: "Você precisa selecionar um produto cadastrado para integrar.",
    //             icon: 'warning',
    //         })
    //         return false;
    //     }
    // });
    
      $('#IntegrationMonetizze').submit(function () {
        var integration_product_id = $("#select-integration-product").val();

        if (integration_product_id == null) {
            Swal.fire({
                title: "Selecione um Produto",
                text: "Você precisa selecionar um produto cadastrado para integrar.",
                icon: 'warning',
            })
            return false;
        }

        $('.btn-loading-icon-change').removeClass('fa-link');
        $('.btn-loading-icon-change').addClass('fa-cog');
        $('#SubmitButton').attr('disabled', 'disabled');

        // Captura os dados do formulário
        var IntegrationMonetizze = document.getElementById('IntegrationMonetizze');

        // Instancia o FormData passando como parâmetro o formulário
        var formData = new FormData(IntegrationMonetizze);

        // Envia O FormData através da requisição AJAX
        $.ajax({
            url: "../../../ajax/external-postback/monetizze.php",
            type: "POST",
            data: formData,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function (feedback) {
                if (feedback.status == 1) {
                    var url = feedback.url;
                    Swal.fire({
                        title: "Sucesso!",
                        text: feedback.msg,
                        icon: 'success',
                    }).then((value) => {
                        $("#url-postback").val(feedback.url);
                        $("#postback-url-genereted").removeClass('d-none');
                    });
                } else {
                    Swal.fire({
                        title: "Erro!",
                        text: feedback.msg,
                        icon: 'error',
                    });
                }
            }
        });

        return false;
    });
    
    $('#IntegrationBraip').submit(function () {
        var integration_product_id = $("#select-integration-product").val();

        if (integration_product_id == null) {
            Swal.fire({
                title: "Selecione um Produto",
                text: "Você precisa selecionar um produto cadastrado para integrar.",
                icon: 'warning',
            })
            return false;
        }

        $('.btn-loading-icon-change').removeClass('fa-link');
        $('.btn-loading-icon-change').addClass('fa-cog');
        $('#SubmitButton').attr('disabled', 'disabled');

        // Captura os dados do formulário
        var IntegrationBraip = document.getElementById('IntegrationBraip');

        // Instancia o FormData passando como parâmetro o formulário
        var formData = new FormData(IntegrationBraip);

        // Envia O FormData através da requisição AJAX
        $.ajax({
            url: "../../../ajax/external-postback/braip.php",
            type: "POST",
            data: formData,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function (feedback) {
                if (feedback.status == 1) {
                    var url = feedback.url;
                    Swal.fire({
                        title: "Sucesso!",
                        text: feedback.msg,
                        icon: 'success',
                    }).then((value) => {
                        $("#url-postback").val(feedback.url);
                        $("#postback-url-genereted").removeClass('d-none');
                    });
                } else {
                    Swal.fire({
                        title: "Erro!",
                        text: feedback.msg,
                        icon: 'error',
                    });
                }
            }
        });

        return false;
    });

    
    $('#EditIntegration').submit(function () {
        var integration_product_id = $("#select-integration-product").val();
        if (integration_product_id === null) {
            Swal.fire({
                title: "Selecione um Produto",
                text: "Você precisa selecionar um produto cadastrado para integrar.",
                icon: 'warning',
            })
            return false;
        }
    });
    

    $('.bling-uf-list').on("select2:select", function (e) { 
        var data = e.params.data.text;
        if(data=='Selecionar todos as UFs'){
            $(".bling-uf-list > option").prop("selected","selected");
            $(".bling-uf-list").trigger("change");
        }
    });
    
    
    
    $("#url-postback-copy").click(function () {
        event.preventDefault();

        var copyText = $("#url-postback").val();
        // copyText.select();
        // copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText);

        toastr.success("Você copiou a URL de Retorno.", "Copiado!", {
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

    });

    // $("#select-integration-product").change(function () {
    //     var multipleValues = $("#select-integration-product").val();
    //     $("#text-integration-product").val(multipleValues);
    // });
    
});
