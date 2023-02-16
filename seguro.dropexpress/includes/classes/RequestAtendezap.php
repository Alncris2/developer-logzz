<?php

$keyAtendezap = "b31b46cd-44a7-41fd-954c-e0a66bfe6d96";
$baseUrlAtendezap = "https://backend.botconversa.com.br/api/v1/webhook/";
$baseUrlAtendezap2 = "https://backend.botconversa.com.br/api/v1/";

/**
 * Função de Cadastro novos usuários Atendezap
 *
 * @param string $name
 * @param string $phone
 * @return bool
 */
function registerAtendezap($name, $phone) 
{
    global $keyAtendezap;
    global $baseUrlAtendezap;
    $name = explode(' ', $name);
    $dataJson = array();
    $dataJson['first_name'] = $name[0];
    $dataJson['last_name'] = isset($name[1]) && !empty($name[1]) ? $name[1] : '';
    $dataJson['phone'] = preg_replace("/[^0-9]/", "", $phone);
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $baseUrlAtendezap . "subscriber/",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($dataJson),
        CURLOPT_HTTPHEADER => [
            "API-KEY: $keyAtendezap",
            "Content-Type: application/json"
        ],
    ]);
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
}

/**
 * Recuperado usuário cadastrado
 *
 * @param string $phone Numero de telefone formatado. Ex:+5534999999299
 * @return array|bool Retorno caso exista um array, se não false
 */
function checkIfClientExistAtendeZap($phone)
{
    global $keyAtendezap;
    global $baseUrlAtendezap;
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $baseUrlAtendezap . "subscriber/$phone",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_FOLLOWLOCATION => TRUE,
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => [
            "API-KEY: $keyAtendezap",
            "Content-Type: application/json"
        ],
    ]);
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    $response = curl_getinfo($curl, CURLINFO_HTTP_CODE) == 404 ? false : json_decode($response, true);
    return $response;
}

/**
 * Setar campos customizaveis
 *
 * @param string $subscriber_id Identificação interna do inscrito na Atendezap
 * @param string $field_id Identificação interna de campos personalizado da Atendezap
 * @param string $value_field Valor a ser definido para o campo
 * @return void
 */
function setCustomFieldAtendezap($subscriber_id, $field_id, $value_field)
{
    global $keyAtendezap;
    global $baseUrlAtendezap;
    $dataJson = array("value" => $value_field);
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $baseUrlAtendezap . "subscriber/$subscriber_id/custom_fields/$field_id/",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($dataJson),
        CURLOPT_HTTPHEADER => [
            "API-KEY: $keyAtendezap",
            "Content-Type: application/json"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
}

/**
 * Envio de fluxo para inscrito
 *
 * @param int $flow_id Identificação interna do fluxo na atendezap
 * @param int $subscriber_id Identificação interna do inscrito na Atendezap
 * @return bool
 */
function sendFlowsAtendezap($flow_id, $subscriber_id)
{
    global $keyAtendezap;
    global $baseUrlAtendezap;
    $dataJson = array("flow" => $flow_id);
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $baseUrlAtendezap . "subscriber/$subscriber_id/send_flow/",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($dataJson),
        CURLOPT_HTTPHEADER => [
            "API-KEY: $keyAtendezap",
            "Content-Type: application/json"
        ],
    ]);
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
}


function sendWebhookStatus($data)
{
    global $baseUrlAtendezap2;
    $dataJson = array("nome_completo" => $data['client_name'], "telefone" => $data['client_number'], "status_pedido" => $data['status_string'], "mensagem_status" => $data['order_status_description'], "nome_produto" => $data['product_name'], "qtd_pedido" => $data['quantity'], "codigo_produto" => $data['order_number']);
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $baseUrlAtendezap2 . "webhooks-automation/catch/22323/nJlvV7OqnIMD/",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($dataJson),
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json"
        ],
    ]);
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
}


function sendWebhookNovosUsuarios($data)
{
    global $baseUrlAtendezap2;
    $dataJson = array("nome_completo" => $data['full_name'], "telefone" =>  '+55' . preg_replace("/[^0-9]/", "", $data['user_phone']), "email" => $data['email'], "data_criacao" => date('d/m/Y', strtotime($data['created_at'])));
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $baseUrlAtendezap2 . "webhooks-automation/catch/22323/87M1lbcIAzM2/",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($dataJson),
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json"
        ],
    ]);
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
}

