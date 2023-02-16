


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
    if(!isset($_GET['manutenção'])) {
        echo '<pre>';
    } 


    $create_temp_table = $conn->prepare('DROP TABLE IF EXISTS orders_all');
    $create_temp_table->execute(array());

    $create_temp_table = $conn->prepare('DROP TABLE IF EXISTS orders_zer');
    $create_temp_table->execute(array());

    $create_temp_table = $conn->prepare('DROP TABLE IF EXISTS orders_one');
    $create_temp_table->execute(array());

    $create_temp_table = $conn->prepare('DROP TABLE IF EXISTS orders_two');
    $create_temp_table->execute(array());

    $create_temp_table = $conn->prepare('DROP TABLE IF EXISTS orders_thr');
    $create_temp_table->execute(array());

    $create_temp_table = $conn->prepare('DROP TABLE IF EXISTS orders_fou');
    $create_temp_table->execute(array());

    $create_temp_table = $conn->prepare('DROP TABLE IF EXISTS orders_fiv');
    $create_temp_table->execute(array());

    $create_temp_table = $conn->prepare('DROP TABLE IF EXISTS orders_nine');
    $create_temp_table->execute(array());

    $create_temp_table = $conn->prepare('DROP TABLE IF EXISTS orders_ten');
    $create_temp_table->execute(array());

    $create_temp_table = $conn->prepare('DROP TABLE IF EXISTS orders_eleven');
    $create_temp_table->execute(array());

    $create_temp_table = $conn->prepare('DROP TABLE IF EXISTS orders_twelve');
    $create_temp_table->execute(array());

    # FILTROS 
    // POR USUÁRIO
    $per_user = isset($_GET['users']) && !empty($_GET['users']) ? 'AND o.user__id = '. addslashes($_GET['users']) : '';
     
    if($_SESSION['UserPlan'] == 5){
        $not_afi_faturamento = 'WHERE order_number NOT LIKE "%AFI%"';
        if(empty($per_user)){
            $not_afi_where = 'WHERE order_number NOT LIKE "%AFI%"';
            $not_afi_and = 'AND order_number NOT LIKE "%AFI%"';
        }
    } else {
        $not_afi_faturamento = '';
        $not_afi_where = '';
        $not_afi_and = '';
    }


    // POR OPERADOR LOGÍSTICO
    $per_logistic_operator = '';
    $per_local_operator = '';

    if(isset($_GET['oplogistico']) && !empty($_GET['oplogistico'])){
        $per_logistic_operator = $per_local_operator == '' ? 'INNER JOIN local_operations_orders loo ON loo.order_id = o.order_id AND loo.responsible_id = '. addslashes($_GET['oplogistico']) : '';
    }
    // POR OPERAÇÃO LOCAL
    if(isset($_GET['locale']) && !empty($_GET['locale'])){
        $per_local_operator = $per_logistic_operator == '' ? 'INNER JOIN local_operations_orders AS loo ON o.order_id = loo.order_id AND loo.operation_id = '. addslashes($_GET['locale']) : 'AND loo.operation_id = '. addslashes($_GET['locale']);
    }

    // PARA AMBOS (OPERAÇÃO LOCAL, OPERADOR LOGÍSTICO)
    if(!empty($_GET['locale']) && !empty($_GET['oplogistico'])){
        $two_filters_same_inner_join = 'INNER JOIN local_operations_orders AS loo ON o.order_id = loo.order_id AND loo.operation_id = '. addslashes($_GET['locale']) .'AND loo.responsible_id = '. addslashes($_GET['oplogistico']);
    }

    // POR STATUS
    $per_status = isset($_GET['status']) && !empty($_GET['status']) ? addslashes($_GET['status']) -1 : 3;
    
    // POR STATUS USUÁRIO 
    $per_product = isset($_GET['produto']) && !empty($_GET['produto']) ? 'AND o.product_id = '. addslashes($_GET['produto']) : '';

    $logistic_or_operator = $per_logistic_operator. " " . $per_local_operator . " ";

    if($_SESSION['UserPlan'] == 5){
        // PEGA DE TODOS OS USUÁRIOS
        
        /*
            CREATE TEMPORARY TABLE orders_all SELECT o.* FROM orders AS o WHERE (o.order_status = 3 AND order_delivery_date BETWEEN '2022-08-23' AND '2022-09-22' AND o.order_number NOT LIKE 'AFI%') AND platform IS null;

            SELECT * FROM orders_all ORDER BY order_delivery_date DESC;

            SELECT A.total FROM (SELECT SUM(order_final_price) AS total, order_delivery_date FROM orders_all AS o WHERE o.order_number NOT LIKE "AFI%") as A INNER JOIN sales AS s;
        */

        $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_all SELECT o.* FROM orders AS o $logistic_or_operator WHERE (o.order_status = :status AND order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product $per_user");
        $create_temp_table->execute(array('date_init' => $date_init, 'date_end' => $date_end, 'status' => $per_status));
        //CREATE TEMPORARY TABLE orders_all SELECT o.* FROM orders AS o WHERE (o.order_status = 3 AND order_delivery_date BETWEEN "2022-09-11" AND "2022-10-12") AND platform IS null AND o.user__id = 37
        //CREATE TEMPORARY TABLE orders_all SELECT o.* FROM orders AS o WHERE (o.order_status = 3 AND order_delivery_date BETWEEN "2022-09-12" AND "2022-10-13") AND platform IS null;

        if(isset($_GET['status']) && !empty($_GET['status'])){
            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_zer SELECT o.* FROM orders_all AS o $logistic_or_operator WHERE (o.order_status = 0 AND o.order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product $per_user");
            $create_temp_table->execute(array('date_init' => $date_init, 'date_end' => $date_end));
        
            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_one SELECT o.* FROM orders_all AS o $logistic_or_operator WHERE (o.order_status = 1 AND o.order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product $per_user");
            $create_temp_table->execute(array('date_init' => $date_init, 'date_end' => $date_end));
        
            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_two SELECT o.* FROM orders_all AS o $logistic_or_operator WHERE (o.order_status = 2 AND o.order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product $per_user");
            $create_temp_table->execute(array('date_init' => $date_init, 'date_end' => $date_end));
        
            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_thr SELECT o.* FROM orders_all AS o $logistic_or_operator WHERE (o.order_status = 3 AND o.order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product $per_user");
            $create_temp_table->execute(array('date_init' => $date_init, 'date_end' => $date_end));
        
            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_fou SELECT o.* FROM orders_all AS o $logistic_or_operator WHERE (o.order_status = 4 AND o.order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product $per_user");
            $create_temp_table->execute(array('date_init' => $date_init, 'date_end' => $date_end));
        
            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_fiv SELECT o.* FROM orders_all AS o $logistic_or_operator WHERE (o.order_status = 5 AND o.order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product $per_user");
            $create_temp_table->execute(array('date_init' => $date_init, 'date_end' => $date_end));
        
            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_nine SELECT o.* FROM orders_all AS o $logistic_or_operator WHERE (o.order_status = 9 AND o.order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product $per_user");
            $create_temp_table->execute(array('date_init' => $date_init, 'date_end' => $date_end));

            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_ten SELECT o.* FROM orders_all AS o $logistic_or_operator WHERE (o.order_status = 10 AND o.order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product $per_user");
            $create_temp_table->execute(array('date_init' => $date_init, 'date_end' => $date_end));

            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_eleven SELECT o.* FROM orders_all AS o $logistic_or_operator WHERE (o.order_status = 11 AND o.order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product $per_user");
            $create_temp_table->execute(array('date_init' => $date_init, 'date_end' => $date_end));

            
            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_twelve SELECT o.* FROM orders_all AS o $logistic_or_operator WHERE (o.order_status = 12 AND o.order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product $per_user");
            $create_temp_table->execute(array('date_init' => $date_init, 'date_end' => $date_end));
            
        }else{

            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_zer SELECT o.* FROM orders AS o $logistic_or_operator WHERE (o.order_status = 0 AND o.order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product $per_user");
            $create_temp_table->execute(array('date_init' => $date_init, 'date_end' => $date_end));
        
            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_one SELECT o.* FROM orders AS o $logistic_or_operator WHERE (o.order_status = 1 AND o.order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product $per_user");
            $create_temp_table->execute(array('date_init' => $date_init, 'date_end' => $date_end));
        
            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_two SELECT o.* FROM orders AS o $logistic_or_operator WHERE (o.order_status = 2 AND o.order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product $per_user");
            $create_temp_table->execute(array('date_init' => $date_init, 'date_end' => $date_end));
        
            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_thr SELECT o.* FROM orders AS o $logistic_or_operator WHERE (o.order_status = 3 AND o.order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product $per_user");
            $create_temp_table->execute(array('date_init' => $date_init, 'date_end' => $date_end));
        
            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_fou SELECT o.* FROM orders AS o $logistic_or_operator WHERE (o.order_status = 4 AND o.order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product $per_user");
            $create_temp_table->execute(array('date_init' => $date_init, 'date_end' => $date_end));
        
            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_fiv SELECT o.* FROM orders AS o $logistic_or_operator WHERE (o.order_status = 5 AND o.order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product $per_user");
            $create_temp_table->execute(array('date_init' => $date_init, 'date_end' => $date_end));
        
            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_nine SELECT o.* FROM orders AS o $logistic_or_operator WHERE (o.order_status = 9 AND o.order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product $per_user");
            $create_temp_table->execute(array('date_init' => $date_init, 'date_end' => $date_end));

            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_ten SELECT o.* FROM orders AS o $logistic_or_operator WHERE (o.order_status = 10 AND o.order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product $per_user");
            $create_temp_table->execute(array('date_init' => $date_init, 'date_end' => $date_end));

            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_eleven SELECT o.* FROM orders AS o $logistic_or_operator WHERE (o.order_status = 11 AND o.order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product $per_user");
            $create_temp_table->execute(array('date_init' => $date_init, 'date_end' => $date_end));

            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_twelve SELECT o.* FROM orders AS o $logistic_or_operator WHERE (o.order_status = 12 AND o.order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product $per_user");
            $create_temp_table->execute(array('date_init' => $date_init, 'date_end' => $date_end));
        }

    }else{
        
        // PEGA DO USUÁRIO LIGADO
        $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_all SELECT o.* FROM orders AS o $per_local_operator WHERE user__id = :user__id AND (order_status = :status AND order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product");
        $create_temp_table->execute(array('user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end, 'status' => $per_status));
        
        if(isset($_GET['status']) && !empty($_GET['status'])){
            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_zer SELECT o.* FROM orders_all AS o $per_local_operator WHERE user__id = :user__id AND (order_status = 0 AND order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product");
            $create_temp_table->execute(array('user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end));
        
            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_one SELECT o.* FROM orders_all AS o $per_local_operator WHERE user__id = :user__id AND (order_status = 1 AND order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product");
            $create_temp_table->execute(array('user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end));
        
            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_two SELECT o.* FROM orders_all AS o $per_local_operator WHERE user__id = :user__id AND (order_status = 2 AND order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product");
            $create_temp_table->execute(array('user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end));
        
            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_thr SELECT o.* FROM orders_all AS o $per_local_operator WHERE user__id = :user__id AND (order_status = 3 AND order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product");
            $create_temp_table->execute(array('user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end));
        
            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_fou SELECT o.* FROM orders_all AS o $per_local_operator WHERE user__id = :user__id AND (order_status = 4 AND order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product");
            $create_temp_table->execute(array('user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end));
        
            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_fiv SELECT o.* FROM orders_all AS o $per_local_operator WHERE user__id = :user__id AND (order_status = 5 AND order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product");
            $create_temp_table->execute(array('user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end));
        
            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_nine SELECT o.* FROM orders_all AS o $per_local_operator WHERE user__id = :user__id AND (order_status = 9 AND order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product");
            $create_temp_table->execute(array('user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end));

            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_ten SELECT o.* FROM orders_all AS o $per_local_operator WHERE user__id = :user__id AND (order_status = 10 AND order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product");
            $create_temp_table->execute(array('user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end));

            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_eleven SELECT o.* FROM orders_all AS o $per_local_operator WHERE user__id = :user__id AND (order_status = 11 AND order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product");
            $create_temp_table->execute(array('user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end));

            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_twelve SELECT o.* FROM orders_all AS o $per_local_operator WHERE user__id = :user__id AND (order_status = 12 AND order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product");
            $create_temp_table->execute(array('user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end));
        }else{ 
            
            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_zer SELECT o.* FROM orders AS o $per_local_operator WHERE user__id = :user__id AND (order_status = 0 AND order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product");
            $create_temp_table->execute(array('user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end));
        
            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_one SELECT o.* FROM orders AS o $per_local_operator WHERE user__id = :user__id AND (order_status = 1 AND order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product");
            $create_temp_table->execute(array('user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end));
        
            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_two SELECT o.* FROM orders AS o $per_local_operator WHERE user__id = :user__id AND (order_status = 2 AND order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product");
            $create_temp_table->execute(array('user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end));
        
            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_thr SELECT o.* FROM orders AS o $per_local_operator WHERE user__id = :user__id AND (order_status = 3 AND order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product");
            $create_temp_table->execute(array('user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end));
        
            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_fou SELECT o.* FROM orders AS o $per_local_operator WHERE user__id = :user__id AND (order_status = 4 AND order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product");
            $create_temp_table->execute(array('user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end));
        
            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_fiv SELECT o.* FROM orders AS o $per_local_operator WHERE user__id = :user__id AND (order_status = 5 AND order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product");
            $create_temp_table->execute(array('user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end));
        
            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_nine SELECT o.* FROM orders AS o $per_local_operator WHERE user__id = :user__id AND (order_status = 9 AND order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product");
            $create_temp_table->execute(array('user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end));

            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_ten SELECT o.* FROM orders AS o $per_local_operator WHERE user__id = :user__id AND (order_status = 10 AND order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product");
            $create_temp_table->execute(array('user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end));

            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_eleven SELECT o.* FROM orders AS o $per_local_operator WHERE user__id = :user__id AND (order_status = 11 AND order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product");
            $create_temp_table->execute(array('user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end)); 

            $create_temp_table = $conn->prepare("CREATE TEMPORARY TABLE orders_twelve SELECT o.* FROM orders AS o $per_local_operator WHERE user__id = :user__id AND (order_status = 12 AND order_delivery_date BETWEEN :date_init AND :date_end) AND platform IS null $per_product");
            $create_temp_table->execute(array('user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end)); 
        }
       
    }

    
    $money_     = 'money';
    $credit_    = 'credit';
    $debit_     = 'debit';
    $pix_       = 'pix';

    # Quantidade de Pagamentos em Dinheiro
    $get_money_payment = $conn->prepare("SELECT A.quant FROM (SELECT COUNT(order_payment_method) AS quant, order_delivery_date, order_payment_method AS method FROM orders_all WHERE order_payment_method = 'money' $not_afi_and ) as A");
    
    $get_money_payment->execute();

    if ($get_money_payment->rowCount() < 1) {  $a = 0;  } else {  $money_payment = $get_money_payment->fetch(); $a = $money_payment['quant'];  }

    # Quantidade de Pagamentos em Crédito
    $get_credit_payment = $conn->prepare("SELECT A.quant FROM (SELECT COUNT(order_payment_method) AS quant, order_delivery_date, order_payment_method AS method FROM orders_all WHERE order_payment_method = 'credit' $not_afi_and ) as A");

    $get_credit_payment->execute();

    if ($get_credit_payment->rowCount() < 1) {  $b = 0;  } else {  $credit_payment = $get_credit_payment->fetch(); $b = $credit_payment['quant'];  }

    # Quantidade de Pagamentos em Débito
    $get_debit_payment = $conn->prepare("SELECT A.quant FROM (SELECT COUNT(order_payment_method) AS quant, order_delivery_date, order_payment_method AS method FROM orders_all WHERE order_payment_method = 'debit' $not_afi_and ) as A");

    $get_debit_payment->execute();

    if ($get_debit_payment->rowCount() < 1) {  $c = 0;  } else {  $debit_payment = $get_debit_payment->fetch(); $c = $debit_payment['quant'];  }

    # Quantidade de Pagamentos em PIX
    $get_pix_payment = $conn->prepare("SELECT A.quant FROM (SELECT COUNT(order_payment_method) AS quant, order_delivery_date, order_payment_method AS method FROM orders_all WHERE order_payment_method = 'pix' $not_afi_and ) as A");

    $get_pix_payment->execute();

    if ($get_pix_payment->rowCount() < 1) {  $d = 0;  } else { $pix_payment = $get_pix_payment->fetch(); $d = $pix_payment['quant'];  }



    # PEGAR TOTAL FATURADO EM VENDAS COMO PRODUTOR
    $faturamento = $conn->prepare("SELECT A.total FROM (SELECT SUM(order_final_price) AS total, order_delivery_date FROM orders_all $not_afi_faturamento ) as A  INNER JOIN sales AS s ");
    $faturamento->execute();

   
    $total_witch_producer = $faturamento->fetch()['total'];

    # PEGAR TODAL FATURADO EM VENDAS COMO AFILIADO
    $get_total_sales_afi = $conn->prepare("SELECT REPLACE(o.order_number, 'AFI', '') AS order_number FROM orders_all AS o INNER JOIN sales AS s ON o.sale_id = s.sale_id AND o.order_number LIKE 'AFI%'");
    $get_total_sales_afi->execute();   

    $orders_number_afi = $get_total_sales_afi->fetchAll(\PDO::FETCH_ASSOC);

    $total_witch_afi = 0;


    foreach ($orders_number_afi as $order_number) {
        $order_number = $order_number['order_number'];
        
        if(!empty($per_user)){
            $faturamento_afi = $conn->prepare('SELECT SUM(order_final_price) AS total, order_delivery_date FROM orders WHERE order_number = :order_number');
            $faturamento_afi->execute(array('order_number' => $order_number));
            $total_witch_producer += $faturamento_afi->fetch()['total'];
        }
        
        $get_member_commission = $conn->prepare('SELECT meta_value FROM orders_meta WHERE meta_key = "member_commission" AND order_number = :order_number');
        $get_member_commission->execute(array('order_number' => $order_number));
        $get_member_commission = $get_member_commission->fetch();
        @$member_commission = $get_member_commission['meta_value'];

        $get_member_tax = $conn->prepare('SELECT meta_value FROM orders_meta WHERE meta_key = "member_tax" AND order_number = :order_number');
        $get_member_tax->execute(array('order_number' => $order_number));
        $get_member_tax = $get_member_tax->fetch();
        @$member_tax = $get_member_tax[0];

        
        $total_witch_afi += $member_commission + $member_tax;
    }


    if($faturamento->rowCount() == 0){
        $faturamento= array('0' => 0);
    } else {
        $faturamento = $total_witch_producer ;//+ $total_witch_afi;
    }

    $vendas = $conn->prepare("SELECT COUNT(*), order_delivery_date FROM orders_all " . $not_afi_where);
    $vendas->execute();

    if( $vendas->rowCount() == 0){
        $vendas = array('0' => 0);
    } else {
        $vendas =  $vendas->fetch();
    }
    

    $produtos = $conn->prepare("SELECT SUM(sale_quantity), order_delivery_date FROM orders_all  INNER JOIN sales ON orders_all.sale_id = sales.sale_id ". $not_afi_where );
    $produtos->execute();

    if($produtos->rowCount() == 0){

        $produtos = 0;

    } else {
        $produtos = $produtos->fetch();

        if ($produtos[0] == null){
            $produtos = 0;
        } else {
            $produtos = $produtos[0];
        }
    }
    
    $clientes = $conn->prepare("SELECT DISTINCT client_number FROM orders_all $not_afi_where GROUP BY order_number");

    $clientes->execute();
    $clientes = $clientes->rowCount();

    $reembolsos = $conn->prepare('SELECT * FROM orders_all WHERE order_status = 6 GROUP BY order_number');
    $reembolsos->execute();
    $reembolsos = $reembolsos->rowCount();

    $total_commisões = $conn->prepare("SELECT DISTINCT SUM(o.order_liquid_value) as total FROM orders AS o INNER JOIN memberships AS m WHERE m.membership_producer_id = :user__id AND o.user__id = m.membership_affiliate_id AND o.product_id = m.membership_product_id AND o.order_number LIKE '%AFI%' AND order_delivery_date BETWEEN :date_init AND :date_end GROUP BY o.order_id ORDER BY `o`.`order_id` DESC");
    $total_commisões->execute([
        'user__id' => $user__id,
        'date_init' => $date_init,
         'date_end' => $date_end
    ]);

    $allComission = $total_commisões->fetchAll(\PDO::FETCH_ASSOC);


    $count_comission = 00.00;

    foreach($allComission as $commision){
        $count_comission += (float) $commision['total'];
    }

    $afiliates_sale = $count_comission;

    if ($faturamento != 0) {
        
        # Cálculo Comissão
        $comission = $conn->prepare('SELECT A.total FROM (SELECT SUM(order_liquid_value) AS total FROM orders_all) as A');
        $comission->execute();
        
        
        if($comission->rowCount() == 0){
            $comission= array('0' => 0);
        } else {
            $comission = $comission->fetch();
        }
        
        if( $faturamento == 0){
            $comissao_por100 = 0;
            $comissao = 0;
        } else {
            $comissao = $comission[0];
            $comissao_por100 = ($comission[0] * 100) / $faturamento;
        }   
    } else {
        $entrega_total = 0;
        $taxa_total = 0;
        $comissao = 0;
        $comissao_por100 = 0;
    }

//{

    /**
     * 
     * Products List for Filters
     * 
     * 
     * 
    */

    $get_product_list = $conn->prepare('SELECT * FROM products WHERE user__id = :user__id AND product_trash = 0');
    $get_product_list->execute(array('user__id' => $user__id));
    
    $get_sales_status0 = $conn->prepare('SELECT COUNT(order_status) FROM orders WHERE order_status = 0 AND user__id = :user__id');
    $get_sales_status0->execute(array('user__id' => $user__id));

    $sales0 = $get_sales_status0->fetch();

    # GRÁFICO DE VENDAS POR PRODUTO

    // PEGAR LISTA DE PRODUTOS 
    $get_products_list = $conn->prepare("SELECT o.product_name, s.sale_quantity, p.product_code FROM orders_all AS o INNER JOIN sales AS s ON s.sale_id = o.sale_id INNER JOIN products AS p ON p.product_id = o.product_id");
    $get_products_list->execute(); //array('user__id' => $user__id)
    $all_products = $get_products_list->fetchAll(\PDO::FETCH_ASSOC);

    // QUERIES PARA DADOS NESSÁRIOS PARA GRAFICO DE VENDAS POR PRODUTOS 
    $get_products_sum = $conn->prepare("SELECT SUM(sale_quantity) AS sale_quantity FROM orders_all AS o INNER JOIN sales AS s ON s.sale_id = o.sale_id WHERE product_name = :product_name $not_afi_and");
    $get_products_id = $conn->prepare("SELECT o.product_id FROM orders_all AS o INNER JOIN sales AS s ON s.sale_id = o.sale_id AND product_name = :product_name $not_afi_and");
    $get_user_id = $conn->prepare("SELECT user__id FROM orders_all WHERE product_id = :product_id $not_afi_and");
    $specific_sale = $conn->prepare("SELECT COUNT(*) AS count_all, order_datetime FROM orders_all WHERE product_id = :product_id $not_afi_and");
    $get_commission_afi = $conn->prepare("SELECT SUM(o.order_liquid_value) AS total FROM orders AS o INNER JOIN memberships AS m ON m.membership_affiliate_id = o.user__id AND o.user__id = m.membership_affiliate_id AND m.membership_product_id = o.product_id AND o.order_number LIKE '%AFI%' AND o.product_id = :product_id AND o.order_date BETWEEN :date_init AND :date_end AND o.order_status = 3");
    $specific_invoice = $conn->prepare("SELECT SUM(o.order_final_price) FROM orders_all as o INNER JOIN sales AS s WHERE o.sale_id = s.sale_id AND o.product_id = :product_id $not_afi_and");  
        
    
    
    // ARRAYS NESSESÁRIOS PARA OS FOREACH
    $names_of_products = [];
    $quantity_of_products = [];
    $products = [];
    $codes = [];
    $total_sales_by_product = [];
    $qtd_sales = [];
    $invoice = [];
    $id_arr = [];
    $percent = [];
    $comission_afi_arr = [];

    foreach($all_products as $value) {
        array_push($names_of_products, $value['product_name']);
        array_push($quantity_of_products, $value['sale_quantity']);
        array_push($codes, $value['product_code']);
    }

    $products_name_list = array_unique($names_of_products);
    foreach($products_name_list as $value) {
        $str = explode(" ", $value);
        
        array_push($products, @$str[0] . " ". @$str[1]); 
    }
    
    foreach($products_name_list as $key => $value) {

        $get_products_sum->execute(array("product_name" => $value));
        $result = $get_products_sum->fetch(\PDO::FETCH_ASSOC);

        $get_products_id->execute(array("product_name" => $value));
        $products_id = $get_products_id->fetch();

        $get_user_id->execute(array("product_id" => $products_id['product_id']));
        $user_id = $get_user_id->fetch();
        
        $specific_sale->execute(array("product_id" => $products_id['product_id']));
        $total_sales = $specific_sale->fetch(); 

        array_push($total_sales_by_product, $result['sale_quantity']);
        array_push($id_arr, $products_id['product_id']);
        array_push($qtd_sales, $total_sales['count_all']);
    }

    foreach($id_arr as $id) {         
        $get_commission_afi->execute(['product_id' => $id, 'date_init' => $date_init, 'date_end' => $date_end]);
        $total_affi_commision =  $get_commission_afi->fetch()['total']; 
        $specific_invoice->execute(array('product_id' => $id));
        $invoice_user = $specific_invoice->fetch(); 
        
        array_push($comission_afi_arr, $total_affi_commision);      
        array_push($invoice,$invoice_user[0]); 
    }

    $total_products = array_sum($total_sales_by_product);

    foreach($total_sales_by_product as $value) {
        $percent[] .= number_format(($value / $total_products)*100,2) ;
    }


    $assoc_arr = array();
    for($i = 0; $i<count($products);$i++) {
        array_push($assoc_arr,[
            "products_id" => $id_arr[$i],
            "product_name" => $products[$i],
            "product_code" => $codes[$i],
            "sales" => $qtd_sales[$i],
            "products" => @$total_sales_by_product[$i],
            "percent" => $percent[$i] . "%",
            "invoicing" => @$invoice[$i],
            "afiliate" => @$comission_afi_arr[$i]
        ]); 
    } 

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

    $sun = 0;
    $whatFilter = $_SESSION['UserPlan'] == 5 ? $logistic_or_operator : $per_local_operator;

    if($_SESSION['UserPlan'] == 5){
        foreach($daysOfWeek["Domingo"] as $days){
            if(isset($_GET['status']) && !empty($_GET['status'])){
                $query = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o $logistic_or_operator INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE o.order_date LIKE :order_date AND o.order_status = :status $per_product $per_user $not_afi_and");
                $query->execute(['order_date' => $days.'%', 'status' => $per_status]);
            }else{
                $query = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o $logistic_or_operator INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE o.order_date LIKE :order_date AND o.order_status = 0 $per_product $per_user $not_afi_and");
                $query->execute(['order_date' => $days.'%']);
            } 
            $sun += $query->fetch()['total'];
        }  
    
        $mon = 0;
        foreach($daysOfWeek["Segunda"] as $days){
            if(isset($_GET['status']) && !empty($_GET['status'])){
                $query = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o $logistic_or_operator INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE o.order_date LIKE :order_date AND o.order_status = :status $per_product $per_user $not_afi_and");
                $query->execute(['order_date' => $days.'%', 'status' => $per_status]);
            }else{
                $query = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o $logistic_or_operator INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE o.order_date LIKE :order_date AND o.order_status = 0 $per_product $per_user $not_afi_and");
                $query->execute(['order_date' => $days.'%']);
            } 
            $mon += $query->fetch()['total'];
        }  
    
        $tue = 0;
        foreach($daysOfWeek["Terça"] as $days){
            if(isset($_GET['status']) && !empty($_GET['status'])){
                $query = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o $logistic_or_operator INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE o.order_date LIKE :order_date AND o.order_status = :status $per_product $per_user $not_afi_and");
                $query->execute(['order_date' => $days.'%', 'status' => $per_status]);
            }else{
                $query = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o $logistic_or_operator INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE o.order_date LIKE :order_date AND o.order_status = 0 $per_product $per_user $not_afi_and");
                $query->execute(['order_date' => $days.'%']);
            } 
            $tue += $query->fetch()['total'];
        }  
    
        $wed = 0;
        foreach($daysOfWeek["Quarta"] as $days){
            if(isset($_GET['status']) && !empty($_GET['status'])){
                $query = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o $logistic_or_operator INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE o.order_date LIKE :order_date AND o.order_status = :status $per_product $per_user $not_afi_and");
                $query->execute(['order_date' => $days.'%', 'status' => $per_status]);
            }else{
                $query = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o $logistic_or_operator INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE o.order_date LIKE :order_date AND o.order_status = 0 $per_product $per_user $not_afi_and");
                $query->execute(['order_date' => $days.'%']);
            } 
            $wed += $query->fetch()['total'];
        }  
        
        $thu = 0;
        foreach($daysOfWeek["Quinta"] as $days){
            if(isset($_GET['status']) && !empty($_GET['status'])){
                $query = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o $logistic_or_operator INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE o.order_date LIKE :order_date AND o.order_status = :status $per_product $per_user $not_afi_and");
                $query->execute(['order_date' => $days.'%', 'status' => $per_status]);
            }else{
                $query = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o $logistic_or_operator INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE o.order_date LIKE :order_date AND o.order_status = 0 $per_product $per_user $not_afi_and");
                $query->execute(['order_date' => $days.'%']);
            } 
            $thu += $query->fetch()['total'];
        }  
    
        $fri = 0;
        foreach($daysOfWeek["Sexta"] as $days){
            if(isset($_GET['status']) && !empty($_GET['status'])){
                $query = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o $logistic_or_operator INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE o.order_date LIKE :order_date AND o.order_status = :status $per_product $per_user $not_afi_and");
                $query->execute(['order_date' => $days.'%', 'status' => $per_status]);
            }else{
                $query = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o $logistic_or_operator INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE o.order_date LIKE :order_date AND o.order_status = 0 $per_product $per_user $not_afi_and");
                $query->execute(['order_date' => $days.'%']);
            } 
            $fri += $query->fetch()['total'];
        }
    
        $sat = 0;
        foreach($daysOfWeek["Sábado"] as $days){
            if(isset($_GET['status']) && !empty($_GET['status'])){
                $query = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o $logistic_or_operator INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE o.order_date LIKE :order_date AND o.order_status = :status $per_product $per_user $not_afi_and");
                $query->execute(['order_date' => $days.'%', 'status' => $per_status]);
            }else{
                $query = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o $logistic_or_operator INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE o.order_date LIKE :order_date AND o.order_status = 0 $per_product $per_user $not_afi_and");
                $query->execute(['order_date' => $days.'%']);
            } 
            $sat += $query->fetch()['total'];
        }  
    }else{

        foreach($daysOfWeek["Domingo"] as $days){
            if(isset($_GET['status']) && !empty($_GET['status'])){
                $query = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o $per_local_operator INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE o.order_date LIKE :order_date AND o.order_status = :status   AND o.user__id = :user__id $per_product $not_afi_and");
                $query->execute(['order_date' => $days.'%', 'status' => $per_status, 'user__id' => $user__id]);
            }else{
                $query = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o $per_local_operator INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE o.order_date LIKE :order_date AND o.order_status = 0 AND o.user__id = :user__id $per_product $not_afi_and");
                $query->execute(['order_date' => $days.'%', 'user__id' => $user__id]);
            } 
            $sun += $query->fetch()['total'];
        }  
    
        $mon = 0;
        foreach($daysOfWeek["Segunda"] as $days){
            if(isset($_GET['status']) && !empty($_GET['status'])){
                $query = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o $per_local_operator INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE o.order_date LIKE :order_date AND o.order_status = :status   AND o.user__id = :user__id $per_product $not_afi_and");
                $query->execute(['order_date' => $days.'%', 'status' => $per_status, 'user__id' => $user__id]);
            }else{
                $query = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o $per_local_operator INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE o.order_date LIKE :order_date AND o.order_status = 0 AND o.user__id = :user__id $per_product $not_afi_and");
                $query->execute(['order_date' => $days.'%', 'user__id' => $user__id]);
            } 
            $mon += $query->fetch()['total'];
        }  
    
        $tue = 0;
        foreach($daysOfWeek["Terça"] as $days){
            if(isset($_GET['status']) && !empty($_GET['status'])){
                $query = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o $per_local_operator INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE o.order_date LIKE :order_date AND o.order_status = :status   AND o.user__id = :user__id $per_product $not_afi_and");
                $query->execute(['order_date' => $days.'%', 'status' => $per_status, 'user__id' => $user__id]);
            }else{
                $query = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o $per_local_operator INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE o.order_date LIKE :order_date AND o.order_status = 0 AND o.user__id = :user__id $per_product $not_afi_and");
                $query->execute(['order_date' => $days.'%', 'user__id' => $user__id]);
            } 
            $tue += $query->fetch()['total'];
        }  
    
        $wed = 0;
        foreach($daysOfWeek["Quarta"] as $days){
            if(isset($_GET['status']) && !empty($_GET['status'])){
                $query = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o $per_local_operator INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE o.order_date LIKE :order_date AND o.order_status = :status   AND o.user__id = :user__id $per_product $not_afi_and");
                $query->execute(['order_date' => $days.'%', 'status' => $per_status, 'user__id' => $user__id]);
            }else{
                $query = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o $per_local_operator INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE o.order_date LIKE :order_date AND o.order_status = 0 AND o.user__id = :user__id $per_product $not_afi_and");
                $query->execute(['order_date' => $days.'%', 'user__id' => $user__id]);
            } 
            $wed += $query->fetch()['total'];
        }  
        
        $thu = 0;
        foreach($daysOfWeek["Quinta"] as $days){
            if(isset($_GET['status']) && !empty($_GET['status'])){
                $query = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o $per_local_operator INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE o.order_date LIKE :order_date AND o.order_status = :status   AND o.user__id = :user__id $per_product $not_afi_and");
                $query->execute(['order_date' => $days.'%', 'status' => $per_status, 'user__id' => $user__id]);
            }else{
                $query = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o $per_local_operator INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE o.order_date LIKE :order_date AND o.order_status = 0 AND o.user__id = :user__id $per_product $not_afi_and");
                $query->execute(['order_date' => $days.'%', 'user__id' => $user__id]);
            } 
            $thu += $query->fetch()['total'];
        }  
    
        $fri = 0;
        foreach($daysOfWeek["Sexta"] as $days){
            if(isset($_GET['status']) && !empty($_GET['status'])){
                $query = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o $per_local_operator INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE o.order_date LIKE :order_date AND o.order_status = :status   AND o.user__id = :user__id $per_product $not_afi_and");
                $query->execute(['order_date' => $days.'%', 'status' => $per_status, 'user__id' => $user__id]);
            }else{
                $query = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o $per_local_operator INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE o.order_date LIKE :order_date AND o.order_status = 0 AND o.user__id = :user__id $per_product $not_afi_and");
                $query->execute(['order_date' => $days.'%', 'user__id' => $user__id]);
            } 
            $fri += $query->fetch()['total'];
        }
    
        $sat = 0;
        foreach($daysOfWeek["Sábado"] as $days){
            if(isset($_GET['status']) && !empty($_GET['status'])){
                $query = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o $per_local_operator INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE o.order_date LIKE :order_date AND o.order_status = :status   AND o.user__id = :user__id $per_product $not_afi_and");
                $query->execute(['order_date' => $days.'%', 'status' => $per_status, 'user__id' => $user__id]);
            }else{
                $query = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders AS o $per_local_operator INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE o.order_date LIKE :order_date AND o.order_status = 0 AND o.user__id = :user__id $per_product $not_afi_and");
                $query->execute(['order_date' => $days.'%', 'user__id' => $user__id]);
            } 
            $sat += $query->fetch()['total'];
        }  
    }

//}      
    
    # Calculo total de comissão de operador logístico 
    $get_order = $conn->prepare("SELECT o.order_id, o.order_status, o.client_address, loo.responsible_id, o.user__id, o.order_payment_method, o.order_final_price, o.credit_times FROM orders_all o INNER JOIN local_operations_orders loo ON loo.order_id = o.order_id");
    $get_order->execute();
    
    $orders = $get_order->fetchAll(\PDO::FETCH_ASSOC);
    $sales = 0; 
    $total_tax = 0;
    
    if($get_order->rowCount() > 0){
        foreach($orders as $order){
            // METÓDO PARA PEGAR CIDADE DA VENDA
            $city = explode("<br>", $order['client_address']);
            $city = explode(", ", $city[3]); 
            $city = $city[0];
            // PEGAR OPERAÇÃO LOCAL DE ACORDO COM A CIDADE
            $get_locale = $conn->prepare('SELECT * FROM operations_locales WHERE city = :city');
            $get_locale->execute(array('city' =>  $city ));

            $id_locale = $get_locale->fetch(\PDO::FETCH_ASSOC);

            if($order['order_status'] == 3) {

                $get_commission = $conn->prepare('SELECT complete_delivery_tax FROM operations_delivery_taxes WHERE operation_locale = :locale AND operator_id = :operator'); 
                $get_commission->execute(array('operator' => $order['responsible_id'], 'locale' => $id_locale['id']));

                if($get_commission->rowCount() > 0){
                    $deliveries_tax = $get_commission->fetchAll(\PDO::FETCH_ASSOC);
                    foreach($deliveries_tax as $tax){
                        $sales += $tax['complete_delivery_tax'];
                    }
                }


               # Calculo de taxa financeira do cartão 
                $get_operations_id = $conn->prepare("SELECT lo.*, u.created_at FROM logistic_operator lo INNER JOIN users u ON lo.user_id = u.user__id WHERE lo.operator_id = :user__id");
                $get_operations_id->execute(['user__id' => $order['responsible_id']]);

                $operator = $get_operations_id->fetch();
                if($get_operations_id->rowCount() > 0){
                    if($order["order_payment_method"] == "credit"){
                        $string_to_get_value_from_times = "credito_tax_".$order['credit_times']."x";
                        @$total_tax += ($operator[$string_to_get_value_from_times] * $order['order_final_price']) / 100;
                    }
                    if($order['order_payment_method'] == "debit"){
                        @$total_tax += $order['order_final_price'] * ($operator['debito_tax'] / 100);
                    }
                }

            }
        }
    }

    if($_SESSION['UserPlan'] == 5){
        # Recalculo de Comissão e porcentagem
        if ($faturamento != 0) {
            $after_comission = $comissao; // Comisão de usuários apenas
            $comissao = $comissao + $sales + $total_tax;
            $comissao_por100 = ($comissao * 100) / $faturamento;
        }else{
            $comissao = 0;
            $comissao_por100 = 0;
        }
    }

    # Dados para grafíco de produtos entregue por operação local
    $get_operations = $conn->prepare("SELECT DISTINCT loo.operation_id, lo.* FROM local_operations AS lo INNER JOIN local_operations_orders AS loo ON loo.operation_id = lo.operation_id INNER JOIN orders_all AS o ON o.order_id = loo.order_id AND o.order_status = :status WHERE lo.operation_active = 1 $per_product");
    $get_operations->execute(['status' => $per_status]);
    
    $operations = $get_operations->fetchAll(\PDO::FETCH_ASSOC);
    
    $operations_name = [];
    foreach($operations as $operation){
        array_push($operations_name, $operation['operation_name']);
    }
    
    // * TOTAL DE PEDIDOS PARA CALCULO POSTERIOR DE PORCENTAGEM
    $total_sales = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders_all AS o INNER JOIN local_operations_orders loo ON loo.order_id = o.order_id ". $not_afi_and);
    $total_sales->execute();
    $count_all_sales = $total_sales->fetch(\PDO::FETCH_ASSOC)['total'];

    // * TOTAL DE PEDIDOS CONCLUIDOS POR OPERAÇÃO LOCAL
    $total_by_operation = [];

    foreach ($operations as $operation) {
        $total_orders = $conn->prepare("SELECT COUNT(o.order_id) AS total FROM orders_all AS o INNER JOIN local_operations_orders loo ON loo.order_id = o.order_id WHERE loo.operation_id = :operation_id ". $not_afi_and);
        $total_orders->execute(['operation_id' => $operation['operation_id']]);
        $total = $total_orders->fetch(\PDO::FETCH_ASSOC)['total'];

        // Calculo de porcentagem para essa operação local
        if($total > 0){
            array_push($total_by_operation, [
                'total' => $total,
                'percent' => number_format(($total / $count_all_sales) * 100, 2)
            ]);
        }else{
            array_push($total_by_operation, [
                'total' => 0,
                'percent' => 0
            ]);
        }
    }

    if($_SESSION['UserPlan'] == 5){
        # Cálculo de items para tabela de despesas total
        $total_percent_and_values = [];
    
        if(isset($after_comission) && $after_comission > 0){
            // COMISSÃO DE USUÁRIOS
            array_push($total_percent_and_values, [
                'description' => 'Comissões Usuários',
                'value' => number_format($after_comission, 2),
                'percent' => number_format(($after_comission / $comissao) * 100, 2)
            ]);

            // TAXAS FINANCEIRAS
            array_push($total_percent_and_values, [
                'description' => 'Comissões Operadores',
                'value' => number_format($sales, 2),
                'percent' => number_format(($sales / $comissao) * 100, 2)
            ]);

            // TAXAS LOGÍSTICAS
            array_push($total_percent_and_values, [
                'description' => 'Taxas Financeiras',
                'value' => number_format($total_tax, 2),
                'percent' => number_format(($total_tax / $comissao) * 100, 2)
            ]);

        }else{
            // COMISSÃO DE USUÁRIOS
            array_push($total_percent_and_values, [
                'description' => 'Comissões Usuários',
                'value' => number_format(0, 2),
                'percent' => 0.00
            ]);

            // TAXAS FINANCEIRAS
            array_push($total_percent_and_values, [
                'description' => 'Comissões Operadores',
                'value' => number_format(0, 2),
                'percent' => 0.00
            ]);
        
            // TAXAS LOGÍSTICAS
            array_push($total_percent_and_values, [
                'description' => 'Taxas Financeiras',
                'value' => number_format(0, 2),
                'percent' => 0.00
            ]);
        
        }

    }

    /**
     * Status List
     * 
     * 0 = Agendada
     * 1 = Reagendada
     * 2 = Atrasada
     * 3 = Completa
     * 4 = Frustrada
     * 5 = Cancelada
     * 6 = Reembolsada
     * 
     * 
    */
    

    # 0 - Agendada 
    $get_sales_status0 = $conn->prepare("SELECT A.Q FROM (SELECT COUNT(order_id) AS Q, order_delivery_date, order_status FROM orders_zer AS o WHERE o.order_status = 0 $not_afi_and ) as A");   
    $get_sales_status0->execute();
    $sales0 = $get_sales_status0->fetch();

    # 1 - Reagendada
    $get_sales_status1 = $conn->prepare("SELECT A.Q FROM (SELECT COUNT(order_id) AS Q, order_delivery_date, order_status FROM orders_one AS o WHERE o.order_status = 1 $not_afi_and ) as A");
    $get_sales_status1->execute();
    $sales1 = $get_sales_status1->fetch();

    # 2 - Atrasada
    $get_sales_status2 = $conn->prepare("SELECT A.Q FROM (SELECT COUNT(order_id) AS Q, order_delivery_date, order_status FROM orders_two AS o WHERE o.order_status = 2 $not_afi_and ) as A");
    $get_sales_status2->execute();
    $sales2 = $get_sales_status2->fetch();


    # 3 - Completa
    $get_sales_status3 = $conn->prepare("SELECT A.Q FROM (SELECT COUNT(order_id) AS Q, order_delivery_date, order_status FROM orders_thr AS o WHERE o.order_status = 3 $not_afi_and ) as A");
    $get_sales_status3->execute();
    $sales3 = $get_sales_status3->fetch();


    # 4 - Frustrada
    $get_sales_status4 = $conn->prepare("SELECT A.Q FROM (SELECT COUNT(order_id) AS Q, order_delivery_date, order_status FROM orders_fou AS o WHERE o.order_status = 4 $not_afi_and ) as A");
    $get_sales_status4->execute();
    $sales4 = $get_sales_status4->fetch();

    # 5 - Cancelada
    $get_sales_status5 = $conn->prepare("SELECT A.Q FROM (SELECT COUNT(order_id) AS Q, order_delivery_date, order_status FROM orders_fiv AS o WHERE o.order_status = 5 $not_afi_and ) as A");
    $get_sales_status5->execute();
    $sales5 = $get_sales_status5->fetch();


    # 9 - Reembolsada
    $get_sales_status9 = $conn->prepare("SELECT A.Q FROM (SELECT COUNT(order_id) AS Q, order_delivery_date, order_status FROM orders_nine AS o WHERE o.order_status = 9 $not_afi_and ) as A");
    $get_sales_status9->execute();
    $sales9 = $get_sales_status9->fetch();
 
    # 10 - Confirmado
    $get_sales_status10 = $conn->prepare("SELECT A.Q FROM (SELECT COUNT(order_id) AS Q, order_delivery_date, order_status FROM orders_ten AS o WHERE o.order_status = 10 $not_afi_and ) as A");
    $get_sales_status10->execute();
    $sales10 = $get_sales_status10->fetch();

    # 11 - Em Aberto
    $get_sales_status11 = $conn->prepare("SELECT A.Q FROM (SELECT COUNT(order_id) AS Q, order_delivery_date, order_status FROM orders_eleven AS o WHERE o.order_status = 11 $not_afi_and ) as A");
    $get_sales_status11->execute();
    $sales11 = $get_sales_status11->fetch();  

    # 11 - Indisponível
    $get_sales_status12 = $conn->prepare("SELECT A.Q FROM (SELECT COUNT(order_id) AS Q, order_delivery_date, order_status FROM orders_twelve AS o WHERE o.order_status = 12 $not_afi_and ) as A");
    $get_sales_status12->execute();
    $sales12 = $get_sales_status12->fetch();  

    $haveDataFromGraphsSales = $sales0[0] + $sales1[0] + $sales2[0] + $sales3[0] + $sales4[0] + $sales5[0] + $sales9[0] + $sales10[0] + $sales11[0] + $sales12[0]; 
    $haveDataFromDaysOfWeek = $sun + $mon + $tue + $wed + $thu + $fri + $sat;

    if(!isset($_GET['manutenção'])) {
        echo '</pre>';
    } 