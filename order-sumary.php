<?php
require_once('includes/config.php');
session_name(SESSION_NAME);
session_start();

if (isset($_GET['order'])) {
    $order = addslashes($_GET['order']);

    $order_data = $conn->prepare('SELECT * FROM orders INNER JOIN sales ON orders.sale_id = sales.sale_id WHERE order_number = :order');
    $order_data->execute(array('order' => $order));

    # Manda pra página de detalhes do pedido com upsel, caso haja
    if ($order_data->rowCount() > 1) {
        header("Location: " . SERVER_URI . "/meus-pedidos/" . $order);
    }

    # Armazena os dados do pedidos em variáveis
    if ($order_data->rowCount() != 0) {
        while ($row = $order_data->fetch()) {
            $sale_freight = $row['sale_freight'];
            $sale_name = $row['sale_name'];
            $product_id = $row['product_id'];
            $sale_id = $row['sale_id'];
            $sale_quantity = $row['sale_quantity'];
            $sale_price = $row['sale_price'];
            $user__id = $row['user__id'];
            $name = $row['client_name'];
            $document = $row['client_document'];
            $address = $row['client_address'];
            $number = $row['client_number'];
            $email = $row['client_email'];
            $product_delivery = $row['order_deadline'];
            $order_delivery_date = $row['order_delivery_date'];
            $fb_pixel = $row['sale_fb_pixel'];
            $fb_purchase_value = $row['order_final_price'];
            $initiate_checkout = $row['initiate_checkout'];
            $order_id = $row['order_id'];
            $order_status = $row['order_status'];
            $order_status_description = $row['order_status_description'];
            $fail_delivery_attemp = $row['fail_delivery_attemp'];
            $proof_contact_attempt = $row['proof_contact_attempt'];
            $payment_proof = $row['payment_proof'];
            $order_date = $row['order_date'];
            $order_liquid_value = $row['order_liquid_value'];
            $order_tracking = $row['order_tracking'];
            $order_shipping = $row['order_shipping'];
            $sale_name = $row['sale_name'];

            # String de Forma de Pagamento
            switch ($row['order_payment_method']) {
                case 'money':
                    $order_payment_method = 'Dinheiro';
                    break;

                case 'credit':
                    $order_payment_method = 'Cartão de Crédito';
                    break;

                case 'debit':
                    $order_payment_method = 'Cartão de Débito';
                    break;

                case 'pix':
                    $order_payment_method = 'PIX';
                    break;

                default:
                    $order_payment_method = "-";
                    break;
            }

            if (preg_match("/AFI/", $order)) {
                $order_number = explode("AFI", $order);
                $order_number = $order_number[1];
            } else {
                $order_number = $order;
            }

            # Busca historico de atualização dos status
            $get_historic_order = $conn->prepare('SELECT * FROM order_details WHERE order_number = :order_number');
            $get_historic_order->execute(array('order_number' => $order_number));
            if ($get_historic_order->rowCount() > 0) {
                $use_historic = true;
                $historic = $get_historic_order->fetchALL();
            }



            # Busca o nome do Produtor com base no HOTCODE da Afiliação
            # Se não houver uma meta_value com o HOTCODE da afiliação, signfica que não houve um afiliado vinculado ao pedido.
            $get_users_id = $conn->prepare('SELECT meta_value FROM orders_meta WHERE meta_key = "membership_hotcode" AND order_number = :order_number');
            $get_users_id->execute(array('order_number' => $order_number));
            if ($get_users_id->rowCount() == 1) {
                $has_member = true;
                $get_users_id = $get_users_id->fetch();
                $hotcode = $get_users_id['meta_value'];

                $get_member_id = $conn->prepare('SELECT membership_affiliate_id FROM memberships WHERE memberships_hotcode = :memberships_hotcode');
                $get_member_id->execute(array('memberships_hotcode' => $hotcode));
                $member_id = $get_member_id->fetch();
                $member_id = $member_id[0];

                $get_member_name = $conn->prepare('SELECT full_name, email, user_phone FROM users WHERE user__id = :user__id');
                $get_member_name->execute(array('user__id' => $member_id));
                $member_details = $get_member_name->fetch();
                $member_name = $member_details['full_name'];
                $member_email = $member_details['email'];
                $member_phone = $member_details['user_phone'];
            }

            #ID DO PRODUTOR 
            $get_id_productor = $conn->prepare("SELECT user__id FROM products WHERE product_id = :product_id");
            $get_id_productor->execute(['product_id' => $product_id]);
            $id_productor = $get_id_productor->fetch(\PDO::FETCH_ASSOC)['user__id'];

            # Busca o operador responsável pela entrega do pedido
            $get_operator = $conn->prepare('SELECT * FROM local_operations_orders loo INNER JOIN logistic_operator lop ON loo.responsible_id = lop.operator_id WHERE order_id = :order_id');
            $get_operator->execute(array('order_id' => $order_id));

            # Orodutor vai ser idenficado pelo ID da Oferta.
            $get_producer_name = $conn->prepare('SELECT full_name FROM users WHERE user__id = :user__id');
            $get_producer_name->execute(array('user__id' => $id_productor));
            $producer_name = $get_producer_name->fetch();
            $producer_name = $producer_name[0];

            # Cálculo dos valores as comissões
            $get_ship_tax = $conn->prepare('SELECT meta_value FROM orders_meta WHERE meta_key = "ship_tax" AND order_number = :order_number');
            $get_ship_tax->execute(array('order_number' => $order_number));
            $get_ship_tax = $get_ship_tax->fetch();
            $main_ship_tax = $get_ship_tax['meta_value'];

            $get_member_commission = $conn->prepare('SELECT meta_value FROM orders_meta WHERE meta_key = "member_commission" AND order_number = :order_number');
            $get_member_commission->execute(array('order_number' => $order_number));
            $get_member_commission = $get_member_commission->fetch();
            @$member_commission = $get_member_commission['meta_value'];

            $get_producer_commission = $conn->prepare('SELECT meta_value FROM orders_meta WHERE meta_key = "producer_commission" AND order_number = :order_number');
            $get_producer_commission->execute(array('order_number' => $order_number));
            $get_producer_commission = $get_producer_commission->fetch();
            $producer_commission = $get_producer_commission['meta_value'];

            $get_member_tax = $conn->prepare('SELECT meta_value FROM orders_meta WHERE meta_key = "member_tax" AND order_number = :order_number');
            $get_member_tax->execute(array('order_number' => $order_number));
            $get_member_tax = $get_member_tax->fetch();
            @$member_tax = $get_member_tax[0];

            $get_producer_tax = $conn->prepare('SELECT meta_value FROM orders_meta WHERE meta_key = "producer_tax" AND order_number = :order_number');
            $get_producer_tax->execute(array('order_number' => $order_number));
            $get_producer_tax = $get_producer_tax->fetch();
            $producer_tax = $get_producer_tax['meta_value'];

            $get_member_commission_base = $conn->prepare('SELECT meta_value FROM orders_meta WHERE meta_key = "member_commission_base" AND order_number = :order_number');
            $get_member_commission_base->execute(array('order_number' => $order_number));
            $get_member_commission_base = $get_member_commission_base->fetch();
            @$member_commission_base = $get_member_commission_base['meta_value'];

            $get_member_tax_base = $conn->prepare('SELECT meta_value FROM orders_meta WHERE meta_key = "member_tax_base" AND order_number = :order_number');
            $get_member_tax_base->execute(array('order_number' => $order_number));
            $get_member_tax_base = $get_member_tax_base->fetch();
            @$member_tax_base = $get_member_tax_base['meta_value'];

            $get_producer_tax_base = $conn->prepare('SELECT meta_value FROM orders_meta WHERE meta_key = "producer_tax_base" AND order_number = :order_number');
            $get_producer_tax_base->execute(array('order_number' => $order_number));
            $get_producer_tax_base = $get_producer_tax_base->fetch();
            $producer_tax_base = $get_producer_tax_base['meta_value'];

            if (!(preg_match("/AFI/", $order))) {
                # Entrega
                $ship_tax = $main_ship_tax;

                # Taxa
                $sale_tax = $producer_tax;
            } else {
                # Entrega
                $ship_tax = 0;

                # Taxa
                $sale_tax = $member_tax;
            }

            $system_commission = $main_ship_tax + $producer_tax + $member_tax;


            # Textos Detalhes das Comissões
            $syst_comm_details = "R$ " . number_format($main_ship_tax, 2, ',', '.') . " da Entrega + " . "R$ " . number_format(($producer_tax + $member_tax), 2, ',', '.') . " das taxas";
            $memb_comm_details = "R$ " . number_format(($member_commission + $member_tax), 2, ',', '.') . " (" . $member_commission_base . "% de " . number_format($fb_purchase_value, 2, ',', '.') . ") - R$ " . number_format(($member_tax), 2, ',', '.') . " (" . ($member_tax_base * 100) . "% de taxa).";
            if (isset($has_member)) {
                $prod_comm_details = "R$ " . number_format(($fb_purchase_value - $sale_freight), 2, ',', '.') . " + R$ " . number_format(($sale_freight), 2, ',', '.') . " (do Frete)" .  " - R$ " . number_format(($member_commission + $member_tax), 2, ',', '.') . " (Comissão do Afiliado) - R$ " . number_format($main_ship_tax, 2, ',', '.')  . " (da Entrega) - R$ " . number_format(($producer_tax), 2, ',', '.') . " (" . ($producer_tax_base * 100) . "% de taxa).";
            } else {
                $prod_comm_details = "R$ " . number_format(($fb_purchase_value - $sale_freight), 2, ',', '.') . " + R$ " . number_format(($sale_freight), 2, ',', '.') . " (do Frete)" . " - R$ " . number_format($main_ship_tax, 2, ',', '.') . " (da Entrega) - R$ " . number_format(($producer_tax), 2, ',', '.') . " (" . ($producer_tax_base * 100) . "% de taxa)";
            }



            // Função Desabiliatada
            # String de Hoário de Entrega
            // if ($row['delivery_period'] == "manha") {
            //     $period = "Manhã";
            // } else {
            //     $period = "Tarde";
            // }

            $get_warranty_time = $conn->prepare('SELECT product_warranty_time FROM products WHERE product_id = :product_id');
            $get_warranty_time->execute(array('product_id' => $product_id));

            $product_warranty_time = $get_warranty_time->fetch();
            $product_warranty_time = $product_warranty_time[0];

            if ($product_warranty_time == null || empty($product_warranty_time) || !($product_warranty_time > 0)) {
                $product_warranty_time = "<small>Período de garantia não informado.</small>";
            } else {
                # Calcula a data em que vence a garantia
                $warranty_time_timestamp = "+" . $product_warranty_time . "days";
                $expire_date = date('Y-m-d', strtotime($order_date . $warranty_time_timestamp));

                if (strtotime($expire_date) > strtotime(date('Y-m-d'))) {

                    $today = date_create_from_format('Y-m-d', date('Y-m-d'));
                    $expire_date = date_create_from_format('Y-m-d', $expire_date);
                    $diff = date_diff($today, $expire_date);

                    $product_warranty_time = $diff->days;

                    $product_warranty_time = $product_warranty_time . " dias restantes";
                } else {

                    $product_warranty_time = "Expirou em " . date_format(date_create($expire_date), 'd/m/Y');
                }
            }
        }
    } else {
        header("Location: /pagina-nao-encontrada");
        exit;
    }
}


