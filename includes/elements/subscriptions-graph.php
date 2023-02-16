
<!--**********************************
  Gráficos Padrão
***********************************-->


<?php
	if ($graphs_context == 1){
?>
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
								$end = $end->modify( '+1 dia' );
								
								$interval = DateInterval::createFromDateString('1 day');
								$period = new DatePeriod($begin, $interval, $end);
								
								foreach ($period as $date) {
									
									$date = $date->format("Y-m-d");
									$order_date = "%" . $date  . "%";

									$get_billing_total = $conn->prepare('SELECT SUM(billing_value) AS total FROM billings WHERE (billing_released IS NOT NULL AND billing_released LIKE :order_date) AND (billing_type = "RECURRENCE" OR billing_type = "PLAN_UPGRADE")');
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
							type: 'bar',
							label: 'Assinaturas Efetuadas',
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
									$get_orders_total = $conn->prepare('SELECT COUNT(billing_value) AS total FROM billings WHERE (billing_released IS NOT NULL AND billing_released LIKE :order_date) AND (billing_type = "RECURRENCE" OR billing_type = "PLAN_UPGRADE")');

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
									beginAtZero: false,
									type: 'linear',
									position: 'left',
									suggestedMax: 2
								},
								B: {
									type: 'linear',
									position: 'right',
                                    suggestedMax: 250,
                                    beginAtZero: false,
									type: 'linear',
								}
						}

                      }
                  });
</script>
<script>
<?php
	
	$get_bronze_users = $conn->prepare('SELECT COUNT(subscription_id) FROM subscriptions WHERE user_plan = 1');
	$get_bronze_users->execute();
	$bronze_users = $get_bronze_users->fetch();
	$bronze_users = $bronze_users[0];

	$get_silver_users = $conn->prepare('SELECT COUNT(subscription_id) FROM subscriptions WHERE user_plan = 2');
	$get_silver_users->execute();
	$silver_users = $get_silver_users->fetch();
	$silver_users = $silver_users[0];

	$get_gold_users = $conn->prepare('SELECT COUNT(subscription_id) FROM subscriptions WHERE user_plan = 3');
	$get_gold_users->execute();
	$gold_users = $get_gold_users->fetch();
	$gold_users = $gold_users[0];

	$get_custom_users = $conn->prepare('SELECT COUNT(subscription_id) FROM subscriptions WHERE user_plan = 4');
	$get_custom_users->execute();
	$custom_users = $get_custom_users->fetch();
	$custom_users = $custom_users[0];

?>
				const subscriptionPlanChart = document.getElementById("subscriptionPlanChart").getContext('2d');
				new Chart(subscriptionPlanChart, {
					type: 'pie',
					data: {
						datasets: [{
							data: [<?php echo $bronze_users; ?>, <?php echo $silver_users; ?>, <?php echo $gold_users; ?>, <?php echo $custom_users; ?>],
							borderWidth: 0,
							backgroundColor: 		["#2fde91", "#4fdf1f", "#0b352b", "#f7e39c"],
							hoverBackgroundColor: 	["#2fde91", "#4fdf1f", "#0b352b", "#f7e39c"]

						}],
						labels: 					["Bronze", "Silver", "Gold", "Personalizado"]
					},
					options: {
						responsive: true,
						legend: false,
					}
				});
</script>
<script>
<?php
	
	$get_bronze_users = $conn->prepare('SELECT COUNT(subscription_id) FROM subscriptions WHERE subscription_pay_status = 0');
	$get_bronze_users->execute();
	$bronze_users = $get_bronze_users->fetch();
	$bronze_users = $bronze_users[0];

	$get_silver_users = $conn->prepare('SELECT COUNT(subscription_id) FROM subscriptions WHERE subscription_pay_status = 1');
	$get_silver_users->execute();
	$silver_users = $get_silver_users->fetch();
	$silver_users = $silver_users[0];

	$get_gold_users = $conn->prepare('SELECT COUNT(subscription_id) FROM subscriptions WHERE subscription_pay_status = 2');
	$get_gold_users->execute();
	$gold_users = $get_gold_users->fetch();
	$gold_users = $gold_users[0];

	$get_custom_users = $conn->prepare('SELECT COUNT(subscription_id) FROM subscriptions WHERE subscription_pay_status = 3');
	$get_custom_users->execute();
	$custom_users = $get_custom_users->fetch();
	$custom_users = $custom_users[0];

