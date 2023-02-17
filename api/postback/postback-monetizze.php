<?php

require dirname(__FILE__) . "/../../includes/config.php";


$request = $_POST;

# Verifica pelo URL a qual integraçãopertence a requisição.
$url = "https://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

# SALVA OS DADOS EM UM ARQUIVO DE LOG
// $fp = file_put_contents( 'pre-request-monetizze.log', $request['venda']['codigo']);


// VERIFICAR SE O POSTBACK FOI ENVIADO PELA MONETIZZE

# PEGAR DADOS DA INTEGRAÇÃO 
$get_integration = $conn->prepare('SELECT * FROM integrations WHERE integration_url = :integration_url AND integration_status = :integration_status');
$get_integration->execute(array('integration_url' => $url, 'integration_status' => "active"));
$integration = $get_integration->fetch();


if ($get_integration->rowCount() > 0) {

    # CHAVE UNICA PARA VERIFICAR SE O POST FOI ENVIADO PELA MONETIZZE 
    $chaveUnica = $request['chave_unica'];
    if ($chaveUnica  != $integration['integration_keys']) {
        exit;
    }

    # VERIFICA SE JÁ FOI INTEGRADO COM A TINY
    if ($integration['status'] == 0) {
        echo "Ainda não foi integrado!";
        exit;
    }
    // PEGAR DADOS DA INTEGRAÇÃO
    $get_integration_tiny = $conn->prepare('SELECT * FROM tiny_dispatches WHERE url_integration = :url');
    $get_integration_tiny->execute([':url' => $url]);
    $integration_tiny = $get_integration_tiny->fetch();

    // VERIFICAR SE EXISTE ESTOQUE DESSE PRODUTO NO CENTRO DE DISTRIBUIÇÃO 

    /**
     * MONTAGEM DO INVENTORY_META PARA CONSULTA DESSE PRODUTO NO BANCO DE DADOS
     * O VALOR "1" SE REFERENCIA AO CENTRO DE DISTRIBUIÇÃO DE SÃO PAULO  
     */

    $integration_user_id = $integration['integration_user_id'];
    $integration_product_id = $integration['integration_product_id'];
    $meta_inventory = $integration_user_id . "-" . $integration_product_id . "-" . "1";

    $query = "SELECT * FROM inventories WHERE inventory_meta = :inventory_meta AND ship_locale = 1";
    $stmt_inventory = $conn->prepare($query);
    $stmt_inventory->execute([
        'inventory_meta' => $meta_inventory
    ]);

    $total_amount = $request['venda']['quantidade']; 

    if($stmt_inventory->rowCount() > 0){ 
        $qtd_product_in_inventory = $stmt_inventory->fetch(\PDO::FETCH_ASSOC)['inventory_quantity'];   
        $qtd_after_discount_from_stock = ($qtd_product_in_inventory - $total_amount);
    }else{
        $qtd_after_discount_from_stock = -1;
    }

    if($qtd_after_discount_from_stock < 0){
        // ESTOQUE INDISPONÍVEL        
        $fp = file_put_contents('no-stock.log', "O usuário de id $integration_user_id não possui estoque para o produto de id $integration_product_id");
    }

    // VERIFICA SE UF DO PRODUTO QUE ESTÁ VINDO PELA BRAIP ESTÁ LISTADA ENTRE AS UF'S PERMITIDAS
    $ufList = explode(',', $integration_tiny['ufs']);
    if (in_array($request['comprador']['estado'], $ufList)) {

        $token = $integration_tiny['token'];

        $integration_user_id = $integration['integration_user_id'];
        $integration_product_id = $integration['integration_product_id'];

        // NENHUM USUÁRIO ENCONTRADO
        if ($integration_user_id == 0) exit;

        // PEGAR TAXA DE ENTREGA E TAXA SOBRE O VALOR DO PRODUTO DE ACORDO COM O PLANO
        $stmt = $conn->prepare("SELECT user_plan_tax,user_plan_shipping_tax FROM subscriptions WHERE user__id = :user_id");
        $stmt->execute([':user_id' => $integration_user_id]);

        $user_data = $stmt->fetch();

        $tax = $user_data['user_plan_tax'] * 100; // Taxa 
        $shipping = number_format($user_data['user_plan_shipping_tax'], 2, '.', "");

        // VERIFICA SE JÁ POSSUI UMA VENDA CRIADA 
        $get_sale_id = $conn->prepare('SELECT sale_id FROM sales WHERE product_id = :integration_product_id AND sale_mirror_key = :sale_mirror_key');
        $get_sale_id->execute(array('integration_product_id' => $integration_product_id, 'sale_mirror_key' => $request['produto']['chave']));

        if ($get_sale_id->rowCount() != 0) {
            $sale_id = $get_sale_id->fetch();
            $sale_id = $sale_id['sale_id'];

            echo "Venda já espelhada!";
        } else {

            // CRIAR NOVA VENDA
            $stmt = $conn->prepare('INSERT INTO sales (product_id,sale_product_name,sale_name,sale_date_start, sale_date_end, sale_quantity, sale_price, sale_status,sale_tax,product_shipping_tax,product_price,sale_cost,sale_mirror_key) VALUES (:product_id,:sale_product_name,:sale_name,:sale_date_start, :sale_date_end,:sale_quantity,:sale_price,:sale_status,:sale_tax,:product_shipping_tax,:product_price,:sale_cost,:sale_mirror_key)');

            // DADOS PARA INSERÇÃO
            $product_id = $integration_product_id;
            $sale_name = $request['produto']['nome'];
            $sale_date_start = $request['venda']['dataInicio'];
            $sale_date_end = $request['venda']['dataFinalizada'];
            $sale_quantity = $request['venda']['quantidade'];
            $sale_price = $request['venda']['valorRecebido'];
            $sale_status = 1;
            $sale_tax = ($sale_price / 100) * $tax;
            $product_shipping_tax = number_format($shipping, 2, '.', ',');;
            $product_price = $sale_price / $sale_quantity;
            $sale_cost = $request['venda']['valorRecebido'];
            $sale_mirror_key = $request['venda']['codigo'];
            $product_name = $request['produto']['nome'];

            try {
                $stmt->execute(array('product_id' => $product_id, 'sale_product_name' => $product_name, 'sale_name' => $sale_name, 'sale_date_start' => $sale_date_start, 'sale_date_end' => $sale_date_end, 'sale_quantity' => $sale_quantity, 'sale_price' => $sale_price, 'sale_status' => $sale_status, 'sale_tax' => $sale_tax, 'product_shipping_tax' => $product_shipping_tax, 'product_price' => $sale_price, 'sale_cost' => $sale_cost, 'sale_mirror_key' => $sale_mirror_key));
                echo "venda criada";
            } catch (PDOException $e) {
                $error = 'ERROR: ' . $e->getMessage();
                echo $error;
            }
        }

        // PEGAR DADOS DA VENDA CRIADA 
        $get_sale_id = $conn->prepare('SELECT sale_id FROM sales WHERE product_id = :integration_product_id AND sale_mirror_key = :sale_mirror_key');
        $get_sale_id->execute(array('integration_product_id' => $integration_product_id, 'sale_mirror_key' => $request['venda']['codigo']));


        if ($get_sale_id->rowCount() != 0) {
            $sale_id = $get_sale_id->fetch();
            $sale_id = $sale_id['sale_id'];
        }

        // ESPELHAR PEDIDO
        $order_number = "MONETIZZE" . strtoupper($request['venda']['codigo']);

        $check_order_duplicate = $conn->prepare('SELECT * FROM orders WHERE order_number = :order_number');
        $check_order_duplicate->execute(array('order_number' => $order_number));

        if ($check_order_duplicate->rowCount() != 0) {
            $sale_id = $check_order_duplicate->fetch();
            $sale_id = $sale_id['sale_id'];
            echo "Pedido já espelhado";
        } else {

            $stmt = $conn->prepare('INSERT INTO orders(order_id, user__id, sale_id, product_id, product_name, order_date, order_deadline, order_status, order_delivery_date, client_name, client_address, client_number, order_delivery_time, order_number, delivery_period, use_coupon, order_final_price, order_liquid_value, order_commission_date, platform, order_trans_key, order_quantity) VALUES (:order_id, :user__id, :sale_id, :product_id, :product_name, :order_date, :order_deadline, :order_status, :order_delivery_date, :client_name, :client_address, :client_number, :order_delivery_time, :order_number, :delivery_period, :use_coupon, :order_final_price, :order_liquid_value, :order_commission_date, :platform, :order_trans_key, order_quantity)');

            // DADOS PARA INSERÇÃO AO BANCO
            $order_trans_key = $request['venda']['codigo'];
            $order_id               = 0;
            $user__id               = $integration_user_id;
            $order_delivery_date    = $request['venda']['dataInicio'];
            $order_date             = $request['venda']['dataFinalizada'];
            $order_deadline         = $request['venda']['dataInicio'];
            $order_status           = 6;
            $order_delivery_date    = "2100-01-01 00:00:01";
            $name                   = '[M] ' . $request['comprador']['nome'];
            $platform               = 'Monetizze';

            $address  = $request['comprador']['endereco'] . ", nº " . $request['comprador']['numero'] . "<br>";
            $address .= "Bairro " . $request['comprador']['bairro'] . "<br>";

            if ($request['comprador']['complemento'] != null) {
                "Complemento: " . $address .= $request['comprador']['complemento'] . "<br>";
            }

            $address .= $request['comprador']['cidade'] . ", " . $request['comprador']['estado'] . "<br>";
            $address .= "CEP: " . $request['comprador']['cep'];
            $whats                  = $request['comprador']['telefone'];
            $delivery_period        = "default";
            $use_coupon             = 1;
            $order_final_price      = $request['venda']['valorRecebido'];
            $order_commission = 0;



            // pegar comissão do produtor
            foreach ($request['comissoes'] as $comission) {
                $order_commission = $comission['valor'];
            }
            $order_commission_date  = "2100-01-01 00:00:01";
            $product_name = $request['produto']['nome'];

            try {
                $stmt->execute(array('order_id' => $order_id, 'user__id' => $user__id, 'sale_id' => $sale_id, 'product_id' => $integration_product_id, 'product_name' => $product_name, 'order_date' => $order_date, 'order_deadline' => $order_deadline, 'order_status' => $order_status, 'order_delivery_date' => $order_delivery_date, 'client_name' => $name, 'client_address' => $address, 'client_number' => $whats, 'order_delivery_time' => $order_delivery_date, 'order_number' => $order_number, 'delivery_period' => $delivery_period, 'use_coupon' => $use_coupon, 'order_final_price' => $order_final_price, 'order_liquid_value' => $order_commission, 'order_commission_date' => $order_commission_date, 'platform' => $platform, 'order_trans_key' => $order_trans_key, 'order_quantity' => $request['venda']['quantidade']));
                $lastIdInsert = $conn->lastInsertId();
                echo "Pedido Espelhado!";
            } catch (PDOException $e) {
                $error = 'ERROR: ' . $e->getMessage();
                echo $error;
                exit;
            }

            //PEGAR DADOS DO PRODUTO CADASTRADO NO DROP EXPRESS
            $get_product_drop = $conn->prepare("SELECT type_packaging, product_weight FROM products WHERE status = 1 AND product_trash = 0 AND product_id = :product_id");
            $get_product_drop->execute(['product_id' => $integration_product_id]);
            $product = $get_product_drop->fetch();
            $product_weight = str_replace(",", ".", $product['product_weight']);

            if ($product['type_packaging'] == 1) {
                $length_package = 16;
                $width_package  = 11;
                $height_package = 6;
            }

            if ($product['type_packaging'] == 2) {
                $length_package = 24;
                $width_package  = 15;
                $height_package = 10;
            }

            if ($product['type_packaging'] == 3) {
                $length_package = 50;
                $width_package  = 33;
                $height_package = 20;
            }

            // VERIFICAR INADIMPÊNCIA DO USÚARIO 
            $query = "SELECT last_charge_ok FROM users WHERE user__id = :user__id";
            $stmt = $conn->prepare($query);
            $stmt->execute(['user__id' => $integration_user_id]);
            $last_charge_ok = $stmt->fetch()['last_charge_ok'];

            if ($last_charge_ok == "1") {

                if($qtd_after_discount_from_stock < 0){
                    // estoque indísponivel 
                    exit;
                }

                // ENVIAR PRODUTO PARA TINY 
                # DADOS DO PRODUTO DE ACORDO COM LAYOUT FORNECIDO PELA API TINY
                $array = array(
                    "produtos" => array(
                        [
                            "produto" => [
                                'sequencia' => $lastIdInsert,
                                'nome' => $request['produto']['nome'],
                                'codigo' => $request['venda']['codigo'],
                                'unidade' => 'UN',
                                'preco' => $request['venda']['valorRecebido'],
                                'origem' => 0,
                                'situacao' => 'A',
                                'tipo' => 'P',
                                'peso_liquido' => $product_weight,
                                'peso_bruto' => $product_weight,
                                'unidade_por_caixa' => '1',
                                'altura_embalagem' => $height_package,
                                'largura_embalagem' => $width_package,
                                'comprimento_embalagem' => $length_package,
                                'variacoes' => [
                                    'variacao' => [
                                        'mapeamentos' => [
                                            'mapeamento' => [
                                                'idEcommerce' => '10519',
                                                'skuMapeamento' => $lastIdInsert,
                                                'skuMapeamentoPai' => $lastIdInsert
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                        ],
                    )
                );
                
                // INICIO DA REQUISIÇÃO PARA ENVIAR O PRODUTO PARA TINY
                $pedido = array("produto" => json_encode($array));
                $params = array(
                    "token" => $token,
                    "formato" => "json"
                );


                $param = array_merge($params, $pedido);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://api.tiny.com.br/api2/produto.incluir.php");
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Content-Type: application/x-www-form-urlencoded",
                ));

                $result = curl_exec($ch);
                curl_reset($ch);
                curl_close($ch);

                print_r(json_decode($result));

                # DADOS DO PEDIDO DE ACORDO COM LAYOUT FORNECIDO PELA API TINY
                $requestArray = [

                    'pedido' => [
                        'numero_pedido_ecommerce' => $lastIdInsert,
                        'id_ecommerce' => '10519',
                        'data_pedido' => date('d/m/Y', strtotime($request['venda']['dataFinalizada'])),
                        'cliente' => [
                            'codigo' => uniqid('cod_'),
                            'nome' => $request['comprador']['nome'],
                            'cpf_cnpj' => $request['comprador']['cnpj_cpf'],
                            'cidade' => $request['comprador']['cidade'],
                            'endereco' => mb_convert_encoding($request['comprador']['endereco'], 'UTF-8', 'UTF-8'),
                            'numero' => mb_convert_encoding($request['comprador']['numero'], 'UTF-8', 'UTF-8'),
                            'complemento' => $request['comprador']['complemento'] !== "" ? $request['comprador']['complemento'] : "Não informado",
                            'bairro' => mb_convert_encoding($request['comprador']['bairro'], 'UTF-8', 'UTF-8'),
                            'cep' => $request['comprador']['cep'],
                            'uf' => $request['comprador']['estado'],
                            'pais' => $request['comprador']['pais']
                        ],
                        'itens' => [
                            (object) array(
                                'item' => (object) array(
                                    "codigo" => str_pad(0, 4, '0', STR_PAD_LEFT),
                                    "descricao" => $request['produto']['nome'],
                                    "unidade" => "UN",
                                    "quantidade" =>  $request['venda']['quantidade'],
                                    "valor_unitario" => number_format(($request['venda']['valorRecebido']) / $request['venda']['quantidade'], 2)
                                ),
                            ),
                        ],
                    ],
                ];


                // INICIO DA REQUISIÇÃO PARA ENVIAR O PRODUTO PARA TINY
                $url = "https://tiny.com.br/api-docs/api2-pedidos-incluir";
                $pedido = array("pedido" => json_encode($requestArray));
                $params = array(
                    "token" => $token,
                    "formato" => "json"
                );

                $param = array_merge($params, $pedido);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://api.tiny.com.br/api2/pedido.incluir.php");
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Content-Type: application/x-www-form-urlencoded",
                ));

                $result = curl_exec($ch);
                curl_reset($ch);
                curl_close($ch);

                print_r(json_decode($result));

                // TROCAR STATUS DE Á ENVIAR PARA ENVIANDO
                $stmt = $conn->prepare("UPDATE orders SET order_status = 7 WHERE order_id = $lastIdInsert");
                $stmt->execute();
            }
        }
    }
}
