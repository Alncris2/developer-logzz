var credit_taxes = {
    1: "",
    2: "",
    3: "",
    4: "",
    5: "",
    6: "",
    7: "",
    8: "",
    9: "",
    10: "",
    11: "",
    12: "",
};
var complete_taxes = [];
var cities_counter;
var is_current_operation = false;

$(document).ready(() => {
    var doc = $(".cpf-cnpj");

    jQuery(".filter-btn").on("click", function () {
        jQuery(".chatbox").addClass("active");
    });
    jQuery(".chatbox-close").on("click", function () {
        jQuery(".chatbox").removeClass("active");
    });

    var operation = $("#operation-id").val();
    var data = new FormData();
    data.append("action", "get-operators");
    data.append("operation", operation);

    $.ajax({
        url: u + "/ajax/get-operators.php",
        type: "POST",
        data: data,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function (feedback) {
            var operators = feedback["data"];
            const container = $("#select-ship-operator");
            container.empty();

            if (operators.length == 0) {
                var option = document.createElement("option");
                option.innerHTML = "Nenhum operador cadastrado";
                option.disabled = true;
                option.selected = true;
                container.append(option);
            } else {
                var default_option = document.createElement("option");
                default_option.innerHTML = "Selecione um operador";
                default_option.disabled = true;
                default_option.selected = true;
                container.append(default_option);
            }

            Array.from(operators).forEach((op) => {
                var option = document.createElement("option");
                option.innerHTML = op["full_name"];
                option.value = op["operator_id"];
                container.append(option);
            });
            container.selectpicker("refresh");
        },
    });

    $(".approve-transfer").click((e) => {
        Swal.fire({
            title: "Alterar status",
            text: "Confirme a alteração de status para Aprovado",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#2BC155",
            cancelButtonColor: "#d33",
            confirmButtonText: "Confirmar",
        }).then((result) => {
            if (result.isConfirmed) {
                let url = u + "/ajax/change-transfer-status.php";

                let data = new FormData();
                data.append("status", 2);
                data.append("billing", $(e.target).data("action"));

                $.ajax({
                    url: url,
                    type: "POST",
                    data: data,
                    dataType: "json",
                    processData: false,
                    contentType: false,
                    success: function (feedback) {
                        if (feedback.status > 0) {
                            Swal.fire({
                                title: "Sucesso!",
                                text: "Status alterado com sucesso",
                                icon: "success",
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: "Erro!",
                                text: feedback.msg,
                                icon: "warning",
                            });
                        }
                    },
                });
            }
        });
    });

    $(".disapprove-transfer").click((e) => {
        Swal.fire({
            title: "Alterar status",
            text: "Confirme a alteração de status para Reprovado",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#2BC155",
            cancelButtonColor: "#d33",
            confirmButtonText: "Confirmar",
        }).then((result) => {
            if (result.isConfirmed) {
                let url = u + "/ajax/change-transfer-status.php";

                let data = new FormData();
                data.append("status", 1);
                data.append("billing", $(e.target).data("action"));

                $.ajax({
                    url: url,
                    type: "POST",
                    data: data,
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
                                text: "Status alterado com sucesso",
                                icon: "success",
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: "Erro!",
                                text: feedback.msg,
                                icon: "warning",
                            });
                        }
                    },
                });
            }
        });
    });

    //Adiciona máscara ao input de CPF/CNPJ
    doc.keydown(() => {
        var masks = ["000.000.000-00", "00.000.000/0000-00"];
        var mask = doc.val().length > 13 ? masks[1] : masks[0];
        doc.mask(mask, { reverse: true });
    });

    //Adiciona máscara ao input de dinheiro
    $("#text-taxa-dinheiro").mask("##0,00%", { reverse: true });

    //Adiciona máscara ao input de débito
    $("#text-taxa-debito").mask("##0,00%", { reverse: true });

    //Adiciona máscara ao input de telefone
    $(".telefone-operation").mask("(99) 9 9999-9999");

    //Desativa o botão de submit até o usuário selecionar as cidades que compõem a operação
    $("#SubmitButton").attr("disabled", "true");

    //Verifica se váriaveis da tela de edição já estão setadas
    if (typeof current_state !== "undefined") {
        handleLocales(current_state);
    }

    if (typeof current_credit_taxes != "undefined") {
        credit_taxes = current_credit_taxes;
    }

    if (typeof current_delivery_taxes != "undefined") {
        is_current_operation = true;
        getOperationCities($("#text-operacao-local").val());
    }

    //Ao selecionar o estado da operação, busca na API os dados das cidades e popula outro select para o usuário
    $("#text-uf-operacao").change((e) => {
        handleLocales(e.target.value);
    });

    $("#operator-list").DataTable({
        paging: true,
        searching: true,
        select: true,
        lengthChange: false,
        language: {
            zeroRecords: "Sem resultados para sua busca",
            info: "Página _PAGE_ de _PAGES_",
            infoEmpty: "Este produto não tem nenhum afiliado ativo.",
            search: "Filtrar",
            infoFiltered: "(filtrando de _MAX_ cupons, no total.)",
            paginate: {
                next: ">",
                previous: "<",
            },
        },
    });
});