function updateUserOnStatus2($order_id)
{
    global $conn;
    // Mensagem WhatsApp atualizando cliente sobre status do pedido 
    $sqlOrdersSendWhats = "SELECT order_id, client_number, order_number, order_status_description, client_name, product_name, CASE WHEN orders.platform <> null OR orders.platform != '' THEN orders.order_quantity ELSE s.sale_quantity END AS quantity, order_status FROM orders INNER JOIN sales s ON orders.sale_id = s.sale_id WHERE order_id = :order_id";
    $stmtOrdersSendWhats = $conn->prepare($sqlOrdersSendWhats);
    $stmtOrdersSendWhats->execute(['order_id' => $order_id]);
    $dataOrdersSendWhats = $stmtOrdersSendWhats->fetch(\PDO::FETCH_ASSOC);
    $dataOrdersSendWhats['client_number'] = '+55' . preg_replace("/[^0-9]/", "", $dataOrdersSendWhats['client_number']); //$dataOrdersSendWhats['client_number']
    $status_string = getOrderStatusString($dataOrdersSendWhats['order_status']);
    $dataOrdersSendWhats['order_status_description'] = !empty($dataOrdersSendWhats['order_status_description']) && $dataOrdersSendWhats['order_status_description'] != NULL ? $dataOrdersSendWhats['order_status_description'] : "Motivo não informado!";
    $dataOrdersSendWhats['status_string'] = $status_string;
    sendWebhookStatus($dataOrdersSendWhats);
}

function updateUserOnStatus3($order_id)
{
    global $conn;
    // Mensagem WhatsApp atualizando cliente sobre status do pedido 
    $sqlOrdersSendWhats = "SELECT order_id, client_number, order_number, order_status_description, client_name, product_name, CASE WHEN orders.platform <> null OR orders.platform != '' THEN orders.order_quantity ELSE s.sale_quantity END AS quantity, order_status, order_deadline FROM orders INNER JOIN sales s ON orders.sale_id = s.sale_id WHERE order_id = :order_id";
    $stmtOrdersSendWhats = $conn->prepare($sqlOrdersSendWhats);
    $stmtOrdersSendWhats->execute(['order_id' => $order_id]); 
    $dataOrdersSendWhats = $stmtOrdersSendWhats->fetch(\PDO::FETCH_ASSOC);
    $dataOrdersSendWhats['client_number'] = '+55' . preg_replace("/[^0-9]/", "", $dataOrdersSendWhats['client_number']); //$dataOrdersSendWhats['client_number']
    $status_string = getOrderStatusString($dataOrdersSendWhats['order_status']);
    $dataOrdersSendWhats['order_status_description'] = !empty($dataOrdersSendWhats['order_status_description']) && $dataOrdersSendWhats['order_status_description'] != NULL ? $dataOrdersSendWhats['order_status_description'] : "Motivo não informado!";
    $dataOrdersSendWhats['status_string'] = $status_string;
    sendWebhookConfirma($dataOrdersSendWhats);
}


function sendWebhookReagendarStatus($data)
{
    global $baseUrlAtendezap2;
    $dataJson = array("nome_completo" => $data['client_name'], "telefone" => $data['client_number'], "nome_produto" => $data['product_name'], "qtd_pedido" => $data['quantity'], "codigo_produto" => $data['order_number'], "order_id" => $data['order_id'], "data_reagendamento" => date('d/m/Y', strtotime($data[''])), "data_entrega" => date('d/m/Y', strtotime($data['order_deadline'])));
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $baseUrlAtendezap2 . "webhooks-automation/catch/22323/bJPJ3TQtnILe/",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($dataJson),
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json"
        ],
    ]);
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
}

