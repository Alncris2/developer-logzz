<?php
error_reporting(-1);             
ini_set('display_errors', 1);


require "../includes/config.php"; 
session_name(SESSION_NAME); 
session_start();


// if(!isset($_GET['user__id'])){

// }

$user__id = $_SESSION['UserID'];

if (isset($_POST['tipo_date']) && $_POST['tipo_date'] == 'today') {
    $date_start = date('Y-m-d', strtotime('-0days'));
} else if (isset($_POST['tipo_date']) && $_POST['tipo_date'] == 'tomorrow') {
    $date_start = date('Y-m-d', strtotime('+1days'));
} else if (isset($_POST['tipo_date']) && $_POST['tipo_date'] == '7days') {
    $date_start = date('Y-m-d', strtotime('+7days'));
} else if (isset($_POST['tipo_date']) && $_POST['tipo_date'] == '15days') {
    $date_start = date('Y-m-d', strtotime('+15days'));
} else if (isset($_POST['tipo_date']) && $_POST['tipo_date'] == '30days') {
    $date_start = date('Y-m-d', strtotime('+30days'));
}




$html_response = '';
$get_transactions_days = $conn->prepare("SELECT date(date_format(date_end, '%Y-%m-%d')) AS transaction_day, sum(value_liquid) AS transaction_day_value, COUNT(*) AS transaction_day_quant FROM transactions b WHERE b.user_id = :user_id AND b.date_end > now() AND b.date_end < '$date_start 23:59:59' GROUP BY transaction_day ORDER BY b.date_end ASC");
$get_transactions_days->execute(array('user_id' => $user__id));

