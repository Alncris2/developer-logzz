(function ($) {
    "use strict"

    //date picker classic default
    $('.datepicker-default').pickadate();

    $('.datepicker-default').pickadate({
        defaultDate: +1,
        firstDay: 1
    });

})(jQuery);