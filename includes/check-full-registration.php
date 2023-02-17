<?php

@session_name(SESSION_NAME);
@session_start();

if (isset($_SESSION['UserID'])){

    # Usuário logado, verifica se o cadastro dele está completo
    $verify_user_infos = $conn->prepare('SELECT * FROM users WHERE user__id = :user__id');
    $verify_user_infos->execute(array('user__id' => $_SESSION['UserID']));
    
    $user_infos = $verify_user_infos->fetch();
    
    if($verify_user_infos->rowCount() > 0 && ($user_infos['user_phone'] == null || $user_infos['company_name'] == null || $user_infos['company_doc'] == null || $user_infos['company_type'] == null)){
        
        if (!(isset($full_registration_page)) && !(isset($user_terms_page))){
            header('Location: ' . SERVER_URI . '/usuario/completar-cadastro/');
            exit;
        }

    } else if ($verify_user_infos->rowCount() > 0 && ($user_infos['user_terms_accepted'] != 1)) {
        
        if (!(isset($full_registration_page)) && !(isset($user_terms_page))){
            header('Location: ' . SERVER_URI . '/usuario/contrato/');
            exit;
        }

    } else {
        header('Location: ' . SERVER_URI . '/');
    }
    
}

?>