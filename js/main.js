/**
Core script to handle the entire core functions
**/
var u = location.protocol + "//" + window.location.hostname;


/* Document.ready Start */
jQuery(document).ready(function() {

    $(function() {
        $('[data-toggle="tooltip"]').tooltip();
    });

    $('.btn-copy-link').each(function(index) {

        $(this).click(function() {

            event.preventDefault();

            var copyText = this.getAttribute('data-text');
            navigator.clipboard.writeText(copyText);

            toastr.success(copyText, "Link Copiado!", {
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
    });

    $('.btn-copy-address').each(function(index) {

        $(this).click(function() {

            event.preventDefault();

            var copyText = this.getAttribute('data-text');
            navigator.clipboard.writeText(copyText);

            toastr.success(copyText, "Endereço Copiado!", {
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
    });

    $('[data-toggle="popover"]').popover();
    'use strict';
    DropExpress.init();

    $('#exp-orders-list').DataTable({
        searching: false,
        paging: true,
        select: false,
        lengthChange: false,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'csv',
                text: 'csv',
                titleAttr: 'CSV',
                bom: true,
                charset: 'utf-8'
            }, 'excel',
            {
                extend: 'pdf',
                orientation: 'landscape',
                title: "Pedidos - DropExpress",
                footer: true
            }
        ]
    });
    //
    $('#exp-transfer-list').dataTable({
        searching: false,
        paging: true,
        select: false,
        lengthChange: false,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'csv',
                text: 'csv',
                titleAttr: 'CSV',
                bom: true,
                charset: 'utf-8'
            }, 'excel',
            {
                extend: 'pdf',
                orientation: 'landscape',
                title: 'Repasses - DropExpress',
                footer: true
            }

        ]
    });

    $('#orders-list').DataTable({
        searching: false,
        paging: true,
        select: false,
        lengthChange: false,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'csv',
                text: 'csv',
                titleAttr: 'CSV',
                bom: true,
                charset: 'utf-8'
            }, 'excel', 'pdf',
            {
                extend: 'pdf',
                orientation: 'landscape',
                title: "Pedidos - DropExpress",
                footer: true
            }
        ],
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "language": {
            "lengthMenu": "_MENU_ envios por página",
            "zeroRecords": "Sem resultados para sua busca",
            "info": "Página _PAGE_ de _PAGES_",
            "infoEmpty": "Nenhuma oferta para exibir aqui.",
            "search": "Filtrar",
            "paginate": {
                "next": "Próximo",
                "previous": "Anterior"
            },
            "infoFiltered": "(filtrando de _MAX_ envios, no total.)"
        },
        drawCallback: function() {
            var page_min = 7;
            var $api = this.api();
            var pages = $api.page.info().pages;
            var rows = $api.data().length;

            // Tailor the settings based on the row count
            if (pages === 1) {
                // With this current length setting, not more than 1 page, hide pagination
                $('.dataTables_paginate').css("display", "none");
            } else {
                // SHow everything
                $('.dataTables_paginate').css("display", "block");
            }
        },
    });

    $('#orders-list-operator').DataTable({
        searching: false,
        paging: true,
        select: false,
        lengthChange: false,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'csv',
                text: 'csv',
                titleAttr: 'CSV',
                bom: true,
                charset: 'utf-8'
            }, 'excel',
            {
                extend: 'pdf',
                orientation: 'landscape',
                title: "Pedidos - DropExpress",
                footer: true
            }
        ]
    });

    $('#finances-table').DataTable({
        searching: false,
        paging: true,
        select: false,
        lengthChange: false,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'csv',
                text: 'csv',
                titleAttr: 'CSV',
                bom: true,
                charset: 'utf-8'
            }, 'excel', 'pdf',
            {
                extend: 'pdf',
                orientation: 'landspace',
                title: "Financeiro - DropExpress",
                footer: true,
            }
        ]
    })

    $('#shippments-list').DataTable({
        paging: true,
        searching: true,
        select: true,
        lengthChange: true,
        "language": {
            "lengthMenu": "_MENU_ envios por página",
            "zeroRecords": "Sem resultados para sua busca",
            "info": "Página _PAGE_ de _PAGES_",
            "infoEmpty": "Nenhuma oferta para exibir aqui.",
            "search": "Filtrar",
            "paginate": {
                "next": "Próximo",
                "previous": "Anterior"
            },
            "infoFiltered": "(filtrando de _MAX_ envios, no total.)"
        }
    });


    if ($('.inventory-list').length > 0) {
        var table = $('.inventory-list').DataTable({
            searching: false,
            paging: false,
            select: false,
            lengthChange: false,

        });
    }    
    if ($('#assinantes').length > 0) {
        var table = $('#assinantes').DataTable({
            searching: false,
            paging: true,
            select: true,
            info: true, 
            lengthChange: true,    
            dom: 'Blfrtip',  
            buttons: [
                {
                extend: 'csv',
                text: 'csv',
                titleAttr: 'CSV',
                bom: true,
                charset: 'utf-8'
                }, 'excel',
                {
                    extend: 'pdf',
                    orientation: 'landscape',
                    title: "Movimentações - Logzz",
                    footer: true
                },
                {
                    extend: 'pdf',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    text: 'pdf',
                    titleAttr: 'PDF',
                    title: "Movimentações - Logzz",
                    exportOptions: {
                        modifier: {
                            search: 'applied',
                            order: 'applied',
                            page: 'all'
                        }
                    },
                }
            ],
            "language": {
                "lengthMenu": "_MENU_ itens por página", 
                "zeroRecords": "Sem resultados para sua busca",
                "info": "Página _PAGE_ de _PAGES_",
                "infoEmpty": "Nenhuma solicitação para exibir aqui.",
                "search": "Filtrar", 
                "paginate": {
                    "next": "Próximo",
                    "previous": "Anterior"
                },
                "infoFiltered": "(filtrando de _MAX_ solicitações, no total.)" 
            }
        });
    }
    if ($('#external-postback-list').length > 0) {
        var table = $('#external-postback-list').DataTable({
            searching: false,
            paging: false,
            select: false,
            info: false,
            lengthChange: false,
        });
    }
    if ($('#example3').length > 0) {
        var table = $('#example3').DataTable({
            searching: false,
            paging: true,
            select: false,
            //info: false,         
            lengthChange: false

        });
    }
    if ($('#example4').length > 0) {
        var table = $('#example4').DataTable({
            searching: false,
            paging: true,
            select: false,
            //info: false,         
            lengthChange: false

        });
    }
    if ($('#example5').length > 0) {
        var table = $('#example5').DataTable({
            searching: false,
            paging: true,
            select: false,
            //info: false,         
            lengthChange: false

        });
    }
    if ($('#example').length > 0) {
        $('#example tbody').on('click', 'tr', function() {
            var data = table.row(this).data();

        });
    }
    if ($('#smartwizard').length > 0) {
        $('#smartwizard').smartWizard();
    }
    if (typeof dataTable != "undefined"){ 
        $.fn.dataTable.ext.errMode = 'none';
        dataTable.on('preDraw', function() {
            display_loader(true); 
        }); 
    }
});
/* Document.ready END */

