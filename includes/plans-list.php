<?php

# Condições Plano Bronze
if ($new_plan_id == 1){
    $user_plan_tax = 0.7597;
    $user_external_gateway_tax = 0.02;
    $user_plan_shipping_tax = 29.9;
    $user_payment_term = 30;
    $new_plan_price = 0;

}

# Condições Plano Silver
else if ($new_plan_id == 2){
    $user_plan_tax = 0.0697;
    $user_external_gateway_tax = 0.015;
    $user_plan_shipping_tax = 28.9;
    $user_payment_term = 14;
    $new_plan_price = 197;

}

# Condições Plano Gold
else if ($new_plan_id == 3){
    $user_plan_tax = 0.0597;
    $user_external_gateway_tax = 0.01;
    $user_plan_shipping_tax = 27.9;
    $user_payment_term = 7;
    $new_plan_price = 497.9;
}

?>