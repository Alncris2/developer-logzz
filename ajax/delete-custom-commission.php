<?php

require (dirname(__FILE__)) . "/../includes/config.php";

    $hotcode = addslashes($_GET['hotcode']);
    $sale_id = addslashes($_GET['sale_id']);

    $meta_key = "custom_commission_" . $hotcode;

    $check_previous_customization = $conn->prepare('SELECT * FROM sales_meta WHERE sale_id = :sale_id AND meta_key = :meta_key');
    $check_previous_customization->execute(array('sale_id' => $sale_id, 'meta_key' => $meta_key));
    

    if ($check_previous_customization->rowCount() != 0) {

        $commission_customization = $conn->prepare('DELETE FROM sales_meta WHERE sale_id = :sale_id AND meta_key = :meta_key');
        $commission_customization->execute(array('sale_id' => $sale_id, 'meta_key' => $meta_key));

        $feedback = array('type' => 'success');
        echo json_encode($feedback);
        exit;
        
    }

?>