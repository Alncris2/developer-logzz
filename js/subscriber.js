/* AJAX */
$(document).ready(function ($) {
    var u = location.protocol + "//" + window.location.hostname;
    var dataTable;

    jQuery(".filter-btn").on("click", function () {
        jQuery(".chatbox").addClass("active");
    });

    jQuery(".chatbox-close").on("click", function () {
        jQuery(".chatbox").removeClass("active");
    });

    $("#select-filter-plan-id").change(function () {
        var multipleValues = $("#select-filter-plan-id").val();
        $("#text-filter-plan-id").val(multipleValues);
    });

    //Suspend user account
    $("#update-account-status").click(function () {
        event.preventDefault();

        var code = this.getAttribute("data-user-code");
        var url = u + "/ajax/update-subscriber-status.php";

        // Envia os parâmetro para o PHP via AJAX
        $.ajax({
            url: url,
            type: "GET",
            data: { code },
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
                        dataTable.ajax.reload();
                    }
                });
            },
        });

        return false;
    });

    autorizarAcesso = (user_id) => {
        const action = "reactivate-subscriber";
        const user__id = user_id;

        Swal.fire({
            title: "Tem certeza ?",
            text: "Este usuário voltará a ter acesso à plataforma.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            reverseButtons: true,
            cancelButtonText: "Cancelar",
            confirmButtonText: "Reativar",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "GET",
                    url: u + "/ajax/update-subscriber-status.php",
                    data: {
                        action,
                        user__id,
                    },
                    dataType: "json",
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
                            text: feedback.msg,
                            icon: feedback.type,
                        }).then((value) => {
                            if (feedback.type == "success") {
                                dataTable.ajax.reload();
                            }
                        });
                    },
                    error: function () {
                        Swal.fire({
                            title: "Error",
                            text: "Erro ao reativar o usuário, tente novamente mais tarde!",
                            icon: "error",
                        });
                    },
                });
            }
        });
    };

    $("#generate-new-pass").click(function () {
        var length = 8;
        var result = "";
        var characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        var charactersLength = characters.length;
        for (var i = 0; i < length; i++) {
            result += characters.charAt(Math.floor(Math.random() * charactersLength));
        }

        $("#input-nova-senha").val(result);

        toastr.success("", "Senha Criada!", {
            timeOut: 5000,
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

    $("#send-new-user-email").change(function () {
        var switche = document.getElementById("send-new-user-email");

        if ($(switche).is(":checked")) {
            $("#send-new-user-email-text").val("1");
        } else {
            $("#send-new-user-email-text").val("0");
        }
    });

    $("#select-subscriber-name").change(function () {
        var multipleValues = $("#select-subscriber-name").val();
        $("#text-subscriber-name").val(multipleValues);
    });

    $("#UpdateSubscriberForm").submit(function () {
        var select_plano_assinante = $("#select-plano-assinante").val();
        var select_taxa_assinante = $("#select-taxa-assinante").val();
        var select_prazo_assinante = $("#select-prazo-assinante").val();
        var valor_entrega_assinante = $("#text-entrega-assinante").val();
        var valor_comissao_assinante = $("#text-comissao-recrutamento").val();

        if (
            select_plano_assinante == null ||
            select_taxa_assinante == null ||
            select_prazo_assinante == null ||
            valor_entrega_assinante == null ||
            valor_comissao_assinante == null
        ) {
            Swal.fire({
                title: "Erro!",
                text: "Todos os detalhes do plano precisam ser informados.",
                icon: "error",
            });
            return false;
        }

        // Captura os dados do formulário
        var UpdateSubscriberForm = document.getElementById("UpdateSubscriberForm");

        // Instância o FormData passando como parâmetro o formulário
        var formData = new FormData(UpdateSubscriberForm);

        var url = u + "/ajax/add-subscriber-ajax.php";

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
                if (feedback.status > 0) {
                    Swal.fire({
                        title: "Sucesso!",
                        text: "As informações do Assinante foram atualizadas.",
                        icon: "success",
                    }).then((value) => {
                        document.location.reload(true);
                    });
                } else {
                    Swal.fire({
                        title: "Erro!",
                        text: feedback.msg,
                        icon: "error",
                    });
                }
            },
        });

        return false;
    });

    $("#older-subscribers").DataTable({
        searching: false,
        paging: true,
        select: true,
        lengthChange: true,
        dom: "Bfrtip",
        buttons: [
            "csv",
            "excel",
            "pdf",
            {
                extend: "pdf",
                orientation: "landscape",
                title: "Assinantes - DropExpress",
                footer: true,
            },
        ],
    });

    dataTable = $("#users-list").DataTable({
        searching: false,
        paging: true,
        processing: true,
        select: true,
        lengthChange: true,
        dom: "Bfrtip",
        buttons: [
            {
                extend: "csv",
                text: "csv",
                titleAttr: "CSV",
                filename: "Usuários - Logzz",
                action: newexportaction,
                bom: true,
                charset: "utf-8",
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6],
                },
            },
            {
                extend: "excel",
                text: "excel",
                titleAttr: "XLS",
                className: "ExcelExport",
                filename: "Usuários - Logzz",
                title: "Usuários - Logzz",
                action: newexportaction,
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6],
                    modifier: {
                        order: "applied",
                        page: "all",
                    },
                },
            },
            {
                extend: "pdf",
                orientation: "landscape",
                pageSize: "A4",
                text: "pdf",
                titleAttr: "PDF",
                title: "Usuários - Logzz",
                action: newexportaction,
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6],
                    modifier: {
                        search: "applied",
                        order: "applied",
                        page: "all",
                    },
                },
            },
        ],
        serverSide: true,
        processing: true,
        serverMethod: "post",
        ajax: {
            url: u + "/ajax/list-datatable-ajax/user-list-ajax.php",
            type: "POST",
            dataType: "JSON",
            data: {
                filter_data: function () {
                    return $("#filter-form").serialize();
                },
            },
        },
        paging: true,
        columns: [
            { data: "date" }, //data da criação
            { data: "name" }, //nome usuario
            { data: "email" }, //email usuario
            { data: "user_phone" },
            { data: "plan" }, //plan usuario
            { data: "tax" }, //taxa usuario
            { data: "freight" }, //taxa de entrega
            { data: "action", class: 'text-center' }, //ações
        ],
        createdRow: function (row, data, dataIndex) {
            if (data.active == 0) {
                $(row).addClass("table-active");
            }
        },
        ordering: true,
        info: true,
        autoWidth: false,
        responsive: true,
        language: {
            processing: "Atualizando listagem...",
            zeroRecords: "Sem resultados para sua busca",
            info: "Página _PAGE_ de _PAGES_",
            infoFiltered: "(filtrando de _MAX_ usúarios, no total.)",
            paginate: {
                next: "Próximo",
                previous: "Anterior",
            },
            infoFiltered: "(filtrando de _MAX_ usúarios, no total.)",
        },
    });

    $("#filter-form").submit(function (e) {
        e.preventDefault();
        dataTable.ajax.reload();
        $(".chatbox").removeClass("active");
    });
});

function copyUserCode(element) {
    event.preventDefault();

    var copyText = element.getAttribute("data-code"); autorizarAcesso
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
}
