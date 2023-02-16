<?php

require_once(dirname(__FILE__) . '/../includes/config.php');
require(dirname(__FILE__) . '/../includes/classes/RandomStrGenerator.php');
session_name(SESSION_NAME);
session_start();

if ($_POST['action'] == 'order-responsible'){
  $order_id = addslashes($_POST['order_id']);
  $order_number = addslashes($_POST['order_number']);
  $user_id = $_SESSION['UserID'];

  try {
    $get_operator = $conn->prepare("SELECT * FROM logistic_operator WHERE user_id = :user_id");
    $get_operator->execute(array("user_id" => $user_id));

    $update_responsible = $conn->prepare("UPDATE local_operations_orders SET responsible_id = :operator_id WHERE order_id = :order_id");
    $update_responsible->execute(array("operator_id" => $get_operator->fetch()["operator_id"], "order_id" => $order_id));

    $change_status_html = '
      <button class="btn btn-success tp-btn-light sharp order-dropdown" data-toggle="dropdown" type="button" id="order-dropdown-0" data-boundary="viewport" aria-haspopup="true" aria-expanded="true">
        <span>
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="18px" height="18px" viewBox="0 0 24 24" version="1.1">
                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                    <rect x="0" y="0" width="24" height="24"></rect>
                    <circle fill="#000000" cx="5" cy="12" r="2"></circle>
                    <circle fill="#000000" cx="12" cy="12" r="2"></circle>
                    <circle fill="#000000" cx="19" cy="12" r="2"></circle>
                </g>
            </svg>
        </span>
      </button>
      <div class="dropdown-menu dropdown-menu-right border py-0" aria-labelledby="order-dropdown-0" x-placement="top-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(825px, 168px, 0px);">
          <div class="py-2">
              <a class="dropdown-item update-order-status" data-status="0" data-id="' . $order_id . '" href="#">Agendado</a>
              <a class="dropdown-item update-order-status" data-status="2" data-id="' . $order_id . '" href="#">Atrasado</a>
              <a class="dropdown-item update-order-status" data-status="5" data-id="' . $order_id . '" href="#">Cancelado</a>
              <a class="dropdown-item" href="'. SERVER_URI .'/pedido-op/frustrar/' . $order_number . '">Frustrado</a>
              <a class="dropdown-item" href="'. SERVER_URI .'/pedido-op/completar/' . $order_id . '">Completo</a>
              <a class="dropdown-item" href="'. SERVER_URI .'/pedido-op/reagendar/' . $order_id . '">Reagendado</a>
          </div>
      </div>
      <script>
		//Update order status from /pedidos in ADM account.
		$(".update-order-status").click(function () {

			event.preventDefault();
			var status = this.getAttribute("data-status");

			var id = this.getAttribute("data-id");

			// Envia os parâmetro para o PHP via AJAX
			$.ajax({
				url: u + "/update-order-status.php",
				type: "GET",
				data: { status, id },
				dataType: "json",
				processData: true,
				contentType: false,
				success: function (feedback) {
					Swal.fire({
						title: "Sucesso!",
						text: feedback.msg,
						icon: "success",
					}).then((value) => {
						document.location.reload(true);
					});
				}
			}).fail(function (data) {
				Swal.fire({
					title: "Erro de Conexão",
					text: "Quando a página atulizar, tente mudar o status do pedido novamente.",
					icon: "warning",
				}).then((value) => {
					window.open("pedidos/", "_self");
				});
			});

			return false;
		});
	  </script>
      ';

    $feedback = array('status' => 1, 'title' => 'Sucesso!', 'msg' => 'Você agora é responsável pelo pedido!', 'content' => $change_status_html);
    echo json_encode($feedback);
    exit;
  } catch(PDOException $e) {
    $error = 'ERROR: ' . $e->getMessage();
    $feedback = array('status' => 0, 'title' => 'Erro!','msg' => $error);
    echo json_encode($feedback);
    exit;
	}
}
?>
