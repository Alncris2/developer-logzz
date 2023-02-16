<?php
error_reporting(-1);
ini_set('display_errors', 1);

require "../includes/config.php";
session_name(SESSION_NAME);
session_start();


// if(!isset($_GET['user__id'])){

// }

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
            $filter_type = '';
            break;
        }
    }
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
}

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

                $html_response .= '<div class="card accordion__item">';
                $html_response .= '<div class="card-header" data-toggle="collapse" data-target="#historic-collapse-' . date('Y-m-d', strtotime($transactions_days['transaction_day'])) . '" aria-expanded="false">';
                $html_response .= '<div class="col-6 text-left d-flex">';
                $html_response .= '<h6>' . date_format(date_create($transactions_days['transaction_day']), 'd/m/y') . '&nbsp; </h6>';
                $html_response .= '<small> &nbsp;' . $transactions_days['transaction_day_quant'] . ($transactions_days['transaction_day_quant'] == 1 ? ' registro' : ' registros') . '</small>';
                $html_response .= '</div>';
                $html_response .= '<div class="col-6 text-right">';
                $html_response .= 'Saldo&nbsp;&nbsp; <b> R$ ' . number_format($balance_day, 2, ',', '.') . ' </b><small>(R$ ' . number_format($transactions_days['transaction_day_value'], 2, ',', '.') . ')</small>';
                $html_response .= ($in_review_balance_day ? '<br><small class="text-warning"><i class="far fa-clock"></i> Pendente R$' . number_format($in_review_balance_day, 2, ',', '.') . '</span></small>' : '');
                $html_response .= '</div>';
                $html_response .= '<a aria-expanded="false"><i style="color: #777777" class="fas fa-angle-down"></i></a>';
                $html_response .= '</div>';
                $html_response .= '<div id="historic-collapse-' . date('Y-m-d', strtotime($transactions_days['transaction_day'])) . '" class="card-bodyaccordion__body collapse" data-parent="#historic-transaction-accordion">';
                $html_response .= '
                <table class="table movement-history">
                    <thead>
                        <tr>
                        <th style="text-align: center;">Data</th> 
                        <th style="text-align: center;">Descrição</th>
                        <th style="text-align: center;">Movimentação</th>
                        <th style="text-align: center;">Valor</th>
                        <th style="text-align: center;">Taxa</th>
                        <th style="text-align: center;">Líquido</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: center;">Comprovante</th>
                        </tr>
                    </thead>
                <tbody>';

                $date_filter = $transactions_days['transaction_day'];
                $date_filter_start  = $date_filter . ' 00:00:00';
                $date_filter_end    = $date_filter . ' 23:59:59';

                $get_transactions_list = $conn->prepare("SELECT t.*, transaction_type_name AS type, transaction_status_name AS status, date(date_format((CASE WHEN date_end IS NULL THEN date_start ELSE date_end END), '%Y-%m-%d')) AS order_date FROM transactions t INNER JOIN transactions_type ON transaction_type_id = type INNER JOIN transaction_status ON transaction_status_id = status WHERE user_id = :user__id AND (date_end < now() OR date_end IS NULL) AND (CASE WHEN t.date_end is null THEN (t.date_start BETWEEN :date_filter_start AND :date_filter_end) ELSE (t.date_end BETWEEN :date_filter_start AND :date_filter_end) END) $filter_type $filter_movement ORDER BY order_date ASC");
                $get_transactions_list->execute(array('user__id' => $user__id, 'date_filter_start' => $date_filter_start, 'date_filter_end' => $date_filter_end));
                while ($transactions_list = $get_transactions_list->fetch()) {

                    if ($transactions_list['type'] == "Saque") {
                        $signal = "-";
                        $color = "#ff2929";
                    } else if ($transactions_list['type'] == "Antecipação") {
                        $signal = "-";
                        $color = "#ff2929";
                    } else if ($transactions_list['type'] == "Cobrança") {
                        $signal = "-";
                        $color = "#ff2929";
                    } else if ($transactions_list['type'] == "Repasse") {
                        $signal = "+";
                        $color = "#2bc155";
                    } else if ($transactions_list['type'] == "Upgrade") {
                        $signal = "-";
                        $color = "#ff2929";
                    } else if ($transactions_list['type'] == "Reembolso") {
                        $signal = "";
                        $color = "#ff2929";
                    } else if ($transactions_list['type'] == "Frustrado") {
                        $signal = "-";
                        $color = "#ff2929";
                    } else if ($transactions_list['type'] == "Pedido") {
                        $signal = "+";
                        $color = "#2bc155";
                    } else if ($transactions_list['type'] == "Manual") {
                        $description = "Alteração Manual";
                        $color = "#ff2929";
                    } else {
                        $description = "";
                        $signal = "-";
                        $color = "#2bc155";
                    }


                    $html_response .= '<tr>';
                    $html_response .= '<td class="fs-12 text-center" data-order=" ' . date_format(date_create($transactions_list['date_end']), 'YmdHis') . ' ">';
                    $html_response .= date_format(date_create($transactions_list['date_end']), 'd/m/y \<\b\r\> H:i');
                    $html_response .= '</td>';
                    $html_response .= '<td style="text-align: center;">';

                    if ($transactions_list['type'] == 'Pedido') {
                        $html_response .= "Comissão <a class='text-primary' href='" . SERVER_URI . '/meu-pedido/' . $transactions_list['order_number'] . "' target='_blank'><i class='fa fa-eye'></i></a>";
                    } else if ($transactions_list['type'] == 'Antecipação' && !$transactions_list['bank_proof']) {
                        $html_response .= "Tax Antecipação"; 
                    } else {
                        $html_response .=  $transactions_list['type'];
                    }

                    $html_response .= '</td>';
                    $html_response .= '<td style="text-align: center;">';

                    if ($transactions_list['type'] == 'Pedido') {
                        $html_response .=  "Entrada";
                    } else if ($transactions_list['type'] == 'Antecipação') {
                        $html_response .=  "--";
                    } else {
                        $html_response .=  "Saída";
                    }

                    $html_response .= '</td>';
                    $html_response .= '<td style="text-align: center; color: ' . $color . '; font-weight: bold;">' . $signal . " R$ " . number_format($transactions_list['value_brute'], 2, ",", ".") . '</td>';
                    $html_response .= '<td style="text-align: center; color: #7e7e7e; font-weight: bold;"> - R$ ' . number_format($transactions_list['tax_value'] + $transactions_list['logistic_value'], 2, ",", ".") . '</td>';
                    $html_response .= '<td style="text-align: center; color: ' . $color . '; font-weight: bold;">';

                    // if ($transactions_list['type'] == 'Antecipação') {
                    //     $html_response .=  "+ R$ " . number_format(($transactions_list['value_brute'] - $transactions_list['tax_value']), 2, ",", ".");
                    // } else {
                        $html_response .=  " R$ " . number_format($transactions_list['value_liquid'], 2, ",", ".");
                    // }
                    $html_response .= '</td>';
                    $html_response .= '<td>';
                    if ($transactions_list['status'] == 'Cancelado') {
                        $html_response .=  '<span class="badge badge-sm d-block m-auto light badge-danger"><i class="far fa-check-circle"></i> Cancelado</span>';
                    } else if ($transactions_list['status'] == 'Concluído') {
                        $html_response .=  '<span class="badge badge-sm d-block m-auto light badge-success"><i class="far fa-check-circle"></i> Concluído</span>';
                    } else if ($transactions_list['status'] == 'Atrasado') {
                        $html_response .= '<span class="badge badge-sm d-block m-auto light badge-info"><i class="far fa-check-circle"></i> Atrasado</span>';
                    } else if (!$transactions_list['bank_proof'] && $transactions_list['type'] != 'Pedido') {
                        $html_response .=  '<span class="badge badge-sm d-block m-auto light badge-warning"><i class="far fa-clock"></i> Pendente</span>';
                    } else {
                        $html_response .=  '<span class="badge badge-sm d-block m-auto light badge-primary"><i class="far fa-check-circle"></i> Liberado</span>';
                    }

                    $html_response .= '</td>';
                    $html_response .= '<td style="text-align: center;">';

                    if ($transactions_list['orders_antecipation']) {
                        if ($transactions_list['orders_antecipation']) {
                            $html_response .= '<a  class="" title="Ver detalhamento da transação" href="' . SERVER_URI . "/perfil/financeiro/detalhamento/" . $transactions_list['transaction_id'] . '" target="_blank">
                                <i class="fa fa-list"></i>
                            </a>';
                        } else {
                            $html_response .= '<a title="Não temos informaçãoes dos pedidos antecipados :(">
                            <i class="fa fa-eye-slash"></i>
                            </a>';
                        }
                    } else {

                        if($transactions_list['bank_proof'] == NULL){
                            $html_response .= '<a title="Ver comprovante de trasnferência">
                            <i class="fa fa-eye-slash"></i>
                            </a>';
                        } else {
                            $html_response .= '
                            <a title="Ver comprovante de trasnferência" href="' . SERVER_URI .'/uploads/saques/comprovantes/'. $transactions_list['bank_proof'] .'" target="_blank">
                                <i class="fa fa-eye"></i>
                            </a>';

                        }
                    }

                    $html_response .= '</td>
                    </tr>';
                }
                $html_response .= '</tbody>
                </table>
                </div>
                </div>';
            }
        }
    }
} else {
    $html_response = '';
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
    
                $html_response .= '<div class="card accordion__item">';
                $html_response .= '<div class="card-header" data-toggle="collapse" data-target="#historic-collapse-' . date('Y-m-d', strtotime($transactions_days['transaction_day'])) . '" aria-expanded="false">';
                $html_response .= '<div class="col-6 text-left d-flex">';
                $html_response .= '<h6>' . date_format(date_create($transactions_days['transaction_day']), 'd/m/y') . '&nbsp; </h6>';
                $html_response .= '<small> &nbsp;' . $transactions_days['transaction_day_quant'] . ($transactions_days['transaction_day_quant'] == 1 ? ' registro' : ' registros') . '</small>';
                $html_response .= '</div>';
                $html_response .= '<div class="col-6 text-right">';
                $html_response .= 'Saldo&nbsp;&nbsp; <b> R$ ' . number_format($balance_day, 2, ',', '.') . ' </b><small>(R$ ' . number_format($transactions_days['transaction_day_value'], 2, ',', '.') . ')</small>';
                $html_response .= ($in_review_balance_day ? '<br><small class="text-warning"><i class="far fa-clock"></i> Pendente R$' . number_format($in_review_balance_day, 2, ',', '.') . '</span></small>' : '');
                $html_response .= '</div>';
                $html_response .= '<a aria-expanded="false"><i style="color: #777777" class="fas fa-angle-down"></i></a>';
                $html_response .= '</div>';
                $html_response .= '<div id="historic-collapse-' . date('Y-m-d', strtotime($transactions_days['transaction_day'])) . '" class="card-bodyaccordion__body collapse" data-parent="#historic-transaction-accordion">';
                $html_response .= '
                <table class="table movement-history">
                    <thead>
                        <tr>
                        <th style="text-align: center;">Data</th> 
                        <th style="text-align: center;">Descrição</th>
                        <th style="text-align: center;">Movimentação</th>
                        <th style="text-align: center;">Valor</th>
                        <th style="text-align: center;">Taxa</th>
                        <th style="text-align: center;">Líquido</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: center;">Comprovante</th>
                        </tr>
                    </thead>
                <tbody>';
    
                $date_filter = $transactions_days['transaction_day'];
                $date_filter_start  = $date_filter . ' 00:00:00';
                $date_filter_end    = $date_filter . ' 23:59:59';
    
                $get_transactions_list = $conn->prepare("SELECT t.*, transaction_type_name AS type, transaction_status_name AS status, date(date_format((CASE WHEN date_end IS NULL THEN date_start ELSE date_end END), '%Y-%m-%d')) AS order_date FROM transactions t INNER JOIN transactions_type ON transaction_type_id = type INNER JOIN transaction_status ON transaction_status_id = status WHERE user_id = :user__id AND (date_end < now() OR date_end IS NULL) AND (CASE WHEN t.date_end is null THEN (t.date_start BETWEEN :date_filter_start AND :date_filter_end) ELSE (t.date_end BETWEEN :date_filter_start AND :date_filter_end) END) $filter_type $filter_movement ORDER BY order_date ASC");
                $get_transactions_list->execute(array('user__id' => $user__id, 'date_filter_start' => $date_filter_start, 'date_filter_end' => $date_filter_end));
                while ($transactions_list = $get_transactions_list->fetch()) {
    
                    if ($transactions_list['type'] == "Saque") {
                        $signal = "-";
                        $color = "#ff2929";
                    } else if ($transactions_list['type'] == "Antecipação") {
                        $signal = "-";
                        $color = "#ff2929";
                    } else if ($transactions_list['type'] == "Cobrança") {
                        $signal = "-";
                        $color = "#ff2929";
                    } else if ($transactions_list['type'] == "Repasse") {
                        $signal = "+";
                        $color = "#2bc155";
                    } else if ($transactions_list['type'] == "Upgrade") {
                        $signal = "-";
                        $color = "#ff2929";
                    } else if ($transactions_list['type'] == "Reembolso") {
                        $signal = "";
                        $color = "#ff2929";
                    } else if ($transactions_list['type'] == "Frustrado") {
                        $signal = "-";
                        $color = "#ff2929";
                    } else if ($transactions_list['type'] == "Pedido") {
                        $signal = "+";
                        $color = "#2bc155";
                    } else if ($transactions_list['type'] == "Manual") {
                        $description = "Alteração Manual";
                        $color = "#ff2929";
                    } else {
                        $description = "";
                        $signal = "-";
                        $color = "#2bc155";
                    }
    
    
                    $html_response .= '<tr>';
                    $html_response .= '<td class="fs-12 text-center" data-order=" ' . date_format(date_create($transactions_list['date_end']), 'YmdHis') . ' ">';
                    $html_response .= date_format(date_create($transactions_list['date_end']), 'd/m/y \<\b\r\> H:i');
                    $html_response .= '</td>';
                    $html_response .= '<td style="text-align: center;">';
    
                    if ($transactions_list['type'] == 'Pedido') {
                        $html_response .= "Comissão <a class='text-primary' href='" . SERVER_URI . '/meu-pedido/' . $transactions_list['order_number'] . "' target='_blank'><i class='fa fa-eye'></i></a>";
                    } else if ($transactions_list['type'] == 'Antecipação' && !$transactions_list['bank_proof']) {
                        $html_response .= "Tax Antecipação"; 
                    } else {
                        $html_response .=  $transactions_list['type'];
                    }
    
                    $html_response .= '</td>';
                    $html_response .= '<td style="text-align: center;">';
    
                    if ($transactions_list['type'] == 'Pedido') {
                        $html_response .=  "Entrada";
                    } else if ($transactions_list['type'] == 'Antecipação') {
                        $html_response .=  "--";
                    } else {
                        $html_response .=  "Saída";
                    }
    
                    $html_response .= '</td>';
                    $html_response .= '<td style="text-align: center; color: ' . $color . '; font-weight: bold;">' . $signal . " R$ " . number_format($transactions_list['value_brute'], 2, ",", ".") . '</td>';
                    $html_response .= '<td style="text-align: center; color: #7e7e7e; font-weight: bold;"> - R$ ' . number_format($transactions_list['tax_value'] + $transactions_list['logistic_value'], 2, ",", ".") . '</td>';
                    $html_response .= '<td style="text-align: center; color: ' . $color . '; font-weight: bold;">';
    
                    // if ($transactions_list['type'] == 'Antecipação') {
                    //     $html_response .=  "+ R$ " . number_format(($transactions_list['value_brute'] - $transactions_list['tax_value']), 2, ",", ".");
                    // } else {
                        $html_response .=  " R$ " . number_format($transactions_list['value_liquid'], 2, ",", ".");
                    // }
                    $html_response .= '</td>';
                    $html_response .= '<td>';
                    if ($transactions_list['status'] == 'Cancelado') {
                        $html_response .=  '<span class="badge badge-sm d-block m-auto light badge-danger"><i class="far fa-check-circle"></i> Cancelado</span>';
                    } else if ($transactions_list['status'] == 'Concluído') {
                        $html_response .=  '<span class="badge badge-sm d-block m-auto light badge-success"><i class="far fa-check-circle"></i> Concluído</span>';
                    } else if ($transactions_list['status'] == 'Atrasado') {
                        $html_response .= '<span class="badge badge-sm d-block m-auto light badge-info"><i class="far fa-check-circle"></i> Atrasado</span>';
                    } else if (!$transactions_list['bank_proof'] && $transactions_list['type'] != 'Pedido') {
                        $html_response .=  '<span class="badge badge-sm d-block m-auto light badge-warning"><i class="far fa-clock"></i> Pendente</span>';
                    } else {
                        $html_response .=  '<span class="badge badge-sm d-block m-auto light badge-primary"><i class="far fa-check-circle"></i> Liberado</span>';
                    }
    
                    $html_response .= '</td>';
                    $html_response .= '<td style="text-align: center;">';
    
                    if ($transactions_list['orders_antecipation']) {
                        if ($transactions_list['orders_antecipation']) {
                            $html_response .= '<a  class="" title="Ver detalhamento da transação" href="' . SERVER_URI . "/perfil/financeiro/detalhamento/" . $transactions_list['transaction_id'] . '" target="_blank">
                                <i class="fa fa-list"></i>
                            </a>';
                        } else {
                            $html_response .= '<a title="Não temos informaçãoes dos pedidos antecipados :(">
                            <i class="fa fa-eye-slash"></i>
                            </a>';
                        }
                    } else {
    
                        if($transactions_list['bank_proof'] == NULL){
                            $html_response .= '<a title="Ver comprovante de trasnferência">
                            <i class="fa fa-eye-slash"></i>
                            </a>';
                        } else {
                            $html_response .= '
                            <a title="Ver comprovante de trasnferência" href="' . SERVER_URI .'/uploads/saques/comprovantes/'. $transactions_list['bank_proof'] .'" target="_blank">
                                <i class="fa fa-eye"></i>
                            </a>';
    
                        }
                    }
    
                    $html_response .= '</td>
                    </tr>';
                }
                $html_response .= '</tbody>
                </table>
                </div>
                </div>';
            }
        }
    }
}

if(empty(str_replace(' ', '', $html_response))) {
    $html_response = '<div class="alert alert-warning fade mb-3 show solid">
        <i class="fa fa-info-circle" aria-hidden="true"></i><strong> Sem transações de acordo com seus filtros para mostrar na listagem!
    </div>';
}

$feedback = array('status' => 1, 'html_response' => $html_response);
echo json_encode($feedback);
exit;


