<?php

    require "includes/config.php";

    $get_orders = $conn->prepare('SELECT product_name, product_id, COUNT(product_id) FROM orders WHERE order_status = 3 AND order_date BETWEEN "2022-04-13" AND "2022-04-13" GROUP BY product_id ORDER BY COUNT(product_id) DESC');
    $get_orders->execute();

    $products = $get_orders->rowCount();

    $n = round($products / 10);
    
    while ($n > ($products + 1)){

        switch ($n) {
            case 1:
                $rating = 5;
                break;
            case 2:
                $rating = 4.5;
                break;
            case 3:
                $rating = 4;
                break;
            case 4:
                $rating = 3.5;
                break;
            case 5:
                $rating = 3;
                break;
            case 6:
                $rating = 2.5;
                break;
            case 7:
                $rating = 2;
                break;
            case 8:
                $rating = 1.5;
                break;
            case 9:
                $rating = 1;
                break;
            case 10:
                $rating = 0.5;
                break;
            default:
                $rating = 0;
                break;
        }
    
        $n = $n + 1;
    }

    exit;

?>