function getDataByReagendamento($order_id)
{
    global $conn;
    $sqlDataReag = "SELECT client_number, order_number, order_status_description, client_name, orders.order_delivery_date, product_name, CASE WHEN orders.platform <> null OR orders.platform != '' THEN orders.order_quantity ELSE s.sale_quantity END AS quantity, order_status, orders.order_id , order_deadline FROM orders INNER JOIN sales s ON orders.sale_id = s.sale_id WHERE order_id = :order_id";
    $stmtOrdersSendWhats = $conn->prepare($sqlDataReag);
    $stmtOrdersSendWhats->execute(['order_id' => $order_id]);
    $dataOrdersSendWhats = $stmtOrdersSendWhats->fetch(\PDO::FETCH_ASSOC);
    $dataOrdersSendWhats['client_number'] = '+55' . preg_replace("/[^0-9]/", "", $dataOrdersSendWhats['client_number']); //$dataOrdersSendWhats['client_number']
    $status_string = getOrderStatusString($dataOrdersSendWhats['order_status']);
    $dataOrdersSendWhats['order_status_description'] = !empty($dataOrdersSendWhats['order_status_description']) && $dataOrdersSendWhats['order_status_description'] != NULL ? $dataOrdersSendWhats['order_status_description'] : "Motivo não informado!";
    $dataOrdersSendWhats['status_string'] = $status_string;
    sendWebhookReagendarStatus($dataOrdersSendWhats);
}



/**
 * Retorna o status em formato de string
 *
 * @param [type] $order_status
 * @return void
 */
function getOrderStatusString($order_status)
{
    switch ($order_status) {
        case 1:
            $status_string = "Reagendado"; // redireciona para pagina de reagendar 
            break;
        case 2:
            $status_string = "Atrasado";
            break;
        case 3:
            $status_string = "Completo"; // redireciona para completar
            break;
        case 4:
            $status_string = "Frustrado"; // redireciona para frustar 
            break;
        case 5:
            $status_string = "Cancelado";
            break;
        case 6: // CENTRO DE DISTRIBUIÇÃO
            $status_string = "A Enviar";
            break;
        case 7: // CENTRO DE DISTRIBUIÇÃO
            $status_string = "Enviando";
            break;
        case 8: // CENTRO DE DISTRIBUIÇÃO
            $status_string = "Enviado";
            break;
        default:
            $status_string = "Agendado";
            break;
    }
    return $status_string;
}
function dayOfWeek($day){
    switch ($day) {
        case 1:
            return "segunda-feira";
        case 2:
            return "terça-feira";
        case 3:
            return "quarta-feira";
        case 4:
            return "quinta-feira";
        case 5:
            return "sexta-feira";
        case 6:
            return "sábado";
        case 7:
            return "domingo";
    }
}

function sendWebhookConfirma($data)
{
    $dataWeek = dayOfWeek(date('w', strtotime($data['order_deadline'])));
    global $baseUrlAtendezap2;
    $dataJson = array("nome_completo" => $data['client_name'], "telefone" => $data['client_number'], "status_pedido" => $data['status_string'], "mensagem_status" => $data['order_status_description'], "nome_produto" => $data['product_name'], "qtd_pedido" => $data['quantity'], "codigo_produto" => $data['order_number'], "dia_pedido" => $dataWeek, "data_entrega" => date('d/m/Y', strtotime($data['order_deadline'])));
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $baseUrlAtendezap2 . "webhooks-automation/catch/22323/gLX4grYksCtw/",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($dataJson),
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json"
        ],
    ]);
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
}