var DropExpress = function() {
    /* Search Bar ============ */
    var screenWidth = $(window).width();

    var handleSelectPicker = function() {
        if (jQuery('.default-select').length > 0) {
            jQuery('.default-select').selectpicker();
        }
    }

    var handleTheme = function() {
        $('#preloader').fadeOut(500);
        $('#main-wrapper').addClass('show');
    }

    var handleMetisMenu = function() {
        if (jQuery('#menu').length > 0) {
            $("#menu").metisMenu();
        }
        jQuery('.metismenu > .mm-active ').each(function() {
            if (!jQuery(this).children('ul').length > 0) {
                jQuery(this).addClass('active-no-child');
            }
        });
    }

    var handleAllChecked = function() {
        $("#checkAll").on('change', function() {
            $("td input:checkbox, .email-list .custom-checkbox input:checkbox").prop('checked', $(this).prop("checked"));
        });
    }

    var handleNavigation = function() {
        $(".nav-control").on('click', function() {

            $('#main-wrapper').toggleClass("menu-toggle");

            $(".hamburger").toggleClass("is-active");
        });
    }

    var handleCurrentActive = function() {
        for (var nk = window.location,
                o = $("ul#menu a").filter(function() {

                    return this.href == nk;

                })
                .addClass("mm-active")
                .parent()
                .addClass("mm-active");;) {

            if (!o.is("li")) break;

            o = o.parent()
                .addClass("mm-show")
                .parent()
                .addClass("mm-active");
        }
    }

    var handleCustomFileInput = function() {
        $(".custom-file-input").on("change", function() {
            var fileName = $(this).val().split("\\").pop();
            $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        });
    }

    var handleMiniSidebar = function() {
        $("ul#menu>li").on('click', function() {
            const sidebarStyle = $('body').attr('data-sidebar-style');
            if (sidebarStyle === 'mini') {
                console.log($(this).find('ul'))
                $(this).find('ul').stop()
            }
        })
    }

    var handleMinHeight = function() {
        var win_h = window.outerHeight;
        var win_h = window.outerHeight;
        if (win_h > 0 ? win_h : screen.height) {
            $(".content-body").css("min-height", (win_h + 60) + "px");
        };
    }

    var handleDataAction = function() {
        $('a[data-action="collapse"]').on("click", function(i) {
            i.preventDefault(),
                $(this).closest(".card").find('[data-action="collapse"] i').toggleClass("mdi-arrow-down mdi-arrow-up"),
                $(this).closest(".card").children(".card-body").collapse("toggle");
        });

        $('a[data-action="expand"]').on("click", function(i) {
            i.preventDefault(),
                $(this).closest(".card").find('[data-action="expand"] i').toggleClass("icon-size-actual icon-size-fullscreen"),
                $(this).closest(".card").toggleClass("card-fullscreen");
        });



        $('[data-action="close"]').on("click", function() {
            $(this).closest(".card").removeClass().slideUp("fast");
        });

        $('[data-action="reload"]').on("click", function() {
            var e = $(this);
            e.parents(".card").addClass("card-load"),
                e.parents(".card").append('<div class="card-loader"><i class=" ti-reload rotate-refresh"></div>'),
                setTimeout(function() {
                    e.parents(".card").children(".card-loader").remove(),
                        e.parents(".card").removeClass("card-load")
                }, 2000)
        });
    }

    var handleHeaderHight = function() {
        const headerHight = $('.header').innerHeight();
        $(window).scroll(function() {
            if ($('body').attr('data-layout') === "horizontal" && $('body').attr('data-header-position') === "static" && $('body').attr('data-sidebar-position') === "fixed")
                $(this.window).scrollTop() >= headerHight ? $('.deznav').addClass('fixed') : $('.deznav').removeClass('fixed')
        });
    }

    var handleDzScroll = function() {
        jQuery('.dz-scroll').each(function() {

            var scroolWidgetId = jQuery(this).attr('id');
            const ps = new PerfectScrollbar('#' + scroolWidgetId, {
                wheelSpeed: 2,
                wheelPropagation: true,
                minScrollbarLength: 20
            });
        })
    }

    var handleMenuTabs = function() {
        if (screenWidth <= 991) {
            jQuery('.menu-tabs .nav-link').on('click', function() {
                if (jQuery(this).hasClass('open')) {
                    jQuery(this).removeClass('open');
                    jQuery('.fixed-content-box').removeClass('active');
                    jQuery('.hamburger').show();
                } else {
                    jQuery('.menu-tabs .nav-link').removeClass('open');
                    jQuery(this).addClass('open');
                    jQuery('.fixed-content-box').addClass('active');
                    jQuery('.hamburger').hide();
                }
                //jQuery('.fixed-content-box').toggleClass('active');
            });
            jQuery('.close-fixed-content').on('click', function() {
                jQuery('.fixed-content-box').removeClass('active');
                jQuery('.hamburger').removeClass('is-active');
                jQuery('#main-wrapper').removeClass('menu-toggle');
                jQuery('.hamburger').show();
            });
        }
    }

    var handleBtnNumber = function() {
        $('.btn-number').on('click', function(e) {
            e.preventDefault();

            fieldName = $(this).attr('data-field');
            type = $(this).attr('data-type');
            var input = $("input[name='" + fieldName + "']");
            var currentVal = parseInt(input.val());
            if (!isNaN(currentVal)) {
                if (type == 'minus')
                    input.val(currentVal - 1);
                else if (type == 'plus')
                    input.val(currentVal + 1);
            } else {
                input.val(0);
            }
        });
    }

    var handleDzChatUser = function() {
        jQuery('.dz-chat-user-box .dz-chat-user').on('click', function() {
            jQuery('.dz-chat-user-box').addClass('d-none');
            jQuery('.dz-chat-history-box').removeClass('d-none');
        });

        jQuery('.dz-chat-history-back').on('click', function() {
            jQuery('.dz-chat-user-box').removeClass('d-none');
            jQuery('.dz-chat-history-box').addClass('d-none');
        });

        jQuery('.dz-fullscreen').on('click', function() {
            jQuery('.dz-fullscreen').toggleClass('active');
        });
    }

    var handleDzLoadMore = function() {
        $(".dz-load-more").on('click', function(e) {
            e.preventDefault();
            $(this).append(' <i class="fa fa-refresh"></i>');

            var dzLoadMoreUrl = $(this).attr('rel');
            var dzLoadMoreId = $(this).attr('id');

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                method: "POST",
                url: dzLoadMoreUrl,
                dataType: 'html',
                beforeSend: function(){
                    display_loader();
                } ,
                complete: function() {
                    display_loader(false);
                },
                success: function(data) {
                    $("#" + dzLoadMoreId + "Content").append(data);
                    $('.dz-load-more i').remove();
                }
            })
        });
    }

    var handleDzFullScreen = function() {
        jQuery('.dz-fullscreen').on('click', function(e) {
            if (document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement) {
                /* Enter fullscreen */
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen(); /* IE/Edge */
                } else if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen(); /* Firefox */
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen(); /* Chrome, Safari & Opera */
                }
            } else { /* exit fullscreen */
                if (document.documentElement.requestFullscreen) {
                    document.documentElement.requestFullscreen();
                } else if (document.documentElement.webkitRequestFullscreen) {
                    document.documentElement.webkitRequestFullscreen();
                } else if (document.documentElement.mozRequestFullScreen) {
                    document.documentElement.mozRequestFullScreen();
                } else if (document.documentElement.msRequestFullscreen) {
                    document.documentElement.msRequestFullscreen();
                }
            }
        });
    }

    var handlePerfectScrollbar = function() {
        if (jQuery('.deznav-scroll').length > 0) {
            const qs = new PerfectScrollbar('.deznav-scroll');
        }
    }

    var heartBlast = function() {
        $(".heart").on("click", function() {
            $(this).toggleClass("heart-blast");
        });
    }

    var handleshowPass = function() {
        jQuery('.show-pass').on('click', function() {
            jQuery(this).toggleClass('active');
            if (jQuery('#dz-password').attr('type') == 'password') {
                jQuery('#dz-password').attr('type', 'text');
            } else if (jQuery('#dz-password').attr('type') == 'text') {
                jQuery('#dz-password').attr('type', 'password');
            }
        });
    }

    var carouselReview = function() {
        /*  event-bx one function by = owl.carousel.js */
        if ($('.event-bx').length > 0) {
            jQuery('.event-bx').owlCarousel({
                loop: true,
                margin: 30,
                nav: true,
                center: true,
                autoplaySpeed: 3000,
                navSpeed: 3000,
                paginationSpeed: 3000,
                slideSpeed: 3000,
                smartSpeed: 3000,
                autoplay: false,
                navText: ['<i class="fa fa-caret-left" aria-hidden="true"></i>', '<i class="fa fa-caret-right" aria-hidden="true"></i>'],
                dots: true,
                responsive: {
                    0: {
                        items: 1
                    },
                    720: {
                        items: 2
                    },

                    1150: {
                        items: 3
                    },

                    1200: {
                        items: 2
                    },
                    1749: {
                        items: 3
                    }
                }
            })
        }
    }

    var handleLightgallery = function() {
        if (jQuery('#lightgallery, .lightgallery').length > 0) {
            $('#lightgallery, .lightgallery').lightGallery({
                thumbnail: true,
            });
        }
    }

    /* Function ============ */
    return {
        init: function() {
            handleSelectPicker();
            handleTheme();
            handleMetisMenu();
            handleAllChecked();
            handleNavigation();
            handleCurrentActive();
            handleCustomFileInput();
            handleMiniSidebar();
            handleMinHeight();
            handleDataAction();
            handleHeaderHight();
            handleDzScroll();
            handleMenuTabs();
            handleBtnNumber();
            handleDzChatUser();
            handleDzLoadMore();
            handleDzFullScreen();
            handlePerfectScrollbar();
            heartBlast();
            handleshowPass();
            carouselReview();
            handleLightgallery();

        },


        load: function() {
            handleSelectPicker();
            handleTheme();
        },

        resize: function() {


        }
    }

}();

