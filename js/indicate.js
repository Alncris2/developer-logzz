/* AJAX */
$(document).ready(function($) {

    var u = location.protocol + "//" + window.location.hostname;

    //ABRIR MODAL 1
    $("#helper").click(function() {
        Swal.fire({
            text: 'Você receberá uma comissão todas as vezes que um usuário recrutado por você cadastrar um produto e realizar um pedido de status COMPLETO ou ENVIADO',
            customClass: {
                confirmButton: 'btn btn-rounded btn-success',
            },
            buttonsStyling: false
        })
    });

    $('#recruited-list').DataTable({
        searching: false,
        paging: true,
        select: true,
        lengthChange: false,
        "language": {
            "lengthMenu": "_MENU_ envios por página",
            "zeroRecords": "Você ainda não recrutou ninguêm",
            "info": "Página _PAGE_ de _PAGES_",
            "infoEmpty": "Nenhum recrutado para exibir aqui.",
            "search": "Filtrar",
            "paginate": {
                "next": ">>",
                "previous": "<<"
            },
            "infoFiltered": "(filtrando de _MAX_ envios, no total.)"
        }
    });

    $('.billing-request').click(function() {

        event.preventDefault();

        let valueSelected = null;
        const value = $('#valor-saque').val();

        $('[name="bank-account-to-transfer"]').each(function (){
            if($(this).is(':checked')){

                valueSelected = $(this)[0].value;
            }
        });

 
        if(value == ""){
            Swal.fire({
                title: "Valor do saque deve ser informado",
                icon: "warning",
            });

            return false;

        }

        if(valueSelected == null){
            Swal.fire({
                title: 'Falha ao realizar tentativa de saque',
                text: 'Verifique o banco selecionado e tente novamente',
                icon: 'error'
            })

            return false;

        }

        const action = this.getAttribute('data-action');
        const type = "commission";
        const url = u + "/ajax/billing-request.php";

        // Envia os parâmetro para o PHP via AJAX
        $.ajax({
            url: url,
            type: "GET",
            data: { action, value, type, account: valueSelected },
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
});