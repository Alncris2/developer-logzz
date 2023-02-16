<?php
require_once ('includes/config.php');

if (isset($_GET['order'])){
  $order = addslashes($_GET['order']);

  $order_data = $conn->prepare('SELECT * FROM orders INNER JOIN sales ON orders.sale_id = sales.sale_id WHERE order_number = :order ORDER BY orders.order_id ASC');
  $order_data->execute(array('order' => $order));


  if ($order_data->rowCount() > 1){
    
    $price_with_upsell = 0;

    while($row = $order_data->fetch()) {
        $sale_name = $row['sale_name'];
        $product_id = $row['product_id'];
        $sale_id = $row['sale_id'];
        $sale_quantity = $row['sale_quantity'];
        $sale_price = $row['sale_price'];
        $sale_tax = $row['sale_tax'];
        $user__id = $row['user__id'];
        $number = $row['client_number'];
        $name = $row['client_name'];
        $address = $row['client_address'];
        $delivery_period = $row['delivery_period'];
        $product_delivery = $row['order_deadline'];
        $order_delivery_date = $row['order_delivery_date'];
        $order_status = $row['order_status'];
        $fb_pixel = $row['sale_fb_pixel'];
        $fb_purchase_value = number_format($row['order_final_price'], 2, '.', '');
        $price_with_upsell = $price_with_upsell + $fb_purchase_value;
        $order_id = $row['order_id'];
        $initiate_checkout = $row['initiate_checkout'];
      }

}else {
  header ("Location: ../pagina-nao-encontrada");
	exit;
  }
}


  $order_product_data = $conn->prepare('SELECT * FROM products WHERE product_id = :product_id');
  $order_product_data->execute(array('product_id' => $product_id));

  while($sow = $order_product_data->fetch()) {
    $get_operator = $conn->prepare('SELECT * FROM local_operations_orders loo INNER JOIN logistic_operator lop ON loo.responsible_id = lop.operator_id WHERE order_id = :order_id');
    $get_operator->execute(array('order_id' => $order_id));

	  $product_image = $sow['product_image'];
	  $product_name = $sow['product_name'];
	  $product_description = $sow['product_description'];
    $user__id = $sow['user__id'];

	  if (strlen($product_description) > 100){
			$product_description = substr($product_description, 0, 100) . "...";
	  }

	  if ($delivery_period = "manha"){
		  $period = "Manhã";
	  } else {
		  $period = "Tarde";
	  }

	  $delivery_date = date_format(date_create($product_delivery), 'd/m/Y') . " | " . $period;

    if(!(empty($fb_pixel)) && $initiate_checkout == 0){
      $fb_pixel_purchase = "<script>
      !function(f,b,e,v,n,t,s)
      {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
      n.callMethod.apply(n,arguments):n.queue.push(arguments)};
      if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
      n.queue=[];t=b.createElement(e);t.async=!0;
      t.src=v;s=b.getElementsByTagName(e)[0];
      s.parentNode.insertBefore(t,s)}(window, document,'script',
      'https://connect.facebook.net/en_US/fbevents.js');
      fbq('init', '" . $fb_pixel . "');
      fbq('track', 'Purchase', {value:  $fb_purchase_value, currency: 'BRL'});
      </script>
      <noscript>
      <img height='1' width='1' style='display:none' 
         src='https://www.facebook.com/tr?id=" . $fb_pixel . "&ev=PageView&noscript=1'/>
      </noscript>";
    }
  
    $page_title =  "Pedido #" . $order;
    require_once('includes/layout/fullwidth/fullwidth-header.php');
	  
?>

