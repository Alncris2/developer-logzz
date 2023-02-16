<?php
error_reporting(-1);
ini_set('display_errors', 1);

require "../includes/config.php";
session_name(SESSION_NAME);
session_start();

$filename = 'Historico de transações - logzz.xls';
header('Content-Description: File Transfer');
header('Content-type: application/excel; charset=utf-8');
header('Content-Disposition: attachment; filename='.$filename);
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');    
header('Date: ' . date ( 'D M j G:i:s T Y' ) );
header('Last-Modified: ' . date ( 'D M j G:i:s T Y' ) );
header("Content-Disposition: attachment;filename=$filename" );


$user__id = $_SESSION['UserID'];
$date_start = date('Y-m-d', strtotime('-7days'));

if (isset($_POST['description'])) {
    switch($_POST['description']) {
        case 'saque': {
            $filter_type = " AND type = 5 ";
            break;
        }
        case 'antecipacao': {
            $filter_type = " AND type = 1 ";
            break;
        }
        case 'cobranca': {
            $filter_type = " AND type IN (2,3,6)";
            break;
        }
        case 'comissao': {
            $filter_type = " AND type = 7 ";
            break;
        }
        case 'despesas': {
            $filter_type = " AND type IN (4) ";
            break;
        }
        case 'estorno': {
            $filter_type = " AND type IN (8,9) ";
            break;
        }
        default: {
            $filter_type = "";
            break;
        }
    }
} else {
    $filter_type = "";
}

if (isset($_POST['movement'])) {
    switch($_POST['movement']) {
        case 'entrada': {
            $filter_movement = " AND type = 7 ";
            break;
        }
        case 'saida': {
            $filter_movement = " AND type NOT IN (7) ";
            break;
        }
        default: {
            $filter_movement = '';
            break;
        }
    }
} else {
    $filter_movement = '';
}

$ref_days = 0;
if (isset($_POST['tipo_date']) && $_POST['tipo_date'] == 'today') {
    $ref_days = 0;
} else if (isset($_POST['tipo_date']) && $_POST['tipo_date'] == 'yesterday') {
    $ref_days = 1;
} else if (isset($_POST['tipo_date']) && $_POST['tipo_date'] == '7days') {
    $ref_days = 6;
} else if (isset($_POST['tipo_date']) && $_POST['tipo_date'] == '30days') {
    $ref_days = 30;
} else if (isset($_POST['tipo_date']) && $_POST['tipo_date'] == 'currentmonth') {
    $ref_days =  date('d', strtotime('-1days'));
}

$html_response = '<html xmlns:x="urn:schemas-microsoft-com:office:excel">
    <head>
        <!--[if gte mso 9]>
        <xml>
            <x:ExcelWorkbook>
                <x:ExcelWorksheets>
                    <x:ExcelWorksheet>
                        <x:Name>Sheet 1</x:Name>
                        <x:WorksheetOptions>
                            <x:Print>
                                <x:ValidPrinterInfo/>
                            </x:Print>
                        </x:WorksheetOptions>
                    </x:ExcelWorksheet>
                </x:ExcelWorksheets>
            </x:ExcelWorkbook>
        </xml>
        <![endif]-->
    </head>
    
    <body>
       <table>
       <tr><td colspan="7" bgcolor="#a1a1a1"> Histórico de Transações - LOGZZ</td></tr>';

