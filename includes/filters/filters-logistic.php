<?php
    
    /** 
     *  Obtém dados de Forma de Pagamento.
     * 
     *  Formas de Pagamento:
     *  1 = Dinheiro
     *  2 = Crédito
     *  3 = Débito
     *  4 = PIX
     * 
     */

    $create_temp_table = $conn->prepare('DROP TABLE IF EXISTS orders_all');
    $create_temp_table->execute();

    $arr_products = [];

    $status = isset($_GET['status']) && !empty($_GET['status']) ? $_GET['status'] : 8; 
    $str_in_query = isset($_GET['produto']) && !empty($_GET['produto']) ? 'AND o.product_id = '. $_GET['produto'] : '';

    $str_in_q_user = isset($_GET['cliente']) && !empty($_GET['cliente']) ? 'AND o.user__id = '. $_GET['cliente'] : ''; 
    
    if($_SESSION['UserPlan'] == 5){
        $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_all SELECT o.*, s.sale_quantity, s.product_shipping_tax FROM orders AS o INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE o.platform IS NOT NULL AND o.order_status = :status $str_in_query $str_in_q_user");
        $create_temp_table->execute(array('status' => $status));
    }else{
        $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_all SELECT o.*, s.sale_quantity, s.product_shipping_tax FROM orders AS o INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE o.user__id = :user__id AND o.platform IS NOT NULL AND o.order_status = :status $str_in_query");
        $create_temp_table->execute(array('user__id' => $user__id, 'status' => $status));
    }

    
    # Cálculo Faturamento
    $faturamento = $conn->prepare("SELECT SUM(o.order_liquid_value) AS total FROM orders_all AS o WHERE order_date BETWEEN :date_init AND :date_end");
    $faturamento->execute(['date_init' => $date_init, 'date_end' => $date_end]);
   
    $faturamento = $faturamento->fetch(\PDO::FETCH_ASSOC)['total'];

    # Cálculo Receita total
    $get_total_freight = $conn->prepare("SELECT SUM(product_shipping_tax) as total FROM orders_all WHERE order_date BETWEEN :date_init AND :date_end");
    $get_total_freight->execute(['date_init' => $date_init, 'date_end' => $date_end]);

    $total_freight = $get_total_freight->fetch(\PDO::FETCH_ASSOC)['total'];
    # Cálculo Despesas total
    $get_expenses = $conn->prepare("SELECT SUM(order_tracking_value) as total FROM orders_all WHERE order_date BETWEEN :date_init AND :date_end");
    $get_expenses->execute(['date_init' => $date_init, 'date_end' => $date_end]);
    
    $expenses = $get_expenses->fetch(\PDO::FETCH_ASSOC)['total'];
    
    # Cálculo Envios
    $shipments = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders_all AS o WHERE order_date BETWEEN :date_init AND :date_end");
    $shipments->execute(['date_init' => $date_init, 'date_end' => $date_end]);

    $shipments = $shipments->fetch(\PDO::FETCH_ASSOC)['total'];

    # Cálculo Produtos
    $products = $conn->prepare("SELECT SUM(s.sale_quantity) AS total FROM orders_all AS s WHERE order_date BETWEEN :date_init AND :date_end");
    $products->execute(['date_init' => $date_init, 'date_end' => $date_end]);
    $total = $products->fetch(\PDO::FETCH_ASSOC)['total'];
    $products = $total == null ? 0 : $total;

    # Cálculo Clientes
    $clientes = $conn->prepare('SELECT DISTINCT * FROM orders_all AS o WHERE order_date BETWEEN :date_init AND :date_end GROUP BY o.client_name');
    $clientes->execute(['date_init' => $date_init, 'date_end' => $date_end]);

    $clientes = $clientes->rowCount();

    if(!isset($_GET['status']) && empty($_GET['status'])){
        if($_SESSION['UserPlan'] == 5){
            # Cálculo a Enviar
            $total_to_send = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o WHERE o.order_status = 6 AND order_date BETWEEN :date_init AND :date_end $str_in_query $str_in_q_user");
            $total_to_send->execute(['date_init' => $date_init, 'date_end' => $date_end]);
        
            $total_to_send = $total_to_send->fetch(\PDO::FETCH_ASSOC)['total'];
        
            # Cálculo Enviando 
            $total_to_sending = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o WHERE o.order_status = 7 AND order_date BETWEEN :date_init AND :date_end $str_in_query $str_in_q_user");
            $total_to_sending->execute(['date_init' => $date_init, 'date_end' => $date_end]);
        
            $total_to_sending = $total_to_sending->fetch(\PDO::FETCH_ASSOC)['total'];
        
            # Cálculo Enviado
            $total_to_sent = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o WHERE o.order_status = 8 AND order_date BETWEEN :date_init AND :date_end $str_in_query $str_in_q_user");
            $total_to_sent->execute(['date_init' => $date_init, 'date_end' => $date_end]);
    
            $total_to_sent = $total_to_sent->fetch(\PDO::FETCH_ASSOC)['total'];

        }else{
            # Cálculo a Enviar
            $total_to_send = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o WHERE o.user__id = :user__id AND o.order_status = 6 AND order_date BETWEEN :date_init AND :date_end $str_in_query ");
            $total_to_send->execute(['user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end]);
        
            $total_to_send = $total_to_send->fetch(\PDO::FETCH_ASSOC)['total'];
        
            # Cálculo Enviando 
            $total_to_sending = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o WHERE o.user__id = :user__id AND o.order_status = 7 AND order_date BETWEEN :date_init AND :date_end $str_in_query");
            $total_to_sending->execute(['user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end]);
        
            $total_to_sending = $total_to_sending->fetch(\PDO::FETCH_ASSOC)['total'];
        
            # Cálculo Enviado
            $total_to_sent = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o WHERE o.user__id = :user__id AND o.order_status = 8 AND order_date BETWEEN :date_init AND :date_end $str_in_query");
            $total_to_sent->execute(['user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end]);
    
            $total_to_sent = $total_to_sent->fetch(\PDO::FETCH_ASSOC)['total'];
        }
    }else{
        if($status == 6){
            if($_SESSION['UserPlan'] == 5){
                
                # Cálculo a Enviar
                $total_to_send = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o WHERE o.order_status = 6 AND order_date BETWEEN :date_init AND :date_end $str_in_query $str_in_q_user");
                $total_to_send->execute(['date_init' => $date_init, 'date_end' => $date_end]);
            
                $total_to_send = $total_to_send->fetch(\PDO::FETCH_ASSOC)['total'];
    
                $total_to_sending = 0;
                $total_to_sent = 0;
            }else{  
                # Cálculo a Enviar
                $total_to_send = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o WHERE o.user__id = :user__id AND o.order_status = 6 AND order_date BETWEEN :date_init AND :date_end $str_in_query");
                $total_to_send->execute(['user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end]);
            
                $total_to_send = $total_to_send->fetch(\PDO::FETCH_ASSOC)['total'];

                $total_to_sending = 0;
                $total_to_sent = 0;
            }
        }elseif($status == 7){
            if($_SESSION['UserPlan'] == 5){
                # Cálculo Enviando 
                $total_to_sending = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o WHERE o.order_status = 7 AND order_date BETWEEN :date_init AND :date_end $str_in_query $str_in_q_user");
                $total_to_sending->execute(['date_init' => $date_init, 'date_end' => $date_end]);

                $total_to_sending = $total_to_sending->fetch(\PDO::FETCH_ASSOC)['total'];

                $total_to_send = 0;
                $total_to_sent = 0;
            }else{
                # Cálculo Enviando 
                $total_to_sending = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o WHERE o.user__id = :user__id AND o.order_status = 7 AND order_date BETWEEN :date_init AND :date_end $str_in_query");
                $total_to_sending->execute(['user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end]);
            
                $total_to_sending = $total_to_sending->fetch(\PDO::FETCH_ASSOC)['total'];

                $total_to_send = 0;
                $total_to_sent = 0;
            }
           
        }else{
            if($_SESSION['UserPlan'] == 5){
                if(isset($_GET['status']) && !empty($_GET['status'])){
                    if($_GET['status'] == 6){
                        $total_to_send = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o WHERE o.order_status = 6 AND order_date BETWEEN :date_init AND :date_end $str_in_query $str_in_q_user");
                        $total_to_send->execute(['date_init' => $date_init, 'date_end' => $date_end]);
                    
                        $total_to_send = $total_to_send->fetch(\PDO::FETCH_ASSOC)['total'];

                        $total_to_sending = 0;
                        $total_to_sent = 0;
                    }elseif($_GET['status'] == 7){
                        $total_to_sending = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o WHERE o.order_status = 7 AND order_date BETWEEN :date_init AND :date_end $str_in_query $str_in_q_user");
                        $total_to_sending->execute(['date_init' => $date_init, 'date_end' => $date_end]);
                    
                        $total_to_sending = $total_to_sending->fetch(\PDO::FETCH_ASSOC)['total'];

                        $total_to_send = 0;
                        $total_to_sent = 0;
                    }else{
                        # Cálculo Enviado
                        $total_to_sent = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o WHERE o.order_status = 8 AND order_date BETWEEN :date_init AND :date_end $str_in_query $str_in_q_user");
                        $total_to_sent->execute(['date_init' => $date_init, 'date_end' => $date_end]);

                        $total_to_sent = $total_to_sent->fetch(\PDO::FETCH_ASSOC)['total'];

                        $total_to_send = 0;
                        $total_to_sending = 0;
                    }
                    
                }else{
                    # Cálculo Enviado
                    $total_to_sent = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o WHERE o.order_status = 8 AND order_date BETWEEN :date_init AND :date_end $str_in_query $str_in_q_user");
                    $total_to_sent->execute(['date_init' => $date_init, 'date_end' => $date_end]);
    
                    $total_to_sent = $total_to_sent->fetch(\PDO::FETCH_ASSOC)['total'];
    
                    $total_to_sending = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o WHERE o.order_status = 7 AND order_date BETWEEN :date_init AND :date_end $str_in_query $str_in_q_user");
                    $total_to_sending->execute(['date_init' => $date_init, 'date_end' => $date_end]);
                
                    $total_to_sending = $total_to_sending->fetch(\PDO::FETCH_ASSOC)['total'];
    
                    $total_to_send = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o WHERE o.order_status = 6 AND order_date BETWEEN :date_init AND :date_end $str_in_query $str_in_q_user");
                    $total_to_send->execute(['date_init' => $date_init, 'date_end' => $date_end]);
                
                    $total_to_send = $total_to_send->fetch(\PDO::FETCH_ASSOC)['total'];
                }

            }else{
                if(isset($_GET['status']) && !empty($_GET['status'])){
                    if($_GET['status'] == 6){
                        # Cálculo a Enviar
                        $total_to_send = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o WHERE o.user__id = :user__id AND o.order_status = 6 AND order_date BETWEEN :date_init AND :date_end $str_in_query");
                        $total_to_send->execute(['user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end]);
                    
                        $total_to_send = $total_to_send->fetch(\PDO::FETCH_ASSOC)['total'];

                        $total_to_sending = 0;
                        $total_to_sent = 0;
                    }elseif($_GET['status'] == 7){
                        # Cálculo Enviando 
                        $total_to_sending = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o WHERE o.user__id = :user__id AND o.order_status = 7 AND order_date BETWEEN :date_init AND :date_end $str_in_query");
                        $total_to_sending->execute(['user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end]);
                    
                        $total_to_sending = $total_to_sending->fetch(\PDO::FETCH_ASSOC)['total'];

                        $total_to_send = 0;
                        $total_to_sent = 0;
                    }else{
                        # Cálculo Enviado
                        $total_to_sent = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o WHERE o.user__id = :user__id AND o.order_status = 8 AND order_date BETWEEN :date_init AND :date_end $str_in_query");
                        $total_to_sent->execute(['user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end]);

                        $total_to_sent = $total_to_sent->fetch(\PDO::FETCH_ASSOC)['total'];

                        $total_to_sending = 0;
                        $total_to_send = 0;
                    }
                }else{
                    # Cálculo Enviado
                    $total_to_sent = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o WHERE o.user__id = :user__id AND o.order_status = 8 AND order_date BETWEEN :date_init AND :date_end $str_in_query");
                    $total_to_sent->execute(['user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end]);

                    $total_to_sent = $total_to_sent->fetch(\PDO::FETCH_ASSOC)['total'];

                    $total_to_sending = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o WHERE o.user__id = :user__id AND o.order_status = 7 AND order_date BETWEEN :date_init AND :date_end $str_in_query");
                    $total_to_sending->execute(['user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end]);
                
                    $total_to_sending = $total_to_sending->fetch(\PDO::FETCH_ASSOC)['total'];

                    $total_to_send = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o WHERE o.user__id = :user__id AND o.order_status = 6 AND order_date BETWEEN :date_init AND :date_end $str_in_query");
                    $total_to_send->execute(['user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end]);
                
                    $total_to_send = $total_to_send->fetch(\PDO::FETCH_ASSOC)['total'];

                }
            }
            
        }

    }

    
    # Cálculo Correios 
    $total_correios = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders_all AS o WHERE o.order_shipping = 'Correios' AND order_date BETWEEN :date_init AND :date_end");
    $total_correios->execute(['date_init' => $date_init, 'date_end' => $date_end]);

    $total_correios = $total_correios->fetch(\PDO::FETCH_ASSOC)['total'];
    
    # Cálculo de datas 
    $begin = new DateTime($date_init);
    $end = new DateTime($date_end);
    $end = $end->modify('+1 dia');
    
    $interval = DateInterval::createFromDateString('1 day');
    $period = new DatePeriod($begin, $interval, $end);
    

    $daysOfWeek = [
        "Segunda" => [], "Terça" => [], "Quarta" => [], "Quinta" => [], "Sexta" => [], "Sábado" => [], "Domingo" => []
    ];

    foreach($period as $date){
        $date = $date->format("Y-m-d");
        $timestamp = strtotime($date);
        
        $day = date('D', $timestamp);
        
        $dayPT = array(
            'Sun' => 'Domingo', 
            'Mon' => 'Segunda',
            'Tue' => 'Terça',
            'Wed' => 'Quarta',
            'Thu' => 'Quinta',
            'Fri' => 'Sexta',
            'Sat' => 'Sábado'
        );

        
        array_push($daysOfWeek[$dayPT[$day]], $date);
    }
    
    if($_SESSION['UserPlan'] == 5){
        $sun = 0;
        foreach($daysOfWeek["Domingo"] as $days){
            $query = $conn->prepare("SELECT SUM(s.sale_quantity) AS total FROM orders AS o INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE order_date LIKE :order_date AND o.platform IS NOT NULL AND o.order_status = 8 $str_in_query $str_in_q_user");
            $query->execute(['order_date' => $days.'%']);
            
            $sun += $query->fetch()['total'];
        }  
    
        $mon = 0;
        foreach($daysOfWeek["Segunda"] as $days){
            $query = $conn->prepare("SELECT SUM(s.sale_quantity) AS total FROM orders AS o INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE order_date LIKE :order_date AND o.platform IS NOT NULL AND o.order_status = 8 $str_in_query $str_in_q_user");
            $query->execute(['order_date' => $days.'%']);
            
            $mon += $query->fetch()['total'];
        }  
    
        $tue = 0;
        foreach($daysOfWeek["Terça"] as $days){
            $query = $conn->prepare("SELECT SUM(s.sale_quantity) AS total FROM orders AS o INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE order_date LIKE :order_date AND o.platform IS NOT NULL AND o.order_status = 8 $str_in_query $str_in_q_user");
            $query->execute(['order_date' => $days.'%']);
            
            $tue += $query->fetch()['total'];
        }  
    
        $wed = 0;
        foreach($daysOfWeek["Quarta"] as $days){
            $query = $conn->prepare("SELECT SUM(s.sale_quantity) AS total FROM orders AS o INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE order_date LIKE :order_date AND o.platform IS NOT NULL AND o.order_status = 8 $str_in_query $str_in_q_user");
            $query->execute(['order_date' => $days.'%']);
            
            $wed += $query->fetch()['total'];
        }  
        
        $thu = 0;
        foreach($daysOfWeek["Quinta"] as $days){
            $query = $conn->prepare("SELECT SUM(s.sale_quantity) AS total FROM orders AS o INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE order_date LIKE :order_date AND o.platform IS NOT NULL AND o.order_status = 8 $str_in_query $str_in_q_user");
            $query->execute(['order_date' => $days.'%']);
            
            $thu += $query->fetch()['total'];
        }  
    
        $fri = 0;
        foreach($daysOfWeek["Sexta"] as $days){
            $query = $conn->prepare("SELECT SUM(s.sale_quantity) AS total FROM orders AS o INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE order_date LIKE :order_date AND o.platform IS NOT NULL AND o.order_status = 8 $str_in_query $str_in_q_user");
            $query->execute(['order_date' => $days.'%']);
            
            $fri += $query->fetch()['total'];
        }
    
        $sat = 0;
        foreach($daysOfWeek["Sábado"] as $days){
            $query = $conn->prepare("SELECT SUM(s.sale_quantity) AS total FROM orders AS o INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE order_date LIKE :order_date AND o.platform IS NOT NULL AND o.order_status = 8 $str_in_query $str_in_q_user");
            $query->execute(['order_date' => $days.'%']);
            
            $sat += $query->fetch()['total'];
        }  
    }else{
        $sun = 0;
        foreach($daysOfWeek["Domingo"] as $days){
            $query = $conn->prepare("SELECT SUM(s.sale_quantity) AS total FROM orders AS o INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE order_date LIKE :order_date AND o.platform IS NOT NULL AND o.order_status = 8 AND o.user__id = :user__id $str_in_query $str_in_q_user");
            $query->execute(['order_date' => $days.'%', 'user__id' => $user__id]);
            
            $sun += $query->fetch()['total'];
        }  
    
        $mon = 0;
        foreach($daysOfWeek["Segunda"] as $days){
            $query = $conn->prepare("SELECT SUM(s.sale_quantity) AS total FROM orders AS o INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE order_date LIKE :order_date AND o.platform IS NOT NULL AND o.order_status = 8 AND o.user__id = :user__id $str_in_query $str_in_q_user");
            $query->execute(['order_date' => $days.'%', 'user__id' => $user__id]);
            
            $mon += $query->fetch()['total'];
        }  
    
        $tue = 0;
        foreach($daysOfWeek["Terça"] as $days){
            $query = $conn->prepare("SELECT SUM(s.sale_quantity) AS total FROM orders AS o INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE order_date LIKE :order_date AND o.platform IS NOT NULL AND o.order_status = 8 AND o.user__id = :user__id $str_in_query $str_in_q_user");
            $query->execute(['order_date' => $days.'%', 'user__id' => $user__id]);
            
            $tue += $query->fetch()['total'];
        }  
    
        $wed = 0;
        foreach($daysOfWeek["Quarta"] as $days){
            $query = $conn->prepare("SELECT SUM(s.sale_quantity) AS total FROM orders AS o INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE order_date LIKE :order_date AND o.platform IS NOT NULL AND o.order_status = 8 AND o.user__id = :user__id $str_in_query $str_in_q_user");
            $query->execute(['order_date' => $days.'%', 'user__id' => $user__id]);
            
            $wed += $query->fetch()['total'];
        }  
        
        $thu = 0;
        foreach($daysOfWeek["Quinta"] as $days){
            $query = $conn->prepare("SELECT SUM(s.sale_quantity) AS total FROM orders AS o INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE order_date LIKE :order_date AND o.platform IS NOT NULL AND o.order_status = 8 AND o.user__id = :user__id $str_in_query $str_in_q_user");
            $query->execute(['order_date' => $days.'%', 'user__id' => $user__id]);
            
            $thu += $query->fetch()['total'];
        }  
    
        $fri = 0;
        foreach($daysOfWeek["Sexta"] as $days){
            $query = $conn->prepare("SELECT SUM(s.sale_quantity) AS total FROM orders AS o INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE order_date LIKE :order_date AND o.platform IS NOT NULL AND o.order_status = 8 AND o.user__id = :user__id $str_in_query $str_in_q_user");
            $query->execute(['order_date' => $days.'%', 'user__id' => $user__id]);
            
            $fri += $query->fetch()['total'];
        }
    
        $sat = 0;
        foreach($daysOfWeek["Sábado"] as $days){
            $query = $conn->prepare("SELECT SUM(s.sale_quantity) AS total FROM orders AS o INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE order_date LIKE :order_date AND o.platform IS NOT NULL AND o.order_status = 8 AND o.user__id = :user__id $str_in_query $str_in_q_user");
            $query->execute(['order_date' => $days.'%', 'user__id' => $user__id]);
            
            $sat += $query->fetch()['total'];
        }  
    }

    // total produtos vendidos 
    
    $query = $conn->prepare("SELECT o.*, p.product_name, p.product_code FROM orders_all AS o INNER JOIN products AS p ON o.product_id = p.product_id WHERE o.order_date BETWEEN :date_init AND :date_end");
    $query->execute(['date_init' => $date_init, 'date_end' => $date_end]);
    
    $all_most_sales = $query->fetchAll(\PDO::FETCH_ASSOC);

    
  
    $products_all = $conn->prepare("SELECT SUM(sale_quantity) AS total FROM orders_all AS o WHERE o.order_date BETWEEN :date_init AND :date_end");
    $products_all->execute(['date_init' => $date_init, 'date_end' => $date_end]);

    $total_products = $products_all->fetch(\PDO::FETCH_ASSOC)['total'];


    $products_most_sales = [];
    foreach ($all_most_sales as $product) {
   
        if(array_search($product['product_name'], array_column($products_most_sales, 'name')) === false){


            $query = $conn->prepare("SELECT SUM(sale_quantity) as quantity FROM orders_all AS o WHERE o.order_date BETWEEN :date_init AND :date_end AND o.product_id = :product_id");
            $query->execute(['date_init' => $date_init, 'date_end' => $date_end, 'product_id' => $product['product_id']]);
            $total_this_product = $query->fetch()['quantity'];

            $sales = $conn->prepare("SELECT COUNT(o.order_id) as sales FROM orders_all AS o WHERE o.order_date BETWEEN :date_init AND :date_end AND o.product_id = :product_id");
            $sales->execute(['date_init' => $date_init, 'date_end' => $date_end, 'product_id' => $product['product_id']]);

            $sales = $sales->fetch()['sales'];

            $income = $conn->prepare("SELECT SUM(o.order_liquid_value) as income FROM orders_all AS o WHERE o.order_date BETWEEN :date_init AND :date_end AND o.product_id = :product_id");
            $income->execute(['date_init' => $date_init, 'date_end' => $date_end, 'product_id' => $product['product_id']]);

            $income = $income->fetch()['income'];

            array_push($products_most_sales, [
                "id" => $product['order_id'],
                'id_product' => $product['product_id'],
                'product_code' => $product['product_code'],
                "name" => $product['product_name'],
                "sales" => $sales,
                "products" => $total_this_product,
                "percent" => number_format(($total_this_product / $total_products)* 100),
                "income" => $income,
                "spent" => 0.00, // Não dá pra calcular esse valor no momento.
            ]);     
        } 
    }

    # Pegar todos os ID's de vendas no tiny dos produtos 
    
    $query = $conn->prepare("SELECT o.order_tiny_id FROM orders_all AS o WHERE o.order_date BETWEEN :date_init AND :date_end AND o.order_status = 8");
    $query->execute(['date_init' => $date_init, 'date_end' => $date_end]);
    
    $arr_all_sales = [];
    foreach($query->fetchAll(\PDO::FETCH_ASSOC) as $id){
        array_push($arr_all_sales, $id['order_tiny_id']);
    }
    
    $array_ids_tiny_string = implode(",",$arr_all_sales);

    // Calculo custo frete

    $query = $conn->prepare("SELECT user_plan_shipping_tax FROM subscriptions WHERE user__id = :user__id");
    $query->execute(['user__id' => $user__id]);

    $shipping_tax = $query->fetch(\PDO::FETCH_ASSOC)['user_plan_shipping_tax'];

    if($shipments > 0){
        $freight_total = $shipments * $shipping_tax;
    }else{
        $freight_total = 0;
    }