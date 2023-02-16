(function ($) {
    "use strict"

    //date picker classic default
    $('.datepicker-default').pickadate();

    $('.datepicker-default').pickadate({
        defaultDate: +1,
        firstDay: 1
    });

})(jQuery);

(function ($) {
    "use strict";

    // checa a hora atual
    let hours = new Date().getHours();
    //date picker classic default 
    $(".datepicker-reagendar").pickadate({
        firstDay: 0,
        // se for depois de 21h só permite marcar a entrega para 2 dias no futuro
        // antes das 21 pode marcar no dia seguinte
        min: hours >= 21 ? +2 : +1,
        max: +7, 
        disable: [1, 2, 3, 4, 5, 6, 7],         
    }); 

    let picker = $('.datepicker-reagendar').pickadate("picker");
    picker.set("disable", [1, 2, 3, 4, 5, 6, 7]); 
    picker.set("enable", $('.datepicker-reagendar').data('days'));
    $("#data-pedido").val(""); 
})(jQuery); 