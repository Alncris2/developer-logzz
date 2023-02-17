
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

    $create_temp_table = $conn->prepare('DROP TABLE IF EXISTS orders_nine');
    $create_temp_table->execute(array());

    $create_temp_table = $conn->prepare('DROP TABLE IF EXISTS orders_ten');
    $create_temp_table->execute(array());

    $create_temp_table = $conn->prepare('DROP TABLE IF EXISTS orders_eleven');
    $create_temp_table->execute(array());

    $create_temp_table = $conn->prepare('DROP TABLE IF EXISTS orders_twelve');



    $create_temp_table = $conn->prepare('CREATE TEMPORARY TABLE orders_all SELECT o.*, loo.responsible_id FROM orders o INNER JOIN local_operations_orders loo ON loo.order_id = o.order_id WHERE (loo.responsible_id = :user__id AND o.external_id IS NULL) AND o.order_date BETWEEN :date_init AND :date_end');
    $create_temp_table->execute(array('user__id' => $operator_id, 'date_init' => $date_init, 'date_end' => $date_end));

    $create_temp_table = $conn->prepare('CREATE TEMPORARY TABLE orders_zer SELECT o.* FROM orders o INNER JOIN local_operations_orders loo ON o.order_id = loo.order_id WHERE loo.responsible_id = :user__id AND (o.order_status = 0 AND o.order_date BETWEEN :date_init AND :date_end)');
    $create_temp_table->execute(array('user__id' => $operator_id, 'date_init' => $date_init, 'date_end' => $date_end));

    $create_temp_table = $conn->prepare('CREATE TEMPORARY TABLE orders_one SELECT o.* FROM orders o INNER JOIN local_operations_orders loo ON o.order_id = loo.order_id WHERE loo.responsible_id = :user__id AND (o.order_status = 1 AND o.order_date BETWEEN :date_init AND :date_end)');
    $create_temp_table->execute(array('user__id' => $operator_id, 'date_init' => $date_init, 'date_end' => $date_end));

    $create_temp_table = $conn->prepare('CREATE TEMPORARY TABLE orders_two SELECT o.* FROM orders o INNER JOIN local_operations_orders loo ON o.order_id = loo.order_id WHERE loo.responsible_id = :user__id AND (o.order_status = 2 AND o.order_date BETWEEN :date_init AND :date_end)');
    $create_temp_table->execute(array('user__id' => $operator_id, 'date_init' => $date_init, 'date_end' => $date_end));

    $create_temp_table = $conn->prepare('CREATE TEMPORARY TABLE orders_thr SELECT o.*, loo.responsible_id FROM orders o INNER JOIN local_operations_orders loo ON loo.order_id = o.order_id WHERE loo.responsible_id = :user__id AND (o.order_status = 3 AND o.order_delivery_date BETWEEN :date_init AND :date_end)');
    $create_temp_table->execute(array('user__id' => $operator_id, 'date_init' => $date_init, 'date_end' => $date_end));

    $create_temp_table = $conn->prepare('CREATE TEMPORARY TABLE orders_fou SELECT o.* FROM orders o INNER JOIN local_operations_orders loo ON o.order_id = loo.order_id WHERE loo.responsible_id = :user__id AND (o.order_status = 4 AND o.order_date BETWEEN :date_init AND :date_end)');
    $create_temp_table->execute(array('user__id' => $operator_id, 'date_init' => $date_init, 'date_end' => $date_end));

    $create_temp_table = $conn->prepare('CREATE TEMPORARY TABLE orders_fiv SELECT o.* FROM orders o INNER JOIN local_operations_orders loo ON o.order_id = loo.order_id WHERE loo.responsible_id = :user__id AND (o.order_status = 5 AND o.order_date BETWEEN :date_init AND :date_end)');
    $create_temp_table->execute(array('user__id' => $operator_id, 'date_init' => $date_init, 'date_end' => $date_end));

    $create_temp_table = $conn->prepare('CREATE TEMPORARY TABLE orders_six SELECT o.* FROM orders o INNER JOIN local_operations_orders loo ON o.order_id = loo.order_id WHERE loo.responsible_id = :user__id AND (o.order_status = 6 AND o.order_date BETWEEN :date_init AND :date_end)');
    $create_temp_table->execute(array('user__id' => $operator_id, 'date_init' => $date_init, 'date_end' => $date_end));

    $create_temp_table = $conn->prepare('CREATE TEMPORARY TABLE orders_nine SELECT o.* FROM orders o INNER JOIN local_operations_orders loo ON o.order_id = loo.order_id WHERE loo.responsible_id = :user__id AND (o.order_status = 9 AND o.order_date BETWEEN :date_init AND :date_end)');
    $create_temp_table->execute(array('user__id' => $operator_id, 'date_init' => $date_init, 'date_end' => $date_end));

    $create_temp_table = $conn->prepare('CREATE TEMPORARY TABLE orders_ten SELECT o.* FROM orders o INNER JOIN local_operations_orders loo ON o.order_id = loo.order_id WHERE loo.responsible_id = :user__id AND (o.order_status = 10 AND o.order_date BETWEEN :date_init AND :date_end)');
    $create_temp_table->execute(array('user__id' => $operator_id, 'date_init' => $date_init, 'date_end' => $date_end));

    $create_temp_table = $conn->prepare('CREATE TEMPORARY TABLE orders_eleven SELECT o.* FROM orders o INNER JOIN local_operations_orders loo ON o.order_id = loo.order_id WHERE loo.responsible_id = :user__id AND (o.order_status = 11 AND o.order_date BETWEEN :date_init AND :date_end)');
    $create_temp_table->execute(array('user__id' => $operator_id, 'date_init' => $date_init, 'date_end' => $date_end));

    $create_temp_table = $conn->prepare('CREATE TEMPORARY TABLE orders_twelve SELECT o.* FROM orders o INNER JOIN local_operations_orders loo ON o.order_id = loo.order_id WHERE loo.responsible_id = :user__id AND (o.order_status = 12 AND o.order_date BETWEEN :date_init AND :date_end)');
    $create_temp_table->execute(array('user__id' => $operator_id, 'date_init' => $date_init, 'date_end' => $date_end));

    
    $money_     = 'money';
    $credit_    = 'credit';
    $debit_     = 'debit';
    $pix_       = 'pix';

    # Quantidade de Pagamentos em Dinheiro
    $get_money_payment = $conn->prepare('SELECT A.quant FROM (SELECT COUNT(order_payment_method) AS quant, order_datetime, order_payment_method AS method FROM orders_thr WHERE order_payment_method = "money") as A');
    
    $get_money_payment->execute();

    if ($get_money_payment->rowCount() < 1) {  $a = 0;  } else {  $money_payment = $get_money_payment->fetch(); $a = $money_payment['quant']; }

    # Quantidade de Pagamentos em Crédito
    $get_credit_payment = $conn->prepare('SELECT A.quant FROM (SELECT COUNT(order_payment_method) AS quant, order_datetime, order_payment_method AS method FROM orders_thr WHERE order_payment_method = "credit") as A');

    $get_credit_payment->execute();

    if ($get_credit_payment->rowCount() < 1) {  $b = 0;  } else {  $credit_payment = $get_credit_payment->fetch(); $b = $credit_payment['quant'];  }

    # Quantidade de Pagamentos em Débito
    $get_debit_payment = $conn->prepare('SELECT A.quant FROM (SELECT COUNT(order_payment_method) AS quant, order_datetime, order_payment_method AS method FROM orders_thr WHERE order_payment_method = "debit") as A');

    $get_debit_payment->execute();

    if ($get_debit_payment->rowCount() < 1) {  $c = 0;  } else {  $debit_payment = $get_debit_payment->fetch(); $c = $debit_payment['quant'];  }

    # Quantidade de Pagamentos em PIX
    $get_pix_payment = $conn->prepare('SELECT A.quant FROM (SELECT COUNT(order_payment_method) AS quant, order_datetime, order_payment_method AS method FROM orders_thr WHERE order_payment_method = "pix") as A');

    $get_pix_payment->execute();

    if ($get_pix_payment->rowCount() < 1) {  $d = 0;  } else { $pix_payment = $get_pix_payment->fetch(); $d = $pix_payment['quant'];  }


    $vendas = $conn->prepare('SELECT COUNT(*), order_datetime FROM orders_all');
    $vendas->execute();

    if( $vendas->rowCount() == 0){
        $vendas = array('0' => 0);
    } else {
        $vendas =  $vendas->fetch();
    }

    $produtos = $conn->prepare('SELECT SUM(sale_quantity), order_datetime FROM orders_all INNER JOIN sales ON orders_all.sale_id = sales.sale_id WHERE orders_all.order_status = 3');
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
    
    $clientes = $conn->prepare('SELECT * FROM orders_all WHERE order_status = 3 GROUP BY order_number');
    $clientes->execute();
    $clientes = $clientes->rowCount();

    $reembolsos = $conn->prepare('SELECT * FROM orders_all WHERE order_status = 6 GROUP BY order_number');
    $reembolsos->execute();
    $reembolsos = $reembolsos->rowCount();

    $entrega_total = 0;
    $taxa_total = 0;
    $comissao = 0;
    $comissao_por100 = 0;

    # Cálculo Faturamento
    $faturamento = $conn->prepare('SELECT A.total FROM (SELECT SUM(order_final_price) AS total, order_datetime FROM orders_all WHERE orders_all.order_status = 3) as A');
    $faturamento->execute();

    if($faturamento->rowCount() == 0){
        $faturamento= array('0' => 0);
    } else {
        $faturamento = $faturamento->fetch();
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
    $get_sales_status0 = $conn->prepare('SELECT A.Q FROM (SELECT COUNT(order_id) AS Q, order_datetime, order_status FROM orders_zer) as A');
    $get_sales_status0->execute();
    $sales0 = $get_sales_status0->fetch();

    # 1 - Reagendada
    $get_sales_status1 = $conn->prepare('SELECT A.Q FROM (SELECT COUNT(order_id) AS Q, order_datetime, order_status FROM orders_one) as A');
    $get_sales_status1->execute();
    $sales1 = $get_sales_status1->fetch();

    # 2 - Atrasada
    $get_sales_status2 = $conn->prepare('SELECT A.Q FROM (SELECT COUNT(order_id) AS Q, order_datetime, order_status FROM orders_two) as A');
    $get_sales_status2->execute();
    $sales2 = $get_sales_status2->fetch();


    # 3 - Completa
    $get_sales_status3 = $conn->prepare('SELECT A.Q FROM (SELECT COUNT(order_id) AS Q, order_datetime, order_status FROM orders_thr) as A');
    $get_sales_status3->execute();
    $sales3 = $get_sales_status3->fetch();


    # 4 - Frustrada
    $get_sales_status4 = $conn->prepare('SELECT A.Q FROM (SELECT COUNT(order_id) AS Q, order_datetime, order_status FROM orders_fou) as A');
    $get_sales_status4->execute();
    $sales4 = $get_sales_status4->fetch();

    # 5 - Cancelada
    $get_sales_status5 = $conn->prepare('SELECT A.Q FROM (SELECT COUNT(order_id) AS Q, order_datetime, order_status FROM orders_fiv) as A');
    $get_sales_status5->execute();
    $sales5 = $get_sales_status5->fetch();


    # 6 - Reembolsada
    $get_sales_status6 = $conn->prepare('SELECT A.Q FROM (SELECT COUNT(order_id) AS Q, order_datetime, order_status FROM orders_six) as A');
    $get_sales_status6->execute();
    $sales6 = $get_sales_status6->fetch();


    # 9 - Reembolsada
    $get_sales_status9 = $conn->prepare('SELECT A.Q FROM (SELECT COUNT(order_id) AS Q, order_datetime, order_status FROM orders_nine) as A');
    $get_sales_status9->execute();
    $sales9 = $get_sales_status9->fetch();


    # 10 - Confirmado
    $get_sales_status10 = $conn->prepare('SELECT A.Q FROM (SELECT COUNT(order_id) AS Q, order_datetime, order_status FROM orders_ten) as A');
    $get_sales_status10->execute();
    $sales10 = $get_sales_status10->fetch();


    # 11 - Em Aberto
    $get_sales_status11 = $conn->prepare('SELECT A.Q FROM (SELECT COUNT(order_id) AS Q, order_datetime, order_status FROM orders_eleven) as A');
    $get_sales_status11->execute();
    $sales11 = $get_sales_status11->fetch();


    # 12 - Indisponível
    $get_sales_status12 = $conn->prepare('SELECT A.Q FROM (SELECT COUNT(order_id) AS Q, order_datetime, order_status FROM orders_twelve) as A');
    $get_sales_status12->execute();
    $sales12 = $get_sales_status12->fetch();

