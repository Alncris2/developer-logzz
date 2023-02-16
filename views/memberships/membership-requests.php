<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
include(dirname(__FILE__) . '/../../includes/classes/StarRating.php');
session_name(SESSION_NAME);
session_start();

# Verifica se o usuário está logado
if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}

if (isset($_GET['page'])){
    if ($_GET['page'] > 1){
        $offset = ($_GET['page'] - 1) * 30;
    } else {
        $offset = 0;
    }
} else {
    $offset = 0;
}

$page_title = "Solicitações de Afiliação | Logzz";
$has_submenu_product = 'active';
$shop_page = true;
$select_datatable_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12 default-tab ml-3 mt-1">
            <div class="col-xl-12 col-xxl-12">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <?php
                        # Armazena o ID do usuário logado.
                        $membership_affiliate_id = $_SESSION['UserID'];

                        # Busca as afiliações ENVIADAS do usuário logado.
                        $get_sent_memberships = $conn->prepare("SELECT * FROM memberships as M INNER JOIN products as P ON membership_product_id = product_id WHERE membership_affiliate_id = :membership_affiliate_id AND membership_status = 'PENDENTE'");
                        $get_sent_memberships->execute(array('membership_affiliate_id' => $membership_affiliate_id));

                        $sent = $get_sent_memberships->rowCount();
                        ?>
                        <a class="nav-link" data-toggle="tab" style="padding: 1.1rem 3rem;" href="#enviadas"><?php if ($sent > 0) {
                                                                                                                        echo '<span class="badge badge-sm badge-success">' .  $sent . '</span>';
                                                                                                                    } ?></span>&nbsp;&nbsp;Solicitações Enviadas</a>
                    </li>
                    <li class="nav-item">
                        <?php
                        $get_all_received_memberships = $conn->prepare("SELECT * FROM memberships as M INNER JOIN products as P ON membership_product_id = product_id WHERE user__id = :user__id AND membership_status = 'PENDENTE'");
                        $get_all_received_memberships->bindParam(':user__id', $membership_affiliate_id, PDO::PARAM_INT);
                        $get_all_received_memberships->execute();
                        $received = $get_all_received_memberships->rowCount();

                        if ($offset >= $received ) {
                            $offset = 0;
                        }

                        # Busca as afiliações RECEBIDAS do usuário logado.
                        $get_received_memberships = $conn->prepare("SELECT * FROM memberships as M INNER JOIN products as P ON membership_product_id = product_id WHERE user__id = :user__id AND membership_status = 'PENDENTE' LIMIT 30 OFFSET :offset");
                        $get_received_memberships->bindParam(':user__id', $membership_affiliate_id, PDO::PARAM_INT);
                        $get_received_memberships->bindParam(':offset', $offset, PDO::PARAM_INT);
                        $get_received_memberships->execute();
                        ?>
                        <a class="nav-link active" data-toggle="tab" style="padding: 1.1rem 3rem;" href="#recebidas"><?php if ($received > 0) {
                                                                                                                    echo '<span class="badge badge-sm badge-success">' . $received . '</span>';
                                                                                                                } ?></span>&nbsp;&nbsp; Solicitações Recebidas</a>
                    </li>
                    <li style="padding: 1.1rem 3rem;" id="multi-membership-selecteds" class="d-none">
                        <div class="d-flex align-items-center">
                            <button title="Aprovar Selecionados" data-status="1" data-array="" class="update-multi-membership-status btn btn-primary btn-xs mr-1"><i class="fas fa-check"></i>&nbsp;Aprovar <span class="array-lenght"></span> Selecionado(s)</button>
                            <button title="Remover Selecionados" data-status="0" data-array="" class="update-multi-membership-status btn btn-danger btn-xs"><i class="fas fa-minus"></i>&nbsp;Remover <span class="array-lenght"></span> Selecionado(s)</button>
                        </div>
                    </li>
                </ul>
                <div class="tab-content" style="background: #fff;border-radius: 0 0 1.25rem 1.25rem;">
                    <div class="table-responsive pt-2 pr-1 tab-pane fade" id="enviadas" role="tabpanel">
                        <?php
                        # Lista as afiliações.
                        if ($get_sent_memberships->rowCount() != 0) {
                        ?>
                            <!-- <div class=" style=" overflow-x: visible;"> -->
                            <table id="sent-requests" class="table card-table display dataTablesCard" data-page-length='100' data-order='[[3, "desc"]]'>
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th class="text-center">Produto</th>
                                        <th class="text-center">Comissão</th>
                                        <th class="text-center">Data</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    while ($row = $get_sent_memberships->fetch()) {
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
                                        <tr class="" style=" padding: 1.2rem 1.5rem !important;">
                                            <td class="text-center" style="border: none;">
                                                <img class="border border-primary rounded-circle" src="<?php echo SERVER_URI . "/uploads/imagens/produtos/" . $product_image; ?>" width="40" height="40">
                                            </td>
                                            <td style="border: none;" class="col-sm-4 col-12 col-xxl-5 my-3 my-sm-0 px-0">
                                                <h5 class="mt-0 mb-0"><?php echo $product_name; ?></h5>
                                            </td>
                                            <td style="border: none;" class="ml-sm-auto col-2 col-sm-2 px-0 align-self-center align-items-center">
                                                <div class="text-center">
                                                    <h4 class="mb-0 text-black"><?php echo $product_commission; ?>%</h4>
                                                </div>
                                            </td>
                                            <td style="border: none;" class="ml-sm-auto col-2 col-sm-2 px-0 align-self-center align-items-center">
                                                <div class="text-center">
                                                    <h4 class="mb-0 text-black"><?php echo date_format(date_create($membership_start), 'd/m'); ?></h4>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php
                                    }
                                } else {
                                    ?>
                                    <h5 class="text-center text-muted pt-2 pb-5">Nenhuma solicitação enviada pendente.</h5>
                                <?php

                                }
                                ?>
                                </tbody>
                            </table>
                    </div>
                    <div class="tab-pane fade active show" id="recebidas">
                            <?php

                            # Lista as afiliações.
                            if ($get_received_memberships->rowCount() != 0) {
                            ?>
                        <table id="example2" class="table card-table display dataTablesCard dataTable no-footer" role="grid" aria-describedby="example2_info">
                            <thead>
                                <tr role="row">
                                    <th class="col-md-1" tabindex="0" aria-controls="example2" rowspan="1" colspan="1" style="width: 23.9931px;">
                                        <div class="checkbox mr-0 align-self-center">
                                            <div class="custom-control custom-checkbox ">
                                                <input type="checkbox" value="ChekedAll" class="custom-control-input" id="checkAll" required="">
                                                <label class="custom-control-label" for="checkAll"></label>
                                            </div>
                                        </div>
                                    </th>
                                    <th class="sorting col-md-4" tabindex="0" aria-controls="example2" rowspan="1" colspan="1">Nome do Afiliado</th>
                                    <th class="sorting col-md-3" tabindex="0" aria-controls="example2" rowspan="1" colspan="1">Produto</th>
                                    <th class="sorting col-md-2" tabindex="0" aria-controls="example2" rowspan="1" colspan="1">Data</th>
                                    <th class="sorting col-md-2" tabindex="0" aria-controls="example2" rowspan="1" colspan="1"></th>
                                </tr>
                            </thead>
                            <tbody>	
                            <?php
                                while ($row = $get_received_memberships->fetch()) {
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
                                    @$member_name = $get_member_name->fetch();
                                    @$member_name = $member_name[0];
                            ?>
                                <tr role="row">
                                    <td class="sorting_1">
                                        <div class="checkbox mr-0 align-self-center">
                                            <div class="custom-control custom-checkbox ">
                                                <input type="checkbox" value="<?php echo $memberships_hotcode; ?>" class="custom-control-input check-this-request" id="customCheckBox<?php echo $memberships_hotcode; ?>" required="">
                                                <label class="custom-control-label" for="customCheckBox<?php echo $memberships_hotcode; ?>"></label>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo $member_name; ?></td>
                                    <td>
                                        <?php if (strlen($product_name) > 25) {
                                            echo substr($product_name, 0, 25) . "...";
                                        } else {
                                            echo $product_name;
                                        } ?>
                                    </td>
                                    <td><?php echo date_format(date_create($membership_start), 'd/m'); ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <a href="#" title="Aprovar Solicitação" data-id="<?php echo $memberships_hotcode; ?>" data-status="1" class="update-membership-status btn btn-primary btn-xs sharp mr-1"><i class="fas fa-check"></i></a>
                                            <a href="#" title="Remover Solicitação" data-id="<?php echo $memberships_hotcode; ?>" data-status="0" class="update-membership-status btn btn-danger btn-xs sharp"><i class="fas fa-minus"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                            </tbody>
                        </table>
                            <?php

                                $pages = $received > 0 ? ceil($received / 30) : 1;
    
                                $page = min($pages, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array(
                                    'options' => array(
                                        'default'   => 1,
                                        'min_range' => 1,
                                        'max_range' => $received,
                                    ),
                                )));

                                if ($pages >= 2) {
                                ?>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <nav style="display: flex; justify-content: center;" class="mt-3">
                                                <ul class="pagination pagination-sm pagination-circle">
                                                    <?php
                                                    // PAGINAÇÃO SEM FILTRO FILTRO
                                                    if ($page > 1) { ?>
                                                            <li class="page-item page-indicator">
                                                                <a title="Página Anterior" class="page-link" href="<?php echo SERVER_URI . "/produtos/solicitacoes/?page=" . @($page - 1); ?>">
                                                                    <i class="fa fa-chevron-left"></i></a>
                                                            </li>
                                                    <?php } 
                                                    $p = 1;
                                                    while ($p <= $pages) { ?>
                                                        <li class="page-item <?php if ($p == $page) { echo 'active'; } ?>">
                                                            <a title="Ir para a página <?php echo $p; ?>" class="page-link" href="<?php echo SERVER_URI . "/produtos/solicitacoes/?page=" . $p; ?>"><?php echo $p; ?></a>
                                                        </li>
                                                    <?php
                                                        $p = $p + 1;
                                                    }
                                                    if ($page < $pages) { ?>
                                                        <li class="page-item page-indicator">
                                                            <a title="Próxima Página" class="page-link" href="<?php echo SERVER_URI . "/produtos/solicitacoes/?page=" . @($page + 1); ?>">
                                                                <i class="fa fa-chevron-right"></i>
                                                            </a>
                                                        </li>
                                                    <?php } 
                                                    ?> 
                                                </ul>
                                            </nav>
                                        </div>
                                    </div>
                                <?php
                                }
                            } else {
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