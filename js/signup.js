$(document).ready(function($) {
    var e = $(this);

    $('#signup-continue').click(function(e) {
        e.preventDefault();

        var name = $('#name').val();
        var email = $('#email').val();
        var whats = $('#whatsapp').val();

        if (name == null || email == null || whats == null || whats == "" || name == "" || email == "") {
            toastr.warning("Informe todos os dados corretamente antes de continuar.", "Calma...", {
                timeOut: 4000,
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
                tapToDismiss: !0
            })
            return false;
        }

        $('#signup-continue').addClass('d-none');
        $('#signup-step-2').removeClass('d-none');

    });

    $('#account-type-switch').change(function() {
        if ($(this).is(':checked')) {
            $('#account-type-text').val('juridica');
            $('#razao-social-txt').removeClass('d-none');
            $('#company-doc-label').html('CNPJ');
            $('#razao-social-txt').removeClass('d-none');
        } else {
            $('#account-type-text').val('fisica');
            $('#company-doc-label').html('CPF');
            $('#razao-social-txt').addClass('d-none');
        }

        $('#documento').val('');
        $('#documento').toggleClass('cnpj');
        $('#documento').toggleClass('cpf');
        $('.cpf').mask('000.000.000-00', { reverse: true });
        $('.cnpj').mask('00.000.000/0000-00', { reverse: true });
    });

    $('#CompleteRegistrationForm').submit(function() {

        // Captura os dados do formulário
        var CompleteRegistrationForm = document.getElementById('CompleteRegistrationForm');

        // Instância o FormData passando como parâmetro o formulário
        var formData = new FormData(CompleteRegistrationForm);

        // Envia O FormData através da requisição AJAX
        $.ajax({
            url: "../../ajax/complete-registration.php",
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
            success: function(feedback) {
                if (feedback.status == 1) {
                    Swal.fire({
                        title: "Cadastro Completo",
                        text: "Suas informações foram atualizadas!",
                        icon: 'success',
                    }).then((value) => {
                        document.location.assign(feedback.url);
                    });
                } else {
                    Swal.fire({
                        title: "Erro",
                        text: feedback.msg,
                        icon: 'error',
                    });
                }
            }
        }).fail(function(data) {
            Swal.fire({
                title: "Erro",
                text: "Erro Interno.",
                icon: 'error',
            });
        });

        return false;
    });

    $('#UserTermsAccept').submit(function() {

        // Captura os dados do formulário
        var aceito = document.getElementById('aceito-os-termos');

        // Envia O FormData através da requisição AJAX

        if (!($(aceito).is(':checked'))) {
            Swal.fire({
                title: "Termos e Condições",
                text: "Você precisa ler e aceitar os Termos e Condições Gerais antes de prosseguir.",
                icon: 'warning',
            });

            return false;
        }

        $.ajax({
            url: "../../ajax/complete-registration.php?aceito=on",
            type: "GET",
            data: aceito,
            dataType: 'json',
            processData: false,
            contentType: false,
            beforeSend: function(){
                display_loader();
            } ,
            complete: function() {
                display_loader(false);
            },
            success: function(feedback) {
                if (feedback.status == 1) {
                    document.location.assign(feedback.url);
                } else {
                    Swal.fire({
                        title: "Erro",
                        text: feedback.msg,
                        icon: 'error',
                    });
                }
            }
        })
    });

    $('#SignupForm').submit(function() {

        // Captura os dados do formulário
        var SignupForm = document.getElementById('SignupForm');

        // Instância o FormData passando como parâmetro o formulário
        var formData = new FormData(SignupForm);

        // Envia O FormData através da requisição AJAX
        $.ajax({
            url: "../ajax/new-registration.php",
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
            success: function(feedback) {
                    if (feedback.status == 1) {
                        Swal.fire({
                           title: "Bem vindo a Logzz!",
                            text: "Você receberá um email com seus dados de acesso.",
                            icon: 'success',
                        }).then((value) => {
                            document.location.assign(feedback.url);
                        });
                    } else {
                        Swal.fire({
                            title: feedback.title,
                            text: feedback.msg,
                            icon: 'warning',
                        });
                    }
                }
                // }).fail(function (data) {
                //     Swal.fire({
                //         title: "Erro",
                //         text: "Erro Interno.",
                //         icon: 'error',
                //     });
        });

        return false;
    });

    function validaCpfCnpj(val) {
        if (val.length == 14) {
            var cpf = val.trim();

            cpf = cpf.replace(/\./g, '');
            cpf = cpf.replace('-', '');
            cpf = cpf.split('');

            var v1 = 0;
            var v2 = 0;
            var aux = false;

            for (var i = 1; cpf.length > i; i++) {
                if (cpf[i - 1] != cpf[i]) {
                    aux = true;
                }
            }

            if (aux == false) {
                return false;
            }

            for (var i = 0, p = 10;
                (cpf.length - 2) > i; i++, p--) {
                v1 += cpf[i] * p;
            }

            v1 = ((v1 * 10) % 11);

            if (v1 == 10) {
                v1 = 0;
            }

            if (v1 != cpf[9]) {
                return false;
            }

            for (var i = 0, p = 11;
                (cpf.length - 1) > i; i++, p--) {
                v2 += cpf[i] * p;
            }

            v2 = ((v2 * 10) % 11);

            if (v2 == 10) {
                v2 = 0;
            }

            if (v2 != cpf[10]) {
                return false;
            } else {
                return true;
            }
        } else if (val.length == 18) {
            var cnpj = val.trim();

            cnpj = cnpj.replace(/\./g, '');
            cnpj = cnpj.replace('-', '');
            cnpj = cnpj.replace('/', '');
            cnpj = cnpj.split('');

            var v1 = 0;
            var v2 = 0;
            var aux = false;

            for (var i = 1; cnpj.length > i; i++) {
                if (cnpj[i - 1] != cnpj[i]) {
                    aux = true;
                }
            }

            if (aux == false) {
                return false;
            }

            for (var i = 0, p1 = 5, p2 = 13;
                (cnpj.length - 2) > i; i++, p1--, p2--) {
                if (p1 >= 2) {
                    v1 += cnpj[i] * p1;
                } else {
                    v1 += cnpj[i] * p2;
                }
            }

            v1 = (v1 % 11);

            if (v1 < 2) {
                v1 = 0;
            } else {
                v1 = (11 - v1);
            }

            if (v1 != cnpj[12]) {
                return false;
            }

            for (var i = 0, p1 = 6, p2 = 14;
                (cnpj.length - 1) > i; i++, p1--, p2--) {
                if (p1 >= 2) {
                    v2 += cnpj[i] * p1;
                } else {
                    v2 += cnpj[i] * p2;
                }
            }

            v2 = (v2 % 11);

            if (v2 < 2) {
                v2 = 0;
            } else {
                v2 = (11 - v2);
            }

            if (v2 != cnpj[13]) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }


});