var current_order_id;
var operation_id;
var current_parent;
var ordernum;
var current_resp_text;

$(".order-dropdown").click(e => {
    var button = $(e.target.closest("button"));
    current_parent = button.parent();

    var tr = button.closest("tr");
    current_resp_text = tr[0].querySelector(".resp-text");

    if (!current_parent.children(".dropdown-menu").length) {

        current_order_id = $(button).data('order');
        ordernum = $(button).data('ordernum');
        operation_id = $(button).data('operation');

        $("#modalConfirmResponsability").modal("show");
    }
});

$("#confirm-resp-button").click(e => {
    var url = u + "/ajax/add-order-responsible.php";

    var data = new FormData();
    data.append("action", "order-responsible");
    data.append("order_id", current_order_id);
    data.append("order_number", ordernum);

    $.ajax({
        url: url,
        type: "POST",
        data: data,
        dataType: "json",
        processData: false,
        contentType: false,
        success: function(feedback) {
            if (feedback.status == 0) {
                Swal.fire({
                    title: feedback.title,
                    text: feedback.msg,
                    icon: 'warning',
                });
            } else {
                Swal.fire({
                    title: feedback.title,
                    text: feedback.msg,
                    icon: 'success',
                });
                current_parent.empty();
                current_parent.append(feedback.content);
                //current_resp_text.innerHTML = "Você";
                dataTable.ajax.reload();
            }
        }
    });
    $("#modalConfirmResponsability").modal("hide");
});