/* Window Load START */
jQuery(window).on('load', function() {
    'use strict';
    DropExpress.load();

});
/*  Window Load END */
/* Window Resize START */
jQuery(window).on('resize', function() {
    'use strict';
    DropExpress.resize();
});
/*  Window Resize END */

$(document).ready(function($) {
    $('#product-image').click(function() {
        document.getElementById('input-file-product-image').click();
    });
});

$(document).ready(function($) {
    $('#export-to-xlsx').click(function() {
        event.preventDefault();
        $('.buttons-excel').click();
    });
    $('#export-to-pdf').click(function() {
        event.preventDefault();
        $('.buttons-pdf').click();
    });
    $('#export-to-csv').click(function() {
        event.preventDefault();
        $('.buttons-csv').click();
    });
});

$(document).ready(function($) {
    $('#export-user-xlsx').click(function () {
     
        event.preventDefault();
        console.log("foi");
        $('.buttons-excel').click();
    });
    $('#export-user-pdf').click(function() {
        event.preventDefault();
        $('.buttons-pdf').click();
    });
    $('#export-user-csv').click(function() {
        event.preventDefault();
        $('.buttons-csv').click();
    });
});

function newexportaction(e, dt, button, config) {
    var self = this;
    var oldStart = dt.settings()[0]._iDisplayStart;
    dt.one('preXhr', function(e, s, data) {
        // Just this once, load all data from the server...
        data.start = 0;
        data.length = 2147483647;
        data.headertype = button[0].className;
        dt.one('preDraw', function(e, settings) {
            // Call the original action function
            if (button[0].className.indexOf('buttons-copy') >= 0) {
                $.fn.dataTable.ext.buttons.copyHtml5.action.call(self, e, dt, button, config);
            } else if (button[0].className.indexOf('buttons-excel') >= 0) {
                $.fn.dataTable.ext.buttons.excelHtml5.available(dt, config) ?
                    $.fn.dataTable.ext.buttons.excelHtml5.action.call(self, e, dt, button, config) :
                    $.fn.dataTable.ext.buttons.excelFlash.action.call(self, e, dt, button, config);
            } else if (button[0].className.indexOf('buttons-csv') >= 0) {
                $.fn.dataTable.ext.buttons.csvHtml5.available(dt, config) ?
                    $.fn.dataTable.ext.buttons.csvHtml5.action.call(self, e, dt, button, config) :
                    $.fn.dataTable.ext.buttons.csvFlash.action.call(self, e, dt, button, config);
            } else if (button[0].className.indexOf('buttons-pdf') >= 0) {
                $.fn.dataTable.ext.buttons.pdfHtml5.available(dt, config) ?
                    $.fn.dataTable.ext.buttons.pdfHtml5.action.call(self, e, dt, button, config) :
                    $.fn.dataTable.ext.buttons.pdfFlash.action.call(self, e, dt, button, config);
            } else if (button[0].className.indexOf('buttons-print') >= 0) {
                $.fn.dataTable.ext.buttons.print.action(e, dt, button, config);
            }
            dt.one('preXhr', function(e, s, data) {
                // DataTables thinks the first item displayed is index 0, but we're not drawing that.
                // Set the property to what it was before exporting.
                settings._iDisplayStart = oldStart;
                data.start = oldStart;
            });
            // Reload the grid with the original page. Otherwise, API functions like table.cell(this) don't work properly.
            setTimeout(dt.ajax.reload, 0);
            // Prevent rendering of the full data to the DOM
            return false;
        });
    });
    // Requery the server with the new one-time export settings
    dt.ajax.reload();
};

