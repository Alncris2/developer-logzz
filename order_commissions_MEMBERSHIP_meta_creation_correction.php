<?php

require_once (dirname(__FILE__) . '/includes/config.php');

$get_all_orders = $conn->prepare('SELECT * FROM orders INNER JOIN products ON orders.product_id = products.product_id ORDER BY order_number');
$get_all_orders->execute();

while($commissions_recalc = $get_all_orders->fetch()){
    
    $order_number = $commissions_recalc['order_number'];
    $order_commission_date = $commissions_recalc['order_commission_date'];
    $order_date = $commissions_recalc['order_date'];
    $order_id = $commissions_recalc['order_id'];
    $member_id = $commissions_recalc['user__id'];
    $sale_id = $commissions_recalc['sale_id'];
    $product_id = $commissions_recalc['product_id'];
    $order_final_price = $commissions_recalc['order_final_price'];
    $order_old_liquid_value = $commissions_recalc['order_liquid_value'];
    $product_commission = $commissions_recalc['product_commission'];

    if (!(preg_match("/AFI/", $order_number))) {
        #echo "O pedido nº <b>" . $order_number . "</b> foi ignorado!<br>";
        continue;
    } else {
        # É um pedido de afiliado
        $new_order_number = explode("AFI", $order_number);
        $new_order_number = $new_order_number[1];

        # Busca o pedido original e pega o ID do produtor.
        $get_linked_order = $conn->prepare('SELECT user__id, order_id FROM orders WHERE order_number = :order_number');
        $get_linked_order->execute(array('order_number' => $new_order_number));

        $get_original_order = $conn->prepare('SELECT user__id, order_id FROM orders WHERE order_number = :order_number');
        $get_original_order->execute(array('order_number' => $order_number));

        if ($get_linked_order->rowCount() == 0) {
           # echo "O pedido nº <b>" . $order_number . "</b> não tem um pedido <b>" . $new_order_number . "</b> vinculado.<br>";
            continue;
        } else {

            # Identifica PRODUTOR e AFILIADO
            $producer_id = $get_linked_order->fetch();
            $producer_id = $producer_id[0];

            $member_id = $get_original_order->fetch();
            $member_id = $member_id['0'];

            # Busca a Afiliação
            $get_membership_details = $conn->prepare('SELECT memberships_hotcode FROM memberships WHERE membership_affiliate_id = :membership_affiliate_id AND membership_producer_id = :membership_producer_id');
            $get_membership_details->execute(array('membership_affiliate_id' => $member_id, 'membership_producer_id' => $producer_id));

            $membership_details = $get_membership_details->fetch();
            $membership_hotcode = $membership_details[0];

        }

    }

    # Verifica se existe comissão personalizada para essa oferta
    $verify_custom_commision = $conn->prepare('SELECT meta_value FROM sales_meta WHERE sale_id = :sale_id AND meta_key = "custom_commission"');
    $verify_custom_commision->execute(array('sale_id' => $sale_id));

    if ($verify_custom_commision->rowCount() == 1) {
        $custom_commision = $verify_custom_commision->fetch();
        $product_commission = $custom_commision['meta_value'];
    }

    # Verifica se a Afiliação é existente e ativa
    $hotcode = $membership_hotcode;

    $verfify_membership = $conn->prepare('SELECT membership_status FROM memberships WHERE memberships_hotcode = :memberships_hotcode AND membership_product_id = :membership_product_id');
    $verfify_membership->execute(array('memberships_hotcode' => $hotcode, 'membership_product_id' => $product_id));
    $membership_status = $verfify_membership->fetch();
    @$membership_status = $membership_status['membership_status'];

    if ($membership_status == 'ATIVA') {
        $has_member = true;
    } else {
        $has_member = false;
    }

    # Busca os Dados do Produtor no Banco de Dados
    $get_product_comission_term = $conn->prepare('SELECT user_payment_term, user_plan_shipping_tax, user_plan_tax FROM users WHERE user__id = :user__id');
    $get_product_comission_term->execute(array('user__id' => $producer_id));

    while ($row = $get_product_comission_term->fetch()) {
        $user_payment_term  = $row['user_payment_term'];
        $user_plan_ship_tax = $row['user_plan_shipping_tax'];
        $producer_plan_tax = $row['user_plan_tax'];
    }

    # Busca os Dados do Afiliado no BD
    $get_member_infos = $conn->prepare('SELECT user_payment_term, user_plan_tax FROM users WHERE user__id = :user__id');
    $get_member_infos->execute(array('user__id' => $member_id));

    while ($row = $get_member_infos->fetch()) {
        $member_payment_term  = $row['user_payment_term'];
        $member_plan_tax = $row['user_plan_tax'];
        $member_user__id = $member_id;
    }

    # Cálculo Custos + Comissões
    if ($has_member == true) {

        # Comissão do Afiliado = Valor da Venda * % de Comissão - Taxa Afiliado
        $member_comission                       = round(($order_final_price * ($product_commission / 100)), 2);
        $member_total_tax                       = round(($member_comission * $member_plan_tax), 2);
        $member_comission                       = round(($member_comission - $member_total_tax), 2);

        # Data de Liberação Afiliado
        $member_order_commission_timestamp      = "+" . $member_payment_term . "days";

        # Custo Total = Comissão do Afiliado + Entrega + Taxa Produtor
        $producer_total_tax = round(($order_final_price * $producer_plan_tax), 2);

        # Custos do Produtor = Comissão Afiliado + Taxa Afiliado + Entrega + Taxa Produtor 
        $costs = round(($member_comission + $member_total_tax + $user_plan_ship_tax + $producer_total_tax), 2);

        $meta_member_comission = $member_comission + $member_total_tax;

        $order_liquid_value                     = round(($order_final_price - $costs), 2);
    } else {
        # Custo Total = Comissão do Afiliado + Entrega + Taxa Produtor
        $producer_total_tax = round(($order_final_price * $producer_plan_tax), 2);

        $costs = $producer_total_tax + $user_plan_ship_tax;

        $order_liquid_value                     = round(($order_final_price - $costs), 2);
    }

    if ($has_member == true) {

        # Cria os Metadados do pedido
        $order_meta_memb_comm       = $conn->prepare('INSERT INTO orders_meta (meta_id, order_number, meta_key, meta_value) VALUES (:meta_id, :order_number, :meta_key, :meta_value)');
        $order_meta_memb_comm_base  = $conn->prepare('INSERT INTO orders_meta (meta_id, order_number, meta_key, meta_value) VALUES (:meta_id, :order_number, :meta_key, :meta_value)');
        $order_meta_memb_tax        = $conn->prepare('INSERT INTO orders_meta (meta_id, order_number, meta_key, meta_value) VALUES (:meta_id, :order_number, :meta_key, :meta_value)');
        $order_meta_memb_tax_base   = $conn->prepare('INSERT INTO orders_meta (meta_id, order_number, meta_key, meta_value) VALUES (:meta_id, :order_number, :meta_key, :meta_value)');
        $order_meta_hotcode         = $conn->prepare('INSERT INTO orders_meta (meta_id, order_number, meta_key, meta_value) VALUES (:meta_id, :order_number, :meta_key, :meta_value)');

        $order_meta_memb_comm->execute(array('meta_id' => 0, 'order_number' => $new_order_number, 'meta_key' => "member_commission", 'meta_value' => $member_comission));
        $order_meta_memb_comm_base->execute(array('meta_id' => 0, 'order_number' => $new_order_number, 'meta_key' => "member_commission_base", 'meta_value' => $product_commission));
        $order_meta_memb_tax->execute(array('meta_id' => 0, 'order_number' => $new_order_number, 'meta_key' => "member_tax", 'meta_value' => $member_total_tax));
        $order_meta_memb_tax_base->execute(array('meta_id' => 0, 'order_number' => $new_order_number, 'meta_key' => "member_tax_base", 'meta_value' => $member_plan_tax));
        $order_meta_hotcode->execute(array('meta_id' => 0, 'order_number' => $new_order_number, 'meta_key' => "membership_hotcode", 'meta_value' => $hotcode));

    }


    // $get_tax_values = $conn->prepare('SELECT user_plan_tax, user_plan_shipping_tax FROM users WHERE user__id = :user__id');
    // $get_tax_values->execute(array('user__id' => $member_id));
    // $tax_values = $get_tax_values->fetch();
    // $plan_tax = $tax_values['user_plan_tax'];
    // $plan_shipping_tax = $tax_values['user_plan_shipping_tax'];
    
    // $tax = round(($order_final_price * $plan_tax), 2);
    // $total_tax = round(($plan_shipping_tax + $tax), 2);
    // $liquid_value = round(($order_final_price - $total_tax), 2);

    $order_meta_prod_comm       = $conn->prepare('INSERT INTO orders_meta (meta_id, order_number, meta_key, meta_value) VALUES (:meta_id, :order_number, :meta_key, :meta_value)');
    $order_meta_prod_tax        = $conn->prepare('INSERT INTO orders_meta (meta_id, order_number, meta_key, meta_value) VALUES (:meta_id, :order_number, :meta_key, :meta_value)');
    $order_meta_prod_tax_base   = $conn->prepare('INSERT INTO orders_meta (meta_id, order_number, meta_key, meta_value) VALUES (:meta_id, :order_number, :meta_key, :meta_value)');
    $order_meta_ship_tax        = $conn->prepare('INSERT INTO orders_meta (meta_id, order_number, meta_key, meta_value) VALUES (:meta_id, :order_number, :meta_key, :meta_value)');
    $liquid_value_update        = $conn->prepare('UPDATE orders SET order_liquid_value = :order_liquid_value WHERE order_id = :order_id');

    $order_meta_prod_comm->execute(array('meta_id' => 0, 'order_number' => $new_order_number, 'meta_key' => "producer_commission", 'meta_value' => $order_liquid_value));
    $order_meta_prod_tax->execute(array('meta_id' => 0, 'order_number' => $new_order_number, 'meta_key' => "producer_tax", 'meta_value' => $producer_total_tax));
    $order_meta_prod_tax_base->execute(array('meta_id' => 0, 'order_number' => $new_order_number, 'meta_key' => "producer_tax_base", 'meta_value' => $producer_plan_tax));
    $order_meta_ship_tax->execute(array('meta_id' => 0, 'order_number' => $new_order_number, 'meta_key' => "ship_tax", 'meta_value' => $user_plan_ship_tax));
    $liquid_value_update->execute(array('order_liquid_value' => $order_liquid_value, 'order_id' => $order_id));


    # echo "O pedido <b>" . $order_number . "</b> foi de <b>R$ " . number_format($order_final_price, 2, ",", ".") . "</b>, então a taxa de <b>" . number_format($tax, 2, ",", ".") . " (" . $plan_tax * 100 . ")%</b> e a entrega <b>" . number_format($plan_shipping_tax, 2, ",", ".") . "</b> do usuário <b>" . $member_id . "</b> serão de <b>" . number_format($total_tax, 2, ", ", " . ") . "</b> e o valor líquido será de <b>" . number_format($liquid_value, 2, ", ", " . ") . "</b>, e não de " . number_format($order_old_liquid_value, 2, ",", ".")  . "!<br>";
    echo "O pedido <b>" . $order_number . "</b> é do Produtor " . $producer_id . " e do afiliado " . $member_id . ", da afiliação " . $membership_hotcode . " (" . $membership_status . ")!<br>";
}



?>