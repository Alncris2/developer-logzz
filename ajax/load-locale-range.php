<?php
require dirname(__FILE__) . "/../includes/config.php";
session_name(SESSION_NAME);
session_start();

# Recebe o ID via GET
$locale_id = intval($_GET['id']);

try {

    # Obtem o nome da Localidade
    $get_locale_list = $conn->prepare('SELECT * FROM local_operations as L INNER JOIN inventories AS I ON inventory_locale_id = operation_id WHERE (inventory_product_id = :product_id AND inventory_quantity > 0) AND ship_locale = 0');
    $get_locale_list->execute(array('product_id' => $product_id));


    $get_locale_name = $conn->prepare('SELECT locale_name FROM locales WHERE locale_id = :locale_id');
    $get_locale_name->execute(array('locale_id' => $locale_id));
    $locale_title = $get_locale_name->fetch();
    $locale_title = $locale_title['locale_name'];

    # Obtém a Lista de Alcance
    $get_range_list = $conn->prepare('SELECT city FROM operations_locales l INNER JOIN local_operations o ON l.operation_id=o.operation_id WHERE l.operation_id = :operation_id');
    $get_range_list->execute(array('operation_id' => $locale_id));
    $range = $get_range_list->fetchAll(\PDO::FETCH_ASSOC);

    foreach($range as $range_item){
        $range_list .= '<label class="text-center text-muted">' . $range_item['city'] . '</label><br>';
    }

    $feedback = array(
        'locale_title' => $locale_title,
        'range_list' => $range_list
    );
    
    echo json_encode($feedback);
    exit;

} catch (PDOException $e) {

    $error = 'ERROR: ' . $e->getMessage();
    $feedback = array(
        'locale_title' => $error,
        'range_list' => 'ERRO'
    );
    
    echo json_encode($feedback);
    exit;
}

?>