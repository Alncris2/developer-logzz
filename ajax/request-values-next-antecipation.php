<?php 
error_reporting(-1);            
ini_set('display_errors', 1);   
require "../includes/config.php";
session_name(SESSION_NAME); 
session_start();

if(!isset($_GET['user_id'])){
    $user__id = $_SESSION['UserID'];
} else {
    $user__id = $_GET['UserID'];
}

if(isset($_GET['value_request'])){
    $value_request = floatval(addslashes(str_replace(',', '.', str_replace('.', '', $_GET['value_request']))));


    try { 
        $get_current_anticipation_value = $conn->prepare("SELECT sum(value_liquid), count(*) FROM transactions t WHERE user_id = :user__id AND type = 7 AND date_end > now() ORDER BY date_end ASC");
        $get_current_anticipation_value->execute(array('user__id' => $user__id));
        $actual_transaction = $get_current_anticipation_value->fetch();
        if($actual_transaction[0] == $value_request){
            $values[0] = array(
                'value' => number_format($actual_transaction[0], 2, ',', '.'),
                'quant' => $actual_transaction[1],
                'ids'   => 'all'
            );
            $feedback = array('status' => 1, 'values' => $values);
            echo json_encode($feedback);
            exit;
        }

        $get_current_anticipation_value = $conn->prepare("SELECT value_liquid, transaction_id FROM transactions t WHERE user_id = :user__id AND type = 7 AND value_liquid = :value_liquid AND transaction_id > now() ORDER BY date_end ASC limit 1");
        $get_current_anticipation_value->execute(array('user__id' => $user__id, 'value_liquid' => $value_request));
        if($actual_transaction = $get_current_anticipation_value->fetch()){
            $values[0] = array(
                'value' => number_format($actual_transaction[0], 2, ',', '.'),
                'quant' => 1,
                'ids'   => $actual_transaction[1]
            );
            $feedback = array('status' => 1, 'values' => $values);
            echo json_encode($feedback);
            exit;
        }

        $lastValue = $sum_values = 0.00;
        $lastRow = $rowSearch = $last_id = $actual_value = 0; 
        $ids = '0';
        do {

            $lastValue = $sum_values;
            $lastRow = $rowSearch;
            $lastids = $ids;
            $rowSearch++; 
            $get_current_anticipation_value = $conn->prepare("SELECT value_liquid, transaction_id FROM transactions t WHERE user_id = :user__id AND type = 7 AND date_end > now() ORDER BY transaction_id ASC limit $rowSearch, 1");
            $get_current_anticipation_value->execute(array('user__id' => $user__id));
            if($actual_transaction = $get_current_anticipation_value->fetch()){
                if($last_id == $actual_transaction['transaction_id']){
                    $rowSearch--;
                    break;
                }


                $sum_values += (float) $actual_value = $actual_transaction['value_liquid'];
                $last_id = $actual_transaction['transaction_id'];
                $ids .= ','. $actual_transaction['transaction_id'];
                
                if($sum_values == $value_request){
                    $values[0] = array(
                        'value' => number_format($sum_values, 2, ',', '.'),
                        'quant' => $rowSearch,
                        'ids'    => $ids
                    );
        
                    $feedback = array('status' => 1, 'values' => $values);
                    echo json_encode($feedback);
                    exit;
                }
            } else {
                $rowSearch--;
                break;
            }            
        } while($value_request > $sum_values);

        if($sum_values == 0){
            $values = array();
        
            $feedback = array('status' => 1, 'values' => $values);
            echo json_encode($feedback);
            exit;
        }

        if($sum_values == $lastValue){
            $lastRow = $rowSearch - 1;
            $lastValue -= (float) $actual_value;
        }

        if($lastValue > 0){
            $values[0] = array(
                'value' => number_format($lastValue, 2, ',', '.'),
                'quant' => $lastRow,
                'ids'    => $lastids
            );
        }
    
        $values[] = array(
            'value' => number_format($sum_values, 2, ',', '.'),
            'quant' => $rowSearch,
            'ids'    => $ids
        );

    
        $feedback = array('status' => 1, 'values' => $values);
        echo json_encode($feedback);
        exit;
    } catch(PDOException $e) {
        $error = 'ERROR: ' . $e->getMessage();
        $feedback = array('title' => 'Erro!', 'msg' => $error, 'type' => 'erro');
        echo json_encode($feedback);
        exit;
    }
}