$("#text-taxa-prazo").mask("##0,00%", { reverse: true });

var old_option, old_credit_option;

$("#text-taxa-credito").change((e) => {
    if (old_option != undefined) {
        old_option.removeAttr("selected");
    }

    var credit_option = e.target.value;
    var credit_tax = $("#text-taxa-prazo").val();

    $("#taxa-prazo-credito").attr("style", "display: block !important");
    $("#text-credit-tax").text(credit_option + "x");

    if (credit_tax != "") {
        old_option.text(old_credit_option + "x - " + credit_tax);
    }
    old_credit_option = e.target.value;
    old_option = $("#credit-" + credit_option);
    old_option.attr("selected");
    $("#select-taxa-credito").selectpicker("refresh");
    $("#text-taxa-prazo").val("");
});

$("#text-taxa-prazo").keyup((e) => {
    $(".credit-tax-container .filter-option-inner-inner").text(
        old_credit_option + "x - " + e.target.value
    );
    old_option.text(old_credit_option + "x - " + e.target.value);

    credit_taxes[old_credit_option] = e.target.value;

    old_option.attr("selected");
    $("#select-taxa-credito").selectpicker("refresh");
});

$("#text-entrega-frustrada").mask("#.##0,00", { reverse: true });
$("#text-entrega-completa").mask("#.##0,00", { reverse: true });

var old_city_html, old_city;
$("#text-cidade-taxa").change((e) => {
    $("#text-entrega-completa").val("");
    $("#text-entrega-frustrada").val("");

    var city = e.target.value;
    old_city = e.target.value;

    $("#taxa-completa-input").attr("style", "display: block !important");
    $("#complete_city_name").text(city);

    $("#taxa-frustrada-input").attr("style", "display: block !important");
    $("#frustrated_city_name").text(city);

    old_city_html = $("#select-taxa-cidades [value='" + old_city + "']");
    old_city_html.attr("selected");

    $("#select-taxa-cidades").selectpicker("refresh");
});

$("#text-entrega-completa").keyup((e) => {
    var tax_city_complete = $("#text-entrega-completa").val();
    var tax_city_frustr = $("#text-entrega-frustrada").val();
    $(".cities-select-container .filter-option-inner-inner").text(
        old_city +
        " | Taxa Compl.: R$" +
        tax_city_complete +
        " | Taxa Frustr.: R$" +
        tax_city_frustr
    );
    old_city_html.text(
        old_city +
        " | Taxa Compl.: R$" +
        tax_city_complete +
        " | Taxa Frustr.: R$" +
        tax_city_frustr
    );

    updateCityTaxes(tax_city_complete, tax_city_frustr);

    old_city_html.attr("selected");
    $("#select-taxa-cidades").selectpicker("refresh");
});

$("#text-entrega-frustrada").keyup((e) => {
    var tax_city_complete = $("#text-entrega-completa").val();
    var tax_city_frustr = $("#text-entrega-frustrada").val();
    $(".cities-select-container .filter-option-inner-inner").text(
        old_city +
        " | Taxa Compl.: R$" +
        tax_city_complete +
        " | Taxa Frustr.: R$" +
        tax_city_frustr
    );
    old_city_html.text(
        old_city +
        " | Taxa Compl.: R$" +
        tax_city_complete +
        " | Taxa Frustr.: R$" +
        tax_city_frustr
    );

    updateCityTaxes(tax_city_complete, tax_city_frustr);

    old_city_html.attr("selected");
    $("#select-taxa-cidades").selectpicker("refresh");
});

$("#text-operacao-local").change(() => {
    complete_taxes = [];
    cities_counter = 0;
    is_current_operation = false;
});

