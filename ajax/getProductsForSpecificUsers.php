<?php

    require_once dirname(__FILE__) . "/../includes/config.php";
    session_name(SESSION_NAME);
    session_start();
    
    if($_POST['query'] !== ""){
        $queryS= "SELECT product_name, product_code, product_id FROM products WHERE product_trash = 0 " . $_POST['query'];
    
        
        $get_products = $conn->prepare($queryS);
        $get_products->execute();
        
        $products_data = $get_products->fetchAll();
        echo json_encode($products_data);
        return;
    }
    
    $queryS = "SELECT product_name, product_code, product_id FROM products WHERE product_trash = 0";
    
    $get_products = $conn->prepare($queryS);
    $get_products->execute();
    $products_data = $get_products->fetchAll();
    
    echo json_encode($products_data);
    return;