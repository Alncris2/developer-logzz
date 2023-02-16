<?php

$order_number = addslashes($_GET['order-id']);
$sale_id = addslashes($_GET['sale-id']);

require_once ('includes/config.php');

$stmt = $conn->prepare('SELECT * FROM orders WHERE order_number = :order_number ORDER BY order_id DESC LIMIT 1');
	$stmt->execute(array('order_number' => $order_number));
		while($row = $stmt->fetch()) {
			$fb_purchase_value = $row['order_final_price'];
			$initiate_checkout = $row['initiate_checkout'];
			$order_id = $row['order_id'];
		}

$stmt = $conn->prepare('SELECT * FROM sales WHERE sale_id = :sale_id');
	$stmt->execute(array('sale_id' => $sale_id));
		while($cow = $stmt->fetch()) {
			$sale_fb_pixel = $cow['sale_fb_pixel'];
			$meta_pixel_facebook_api = $cow['meta_pixel_facebook_api'];
			$url_upsell = $cow['url_upsell'];
		}
		
		$page_title =  "Redirecionando...";
		require_once('includes/layout/fullwidth/fullwidth-header.php');

?>
<script>
	setTimeout(function(){
		window.location.assign("<?php echo $url_upsell; ?>");
		});
</script>
<?php
	#$stmt = $conn->prepare('UPDATE orders SET initiate_checkout = 1 WHERE order_id = :order_id');
	#$stmt->execute(array('order_id' => $order_id));
?>