$(document).ready(function($) {
    $('#modalFiltrosBtn').click(function() {
        $('#modalFiltrosDiv').toggleClass('d-none');
    });
});

$(document).ready(function($) {
    $('.ofertas-vinculadas-mult-select').select2();
});

$("#link-url-checkout-to-copy").click(function() {
    event.preventDefault();
    //$("#url-checkout-to-copy").select();
    var copyText = document.getElementById("url-checkout-to-copy");
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value);
    toastr.success("Você copiou a URL de Checkout.", "Copiado!", {
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
        tapToDismiss: !0
    })
});

$(".link-url-return-to-copy").click(function() {
    event.preventDefault();

    var id = $(this).attr('data-id');

    //$("#url-return-to-copy").select();
    var urlreturntocopy = "url-return-to-copy-" + id;

    var copyText = document.getElementById(urlreturntocopy);

    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value);
    toastr.success("Você copiou a URL de Retorno da Integração.", "Copiado!", {
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
        tapToDismiss: !0
    })
});


$("#link-url-one-clique-to-copy").click(function() {
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
        tapToDismiss: !0
    })
});


$(".ofertas-vinculadas-mult-select").change(function() {
    var multipleValues = $(".ofertas-vinculadas-mult-select").val();
    $("#ofertas-vinculadas-mult-select-text").val(multipleValues);
});

$(".multselect_preload").change(function() {
    var multipleValues = $(".multselect_preload").val();
    $("#ofertas-vinculadas-mult-select-text").val(multipleValues);
});

$("#select-ship-credit-op").change(function() {
    var multipleValues = $("#select-ship-credit-op").val();
    $("#text-ship-credit-op").val(multipleValues);
});

$("#select-ship-paymethod").change(function() {
    var multipleValues = $("#select-ship-paymethod").val();
    $("#text-ship-paymethod").val(multipleValues);
});

$("#select-ship-operator").change(function() {
    var multipleValues = $("#select-ship-operator").val();
    $("#text-ship-operator").val(multipleValues);
});

$("#select-ship-paymethod-op").change(function() {
    var multipleValues = $("#select-ship-paymethod-op").val();
    $("#text-ship-paymethod-op").val(multipleValues);
    $("#text-ship-paymethod-op").trigger('change');
});
//

$("#select-filter-status-id").change(function() {
    var multipleValues = $("#select-filter-status-id").val();
    $("#text-filter-status-id").val(multipleValues);
});

$("#select-operations").change(function() {
    var multipleValues = $("#select-operations").val();
    $("#text-select-operations").val(multipleValues);
});

$("#select-operators").change(function() {
    var multipleValues = $("#select-operators").val();
    $("#text-select-operators").val(multipleValues);
});

$("#description-filter-select").change(function() {
    var multipleValues = $("#description-filter-select").val();
    $("#description-filter-text").val(multipleValues);
});

$("#status-filter-select").change(function() {
    var multipleValues = $("#status-filter-select").val();
    $("#status-filter-text").val(multipleValues);
});

//

$("#select-plano-assinante").change(function() {
    var multipleValues = $("#select-plano-assinante").val();

    var readFields = ["#tax-disabled-field", "#gateway-disabled-field", "#prazo-disabled-field", "#entrega-disabled-field"]
    var editFields = ["#tax-field", "#gateway-field", "#prazo-field", "#entrega-field"]

    var tax_input = $('#disabled-tax-input');
    var prazo_input = $('#disabled-prazo-input');
    var entrega_input = $('#disabled-entrega-input');
    switch (multipleValues) {
        case "1":
            hideEditableFields();
            showDisabledFields();

            tax_input.val("7,97%");
            prazo_input.val("30 dias");
            entrega_input.val("R$29,90");

            values = ["0.0797", "30", "29.9"];
            populateFormdata(values);
            console.log(values)

            break;
        case "2":
            hideEditableFields();
            showDisabledFields();

            tax_input.val("6,97%");
            prazo_input.val("14 dias");
            entrega_input.val("R$28,90");

            values = ["0.0697", "14", "28.9"];
            populateFormdata(values);
            console.log(values)

            break;
        case "3":
            hideEditableFields();
            showDisabledFields();

            tax_input.val("5,97%");
            prazo_input.val("7 dias");
            entrega_input.val("R$26,90");

            values = ["0.0597", "7", "27.9"];
            populateFormdata(values);
           

            break;
        case "4":
           
            hideDisabledFields();
            showEditableFields();
            break;
    }
    $("#text-plano-assinante").val(multipleValues);

    function populateFormdata(values) {
        $("#text-taxa-assinante").val(values[0]);
        $("#text-prazo-assinante").val(values[1]);
        $("#text-entrega-assinante").val(values[2]);

        $("#select-taxa-assinante").val(values[0]).change();
        $("#select-prazo-assinante").val(values[1]).change();
    }

    function showEditableFields() {
        editFields.forEach(field => {
            $(field).attr("style", "display: block !important");
        })
    }

    function hideEditableFields() {
        editFields.forEach(field => {
            $(field).attr("style", "display: none !important");
        })
    }

    function hideDisabledFields() {
        readFields.forEach(field => {
            $(field).attr("style", "display: none !important");
        })
    }

    function showDisabledFields() {
        readFields.forEach(field => {
            $(field).attr("style", "display: block !important");
        })
    }
});

$("#select-filter-status-id").change(function() {
    var multipleValues = $("#select-filter-status-id").val();
    $("#text-filter-status-id").val(multipleValues);
});

$("#select-operations").change(function() {
    var multipleValues = $("#select-operations").val();
    $("#text-select-operations").val(multipleValues);
});

$("#select-operators").change(function() {
    var multipleValues = $("#select-operators").val();
    $("#text-select-operators").val(multipleValues);
});

$("#select-filter-resp-id").change(function() {
    var multipleValues = $("#select-filter-resp-id").val();
    $("#text-filter-resp-id").val(multipleValues);
});

$("#select-uf-operacao").change(function() {
    var multipleValues = $("#select-uf-operacao").val();
    $("#text-uf-operacao").val(multipleValues);
    $("#text-uf-operacao").trigger("change")
});

$("#select-cidade-operacao").change(function() {
    var multipleValues = $("#select-cidade-operacao").val();
    $("#text-cidade-operacao").val(multipleValues);
});

$("#select-taxa-credito").change(function() {
    var multipleValues = $("#select-taxa-credito").val();
    $("#text-taxa-credito").val(multipleValues);
    $("#text-taxa-credito").trigger('change');
});

$("#select-taxa-cidades").change(function() {
    var multipleValues = $("#select-taxa-cidades").val();
    $("#text-cidade-taxa").val(multipleValues);
    $("#text-cidade-taxa").trigger('change');
});

