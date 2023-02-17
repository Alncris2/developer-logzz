/* AJAX */
$(document).ready(function ($) {

    var u = location.protocol + "//" + window.location.hostname;

    jQuery('.filter-btn').on('click', function () {
        jQuery('.chatbox').addClass('active');
    });
    jQuery('.chatbox-close').on('click', function () {
        jQuery('.chatbox').removeClass('active');
    });

    $('#new-membership-btn').click(function () {

        event.preventDefault();

        var url = u + "/ajax/new-membership.php";
        
        var id = this.getAttribute('data-id');

                $.ajax({
                    url: url,
                    type: "GET",
                    data: { id },
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
                                document.location.assign(feedback.url);
                            }
                        });
                    }
                });

        return false;
    });

    
    $('.update-membership-status').click(function () {

        event.preventDefault();

        var url = u + "/ajax/update-membership-status.php";
        var id = this.getAttribute('data-id');
        var status = this.getAttribute('data-status');

        $.ajax({
            url: url,
            type: "GET",
            data: { id, status },
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
                        document.location.assign(feedback.url);
                    }
                });
            }
        });

        return false;
    });

    $('.copy-hotcode-btn').click(function () {

        event.preventDefault();
        
        var copyText = this.getAttribute('data-link');
        // copyText.select();
        // copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText);

        toastr.success("", "Link Copiado!", {
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

    // Update SaleForm Submit
    $('#MembershipSaleUpdate').submit(function () {

        // Captura os dados do formulário
        var MembershipSaleUpdate = document.getElementById('MembershipSaleUpdate');

        // Instancia o FormData passando como parâmetro o formulário
        var formData = new FormData(MembershipSaleUpdate);

        var url = u + "/ajax/membership-update-sale-ajax.php";

        // Envia O FormData através da requisição AJAX
        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function (feedback) {
                Swal.fire({
                    title: feedback.title,
                    text: feedback.msg,
                    icon: feedback.type,
                }).then((value) => {
                    if (feedback.type == 'success'){
                        document.location.reload(true);
                    }
                });
            }
        });

        return false;
    });


    $('#sent-requests').DataTable({
        paging: true,
        searching: true,
        select: true,
        lengthChange: false,
        ordering: false,
        language: {
            "lengthMenu": "_MENU_ solicitações por página",
            "zeroRecords": "Sem resultados para sua busca",
            "info": "Página _PAGE_ de _PAGES_",
            "infoEmpty": "Nenhuma solicitação para exibir aqui.",
            "search": "Filtrar",
            "paginate": {
                "next": ">",
                "previous": "<"
            },
            "infoFiltered": "(filtrando de _MAX_ solicitações, no total.)"
        }
    });

    $('.categoria-produto-select').select2({
    });

    $(".categoria-produto-select").change(function () {
        var multipleValues = $(".categoria-produto-select").val();
        $("#categoria-produto-select-text").val(multipleValues);
    });

});
