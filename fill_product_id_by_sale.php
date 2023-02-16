<?php

require_once (dirname(__FILE__) . '/includes/config.php');

$get_products_id = $conn->prepare('SELECT product_id, order_id, sale_id FROM orders ORDER BY order_id');
$get_products_id->execute();

# echo $get_products_id->rowCount() . "<br>";

while($fill_product_id = $get_products_id->fetch()){
    
    $sale_id = $fill_product_id['sale_id'];

    $get_sales_id = $conn->prepare('SELECT product_id FROM sales WHERE sale_id = :sale_id');
    $get_sales_id->execute(array('sale_id' => $sale_id));

    
    while ($product_id = $get_sales_id->fetch()) {

        $product_id = $product_id['product_id'];

        $fill_the_product_id = $conn->prepare('UPDATE orders SET product_id = :product_id WHERE sale_id = :sale_id');
        $fill_the_product_id->execute(array('sale_id' => $sale_id, 'product_id' => $product_id));

        echo "O Código do Produto da ORDER <b>" . $fill_product_id['order_id'] . "</b> cuja SALE é <b>" . $sale_id . "</b> foi alterado para " . $product_id .".<br>";

    }

}



?>