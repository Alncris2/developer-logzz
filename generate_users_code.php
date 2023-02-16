<?php

require_once (dirname(__FILE__) . '/includes/config.php');
require (dirname(__FILE__) . '/includes/classes/RandomStrGenerator.php');

$get_nocode_users = $conn->prepare('SELECT user__id, user_code FROM users WHERE user_code = "" OR user_code IS NULL');
$get_nocode_users->execute();

while($create_code = $get_nocode_users->fetch()){
    
    $user_id = $create_code['user__id'];

    # Geração do user_CODE único
    $user_code = new RandomStrGenerator();
    $user_code = $user_code->onlyLetters(6);

    $verify_unique_user_code = $conn->prepare('SELECT * FROM users WHERE user_code = :user_code');
    $verify_unique_user_code->execute(array('user_code' => $user_code));

    if (!($verify_unique_user_code->rowCount() == 0)) {
        do {
            $user_code = new RandomStrGenerator();
            $user_code = $user_code->onlyLetters(6);

            $verify_unique_user_code = $conn->prepare('SELECT * FROM users WHERE user_code = :user_code');
            $verify_unique_user_code->execute(array('user_code' => $user_code));
        } while ($stmt->rowCount() != 0);
    }

    $save_the_code = $conn->prepare('UPDATE users SET user_code = :user_code WHERE user__id = :user__id');
    $save_the_code->execute(array('user_code' => $user_code, 'user__id' => $user_id));

    echo "O Código do Usuário <b>" . $user_id . "</b> foi gerado e agora é <b>" . $user_code . "</b>!<br>";
}



?>