<div class="container-fluid" style="margin-top: 30px;">
	<div class="row">
    <div class="col-lg-9 d-block m-auto">
      <div class="card">
        <div class="card-header"><h2 style="font-weight: 300; border-color: #f0f1f;">Pedido #<?php echo $order; ?></h2></div>
        <div class="card-body">
          <div class="row mb-5">
            <div class="col-sm-12">
              <?php
              switch ($order_status) {
                  case 1:
                      $alert_class = "success";
                      $alert_icon = "fas fa-check-circle";
                      $alert_title = "Entrega Reagendada";
                      if($operator = $get_operator->fetch()) {
                        $get_operator_name = $conn->prepare("SELECT full_name FROM users WHERE user__id = :user_id");
                        $get_operator_name->execute(array("user_id" => $operator['user_id']));
                        $alert_msg = "O produto agora será entregue no dia <b>" . date_format(date_create($product_delivery), 'd/m') . "</b> pelo operador <b>" . $get_operator_name->fetch()["full_name"] . "</b> no período da <b>" . $period . "</b>";
                      } else {
                        $alert_msg = "O produto agora será entregue no dia <b>" . date_format(date_create($product_delivery), 'd/m') . "</b> no período da <b>" . $period . "</b>";
                      }
                      break;

                  case 2:
                      $alert_class = "warning";
                      $alert_icon = "fas fa-clock";
                      $alert_title = "Entrega Atrasada";
                      if($operator = $get_operator->fetch()) {
                        $get_operator_name = $conn->prepare("SELECT full_name FROM users WHERE user__id = :user_id");
                        $get_operator_name->execute(array("user_id" => $operator['user_id']));
                        $alert_msg = "A entrega era prevista para ser entregue pelo operador " . $get_operator_name->fetch()["full_name"] . " a <b>" . strtolower($period) . "</b> do dia <b>" . date_format(date_create($product_delivery), 'd/m') . "</b>.";
                      } else {
                        $alert_msg = "O produto agora será entregue no dia <b>" . date_format(date_create($product_delivery), 'd/m') . "</b> no período da <b>" . $period . "</b>";
                      }
                      break;

                  case 3:
                      $alert_class = "success solid";
                      $alert_icon = "fas fa-check-circle";
                      $alert_title = "Pedido Completo";
                      if($operator = $get_operator->fetch()) {
                        $get_operator_name = $conn->prepare("SELECT full_name FROM users WHERE user__id = :user_id");
                        $get_operator_name->execute(array("user_id" => $operator['user_id']));
                        $alert_msg = "O produto foi entregue no dia <b>" . date_format(date_create($order_delivery_date), 'd/m') . "</b> pelo operador <b>" . $get_operator_name->fetch()["full_name"] . "</b> às <b>" . date_format(date_create($order_delivery_date), 'H:i') . "</b>.";
                      } else {
                        $alert_msg = "O produto foi entregue no dia <b>" . date_format(date_create($product_delivery), 'd/m') . "</b> às <b>" . date_format(date_create($product_delivery), 'H:i') . "</b>.";
                      }

                      break;

                  case 4:
                      $alert_class = "warning";
                      $alert_icon = "fas fa-exclamation-circle";
                      $alert_title = "Entrega Frustrada";
                      if($operator = $get_operator->fetch()) {
                        $get_operator_name = $conn->prepare("SELECT full_name FROM users WHERE user__id = :user_id");
                        $get_operator_name->execute(array("user_id" => $operator['user_id']));
                        $alert_msg = "O entregador " . $get_operator_name->fetch()["full_name"] . " tentou realizar a entregar do pedido, mas não houve êxito.";
                      } else {
                        $alert_msg = "O entregador tentou realizar a entregar do pedido, mas não houve êxito.";
                      }
                      $alert_msg .= '<br><small><a href="' . SERVER_URI . '/uploads/pedidos/frustrados/' . $fail_delivery_attemp . '" target="_blank">Ver Comprovante</a></small>';
                      break;

                  case 5:
                      $alert_class = "danger solid";
                      $alert_icon = "fas fa-ban";
                      $alert_title = "Pedido Cancelado";
                      if($operator = $get_operator->fetch()) {
                        $get_operator_name = $conn->prepare("SELECT full_name FROM users WHERE user__id = :user_id");
                        $get_operator_name->execute(array("user_id" => $operator['user_id']));
                        $alert_msg = "O pedido foi cancelado sem que fosse preciso o entregador " . $get_operator_name->fetch()["full_name"] . " ir ao local.";
                      } else {
                        $alert_msg = "O pedido foi cancelado sem ser preciso que o entregador fosse ao local.";
                      }
                      break;

                  default:
                      $alert_class = "success";
                      $alert_icon = "fas fa-clock";
                      $alert_title = "Entrega Agendada";
                      if($operator = $get_operator->fetch()) {
                        $get_operator_name = $conn->prepare("SELECT full_name FROM users WHERE user__id = :user_id");
                        $get_operator_name->execute(array("user_id" => $operator['user_id']));
                        $alert_msg = "Aguarde o entregador " . $get_operator_name->fetch()["full_name"] . " no dia <b>" . date_format(date_create($product_delivery), 'd/m') . "</b> no período da <b>" . $period . "</b>.";
                      } else {
                        $alert_msg = "Aguarde o entregador no dia <b>" . date_format(date_create($product_delivery), 'd/m') . "</b> no período da <b>" . $period . "</b>.";
                      }
                      break;
              }
              ?>
              <div class="alert <?php echo "alert-" . $alert_class; ?> fade show">
                  <i class="<?php echo $alert_icon; ?>"></i>
                  <strong><?php echo $alert_title . "!"; ?></strong><br><?php echo $alert_msg; ?>
              </div>
            </div>
            <div class="mt-4 col-xl-8 col-lg-5 col-md-6 col-sm-12">
              <h6>Cliente:</h6>
              <div> <strong><?php echo $name; ?></strong> </div>
              <div><?php echo $address; ?></div>
              <div><?php echo '<i class="fab fa-whatsapp"></i> ' . $number; ?></div>
			  <h6 class="mt-4">Entrega:</h6>
			  <div><?php echo $delivery_date; ?></div>
            </div>
			<div class="mt-4 col-xl-4 col-lg-5 col-md-6 col-sm-12">
               <img class="img-fluid" src="<?php echo SERVER_URI . "/uploads/imagens/produtos/" . $product_image; ?>">
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-striped" style="font-size: 0.9em;">
              <thead>
                <tr>
                  <th class="col-md-1 center">#</th>
                  <th class="col-md-3 center">Produto</th>
                  <th class="col-md-6 center">Descrição</th>
                  <th class="col-md-2 center">Qtd.</th>
                  <th class="col-md-6 center">Preço (R$)</th>
                </tr>
              </thead>
			  <tbody>
        <?php
              $n = 1;
              $sales_prices = 0;

              $order_data = $conn->prepare('SELECT * FROM orders INNER JOIN sales ON orders.sale_id = sales.sale_id WHERE order_number = :order');
              $order_data->execute(array('order' => $order));
              while($row = $order_data->fetch()) {
                $sale_quantity = $row['sale_quantity'];
                $product_id = $row['product_id'];
                $sale_price = $row['sale_price'];
                $sales_prices = $sales_prices + $row['sale_price'];

                $order_product_data = $conn->prepare('SELECT * FROM products WHERE product_id = :product_id');
                $order_product_data->execute(array('product_id' => $product_id));


                while($sow = $order_product_data->fetch()) {
                  $product_name = $sow['product_name'];
                  $product_description = $sow['product_description'];
                  $price = $row['order_final_price'];
              
                  if (strlen($product_description) > 100){
                    $product_description = substr($product_description, 0, 100) . "...";
                  }


        ?>
                <tr>
                  <td class="center"><?php echo $n; ?></td>
                  <td class="center"><?php echo $product_name; ?></td>
                  <td class="center"><?php echo $product_description; ?></td>
                  <td class="center"><?php echo $sale_quantity; ?></td>
                  <td class="center"><?php echo "<small><s>" .  number_format($sale_price, 2,',', '.') . "</s></small>  " . number_format($price, 2,',', '.'); ?></td>
                </tr>

          <?php
              $n = $n + 1;
                }
              }
          ?>
			  </tbody>
			  <tfoot>
				<tr>
                  <td class="center text-right" colspan="4"><strong><br>Total</strong></td>
                  <td class="center" colspan="2"><strong><?php echo "<small><s> R$ " . number_format($sales_prices, 2,',', '.') . "</s></small><br>R$ " . number_format($price_with_upsell, 2,',', '.'); ?></strong></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
      $stmt = $conn->prepare('UPDATE orders SET initiate_checkout = 1 WHERE order_id = :order_id');
      $stmt->execute(array('order_id' => $order_id));
			}
    require_once('includes/layout/fullwidth/fullwidth-footer.php');
?>
	
