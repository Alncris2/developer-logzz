/* AJAX */
$(document).ready(function ($) {
    $('#ResetPasswordForm').submit(function () {

        // Captura os dados do formulário
        var ResetPasswordForm = document.getElementById('ResetPasswordForm');

        // Instância o FormData passando como parâmetro o formulário
        var formData = new FormData(ResetPasswordForm);

        // Envia O FormData através da requisição AJAX
        $.ajax({
            url: "../../ajax/reset-password-ajax.php",
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
                Swal.fire({
                    title: feedback.title,
                    text: feedback.msg,
                    icon: feedback.type,
                }).then((value) => {
                    if (feedback.type == 'success') {
                        document.location.assign(feedback.url);
                    }
                });
            }
            }).fail(function (data) {
            	Swal.fire({
            		title: "Erro Interno.",
            		text: "Não foi possível alterar sua senha. Contate o suporte.",
            		icon: 'error',
            	});
        });

        return false;
    });

    function progress(timeleft, timetotal, $element) {
        var progressBarWidth = $element.width() / timetotal;
        $element.find('div').animate({ width: progressBarWidth }, timeleft == timetotal ? 0 : 5300, "swing");
        if (timeleft > 0) {
            setTimeout(function () {
                progress(timeleft + 1, timetotal, $element);
            }, 5300);
        }
    };

    progress(200, 1, $('#progress-bar'));

});