?>
				const subscriptionStatusChart = document.getElementById("subscriptionStatusChart").getContext('2d');
				new Chart(subscriptionStatusChart, {
					type: 'pie',
					data: {
						datasets: [{
							data: [<?php echo $bronze_users; ?>, <?php echo $silver_users; ?>, <?php echo $gold_users; ?>, <?php echo $custom_users; ?>],
							borderWidth: 0,
							backgroundColor: 		["#2fde91", "#4fdf1f", "#0b352b", "#f7e39c"],
							hoverBackgroundColor: 	["#2fde91", "#4fdf1f", "#0b352b", "#f7e39c"]

						}],
						labels: 					["Cancelada", "Aprovada", "Em Aberto", "Reembolsada"]
					},
					options: {
						responsive: true,
						legend: false,
					}
				});
</script>

<?php } ?>








<?php
/** 
 * Gráficos Filtro Plano
 * 
 * 
 * 
*/

	if ($graphs_context == 2){
?>
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
								$end = $end->modify( '+1 dia' );
								
								$interval = DateInterval::createFromDateString('1 day');
								$period = new DatePeriod($begin, $interval, $end);
								
								foreach ($period as $date) {
									
									$date = $date->format("Y-m-d");
									$order_date = "%" . $date  . "%";

									$get_billing_total = $conn->prepare('SELECT SUM(billing_value) AS total FROM billings INNER JOIN subscriptions ON billings.user__id = subscriptions.user__id WHERE (billing_released IS NOT NULL AND billing_released LIKE :order_date) AND (billing_type = "RECURRENCE" OR billing_type = "PLAN_UPGRADE") AND user_plan = :user_plan');
									$get_billing_total->execute(array('user_plan' => $user_plan, 'order_date' => $order_date));
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
							type: 'bar',
							label: 'Assinaturas Efetuadas',
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
									$get_orders_total = $conn->prepare('SELECT COUNT(billing_value) AS total FROM billings INNER JOIN subscriptions ON billings.user__id = subscriptions.user__id WHERE (billing_released IS NOT NULL AND billing_released LIKE :order_date) AND (billing_type = "RECURRENCE" OR billing_type = "PLAN_UPGRADE") AND user_plan = :user_plan');

									$get_orders_total->execute(array('user_plan' => $user_plan, 'order_date' => $order_date));

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
									beginAtZero: false,
									type: 'linear',
									position: 'left',
									suggestedMax: 2
								},
								B: {
									type: 'linear',
									position: 'right',
                                    suggestedMax: 250,
                                    beginAtZero: false,
									type: 'linear',
								}
						}

                      }
                  });
</script>
<script>
<?php
	
	$get_bronze_users = $conn->prepare('SELECT COUNT(subscription_id) FROM subscriptions WHERE subscription_renewal BETWEEN :date_init AND :date_end AND user_plan = :user_plan');
	$get_bronze_users->execute(array('user_plan' => $user_plan, 'date_init' => $date_init, 'date_end' => $date_end));
	if ($get_bronze_users->rowCount() > 0){
		$bronze_users = $get_bronze_users->fetch();
		$bronze_users = $bronze_users[0];
	} else {
		$bronze_users = 0;
	}

?>
				const subscriptionPlanChart = document.getElementById("subscriptionPlanChart").getContext('2d');
				new Chart(subscriptionPlanChart, {
					type: 'pie',
					data: {
						datasets: [{
							data: [<?php echo $bronze_users; ?>],
							borderWidth: 0,
							backgroundColor: 		["#2fde91"],
							hoverBackgroundColor: 	["#2fde91"]

						}],
						labels: 					["<?php echo $status; ?>"]
					},
					options: {
						responsive: true,
						legend: false,
					}
				});
</script>
<script>
<?php
	
	$get_bronze_users = $conn->prepare('SELECT COUNT(subscription_id) FROM subscriptions WHERE subscription_renewal BETWEEN :date_init AND :date_end AND user_plan = :user_plan AND subscription_pay_status = 0');
	$get_bronze_users->execute(array('user_plan' => $user_plan, 'date_init' => $date_init, 'date_end' => $date_end));
	$bronze_users = $get_bronze_users->fetch();
	$bronze_users = $bronze_users[0];

	$get_silver_users = $conn->prepare('SELECT COUNT(subscription_id) FROM subscriptions WHERE subscription_renewal BETWEEN :date_init AND :date_end AND user_plan = :user_plan AND subscription_pay_status = 1');
	$get_silver_users->execute(array('user_plan' => $user_plan, 'date_init' => $date_init, 'date_end' => $date_end));
	$silver_users = $get_silver_users->fetch();
	$silver_users = $silver_users[0];

	$get_gold_users = $conn->prepare('SELECT COUNT(subscription_id) FROM subscriptions WHERE subscription_renewal BETWEEN :date_init AND :date_end AND user_plan = :user_plan AND subscription_pay_status = 2');
	$get_gold_users->execute(array('user_plan' => $user_plan, 'date_init' => $date_init, 'date_end' => $date_end));
	$gold_users = $get_gold_users->fetch();
	$gold_users = $gold_users[0];

	$get_custom_users = $conn->prepare('SELECT COUNT(subscription_id) FROM subscriptions WHERE subscription_renewal BETWEEN :date_init AND :date_end AND user_plan = :user_plan AND subscription_pay_status = 3');
	$get_custom_users->execute(array('user_plan' => $user_plan, 'date_init' => $date_init, 'date_end' => $date_end));
	$custom_users = $get_custom_users->fetch();
	$custom_users = $custom_users[0];

