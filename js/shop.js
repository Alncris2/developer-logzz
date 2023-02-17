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

    $('.locale-list-btn').click(function () {

        var url = u + "/ajax/load-locale-range.php";

        var id = this.getAttribute('data-id');

        $.ajax({
            url: url,
            type: "GET",
            data: { id },
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
                $('#locale-name').html('');
                $('#locale-name').html(feedback.locale_title);
                $('#range-list').html('');
                $('#range-list').html(feedback.range_list);
            }
        });
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
                        document.location.reload(true);
                    }
                });
            }
        }).fail( function(data) {
            Swal.fire({
                title: "Erro de Conexão",
                text: "Quando a página atulizar, tente atualizar novamente.",
                icon: 'warning',
            }).then((value) => {
                document.location.reload(true);
            }); 
        });

        return false;
    });

    // Update RedirectForm Submit
    $('#MembershipRedirectUpdate').submit(function () {

        // Captura os dados do formulário
        var MembershipRedirectUpdate = document.getElementById('MembershipRedirectUpdate');

        // Instancia o FormData passando como parâmetro o formulário
        var formData = new FormData(MembershipRedirectUpdate);

        var url = u + "/ajax/membership-update-redirect-ajax.php";

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
        }).fail( function(data) {
            Swal.fire({
                title: "Erro de Conexão",
                text: "Quando a página atulizar, tente atualizar novamente.",
                icon: 'warning',
            }).then((value) => {
                document.location.reload(true);
            }); 
        }); 

        return false;
    });

    var array = []

    $('#checkAll').change(function () {
        
        if ($('#checkAll').is(":checked")) {
            var checkboxes = document.querySelectorAll('input[type=checkbox]:checked')
            
            array = []

            for (var i = 0; i < checkboxes.length; i++) {
                array.push(checkboxes[i].value);
            }
        } else {
            array = []
        }

        if (array.length > 0) {
            $('#multi-membership-selecteds').removeClass('d-none');
            $('.array-lenght').html('');
            $('.array-lenght').html(array.length - 1);
            $('.update-multi-membership-status').attr("data-array", array);
        } else{
            $('#multi-membership-selecteds').addClass('d-none');
            $('.array-lenght').html('');
        }
        
        //alert(array);
        
    });
    
    $('.check-this-request').change(function () {

        if ($(this).is(":checked")) {

            var checkbox = this.value;
            array.push(checkbox);

        } else {
            
            var checkbox = this.value;
            array = jQuery.grep(array, function (value) {
                return value != checkbox;
            });

        }

        if (array.length > 0) {
            $('#multi-membership-selecteds').removeClass('d-none');
            $('.array-lenght').html('');
            $('.array-lenght').html(array.length);
            $('.update-multi-membership-status').attr("data-array", array);
        } else{
            $('#multi-membership-selecteds').addClass('d-none');
            $('.array-lenght').html('');
        }
        
        //alert(array);
        
    });

    $('.update-multi-membership-status').click(function () {

        var url = u + "/ajax/update-membership-status.php";
        var status = this.getAttribute('data-status');
        var array = this.getAttribute('data-array');

        var list = array;

        var array = list.toString();

        $.ajax({
            url: url,
            type: "GET",
            data: { array, status },
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

