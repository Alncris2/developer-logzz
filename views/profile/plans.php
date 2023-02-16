<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
}

$page_title = "Planos | Logzz";
$profile_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

$user__id = $_SESSION['UserID'];

$get_users_infos = $conn->prepare('SELECT * FROM subscriptions INNER JOIN users ON users.user__id = subscriptions.user__id WHERE users.user__id = :user__id');
$get_users_infos->execute(array('user__id' => $user__id));
    
if ($get_users_infos->rowCount() != 0){
  while($row = $get_users_infos->fetch()) {
    $user_name              = $row['full_name'];
    $user_email             = $row['email'];
    $user_plan              = $row['user_plan'];
    $user_avatar            = $row['user_avatar'];
    $company_name		    = $row['company_name'];
    $user_plan_tax          = $row['user_plan_tax'];
    $user_plan_shipping_tax = $row['user_plan_shipping_tax'];
    $user_external_gateway_tax = $row['user_external_gateway_tax'];
    $user_payment_term      = $row['user_payment_term'];
    $user_plan_price        = $row['plan_price'];
    $subscription_renewal   = $row['subscription_renewal'];

    $company_doc			= $row['company_doc'];
    $company_bank		    = $row['company_bank'];
    $company_agency		    = $row['company_agency'];
    $company_account		= $row['company_account'];
    $company_account_type	= $row['company_account_type'];
    $company_type           = $row['company_type'];
    $company_pix_key        = $row['company_pix_key'];
  }  
} else {
  exit;
}

$get_user_card = $conn->prepare('SELECT * FROM cards WHERE card_user_id = :user__id AND card_active = 1');
$get_user_card->execute(array('user__id' => $user__id));

if ($get_user_card->rowCount() != 0 ){
    $has_active_card = true;

    $card_details = $get_user_card->fetch();
    $card_brand = $card_details['card_brand'];
    $card_final = $card_details['card_final'];
    $card_id = $card_details['card_id'];

} else {
    $has_active_card = false;
}

?>
  
  <style>
    .photo-content .cover-photo {
        background: url(../images/profile/cover.jpg);
        background-size: cover !important;
        background-position: center !important;
        min-height: 250px;
        width: 100%;
    }
  </style>

