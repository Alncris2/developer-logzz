<?php 

    /**
     * ---------------------------------------------------------------------------
     * SCRIPT PARA DEFINIR CHECKOUT PADRÃO PARA OFERTAS ANTERIORES A IMPLEMENTAÇÃO
     * ---------------------------------------------------------------------------
    */

    require "includes/config.php";
    session_name(SESSION_NAME);
    session_start();

    $getAllUsers = $conn->prepare("SELECT * FROM users AS o");
    $getAllUsers->execute();
    $allUsers = $getAllUsers->fetchAll(\PDO::FETCH_ASSOC);

    // foreach ($allUsers as $value) {
    //     $insertDefaultCheckout = $conn->prepare("INSERT INTO custom_checkout(user__id, name_checkout, counter_active, isActive, support_active, support_whatsapp, support_email) VALUES (:user__id, :name_checkout, :counter_active, :isActive, :support_active, :support_whatsapp, :support_email)");
    //     $insertDefaultCheckout->execute([
    //         'user__id' => $value['user__id'],
    //         'name_checkout' => 'CHECKOUT_PADRÃO',
    //         'counter_active' => 0,
    //         'isActive' => 1,
    //         'support_active' => 0,
    //         'support_whatsapp' => "",
    //         'support_email' => "",
    //     ]);

    //     // echo "usuário de id" . $$value['user__id'] . "agora tem um checkout padrão.";
    // }


    // ATUALIZAR OFERTAS ANTERIORES A UM CHECKOUT PADRÃO
    $stmt = $conn->prepare("UPDATE sales AS s SET s.type_checkout = 'CHECKOUT_PADRÃO'");
    $stmt->execute();
    