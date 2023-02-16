<?php
    require_once (dirname(__FILE__) . '/includes/config.php');
    session_name(SESSION_NAME);
    session_start();
    
    if(isset($_GET['id'])){

        if(!isset($_SESSION['UserSuperAdmin']) || (!isset($_SESSION['UserSuperAdmin']) && !$_SESSION['UserSuperAdmin'])){
            session_destroy();

            $PAGE_REFERER = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : SERVER_URI . "/login/";
            header("location: ". $PAGE_REFERER);  
            exit;
        }
            
        $get_user_infos = $conn->prepare('SELECT * FROM subscriptions INNER JOIN users ON users.user__id = subscriptions.user__id WHERE users.user__id = :user__id');
        $get_user_infos->execute(array('user__id' => $_GET['id'])); 

        while($row = $get_user_infos->fetch()) {
            # Destroi a sessão atual para criar outra    
            session_destroy();
    
            session_name(SESSION_NAME);
            session_start();
    
            # Cria as variáveis de sessão do usuário
            $_SESSION['UserID'] = $row['user__id'];
            $_SESSION['UserFullName'] = $row['full_name'];
            $_SESSION['UserEmail'] = $row['email'];
            $_SESSION['UserPlan'] = $row['user_plan'];
            $_SESSION['UserPlanTax'] = $row['user_plan_tax'];
            $_SESSION['UserPlanExternalTax'] = $row['user_external_gateway_tax'];
            $_SESSION['UserPlanString'] =  userPlanString($row['user_plan']);
            $_SESSION['UserPlanShipTax'] = $row['user_plan_shipping_tax'];
            $_SESSION['UserPaymentTerm'] = $row['user_payment_term']; 
            $_SESSION['UserSuperAdmin'] = 1;    
        } 
    }  

    $PAGE_REFERER = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : SERVER_URI . "/pedidos/dashboard";
    header("location: ". $PAGE_REFERER);  
    exit;
?>