$(document).ready(function () {
    var preload_sales = $('.ofertas-vinculadas-mult-select-preload').select2();
    preload_sales.val([87, 86, 85,]).trigger("change");
});