$order_product_data = $conn->prepare('SELECT * FROM products WHERE product_id = :product_id AND status = 1');
$order_product_data->execute(array('product_id' => $product_id));

while ($sow = $order_product_data->fetch()) {
    $product_image = $sow['product_image'];
    $product_name = $sow['product_name'];
    $product_description = $sow['product_description'];
    $user__id = $sow['user__id'];

    if (strlen($product_description) > 100) {
        $product_description = substr($product_description, 0, 100) . "...";
    }


    $delivery_date = date_format(date_create($product_delivery), 'd/m/Y'); # função desabilitada [. " | " . $period;]

    if (!(empty($fb_pixel)) && $initiate_checkout == 0) {
        $fb_pixel_purchase = "<script>
      !function(f,b,e,v,n,t,s)
      {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
      n.callMethod.apply(n,arguments):n.queue.push(arguments)};
      if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
      n.queue=[];t=b.createElement(e);t.async=!0;
      t.src=v;s=b.getElementsByTagName(e)[0];
      s.parentNode.insertBefore(t,s)}(window, document,'script',
      'https://connect.facebook.net/en_US/fbevents.js');
      fbq('init', '" . $fb_pixel . "');
      fbq('track', 'Purchase', {value:  $fb_purchase_value, currency: 'BRL'});
      </script>
      <noscript>
      <img height='1' width='1' style='display:none' 
         src='https://www.facebook.com/tr?id=" . $fb_pixel . "&ev=PageView&noscript=1'/>
      </noscript>";

        $stmt = $conn->prepare('UPDATE orders SET initiate_checkout = 1 WHERE order_id = :order_id');
        $stmt_afi = $conn->prepare('UPDATE orders SET initiate_checkout = 1 WHERE order_id = :order_id');
        $stmt->execute(array('order_id' => $order_id));
        $stmt_afi->execute(array('order_id' => "AFI" . $order_id));
    }

    $page_title =  "Pedido #" . $order;
    require_once('includes/layout/fullwidth/fullwidth-header.php');
