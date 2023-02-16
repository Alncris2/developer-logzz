$(document).ready(function ($) {

    var batatinha = "123";

    $.ajax({
        url: "ajax/external-postback/braip.php",
        type: "POST",
        data: { batatinha },
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
                alert('Ok.');
            } else {
                alert('Erro.');
            }
        }
    });
});