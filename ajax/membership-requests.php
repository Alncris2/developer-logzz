<?php

require_once (dirname(__FILE__) . '/../../includes/config.php');
include (dirname(__FILE__) . '/../../includes/classes/StarRating.php');
session_name(SESSION_NAME);
session_start();

# Verifica se o usuário está logado
if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
}

$page_title = "Solicitações de Afiliação | DropExpress";
$has_submenu_product = 'active';
$shop_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');
?>

<div class="container-fluid">
  <div class="row">
    <div class="default-tab ml-3 mt-1">
        <div class="col-xl-12 col-xxl-12">
            <ul class="nav nav-tabs" role="tablist">
              <li class="nav-item">
                <?php
                    # Armazena o ID do usuário logado.
                    $membership_affiliate_id = $_SESSION['UserID'];

                    # Busca as afiliações ENVIADAS do usuário logado.
                    $get_sent_memberships = $conn->prepare("SELECT * FROM products_memberships as M INNER JOIN products as P ON m.membership_product_id = p.product_id WHERE membership_affiliate_id = :membership_affiliate_id AND membership_status = 'PENDENTE'");
                    $get_sent_memberships->execute(array('membership_affiliate_id' => $membership_affiliate_id));

                    $sent = $get_sent_memberships->rowCount();
                ?>
                <a class="nav-link active" data-toggle="tab" style="padding: 1.1rem 3rem;" href="#enviadas"><?php if ( $sent > 0 ) { echo '<span class="badge badge-sm badge-success">' .  $sent . '</span>'; } ?></span>&nbsp;&nbsp;Solicitações Enviadas</a>
              </li>
              <li class="nav-item">
                  <?php
                    # Busca as afiliações RECEBIDAS do usuário logado.
                    $get_received_memberships = $conn->prepare("SELECT * FROM products_memberships as M INNER JOIN products as P ON m.membership_product_id = p.product_id WHERE p.user__id = :user__id AND membership_status = 'PENDENTE'");
                    $get_received_memberships->execute(array('user__id' => $membership_affiliate_id));

                    $received = $get_received_memberships->rowCount();
                  ?>
                <a class="nav-link" data-toggle="tab" style="padding: 1.1rem 3rem;" href="#recebidas"><?php if ( $received > 0 ) { echo '<span class="badge badge-sm badge-success">' . $received . '</span>'; } ?></span>&nbsp;&nbsp; Solicitações Recebidas</a>
              </li>
            </ul>
            <div class="tab-content" style="background: #fff;border-radius: 0 0 1.25rem 1.25rem;">
              <div class="tab-pane fade active show" id="enviadas" role="tabpanel">
                <div class="pt-4">
                <?php
                    # Lista as afiliações.
                    if ($get_sent_memberships->rowCount() != 0){
                        while($row = $get_sent_memberships->fetch()) {
                        $product_id = $row['product_id'];
                        $product_name = $row['product_name'];
                        $product_price = $row['product_price'];
                        $product_image = $row['product_image'];
                        $product_rating = $row['product_rating'];
                        $product_commission = $row['product_commission'];
                        $product_max_commission = $row['product_max_price'] * ($row['product_commission'] / 100);
                        $memberships_hotcode = $row['memberships_hotcode'];
                        $membership_start = $row['membership_start'];
                ?>
                <div class="align-items-center row mx-0 border-bottom p-4" style="padding: 1.2rem 1.5rem !important;">
                    <!-- <span class="number col-2 col-sm-1 px-0 align-self-center">#1</span> -->
                    <div class="border border-primary rounded-circle p-3 mr-3">
                    </div>
                    <div class="col-sm-4 col-12 col-xxl-5 my-3 my-sm-0 px-0">
                      <h5 class="mt-0 mb-0"><?php echo $product_name; ?></h5>
                    </div>
                    <div class="ml-sm-auto col-2 col-sm-2 px-0 d-flex align-self-center align-items-center">
                      <div class="text-center">
                        <h4 class="mb-0 text-black"><?php echo $product_commission; ?>%</h4>
                        <span class="fs-12">Comissão</span>
                      </div>
                    </div>
                    <div class="ml-sm-auto col-2 col-sm-2 px-0 d-flex align-self-center align-items-center">
                      <div class="text-center">
                        <h4 class="mb-0 text-black"><?php echo date_format(date_create($membership_start), 'd/m'); ?></h4>
                          <span class="fs-12">Data</span>
                      </div>
                    </div>
                  </div>
                <?php
                    } } else {
                ?>
                    <h5 class="text-center text-muted pt-2 pb-5">Nenhuma solicitação enviada pendente.</h5>
                <?php
                    
                }
                ?>
                </div>
              </div>
              <div class="tab-pane fade" id="recebidas">
                <div class="pt-4">
                <?php

                    # Lista as afiliações.
                    if ($get_received_memberships->rowCount() != 0){
                        while($row = $get_received_memberships->fetch()) {
                        $product_id = $row['product_id'];
                        $product_name = $row['product_name'];
                        $product_price = $row['product_price'];
                        $product_image = $row['product_image'];
                        $product_rating = $row['product_rating'];
                        $product_commission = $row['product_commission'];
                        $product_max_commission = $row['product_max_price'] * ($row['product_commission'] / 100);
                        $memberships_hotcode = $row['memberships_hotcode'];
                        $membership_start = $row['membership_start'];
                        $membership_affiliate_id = $row['membership_affiliate_id'];

                        $get_member_name = $conn->prepare("SELECT full_name FROM users WHERE user__id = :membership_affiliate_id");
                        $get_member_name->execute(array('membership_affiliate_id' => $membership_affiliate_id));
                        $member_name = $get_member_name->fetch();
                        $member_name = $member_name[0];
                ?>
                <div class="align-items-center row mx-0 border-bottom p-4" style="padding: 1.2rem 1.5rem !important;">
                    <!-- <span class="number col-2 col-sm-1 px-0 align-self-center">#1</span> -->
                    <div class="border border-primary rounded-circle p-3 mr-3">
                    </div>
                    <div class="col-sm-3 col-3 my-sm-0 px-0">
                      <h5 class="mt-0 mb-0"><?php echo $member_name; ?></h5>
                    </div>
                    <div class="ml-sm-auto col-2 col-sm-2 px-0 d-flex align-self-center align-items-center">
                      <div class="text-center">
                        <h4 class="mb-0 text-black"><?php echo $product_name; ?></h4>
                        <span class="fs-12">Produto</span>
                      </div>
                    </div>
                    <div class="ml-sm-auto col-2 col-sm-2 px-0 d-flex align-self-center align-items-center">
                      <div class="text-center">
                        <h4 class="mb-0 text-black"><?php echo date_format(date_create($membership_start), 'd/m'); ?></h4>
                        <span class="fs-12">Data</span>
                      </div>
                    </div>
                    <div class="col-2 col-sm-2 px-0 d-flex align-self-center align-items-center">
                      <div class="text-center">
                        <div class="d-flex">
                            <a href="#" title="Aprovar Solicitação" data-id="<?php echo $memberships_hotcode; ?>" data-status="1" class="update-membership-status btn btn-primary btn-xs sharp mr-1"><i class="fas fa-check"></i></a>
                            <a href="#" title="Remover Solicitação" data-id="<?php echo $memberships_hotcode; ?>" data-status="0" class="update-membership-status btn btn-danger btn-xs sharp"><i class="fas fa-minus"></i></a>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php
                    } } else {
                ?>
                    <h5 class="text-center text-muted pt-2 pb-5">Nenhuma solicitação recebida pendente.</h5>
                <?php
                    }
                ?>
                </div>
              </div>
            </div>
          </div>
        </div>
  </div>
</div>

<?php
        #}
      #}
    require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>