?>

    <div class="container-fluid" style="margin-top: 30px;">
        <div class="row">
            <div class="col-lg-9 d-block m-auto">
                <div class="card">
                    <div class="card-header">
                        <h2 style="font-weight: 300; border-color: #f0f1f;">Pedido #<?php echo $order; ?></h2>
                    </div>
                    <div class="card-body">
                        <div class="row mb-5">
                            <div class="col-sm-12">
                                <?php
                                switch ($order_status) {
                                    case 1:
                                        $alert_class = "success";
                                        $alert_icon = "fas fa-check-circle";
                                        $alert_title = "Entrega Reagendada";
                                        if ($operator = $get_operator->fetch()) {
                                            $get_operator_name = $conn->prepare("SELECT full_name FROM users WHERE user__id = :user_id");
                                            $get_operator_name->execute(array("user_id" => $operator['user_id']));
                                            $alert_msg = "O produto agora será entregue no dia <b>" . date_format(date_create($product_delivery), 'd/m') . "</b> pelo operador <b>" . $get_operator_name->fetch()["full_name"]; # função desabilitada [. "</b> no período da <b>" . $period . "</b>";]
                                        } else {
                                            $alert_msg = "O produto agora será entregue no dia <b>" . date_format(date_create($product_delivery), 'd/m'); # função desabilitada [. "</b> no período da <b>" . $period . "</b>";]
                                        }
                                        break;

                                    case 2:
                                        $alert_class = "warning";
                                        $alert_icon = "fas fa-clock";
                                        $alert_title = "Entrega Atrasada";
                                        if ($operator = $get_operator->fetch()) {
                                            $get_operator_name = $conn->prepare("SELECT full_name FROM users WHERE user__id = :user_id");
                                            $get_operator_name->execute(array("user_id" => $operator['user_id']));
                                            $alert_msg = "A entrega era prevista para ser entregue pelo operador " . $get_operator_name->fetch()["full_name"] . " a <b>" . strtolower($period) . "</b> do dia <b>" . date_format(date_create($product_delivery), 'd/m') . "</b>.";
                                        } else {
                                            $alert_msg = "O produto agora será entregue no dia <b>" . date_format(date_create($product_delivery), 'd/m'); # função desabilitada [. "</b> no período da <b>" . $period . "</b>";]
                                        }
                                        break;

                                    case 3:
                                        
                                        $alert_class = "success solid";
                                        $alert_icon = "fas fa-check-circle";
                                        $alert_title = "Pedido Completo";
                                        if ($operator = $get_operator->fetch()) {
                                            $get_operator_name = $conn->prepare("SELECT full_name FROM users WHERE user__id = :user_id");
                                            $get_operator_name->execute(array("user_id" => $operator['user_id']));
                                            $alert_msg = "O produto foi entregue no dia <b>" . date_format(date_create($order_delivery_date), 'd/m') . "</b> pelo operador <b>" . $get_operator_name->fetch()["full_name"] . "</b> às <b>" . date_format(date_create($order_delivery_date), 'H:i') . "</b>.";
                                        } else {
                                            $alert_msg = "O produto foi entregue no dia <b>" . date_format(date_create($product_delivery), 'd/m') . "</b> às <b>" . date_format(date_create($product_delivery), 'H:i') . "</b>.";
                                        }
                                        break;

                                    case 4:
                                        $alert_class = "warning";
                                        $alert_icon = "fas fa-exclamation-circle";
                                        $alert_title = "Entrega Frustrada";
                                        if ($operator = $get_operator->fetch()) {
                                            $get_operator_name = $conn->prepare("SELECT full_name FROM users WHERE user__id = :user_id");
                                            $get_operator_name->execute(array("user_id" => $operator['user_id']));
                                            $alert_msg = "O entregador " . $get_operator_name->fetch()["full_name"] . " tentou realizar a entregar do pedido, mas não houve êxito.";
                                        } else {
                                            $alert_msg = "O entregador tentou realizar a entregar do pedido, mas não houve êxito.";
                                        }
                                        $alert_msg .= '<br><small><a href="' . SERVER_URI . '/uploads/pedidos/frustrados/' . $fail_delivery_attemp . '" target="_blank">Ver comprovante de tentativa de entrega</a></small>';
                                        if ($proof_contact_attempt) {
                                            $alert_msg .= ' / <small><a href="' . SERVER_URI . '/uploads/pedidos/frustrados/' . $proof_contact_attempt . '" target="_blank">Ver comprovante de tentativa de contato</a></small>';
                                        }
                                        break;

                                    case 5:
                                        $alert_class = "danger solid";
                                        $alert_icon = "fas fa-ban";
                                        $alert_title = "Pedido Cancelado";
                                        if ($operator = $get_operator->fetch()) {
                                            $get_operator_name = $conn->prepare("SELECT full_name FROM users WHERE user__id = :user_id");
                                            $get_operator_name->execute(array("user_id" => $operator['user_id']));
                                            $alert_msg = "O pedido foi cancelado sem que fosse preciso o entregador " . $get_operator_name->fetch()["full_name"] . " fosse ao local.";
                                        } else {
                                            $alert_msg = "O pedido foi cancelado sem ser preciso que o entregador fosse ao local.";
                                        }
                                        break;

                                    case 9:
                                        $alert_class = "info";
                                        $alert_icon = "fas fa-check-circle";
                                        $alert_title = "Pedido Reembolsado";
                                        $alert_msg = "";  
                                        break;

                                    case 10:
                                        $alert_class = "dark";
                                        $alert_icon = "fas fa-check-circle"; 
                                        $alert_title = "Pedido Confirmado";
                                        if ($operator = $get_operator->fetch()) {
                                            $get_operator_name = $conn->prepare("SELECT full_name FROM users WHERE user__id = :user_id");
                                            $get_operator_name->execute(array("user_id" => $operator['user_id']));
                                            $alert_msg = "Aguarde o entregador <b>" . $get_operator_name->fetch()["full_name"] . "</b> no dia <b>" . date_format(date_create($product_delivery), 'd/m'); # função desabilitada [. "</b> no período da <b>" . $period . "</b>";]
                                        } else {
                                            $alert_msg = "Aguarde o entregador no dia <b>" . date_format(date_create($product_delivery), 'd/m'); # função desabilitada [. "</b> no período da <b>" . $period . "</b>";]
                                        }    
                                        break;

                                    case 11:
                                        $alert_class = "warning";    
                                        $alert_icon = "fas fa-check-circle";
                                        $alert_title = "Pedido Em Aberto"; 
                                        if ($operator = $get_operator->fetch()) {
                                            $get_operator_name = $conn->prepare("SELECT full_name FROM users WHERE user__id = :user_id");
                                            $get_operator_name->execute(array("user_id" => $operator['user_id']));
                                            $alert_msg = "O pedido estava agendado para entrega pelo <b>" . $get_operator_name->fetch()["full_name"] . "</b> no dia <b>" . date_format(date_create($product_delivery), 'd/m'); # função desabilitada [. "</b> no período da <b>" . $period . "</b>";]
                                        } else {
                                            $alert_msg = "O pedido estava agendado no dia <b>" . date_format(date_create($product_delivery), 'd/m'); # função desabilitada [. "</b> no período da <b>" . $period . "</b>";]
                                        }    
                                        break;

                                    case 12:
                                        $alert_class = "outline-danger";     
                                        $alert_icon = "fas fa-times-circle"; 
                                        $alert_title = "Pedido Indisponível"; 
                                        $alert_msg = "Esse pedido não pôde sair na rota do dia <b>" . date_format(date_create($product_delivery), 'd/m') . '</b> pelo motivo: <span class="text-black-50">'. $order_status_description .'</span>, contacte o cliente e tente reagendar o pedido para a próxima data disponível';
                                        break;
 

                                    default:
                                        $alert_class = "success";
                                        $alert_icon = "fas fa-clock";
                                        $alert_title = "Entrega Agendada";
                                        if ($operator = $get_operator->fetch()) {
                                            $get_operator_name = $conn->prepare("SELECT full_name FROM users WHERE user__id = :user_id");
                                            $get_operator_name->execute(array("user_id" => $operator['user_id']));
                                            $alert_msg = "Aguarde o entregador <b>" . $get_operator_name->fetch()["full_name"] . "</b> no dia <b>" . date_format(date_create($product_delivery), 'd/m'); # função desabilitada [. "</b> no período da <b>" . $period . "</b>";]
                                        } else {
                                            $alert_msg = "Aguarde o entregador no dia <b>" . date_format(date_create($product_delivery), 'd/m'); # função desabilitada [. "</b> no período da <b>" . $period . "</b>";]
                                        } 
                                        break;
                                }
                                ?>
                                <div class="alert <?php echo "alert-" . $alert_class; ?> fade show">
                                    <i class="<?php echo $alert_icon; ?>"></i>
                                    <strong><?php echo $alert_title . "!"; ?></strong><br><?php echo $alert_msg; ?>
                                    <?php if ($order_status_description && ($order_status == 5 || $order_status == 11)) : ?> 
                                        <p>Motivo: <span class="text-black-50"><?= $order_status_description ?></span></p>
                                    <?php endif ?> 
                                </div> 

                            </div>
                            <?php if (isset($_SESSION['UserID']) && ($_SESSION['UserID'] == $id_productor || $_SESSION['UserID'] == $member_id || $_SESSION['UserPlan'] == 5 )) { ?>  
                                <div class="col-sm-12 my-3"> 
                                    <div class="alert alert-light">  
                                        <h6>Link de confirmação ou reagendamento do pedido: </h6> 
                                        <a class="btn-copy-link" data-text="<?= SERVER_URI . "/confirma-pedido/" . $order ?>">  
                                            <?= SERVER_URI . "/confirma-pedido/" . $order ?>  
                                        </a> 
                                    </div> 
                                </div>
                            <?php } ?> 
                            <div class="mt-4 col-xl-8 col-lg-5 col-md-6 col-sm-12">
                                <h6>Cliente:</h6>
                                <div> <strong><?php echo $name; ?></strong> </div>
                                <div> <strong><?php echo $document; ?></strong> </div>
                                <div><?php echo $address; ?></div>
                                <?php if ($email !== null && $email !== '')  echo "<div><i class='fa fa-envelope'></i> " . $email . "</div>"; ?>
                                <div><a href="<?= 'https://api.whatsapp.com/send/?phone=55' . preg_replace('/[@\.\;\)\(\" "]+/', '', $number)  ?>" target="_blank" rel="noopener noreferrer"><i class="fab fa-whatsapp"></i></a> <?= $number ?></div>
                            </div>
                            <div class="mt-4 col-xl-4 col-lg-5 col-md-6 col-sm-12">
                                <img class="img-fluid" src="<?php echo SERVER_URI . "/uploads/imagens/produtos/" . $product_image; ?>">
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped" style="font-size: 0.9em;">
                                <thead>
                                    <tr>
                                        <th class="col-md-1 center">#</th>
                                        <th class="col-md-3 center">Produto</th>
                                        <th class="col-md-6 center">Descrição</th>
                                        <th class="col-md-2 center">Qtd.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="center">1</td>
                                        <td class="center"><?php echo $product_name; ?></td>
                                        <td class="center"><?php echo $product_description; ?></td>
                                        <td class="center"><?php echo $sale_quantity; ?></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td class="center text-right" colspan="3"><strong>Total </strong></td>
                                        <td class="center" colspan="2"><strong>R$ <?php echo number_format($fb_purchase_value, 2, ',', '.'); ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <?php
                if (isset($_SESSION['UserID'])) {
                ?>
                    <div class="card">
                        <div class="default-tab">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#detalhes" style="padding: 0.7rem 1.3em;"><i class="fas fa-receipt mr-1 fs-12"></i> Detalhes</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#comissoes" style="padding: 0.7rem 1.3em;"><i class="fas fa-money-check-alt mr-1 fs-12"></i> Comissões</a>
                                </li>
                                <?php if (isset($has_member)) { ?>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#afiliado" style="padding: 0.7rem 1.3em;"><i class="fas fa-user mr-1 fs-12"></i> Afiliado</a>
                                    </li>
                                <?php } ?>
                                <?php if (isset($use_historic)) { ?>
                                    <li class="nav-item">
                                        <a class="nav-link" data-toggle="tab" href="#historico" style="padding: 0.7rem 1.3em;"><i class="fas fa-clock mr-1 fs-12"></i> Histórico</a>
                                    </li>
                                <?php } ?>
                            </ul>
                            <div class="tab-content" style="padding: 1rem 2.6rem;">
                                <div class="tab-pane fade active show" id="detalhes" role="tabpanel">
                                    <div class="pt-4">
                                        <div class="profile-personal-info">
                                            <div class="row mb-1">
                                                <div class="col-sm-3 col-5">
                                                    <h5 class="">Data da venda:</h5>
                                                </div>
                                                <div class="col-sm-9 col-7"><span><?php echo date_format(date_create($order_date), 'd/m/Y h:i'); ?></span>
                                                </div>
                                            </div>
                                            <div class="row mb-1">
                                                <div class="col-sm-3 col-5">
                                                    <h5 class="">Pagamento:
                                                    </h5>
                                                </div>
                                                <div class="col-sm-9 col-7"><span><?php echo $order_payment_method; ?></span><?php if($order_payment_method == "PIX"){?>&nbsp&nbsp<a target="_blank" rel="noopener noreferrer" href="<?php echo SERVER_URI . "/uploads/pedidos/comprovante/" . $payment_proof; ?>"><i class="fa fa-eye" aria-hidden="true"></i></a><?php } ?>
                                                </div>
                                            </div>
                                            <div class="row mb-1">
                                                <div class="col-sm-3 col-5">
                                                    <h5 class="">Status:</h5>
                                                </div>
                                                <div class="col-sm-9 col-7"><span class="badge badge-sm <?php echo "badge-" . $alert_class; ?> mb-1"><i class="<?php echo $alert_icon; ?>"></i> <?php echo $alert_title; ?></span>
                                                </div>
                                            </div>
                                            <div class="row mb-1">
                                                <div class="col-sm-3 col-5">
                                                    <h5 class="">Garantia:
                                                    </h5>
                                                </div>
                                                <div class="col-sm-9 col-7"><span><?php echo $product_warranty_time; ?></span>
                                                </div>
                                            </div>
                                            <div class="row mb-1">
                                                <div class="col-sm-3 col-5">
                                                    <h5 class="">Oferta: </h5>
                                                </div>
                                                <div class="col-sm-9 col-7"><span><?php echo $sale_name; ?></span>
                                                </div>
                                            </div>
                                            <div class="row mb-1">
                                                <div class="col-sm-3 col-5">
                                                    <h5 class="">Valor da venda: </h5>
                                                </div>
                                                <div class="col-sm-9 col-7"><span class="fs-"><strong>R$ <?php echo number_format($fb_purchase_value, 2, ',', '.'); ?></strong></span>
                                                </div>
                                            </div>
                                            <div class="row mb-1 mt-4">
                                                <div class="col-sm-3 col-5">
                                                    <h5 class="">Código de Rastreio: </h5>
                                                </div>
                                                <div class="col-sm-9 col-7"><span>
                                                        <?= $order_tracking == null ? '-' : $order_tracking ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="row mb-1">
                                                <div class="col-sm-3 col-5">
                                                    <h5 class="">Transportadora: </h5>
                                                </div>
                                                <div class="col-sm-9 col-7"><span>
                                                        <?= $order_shipping == null ? '-' : $order_shipping ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <?php if ($operator = $get_operator->fetch()) {
                                                $get_operator_name = $conn->prepare("SELECT full_name FROM users WHERE user__id = :user_id");
                                                $get_operator_name->execute(array("user_id" => $operator['user_id']));
                                            ?>
                                                <div class="row mb-1">
                                                    <div class="col-sm-3 col-5">
                                                        <h5 class="">Operador: </h5>
                                                    </div>
                                                    <div class="col-sm-9 col-7"><span><?php echo $get_operator_name->fetch()["full_name"] ?></span>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                            <div class="row mb-1">
                                                <div class="col-sm-3 col-5">
                                                    <h5 class="f-w-600 fs-22 text-black">Valor Total: </h5>
                                                </div>
                                                <div class="col-sm-9 col-7"><strong><span class="f-w-600 fs-22 text-black">R$ <?php echo number_format($fb_purchase_value, 2, ',', '.'); ?></span></strong>
                                                </div>
                                            </div>
                                            <div class="row mb-1">
                                                <div class="col-sm-3 col-5">
                                                    <h5 class="f-w-600 fs-22 text-success">Valor Líquido: </h5>
                                                </div>
                                                <div class="col-sm-9 col-7"><strong><span class="f-w-600 fs-22 text-success">R$ <?php echo number_format($order_liquid_value, 2, ',', '.'); ?></span></strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="comissoes">
                                    <div class="pt-4">
                                        <div class="bd-example">
                                            <table class="table table-hover table-responsive-sm">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">Participação</th>
                                                        <th scope="col">Valor</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>Logzz <small class='text-muted'>(Plataforma)</small></td>
                                                        <td><span class="badge badge-md badge-danger mb-1" data-toggle="tooltip" data-placement="top" title="<?php echo $syst_comm_details; ?>">R$ <?php echo number_format($system_commission, 2, ',', '.'); ?> <small><i class="fas fa-info-circle"></i>
                                                                    <span>
                                                        </td>
                                                    </tr>
                                                    <?php if (isset($has_member)) { ?>
                                                        <tr>
                                                            <td><?php echo strtoupper(utf8_encode($member_name)) . " <small class='text-muted'>(Afiliado)</small>"; ?></td>
                                                            <td><span class="badge badge-md badge-success mb-1" data-toggle="tooltip" data-placement="top" title="<?php echo $memb_comm_details; ?>">R$ <?php echo number_format($member_commission, 2, ',', '.'); ?> <small><i class="fas fa-info-circle"></i>
                                                                        <span>
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                    <tr>
                                                        <td><?php echo strtoupper($producer_name) . " <small class='text-muted'>(Produtor)</small>"; ?></td>
                                                        <td><span class="badge badge-md badge-success mb-1" data-toggle="tooltip" data-placement="top" title="<?php echo $prod_comm_details; ?>">R$ <?php echo number_format($producer_commission, 2, ',', '.'); ?> <small><i class="fas fa-info-circle"></i>
                                                                    <span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <?php if (isset($has_member)) { ?>
                                    <div class="tab-pane fade" id="afiliado">
                                        <div class="pt-4">
                                            <div class="bd-example">
                                                <table class="table table-hover table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th scope=" col">Dados do Afiliado</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td style="padding: 0.8em 1.5em;">Nome</td>
                                                            <td style="padding: 0.8em 1.5em;" class="text-muted"><?php echo $member_name; ?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="padding: 0.8em 1.5em;">Email</td>
                                                            <td style="padding: 0.8em 1.5em;" class="text-muted"><?php echo $member_email; ?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="padding: 0.8em 1.5em;">Telefone</td>
                                                            <td style="padding: 0.8em 1.5em;" class="text-muted"><?php echo $member_phone; ?></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>

                                <div class="tab-pane fade" id="historico">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Status</th>
                                                <th>Atualização</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($historic as $detail) {
                                                $status_info = get_color_status($detail['order_status']) ?>                                                
                                                <tr>
                                                    <td class="text-center <?= @$status_info[1] ?>"><?= @$status_info[0] ?></td> 
                                                    <td><?= date_format(date_create($detail['order_status_update']), 'd/m/Y H:i') ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>

            </div>
        </div>
    </div>

<?php
}

require_once('includes/layout/fullwidth/fullwidth-footer.php');

function get_color_status($status) {
    switch ($status) {
        case 1:
            $return_color =  "text-success";
            $return_name = 'Reagendada';
            break;            
        case 2:
            $return_color = "text-warning";
            $return_name = 'Atrasada';
            break;            
        case 3:                                                    
            $return_color = "text-success solid";
            $return_name = 'Completa';
            break;            
        case 4:
            $return_color = "text-warning";
            $return_name = 'Frustrada';
            break;            
        case 5:
            $return_color = "text-danger solid";
            $return_name = 'Cancelada';
            break;            
        case 9:
            $return_color = "text-info";
            $return_name = 'Reembolsada';
            break;            
        case 10:
            $return_color = "text-dark";
            $return_name = 'Confirmada';
            break;            
        case 11:
            $return_color = "text-warning";
            $return_name = 'Em Aberto';
            break;            
        case 12:
            $return_color = "text--danger";   
            $return_name = 'Indisponível';
            break; 
        default:
            $return_color = "text-success";
            $return_name = 'Agendada';
            break;
    }

    return [$return_name, $return_color];
}
?>