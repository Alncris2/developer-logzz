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

    $create_temp_table = $conn->prepare('DROP TABLE IF EXISTS orders_six');
    $create_temp_table->execute(array());



    $create_temp_table = $conn->prepare('CREATE TEMPORARY TABLE orders_all SELECT * FROM orders WHERE (user__id = :user__id AND product_id IN (:filter_by_product)) AND (order_date BETWEEN :date_init AND :date_end AND order_status = :filter_by_status) AND order_status = 3');
    $create_temp_table->execute(array('user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end, 'filter_by_product' => $filter_by_product, 'filter_by_status' => $filter_by_status));

    $create_temp_table = $conn->prepare('CREATE TEMPORARY TABLE orders_zer SELECT * FROM orders_all WHERE user__id = :user__id AND product_id = :filter_by_product');
    $create_temp_table->execute(array('user__id' => $user__id, 'filter_by_product' => $filter_by_product));

    $create_temp_table = $conn->prepare('CREATE TEMPORARY TABLE orders_one SELECT * FROM orders_all WHERE user__id = :user__id AND product_id = :filter_by_product');
    $create_temp_table->execute(array('user__id' => $user__id, 'filter_by_product' => $filter_by_product));

    $create_temp_table = $conn->prepare('CREATE TEMPORARY TABLE orders_two SELECT * FROM orders_all WHERE user__id = :user__id AND product_id = :filter_by_product');
    $create_temp_table->execute(array('user__id' => $user__id, 'filter_by_product' => $filter_by_product));

    $create_temp_table = $conn->prepare('CREATE TEMPORARY TABLE orders_thr SELECT * FROM orders_all WHERE user__id = :user__id AND product_id = :filter_by_product');
    $create_temp_table->execute(array('user__id' => $user__id, 'filter_by_product' => $filter_by_product));

    $create_temp_table = $conn->prepare('CREATE TEMPORARY TABLE orders_fou SELECT * FROM orders_all WHERE user__id = :user__id AND product_id = :filter_by_product');
    $create_temp_table->execute(array('user__id' => $user__id, 'filter_by_product' => $filter_by_product));

    $create_temp_table = $conn->prepare('CREATE TEMPORARY TABLE orders_fiv SELECT * FROM orders_all WHERE user__id = :user__id AND product_id = :filter_by_product');
    $create_temp_table->execute(array('user__id' => $user__id, 'filter_by_product' => $filter_by_product));

    $create_temp_table = $conn->prepare('CREATE TEMPORARY TABLE orders_six SELECT * FROM orders_all WHERE user__id = :user__id AND product_id = :filter_by_product');
    $create_temp_table->execute(array('user__id' => $user__id, 'filter_by_product' => $filter_by_product));

    
    $money_     = 'money';
    $credit_    = 'credit';
    $debit_     = 'debit';
    $pix_       = 'pix';

    # Quantidade de Pagamentos em Dinheiro
    $get_money_payment = $conn->prepare('SELECT A.quant FROM (SELECT COUNT(order_payment_method) AS quant, order_delivery_date, order_payment_method AS method FROM orders_thr WHERE order_payment_method = "money") as A');
    
    $get_money_payment->execute();

    if ($get_money_payment->rowCount() < 1) {  $a = 0;  } else {  $money_payment = $get_money_payment->fetch(); $a = $money_payment['quant'];  }

    # Quantidade de Pagamentos em Crédito
    $get_credit_payment = $conn->prepare('SELECT A.quant FROM (SELECT COUNT(order_payment_method) AS quant, order_delivery_date, order_payment_method AS method FROM orders_thr WHERE order_payment_method = "credit") as A');

    $get_credit_payment->execute();

    if ($get_credit_payment->rowCount() < 1) {  $b = 0;  } else {  $credit_payment = $get_credit_payment->fetch(); $b = $credit_payment['quant'];  }

    # Quantidade de Pagamentos em Débito
    $get_debit_payment = $conn->prepare('SELECT A.quant FROM (SELECT COUNT(order_payment_method) AS quant, order_delivery_date, order_payment_method AS method FROM orders_thr WHERE order_payment_method = "debit") as A');

    $get_debit_payment->execute();

    if ($get_debit_payment->rowCount() < 1) {  $c = 0;  } else {  $debit_payment = $get_debit_payment->fetch(); $c = $debit_payment['quant'];  }

    # Quantidade de Pagamentos em PIX
    $get_pix_payment = $conn->prepare('SELECT A.quant FROM (SELECT COUNT(order_payment_method) AS quant, order_delivery_date, order_payment_method AS method FROM orders_thr WHERE order_payment_method = "pix") as A');

    $get_pix_payment->execute();

    if ($get_pix_payment->rowCount() < 1) {  $d = 0;  } else { $pix_payment = $get_pix_payment->fetch(); $d = $pix_payment['quant'];  }


    # Cálculo Faturamento
    $faturamento = $conn->prepare('SELECT A.total FROM (SELECT SUM(order_final_price) AS total, order_delivery_date FROM orders_all) as A');
    $faturamento->execute();
       
    if($faturamento->rowCount() == 0){
        $faturamento= array('0' => 0);
    } else {
        $faturamento = $faturamento->fetch();
    }

    $vendas = $conn->prepare('SELECT COUNT(*), order_delivery_date FROM orders_all');
    $vendas->execute();

    if( $vendas->rowCount() == 0){
        $vendas = array('0' => 0);
    } else {
        $vendas =  $vendas->fetch();
    }

    $produtos = $conn->prepare('SELECT SUM(sale_quantity), order_delivery_date FROM orders_all INNER JOIN sales ON orders_all.sale_id = sales.sale_id');
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
    
    $clientes = $conn->prepare('SELECT * FROM orders_all GROUP BY order_number');
    $clientes->execute();
    $clientes = $clientes->rowCount();

    $reembolsos = $conn->prepare('SELECT * FROM orders_all WHERE order_status = 6 GROUP BY order_number');
    $reembolsos->execute();
    $reembolsos = $reembolsos->rowCount();

    if ($faturamento != 0) {
        
    # Cálculo Comissão
    $comission = $conn->prepare('SELECT A.total FROM (SELECT SUM(order_liquid_value) AS total FROM orders_all) as A');
    $comission->execute();

    if($comission->rowCount() == 0){
        $comission= array('0' => 0);
    } else {
        $comission = $comission->fetch();
    }
    
    if( $faturamento[0] == 0){
        $comissao_por100 = 0;
        $comissao = 0;
    } else {
        $comissao = $comission[0];
        $comissao_por100 = ($comission[0] * 100) / $faturamento[0];
    }

    } else {

        $entrega_total = 0;
        $taxa_total = 0;
        $comissao = 0;
        $comissao_por100 = 0;

    }






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
    $get_sales_status0 = $conn->prepare('SELECT A.Q FROM (SELECT COUNT(order_id) AS Q, order_delivery_date, order_status FROM orders_all WHERE order_status = 0) as A');
    $get_sales_status0->execute();
    $sales0 = $get_sales_status0->fetch();

    # 1 - Reagendada
    $get_sales_status1 = $conn->prepare('SELECT A.Q FROM (SELECT COUNT(order_id) AS Q, order_delivery_date, order_status FROM orders_all WHERE order_status = 1) as A');
    $get_sales_status1->execute();
    $sales1 = $get_sales_status1->fetch();

    # 2 - Atrasada
    $get_sales_status2 = $conn->prepare('SELECT A.Q FROM (SELECT COUNT(order_id) AS Q, order_delivery_date, order_status FROM orders_all WHERE order_status = 2) as A');
    $get_sales_status2->execute();
    $sales2 = $get_sales_status2->fetch();


    # 3 - Completa
    $get_sales_status3 = $conn->prepare('SELECT A.Q FROM (SELECT COUNT(order_id) AS Q, order_delivery_date, order_status FROM orders_all WHERE order_status = 3) as A');
    $get_sales_status3->execute();
    $sales3 = $get_sales_status3->fetch();


    # 4 - Frustrada
    $get_sales_status4 = $conn->prepare('SELECT A.Q FROM (SELECT COUNT(order_id) AS Q, order_delivery_date, order_status FROM orders_all WHERE order_status = 4) as A');
    $get_sales_status4->execute();
    $sales4 = $get_sales_status4->fetch();

    # 5 - Cancelada
    $get_sales_status5 = $conn->prepare('SELECT A.Q FROM (SELECT COUNT(order_id) AS Q, order_delivery_date, order_status FROM orders_all WHERE order_status = 5) as A');
    $get_sales_status5->execute();
    $sales5 = $get_sales_status5->fetch();


    # 6 - Reembolsada
    $get_sales_status6 = $conn->prepare('SELECT A.Q FROM (SELECT COUNT(order_id) AS Q, order_delivery_date, order_status FROM orders_all WHERE order_status = 6) as A');
    $get_sales_status6->execute();
    $sales6 = $get_sales_status6->fetch();
?>