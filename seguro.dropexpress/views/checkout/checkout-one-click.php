<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (isset($_GET['url_one_clique'])){
  $url = addslashes($_GET['url_one_clique']);

		if (!(isset($_SESSION['oclck_order_number'])) || !(isset($_SESSION['oclck_client_name'])) || !(isset($_SESSION['oclck_client_name']))){
			$redirect = CHECKOUT_URI . "/pay/" . $url . "?autofill=0";
			header ("Location: " . $redirect);
		}

  $sale_data = $conn->prepare('SELECT * FROM sales WHERE sale_url = :sale_url');
  $sale_data->execute(array('sale_url' => $url));
  
  if ($sale_data->rowCount() != 0){
    while($row = $sale_data->fetch()) {
      $sale_name = $row['sale_name'];
      $sale_quantity = $row['sale_quantity'];
      $sale_price = $row['sale_price'];
      $sale_status = $row['sale_status'];
      $sale_id = $row['sale_id'];
      $product_id = $row['product_id'];
      
  } 
}else {
	header ("Location: /pagina-nao-encontrada");
	exit;
  }
}


$product_data = $conn->prepare('SELECT * FROM products WHERE product_id = :product_id');
$product_data->execute(array('product_id' =>  $product_id));

while($row = $product_data->fetch()) {
  $product_name = $row['product_name'];
  $product_price = $row['product_price'];
  $product_description = $row['product_description'];
  $product_image = $row['product_image'];
  $product_id = $row['product_id'];
  $user__id = $row['user__id'];
  }


  $address                  = @$_SESSION['oclck_client_address'];
  $name 					= @$_SESSION['oclck_client_name'];
  $whats 					= @$_SESSION['oclck_client_number'];
  $delivery_period 			= @$_SESSION['oclck_delivery_period'];
  $order_deadline 	 		= @$_SESSION['oclck_order_deadline'];
  $order_delivery_date 	    = date('Y-m-d H-i-s');
  $order_delivery_time 	    = date('Y-m-d H-i-s');
  $order_number 		  	= @$_SESSION['oclck_order_number'];
  $order_deadline 	 		= @$_SESSION['oclck_order_deadline'];

  $order_status = $order_id = 0;
  
  if (isset($_SESSION['oclck_coupon'])){
    $order_final_price = $sale_price * $_SESSION['oclck_coupon'];
    $use_coupon = $_SESSION['oclck_coupon'];
  } else {
    $order_final_price = $sale_price;
    $use_coupon = 0;
  }


  $stmt = $conn->prepare('INSERT INTO orders(order_id, user__id, sale_id, product_name, order_date, order_deadline, order_status, order_delivery_date, client_name, client_address, client_number, order_delivery_time, order_number, delivery_period, use_coupon, order_final_price) VALUES (:order_id, :user__id, :sale_id, :product_name, :order_date, :order_deadline, :order_status, :order_delivery_date, :client_name, :client_address, :client_number, :order_delivery_time, :order_number, :delivery_period, :use_coupon, :order_final_price)');
	
	try {
		$stmt->execute(array('order_id' => $order_id, 'user__id' => $user__id, 'sale_id' => $sale_id, 'product_name' => $product_name, 'order_date' => $order_delivery_date, 'order_deadline' => $order_deadline, 'order_status' => $order_status, 'order_delivery_date' => $order_delivery_date, 'client_name' => $name , 'client_address' => $address, 'client_number' => $whats, 'order_delivery_time' => $order_delivery_date, 'order_number' => $order_number, 'delivery_period' => $delivery_period, 'use_coupon' => $use_coupon, 'order_final_price' => $order_final_price));

		$url = SERVER_URI . '/meu-pedido/' . $order_number;

		$upsell_feedback = 1;
		
      } catch(PDOException $e) {
        $error = 'ERROR: ' . $e->getMessage();
		echo $error;
      }
	  		@session_start();
	  		unset($_SESSION['oclck_order_deadline']);
            unset($_SESSION['oclck_client_name']);
            unset($_SESSION['oclck_client_address']);
            unset($_SESSION['oclck_client_number']);
            unset($_SESSION['oclck_order_number']);
            unset($_SESSION['oclck_delivery_period']);
            unset($_SESSION['oclck_coupon']);

$simple_checkout = true;
$page_title =  $product_name . " | Checkout DropExpress";
require_once(dirname(__FILE__) . '/../../includes/layout/fullwidth/fullwidth-header.php');
?>

<div class="container-fluid">
	<div class="row">
		<div class="col-xl-12" style="max-width: 1000px;margin: 0 auto;">
		<script>
			var url = '<?php echo $url; ?>';
		</script>
		</div>
	</div>
</div>

<?php
    require_once(dirname(__FILE__) . '/../../includes/layout/fullwidth/fullwidth-footer.php');
?>
	