?>
				const subscriptionStatusChart = document.getElementById("subscriptionStatusChart").getContext('2d');
				new Chart(subscriptionStatusChart, {
					type: 'pie',
					data: {
						datasets: [{
							data: [<?php echo $bronze_users; ?>, <?php echo $silver_users; ?>, <?php echo $gold_users; ?>, <?php echo $custom_users; ?>],
							borderWidth: 0,
							backgroundColor: 		["#2fde91", "#4fdf1f", "#0b352b", "#f7e39c"],
							hoverBackgroundColor: 	["#2fde91", "#4fdf1f", "#0b352b", "#f7e39c"]

						}],
						labels: 					["Cancelada", "Aprovada", "Em Aberto", "Reembolsada"]
					},
					options: {
						responsive: true,
						legend: false,
					}
				});
</script>

<?php 
} 




















/** 
 * Gráficos Filtro Assinante
 * 
 * 
 * 
*/

	if ($graphs_context == 3){
?>
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
								$end = $end->modify( '+1 dia' );
								
								$interval = DateInterval::createFromDateString('1 day');
								$period = new DatePeriod($begin, $interval, $end);
								
								foreach ($period as $date) {
									
									$date = $date->format("Y-m-d");
									$order_date = "%" . $date  . "%";

									$get_billing_total = $conn->prepare('SELECT SUM(billing_value) AS total FROM billings INNER JOIN subscriptions ON subscriptions.user__id = billings.user__id WHERE (billing_released LIKE :order_date) AND (billing_type = "RECURRENCE" OR billing_type = "PLAN_UPGRADE") AND subscriptions.user__id = :user__id');
									// $get_billing_total->execute(array('user__id' => $user__id, 'order_date' => $order_date));
									$get_billing_total->bindParam(':user__id', $filter_by_subscriber, PDO::PARAM_INT);
									$get_billing_total->bindParam(':order_date', $order_date, PDO::PARAM_STR);
									$get_billing_total->execute();
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
							type: 'bar',
							label: 'Assinaturas Efetuadas',
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
									$get_orders_total = $conn->prepare('SELECT COUNT(billing_value) AS total FROM billings INNER JOIN subscriptions ON billings.user__id = subscriptions.user__id WHERE (billing_released LIKE :order_date) AND (billing_type = "RECURRENCE" OR billing_type = "PLAN_UPGRADE") AND subscriptions.user__id = :user__id');

									//$get_orders_total->execute(array('user__id' => $user__id, 'order_date' => $order_date));
									$get_orders_total->bindParam(':user__id', $filter_by_subscriber, PDO::PARAM_INT);
									$get_orders_total->bindParam(':order_date', $order_date, PDO::PARAM_STR);
									$get_orders_total->execute();

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
									beginAtZero: false,
									type: 'linear',
									position: 'left',
									suggestedMax: 2
								},
								B: {
									type: 'linear',
									position: 'right',
                                    suggestedMax: 250,
                                    beginAtZero: false,
									type: 'linear',
								}
						}

                      }
                  });
