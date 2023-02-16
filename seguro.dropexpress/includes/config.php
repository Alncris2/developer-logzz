<?php
    /** Server URL */
    DEFINE ('SERVER_URI', "https://implements.logzz.com.br");

    /** Secure Checkout URL */
    DEFINE ('CHECKOUT_URI', "https://implements.logzz.com.br/seguro.dropexpress/");

    /** The personalized Session Name */
    DEFINE ('SESSION_NAME', sha1('u24ever' . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']));

    /** The global default Timezone */
    date_default_timezone_set('America/Sao_Paulo');

    /** The name of the database */
    DEFINE ('DB_NAME', 'logzzcom_implements' );

    /** MySQL database username */
    DEFINE ( 'DB_USER', 'logzzcom_implements');

    /** MySQL database password */
    DEFINE ( 'DB_PASSWORD', 'Tq0;$yonjE+&');

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
            
            else if ($user_plan == 4){
                $userPlanString = "Afiliado";
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
?>
