<?php

require_once (dirname(__FILE__) . '/includes/config.php');
require (dirname(__FILE__) . '/includes/classes/RandomStrGenerator.php');

$get_nocode_products = $conn->prepare('SELECT product_id, product_code FROM products WHERE product_code = "" OR product_code IS NULL');
$get_nocode_products->execute();

while($create_code = $get_nocode_products->fetch()){
    
    $product_id = $create_code['product_id'];

    # Geração do PRODUCT_CODE único
    $product_code = new RandomStrGenerator();
    $product_code = $product_code->onlyLetters(6);

    $verify_unique_product_code = $conn->prepare('SELECT * FROM products WHERE product_code = :product_code');
    $verify_unique_product_code->execute(array('product_code' => $product_code));

    if (!($verify_unique_product_code->rowCount() == 0)) {
        do {
            $product_code = new RandomStrGenerator();
            $product_code = $product_code->onlyLetters(6);

            $verify_unique_product_code = $conn->prepare('SELECT * FROM products WHERE product_code = :product_code');
            $verify_unique_product_code->execute(array('product_code' => $product_code));
        } while ($stmt->rowCount() != 0);
    }

    $save_the_code = $conn->prepare('UPDATE products SET product_code = :product_code WHERE product_id = :product_id');
    $save_the_code->execute(array('product_code' => $product_code, 'product_id' => $product_id));

    echo "O Código do Produto <b>" . $product_id . "</b> foi gerado e agora é <b>" . $product_code . "</b>!<br>";
}



?>