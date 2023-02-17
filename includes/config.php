<?php     
    error_reporting(-1);            
    ini_set('display_errors', 1); 

    /** Server URL */
    DEFINE ('SERVER_URI', "http://localhost/developer-logzz");

    /** Secure Checkout URL */
    DEFINE ('CHECKOUT_URI', "http://localhost/developer-logzz/seguro.dropexpress/");

    /** Pagar.me API Key */ 
    #DEFINE ('PGME_API_KEY', 'ak_live_0Yj4rdq83QiAgGw2PLtwCulIwBpRbK'); 
    DEFINE ('PGME_API_KEY', "ak_test_N951BStfuEcJlJV9x0sKtSpPASgn28"); 

    /** The personalized Session Name */
    DEFINE ('SESSION_NAME', sha1('u24ever' . $_SERVER['REMOTE_ADDR'] ));

    /** The global default Timezone */
    date_default_timezone_set('America/Sao_Paulo');

    /** The name of the database */
    DEFINE ('DB_NAME', 'developer_logzz' );

    /** MySQL database username */
    DEFINE ( 'DB_USER', 'root');

    /** MySQL database password */
    DEFINE ( 'DB_PASSWORD', '');

    /** MySQL hostname */
    DEFINE ('DB_HOST', 'localhost');

    /** Database charset */
    DEFINE ('DB_CHARSET', 'utf8mb4');

    /** The database collate type */
    DEFINE ('DB_COLLATE', '');


    /** Create database connection var ($conn) */
    try {
        $conn = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHARSET.'', DB_USER, DB_PASSWORD);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        echo 'ERROR: ' . $e->getMessage();
    }


    /** Set the User Plan show name */
    function userPlanString($user_plan){

        if ($user_plan == 5){
            $userPlanString = "Hero";
        }
        
        else if ($user_plan == 3){
            $userPlanString = "Gold";
        } 
        
        else if ($user_plan == 2 ) {
            $userPlanString = "Silver";
        }
        
        else if ($user_plan == 1 ) {
            $userPlanString = "Bronze";
        }

        else if ($user_plan == 4 ) {
            $userPlanString = "Personalizado";
        }

        else if ($user_plan == 6 ) {
            $userPlanString = "Operador Logístico";
        }

        return $userPlanString;
    }
    
    /** Formate the pickerCalendar date input */
    function pickerDateFormate($pickerDate) {
        $pickerDate = explode(" ", $pickerDate);
        $month = $pickerDate[1];
        
        switch ($month) {
            case "Janeiro,":
                $month = '01';
                break;
            case "Fevereiro,":
                $month = '02';
                break;
            case "Março,":
                $month = '03';
                break;
            case "Abril,":
                $month = '04';
                break;
            case "Maio,":
                $month = '05';
                break;
            case "Junho,":
                $month = '06';
                break;
            case "Julho,":
                $month = '07';
                break;
            case "Agosto,":
                $month = '08';
                break;
            case "Setembro,":
                $month = '09';
                break;
            case "Outubro,":
                $month = '10';
                break;
            case "Novembro,":
                $month = '11';
                break;
            case "Dezembro,":
                $month = '12';
                break;
        }
        
        $formated_date = $pickerDate[2] . "-" . $month . "-" . $pickerDate[0] . " 00:00:00";
        return $formated_date;

    }
      
    /** Get bank name*/
    function bankName ($bank_number){
        try {
            $temp_conn = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHARSET.'', DB_USER, DB_PASSWORD);
            $temp_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo 'ERROR: ' . $e->getMessage();
        }

        $get_bank_name = $temp_conn->prepare('SELECT bank_name, bank_short_name FROM bank_list WHERE bank_number = :bank_number');
        $get_bank_name->execute(array('bank_number' => $bank_number));
        $bank_name = $get_bank_name->fetch();

        if ($bank_name['bank_short_name'] == NULL || empty($bank_name['bank_short_name'])){
            $bank_name = $bank_name['bank_name'];
        } else {
            $bank_name = $bank_name['bank_short_name'];
        }

        return $bank_name;
    }

    function raised($user__id){
        try {
            $temp_conn = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.'', DB_USER, DB_PASSWORD);
            $temp_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo 'ERROR: ' . $e();
        }

        $get_value_raised = $temp_conn->prepare('SELECT meta_value FROM `transactions_meta` WHERE `user__id` = :user__id AND `meta_key` LIKE "total_comission" ');
        $get_value_raised->execute(array('user__id' => $user__id));

        while($value_raised = $get_value_raised->fetch()){
            return $value_raised['meta_value'];
        }
        return 0.00;
    }