$("#select-operacao-local").change(function() {
    var multipleValues = $("#select-operacao-local").val();
    $("#text-operacao-local").val(multipleValues);
    $("#text-operacao-local").trigger('change');
});

$("#select-bank-acc-type-id").change(function() {
    var multipleValues = $("#select-bank-acc-type-id").val();
    $("#text-bank-acc-type-id").val(multipleValues);
});


$("#select-filter-period-id").change(function() {
    var multipleValues = $("#select-filter-period-id").val();
    $("#text-filter-period-id").val(multipleValues);
});

$("#select-taxa-assinante").change(function() {
    var multipleValues = $("#select-taxa-assinante").val();
    $("#text-taxa-assinante").val(multipleValues);
});

$("#select-prazo-assinante").change(function() {
    var multipleValues = $("#select-prazo-assinante").val();
    $("#text-prazo-assinante").val(multipleValues);
});

$("#select-entrega-assinante").change(function() {
    var multipleValues = $("#select-entrega-assinante").val();
    $("#text-entrega-assinante").val(multipleValues);
});

$("#select-ship-product").change(function() {
    var multipleValues = $("#select-ship-product").val();
    $("#text-ship-product").val(multipleValues);
});

$("#select-ship-name").change(function() {
    var multipleValues = $("#select-ship-name").val();
    $("#text-ship-name").val(multipleValues);
});

$("#select-filter-description-id").change(function() {
    var multipleValues = $("#select-filter-description-id").val();
    $("#text-filter-description-id").val(multipleValues);
});

$("#select-filter-description-id").change(function() {
    var multipleValues = $("#select-filter-description-id").val();
    $("#text-filter-description-id").val(multipleValues);
});

$("#select-ship-tipo").change(function() {
    var multipleValues = $("#select-ship-tipo").val();
    $("#text-ship-tipo").val(multipleValues);
});

$("#select-ship-locale").change(function() {
    var multipleValues = $("#select-ship-locale").val();
    $("#text-ship-locale").val(multipleValues);
    $("#text-ship-locale").trigger('change');
});

$("#select-tipo-conta").change(function() {
    var multipleValues = $("#select-tipo-conta").val();
    $("#text-tipo-conta").val(multipleValues);
});

jQuery.fn.preventDoubleSubmit = function() {
    jQuery(this).submit(function() {
        if (this.beenSubmitted) {
            $('input[type=submit]', this).attr("disabled", "disabled");
            return false;
        } else {
            this.beenSubmitted = true;
        }
    });
};