function handleLocales(e) {
    $(".cidades-container").attr("style", "display: block !important");
    $("#SubmitButton").removeAttr("disabled");
    $.ajax({
        url: `https://servicodados.ibge.gov.br/api/v1/localidades/estados/${e}/municipios`,
        type: "GET",
        success: function (data) {
            data.forEach((element) => {
                var option = document.createElement("option");
                option.value = element.nome;
                option.innerHTML = element.nome;

                var select = document.querySelector("#select-cidade-operacao");

                if ($(".select-cidade").data("uf") != e) {
                    select.innerHTML = "";
                    $(".select-cidade").data("uf", e);
                }

                if (
                    typeof current_locales !== "undefined" &&
                    current_locales.includes(option.value)
                ) {
                    select.innerHTML +=
                        option.outerHTML.slice(0, 7) +
                        " selected" +
                        option.outerHTML.slice(7);
                } else {
                    select.innerHTML += option.outerHTML;
                }
            });
            $(".select-cidade").selectpicker("refresh");
        },
    });

    $.ajax({
        url: `https://servicodados.ibge.gov.br/api/v1/localidades/estados/${e}/subdistritos`,
        type: "GET",
        success: function (data) {
            data.forEach((element) => {
                var option = document.createElement("option");
                option.value = element.nome;
                option.innerHTML = element.nome;

                var select = document.querySelector("#select-cidade-operacao");
                if (
                    typeof current_locales !== "undefined" &&
                    current_locales.includes(option.value)
                ) {
                    select.innerHTML +=
                        option.outerHTML.slice(0, 7) +
                        " selected" +
                        option.outerHTML.slice(7);
                } else {
                    select.innerHTML += option.outerHTML;
                }
            });
            $(".select-cidade").selectpicker("refresh");
        },
    });
}

function updateCityTaxes(tax_city_complete, tax_city_frustr) {
    var city_is_present = false;
    var ctr = 0;
    if (complete_taxes.length != 0) {
        complete_taxes.forEach((obj) => {
            if (obj.city == old_city) {
                city_is_present = true;
                complete_taxes[ctr] = {
                    city: old_city,
                    complete_tax: tax_city_complete,
                    frustrated_tax: tax_city_frustr,
                };
            }

            ctr++;

            if (ctr === complete_taxes.length) {
                updateCity();
            }
        });

        function updateCity() {
            if (!city_is_present) {
                complete_taxes.push({
                    city: old_city,
                    complete_tax: tax_city_complete,
                    frustrated_tax: tax_city_frustr,
                });
            }
        }
    } else {
        complete_taxes.push({
            city: old_city,
            complete_tax: tax_city_complete,
            frustrated_tax: tax_city_frustr,
        });
    }
}

$("#AddOperatorForm").submit(function () {
    var nome = $("#nomeOperador").val();
    var email = $("#emailOperador").val();
    var telefone = $("#telefoneOperador").val();
    var operacao = $("#text-operacao-local").val();
    var credito = $("#text-taxa-credito").val();

    if (
        nome == null ||
        email == null ||
        telefone == null ||
        operacao == null ||
        credito == null
    ) {
        Swal.fire({
            title: "Erro!",
            text: "Todos os detalhes do plano precisam ser informados.",
            icon: "warning",
        });
        return false;
    }

    var credit_taxes_entered = true;
    const keys = Object.keys(credit_taxes);
    keys.forEach((key) => {
        if (credit_taxes[key] == "") {
            Swal.fire({
                title: "Erro!",
                text: "Todas as taxas de crédito precisam ser informadas.",
                icon: "warning",
            });
            credit_taxes_entered = false;
        }
    });

    if (!credit_taxes_entered) {
        return false;
    }

    complete_taxes_entered = true;
    complete_taxes.forEach((tax) => {
        if (tax["complete_tax"] == "" || tax["frustrated_tax"] == "") {
            complete_taxes_entered = false;
        }
    });

    if (complete_taxes.length != cities_counter || !complete_taxes_entered) {
        Swal.fire({
            title: "Erro!",
            text: "Todas as taxas de entrega por cidade precisam ser informadas.",
            icon: "warning",
        });
        return false;
    }

    var AddOperatorForm = document.getElementById("AddOperatorForm");

    var formData = new FormData(AddOperatorForm);

    formData.append("taxa-credito", JSON.stringify(credit_taxes));
    formData.append("taxas-entrega", JSON.stringify(complete_taxes));

    var url = u + "/ajax/add-operator-ajax.php";
    formData;
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
            if (feedback.status > 0) {
                Swal.fire({
                    title: "Sucesso!",
                    text: "O operador foi cadastrado com sucesso.",
                    icon: "success",
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    title: "Erro!",
                    text: feedback.msg,
                    icon: "warning",
                });
            }
        },
    });

    return false;
});