</script>
<script>
<?php
	
	$get_bronze_users = $conn->prepare('SELECT COUNT(subscription_id) FROM subscriptions WHERE subscription_renewal BETWEEN :date_init AND :date_end AND user__id = :user__id');
	$get_bronze_users->execute(array('user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end));
	if ($get_bronze_users->rowCount() > 0){
		$bronze_users = $get_bronze_users->fetch();
		$bronze_users = $bronze_users[0];
	} else {
		$bronze_users = 0;
	}

?>
				const subscriptionPlanChart = document.getElementById("subscriptionPlanChart").getContext('2d');
				new Chart(subscriptionPlanChart, {
					type: 'pie',
					data: {
						datasets: [{
							data: [<?php echo $bronze_users; ?>],
							borderWidth: 0,
							backgroundColor: 		["#2fde91"],
							hoverBackgroundColor: 	["#2fde91"]

						}],
						labels: 					["<?php echo userPlanString($bronze_users); ?>"]
					},
					options: {
						responsive: true,
						legend: false,
					}
				});
</script>
<script>
<?php
	
	$get_bronze_users = $conn->prepare('SELECT COUNT(subscription_id) FROM subscriptions WHERE subscription_renewal BETWEEN :date_init AND :date_end AND user__id = :user__id AND subscription_pay_status = 0');
	$get_bronze_users->execute(array('user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end));
	$bronze_users = $get_bronze_users->fetch();
	$bronze_users = $bronze_users[0];

	$get_silver_users = $conn->prepare('SELECT COUNT(subscription_id) FROM subscriptions WHERE subscription_renewal BETWEEN :date_init AND :date_end AND user__id = :user__id AND subscription_pay_status = 1');
	$get_silver_users->execute(array('user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end));
	$silver_users = $get_silver_users->fetch();
	$silver_users = $silver_users[0];

	$get_gold_users = $conn->prepare('SELECT COUNT(subscription_id) FROM subscriptions WHERE subscription_renewal BETWEEN :date_init AND :date_end AND user__id = :user__id AND subscription_pay_status = 2');
	$get_gold_users->execute(array('user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end));
	$gold_users = $get_gold_users->fetch();
	$gold_users = $gold_users[0];

	$get_custom_users = $conn->prepare('SELECT COUNT(subscription_id) FROM subscriptions WHERE subscription_renewal BETWEEN :date_init AND :date_end AND user__id = :user__id AND subscription_pay_status = 3');
	$get_custom_users->execute(array('user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end));
	$custom_users = $get_custom_users->fetch();
	$custom_users = $custom_users[0];

?>
				const subscriptionStatusChart = document.getElementById("subscriptionStatusChart").getContext('2d');
				new Chart(subscriptionStatusChart, {
					type: 'pie',
					data: {
						datasets: [{
							data: [<?php echo $bronze_users; ?>, <?php echo $silver_users; ?>, <?php echo $gold_users; ?>, <?php echo $custom_users; ?>],
							borderWidth: 0,
							backgroundColor: 		["#2fde91", "#4fdf1f", "#0b352b", "#f7e39c"],
							hoverBackgroundColor: 	["#2fde91", "#4fdf1f", "#0b352b", "#f7e39c"]

						}],
						labels: 					["Cancelada", "Aprovada", "Em Aberto", "Reembolsada"]
					},
					options: {
						responsive: true,
						legend: false,
					}
				});
</script>

<?php }









/** 
 * Gráficos Filtro Assinante e Plano
 * 
 * 
 * 
*/

	if ($graphs_context == 4){
?>
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
								$end = $end->modify( '+1 dia' );
								
								$interval = DateInterval::createFromDateString('1 day');
								$period = new DatePeriod($begin, $interval, $end);
								
								foreach ($period as $date) {
									
									$date = $date->format("Y-m-d");
									$order_date = "%" . $date  . "%";

									$get_billing_total = $conn->prepare('SELECT SUM(billing_value) AS total FROM billings INNER JOIN subscriptions ON subscriptions.user__id = billings.user__id WHERE (billing_released LIKE :order_date) AND (billing_type = "RECURRENCE" OR billing_type = "PLAN_UPGRADE") AND (subscriptions.user__id = :user__id AND user_plan = :user_plan)');
									// $get_billing_total->execute(array('user__id' => $user__id, 'order_date' => $order_date));
									$get_billing_total->bindParam(':user__id', $filter_by_subscriber, PDO::PARAM_INT);
									$get_billing_total->bindParam(':order_date', $order_date, PDO::PARAM_STR);
									$get_billing_total->bindParam(':user_plan', $filter_by_plan, PDO::PARAM_INT);
									$get_billing_total->execute();
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
							type: 'bar',
							label: 'Assinaturas Efetuadas',
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
									$get_orders_total = $conn->prepare('SELECT COUNT(billing_value) AS total FROM billings INNER JOIN subscriptions ON billings.user__id = subscriptions.user__id WHERE (billing_released LIKE :order_date) AND (billing_type = "RECURRENCE" OR billing_type = "PLAN_UPGRADE") AND (subscriptions.user__id = :user__id AND user_plan = :user_plan)');

									//$get_orders_total->execute(array('user__id' => $user__id, 'order_date' => $order_date));
									$get_orders_total->bindParam(':user__id', $filter_by_subscriber, PDO::PARAM_INT);
									$get_orders_total->bindParam(':order_date', $order_date, PDO::PARAM_STR);
									$get_orders_total->bindParam(':user_plan', $filter_by_plan, PDO::PARAM_INT);
									$get_orders_total->execute();

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
									beginAtZero: false,
									type: 'linear',
									position: 'left',
									suggestedMax: 2
								},
								B: {
									type: 'linear',
									position: 'right',
                                    suggestedMax: 250,
                                    beginAtZero: false,
									type: 'linear',
								}
						}

                      }
                  });
</script>
<script>
<?php
	
	$get_bronze_users = $conn->prepare('SELECT COUNT(subscription_id) FROM subscriptions WHERE subscription_renewal BETWEEN :date_init AND :date_end AND (user__id = :user__id AND user_plan = :user_plan)');
	$get_bronze_users->execute(array('user_plan' => $filter_by_plan, 'user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end));
	if ($get_bronze_users->rowCount() > 0){
		$bronze_users = $get_bronze_users->fetch();
		$bronze_users = $bronze_users[0];
	} else {
		$bronze_users = 0;
	}

?>
				const subscriptionPlanChart = document.getElementById("subscriptionPlanChart").getContext('2d');
				new Chart(subscriptionPlanChart, {
					type: 'pie',
					data: {
						datasets: [{
							data: [<?php echo $bronze_users; ?>],
							borderWidth: 0,
							backgroundColor: 		["#2fde91"],
							hoverBackgroundColor: 	["#2fde91"]

						}],
						labels: 					["<?php echo userPlanString($bronze_users); ?>"]
					},
					options: {
						responsive: true,
						legend: false,
					}
				});
</script>
<script>
<?php
	
	$get_bronze_users = $conn->prepare('SELECT COUNT(subscription_id) FROM subscriptions WHERE subscription_renewal BETWEEN :date_init AND :date_end AND user__id = :user__id AND (subscription_pay_status = 0 AND user_plan = :user_plan');
	$get_bronze_users->execute(array('user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end, 'user_plan' => $filter_by_plan));
	$bronze_users = $get_bronze_users->fetch();
	$bronze_users = $bronze_users[0];

	$get_silver_users = $conn->prepare('SELECT COUNT(subscription_id) FROM subscriptions WHERE subscription_renewal BETWEEN :date_init AND :date_end AND user__id = :user__id AND (subscription_pay_status = 1 AND user_plan = :user_plan');
	$get_silver_users->execute(array('user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end, 'user_plan' => $filter_by_plan));
	$silver_users = $get_silver_users->fetch();
	$silver_users = $silver_users[0];

	$get_gold_users = $conn->prepare('SELECT COUNT(subscription_id) FROM subscriptions WHERE subscription_renewal BETWEEN :date_init AND :date_end AND user__id = :user__id AND (subscription_pay_status = 2 AND user_plan = :user_plan');
	$get_gold_users->execute(array('user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end, 'user_plan' => $filter_by_plan));
	$gold_users = $get_gold_users->fetch();
	$gold_users = $gold_users[0];

	$get_custom_users = $conn->prepare('SELECT COUNT(subscription_id) FROM subscriptions WHERE subscription_renewal BETWEEN :date_init AND :date_end AND user__id = :user__id AND (subscription_pay_status = 3 AND user_plan = :user_plan');
	$get_custom_users->execute(array('user__id' => $user__id, 'date_init' => $date_init, 'date_end' => $date_end, 'user_plan' => $filter_by_plan));
	$custom_users = $get_custom_users->fetch();
	$custom_users = $custom_users[0];

?>
				const subscriptionStatusChart = document.getElementById("subscriptionStatusChart").getContext('2d');
				new Chart(subscriptionStatusChart, {
					type: 'pie',
					data: {
						datasets: [{
							data: [<?php echo $bronze_users; ?>, <?php echo $silver_users; ?>, <?php echo $gold_users; ?>, <?php echo $custom_users; ?>],
							borderWidth: 0,
							backgroundColor: 		["#2fde91", "#4fdf1f", "#0b352b", "#f7e39c"],
							hoverBackgroundColor: 	["#2fde91", "#4fdf1f", "#0b352b", "#f7e39c"]

						}],
						labels: 					["Cancelada", "Aprovada", "Em Aberto", "Reembolsada"]
					},
					options: {
						responsive: true,
						legend: false,
					}
				});
</script>

<?php } ?>