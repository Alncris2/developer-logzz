/* AJAX */
$(document).ready(function ($) {
    var u = location.protocol + "//" + window.location.hostname;

    jQuery(".filter-btn").on("click", function () {
        jQuery(".chatbox").addClass("active");
    });
    jQuery(".chatbox-close").on("click", function () {
        jQuery(".chatbox").removeClass("active");
    });

    $("#new-membership-btn").click(function () {
        event.preventDefault();

        var url = u + "/ajax/new-membership.php";

        var id = this.getAttribute("data-id");

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
                        document.location.assign(feedback.url);
                    }
                });
            },
        });

        return false;
    });

    // Change phone form submit
    $("#ChangePhoneForm").submit(function () {
        //Captura os dados do formulário
        var changePhoneForm = document.getElementById("ChangePhoneForm");

        // Instância o FormData passando como parâmetro o formulário
        var formData = new FormData(changePhoneForm);

        // Envia O FormData através da requisição AJAX
        $.ajax({
            url: u + "/ajax/update-information-ajax.php",
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
                        icon: "error",
                    });
                } else if (feedback.status == 1) {
                    Swal.fire({
                        title: feedback.title,
                        text: feedback.msg,
                        icon: "success",
                    }).then(() => {
                        document.location.reload(true);
                    });
                    //changePhoneForm.reset();
                } else if (feedback.status == 2) {
                    Swal.fire({
                        title: feedback.title,
                        text: feedback.msg,
                        icon: "info",
                    }).then(() => {
                        var div = $("#phoneCodeVerification");
                        div.append("<label>Código de verificação</label");
                        div.append(
                            "<input class='form-control' type='number' name='phone-verification-code'></input"
                        );
                        $.ajax({
                            url: u + "/sendmail/change_phone/" + feedback.id,
                            type: "GET",
                            data: feedback.code,
                            dataType: "json",
                            processData: false,
                            contentType: false,
                        });
                    });
                }
            },
        });
        return false;
    });

    // Add New Bank Account form submit
    $("#AddBankAccForm").submit(function () {
        //Captura os dados do formulário
        var AddBankAccForm = document.getElementById("AddBankAccForm");

        // Instância o FormData passando como parâmetro o formulário
        var formData = new FormData(AddBankAccForm);

        // Envia O FormData através da requisição AJAX
        $.ajax({
            url: u + "/ajax/add-bank-account.php",
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
                        icon: "error",
                    });
                } else {
                    Swal.fire({
                        title: feedback.title,
                        text: feedback.msg,
                        icon: "success",
                    }).then(() => {
                        document.location.reload(true);
                    });
                }
            },
        });
        return false;
    });

    // Add New Bank Account form submit
    $("#UpdateBankAccForm").submit(function () {
        //Captura os dados do formulário
        var UpdateBankAccForm = document.getElementById("UpdateBankAccForm");

        // Instância o FormData passando como parâmetro o formulário
        var formData = new FormData(UpdateBankAccForm);

        // Envia O FormData através da requisição AJAX
        $.ajax({
            url: u + "/ajax/add-bank-account.php",
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
                        icon: "error",
                    });
                } else {
                    Swal.fire({
                        title: feedback.title,
                        text: feedback.msg,
                        icon: "success",
                    }).then(() => {
                        document.location.reload(true);
                    });
                }
            },
        });
        return false;
    });

    // Add New Bank Account form submit
    $("#AdmChangeUserPassForm").submit(function () {
        //Captura os dados do formulário
        var AdmChangeUserPassForm = document.getElementById(
            "AdmChangeUserPassForm"
        );

        // Instância o FormData passando como parâmetro o formulário
        var formData = new FormData(AdmChangeUserPassForm);

        // Envia O FormData através da requisição AJAX
        $.ajax({
            url: u + "/ajax/adm-change-user-pass.php",
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
                        icon: "error",
                    });
                } else {
                    Swal.fire({
                        title: feedback.title,
                        text: feedback.msg,
                        icon: "success",
                    }).then(() => {
                        document.location.reload(true);
                    });
                }
            },
        });
        return false;
    });

    // Change password form submit
    $("#ChangePasswordForm").submit(function () {
        //Captura os dados do formulário
        var changePasswordForm = document.getElementById("ChangePasswordForm");
        // Instância o FormData passando como parâmetro o formulário
        var formData = new FormData(changePasswordForm);

        // Envia O FormData através da requisição AJAX
        $.ajax({
            url: u + "/ajax/update-information-ajax.php",
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
                        icon: "error",
                    });
                } else if (feedback.status == 1) {
                    Swal.fire({
                        title: feedback.title,
                        text: feedback.msg,
                        icon: "success",
                    });
                    changePasswordForm.reset();
                } else if (feedback.status == 2) {
                    Swal.fire({
                        title: feedback.title,
                        text: feedback.msg,
                        icon: "info",
                    }).then(() => {
                        var div = $("#codeVerification");
                        div.append("<label>Código de Verificação</label");
                        div.append(
                            "<input class='form-control' type='text' name='verification-code'></input"
                        );
                        $.ajax({
                            url: u + "/sendmail/change_password/" + feedback.id,
                            type: "GET",
                            data: feedback.code,
                            dataType: "json",
                            processData: false,
                            contentType: false,
                        });
                    });
                }
            },
        });
        return false;
    });

    //
    $(".billing-request").click(function () {
        event.preventDefault();

        var action = this.getAttribute("data-action");
        var value = $("#valor-saque").val();
        var userPlan = $("#user-plan").val();

        var url = u + "/ajax/billing-request.php";
        var account = $("#text-bank-checkbox-s").val();

        // Taxa de saque
        let withdrawTax = 2.99;

        let dollarUS = Intl.NumberFormat("pt-BR", {
            style: "currency",
            currency: "BRL",
        });

        withdrawTax = dollarUS.format(withdrawTax);

        if (value < 5) {
            Swal.fire({
                title: "Valor do Saque Baixo",
                text: "O valor mínimo é R$ 1,00",
                icon: "warning",
            });

            return false;
        } else if (account < 1) {
            Swal.fire({
                title: "Selecione uma Conta",
                text: "Selecione 1 conta Bancária para o Saque.",
                icon: "warning",
            });

            return false;
        }

        let taxText =
            userPlan < 5
                ? `Uma taxa de ${withdrawTax} será debitada do valor sacado.`
                : "";

        Swal.fire({
            title: "Confirmar Saque de R$ " + value + "?",
            text: taxText,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#2BC155",
            cancelButtonColor: "#FF6D4D",
            confirmButtonText: "Sim, confirmar",
            cancelButtonText: "Não tenho certeza",
        }).then((result) => {
            if (result.isConfirmed) {
                // Envia os parâmetro para o PHP via AJAX
                $.ajax({
                    url: url,
                    type: "GET",
                    data: { action, value, account },
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
                });
            }
        });

        return false;
    });

    //
    $(".anticipation-request").click(function () {
        event.preventDefault();

        var action = this.getAttribute("data-action");
        var value = $("#valor-antecipacao").val(); 

        var local_value = value.replace(".", "");
        var local_value = local_value.replace(",", ".");
        // Taxa de antecipação
        let antecipationTax = local_value * 0.0499;
        // Taxa de saque

        let dollarUS = Intl.NumberFormat("pt-BR", {
            style: "currency",
            currency: "BRL",
        });

        antecipationTax = dollarUS.format(antecipationTax);

        var url = u + "/ajax/billing-request.php";

        if (value < 1) {
            Swal.fire({
                title: "Valor da Antecipação Baixo",
                text: "O valor mínimo é R$ 1,00",
                icon: "warning",
            });
            return false;
        } 

        Swal.fire({
            title: "Confirmar Antecipação de R$ " + value + "?",
            text: `Uma taxa de ${antecipationTax} será debitada do valor antecipado.`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#2BC155",
            cancelButtonColor: "#FF6D4D", 
            confirmButtonText: "Confirmar Antecipação",
            cancelButtonText: "Não tenho certeza",
        }).then((result) => { 
            if (result.isConfirmed) {
                // Envia os parâmetro para o PHP via AJAX
                $.ajax({
                    url: url,
                    type: "GET",
                    data: { action, value },
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
                });
            }
        });
        return false;
    });

    $("#RepasseForm").submit(function () {
        var data = new FormData(this);
        var value = $("#valor-repasse").val();

        if (value < 5) {
            Swal.fire({
                title: "Valor do Saque Baixo",
                text: "O valor mínimo é R$ 1,00",
                icon: "warning",
            });

            return false;
        }
        var url = u + "/ajax/billing-request.php";
        // Envia os parâmetro para o PHP via AJAX
        $.ajax({
            url: url,
            type: "POST",
            data: data,
            dataType: "script",
            processData: false,
            contentType: false,
            beforeSend: function(){
                display_loader();
            } ,
            complete: function() {
                display_loader(false);
            },
            success: function (feedback) {
                feedback = JSON.parse(feedback);
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
        });

        return false;
    });

    $(".need-paym-btn").click(function () {
        event.preventDefault();

        //var plan = this.getAttribute('data-plan-id');

        toastr.warning(
            "Você precisa informar um método de pagamento antes de mudar de plano.",
            "Método de Pagamento",
            {
                timeOut: 8000,
                closeButton: !0,
                debug: !1,
                newestOnTop: !0,
                progressBar: !0,
                positionClass: "toast-top-center",
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
            }
        );
    });

    $(".change-plan-btn").click(function () {
        event.preventDefault();

        var plan = this.getAttribute("data-plan-id");

        var url = u + "/ajax/change-plan/";
        Swal.fire({
            title: "Confirmar Mudança",
            text: "Se for o caso, uma cobrança será realizada no seu cartão, referente ao novo plano.",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#2BC155",
            cancelButtonColor: "#FF6D4D",
            confirmButtonText: "Confirmar",
            cancelButtonText: "Não, cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                $("#preloader").removeAttr("style");
                $("#preloader").attr("style", "display: block;z-index: 3;");

                // Envia os parâmetro para o PHP via AJAX
                $.ajax({
                    url: url,
                    type: "GET",
                    data: { plan },
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
                            } else {
                                $("#preloader").removeAttr("style");
                                $("#preloader").attr("style", "display: none;");
                            }
                        });
                    },
                }).fail(function (data) {
                    Swal.fire({
                        title: "Erro Interno",
                        text: "Não foi possível alterar seu plano. Por favor, contate o suporte.",
                        icon: "warning",
                    }).then((value) => {
                        document.location.reload(true);
                    });
                });
                return false;
            }
        });
    });

    $("#SaveCardForm").submit(function () {
        // Captura os dados do formulário
        var SaveCardForm = document.getElementById("SaveCardForm");

        // Instância o FormData passando como parâmetro o formulário
        var formData = new FormData(SaveCardForm);

        var url = u + "/ajax/change-pay-method/";

        $("#save-card-btn").attr("disabled", "disabled");
        $("#save-card-btn").html("");
        $("#save-card-btn").html(
            '<i class="fas fa-spinner fa-spin"></i> Validando'
        );

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
                if (feedback.type == "success") {
                    document.location.reload(true);
                } else {
                    $("#save-card-btn").removeAttr("disabled");
                    $("#save-card-btn").html("");
                    $("#save-card-btn").html("Salvar Cartão");

                    toastr.error(feedback.msg, feedback.title, {
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
                }
            },
        }).fail(function (data) {
            Swal.fire({
                title: "Erro de Conexão",
                text: "Quando a página atulizar, tente cadastrar o seu cartão novamente novamente.",
                icon: "warning",
            }).then((value) => {
                window.location.reload;
            });
        });

        return false;
    });

    $("#bank-accounts").DataTable({
        searching: false,
        paging: false,
        select: false,
        ordering: false,
        lengthChange: false,
    });

    $(".bank-account-details").click(function () {
        var url = u + "/ajax/bank-account-details.php";

        var id = this.getAttribute("data-id");

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
                $(".acc-details-json").html("");
                $("#acc-details-status").html(feedback.status);
                $("#acc-details-bank").html(feedback.bank);
                $("#acc-details-agency").html(feedback.agency);
                $("#acc-details-number").html(feedback.number);
                $("#acc-details-type").html(feedback.type);
                $("#acc-details-pix-type").html(feedback.pix);
                $("#acc-details-pix-key").html(feedback.key);

                if (feedback.justification == false) {
                    $("#disapproval-justification-div").addClass("d-none");
                    $("disapproval-justification-i").html("");
                } else {
                    $("#disapproval-justification-div").removeClass("d-none");
                    $("#acc-details-justification").html("");
                    $("#acc-details-justification").html(feedback.justification);
                }
            },
        });
    });

    $(".approve-bank-account").click(function () {
        event.preventDefault();

        var id = this.getAttribute("data-id");
        var action = this.getAttribute("data-action");
        var url = u + "/ajax/change-bank-account-status.php";

        var btnID = "#" + this.getAttribute("data-btn-id");

        $.ajax({
            url: url,
            type: "GET",
            data: { action, id },
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
                toastr.success(feedback.msg, feedback.title, {
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
            },
        }).then(() => {
            $(btnID).html("");
            $(btnID).removeClass();
            $(btnID).attr("class", "btn btn-success dropdown-toggle btn-xs");
            $(btnID).html("Aprovada");
        });
    });
    $(".disapprove-bank-account-link").click(function () {
        event.preventDefault();

        var id = this.getAttribute("data-id");

        $("#disapprove-bank-account-submit").removeAttr("data-id");
        $("#disapprove-bank-account-submit").attr("data-id", id);
    });

    $("#disapprove-bank-account-submit").click(function () {
        event.preventDefault();

        var id = this.getAttribute("data-id");
        var action = 0;
        var url = u + "/ajax/change-bank-account-status.php";

        var btnID = "#bank-account-status-btn-" + id;

        var justification = $("#justification-textarea").val();

        $.ajax({
            url: url,
            type: "GET",
            data: { action, id, justification },
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
                document.location.reload(true);
            },
        });
    });

    $("input:checkbox").on("click", function () {
        // in the handler, 'this' refers to the box clicked on
        var $box = $(this);
        if ($box.is(":checked")) {
            // the name of the box is retrieved using the .attr() method
            // as it is assumed and expected to be immutable
            var group = "input:checkbox[name='" + $box.attr("name") + "']";
            // the checked state of the group/box on the other hand will change
            // and the current value is retrieved using .prop() method
            $(group).prop("checked", false);
            $box.prop("checked", true);
        } else {
            $box.prop("checked", false);
        }
    });

    $("#all-accounts-list").DataTable({
        paging: true,
        searching: true,
        select: true,
        lengthChange: false,
        language: {
            lengthMenu: "_MENU_ contas por página",
            zeroRecords: "Sem resultados para sua busca",
            info: "Página _PAGE_ de _PAGES_",
            infoEmpty: "Nenhuma conta para exibir aqui.",
            search: "Filtrar",
            paginate: {
                next: "Próximas",
                previous: "Anteriores",
            },
            infoFiltered: "(filtrando de _MAX_ contas, no total.)",
        },
    });

    $(".bank-checkbox-s").change(function () {
        var switche = this.value;

        if ($(".bank-checkbox-s").is(":checked")) {
            $("#text-bank-checkbox-s").val(switche);
        } else {
            $("#text-bank-checkbox-s").val("");
        }
    });

    $(".bank-checkbox-a").change(function () {
        var switche = this.value;

        if ($(".bank-checkbox-a").is(":checked")) {
            $("#text-bank-checkbox-a").val(switche);
        } else {
            $("#text-bank-checkbox-a").val("");
        }
    });

    jQuery(function ($) {
        $("#select-bank-pix-type").change(function () {
            addMaskToInput();
        });

        var $input = $("#pix-chave");
        $input.mask("+99 (99) 99999-9999");

        function addMaskToInput() {
            var $select = $("#select-bank-pix-type option:selected").val();
            var $input = $("#pix-chave");
            //$input.mask('(99) 99999-9999');

            if ($select == "1") {
                $input.mask("+99 (99) 99999-9999");
            } else if ($select == "2") {
                console.log($select);
                $input.mask("999.999.999-99");
            } else if ($select == "3") {
                $input.mask("99.999.999/9999-99");
            } else if ($select == "4") {
                $input.unmask();
            } else if ($select == "5") {
                $input.mask("AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA");
            }
        }
    });

    $("#movement-history").DataTable({
        paging: true,
        searching: false, 
        select: true,
        lengthChange: false,
        language: {
            lengthMenu: "_MENU_ itens por página",
            zeroRecords: "Sem resultados para sua busca",
            info: "Página _PAGE_ de _PAGES_",
            infoEmpty: "Nenhuma movimentação para exibir aqui.",
            search: "Filtrar",
            paginate: {
                next: ">>",
                previous: "<<",
            },
            infoFiltered: "(filtrando de _MAX_ itens, no total.)",
        },
        order: [[0, "desc"]],
        dom: "Bfrtip",
        buttons: [
            {
                extend: "pdf",
                orientation: "portrait",
                title: "Histório de Movimentações - DropExpress",
                footer: true,
                exportOptions: {
                    columns: [0, 1, 2, 3],
                },
            },
            {
                extend: "excel",
                title: "Histório de Movimentações - DropExpress",
                exportOptions: {
                    columns: [0, 1, 2, 3],
                },
            },
            {
                extend: "csv",
                title: "Histório de Movimentações - DropExpress",
                footer: true,
                exportOptions: {
                    columns: [0, 1, 2, 3],
                },
            },
        ],
    });

    $("#make-charge-now").click(function () {
        event.preventDefault();

        Swal.fire({
            title: "Pagar Agora?",
            text: "Uma cobrança será realizada no seu cartão.",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#2BC155",
            cancelButtonColor: "#FF6D4D",
            confirmButtonText: "Pagar Agora",
            cancelButtonText: "Não, cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                var url = u + "/ajax/make-pagarme-charge.php";

                $.ajax({
                    url: url,
                    type: "GET",
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
                }).fail(function (data) {
                    Swal.fire({
                        title: "Erro de Conexão",
                        text: "Quando a página atulizar, tente mudar o status do pedido novamente.",
                        icon: "warning",
                    }).then((value) => {
                        window.open("pedidos/", "_self");
                    });
                });
            }
        });

        return false;
    });
});
