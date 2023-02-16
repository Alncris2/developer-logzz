<?php 
    require_once(dirname(__FILE__) . '/../includes/config.php');
    session_name(SESSION_NAME);
    session_start();
    
    $ids = $_POST['ids'];
    
    $result = explode(",", $ids);

    $str = "'" . implode("','", $result) . "'";

    
    // var_dump($result);
    $user_plan = isset($_SESSION['UserPlan']) ? $_SESSION['UserPlan'] : $_POST['UserPlan'];
    // PEGAR TOKEN DA INTEGRAÇÃO 

    if($user_plan == 5){
        $query = $conn->prepare("SELECT token FROM orders AS o INNER JOIN integrations AS i ON i.integration_product_id = o.product_id AND i.integration_user_id = o.user__id INNER JOIN tiny_dispatches AS t ON t.url_integration = i.integration_url WHERE o.order_tiny_id IN (".$str.")");
        $query->execute();
        
        $all_tokens = $query->fetchAll(\PDO::FETCH_ASSOC);
    }else{
        
        $user__id = isset($_SESSION['UserID']) ? $_SESSION['UserID'] : $_POST['UserID'];

        $query = $conn->prepare("SELECT token FROM orders AS o INNER JOIN integrations AS i ON i.integration_product_id = o.product_id AND i.integration_user_id = o.user__id INNER JOIN tiny_dispatches AS t ON t.url_integration = i.integration_url WHERE o.user__id = :user__id AND o.order_tiny_id IN (".$str.")");
        $query->execute(['user__id' => $user__id]);
        
        $all_tokens = $query->fetchAll(\PDO::FETCH_ASSOC);
    }



    $tokens_diff = [];
    foreach ($all_tokens as $key) {
        if(!in_array($key['token'], $tokens_diff)){
            array_push($tokens_diff, $key['token']);
        }
    }
    $value_freight = 0;
    $errors = 0;
    $per_id = [];

    foreach($tokens_diff as $token){ // CASO O USUÁRIO TENHA CADASTRADO MAIS DE UMA CONTA TINY, ELE TERÁ 2 TOKENS DIFERENTES
        foreach($result as $id){
            if($id !== ""){
                $params = array(
                    "token" => $token,
                    "formato" => "json",
                    "id" => $id
                );
    
                try {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "https://api.tiny.com.br/api2/pedido.obter.php");
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        "Content-Type: application/x-www-form-urlencoded",
                    ));
                
                    $result = curl_exec($ch);
                    curl_reset($ch);
                    curl_close($ch);
        
                    $result = json_decode($result);
    
                    if($result->retorno->status == "OK"){
                        if($result->retorno->status_processamento == 3){ // PEDIDO ENCONTRADO
                            $value_freight += $result->retorno->pedido->valor_frete;
                        }else{
                            $errors++;
                        }
    
                        array_push($per_id, [
                            'id' => $result->retorno->pedido->ecommerce->numeroPedidoEcommerce,
                            'value' => $result->retorno->pedido->valor_frete
                        ]);
        
                    }else{
                        $errors++;
                    }
    
    
                } catch (\Throwable $th) {
                    die($th->getMessage());
                }
            }else{
                $errors++;
            }
        }
    }


    echo json_encode(['freight' => $value_freight, 'errors' => $errors, 'per_id' => $per_id]);