if ($get_transactions_days->rowCount() > 0) {
    while ($transactions_days = $get_transactions_days->fetch()) {

        if (isset($_POST['tipo_date']) && $_POST['tipo_date'] == 'tomorrow'){
            if($transactions_days['transaction_day'] == date('Y-m-d')){
                continue;
            }
        }

        $html_response .= '<div class="card accordion__item">';
        $html_response .= '<div class="card-header" data-toggle="collapse" data-target="#future-releases-'. date('Y-m-d', strtotime($transactions_days['transaction_day'])) .'" aria-expanded="false">';
        $html_response .= '<div class="col-8 d-flex">';
        $html_response .= '<h6>'. date_format(date_create($transactions_days['transaction_day']), 'd/m/y') .'&nbsp; </h6><small> &nbsp;'. $transactions_days['transaction_day_quant'] . ($transactions_days['transaction_day_quant'] == 1 ? ' registro' : ' registros') .'</small>';
        $html_response .= '</div>';
        $html_response .= '<div class="col-3 d-flex justify-content-end">Saldo&nbsp;&nbsp; <b>R$'. number_format($transactions_days['transaction_day_value'], 2, ',', '.') .'</b> </div>';
        $html_response .= '<div class="col-1 d-flex justify-content-end">';
        $html_response .= '<a aria-expanded="false"><i style="color: #777777" class="fas fa-angle-down"></i></a>';
        $html_response .= '</div>';
        $html_response .= '</div>';
        $html_response .= '<div id="future-releases-'. date('Y-m-d', strtotime($transactions_days['transaction_day'])) .'" class="card-bodyaccordion__body collapse" data-parent="#historic-transaction-accordion">';
        $html_response .= '<table class="table movement-history">';
        $html_response .= '<thead>';
        $html_response .= '<tr>';
        $html_response .= '<th style="text-align: center;">Agendamento</th>';
        $html_response .= '<th style="text-align: center;">Conclusão</th>';
        $html_response .= '<!-- <th style="text-align: center;">Reembolso</th> -->';
        $html_response .= '<th style="text-align: center;">Comissão</th>';
        $html_response .= '<!-- <th style="text-align: center;">Taxa</th>';
        $html_response .= '<th style="text-align: center;">Líquido</th>';
        $html_response .= '<th style="text-align: center;">Status</th> -->';
        $html_response .= '<th style="text-align: center;">Liberação</th>';
        $html_response .= '<th style="text-align: center;">Pedido</th>';
        $html_response .= '</tr>';
        $html_response .= '</thead>';
        $html_response .= '<tbody>';
                    
        $date_filter = $transactions_days['transaction_day'];
        $date_filter_start  = $date_filter . ' 00:00:00';
        $date_filter_end    = $date_filter . ' 23:59:59';

        $get_transactions_list = $conn->prepare('SELECT t.*, transaction_type_name as type, transaction_status_name as status FROM transactions t INNER JOIN transactions_type ON transaction_type_id = type INNER JOIN transaction_status ON transaction_status_id = status WHERE user_id = :user__id AND date_end between :date_filter_start AND :date_filter_end AND date_end > now() ORDER BY date_end ASC');
        $get_transactions_list->execute(array('user__id' => $user__id, 'date_filter_start' => $date_filter_start, 'date_filter_end' => $date_filter_end));
        while ($transactions_list = $get_transactions_list->fetch()) {

            if ($transactions_list['type'] == "Saque") {
                $signal = "-";
                $color = "#ff2929";
            } else if ($transactions_list['type'] == "Antecipação") {
                $signal = "+";
                $color = "#2bc155";
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
                $signal = "-";
                $color = "#ff2929";
            } else if ($transactions_list['type'] == "Frustrado") {
                $signal = "-";
                $color = "#ff2929";
            } else if ($transactions_list['type'] == "Pedido") {
                $signal = "+";
                $color = "#2bc155";
            } else if ($transactions_list['type'] == "Manual") {
                $description = "Alteração Manual";
                $signal = $transactions_list['billing_value_full'] < 0 ? "" : '+';
                $color = "#ff2929";
            } else {
                $description = "";
                $signal = "-";
                $color = "#2bc155";
            }

            if ($transactions_list['bank_proof'] == NULL) {
                $status = "Pendente";
            } else {
                $status = "Liberado";
            } 


            $html_response .= '<tr>';
                
            $html_response .= '<td style="text-align: center;">';
                    
                    if ($transactions_list['type'] == 'Pedido') {
                        $html_response .= "<a class='btn btn-link text-primary' href='" . SERVER_URI . '/meu-pedido/' . $transactions_list['order_number'] . "' target='_blank'>Pedido</a>";
                    } else {
                        $html_response .= $transactions_list['type'];
                    }
                    
            $html_response .= '</td>';
            $html_response .= '<td class="fs-12 text-center" data-order="'. date_format(date_create($transactions_list['date_start']), 'YmdHis') .'">';
            $html_response .= date_format(date_create($transactions_list['date_start']), 'd/m/y H:i');
            $html_response .= '<td style="text-align: center; color: '. $color .'; font-weight: bold;">';
            $html_response .= "R$ " . number_format($transactions_list['value_brute'], 2, ",", ".");
            $html_response .= '</td>';
            $html_response .= '<td class="fs-12 text-center" data-order="'. date_format(date_create($transactions_list['date_end']), 'YmdHis') .' ">';
            $html_response .= date_format(date_create($transactions_list['date_end']), 'd/m/y H:i');
            $html_response .= '</td>';
            $html_response .= '<td style="text-align: center;">';
            $html_response .= '<a title="Ver pedido" href="'. SERVER_URI . "/meu-pedido/" . $transactions_list['order_number'] .' " target="_blank">';
            $html_response .= '<i class="fa fa-eye"></i>';
            $html_response .= '</a>';
            $html_response .= '</td>';
            $html_response .= '</tr>';
        }

        $html_response .= '</tbody>';
        $html_response .= '</table>';
        $html_response .= '</div>';
        $html_response .= '</div>';
    }
} 

if(empty(str_replace(' ', '', $html_response))) {
    $html_response .= '<div class="alert alert-warning fade mb-3 show solid">
        <i class="fa fa-info-circle" aria-hidden="true"></i><strong> Sem transações de acordo com seus filtros para mostrar na listagem!
    </div>';
}


$feedback = array('status' => 1, 'html_response' => $html_response);
echo json_encode($feedback);
exit;
