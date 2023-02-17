/**
Core script to handle the entire core functions
**/
var u = location.protocol + "//" + window.location.hostname;

/* Document.ready Start */
jQuery(document).ready(function () {
    
    $( "#helper" ).click(function() {
      $( ".message-helper" ).toggle();
    });
    
    $('.btn-copy-address').each(function( index ) {
        
         $(this).click(function () {

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
    
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
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
            'csv', 'excel',
            {
                extend: 'pdf',
                orientation: 'landscape',
                title: "Pedidos - DropExpress",
                footer: true
            }
        ]
    });

    $('#orders-list').DataTable({
        searching: false,
        paging: true,
        select: false,
        lengthChange: false,
    });

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
            paging: false,
            select: false,
            lengthChange: false,

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
        $('#example tbody').on('click', 'tr', function () {
            var data = table.row(this).data();

        });
    }

    if ($('#smartwizard').length > 0) {
        $('#smartwizard').smartWizard();
    }


});
/* Document.ready END */

var DropExpress = function () {
	/* Search Bar ============ */
	var screenWidth = $(window).width();

	var handleSelectPicker = function () {
		if (jQuery('.default-select').length > 0) {
			jQuery('.default-select').selectpicker();
		}
	}

	var handleTheme = function () {
		$('#preloader').fadeOut(500);
		$('#main-wrapper').addClass('show');
	}

	var handleMetisMenu = function () {
		if (jQuery('#menu').length > 0) {
			$("#menu").metisMenu();
		}
		jQuery('.metismenu > .mm-active ').each(function () {
			if (!jQuery(this).children('ul').length > 0) {
				jQuery(this).addClass('active-no-child');
			}
		});
	}

	var handleAllChecked = function () {
		$("#checkAll").on('change', function () {
			$("td input:checkbox, .email-list .custom-checkbox input:checkbox").prop('checked', $(this).prop("checked"));
		});
	}

	var handleNavigation = function () {
		$(".nav-control").on('click', function () {

			$('#main-wrapper').toggleClass("menu-toggle");

			$(".hamburger").toggleClass("is-active");
		});
	}

	var handleCurrentActive = function () {
		for (var nk = window.location,
			o = $("ul#menu a").filter(function () {

				return this.href == nk;

			})
				.addClass("mm-active")
				.parent()
				.addClass("mm-active"); ;) {

			if (!o.is("li")) break;

			o = o.parent()
				.addClass("mm-show")
				.parent()
				.addClass("mm-active");
		}
	}

	var handleCustomFileInput = function () {
		$(".custom-file-input").on("change", function () {
			var fileName = $(this).val().split("\\").pop();
			$(this).siblings(".custom-file-label").addClass("selected").html(fileName);
		});
	}

	var handleMiniSidebar = function () {
		$("ul#menu>li").on('click', function () {
			const sidebarStyle = $('body').attr('data-sidebar-style');
			if (sidebarStyle === 'mini') {
				console.log($(this).find('ul'))
				$(this).find('ul').stop()
			}
		})
	}

	var handleMinHeight = function () {
		var win_h = window.outerHeight;
		var win_h = window.outerHeight;
		if (win_h > 0 ? win_h : screen.height) {
			$(".content-body").css("min-height", (win_h + 60) + "px");
		};
	}

	var handleDataAction = function () {
		$('a[data-action="collapse"]').on("click", function (i) {
			i.preventDefault(),
				$(this).closest(".card").find('[data-action="collapse"] i').toggleClass("mdi-arrow-down mdi-arrow-up"),
				$(this).closest(".card").children(".card-body").collapse("toggle");
		});

		$('a[data-action="expand"]').on("click", function (i) {
			i.preventDefault(),
				$(this).closest(".card").find('[data-action="expand"] i').toggleClass("icon-size-actual icon-size-fullscreen"),
				$(this).closest(".card").toggleClass("card-fullscreen");
		});



		$('[data-action="close"]').on("click", function () {
			$(this).closest(".card").removeClass().slideUp("fast");
		});

		$('[data-action="reload"]').on("click", function () {
			var e = $(this);
			e.parents(".card").addClass("card-load"),
				e.parents(".card").append('<div class="card-loader"><i class=" ti-reload rotate-refresh"></div>'),
				setTimeout(function () {
					e.parents(".card").children(".card-loader").remove(),
						e.parents(".card").removeClass("card-load")
				}, 2000)
		});
	}

	var handleHeaderHight = function () {
		const headerHight = $('.header').innerHeight();
		$(window).scroll(function () {
			if ($('body').attr('data-layout') === "horizontal" && $('body').attr('data-header-position') === "static" && $('body').attr('data-sidebar-position') === "fixed")
				$(this.window).scrollTop() >= headerHight ? $('.deznav').addClass('fixed') : $('.deznav').removeClass('fixed')
		});
	}

	var handleDzScroll = function () {
		jQuery('.dz-scroll').each(function () {

			var scroolWidgetId = jQuery(this).attr('id');
			const ps = new PerfectScrollbar('#' + scroolWidgetId, {
				wheelSpeed: 2,
				wheelPropagation: true,
				minScrollbarLength: 20
			});
		})
	}

	var handleMenuTabs = function () {
		if (screenWidth <= 991) {
			jQuery('.menu-tabs .nav-link').on('click', function () {
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
			jQuery('.close-fixed-content').on('click', function () {
				jQuery('.fixed-content-box').removeClass('active');
				jQuery('.hamburger').removeClass('is-active');
				jQuery('#main-wrapper').removeClass('menu-toggle');
				jQuery('.hamburger').show();
			});
		}
	}

	var handleBtnNumber = function () {
		$('.btn-number').on('click', function (e) {
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

	var handleDzChatUser = function () {
		jQuery('.dz-chat-user-box .dz-chat-user').on('click', function () {
			jQuery('.dz-chat-user-box').addClass('d-none');
			jQuery('.dz-chat-history-box').removeClass('d-none');
		});

		jQuery('.dz-chat-history-back').on('click', function () {
			jQuery('.dz-chat-user-box').removeClass('d-none');
			jQuery('.dz-chat-history-box').addClass('d-none');
		});

		jQuery('.dz-fullscreen').on('click', function () {
			jQuery('.dz-fullscreen').toggleClass('active');
		});
	}

	var handleDzLoadMore = function () {
		$(".dz-load-more").on('click', function (e) {
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
				success: function (data) {
					$("#" + dzLoadMoreId + "Content").append(data);
					$('.dz-load-more i').remove();
				}
			})
		});
	}

	var handleDzFullScreen = function () {
		jQuery('.dz-fullscreen').on('click', function (e) {
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
			}
			else { /* exit fullscreen */
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

	var handlePerfectScrollbar = function () {
		if (jQuery('.deznav-scroll').length > 0) {
			const qs = new PerfectScrollbar('.deznav-scroll');
		}
	}

	var heartBlast = function () {
		$(".heart").on("click", function () {
			$(this).toggleClass("heart-blast");
		});
	}

	var handleshowPass = function () {
		jQuery('.show-pass').on('click', function () {
			jQuery(this).toggleClass('active');
			if (jQuery('#dz-password').attr('type') == 'password') {
				jQuery('#dz-password').attr('type', 'text');
			} else if (jQuery('#dz-password').attr('type') == 'text') {
				jQuery('#dz-password').attr('type', 'password');
			}
		});
	}

	var carouselReview = function () {
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

	var handleLightgallery = function () {
		if (jQuery('#lightgallery, .lightgallery').length > 0) {
			$('#lightgallery, .lightgallery').lightGallery({
				thumbnail: true,
			});
		}
	}

	/* Function ============ */
	return {
		init: function () {
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


		load: function () {
			handleSelectPicker();
			handleTheme();
		},

		resize: function () {


		}
	}

}();

/* Window Load START */
jQuery(window).on('load', function () {
	'use strict';
	DropExpress.load();

});
/*  Window Load END */
/* Window Resize START */
jQuery(window).on('resize', function () {
	'use strict';
	DropExpress.resize();
});
/*  Window Resize END */


$(document).ready(function ($) {
	$('#product-image').click(function () {
		document.getElementById('input-file-product-image').click();
	});
});

$(document).ready(function ($) {
	$('#export-to-xlsx').click(function () {
        event.preventDefault();
		$('.buttons-excel').click();
	});
	$('#export-to-pdf').click(function () {
        event.preventDefault();
		$('.buttons-pdf').click();
	});
	$('#export-to-csv').click(function () {
        event.preventDefault();
		$('.buttons-csv').click();
	});
});

$(document).ready(function ($) {
	$('#modalFiltrosBtn').click(function () {
		$('#modalFiltrosDiv').toggleClass('d-none');
	});
});

$(document).ready(function ($) {
	$('.ofertas-vinculadas-mult-select').select2();
});

$("#link-url-checkout-to-copy").click(function () {
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

$(".link-url-return-to-copy").click(function () {
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
        tapToDismiss: !0
    })
});

$(".ofertas-vinculadas-mult-select").change(function () {
	var multipleValues = $(".ofertas-vinculadas-mult-select").val();
	$("#ofertas-vinculadas-mult-select-text").val(multipleValues);
});

$(".multselect_preload").change(function () {
	var multipleValues = $(".multselect_preload").val();
	$("#ofertas-vinculadas-mult-select-text").val(multipleValues);
});

$("#select-ship-paymethod").change(function () {
	var multipleValues = $("#select-ship-paymethod").val();
	$("#text-ship-paymethod").val(multipleValues);
});

$("#select-plano-assinante").change(function () {
	var multipleValues = $("#select-plano-assinante").val();

	var readFields = ["#tax-disabled-field", "#gateway-disabled-field", "#prazo-disabled-field", "#entrega-disabled-field"]
	var editFields = ["#tax-field", "#gateway-field", "#prazo-field", "#entrega-field"]

	var tax_input = $('#disabled-tax-input');
	var gateway_input = $('#disabled-gateway-input');
	var prazo_input = $('#disabled-prazo-input');
	var entrega_input = $('#disabled-entrega-input');
	switch(multipleValues) {
		case "1":
			hideEditableFields();
			showDisabledFields();

			tax_input.val("4,97%");
			gateway_input.val("2%");
			prazo_input.val("30 dias");
			entrega_input.val("R$28,90");

			values = ["0.0497", "0.02", "30", "28.9"]
			populateFormdata(values);

			break;
		case "2":
			hideEditableFields();
			showDisabledFields();

			tax_input.val("3,97%");
			gateway_input.val("1,5%");
			prazo_input.val("14 dias");
			entrega_input.val("R$27,90");

			values = ["0.0397", "0.015", "14", "27.9"]
			populateFormdata(values);

			break;
		case "3":
			hideEditableFields();
			showDisabledFields();

			tax_input.val("2,97%");
			gateway_input.val("1%");
			prazo_input.val("7 dias");
			entrega_input.val("R$26,90");

			values = ["0.0297", "0.01", "7", "26.9"]
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
		$("#text-taxa-gateway-assinante").val(values[1]);
		$("#text-prazo-assinante").val(values[2]);
		$("#text-entrega-assinante").val(values[3]);

		$("#select-taxa-assinante").val(values[0]);
		$("#select-taxa-gateway-assinante").val(values[1]);
		$("#select-prazo-assinante").val(values[2]);
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

$("#select-filter-status-id").change(function () {
	var multipleValues = $("#select-filter-status-id").val();
	$("#text-filter-status-id").val(multipleValues);
});

$("#select-uf-operacao").change(function () {
	var multipleValues = $("#select-uf-operacao").val();
	$("#text-uf-operacao").val(multipleValues);
	$("#text-uf-operacao").trigger("change")
});

$("#select-cidade-operacao").change(function () {
	var multipleValues = $("#select-cidade-operacao").val();
	$("#text-cidade-operacao").val(multipleValues);
});

$("#select-taxa-credito").change(function () {
	var multipleValues = $("#select-taxa-credito").val();
	$("#text-taxa-credito").val(multipleValues);
});

$("#select-operacao-local").change(function () {
	var multipleValues = $("#select-operacao-local").val();
	$("#text-operacao-local").val(multipleValues);
});

$("#select-bank-acc-type-id").change(function () {
	var multipleValues = $("#select-bank-acc-type-id").val();
	$("#text-bank-acc-type-id").val(multipleValues);
});

$("#select-filter-period-id").change(function () {
	var multipleValues = $("#select-filter-period-id").val();
	$("#text-filter-period-id").val(multipleValues);
});

$("#select-taxa-assinante").change(function () {
	var multipleValues = $("#select-taxa-assinante").val();
	$("#text-taxa-assinante").val(multipleValues);
});

$("#select-prazo-assinante").change(function () {
	var multipleValues = $("#select-prazo-assinante").val();
	$("#text-prazo-assinante").val(multipleValues);
});

$("#select-entrega-assinante").change(function () {
	var multipleValues = $("#select-entrega-assinante").val();
	$("#text-entrega-assinante").val(multipleValues);
});

$("#select-ship-product").change(function () {
	var multipleValues = $("#select-ship-product").val();
	alert("mudou o valor: "+ multipleValues);
	$("#text-ship-product").val(multipleValues);
});

$("#select-ship-name").change(function () {
    var multipleValues = $("#select-ship-name").val();
    $("#text-ship-name").val(multipleValues);
});

$("#select-ship-user").change(function () {
    var multipleValues = $("#select-ship-user").val();
    $("#text-ship-user").val(multipleValues);
});

$("#select-ship-locale").change(function () {
	var multipleValues = $("#select-ship-locale").val();
	$("#text-ship-locale").val(multipleValues);
});

$("#select-tipo-conta").change(function () {
	var multipleValues = $("#select-tipo-conta").val();
	$("#text-tipo-conta").val(multipleValues);
});

jQuery.fn.preventDoubleSubmit = function () {
	jQuery(this).submit(function () {
		if (this.beenSubmitted) {
			$('input[type=submit]', this).attr("disabled", "disabled");
			return false;
		}
		else {
			this.beenSubmitted = true;
		}
	});
};

/* AJAX */
$(document).ready(function ($) {

	jQuery('form').preventDoubleSubmit();

	$('div[data-toggle="toggle"]').click(function () {
		console.log("toggle_show_cancelled value: " + $("#toggle_show_cancelled").prop("checked"))
	})

	//Upload
	$('#ReleaseBillingForm').submit(function () {

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
			success: function (feedback) {
			    console.log(feedback);
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
	$("input[type=file]").on("change", function () {
		var files = !!this.files ? this.files : [];
		if (!files.length || !window.FileReader) return;

		if (/^image/.test(files[0].type)) {
			var reader = new FileReader();
			reader.readAsDataURL(files[0]);

			reader.onload = function () {
				$("#product-image").attr('src', this.result);
			}
		}
	});


	//SaleForm Submit
	$('#AddSaleForm').submit(function () {

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
			success: function (feedback) {
				Swal.fire({
					title: feedback.title,
					text: feedback.msg, //"Você receberá seu produto na data e perído selecionados.",
                    icon: feedback.type,
				}).then((value) => {
                    if (feedback.type == 'success'){
                        document.location.reload(true);
                    }
				});
			}
		}).fail(function (data) {
			console.log(data);
			Swal.fire({
				title: "Erro #PAS001",
				text: "Não foi possível criar sua oferta..",
				icon: 'warning',
			});
		});

		return false;
	});



	//SaleForm Submit
	$('#AddCouponForm').submit(function () {

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
            success: function (feedback) {
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

		return false;
	});

	$('#RescheduleOrderForm').submit(function () {

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
			success: function (feedback) {
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


	$('#FailOrderForm').submit(function () {

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
			success: function (feedback) {
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


	$('#CompleteOrderForm').submit(function () {

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
			success: function (feedback) {
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
	$('#UpdateCouponForm').submit(function () {

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
			success: function (feedback) {
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
	$('#AddDomainForm').submit(function () {

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
			success: function () {
				document.getElementById('AddDomainForm');
			}
		});

		return false;
	});


	//AddSubscriber Submit
	$('#AddSubscriberForm').submit(function () {

		var select_plano_assinante = $('#select-plano-assinante').val();
		var select_taxa_assinante = $('#select-taxa-assinante').val();
		var select_prazo_assinante = $('#select-prazo-assinante').val();
		//var valor_entrega_assinante = $('#select-entrega-assinante').val();
		var valor_entrega_assinante = $('#text-entrega-assinante').val();

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

		// Envia O FormData através da requisição AJAX
		$.ajax({
			url: u + "/ajax/add-subscriber-ajax.php",
			type: "POST",
			data: formData,
			dataType: 'json',
			processData: false,
			contentType: false,
			success: function (feedback) {
				if (feedback.status > 0) {
					var url = feedback.url;
					Swal.fire({
						title: "Usuário Cadastrado!",
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
	$('#SubscriberPayInfos').submit(function () {

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
			success: function (feedback) {
				if (feedback.status > 0) {
					Swal.fire({
						title: "Sucesso!",
						text: "Suas informações de pagamento foram atualizas!",
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
	$('.update-order-status').click(function () {

		event.preventDefault();
		var status = this.getAttribute('data-status');

		var id = this.getAttribute('data-id');

		// Envia os parâmetro para o PHP via AJAX
		$.ajax({
			url: "../update-order-status.php",
			type: "GET",
			data: { status, id },
			dataType: 'json',
			processData: true,
			contentType: false,
			success: function (feedback) {
				Swal.fire({
					title: "Sucesso!",
					text: feedback.msg,
					icon: 'success',
				}).then((value) => {
					document.location.reload(true);
				});
			}
		}).fail(function (data) {
			Swal.fire({
				title: "Erro de Conexão",
				text: "Quando a página atulizar, tente mudar o status do pedido novamente.",
				icon: 'warning',
			}).then((value) => {
				window.open("pedidos/", '_self');
			});
		});

		return false;
	});

	// $('.btn-billing-request').click(function () {
	// 	$('#valor-saque').focus();
	// });

	//Update shipment status from /pedidos in ADM account.
	$('.update-shipment-status-link').click(function () {

		event.preventDefault();

		var status = this.getAttribute('data-status');
		var id = this.getAttribute('data-id');
		var product = this.getAttribute('data-produto');
		var locale = this.getAttribute('data-local');
		var quant = this.getAttribute('data-quantidade');

        var url = u + "/ajax/update-shipment-status.php";


		// Envia os parâmetro para o PHP via AJAX
		$.ajax({
			url: url,
			type: "GET",
			data: { status, id, product, locale, quant },
			dataType: 'json',
			processData: true,
			contentType: false,
			success: function (feedback) {
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




	$(document).ready(function ($) {
		$('#sale-name-input').focusout(function () {

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
				success: function (feedback) {
					$('#url-friedly-input').val(feedback.url);
				}
			});
		});

		$('#edit-sale-name-input').on('change', function () {

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
				success: function (feedback) {
					$('#url-friedly-input').val(feedback.url);
				}
			});
		});

		$('#url-friedly-input').on('change', function () {

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
				success: function (feedback) {
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
	$('.update-sale-status').click(function () {

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
			success: function (feedback) {
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


$('#AddOperationForm').submit(function () {

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
	// Envia O FormData através da requisição AJAX
	$.ajax({
	  url: "../ajax/add-operation-ajax.php",
	  type: "POST",
	  data: formData,
	  dataType: 'json',
	  processData: false,
	  contentType: false,
	  success: function (feedback) {
		if (feedback.status > 0) {
			Swal.fire({
			title: "Sucesso!",
			text: "A operação foi cadastrada com sucesso.",
			icon: 'success',
		  });
		  AddOperationForm.reset();
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
  
$('#AddOperatorForm').submit(function () {
  
	var nome = $("#nomeOperador").val();
	var email = $("#emailOperador").val();
	var telefone = $("#telefoneOperador").val();
	var operacao = $("#text-operacao-local").val();
	var credito = $("#text-taxa-credito").val();
  
	if (nome == null || email == null || telefone == null || operacao == null || credito == null) {
		Swal.fire({
		title: "Erro!",
		text: "Todos os detalhes do plano precisam ser informados.",
		icon: 'warning',
	  })
		return false;
	  }
  
	var AddOperatorForm = document.getElementById('AddOperatorForm');
  
	var formData = new FormData(AddOperatorForm);
	
	var url = u + "../ajax/add-operator-ajax.php";
	formData.append("taxa-credito", credito);
  
	$.ajax({
	  url: url,
	  type: "POST",
	  data: formData,
	  dataType: 'json',
	  processData: false,
	  contentType: false,
	  success: function (feedback) {
		if (feedback.status > 0) {
			Swal.fire({
			title: "Sucesso!",
			text: "O operador foi cadastrado com sucesso.",
			icon: 'success',
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
	AddOperatorForm.reset();
	  
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
$('#documento-pedido').mask('000.000.000-00', {
    onKeyPress : function(cpfcnpj, e, field, options) {
      const masks = ['000.000.000-000', '00.000.000/0000-00'];
      const mask = (cpfcnpj.length > 14) ? masks[1] : masks[0];
      $('#documento-pedido').mask(mask, options);
    }
});
$('.money').mask("#.##0,00", { reverse: true });

function MascaraMoeda(objTextBox, SeparadorMilesimo, SeparadorDecimal, e){
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
    for(i = 0; i < len; i++)
        if ((objTextBox.value.charAt(i) != '0') && (objTextBox.value.charAt(i) != SeparadorDecimal)) break;
    aux = '';
    for(; i < len; i++)
        if (strCheck.indexOf(objTextBox.value.charAt(i))!=-1) aux += objTextBox.value.charAt(i);
    aux += key;
    len = aux.length;
    if (len == 0) objTextBox.value = '';
    if (len == 1) objTextBox.value = '0'+ SeparadorDecimal + '0' + aux;
    if (len == 2) objTextBox.value = '0'+ SeparadorDecimal + aux;
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