/* AJAX */
$(document).ready(function($) {

    jQuery('form').preventDoubleSubmit();

    $('div[data-toggle="toggle"]').click(function() {
        console.log("toggle_show_cancelled value: " + $("#toggle_show_cancelled").prop("checked"))
    })

    //Upload
    $('#ReleaseBillingForm').submit(function() {

        $("#processBillingBtn").attr("disabled", "disabled");
        // Captura os dados do formulário
        var ReleaseBillingForm = document.getElementById('ReleaseBillingForm');

        // Instância o FormData passando como parâmetro o formulário
        var formData = new FormData(ReleaseBillingForm);

        // Envia O FormData através da requisição AJAX
        $.ajax({
            url: u + "/ajax/billing-release-ajax.php",
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
                    Swal.fire({
                        title: feedback.title,
                        text: feedback.msg,
                        icon: feedback.type,
                    }).then((value) => {
                        $("#processBillingBtn").removeAttr("disabled");
                        if (feedback.type == 'success') {
                            document.location.reload(true);
                        }
                    });
                }
                // }).fail(function (data) {
                // 	Swal.fire({
                // 		title: "Erro #RB001",
                // 		text: "Não foi possível processar o saque. Contate o suporte!",
                // 		icon: 'error',
                // 	});
        });


        return false;
    });


    // Carrega a imagem selecionada no elemento <img>
    $("#input-file-product-image").on("change", function() {
        var files = !!this.files ? this.files : [];
        if (!files.length || !window.FileReader) return;

        if (/^image/.test(files[0].type)) {
            var reader = new FileReader();
            reader.readAsDataURL(files[0]);

            reader.onload = function() {
                $("#product-image").attr('src', this.result);
            }
        }
    });

    //SaleForm Submit
    $('#AddSaleForm').submit(function() {

        // Captura os dados do formulário
        var AddSaleForm = document.getElementById('AddSaleForm');

        // Instância o FormData passando como parâmetro o formulário
        var formData = new FormData(AddSaleForm);

        var url = u + "/ajax/add-sale-ajax.php";

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
            success: function(feedback) {
                Swal.fire({
                    title: feedback.title,
                    text: feedback.msg, //"Você receberá seu produto na data e perído selecionados.",
                    icon: feedback.type,
                }).then(response => {
                    if (feedback.type == 'success') {
                        document.location.reload(true);
                    }
                })
            }
        }).fail(function(data) {
            Swal.fire({
                title: "Erro #PAS001",
                text: "Não foi possível criar sua oferta..",
                icon: 'warning',
            });
        });

        return false;
    });

    //SaleForm Submit
    $('#AddCouponForm').submit(function() {

        //var multipleValues = $(".ofertas-vinculadas-mult-select").val() || [];
        // When using jQuery 3:
        var multipleValues = $('.ofertas-vinculadas-mult-select').val();
        $("#ofertas-vinculadas-mult-select-text").val(multipleValues);

        // Captura os dados do formulário
        var AddCouponForm = document.getElementById('AddCouponForm');

        // Instância o FormData passando como parâmetro o formulário
        var formData = new FormData(AddCouponForm);

        var url = u + "/ajax/add-coupon-ajax.php";

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
            success: function(feedback) {
                    if (feedback.status > 0) {
                        Swal.fire({
                            title: "Cupom Criado!",
                            text: feedback.msg,
                            icon: 'success',
                        }).then((value) => {
                            document.location.reload(true);
                        });
                    } else {
                        Swal.fire({
                            title: "Erro Interno",
                            text: feedback.msg,
                            type: 'erroe',
                        });
                    }
                }
                // }).fail(function (data) {
                //     Swal.fire({
                //         title: "Erro #PAS001",
                //         text: "Não foi possível criar o cupom",
                //         icon: 'warning',
                //     });
        });
    });

    $('#RescheduleOrderForm').submit(function() {

        // Captura os dados do formulário
        var RescheduleOrderForm = document.getElementById('RescheduleOrderForm');

        // Instância o FormData passando como parâmetro o formulário
        var formData = new FormData(RescheduleOrderForm);

        // Envia O FormData através da requisição AJAX
        $.ajax({
            url: u + "/ajax/reschedule-order.php",
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
                Swal.fire({
                    title: feedback.title,
                    text: feedback.msg,
                    icon: feedback.type,
                }).then((value) => {
                    if (feedback.type == 'success') {
                        window.location.assign("../../pedidos/");
                    }
                });

            }
        });

        return false;
    });

    $("#text-ship-paymethod-op").change(e => {
        if (e.target.value == "credit") {
            $("#op-credit-options").attr("style", "display: block !important;");
            $("#op-credit-options").attr("required");
        } else {
            $("#op-credit-options").removeAttr("style");
            $("#op-credit-options").removeAttr("required");
        }
    });

    $('#FailOrderForm').submit(function() {

        // Captura os dados do formulário
        var FailOrderForm = document.getElementById('FailOrderForm');

        // Instância o FormData passando como parâmetro o formulário
        var formData = new FormData(FailOrderForm);

        // Envia O FormData através da requisição AJAX
        $.ajax({
            url: u + "/ajax/fail-order.php",
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
                Swal.fire({
                    title: feedback.title,
                    text: feedback.msg,
                    icon: feedback.type,
                }).then((value) => {
                    if (feedback.type == 'success') {
                        window.location.assign("../../pedidos/");
                    }
                });

            }
        });

        return false;
    });


    $('#CompleteOrderForm').submit(function() {

        if ($("#text-ship-paymethod-op").val() == "") {
            Swal.fire({
                title: "Erro!",
                text: "Informe a forma de pagamento",
                icon: 'error',
            })
            return false;
        }
        if ($("#text-ship-operator").val() == "") {
            Swal.fire({
                title: "Erro!",
                text: "Informe um operador válido",
                icon: 'error',
            })
            return false;
        }
        if ($("#cpf-cliente").val() != "" && !validaCpfCnpj($("#cpf-cliente").val())) {
            Swal.fire({
                title: "Erro!",
                text: "Informe um CPF válido",
                icon: 'error',
            })
            return false;
        }

        // Captura os dados do formulário
        var CompleteOrderForm = document.getElementById('CompleteOrderForm');

        // Instância o FormData passando como parâmetro o formulário
        var formData = new FormData(CompleteOrderForm);

        // Envia O FormData através da requisição AJAX
        $.ajax({
            url: u + "/ajax/complete-order.php",
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
                Swal.fire({
                    title: feedback.title,
                    text: feedback.msg,
                    icon: feedback.type,
                }).then((value) => {
                    if (feedback.type == 'success') {
                        window.location.assign("../../pedidos/");
                    }
                });

            }
        });

        return false;
    });


    //Update CouponForm Submit
    $('#UpdateCouponForm').submit(function() {

        // Captura os dados do formulário
        var UpdateCouponForm = document.getElementById('UpdateCouponForm');

        // Instância o FormData passando como parâmetro o formulário
        var formData = new FormData(UpdateCouponForm);

        var url = u + "/ajax/update-coupon-ajax.php";

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
            success: function(feedback) {
                if (feedback.status == 1) {
                    Swal.fire({
                        title: "Alterações Salvas!",
                        text: feedback.msg,
                        icon: 'success',
                    }).then((value) => {
                        document.location.reload(true);
                    });
                } else {
                    Swal.fire({
                        title: feedback.title,
                        text: feedback.msg,
                        icon: 'warning',
                    });
                }

            }
        });

        return false;
    });


    //AddDomainForm Form Submit
    $('#AddDomainForm').submit(function() {

        // Captura os dados do formulário
        var AddDomainForm = document.getElementById('AddDomainForm');

        // Instância o FormData passando como parâmetro o formulário
        var formData = new FormData(AddDomainForm);

        // Envia O FormData através da requisição AJAX
        $.ajax({
            url: "add-domain-ajax.php",
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
            success: function() {
                document.getElementById('AddDomainForm');
            }
        });

        return false;
    });


    //AddSubscriber Submit
    $('#AddSubscriberForm').submit(function(e) {

        var select_plano_assinante = $('#select-plano-assinante').val();
        var select_taxa_assinante = $('#select-taxa-assinante').val();
        var select_prazo_assinante = $('#select-prazo-assinante').val();
        var valor_entrega_assinante = $('#select-entrega-assinante').val();
        var valor_entrega_assinante = $('#text-entrega-assinante').val();
        var valor_plano_assinante = $('#text-plano-assinante').val();

        if (select_plano_assinante == null || select_taxa_assinante == null || select_prazo_assinante == null || valor_entrega_assinante == null) {
            Swal.fire({
                title: "Erro!",
                text: "Todos os detalhes do plano precisam ser informados.",
                icon: 'warning',
            })
            return false;
        }
       

        // Captura os dados do formulário
        var AddSubscriberForm = document.getElementById('AddSubscriberForm');

        // Instância o FormData passando como parâmetro o formulário
        var formData = new FormData(AddSubscriberForm);
      //  console.log(valor_entrega_assinante);

       // Envia O FormData através da requisição AJAX
        $.ajax({
            url: u + "/ajax/add-subscriber-ajax.php",
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
                if (feedback.status > 0) {
                    var url = feedback.url;
                    Swal.fire({
                        title: feedback.msg ,
                        text: "Ele receberá um email com o login e a senha de acesso.",
                        icon: 'success',
                    }).then((value) => {
                        window.open(url, '_self');
                    });
                } else {
                    Swal.fire({
                        title: "Erro!",
                        text: feedback.msg,
                        icon: 'warning',
                    })
                }
            }
        });

        return false;
    });


    //AddSubscriber Submit
    $('#SubscriberPayInfos').submit(function() {

        var select_acc_type = $('#select-tipo-conta').val();

        if (select_acc_type == null) {
            Swal.fire({
                title: "Erro!",
                text: "Informe o tipo de conta",
                icon: 'error',
            })
            return false;
        }

        // Captura os dados do formulário
        var SubscriberPayInfos = document.getElementById('SubscriberPayInfos');

        // Instância o FormData passando como parâmetro o formulário
        var formData = new FormData(SubscriberPayInfos);

        // Envia O FormData através da requisição AJAX
        $.ajax({
            url: "ajax/add-subscriber-ajax.php",
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
                if (feedback.status > 0) {
                    Swal.fire({
                        title: "Sucesso!",
                        text: "Suas informações de pagamento foram atualizadas!",
                        icon: 'success',
                    }).then((value) => {
                        document.location.reload(true);
                    });
                } else {
                    Swal.fire({
                        title: "Erro!",
                        text: feedback.msg,
                        icon: 'error',
                    })
                }
            }
        });

        return false;
    });

    //Update order status from /pedidos in ADM account.
    $('.update-order-status').click(function() {

        event.preventDefault();
        var status = this.getAttribute('data-status');

        var id = this.getAttribute('data-id');

        // Envia os parâmetro para o PHP via AJAX
        $.ajax({
            url: u + "/update-order-status.php",
            type: "GET",
            data: { status, id },
            dataType: 'json',
            processData: true,
            contentType: false,
            beforeSend: function(){
                display_loader();
            } ,
            complete: function() {
                display_loader(false);
            },
            success: function(feedback) {
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
        }).fail(function(data) {
            Swal.fire({
                title: "Erro de Conexão",
                text: "Quando a página atulizar, tente mudar o status do pedido novamente.",
                icon: 'warning',
            }).then((value) => {
                document.location.reload(true);
            });
        });

        return false;
    });

    //Update shipment status from /pedidos in ADM account.
    $('.update-shipment-status-link').click(function() {

        event.preventDefault();

        var status = this.getAttribute('data-status');
        var id = this.getAttribute('data-id');
        var product = this.getAttribute('data-produto');
        var locale = this.getAttribute('data-local');
        var type = this.getAttribute('data-type')
        var quant = this.getAttribute('data-quantidade');

        var url = u + "/ajax/update-shipment-status.php";


        // Envia os parâmetro para o PHP via AJAX
        $.ajax({
            url: url,
            type: "GET",
            data: { status, id, product, locale, quant, type },
            dataType: 'json',
            processData: true,
            contentType: false,
            beforeSend: function(){
                display_loader();
            } ,
            complete: function() {
                display_loader(false);
            },
            success: function(feedback) {
                Swal.fire({
                    title: "Sucesso!",
                    text: feedback.msg,
                    icon: 'success',
                }).then((value) => {
                    document.location.reload(true);
                });
            }
        });

        return false;
    });




    $(document).ready(function($) {
        $('#sale-name-input').focusout(function() {

            var string = $('#sale-name-input').val();
            var url = u + "/ajax/url-friendly-create.php";

            // Envia O FormData através da requisição AJAX
            $.ajax({
                url: url,
                type: "GET",
                data: { string },
                dataType: 'json',
                processData: true,
                contentType: false,
                beforeSend: function(){
                    display_loader();
                } ,
                complete: function() {
                    display_loader(false);
                },
                success: function(feedback) {
                    $('#url-friedly-input').val(feedback.url);
                }
            });
        });

        $('#edit-sale-name-input').on('change', function() {

            var string = $('#edit-sale-name-input').val();
            var url = u + "/ajax/url-friendly-create.php";

            // Envia O FormData através da requisição AJAX
            $.ajax({
                url: url,
                type: "GET",
                data: { string },
                dataType: 'json',
                processData: true,
                contentType: false,
                beforeSend: function(){
                    display_loader();
                } ,
                complete: function() {
                    display_loader(false);
                },
                success: function(feedback) {
                    $('#url-friedly-input').val(feedback.url);
                }
            });
        });

        $('#url-friedly-input').on('change', function() {

            var string = $('#url-friedly-input').val();
            var url = u + "/ajax/url-friendly-create.php";

            // Envia O FormData através da requisição AJAX
            $.ajax({
                url: url,
                type: "GET",
                data: { string },
                dataType: 'json',
                processData: true,
                contentType: false,
                beforeSend: function(){
                    display_loader();
                } ,
                complete: function() {
                    display_loader(false);
                },
                success: function(feedback) {
                    $('#url-friedly-input').val(feedback.url);
                    if (feedback.unified == 1) {
                        toastr.success("A URL da oferta foi alterada para evitar duplicidade de URLs.", "URL Amigável Ajustada!", {
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
                            tapToDismiss: !0
                        })
                    }
                }
            });
        });

    });

    //Trash the sale.
    $('.update-sale-status').click(function() {

        event.preventDefault();

        var sid = this.getAttribute('data-sid');
        var pid = this.getAttribute('data-pid');
        var url = u + "/update-sale-status.php";

        // Envia os parâmetro para o PHP via AJAX
        $.ajax({
            url: url,
            type: "GET",
            data: { sid, pid },
            dataType: 'json',
            processData: true,
            contentType: false,
            beforeSend: function(){
                display_loader();
            } ,
            complete: function() {
                display_loader(false);
            },
            success: function(feedback) {
                Swal.fire({
                    title: "Sucesso!",
                    text: feedback.msg,
                    icon: 'success',
                }).then((value) => {
                    document.location.reload(true);
                });
            }
        });

        return false;
    });

});

