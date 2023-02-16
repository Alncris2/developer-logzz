<?php

require (dirname(__FILE__)) . "/../includes/config.php";

    $hotcode = addslashes($_GET['hotcode']);
    $sale_id = addslashes($_GET['sale_id']);
    $value = addslashes($_GET['value']);

    $meta_key = "custom_commission_" . $hotcode;

    $check_previous_customization = $conn->prepare('SELECT * FROM sales_meta WHERE sale_id = :sale_id AND meta_key = :meta_key');
    $check_previous_customization->execute(array('sale_id' => $sale_id, 'meta_key' => $meta_key));
    

    if ($check_previous_customization->rowCount() != 0) {

        $commission_customization = $conn->prepare('UPDATE sales_meta SET meta_value = :meta_value WHERE sale_id = :sale_id AND meta_key = :meta_key');
        $commission_customization->execute(array('sale_id' => $sale_id, 'meta_key' => $meta_key, 'meta_value' => $value));

    } else {

        $commission_customization = $conn->prepare('INSERT INTO sales_meta (meta_id, sale_id, meta_key, meta_value) VALUES (:meta_id, :sale_id, :meta_key, :meta_value)');
        $commission_customization->execute(array('meta_id' => '0', 'sale_id' => $sale_id, 'meta_key' => $meta_key, 'meta_value' => $value));

    }


	$feedback = array('type' => 'success');
	echo json_encode($feedback);
	exit;

?>
