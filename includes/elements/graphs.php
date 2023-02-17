<?php if($_SESSION['UserPlan'] == 5): ?>
    <?php $arr = []; ?>
    <script>
        var ctx = document.getElementById('salesVolumeChart');
        var salesVolumeChart = new Chart(ctx, {
            data: {
                datasets: [{
                    type: 'line',
                    label: 'Receita Total',
                    yAxisID: 'B',
                    data: [
                        <?php
                        $begin = new DateTime($date_init);
                        $end = new DateTime($date_end);
                        $end = $end->modify('+1 dia');

                        $interval = DateInterval::createFromDateString('1 day');
                        $period = new DatePeriod($begin, $interval, $end);

                        foreach ($period as $date) {

                            $date = $date->format("Y-m-d");
                            $order_date = "%" . $date  . "%";
    
                            $get_billing_total = $conn->prepare('SELECT SUM(order_final_price) FROM orders_all WHERE order_delivery_date LIKE :order_date AND order_status = 3');
                            $get_billing_total->execute(array('order_date' => $order_date));
                            $billing = $get_billing_total->fetch();

                            if ($billing[0] == null) {

                                echo $billing_string = 0;
                                echo ", ";
                            } else {

                                echo $billing_string = round($billing[0], 2);
                                echo ", ";
                            }
                        }
                        ?>
                    ],
                    borderColor: 'rgb(47,222,145)',
                    backgroundColor: 'rgb(47,222,145)',
                    tension: 0.4
                }, {
                    type: 'line',
                    label: 'Lucro plataforma',
                    yAxisID: 'B',
                    data: [
                        <?php
                        $begin = new DateTime($date_init);
                        $end = new DateTime($date_end);
                        $end = $end->modify('+1 dia');

                        $interval = DateInterval::createFromDateString('1 day');
                        $period = new DatePeriod($begin, $interval, $end);

                        foreach ($period as $date) {

                            $date = $date->format("Y-m-d");
                            $order_date = "%" . $date  . "%";

                            $get_liquid_total = $conn->prepare('SELECT SUM(o.order_liquid_value) AS total FROM orders_all AS  o WHERE o.order_delivery_date LIKE :order_date AND o.order_status = 3');
                            $get_liquid_total->execute(array('order_date' => $order_date));
                            $sales = $get_liquid_total->fetch();
        
                            $get_billing_total = $conn->prepare('SELECT SUM(o.order_final_price) AS total FROM orders_all AS o WHERE o.order_delivery_date LIKE :order_date AND o.order_status = 3');
                            $get_billing_total->execute(array('order_date' => $order_date));
                            $billing = $get_billing_total->fetch();

                            $get_liquid_total->rowCount() == 0 ? $sales = 0 : $sales = $sales['total'];
                            $get_billing_total->rowCount() == 0 ? $billing = 0 : $billing = $billing['total'];

                            
                            // SOMAR TAXA FINANCEIRA + COMISSÃO OPERADOR LOGÍSTICO
                            // PEGAR ORDER_ID DE CADA PEDIDO 
                            $get_order = $conn->prepare("SELECT o.order_id, o.order_payment_method, o.credit_times, o.order_final_price, o.client_address FROM orders_all AS  o WHERE o.order_delivery_date LIKE :order_date AND o.order_status = 3");
                            $get_order->execute(['order_date' => $order_date]);

                            $total_taxs = 0;
                            $delivery_tax = 0;
                            if($get_order->rowCount() > 0){
                                
                                $all_ids = $get_order->fetchAll(\PDO::FETCH_ASSOC);

                                
                                foreach($all_ids as $order){
                                    $get_operator_id = $conn->prepare("SELECT loo.operation_id, loo.responsible_id FROM local_operations_orders AS loo WHERE loo.order_id = :order_id");
                                    $get_operator_id->execute(['order_id' => $order['order_id']]);

                                    if($get_operator_id->rowCount() > 0){
                                        $operator_id = $get_operator_id->fetch(\PDO::FETCH_ASSOC)['responsible_id'];
                                        
                                        $get_operations_data = $conn->prepare("SELECT lo.*, u.created_at FROM logistic_operator lo INNER JOIN users u ON lo.user_id = u.user__id WHERE lo.operator_id = :user__id");
                                        $get_operations_data->execute(['user__id' => $operator_id]);
                                        $get_operations_data = $get_operations_data->fetch(\PDO::FETCH_ASSOC);

                                        // CALCULO DE TAXA FINANCEIRA DO CARTÃO
                                        if($order['order_payment_method'] == "credit"){
                                            $string_to_get_value_from_times = "credito_tax_".$order['credit_times']."x";
                                            @$total_taxs = $get_operations_data[$string_to_get_value_from_times];
                                        }

                                        if($order['order_payment_method'] == "debit"){
                                            $string_to_get_value_from_times = "credito_tax_".$order['credit_times']."x";
                                            @$total_taxs = $order['order_final_price'] * $get_operations_data['debito_tax'];
                                        }
                                        
                                        // METÓDO PARA PEGAR CIDADE DA VENDA
                                        $city = explode("<br>", $order['client_address']);
                                        $city = explode(", ", $city[3]); 
                                        $city = $city[0];
                                        
                                        
                                        // PEGAR OPERAÇÃO LOCAL DE ACORDO COM A CIDADE
                                        $get_locale = $conn->prepare('SELECT * FROM operations_locales WHERE city LIKE :city');
                                        $get_locale->execute(array('city' => '%' . $city . '%'));
                                        $id_locale = $get_locale->fetch(\PDO::FETCH_ASSOC);
                                        
                                        // CALCULO TAXAS FINANCEIRAS 
                                        $get_commission = $conn->prepare('SELECT complete_delivery_tax FROM operations_delivery_taxes WHERE operation_locale = :locale AND operator_id = :operator'); 
                                        $get_commission->execute(array('operator' => $operator_id, 'locale' => $id_locale['id']));
                                        
                                        if($get_commission->rowCount() > 0){
                                            
                                            $deliveries_tax = $get_commission->fetchAll(\PDO::FETCH_ASSOC);
                                            foreach($deliveries_tax as $tax){
                                                $delivery_tax += $tax['complete_delivery_tax'];
                                            }
                                        }

                                    }
                                }
                            }

                            $expenses_platform = ($sales + $total_taxs + $delivery_tax);
                            
                            echo $liquid_string = round($billing - $expenses_platform, 2) . ", ";
                            
                                    
                                
                            
                        }
                        ?>
                    ],
                    borderColor: 'rgb(11,53,43)',
                    backgroundColor: 'rgb(11,53,43)',
                    tension: 0.4
                }, {
                    type: 'bar',
                    label: 'Produtos Vendidos',
                    yAxisID: 'A',
                    data: [
                        <?php
                        $begin = new DateTime($date_init);
                        $end = new DateTime($date_end);
                        $end = $end->modify('+1 dia');

                        $interval = DateInterval::createFromDateString('1 day');
                        $period = new DatePeriod($begin, $interval, $end);

                        foreach ($period as $date) {

                            $date = $date->format("Y-m-d");
                            $order_date = "%" . $date  . "%";
                            $get_orders_total = $conn->prepare('SELECT SUM(sale_quantity) FROM sales AS s INNER JOIN orders_all AS o ON s.sale_id = o.sale_id WHERE o.order_delivery_date LIKE :order_date AND o.order_status = 3');

                            $get_orders_total->execute(array('order_date' => $order_date));

                            $orders = $get_orders_total->fetch();

                            if ($orders[0] == null) {
                                echo $orders = 0;
                                echo ", ";
                            } else {

                                echo $orders = $orders['0'];
                                echo ", ";
                            }
                        }
                        ?>
                    ],
                    borderColor: 'rgb(212 249 232)',
                    backgroundColor: 'rgb(212 249 232)',
                }, ],
                labels: [
                    <?php
                    $begin = new DateTime($date_init);
                    $end = new DateTime($date_end);
                    $end = $end->modify('+1 dia');

                    $interval = DateInterval::createFromDateString('1 day');
                    $period = new DatePeriod($begin, $interval, $end);

                    foreach ($period as $date) {
                        // echo $date = $date->format("m\d");
                        echo "'" . $date->format("d/m") . "'";
                        echo ", ";
                    }
                    ?>
                ],
            },

            options: {
                scales: {
                    A: {
                        beginAtZero: true,
                        type: 'linear',
                        position: 'left',
                        suggestedMax: 10
                    },
                    B: {
                        beginAtZero: true,
                        type: 'linear',
                        position: 'right',
                    }
                }

            }
        });
    </script>
