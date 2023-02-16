/* AJAX */
$(document).ready(function ($) {

    var u = location.protocol + "//" + window.location.hostname;

    jQuery('.filter-btn').on('click', function () {
        jQuery('.chatbox').addClass('active');
    });
    jQuery('.chatbox-close').on('click', function () {
        jQuery('.chatbox').removeClass('active');
    });

    //Update order status from /pedidos in ADM account.
    $('.delete-order-link').click(function () {

        event.preventDefault();
        
        var order = this.getAttribute('data-id');

        Swal.fire({
            title: 'Tem certeza?',
            text: "Isso apagará todos os dados do pedido, incluindo upsell.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#2BC155',
            cancelButtonColor: '#FF6D4D',
            confirmButtonText: 'Sim, deletar',
            cancelButtonText: 'Não, cancelar'
        }).then((result) => {
            if (result.isConfirmed) {

                var url = u + "/ajax/delete-order.php";

                $.ajax({
                    url: url,
                    type: "GET",
                    data: { order },
                    dataType: 'json',
                    processData: true,
                    contentType: false,
                    success: function (feedback) {
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
                // }).fail(function (data) {
                //     Swal.fire({
                //         title: "Erro de Conexão",
                //         text: "Quando a página atulizar, tente mudar o status do pedido novamente.",
                //         icon: 'warning',
                //     }).then((value) => {
                //         window.open("pedidos/", '_self');
                //     });
                });
            }
        });

        return false;
    });

    // Deletar solicitação de integração
    $('.deleteSolicitation').each(function () {
        $(this).on('click', function(){
             const idToDeleteSolicitation = $(this)[0].dataset.idtodelete;
             const url = $(this)[0].dataset.url;
             Swal.fire({
                 title: 'Deseja deletar essa solicitação?',
                 text: 'Essa integração será apagada',
                 icon: 'warning',
                 showCancelButton: true,
             }).then(function(isConfirm) {
                 if (isConfirm.isConfirmed) {
                     $.ajax({
                         type: "POST",
                         url: '/api/v1/delete-solicitation-of-integration',
                         dataType: "json",
                         data: { "idToDelete": idToDeleteSolicitation, "url": url},
                         success: function(data) {
                             console.log(data);
                             if(data.status == "ok"){
                                 window.location.reload();
                             }
                         }
                     });
                 } 
             });
         }); 
     });
     
    $("#select-affiliates").change(function () {
        var multipleValues = $("#select-affiliates").val();
        $("#text-select-affiliates").val(multipleValues);
    });

});
