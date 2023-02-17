<script>
                var ctx = document.getElementById('salesVolumeChart');
                var salesVolumeChart = new Chart(ctx, {
				data: {
						datasets: [{
							type: 'line',
							label: 'Valor de Venda',
							yAxisID: 'B',
							data: [
							<?php
								$begin = new DateTime($date_init);
								$end = new DateTime($date_end);
								$end = $end->modify( '+1 dia' );
								
								$interval = DateInterval::createFromDateString('1 day');
								$period = new DatePeriod($begin, $interval, $end);
								
								foreach ($period as $date) {
									
									$date = $date->format("Y-m-d");
									$order_date = "%" . $date  . "%";

									$get_billing_total = $conn->prepare('SELECT SUM(order_final_price) FROM orders_all WHERE order_date LIKE :order_date');
									$get_billing_total->execute(array('order_date' => $order_date));
									$billing = $get_billing_total->fetch();
									
									if($billing[0] == null){

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
							label: 'Lucro Bruto',
							yAxisID: 'B',
							data: [
								<?php
								$begin = new DateTime($date_init);
								$end = new DateTime($date_end);
								$end = $end->modify( '+1 dia' );
								
								$interval = DateInterval::createFromDateString('1 day');
								$period = new DatePeriod($begin, $interval, $end);
								
								foreach ($period as $date) {
									
									$date = $date->format("Y-m-d");
									$order_date = "%" . $date  . "%";

									$get_liquid_total = $conn->prepare('SELECT SUM(order_liquid_value) FROM orders_all WHERE order_date LIKE :order_date');
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
								$end = $end->modify( '+1 dia' );
								
								$interval = DateInterval::createFromDateString('1 day');
								$period = new DatePeriod($begin, $interval, $end);
								
								foreach ($period as $date) {
									
									$date = $date->format("Y-m-d");
									$order_date = "%" . $date  . "%";
									$get_orders_total = $conn->prepare('SELECT SUM(sale_quantity) FROM sales AS s INNER JOIN orders_all AS o ON s.sale_id = o.sale_id WHERE o.order_date LIKE :order_date');

									$get_orders_total->execute(array('order_date' => $order_date));

									$orders = $get_orders_total->fetch();

									if ($orders[0] == null){
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
						},],
						labels: [
							<?php
								$begin = new DateTime($date_init);
								$end = new DateTime($date_end);
								$end = $end->modify( '+1 dia' );
								
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