function sendWebhookStatusAuto($order_id) 
{
    global $conn;
    // Mensagem WhatsApp atualizando cliente sobre status do pedido 
    $sqlOrdersSendWhats = "SELECT order_id, client_number, order_number, order_status_description, client_name, product_name, CASE WHEN orders.platform <> null OR orders.platform != '' THEN orders.order_quantity ELSE s.sale_quantity END AS quantity, order_status, order_status_description, order_deadline FROM orders INNER JOIN sales s ON orders.sale_id = s.sale_id WHERE order_id = :order_id";
    $stmtOrdersSendWhats = $conn->prepare($sqlOrdersSendWhats);
    $stmtOrdersSendWhats->execute(['order_id' => $order_id]);
    $dataOrdersSendWhats = $stmtOrdersSendWhats->fetch(\PDO::FETCH_ASSOC);
    $dataOrdersSendWhats['client_number'] = '+55' . preg_replace("/[^0-9]/", "", $dataOrdersSendWhats['client_number']); //$dataOrdersSendWhats['client_number']
    $status_string = getOrderStatusString($dataOrdersSendWhats['order_status']);
    $dataOrdersSendWhats['order_status_description'] = !empty($dataOrdersSendWhats['order_status_description']) && $dataOrdersSendWhats['order_status_description'] != NULL ? $dataOrdersSendWhats['order_status_description'] : "Motivo não informado!";
    $dataOrdersSendWhats['status_string'] = $status_string;

    $get_integration = $conn->prepare('SELECT * FROM atendezap_integration WHERE az_level LIKE "owner" AND az_status LIKE :az_status AND az_active = 1 LIMIT 1');
    $get_integration->execute(array('az_status' => $dataOrdersSendWhats['order_status'])); 

    if($integration = $get_integration->fetch(\PDO::FETCH_ASSOC)){        
        $dataWeek = dayOfWeek(date('w', strtotime($dataOrdersSendWhats['order_deadline'])));
        $dataJson = array(
            "nome_completo"         => $dataOrdersSendWhats['client_name'], 
            "telefone"              => $dataOrdersSendWhats['client_number'], 
            "nome_produto"          => $dataOrdersSendWhats['product_name'], 
            "qtd_pedido"            => $dataOrdersSendWhats['quantity'], 
            "codigo_produto"        => $dataOrdersSendWhats['order_number'], 
            "order_id"              => $dataOrdersSendWhats['order_id'], 
            "status_pedido"         => $dataOrdersSendWhats['status_string'],
            "mensagem_status"       => $dataOrdersSendWhats['order_status_description'],             
            "dia_pedido"            => $dataWeek, 
            "data_entrega"          => date('d/m/Y', strtotime($dataOrdersSendWhats['order_deadline'])),
        );
        
        $az_key = $integration['az_key'];  

        $curl = curl_init();
        curl_setopt_array($curl, [ 
            CURLOPT_URL => $integration['az_webhook'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($dataJson),
            CURLOPT_HTTPHEADER => [ 
                "API-KEY: $az_key", 
                "Content-Type: application/json"
            ],
        ]);  
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);         
        return true;
    }    
    
    return false;
}


// /**
//  * Informa o cliente os status do pedido!
//  *
//  * @param integer $order_id
//  * @return void
//  */
// function updateUserOnStatus($order_id, $flow_id = 831281)
// {
//   global $conn;

//   // Mensagem WhatsApp atualizando cliente sobre status do pedido
//   $sqlOrdersSendWhats = "SELECT client_number, order_number, order_status_description, client_name, product_name, order_quantity, order_status FROM orders WHERE order_id = :order_id";
//   $stmtOrdersSendWhats = $conn->prepare($sqlOrdersSendWhats);
//   $stmtOrdersSendWhats->execute(['order_id' => $order_id]);
//   $dataOrdersSendWhats = $stmtOrdersSendWhats->fetch(\PDO::FETCH_ASSOC);
//   // $dataOrdersSendWhats['client_number'] = '+55' . preg_replace("/[^0-9]/", "",$dataOrdersSendWhats['client_number']);
//   $dataOrdersSendWhats['client_number'] = "+553498005744";
//   $searchSubscriber = checkIfClientExistAtendeZap($dataOrdersSendWhats['client_number']);
//   if ($searchSubscriber == false) {
//     registerAtendezap($dataOrdersSendWhats['client_name'], $dataOrdersSendWhats['client_number']);
//     $searchSubscriber = checkIfClientExistAtendeZap($dataOrdersSendWhats['client_number']);
//   }
//   $status_string = getOrderStatusString($dataOrdersSendWhats['order_status']);
//   $dataOrdersSendWhats['order_status_description'] = !empty($dataOrdersSendWhats['order_status_description']) && $dataOrdersSendWhats['order_status_description'] != NULL ? $dataOrdersSendWhats['order_status_description'] : "Motivo não informado!";
//   setCustomFieldAtendezap($searchSubscriber['id'], 539600, $dataOrdersSendWhats['order_number']); // Seta número do pedido
//   setCustomFieldAtendezap($searchSubscriber['id'], 539599, $dataOrdersSendWhats['order_status_description']); // Seta descrição status
//   setCustomFieldAtendezap($searchSubscriber['id'], 539598, $status_string); // Seta campo status
//   setCustomFieldAtendezap($searchSubscriber['id'], 539606, $dataOrdersSendWhats['product_name']); // Seta nome do produto
//   setCustomFieldAtendezap($searchSubscriber['id'], 539603, $dataOrdersSendWhats['order_quantity']); // Seta quantidade produto
//   sendFlowsAtendezap($flow_id, $searchSubscriber['id']);
// }