<!-- row -->
<div class="container-fluid">
    <!-- row -->
    <div class="row">
        <div class="col-lg-12">
            <div class="profile card card-body px-3 pt-3 pb-0">
                <div class="profile-head">
                    <div class="photo-content">
                        <div class="cover-photo" style="background: url(<?php echo SERVER_URI; ?>/uploads/imagens/usuarios/<?php echo strtolower($_SESSION['UserPlanString']); ?>-plan-cover.jpg)"></div>
                    </div>
                    <div class="profile-info">
                        <div class="profile-photo">
                            <img src="<?php echo SERVER_URI; ?>/uploads/imagens/usuarios/<?php echo $user_avatar; ?>" class="img-fluid rounded-circle" alt="">
                        </div>
                        <div class="profile-details">
                            <div class="profile-name px-3 pt-2">
                                <h4 class="text-primary mb-0"><?php echo $user_name; ?></h4>
                                <p>
                                    <small><?php echo 'Usuário ' . $_SESSION['UserPlanString']; ?></small>
                                </p>
                            </div>
                            <div class="profile-email px-2 pt-2">
                                <h4 class="text-muted mb-0"><?php echo $user_email; ?></h4>
                                <p>
                                    <small>Email</small>
                                </p>
                            </div>
                            <!-- <div class="dropdown ml-auto">
                                <a href="minha-conta/solicitar-saque/" data-action="billing-request" class="mt-3 btn btn-success billing-request">Solicitar Saque</a>
                                <a href="https://dropexpress.com.br/#ofertas" target="_blank" class="mt-3 btn btn-primary">Fazer Upgrade</a>
                            </div> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
        $search_scheduled_changes = $conn->prepare('SELECT meta_value FROM subscriptions_meta WHERE subscription_id = :user__id AND meta_key = "plan_change_scheduled"');
        $search_scheduled_changes->execute(array('user__id' => $user__id));
        if ($search_scheduled_changes->rowCount() > 0){
            $meta_value = $search_scheduled_changes->fetch();
            $meta_value = $meta_value['meta_value'];
            $values = explode('{', $meta_value);
            $change_date = date_format(date_create($subscription_renewal), "d/m");
            $change_to = userPlanString($values[2]);
    ?>
    <div class="alert alert-warning fade show mt-0 fs-14">
        <i class="fas fa-info-circle"></i>
            <b>Você solicitou Downgrade do seu plano.</b><br>No dia <?php echo $change_date; ?> sua conta mudará do Plano <?php echo $_SESSION['UserPlanString']; ?> para o Plano <?php echo $change_to; ?>. Até lá, você pode continuar usufruindo normalmente dos benefícios do plano atual.
    </div>
    <?php
        }
    ?>
    <div class="row" style="justify-content: space-between;">
        <div class="col-xl-4 col-lg-4 col-sm-4">
            <div class="widget-stat card bg-success">
                <div class="card-body">
                    <div class="media ai-icon">
                        <span class="mr-2" style="background-color: #cffeea; color: #2bc155;">
                            <i class="fas fa-gem"></i>
                        </span>
                        <div class="media-body">
                            <p class="mb-1 text-white">SEU PLANO</p>
                            <h4 class="mb-0 text-white"><?php echo ucwords(strtolower($_SESSION['UserPlanString'])); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-4 col-sm-4">
            <div class="widget-stat card">
                <div class="card-body">
                <div class="media ai-icon">
                    <span class="mr-2" style="background-color: #cffeea; color: #2bc155;">
                    <i class="fas fa-file-invoice-dollar"></i>
                    </span>
                    <div class="media-body">
                    <p class="mb-1">MENSALIDADE</p>
                    <h4 class="mb-0"><?php echo "R$ "  .  number_format($user_plan_price, 2, ',', ''); ?></h4>
                    </div>
                </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4 col-lg-4 col-sm-4">
            <div class="widget-stat card">
                <div class="card-body">
                <div class="media ai-icon">
                    <span class="mr-2" style="background-color: #cffeea; color: #2bc155;">
                    <i class="fas fa-truck"></i>
                    </span>
                    <div class="media-body">
                    <p class="mb-1">ENTREGA</p>
                    <h4 class="mb-0"><?php echo "R$ "  .  number_format($user_plan_shipping_tax, 2, ',', ''); ?></h4>
                    </div>
                </div>
                </div>
            </div>
        </div>
        
    </div>

    <div class="row" style="">

        <div class="col-xl-4 col-lg-4 col-sm-4">
            <div class="widget-stat card">
                <div class="card-body">
                <div class="media ai-icon">
                    <span class="mr-2" style="background-color: #cffeea; color: #2bc155;">
                    <i class="fas fa-percent"></i>
                    </span>
                    <div class="media-body">
                    <p class="mb-1">TAXA PAGAMENTO FÍSICO</p>
                    <h4 class="mb-0"><?php echo ($user_plan_tax * 100) . "%"; ?></h4>
                    </div>
                </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-4 col-sm-4">
            <div class="widget-stat card">
                <div class="card-body">
                <div class="media ai-icon">
                    <span class="mr-2" style="background-color: #cffeea; color: #2bc155;">
                    <i class="far fa-calendar-check"></i>
                    </span>
                    <div class="media-body">
                    <p class="mb-1">LIBERAÇÃO</p>
                    <h4 class="mb-0"><?php echo $user_payment_term . " dias"; ?></h4>
                    </div>
                </div>
                </div>
            </div>
        </div>

    </div>

    <!-- row -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4">
                            <h4>Forma de Pagamento</h4>
                            <?php
                                if ($has_active_card) {
                            ?>
                            <label class="fs-12 text-muted d-block">Cartão de Crédito</label>
                            <i class="fab fa-cc-<?php echo $card_brand; ?> text-success"></i>&nbsp;<?php echo ucfirst($card_brand); ?><br>
                            <label>**** **** **** <?php echo $card_final; ?></label><br>
                            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#FormaDePagamentoModal">
                                <i class="fas fa-exchange-alt"></i>&nbsp;&nbsp;Alterar
                            </button>
                            <?php
                                } else {
                            ?>
                            <label class="text-muted fs-14 d-block">Você não tem uma forma de pagamento ativa.</label>

                            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#FormaDePagamentoModal">
                                <i class="fas fa-plus"></i>&nbsp;&nbsp;Adicionar
                            </button>
                            <?php
                                }
                            ?>
                        </div>
                        <div class="col-lg-8">
                            <h4>Suporte</h4>
                            <p class="fs-14">Tem alguma dúvida sobre os planos ou algum problema com a sua Assinatura? <br>Clique no botão abaixo e nós tentaremos de ajudar.</p>
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#SuporteOnlineModal">
                                <i class="fas fa-comment-alt"></i>&nbsp;&nbsp;Suporte Online
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row" style="justify-content: space-between;">

        <div class="col-xl-3 col-lg-12 col-md-12">
            <?php
                $get_current_plan = $conn->prepare('SELECT * FROM subscriptions WHERE user__id = :user__id');
                $get_current_plan->execute(array('user__id' => $user__id));
                $current_plan_details = $get_current_plan->fetch();
            
                if ($current_plan_details['custom_conditions'] == 1 && $current_plan_details['user_plan'] != 5) {
            ?>
            <div class="card overflow-hidden">
                <div class="card-body">
                    <p class="text-muted h5 mt-4">Seu Plano atual é</p>
                    <h3 class="mt-0 mb-0  h3 font-weight-bold">Personalizado</h3>

                    <small><p class="text-muted  mb-2 mt-4">Ao mudar para um dos planos padrão, você poderá perder sua negociação personalizada.</p></small>
                    <small><p class="text-muted  mb-2 mt-4">Se tiver alguma dúvida, fale com seu Gerente de Contas.</p></small>
                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#SuporteOnlineModal">
                                <i class="fas fa-comment-alt"></i>&nbsp;&nbsp;Suporte Online
                            </button>
                </div>
            </div>
            <?php
                } else {
                    $plan_id = $current_plan_details['user_plan']; //subscription_current_plan
                    $plan_string = userPlanString($plan_id);
            ?>
            <div class="card overflow-hidden">
                <div class="card-body">
                    <p class="text-muted h5 mt-4">Seu Plano atual é</p>
                    <h3 class="mt-0 mb-0  h3 font-weight-bold"><?php echo $plan_string; ?></h3>

                    <small><p class="text-muted  mb-2 mt-4">Se tiver alguma dúvida com relação aos planos ou condições personalizadas, fale com seu Gerente de Contas.</p></small>
                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#SuporteOnlineModal">
                                <i class="fas fa-comment-alt"></i>&nbsp;&nbsp;Suporte Online
                            </button>
                </div>
            </div>
            <?php
                }
            ?>
        </div>

        <div class="col-xl-3 col-lg-4 col-sm-4">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <h3 class="mt-4 mb-1 text-center h2 font-weight-bold text-success">Gratuito</h3>
                    <p class="text-muted text-center h5">Logzz Bronze</p>

                    <small><p class="text-muted text-center mb-2 mt-4"><strong>7.97%</strong> de taxa p/ pagamento físico</p></small>
                    <small><p class="text-muted text-center mb-2"><strong>30</strong> dias para recebimento ou antecipação</p></small>
                    <!-- <small><p class="text-muted text-center mb-2"><strong>2%</strong> dos pedidos via Checkout Logzz + Integração com gateway externo <br>(ex: pagar.me / mercadopago)</p></small> -->
                    <small><p class="text-muted text-center mb-2"><strong>R$ 29,90</strong> por entrega em todo o BR (incluso manuseio, sendo esse R$ 0,00)</p></small>
                
                    <?php
                        if ($has_active_card){
                    ?>
                    <a class="btn btn-success btn-rounded d-block mt-4 mb-3 px-5 change-plan-btn" href="#" data-plan-id="1">Alterar Plano</a>
                    <?php
                        } else {
                    ?>
                    <a class="btn btn-success btn-rounded d-block mt-4 mb-3 px-5 need-paym-btn" href="#" data-toggle="modal" data-target="#FormaDePagamentoModal">Alterar Plano</a>
                    <?php
                        }
                    ?>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-4 col-sm-4">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <h3 class="mt-4 mb-1 text-center h2 font-weight-bold text-success"><span class="h3 text-success">R$</span> 197,00</h3>
                    <p class="text-muted text-center h5">Logzz Silver</p>
                    
                    <small><p class="text-muted text-center mb-2 mt-4"><strong>6.97%</strong> de taxa p/ pagamento físico</p></small>
                    <small><p class="text-muted text-center mb-2"><strong>14</strong> dias para recebimento ou antecipação</p></small>
                    <!-- <small><p class="text-muted text-center mb-2"><strong>1.5%</strong> dos pedidos via Checkout Logzz + Integração com gateway externo <br>(ex: pagar.me / mercadopago)</p></small> -->
                    <small><p class="text-muted text-center mb-2"><strong>R$ 28,90</strong> por entrega em todo o BR (incluso manuseio, sendo esse R$ 0,00)</p></small>
                
                    <?php
                        if ($has_active_card){
                    ?>
                    <a class="btn btn-success btn-rounded d-block mt-4 mb-3 px-5 change-plan-btn" href="#" data-plan-id="2">Alterar Plano</a>
                    <?php
                        } else {
                    ?>
                    <a class="btn btn-success btn-rounded d-block mt-4 mb-3 px-5 need-paym-btn" href="#" data-toggle="modal" data-target="#FormaDePagamentoModal">Alterar Plano</a>
                    <?php
                        }
                    ?>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-4 col-sm-4">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <h3 class="mt-4 mb-1 text-center h2 font-weight-bold text-success"><span class="h3 text-success">R$</span> 497,90</h3>
                    <p class="text-muted text-center h5">Logzz Gold</p>
                    
                    <small><p class="text-muted text-center mb-2 mt-4"><strong>5.97%</strong> de taxa p/ pagamento físico</p></small>
                    <small><p class="text-muted text-center mb-2"><strong>7</strong> dias para recebimento ou antecipação</p></small>
                    <!-- <small><p class="text-muted text-center mb-2"><strong>1%</strong> dos pedidos via Checkout Logzz + Integração com gateway externo <br>(ex: pagar.me / mercadopago)</p></small> -->
                    <small><p class="text-muted text-center mb-2"><strong>R$ 27,90</strong> por entrega em todo o BR (incluso manuseio, sendo esse R$ 0,00)</p></small>

                    <?php
                        if ($has_active_card){
                    ?>
                    <a class="btn btn-success btn-rounded d-block mt-4 mb-3 px-5 change-plan-btn" href="#" data-plan-id="3">Alterar Plano</a>
                    <?php
                        } else {
                    ?>
                    <a class="btn btn-success btn-rounded d-block mt-4 mb-3 px-5 need-paym-btn" href="#" data-toggle="modal" data-target="#FormaDePagamentoModal">Alterar Plano</a>
                    <?php
                        }
                    ?>
                </div>
            </div>
        </div>

    </div>