function limpa_formulário_cep() {
    //Limpa valores do formulário de cep.
    document.getElementById('rua').value = ("");
    document.getElementById('bairro').value = ("");
    document.getElementById('cidade').value = ("");
    document.getElementById('uf').value = ("");
}

//Exibe o checkout com base na localidade
function generate_checkout(in_stock, locale_id) {

    $('#in-stock-checkout').addClass('d-none');
    $('#no-stock-checkout').addClass('d-none');
    $('#delivery-resp-msg').addClass('d-none');

    if (in_stock > 0) {
        $('#in-stock-checkout').removeClass('d-none');
        $('#delivery-resp-msg').removeClass('d-none');
        $('#submit-btn').text("Concluir Pedido");
        $('#hdn-inpt-action').val("done-order");
    } else {
        $('#no-stock-checkout').removeClass('d-none');
        $('#submit-btn').text("Prosseguir para Pagamento");
        $('#hdn-inpt-action').val("proceed-to-checkout");
    }

}

//Realiza Callback com os dados dados retornados pela viacep
function meu_callback(conteudo) {
    if (!("erro" in conteudo)) {
        //Atualiza os campos com os valores.
        document.getElementById('rua').value = (conteudo.logradouro);
        document.getElementById('bairro').value = (conteudo.bairro);
        document.getElementById('cidade').value = (conteudo.localidade);
        document.getElementById('uf').value = (conteudo.uf);

        var locale = conteudo.localidade;

        //Identifica a localidade do CEP via PHP/AJAX
        $.ajax({
            url: "../includes/classes/IdentifyLocale.php",
            type: "GET",
            data: { locale },
            dataType: 'json',
            processData: true,
            contentType: false,
            beforeSend: function(){
                display_loader();
            } ,
            complete: function() {
                display_loader(false);
            },
            success: function(feedback) {
                generate_checkout(feedback.in_stock, feedback.locale_id);
            }
        });

    } //end if.
    else {
        //CEP não Encontrado.
        limpa_formulário_cep();
        Swal.fire({
            title: "CEP Não encontrado!",
            text: "Certifique-se de informar corretamente seu CEP.",
            icon: 'error',
        });
    }
}

function pesquisacep(valor) {

    //Nova variável "cep" somente com dígitos.
    var cep = valor.replace(/\D/g, '');

    //Verifica se campo cep possui valor informado.
    if (cep != "") {

        //Expressão regular para validar o CEP.
        var validacep = /^[0-9]{8}$/;

        //Valida o formato do CEP.
        if (validacep.test(cep)) {

            //Preenche os campos com "..." enquanto consulta webservice.
            document.getElementById('rua').value = "...";
            document.getElementById('bairro').value = "...";
            document.getElementById('cidade').value = "...";
            document.getElementById('uf').value = "...";

            //Cria um elemento javascript.
            var script = document.createElement('script');

            //Sincroniza com o callback.
            script.src = 'https://viacep.com.br/ws/' + cep + '/json/?callback=meu_callback';

            //Insere script no documento e carrega o conteúdo.
            document.body.appendChild(script);

        } //end if.
        else {
            //cep é inválido.
            limpa_formulário_cep();
            Swal.fire({
                title: "CEP Inválido!",
                text: "Informe corretamente seu CEP.",
                icon: 'error',
            });
        }
    } //end if.
    else {
        //cep sem valor, limpa formulário.
        limpa_formulário_cep();
    }
};

