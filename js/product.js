/* AJAX */
$(document).ready(function ($) {
    var u = location.protocol + "//" + window.location.hostname;

    var u = location.protocol + "//" + window.location.hostname;
    $(".peso").mask("00/00/0000");
    $(".weight").mask("0.000");

    $('#descricao-produto').summernote({       
        lang: 'pt-BR',
    });

    $('#btn-product-image').click(function() {
        document.getElementById('input-file-product-image').click();
    });

    // Carrega a imagem selecionada no elemento <img>
    $("#input-file-product-image").on("change", function() {
        var files = !!this.files ? this.files : [];
        if (!files.length || !window.FileReader) return;

        if (/^image/.test(files[0].type) || /^video/.test(files[0].type)) {
            var reader = new FileReader();
            reader.readAsDataURL(files[0]);

            reader.onload = function() {  
                if (/^image/.test(files[0].type)) {
                    $("#product-image").show();
                    $("#product-image").attr('src', this.result);
                    $("#product-video").hide();
                } else {
                    $("#product-image").hide();
                    $("#product-video").attr('src', this.result);
                    $("#product-video").show();
                }
            }
        }
    });

    $('.btn-remove-image').on('click', function() {
        let imageDefault = u + '/images/product/placeholder.jpg';
        let index = $(this).data('index');
        var files = $('#input-file-product-images')[0].files; 
        var fileBuffer = new DataTransfer();
        
        for (let i = 0; i < files.length; i++) {
            if (index !== i)
            fileBuffer.items.add(files[i]);
        }
        
        $('#input-file-product-images')[0].files = fileBuffer.files;
        
        var product_images = $(".product-images");

        for (let i = 0; i < product_images.length; i++) {
            const element = product_images[i];
            element.src = imageDefault;
        }

        loadImages(fileBuffer.files);

    });

    // Abre o input parar carregar várias imagens
    let oldFiles;
    $('#btn-product-images').click(function() {
        oldFiles = $('#input-file-product-images')[0].files;
        document.getElementById('input-file-product-images').click();
    });

    // Carrega as imagens selecionadas nos elementos <img class="product-images">
    $("#input-file-product-images").on("change", function() {
        var files = !!this.files ? this.files : [];
        
        let fileBuffer = new DataTransfer();
        
        for (let i = 0; i < oldFiles.length; i++) {
            fileBuffer.items.add(oldFiles[i]);   
        }
        
        for (let i = 0; i < files.length; i++) {
            fileBuffer.items.add(files[i]);
        }
        
        if (fileBuffer.files.length <= 9) {
            $('#input-file-product-images')[0].files = fileBuffer.files;
            loadImages(files);
        } else  {
            $('#input-file-product-images')[0].files = oldFiles;
            Swal.fire({
                title: "Algo está errado",
                text: "O número máximo de imagens é 9",
                icon: 'error',
            });
        }

    });

    function loadImages( files ) {
        let imageDefault = u + '/images/product/placeholder.jpg';
        
        if (!files.length || !window.FileReader) return;

        var product_images = $(".product-images");

        let product_images_filtered = []
        for (let i = 0; i < product_images.length; i++) {
            if (product_images[i].src === imageDefault) {
                product_images_filtered.push(product_images[i]);
            }
        }

        for (let i = 0; i < files.length; i++) {
            const element = product_images_filtered[i];     
        
            if (/^image/.test(files[i].type)) {
                var reader = new FileReader();
                reader.readAsDataURL(files[i]);

                reader.onload = function() {
                    element.src = this.result;
                }
            }
        }
    }

    $(".comissao-personalizada-btn").click(function () {
        $(".comissao-personalizada-input").attr(
            "style",
            "display: block !important"
        );
        $(".comissao-personalizada-btn-container").attr("style", "display: none");
    });

    jQuery(".filter-btn").on("click", function () {
        jQuery(".chatbox").addClass("active");
    });
    jQuery(".chatbox-close").on("click", function () {
        jQuery(".chatbox").removeClass("active");
    });

    $("#copy-member-invite-link-btn").click(function () {
        event.preventDefault();

        var copyText = this.getAttribute("data-link");
        // copyText.select();
        // copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText);

        toastr.success(copyText, "Link de Convite Copiado!", {
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
            tapToDismiss: !0,
        });

        return false;
    });

    $(".categoria-produto-select").select2({});

    $(".categoria-produto-select").change(function () {
        var multipleValues = $(".categoria-produto-select").val();
        $("#categoria-produto-select-text").val(multipleValues);
    });

    $("#tipo-afiliacao-select").change(function () {
        var value = $("#tipo-afiliacao-select").val();
        $("#tipo-afiliacao").val(value);
    });

    $("#produto-disponivel-afiliacao").change(function () {
        var switche = document.getElementById("produto-disponivel-afiliacao");

        if ($(switche).is(":checked")) {
            $("#disponivel-afiliacao").val("sim");
            $(".campos-disponivel-afiliacao").removeClass("d-none");
        } else {
            $("#disponivel-afiliacao").val("nao");
            $(".campos-disponivel-afiliacao").addClass("d-none");
        }
    });

    $("#produto-visivel-afiliacao").change(function () {
        var switche = document.getElementById("produto-visivel-afiliacao");

        if ($(switche).is(":checked")) {
            $("#visivel-afiliacao").val("sim");
            $("#visivel-afiliacao-caption").text(
                "O produto será exibido para todos os afiliados."
            );
        } else {
            $("#visivel-afiliacao").val("nao");
            $("#visivel-afiliacao-caption").text(
                "O produto só será visto por afiliados convidados."
            );
        }
    });

    $("#produto-afiliacao-automatica").change(function () {
        var switche = document.getElementById("produto-afiliacao-automatica");

        if ($(switche).is(":checked")) {
            $("#afiliacao-automatica").val("sim");
            $("#afiliacao-automatica-caption").text(
                "Os afiliados terão suas solicitações aprovadas automaticamente."
            );
        } else {
            $("#afiliacao-automatica").val("nao");
            $("#afiliacao-automatica-caption").text(
                "Os afiliados precisarão aguardar aprovação."
            );
        }
    });

    // Add product form submit
    $("#AddProductForm").submit(function () {
        // Captura os dados do formulário
        var AddProductForm = document.getElementById("AddProductForm");

        // Instância o FormData passando como parâmetro o formulário
        var formData = new FormData(AddProductForm);

        // Envia O FormData através da requisição AJAX
        $.ajax({
            url: "../../ajax/add-product-ajax.php",
            type: "POST",
            data: formData,
            dataType: "json",
            contentType: false,
            processData: false,
            beforeSend: function () {
                display_loader();
            },
            complete: function () {
                display_loader(false);
            },
            success: function (feedback) {
                if (feedback.status == 0) {
                    Swal.fire({
                        title: feedback.title,
                        text: feedback.msg,
                        icon: "error",
                    });
                } else {
                    Swal.fire({
                        title: feedback.title,
                        text: feedback.msg,
                        icon: "success",
                    }).then((value) => {
                        window.open(feedback.url, "_self");
                    });
                }
            },
        }).fail(function (data) {
            Swal.fire({
                title: "Algo deu errado...",
                text: "Atualize a página e tente novamente.",
                icon: "warning",
            });

            $("#SubmitButton").prop("disabled", false);
        });

        return false;
    });

    // Add product form submit
    $("#UpdateProductForm").submit(function () {
        // Captura os dados do formulário
        var UpdateProductForm = document.getElementById("UpdateProductForm");

        // Instância o FormData passando como parâmetro o formulário
        var formData = new FormData(UpdateProductForm);

        var url = u + "/ajax/add-product-ajax.php";

        // Envia O FormData através da requisição AJAX
        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            dataType: "json",
            processData: false,
            contentType: false,
            beforeSend: function () {
                display_loader();
            },
            complete: function () {
                display_loader(false);
            },
            success: function (feedback) {
                if (feedback.status == 0) {
                    Swal.fire({
                        title: feedback.title,
                        text: feedback.msg,
                        icon: "warning",
                    });
                } else {
                    Swal.fire({
                        title: feedback.title,
                        text: feedback.msg,
                        icon: "success",
                    }).then((value) => {
                        window.open(feedback.url, "_self");
                    });
                }
            },
            // }).fail(function (data) {
            // 	Swal.fire({
            // 		title: "Algo deu errado...",
            //         text: "Atualize a página e tente novamente.",
            // 		icon: 'warning',
            // 	});
        });

        return false;
    });

    $("#UpdateMembershipConfigForm").submit(function () {
        // Captura os dados do formulário
        var UpdateMembershipConfigForm = document.getElementById(
            "UpdateMembershipConfigForm"
        );

        // Instância o FormData passando como parâmetro o formulário
        var formData = new FormData(UpdateMembershipConfigForm);

        var url = u + "/ajax/update-membership-configs.php";

        // Envia O FormData através da requisição AJAX
        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            dataType: "json",
            processData: false,
            contentType: false,
            beforeSend: function(){
                display_loader();
            } ,
            complete: function() {
                display_loader(false);
            },
            success: function (feedback) {
                if (feedback.status == 0) {
                    Swal.fire({
                        title: feedback.title,
                        text: feedback.msg,
                        icon: "warning",
                    });
                } else {
                    Swal.fire({
                        title: feedback.title,
                        text: feedback.msg,
                        icon: "success",
                    }).then((value) => {
                        window.open(feedback.url, "_self");
                    });
                }
            },
            // }).fail(function (data) {
            //     Swal.fire({
            //         title: "Algo deu errado...",
            //         text: "Atualize a página e tente novamente.",
            //         icon: 'warning',
            //     });
        });

        return false;
    });

    $("#link-url-one-clique-to-copy").click(function () {
        event.preventDefault();
        //$("#url-checkout-to-copy").select();
        var copyText = document.getElementById("url-one-clique-to-copy");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.value);
        toastr.success("Você copiou a URL de Checkout com 1 Clique.", "Copiado!", {
            timeOut: 1500,
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
            tapToDismiss: !0,
        });
    });

    //Update order status from /pedidos in ADM account.
    $("#DeleteProductLink").click(function () {
        event.preventDefault();

        var id = this.getAttribute("data-id");

        Swal.fire({
            title: "Tem certeza?",
            text: "A exclusão de produtos não pode ser desfeita.",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#2BC155",
            cancelButtonColor: "#FF6D4D",
            confirmButtonText: "Sim, excluir",
            cancelButtonText: "Não, cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                var url = u + "/ajax/delete-product-ajax.php";

                $.ajax({
                    url: url,
                    type: "GET",
                    data: { id },
                    dataType: "json",
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
                            if (feedback.type == "success") {
                                document.location.reload(true);
                            }
                        });
                    },
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

    // Trash the coupon.
    $(".delete-coupon-status").click(function () {
        event.preventDefault();

        var url = u + "/ajax/update-coupon-ajax.php";
        var coupon = this.getAttribute("data-cupom");
        var product = this.getAttribute("data-produto");
        var status = this.getAttribute("data-status");
        var action = "delete-coupon";

        // Envia os parâmetros para o PHP via AJAX
        $.ajax({
            url: url,
            type: "GET",
            data: { coupon, product, status, action },
            dataType: "json",
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
                    if ((feedback.type = "success")) {
                        document.location.reload(true);
                    }
                });
            },
        });

        return false;
    });

    $(".copy-user-code").click(function () {
        event.preventDefault();

        var copyText = this.getAttribute("data-code");
        navigator.clipboard.writeText(copyText);

        toastr.success(copyText, "Código de Usuário Copiado!", {
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
            tapToDismiss: !0,
        });

        return false;
    });

    $("#members-list").DataTable({
        paging: true,
        searching: true,
        select: true,
        lengthChange: false,
        dom: "Bfrtip",
        buttons: [
            "csv",
            "excel",
            {
                extend: "pdf",
                title: "Afiliados - DropExpress",
                footer: true,
            },
        ],
        language: {
            lengthMenu: "_MENU_ cupons por página",
            zeroRecords: "Sem resultados para sua busca",
            info: "Página _PAGE_ de _PAGES_",
            infoEmpty: "Nenhuma oferta para exibir aqui.",
            search: "Filtrar",
            paginate: {
                next: ">",
                previous: "<",
            },
            infoFiltered: "(filtrando de _MAX_ cupons, no total.)",
        },
    });

    // $('#coupons-datatable').DataTable({
    //         paging: true,
    //         searching: true,
    //         select: true,
    //         lengthChange: false,
    //         "language": {
    //             "lengthMenu": "_MENU_ cupons por página",
    //             "zeroRecords": "Sem resultados para sua busca",
    //             "info": "Página _PAGE_ de _PAGES_",
    //             "infoEmpty": "Nenhuma oferta para exibir aqui.",
    //             "search": "Filtrar",
    //             "paginate": {
    //                 "next": ">",
    //                 "previous": "<"
    //             },
    //             "infoFiltered": "(filtrando de _MAX_ cupons, no total.)"
    //         }
    //     });
});