if(isset($_POST['tipo_date']) && $_POST['tipo_date'] == 'personalizado'){

    #filter date_start
    if(!empty( str_replace(' ', '', $_POST['date_start']))){

        $start_date = pickerDateFormate($_POST['date_start']);
        $start_date = explode(" ", $start_date);
        $start_date = date_create($start_date[0]);
    } else {
        $start_date 	= date_create(date('Y-m-d'));
    }

    #filter date_end
    if(!empty( str_replace(' ', '', $_POST['date_end']))){
        $end_date = pickerDateFormate($_POST['date_end']);
        $end_date = explode(" ", $end_date)[0];
        $date_add_1day = date('Y-m-d', strtotime("+1 day", strtotime($end_date)));
        $end_date 	= date_create($date_add_1day);
    } else {
        $end_date 	= date_create(date('Y-m-d', strtotime("+1 day")));
    }
    $interval = DateInterval::createFromDateString('1 day');
    $daterange = new DatePeriod($start_date, $interval , $end_date);
    $daterange = array_reverse(iterator_to_array($daterange));

    $html_response = '';
    
    foreach($daterange as $date1){

        $date_start = $date1->format('Y-m-d');        

        
        $get_balance_to_day = $conn->prepare("SELECT SUM(CASE WHEN type = 5 THEN value_brute WHEN type = 1 AND bank_proof is not null THEN value_brute ELSE value_liquid END) CURRENT_SUM, date(date_format((CASE WHEN date_end IS NULL THEN date_start ELSE date_end END), '%Y-%m-%d')) AS transaction_day from transactions A where user_id = :user_id AND (CASE WHEN date_end is null THEN (date_start < now() AND date_start <= :date_end ) ELSE (date_end < now() AND date_end <= :date_end) END) ORDER BY transaction_day ASC ");
        $get_balance_to_day->execute(array('user_id' => $user__id, 'date_end' => $date_start . ' 23:59:59'));
        if (!$balance_day = $get_balance_to_day->fetch()[0]) {
            $balance_day = 0.00; 
        }
    
        $get_in_review_balance_day = $conn->prepare("SELECT ABS(sum(value_brute)) from transactions where date_start between :date_start AND :date_end AND user_id = :user_id AND (date_end IS NULL)  ORDER BY date_start DESC");
        $get_in_review_balance_day->execute(array('user_id' => $user__id, 'date_start' => $date_start . ' 00:00:00', 'date_end' => $date_start . ' 23:59:59'));
        if (!$in_review_balance_day = $get_in_review_balance_day->fetch()[0]) {
            $in_review_balance_day = null;
        }
    
        $get_transactions_days = $conn->prepare("SELECT user_id, date(date_format(date_end, '%Y-%m-%d')) AS transaction_day, (SELECT sum(value_liquid) FROM transactions b WHERE b.user_id = a.user_id AND b.date_end BETWEEN '$date_start 00:00:00' AND '$date_start 23:59:59' AND (b.date_end < now() OR b.date_end IS NOT NULL) $filter_type $filter_movement GROUP BY transaction_day ORDER BY b.date_start DESC) AS transaction_day_value, count(*) AS transaction_day_quant FROM transactions a WHERE user_id = :user_id AND date_end BETWEEN '$date_start 00:00:00' AND '$date_start 23:59:59' AND (date_end < now() OR date_end IS NULL) $filter_type $filter_movement GROUP BY transaction_day ORDER BY `date_end` DESC");
        $get_transactions_days->execute(array('user_id' => $user__id));
        if ($get_transactions_days->rowCount() > 0) {
            while ($transactions_days = $get_transactions_days->fetch()) {

                if(isset($verify_content)){
                    $html_response .= '<tr></tr>';
                }
                $html_response .= '<tr>';
                $html_response .= '<th colspan="3" bgcolor="#fff">' . date_format(date_create($transactions_days['transaction_day']), 'd/m/y') . ' ('. $transactions_days['transaction_day_quant'] . ($transactions_days['transaction_day_quant'] == 1 ? ' registro' : ' registros') .')</th>';
                $html_response .= '<th colspan="4" bgcolor="#fff">Saldo R$ ' . number_format($balance_day, 2, ',', '.') . ' (R$ ' . number_format($transactions_days['transaction_day_value'], 2, ',', '.') . ')</th>';
                $html_response .= '</tr>';
                if($in_review_balance_day){
                    $html_response .= '<tr>';
                        $html_response .= '<th colspan="7">Pendente R$' . number_format($in_review_balance_day, 2, ',', '.') . '</tr>';
                    $html_response .= '</tr>';
                }

                $html_response .= '<tr>
                                <th>Data</th> 
                                <th>Descrição</th>
                                <th>Movimentação</th>
                                <th>Valor</th>
                                <th>Taxa</th>
                                <th>Líquido</th>
                                <th>Status</th>
                            </tr>
                            
                            <tbody>';
        
                $date_filter = $transactions_days['transaction_day'];
                $date_filter_start  = $date_filter . ' 00:00:00';
                $date_filter_end    = $date_filter . ' 23:59:59';
    
                $get_transactions_list = $conn->prepare("SELECT t.*, transaction_type_name AS type, transaction_status_name AS status, date(date_format((CASE WHEN date_end IS NULL THEN date_start ELSE date_end END), '%Y-%m-%d')) AS order_date FROM transactions t INNER JOIN transactions_type ON transaction_type_id = type INNER JOIN transaction_status ON transaction_status_id = status WHERE user_id = :user__id AND (date_end < now() OR date_end IS NULL) AND (CASE WHEN t.date_end is null THEN (t.date_start BETWEEN :date_filter_start AND :date_filter_end) ELSE (t.date_end BETWEEN :date_filter_start AND :date_filter_end) END) $filter_type $filter_movement ORDER BY order_date ASC");
                $get_transactions_list->execute(array('user__id' => $user__id, 'date_filter_start' => $date_filter_start, 'date_filter_end' => $date_filter_end));
                while ($transactions_list = $get_transactions_list->fetch()) {
    
    
                    $html_response .= '<tr>';
                    $html_response .= '<td class="fs-12 text-center" data-order=" ' . date_format(date_create($transactions_list['date_end']), 'YmdHis') . ' ">';
                    $html_response .= date_format(date_create($transactions_list['date_end']), 'd/m/y H:i');
                    $html_response .= '</td>';
                    $html_response .= '<td >';
    
                    if ($transactions_list['type'] == 'Pedido') {
                        $html_response .= "Comissão";
                    } else if ($transactions_list['type'] == 'Antecipação' && !$transactions_list['bank_proof']) {
                        $html_response .= "Tax Antecipação"; 
                    } else {
                        $html_response .=  $transactions_list['type'];
                    }
    
                    $html_response .= '</td>';
                    $html_response .= '<td >';
    
                    if ($transactions_list['type'] == 'Pedido') {
                        $html_response .=  "Entrada";
                    } else if ($transactions_list['type'] == 'Antecipação') {
                        $html_response .=  "--";
                    } else {
                        $html_response .=  "Saída";
                    }
    
                    $html_response .= '</td>';
                    $html_response .= '<td> R$ ' . number_format($transactions_list['value_brute'], 2, ",", ".") . '</td>';
                    $html_response .= '<td> - R$ ' . number_format($transactions_list['tax_value'] + $transactions_list['logistic_value'], 2, ",", ".") . '</td>';
                    $html_response .= '<td>';
    
                    // if ($transactions_list['type'] == 'Antecipação') {
                    //     $html_response .=  "+ R$ " . number_format(($transactions_list['value_brute'] - $transactions_list['tax_value']), 2, ",", ".");
                    // } else {
                        $html_response .=  " R$ " . number_format($transactions_list['value_liquid'], 2, ",", ".");
                    // }
                    $html_response .= '</td>';
                    $html_response .= '<td>';
                    if ($transactions_list['status'] == 'Cancelado') {
                        $html_response .=  'Cancelado';
                    } else if ($transactions_list['status'] == 'Concluído') {
                        $html_response .=  'Concluído';
                    } else if ($transactions_list['status'] == 'Atrasado') {
                        $html_response .= 'Atrasado';
                    } else if (!$transactions_list['bank_proof'] && $transactions_list['type'] != 'Pedido') {
                        $html_response .=  'Pendente';
                    } else {
                        $html_response .=  'Liberado';
                    }
    
                    $html_response .= '</td>
                    </tr>';
                }


                
                $verify_content = true;
                $html_response .= '</tbody>';
            }
        }
    }
    


} else {
    for ($aux = 0; $aux <= $ref_days; $aux++) {
    
        if (isset($_POST['tipo_date']) && $_POST['tipo_date'] == 'yesterday' && $aux == 0) {
            continue;
        }
    
        $date_start = date('Y-m-d', strtotime("-$aux days"));
    
        $get_balance_to_day = $conn->prepare("SELECT SUM(CASE WHEN type = 5 THEN value_brute WHEN type = 1 AND bank_proof is not null THEN value_brute ELSE value_liquid END) CURRENT_SUM, date(date_format((CASE WHEN date_end IS NULL THEN date_start ELSE date_end END), '%Y-%m-%d')) AS transaction_day from transactions A where user_id = :user_id AND (CASE WHEN date_end is null THEN (date_start < now() AND date_start <= :date_end ) ELSE (date_end < now() AND date_end <= :date_end) END) ORDER BY transaction_day ASC ");
        $get_balance_to_day->execute(array('user_id' => $user__id, 'date_end' => $date_start . ' 23:59:59'));
        if (!$balance_day = $get_balance_to_day->fetch()[0]) {
            $balance_day = 0.00; 
        }
    
        $get_in_review_balance_day = $conn->prepare("SELECT ABS(sum(value_brute)) from transactions where date_start between :date_start AND :date_end AND user_id = :user_id AND (date_end IS NULL)  ORDER BY date_start DESC");
        $get_in_review_balance_day->execute(array('user_id' => $user__id, 'date_start' => $date_start . ' 00:00:00', 'date_end' => $date_start . ' 23:59:59'));
        if (!$in_review_balance_day = $get_in_review_balance_day->fetch()[0]) {
            $in_review_balance_day = null;
        }
    
        $get_transactions_days = $conn->prepare("SELECT user_id, date(date_format(date_end, '%Y-%m-%d')) AS transaction_day, (SELECT sum(value_liquid) FROM transactions b WHERE b.user_id = a.user_id AND b.date_end BETWEEN '$date_start 00:00:00' AND '$date_start 23:59:59' AND (b.date_end < now() OR b.date_end IS NOT NULL) $filter_type $filter_movement GROUP BY transaction_day ORDER BY b.date_start DESC) AS transaction_day_value, count(*) AS transaction_day_quant FROM transactions a WHERE user_id = :user_id AND date_end BETWEEN '$date_start 00:00:00' AND '$date_start 23:59:59' AND (date_end < now() OR date_end IS NULL) $filter_type $filter_movement GROUP BY transaction_day ORDER BY `date_end` DESC");
        $get_transactions_days->execute(array('user_id' => $user__id));
        if ($get_transactions_days->rowCount() > 0) {
            while ($transactions_days = $get_transactions_days->fetch()) {
    
                if(isset($verify_content)){
                    $html_response .= '<tr></tr>';
                }
                $html_response .= '<tr>';
                $html_response .= '<th colspan="3" bgcolor="#fff">' . date_format(date_create($transactions_days['transaction_day']), 'd/m/y') . ' ('. $transactions_days['transaction_day_quant'] . ($transactions_days['transaction_day_quant'] == 1 ? ' registro' : ' registros') .')</th>';
                $html_response .= '<th colspan="4" bgcolor="#fff">Saldo R$ ' . number_format($balance_day, 2, ',', '.') . ' (R$ ' . number_format($transactions_days['transaction_day_value'], 2, ',', '.') . ')</th>';
                $html_response .= '</tr>';
                if($in_review_balance_day){
                    $html_response .= '<tr>';
                        $html_response .= '<th colspan="7">Pendente R$' . number_format($in_review_balance_day, 2, ',', '.') . '</tr>';
                    $html_response .= '</tr>';
                }
    
                $html_response .= '<tr>
                                <th>Data</th> 
                                <th>Descrição</th>
                                <th>Movimentação</th>
                                <th>Valor</th>
                                <th>Taxa</th>
                                <th>Líquido</th>
                                <th>Status</th>
                            </tr>
                            
                            <tbody>';
        
                $date_filter = $transactions_days['transaction_day'];
                $date_filter_start  = $date_filter . ' 00:00:00';
                $date_filter_end    = $date_filter . ' 23:59:59';
    
                $get_transactions_list = $conn->prepare("SELECT t.*, transaction_type_name AS type, transaction_status_name AS status, date(date_format((CASE WHEN date_end IS NULL THEN date_start ELSE date_end END), '%Y-%m-%d')) AS order_date FROM transactions t INNER JOIN transactions_type ON transaction_type_id = type INNER JOIN transaction_status ON transaction_status_id = status WHERE user_id = :user__id AND (date_end < now() OR date_end IS NULL) AND (CASE WHEN t.date_end is null THEN (t.date_start BETWEEN :date_filter_start AND :date_filter_end) ELSE (t.date_end BETWEEN :date_filter_start AND :date_filter_end) END) $filter_type $filter_movement ORDER BY order_date ASC");
                $get_transactions_list->execute(array('user__id' => $user__id, 'date_filter_start' => $date_filter_start, 'date_filter_end' => $date_filter_end));
                while ($transactions_list = $get_transactions_list->fetch()) {
    
    
                    $html_response .= '<tr>';
                    $html_response .= '<td class="fs-12 text-center" data-order=" ' . date_format(date_create($transactions_list['date_end']), 'YmdHis') . ' ">';
                    $html_response .= date_format(date_create($transactions_list['date_end']), 'd/m/y H:i');
                    $html_response .= '</td>';
                    $html_response .= '<td >';
    
                    if ($transactions_list['type'] == 'Pedido') {
                        $html_response .= "Comissão";
                    } else if ($transactions_list['type'] == 'Antecipação' && !$transactions_list['bank_proof']) {
                        $html_response .= "Tax Antecipação"; 
                    } else {
                        $html_response .=  $transactions_list['type'];
                    }
    
                    $html_response .= '</td>';
                    $html_response .= '<td >';
    
                    if ($transactions_list['type'] == 'Pedido') {
                        $html_response .=  "Entrada";
                    } else if ($transactions_list['type'] == 'Antecipação') {
                        $html_response .=  "--";
                    } else {
                        $html_response .=  "Saída";
                    }
    
                    $html_response .= '</td>';
                    $html_response .= '<td> R$ ' . number_format($transactions_list['value_brute'], 2, ",", ".") . '</td>';
                    $html_response .= '<td> - R$ ' . number_format($transactions_list['tax_value'] + $transactions_list['logistic_value'], 2, ",", ".") . '</td>';
                    $html_response .= '<td>';
    
                    // if ($transactions_list['type'] == 'Antecipação') {
                    //     $html_response .=  "+ R$ " . number_format(($transactions_list['value_brute'] - $transactions_list['tax_value']), 2, ",", ".");
                    // } else {
                        $html_response .=  " R$ " . number_format($transactions_list['value_liquid'], 2, ",", ".");
                    // }
                    $html_response .= '</td>';
                    $html_response .= '<td>';
                    if ($transactions_list['status'] == 'Cancelado') {
                        $html_response .=  'Cancelado';
                    } else if ($transactions_list['status'] == 'Concluído') {
                        $html_response .=  'Concluído';
                    } else if ($transactions_list['status'] == 'Atrasado') {
                        $html_response .= 'Atrasado';
                    } else if (!$transactions_list['bank_proof'] && $transactions_list['type'] != 'Pedido') {
                        $html_response .=  'Pendente';
                    } else {
                        $html_response .=  'Liberado';
                    }
    
                    $html_response .= '</td>
                    </tr>';
                }
    
    
                
                $verify_content = true;
                $html_response .= '</tbody>';
            }
        }
    }    
}

$html_response .= '</table>
</body></html>';

print chr(255) . chr(254) . mb_convert_encoding($html_response, 'UTF-16LE', 'UTF-8');