$('#AddOperationForm').submit(function() {

    var nome_operacao = $('#text-nome-operacao').val();

    var cep = $('#CEP').val();
    var rua = $('#rua').val();
    var numero = $('#numero').val();
    var bairro = $('#bairro').val();
    var cidade = $('#cidade').val();
    var uf = $('#uf').val();
    var referencia = $('#referencia-operacao').val();

    var telefone = $("#text-telefone-destinatario").val();
    var doc = $("#text-doc-destinatario").val();

    var estado_operacao = $('#text-uf-operacao').val();
    var cidade_operacao = $('#text-cidade-operacao').val();

    if (cep == null || rua == null || numero == null || bairro == null || cidade == null || nome_operacao == null || estado_operacao == null || cidade_operacao == null || uf == null || referencia == null || doc == null || telefone == null) {
        Swal.fire({
            title: "Erro!",
            text: "Todos os detalhes do plano precisam ser informados.",
            icon: 'warning',
        })
        return false;
    }

    // Captura os dados do formulário
    var AddOperationForm = document.getElementById('AddOperationForm');

    // Instância o FormData passando como parâmetro o formulário
    var formData = new FormData(AddOperationForm);
    formData.append("cidades-operacao", cidade_operacao);
    formData.append("uf-operacao", estado_operacao);

    var url = u + "/ajax/add-operation-ajax.php";
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
        success: function(feedback) {
            if (feedback.status > 0) {
                Swal.fire({
                    title: "Sucesso!",
                    text: "A operação foi cadastrada com sucesso.",
                    icon: 'success',
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    title: "Erro!",
                    text: feedback.msg,
                    icon: 'warning',
                })
            }
        }
    });

    return false;
});

$("#UpdateOperationForm").submit(function () {
    var nome_operacao = $("#text-nome-operacao").val();

    var cep = $("#CEP").val();
    var rua = $("#rua").val();
    var numero = $("#numero").val();
    var bairro = $("#bairro").val();
    var cidade = $("#cidade").val();
    var uf = $("#uf").val();
    var referencia = $("#referencia-operacao").val();

    var telefone = $("#text-telefone-destinatario").val();
    var doc = $("#text-doc-destinatario").val();

    var estado_operacao = $("#text-uf-operacao").val();
    var cidade_operacao = $("#text-cidade-operacao").val();

    if (
        cep == null ||
        rua == null ||
        numero == null ||
        bairro == null ||
        cidade == null ||
        nome_operacao == null ||
        estado_operacao == null ||
        cidade_operacao == null ||
        uf == null ||
        referencia == null ||
        doc == null ||
        telefone == null
    ) {
        Swal.fire({
            title: "Erro!",
            text: "Todos os detalhes do plano precisam ser informados.",
            icon: "warning",
        });
        return false;
    }

    // Captura os dados do formulário
    var UpdateOperationForm = document.getElementById("UpdateOperationForm");

    // Instância o FormData passando como parâmetro o formulário
    var formData = new FormData(UpdateOperationForm);
    formData.append("cidades-operacao", cidade_operacao);
    formData.append("uf-operacao", estado_operacao);

    var url = u + "/ajax/add-operation-ajax.php";
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
                    html: "A operação foi atualizada com sucesso.",
                    icon: "success",
                }).then(() => {
                    window.location.href =
                        window.location.origin + "/localidades/operacoes-locais/";
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

//Inputs Mask
$('.cc-number').mask('0000 0000 0000 0000');
$('.cc-expiration').mask('00/00');
$('.cc-cvv').mask('0000');
$('.date').mask('00/00/0000');
$('.cep').mask('00000-000');
$('.phone').mask('(00) 000000000');
$('.whats').mask('(00) 0 0000-0000');
$('.cpf').mask('000.000.000-00', { reverse: true });
$('.cnpj').mask('00.000.000/0000-00', { reverse: true });
$('.money').mask("#.##0,00", { reverse: true });
$('.documento').mask('000.000.000-00', {
    onKeyPress : function(cpfcnpj, e, field, options) {
      const masks = ['000.000.000-000', '00.000.000/0000-00'];
      const mask = (cpfcnpj.length > 14) ? masks[1] : masks[0];
      $('.documento').mask(mask, options); 
    }
});

function MascaraMoeda(objTextBox, SeparadorMilesimo, SeparadorDecimal, e) {
    var sep = 0;
    var key = '';
    var i = j = 0;
    var len = len2 = 0;
    var strCheck = '0123456789';
    var aux = aux2 = '';
    var whichCode = (window.Event) ? e.which : e.keyCode;
    if (whichCode == 13) return true;
    key = String.fromCharCode(whichCode); // Valor para o código da Chave
    if (strCheck.indexOf(key) == -1) return false; // Chave inválida
    len = objTextBox.value.length;
    for (i = 0; i < len; i++)
        if ((objTextBox.value.charAt(i) != '0') && (objTextBox.value.charAt(i) != SeparadorDecimal)) break;
    aux = '';
    for (; i < len; i++)
        if (strCheck.indexOf(objTextBox.value.charAt(i)) != -1) aux += objTextBox.value.charAt(i);
    aux += key;
    len = aux.length;
    if (len == 0) objTextBox.value = '';
    if (len == 1) objTextBox.value = '0' + SeparadorDecimal + '0' + aux;
    if (len == 2) objTextBox.value = '0' + SeparadorDecimal + aux;
    if (len > 2) {
        aux2 = '';
        for (j = 0, i = len - 3; i >= 0; i--) {
            if (j == 3) {
                aux2 += SeparadorMilesimo;
                j = 0;
            }
            aux2 += aux.charAt(i);
            j++;
        }
        objTextBox.value = '';
        len2 = aux2.length;
        for (i = len2 - 1; i >= 0; i--)
            objTextBox.value += aux2.charAt(i);
        objTextBox.value += SeparadorDecimal + aux.substr(len - 2, len);
    }
    return false;
}

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

display_loader = (verify = true) => {
    if (verify) {
      $("#preloader").css("display", "flex");
      $("#preloader").css("background-color", "hsl(0 0% 100% / 40%)");
      $("#preloader .sk-three-bounce").css("background-color", "unset");
      $("#preloader").css("z-index", "1000");
      return "atualizando...";
    }
    $("#preloader").css("display", "none");
    $("#preloader").css("z-index", "unset");
    $("#preloader").css("background-color", "white");
    $("#preloader .sk-three-bounce").css("background-color", "white");
    return "liberado...";
}