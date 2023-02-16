$(document).ready(function ($) {
    jQuery(".filter-btn").on("click", function () {
        jQuery(".chatbox").addClass("active");
    });
    jQuery(".chatbox-close").on("click", function () {
        jQuery(".chatbox").removeClass("active");
    });

    $('#billing-dataTables').DataTable({
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
});
