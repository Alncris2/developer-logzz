<?php

require dirname(__FILE__) . "/../../includes/config.php";
include "functions.php";

# PEGA OS DADOS VINDO PELA BRAIP
$data = json_decode(file_get_contents('php://input'), true);

# SALVA OS DADOS EM UM ARQUIVO DE LOG
$fp = file_put_contents('pre-request.log', file_get_contents('php://input'));

# Verifica se o Status da Solicitação é de VENDA_COMPLETA OU STATUS ALTERADO
if ($data['type'] == "VENDA_COMPLETA" || $data['type'] == "STATUS_ALTERADO") {

    # VERIFICA SE O PAGAMENTO FOI APROVADO
    if ($data['trans_status'] == 'Pagamento Aprovado') {

        # VERIFICA PELA URL A QUAL INTEGRAÇÃO PERTENCE A REQUISIÇÃO.
        $url = "https://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

        $get_integration = $conn->prepare('SELECT integration_user_id, integration_product_id, status FROM integrations WHERE integration_url = :url');
        $get_integration->execute([
            ':url' => $url
        ]);

        $integration = $get_integration->fetch();

        // VERIFICA SE JÁ FOI INTEGRADO COM A TINY
        if ($integration['status'] == 0) {
            echo "Ainda não foi integrado!";
            exit;
        }

        // PEGAR DADOS DA INTEGRAÇÃO
        $get_integration_tiny = $conn->prepare('SELECT * FROM tiny_dispatches WHERE url_integration = :url');
        $get_integration_tiny->execute([':url' => $url]);
        $integration_tiny = $get_integration_tiny->fetch();

        // SEM O "55"
        $number_phone = explode(" ",$data['client_cel'])[1];


        // VERIFICA SE UF DO PRODUTO QUE ESTÁ VINDO PELA BRAIP ESTÁ LISTADA ENTRE AS UF'S PERMITIDAS
        $ufList = explode(',', $integration_tiny['ufs']);
        if (in_array($data['client_address_state'], $ufList)) {

            $token = $integration_tiny['token'];
            $integration_user_id = $integration['integration_user_id'];
            $integration_product_id = $integration['integration_product_id'];

            // NENHUM USUÁRIO ENCONTRADO
            if ($integration_user_id == 0) exit;

            
            // VERIFICAR SE EXISTE ESTOQUE DESSE PRODUTO NO CENTRO DE DISTRIBUIÇÃO 

            /**
             * MONTAGEM DO INVENTORY_META PARA CONSULTA DESSE PRODUTO NO BANCO DE DADOS
             * O VALOR "14" SE REFERENCIA AO CENTRO DE DISTRIBUIÇÃO DE SÃO PAULO  
             */

            $meta_inventory = $integration_user_id . "-" . $integration_product_id . "-" . "14";

            $query = "SELECT * FROM inventories WHERE inventory_meta = :inventory_meta";
            $stmt_inventory = $conn->prepare($query);
            $stmt_inventory->execute([
                'inventory_meta' => $meta_inventory
            ]);

            $qtd_product_in_inventory = $stmt_inventory->fetch(\PDO::FETCH_ASSOC)['inventory_quantity'];   
            $total_amount =  $data['plan_amount'];

            $qtd_after_discount_from_stock = ($qtd_product_in_inventory - $total_amount);

            if($qtd_after_discount_from_stock < 0){
                // ESTOQUE INDISPONÍVEL 
                echo "Estoque Indisponível!";
                exit;
            }
           
            // ATUALIZAR ESTOQUE DISPONÍVEL
            $query = "UPDATE inventories SET inventory_quantity = :inventory_quantity WHERE inventory_meta = :inventory_meta";
            $stmt = $conn->prepare($query);
            $stmt->execute([
                'inventory_quantity' => $qtd_after_discount_from_stock,
                'inventory_meta' => $meta_inventory
            ]);


            // PEGAR TAXA DE ENTREGA E TAXA SOBRE O VALOR DO PRODUTO DE ACORDO COM O PLANO
            $stmt = $conn->prepare("SELECT user_plan_tax,user_plan_shipping_tax FROM subscriptions WHERE user__id = :user_id");
            $stmt->execute([':user_id' => $integration_user_id]);
            $user_data = $stmt->fetch();

            $tax = $user_data['user_plan_tax'] * 100; // Taxa 
            $shipping = number_format($user_data['user_plan_shipping_tax'], 2, '.', "");


            // VERIFICA SE A CHAVE RECEBINA NA REQUISIÇÃO É DE FATO A CHAVE ÚNICA DA CONTA BRAIP DO ASSINANTE.
            $basic_authentication = $data['basic_authentication'];
            $verify_auth = $conn->prepare('SELECT COUNT(*) FROM integrations WHERE integration_user_id = :integration_user_id AND integration_keys = :integration_keys');
            $verify_auth->execute(array('integration_user_id' => $integration_user_id, 'integration_keys' => $basic_authentication));
            $verified_auth = $verify_auth->fetch();

            // CHAVE INVÁLIDA
            if ($verified_auth == 0) exit;

            // COM A CHAVE UNICA VERIFICADA COMEÇA A VERIFICAÇÃO DE DADOS DUPLICADOS E INSERÇÃO NO BANCO DE DADOS
            $sale_mirror_key = $data['plan_key'];

            // VERIFICA SE JÁ POSSUI UMA VENDA CRIADA 
            $get_sale_id = $conn->prepare('SELECT sale_id FROM sales WHERE product_id = :integration_product_id AND sale_mirror_key = :sale_mirror_key');
            $get_sale_id->execute(array('integration_product_id' => $integration_product_id, 'sale_mirror_key' => $sale_mirror_key));


            //PEGAR DADOS DO PRODUTO CADASTRADO NO DROP EXPRESS
            $get_product_drop = $conn->prepare("SELECT type_packaging, product_weight FROM products WHERE status = 1 AND product_trash = 0 AND product_id = :product_id");
            $get_product_drop->execute(['product_id' => $integration['integration_product_id']]);
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


            if ($get_sale_id->rowCount() != 0) {
                $sale_id = $get_sale_id->fetch();
                $sale_id = $sale_id['sale_id'];

                echo "Venda já espelhada!";
            } else {
                // CRIAR NOVA VENDA
                $stmt = $conn->prepare('INSERT INTO sales (product_id, sale_product_name, sale_name, sale_date_start, sale_date_end, sale_quantity, sale_price, sale_status, sale_url, sale_tax, product_shipping_tax, product_price, sale_cost, sale_mirror_key) VALUES (:product_id, :sale_product_name, :sale_name, :sale_date_start, :sale_date_end, :sale_quantity, :sale_price, :sale_status, :sale_url, :sale_tax, :product_shipping_tax, :product_price, :sale_cost, :sale_mirror_key)');


                // DADOS PARA INSERÇÃO
                $product_id = $integration_product_id;
                $sale_name = $data['plan_name'];
                $sale_date_start = date('Y-m-d H:m:s');
                $sale_date_end = date('Y-m-d H:m:s');
                $sale_quantity = $data['plan_amount'];
                $sale_price = $data['trans_value'] / 100;
                $sale_status = 1;
                $sale_url = $data['trans_payment_url'];
                $sale_tax = ($sale_price / 100) * $tax;
                $product_shipping_tax = number_format($shipping, 2, '.', ',');;
                $product_price = ($data['trans_value'] / 100) / $data['plan_amount'];
                $sale_cost = $data['trans_value'] / 100;
                $sale_mirror_key = $data['plan_key'];
                $product_name = $data['product_name'];

                try {
                    $stmt->execute(array('product_id' => $product_id, 'sale_product_name' => $product_name, 'sale_name' => $sale_name, 'sale_date_start' => $sale_date_start, 'sale_date_end' => $sale_date_end, 'sale_quantity' => $sale_quantity, 'sale_price' => $sale_price, 'sale_status' => $sale_status, 'sale_url' => $sale_url, 'sale_tax' => $sale_tax, 'product_shipping_tax' => $product_shipping_tax, 'product_price' => $product_price, 'sale_cost' => $sale_cost, 'sale_mirror_key' => $sale_mirror_key));
                } catch (PDOException $e) {
                    $error = 'ERROR: ' . $e->getMessage();
                }
            }

            // PEGAR DADOS DA VENDA CRIADA 
            $get_sale_id = $conn->prepare('SELECT sale_id FROM sales WHERE product_id = :integration_product_id AND sale_mirror_key = :sale_mirror_key');
            $get_sale_id->execute(array('integration_product_id' => $integration_product_id, 'sale_mirror_key' => $sale_mirror_key));

            if ($get_sale_id->rowCount() != 0) {
                $sale_id = $get_sale_id->fetch();
                $sale_id = $sale_id['sale_id'];
            }

            // ESPELHAR PEDIDO
            $order_number = "BRAIP" . strtoupper($data['trans_key']);

            $check_order_duplicate = $conn->prepare('SELECT * FROM orders WHERE order_number = :order_number');
            $check_order_duplicate->execute(array('order_number' => $order_number));

            if ($check_order_duplicate->rowCount() != 0) {
                $sale_id = $check_order_duplicate->fetch();
                $sale_id = $sale_id['sale_id'];
                echo "Pedido já espelhado";
            } else {
                $stmt = $conn->prepare('INSERT INTO orders(order_id, user__id, sale_id, product_id, product_name, order_date, order_deadline, order_status, order_delivery_date, client_name, client_address, client_number, order_delivery_time, order_number, delivery_period, use_coupon, order_final_price, order_liquid_value, order_commission_date, platform, order_quantity, order_trans_key) VALUES (:order_id, :user__id, :sale_id, :product_id, :product_name, :order_date, :order_deadline, :order_status, :order_delivery_date, :client_name, :client_address, :client_number, :order_delivery_time, :order_number, :delivery_period, :use_coupon, :order_final_price, :order_liquid_value, :order_commission_date, :platform, :order_quantity, :order_trans_key)');

                // DADOS PARA INSERÇÃO AO BANCO
                $order_trans_key = $data['trans_key'];
                $order_id               = 0;
                $user__id               = $integration_user_id;
                $order_delivery_date    = $data['trans_createdate'];
                $order_date             = $data['trans_updatedate'];
                $order_deadline         = $data['trans_updatedate'];
                $order_status           = 6;
                $order_delivery_date    = "2100-01-01 00:00:01";
                $name                   = '[B] ' . $data['client_name'];
                $platform               = 'Braip';
                $quantity               = $data['plan_amount'];

                $address  = $data['client_address'] . ", nº " . $data['client_address_number'] . "<br>";
                $address .= "Bairro " . $data['client_address_district'] . "<br>";

                if ($data['client_address_comp'] != null) {
                    "Complemento: " . $address .= $data['client_address_comp'] . "<br>";
                }

                $address .= $data['client_address_city'] . ", " . $data['client_address_state'] . "<br>";
                $address .= "CEP: " . $data['client_zip_code'];
                $whats                  = formataTelefone($number_phone);
                $delivery_period        = "default";
                $use_coupon             = 1;
                $order_final_price      = $data['trans_total_value'] / 100;
                $order_commission = 0;

                // pegar comissão do produtor
                foreach ($data['commissions'] as $comission) {
                    $array = (array) $comission;
                    if ($array['type'] == "Produtor") {
                        $order_commission = $array['value'] / 100;
                    }
                }

                $order_commission_date  = "2100-01-01 00:00:01";
                $product_name = $data['product_name'];

                try {
                    $stmt->execute(array('order_id' => $order_id, 'user__id' => $user__id, 'sale_id' => $sale_id, 'product_id' => $integration_product_id, 'product_name' => $product_name, 'order_date' => $order_date, 'order_deadline' => $order_deadline, 'order_status' => $order_status, 'order_delivery_date' => $order_delivery_date, 'client_name' => $name, 'client_address' => $address, 'client_number' => $whats, 'order_delivery_time' => $order_delivery_date, 'order_number' => $order_number, 'delivery_period' => $delivery_period, 'use_coupon' => $use_coupon, 'order_final_price' => $order_final_price, 'order_liquid_value' => $order_commission, 'order_commission_date' => $order_commission_date, 'platform' => $platform, 'order_quantity' => $quantity, 'order_trans_key' => $order_trans_key));
                    $lastIdInsert = $conn->lastInsertId();
                    echo "Pedido Espelhado!";
                } catch (PDOException $e) {
                    $error = 'ERROR: ' . $e->getMessage();
                    echo $error;
                    exit;
                }

                // VERIFICAR INADIMPÊNCIA DO USÚARIO 
                $query = "SELECT last_charge_ok FROM users WHERE user__id = :user__id";
                $stmt = $conn->prepare($query);
                $stmt->execute(['user__id' => $integration_user_id]);
                $last_charge_ok = $stmt->fetch()['last_charge_ok'];

                if ($last_charge_ok == "1") {

                    // ENVIAR PRODUTO PARA TINY 
                    # DADOS DO PRODUTO DE ACORDO COM LAYOUT FORNECIDO PELA API TINY
                    $array = array(
                        "produtos" => array(
                            [
                                "produto" => [
                                    'sequencia' => $lastIdInsert,
                                    'nome' => $data['product_name'],
                                    'codigo' => $data['product_key'],
                                    'unidade' => 'UN',
                                    'preco' => number_format(($data['trans_total_value'] / 100) / $data['plan_amount'], 2),
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


                    // ENVIAR PEDIDO PARA TINY 
                    # DADOS DO PEDIDO DE ACORDO COM LAYOUT FORNECIDO PELA API TINY
                    $request = [
                        'pedido' => [
                            'numero_pedido_ecommerce' => $lastIdInsert,
                            'id_ecommerce' => '10519',
                            'data_pedido' => date('d/m/Y', strtotime($data['trans_updatedate'])),
                            'cliente' => [
                                'codigo' => uniqid('cod_'),
                                'nome' => $data['client_name'],
                                'cpf_cnpj' => $data['client_documment'],
                                'cidade' => $data['client_address_city'],
                                'endereco' => mb_convert_encoding($data['client_address'], 'UTF-8', 'UTF-8'),
                                'numero' => mb_convert_encoding($data['client_address_number'], 'UTF-8', 'UTF-8'),
                                'complemento' => $data['client_address_complement'] !== "" ? $data['client_address_complement'] : "Não informado",
                                'bairro' => mb_convert_encoding($data['client_address_district'], 'UTF-8', 'UTF-8'),
                                'cep' => $data['client_zip_code'],
                                'uf' => $data['client_address_state'],
                                'pais' => $data['client_address_country']
                            ],
                            'itens' => [
                                (object) array(
                                    'item' => (object) array(
                                        "codigo" => str_pad($order['id'], 4, '0', STR_PAD_LEFT),
                                        "descricao" => $data['product_name'],
                                        "unidade" => "UN",
                                        "quantidade" =>  $data['plan_amount'],
                                        "valor_unitario" => number_format(($data['trans_total_value'] / 100) / $data['plan_amount'], 2)
                                    ),
                                ),
                            ],
                        ],
                    ];


                    // INICIO DA REQUISIÇÃO PARA ENVIAR O PEDIDO PARA TINY
                    $url = "https://tiny.com.br/api-docs/api2-pedidos-incluir";
                    $pedido = array("pedido" => json_encode($request));
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
                } else {
                    echo "A conta desse usuario possui inadimplências";
                }
            }
            return;
        }

        // CASO NÃO ENTRE NO IF
        echo "UF do pedido não permitida";
    }
}
