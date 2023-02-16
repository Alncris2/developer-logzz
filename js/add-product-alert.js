$(document).ready(function ($) {

    Swal.fire({
        title: "Sucesso!",
        text: "O produto foi adicionado ao seu pedido!",
        icon: 'success',
    }).then((value) => {
        window.open(url, '_self');
    });
});