</div>
</div>

    <div class="modal fade" id="FormaDePagamentoModal" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">                                                                        
                <div class="modal-header center text-center d-block">
                <h5 class="modal-title">Adicionar Novo Cartão</h5>
                </div>
                <div class="card-body">
                    <form id="SaveCardForm" method="POST" action="<?php echo SERVER_URI ?>/add-pay-info">

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <input type="text" class="form-control" name="cc-name" placeholder="Nome no Cartão" required="">
                                <small class="text-muted"></small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <input type="text" class="form-control cc-number" name="cc-number" placeholder="Número" required="">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <input type="text" class="form-control cc-expiration" name="cc-expiration" placeholder="Validade" required="">
                            </div>
                            <div class="col-md-6 mb-3">
                                <input type="text" class="form-control cc-cvv" name="cc-cvv" placeholder="CVV" required="">
                            </div>
                        </div>
                        
                        <hr class="mb-4">
                        
                        <button id="save-card-btn" class="btn btn-success btn-lg btn-block" type="submit">Salvar Cartão</button>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="SuporteOnlineModal" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="text-center p-3" style="background-color:#0b352b; border-top-right-radius: 1.15rem;  border-top-left-radius: 1.15rem;">
                <div class="profile-photo">
                    <img src="<?php echo SERVER_URI . "/uploads/perfis/gerente-de-contas-hugo.png" ?>" width="100" class="img-fluid rounded-circle" alt="">
                </div>
                <h3 class="mt-3 mb-1 text-white">Hugo</h3>
                <p class="text-white m-auto">Gerente de Contas - DropExpress</p>
                </div>
                <label class="tex-center m-auto pt-3"><a href="mailto:hugo@dropexpress.com.br" target="_blank" class="">hugo@dropexpress.com.br</a href=""></label>

                <div class="card-footer border-0 mt-0">					
                <a href="https://api.whatsapp.com/send?phone=556293351785" target="_blank" class="btn btn-primary btn-lg btn-block">
                    <i class="fab fa-whatsapp"></i> (62) 9 9335-1785
                </a>
            </div>
        </div>
    </div>
    

<?php
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>