$("#UpdateOperatorForm").submit(function () {
    var nome = $("#nomeOperador").val();
    var email = $("#emailOperador").val();
    var telefone = $("#telefoneOperador").val();
    var operacao = $("#text-operacao-local").val();
    var credito = $("#text-taxa-credito").val();

    if (
        nome == null ||
        email == null ||
        telefone == null ||
        operacao == null ||
        credito == null
    ) {
        Swal.fire({
            title: "Erro!",
            text: "Todos os detalhes do plano precisam ser informados.",
            icon: "warning",
        });
        return false;
    }

    var credit_taxes_entered = true;
    const keys = Object.keys(credit_taxes);
    keys.forEach((key) => {
        if (credit_taxes[key] == "") {
            Swal.fire({
                title: "Erro!",
                text: "Todas as taxas de crédito precisam ser informadas.",
                icon: "warning",
            });
            credit_taxes_entered = false;
        }
    });

    if (!credit_taxes_entered) {
        return false;
    }

    complete_taxes_entered = true;
    complete_taxes.forEach((tax) => {
        if (tax["complete_tax"] == "" || tax["frustrated_tax"] == "") {
            complete_taxes_entered = false;
        }
    });

    if (complete_taxes.length != cities_counter || !complete_taxes_entered) {
        Swal.fire({
            title: "Erro!",
            text: "Todas as taxas de entrega por cidade precisam ser informadas.",
            icon: "warning",
        });
        return false;
    }

    var UpdateOperatorForm = document.getElementById("UpdateOperatorForm");

    var formData = new FormData(UpdateOperatorForm);

    formData.append("taxa-credito", JSON.stringify(credit_taxes));
    formData.append("taxas-entrega", JSON.stringify(complete_taxes));

    var url = u + "/ajax/add-operator-ajax.php";
    formData;
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
            if (feedback.status > 0) {
                Swal.fire({
                    title: "Sucesso!",
                    text: "O operador foi cadastrado com sucesso.",
                    icon: "success",
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    title: "Erro!",
                    text: feedback.msg,
                    icon: "warning",
                });
            }
        },
    });

    return false;
});

$("#text-operacao-local").change((e) => {
    getOperationCities(e.target.value);
});

function getOperationCities(e) {
    var operation_id = e;

    var data = new FormData();
    data.append("operation_id", operation_id);
    data.append("action", "get-cities");

    var url = u + "/ajax/get-operation-cities.php";

    $.ajax({
        url: url,
        type: "POST",
        data: data,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function (feedback) {
            if (feedback.status > 0) {
                var cities = feedback.data;
                cities_counter = 0;
                complete_taxes = [];
                $("#select-taxa-cidades").empty();

                let default_option =
                    "<option id='disabled-city-option' disabled selected>Selecione a cidade</option>";
                $("#select-taxa-cidades").append(default_option);

                cities.forEach((city) => {
                    let option =
                        "<option id='city-" +
                        cities_counter +
                        "' class='" +
                        city["city"] +
                        "' value='" +
                        city["city"] +
                        "'>" +
                        city["city"] +
                        "</option>";
                    $("#select-taxa-cidades").append(option);
                    cities_counter++;
                });
                if (
                    typeof current_delivery_taxes != "undefined" &&
                    is_current_operation
                ) {
                    complete_taxes = current_delivery_taxes;
                    complete_taxes.forEach((obj) => {
                        var option = $("option[value='" + obj["city"] + "']");
                        $(".cities-select-container .filter-option-inner-inner").text(
                            obj["city"] +
                            " | Taxa Compl.: R$" +
                            obj["complete_tax"] +
                            " | Taxa Frustr.: R$" +
                            obj["frustrated_tax"]
                        );
                        option.text(
                            obj["city"] +
                            " | Taxa Compl.: R$" +
                            obj["complete_tax"] +
                            " | Taxa Frustr.: R$" +
                            obj["frustrated_tax"]
                        );
                    });
                }
                $("#select-taxa-cidades").selectpicker("refresh");

                $("#taxa-completa-input").attr("style", "display: none !important");
                $("#taxa-frustrada-input").attr("style", "display: none !important");
            } else {
                Swal.fire({
                    title: "Erro!",
                    text: feedback.msg,
                    icon: "warning",
                });
            }
        },
    });
}