<?php else: ?>
    <script>
        var ctx = document.getElementById('salesVolumeChart');
        var salesVolumeChart = new Chart(ctx, {
            data: {
                datasets: [{
                    type: 'line',
                    label: 'Faturamento',
                    yAxisID: 'B',
                    data: [
                        <?php
                        $begin = new DateTime($date_init);
                        $end = new DateTime($date_end);
                        $end = $end->modify('+1 dia');

                        $interval = DateInterval::createFromDateString('1 day');
                        $period = new DatePeriod($begin, $interval, $end);

                        foreach ($period as $date) {

                            $date = $date->format("Y-m-d");
                            $order_date = "%" . $date  . "%";
    
                            // TOTAL DE FATURAMENTO COMO PRODUTOR
                            $get_billing_total = $conn->prepare('SELECT SUM(order_final_price) AS total FROM orders_all WHERE order_delivery_date LIKE :order_date AND order_status = 3 AND order_number NOT LIKE "AFI%"');
                            $get_billing_total->execute(array('order_date' => $order_date));
                            $billing = $get_billing_total->fetch();

                            // TOTAL DE FATURAMENTO COMO AFILIADO 
                            $get_total_sales_afi = $conn->prepare('SELECT order_number FROM orders_all WHERE order_delivery_date LIKE :order_date AND order_status = 3 AND order_number LIKE "AFI%"');
                            $get_total_sales_afi->execute(array('order_date' => $order_date));
                            $orders_number_afi = $get_total_sales_afi->fetchAll();

                            $total_witch_afi = 0;

                            foreach ($orders_number_afi as $order_number) {
                                $order_number = explode("AFI", $order_number['order_number']);
                                $order_number = $order_number[1];
                        
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

                            

                            if ($billing['total'] + $total_witch_afi == 0) {
                                echo $billing_string = 0;
                                echo ", ";
                            } else {
                                echo $billing_string = round($billing['total'] + $total_witch_afi, 2);
                                echo ", ";
                            }
                        }
                        ?>
                    ],
                    borderColor: 'rgb(47,222,145)',
                    backgroundColor: 'rgb(47,222,145)',
                    tension: 0.4
                }, {
                    type: 'line',
                    label: 'Comissão',
                    yAxisID: 'B',
                    data: [
                        <?php
                        $begin = new DateTime($date_init);
                        $end = new DateTime($date_end);
                        $end = $end->modify('+1 dia');

                        $interval = DateInterval::createFromDateString('1 day');
                        $period = new DatePeriod($begin, $interval, $end);

                        foreach ($period as $date) {

                            $date = $date->format("Y-m-d");
                            $order_date = "%" . $date  . "%";

                            $get_liquid_total = $conn->prepare('SELECT SUM(order_liquid_value) FROM orders_all WHERE order_delivery_date LIKE :order_date AND order_status = 3');
                            $get_liquid_total->execute(array('order_date' => $order_date));
                            $sales = $get_liquid_total->fetch();


                            if ($sales[0] == null) {

                                echo $liquid_string = 0;
                                echo ", ";
                            } else {

                                echo $liquid_string = round($sales[0], 2);
                                echo ", ";
                            }
                        }
                        ?>
                    ],
                    borderColor: 'rgb(11,53,43)',
                    backgroundColor: 'rgb(11,53,43)',
                    tension: 0.4
                }, {
                    type: 'bar',
                    label: 'Produtos Vendidos',
                    yAxisID: 'A',
                    data: [
                        <?php
                        $begin = new DateTime($date_init);
                        $end = new DateTime($date_end);
                        $end = $end->modify('+1 dia');

                        $interval = DateInterval::createFromDateString('1 day');
                        $period = new DatePeriod($begin, $interval, $end);

                        foreach ($period as $date) {

                            $date = $date->format("Y-m-d");
                            $order_date = "%" . $date  . "%";
                            $get_orders_total = $conn->prepare('SELECT SUM(sale_quantity) FROM sales AS s INNER JOIN orders_all AS o ON s.sale_id = o.sale_id WHERE o.order_delivery_date LIKE :order_date AND o.order_status = 3');

                            $get_orders_total->execute(array('order_date' => $order_date));

                            $orders = $get_orders_total->fetch();

                            if ($orders[0] == null) {
                                echo $orders = 0;
                                echo ", ";
                            } else {

                                echo $orders = $orders['0'];
                                echo ", ";
                            }
                        }
                        ?>
                    ],
                    borderColor: 'rgb(212 249 232)',
                    backgroundColor: 'rgb(212 249 232)',
                }, ],
                labels: [
                    <?php
                    $begin = new DateTime($date_init);
                    $end = new DateTime($date_end);
                    $end = $end->modify('+1 dia');

                    $interval = DateInterval::createFromDateString('1 day');
                    $period = new DatePeriod($begin, $interval, $end);

                    foreach ($period as $date) {
                        // echo $date = $date->format("m\d");
                        echo "'" . $date->format("d/m") . "'";
                        echo ", ";
                    }
                    ?>
                ],
            },

            options: {
                scales: {
                    A: {
                        beginAtZero: true,
                        type: 'linear',
                        position: 'left',
                        suggestedMax: 10
                    },
                    B: {
                        beginAtZero: true,
                        type: 'linear',
                        position: 'right',
                    }
                }

            }
        });
    </script>
<?php endif; ?>
