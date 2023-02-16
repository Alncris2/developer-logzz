<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');

session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}

$page_title = "Pedidos | Logzz";
$sidebar_expanded = false;
$orders_page = $select_datatable_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

//Busca o nível do usuário com base no ID
$user__id = $_SESSION['UserID'];

$filter_result = array();

if ($_SESSION['UserPlan'] == 5) {
    
    # Busca todos dos pedidos se o usuário for ADM.
    $get_orders_list = $stmt = $conn->prepare('SELECT * FROM orders_postback INNER JOIN sales ON orders_postback.sale__id = sales.sale_id WHERE orders_postback.order_number NOT LIKE "AFI%" ORDER BY orders_postback.sale__id ASC');
    $stmt->execute();

    $get_orders_list = $conn->prepare('SELECT * FROM orders_postback INNER JOIN sales ON orders_postback.sale__id = sales.sale_id WHERE orders_postback.order_number NOT LIKE "AFI%" ORDER BY orders_postback.sale__id ASC');
    $get_orders_list->execute();

    $get_product_list = $conn->prepare('SELECT * FROM products WHERE product_trash = 0');
    $get_product_list->execute();

    $get_affiliate_list = $conn->prepare('SELECT * FROM products WHERE product_trash = 0');


} else {
    
    # Busca apenas os pedidos do usário, se ele não for ADM.
    $stmt = $conn->prepare('SELECT * FROM orders_postback INNER JOIN sales ON orders_postback.sale__id = sales.sale_id WHERE user__id = :user__id ORDER BY orders_postback.sale__id ASC');
    $stmt->execute(array('user__id' => $user__id));

    $get_orders_list = $conn->prepare('SELECT * FROM orders_postback INNER JOIN sales ON orders_postback.sale__id = sales.sale_id WHERE user__id = :user__id ORDER BY orders_postback.sale__id ASC');
    $get_orders_list->execute(array('user__id' => $user__id));

    $get_product_list = $conn->prepare('SELECT * FROM products WHERE user__id = :user__id AND product_trash = 0 AND status = 1');
    $get_product_list->execute(array('user__id' => $user__id));

}

//Verfica se os filtros estão ativos
if (isset($_GET['filtro']) && $_GET['filtro'] == 'ativo') {

    # Filtro por DATA
    $filter_data_result = array();

    if (!(empty($_GET['data-inicio']))) {
        $data_inicio_ = pickerDateFormate($_GET['data-inicio']);
        $data_inicio_ = explode(" ", $data_inicio_);
        $data_inicio = $data_inicio_[0] . " 00:00:00";
    } else {
        $data_inicio = '2010-01-01';
    }

    if (!(empty($_GET['data-final']))) {
        $data_final_ = pickerDateFormate($_GET['data-final']);
        $data_final_ = explode(" ", $data_final_);
        $data_final = $data_final_[0] . " 23:59:59";
    } else {
        $data_final = date('Y-m-d') . " 23:59:59";
    }

    $data_ids = $conn->prepare('SELECT order_id FROM orders WHERE order_date BETWEEN :data_inicio AND :data_final');
    $data_ids->execute(array('data_inicio' => $data_inicio, 'data_final' => $data_final));

    while ($data_id = $data_ids->fetch()) {
        array_push($filter_data_result, $data_id['order_id']);
    }

    $filter_result = $filter_data_result;


    //Filtro por NOME DO CLIENTE
    if (!(empty($_GET['nome-cliente']))) {
        $filter_name_result = array();

        $nome_cliente = '%' . addslashes($_GET['nome-cliente']) . '%';

        $nome_cliente_ids = $conn->prepare('SELECT order_id FROM orders WHERE client_name LIKE :nome_cliente');
        $nome_cliente_ids->execute(array('nome_cliente' => $nome_cliente));

        while ($nome_cliente_id = $nome_cliente_ids->fetch()) {
            array_push($filter_name_result, $nome_cliente_id['order_id']);
        }

        $filter_result = array_intersect($filter_result, $filter_name_result);
    }


    //Filtro por NOME DO PRODUTO
    if (!(empty($_GET['produto']))) {
        $filter_sale_result = array();

        $produto = $_GET['produto'];

        //$produto = '%' . addslashes($_GET['nome-oferta']) . '%';

        $produto_ids = $conn->prepare('SELECT order_id FROM orders AS o INNER JOIN sales AS s ON o.sale_id = s.sale_id WHERE s.product_id LIKE :produto');
        $produto_ids->execute(array('produto' => $produto));

        while ($produto_id = $produto_ids->fetch()) {
            array_push($filter_sale_result, $produto_id['order_id']);
        }

        $filter_result = array_intersect($filter_result, $filter_sale_result);
    }

    //Filtro por NOME DO AFILIADO
    if (!(empty($_GET['afiliado']))) {
        $filter_affiliate_result = array();

        $afiliado = $_GET['afiliado'];

        //$afiliado = '%' . addslashes($_GET['nome-oferta']) . '%';

        $afiliado_ids = $conn->prepare('SELECT order_id FROM orders  WHERE affiliate_name LIKE :affiliate_name');
        $afiliado_ids->execute(array('affiliate_name' => "%" . $afiliado . "%"));

        while ($afiliado_id = $afiliado_ids->fetch()) {
            array_push($filter_affiliate_result, $afiliado_id['order_id']);
        }

        $filter_result = array_intersect($filter_result, $filter_affiliate_result);
    }

    //Filtro por STATUS
    if (!(empty($_GET['status']))) {
        $filter_status_result = array();

        $status = addslashes($_GET['status']);

        $status_ids = $conn->prepare('SELECT order_id FROM orders WHERE order_status = :o_status');
        $status_ids->execute(array('o_status' => $status));

        while ($status_id = $status_ids->fetch()) {
            array_push($filter_status_result, $status_id['order_id']);
        }

        $filter_result = array_intersect($filter_result, $filter_status_result);
    }

    //Filtro por WHATHSAPP
    if (!(empty($_GET['numero-cliente-produto']))) {
        $filter_number_result = array();

        $client_number = '%' . $_GET['numero-cliente-produto'] . '%';

        $numero_ids = $conn->prepare('SELECT order_id FROM orders WHERE client_number LIKE :client_number');
        $numero_ids->execute(array('client_number' => $client_number));

        while ($numero_id = $numero_ids->fetch()) {
            array_push($filter_number_result, $numero_id['order_id']);
        }

        $filter_result = array_intersect($filter_result, $filter_number_result);
    }
}


//Armazena o número de pedidos encontrados.
$num_orders = $stmt->rowCount();

?>

<div class="container-fluid" style="overflow-x: scroll;">
    <?php
    if ($num_orders == 0 && !($_GET['filtro'] == 'ativo')) {
        # Mensagem genérica caso todos os dados estejam zerados.
    ?>
        <div class="alert alert-success solid fade show mb-3">
            <i class="fa fa-info-circle" aria-hidden="true"></i><strong> Você ainda não fez a sua primeira venda.</strong> Assim que ela acontecer, todos os dados aparecerão aqui.
        </div>
    <?php
    }
    ?>
    <div class="d-flex flex-wrap mb-2 align-items-center justify-content-between">
        <div class="mb-3 mr-3">
            <?php
            if (isset($_GET['filtro']) && $_GET['filtro'] == 'ativo') {
            ?>
                <h6 class="fs-16 text-black font-w600 mb-0">Exibindo <?php if (count($filter_result) < $num_orders) {
                                                                            echo count($filter_result);
                                                                        } else {
                                                                            echo $num_orders;
                                                                        }; ?> pedidos, de acordo com seus filtros.</h6>
            <?php
            } else {
            ?>
                <h6 class="fs-16 text-black font-w600 mb-0"><?php echo $num_orders;  ?> Pedidos no total</h6>
            <?php
            }
            if ($_SESSION['UserPlan'] == 5) {
            ?>
                <span class="fs-14">Usuário <b>Administrador</b></span>
            <?php
            } else {
            ?>
                <span class="fs-14">Plano: <b><?php echo $_SESSION['UserPlanString']; ?></b></span>
            <?php
            }
            ?>
        </div>
        <div class="event-tabs mb-3 mr-3">
        </div>
        <div class="d-flex mb-3">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-rounded btn-outline-success mr-2" data-toggle="dropdown" aria-expanded="true"><i class="fas fa-download scale2 mr-2"></i> Exportar</button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="#" id="export-to-csv"><i class="fas fa-file-csv scale2 mr-2"></i></i> CSV</a>
                    <a class="dropdown-item" href="#" id="export-to-xlsx"><i class="fas fa-file-excel scale2 mr-2"></i> Excel</a>
                    <a class="dropdown-item" href="#" id="export-to-pdf"><i class="fas fa-file-pdf scale2 mr-2"></i> PDF</a>
                </div>
            </div>
            <button type="button" class="btn btn-rounded btn-success filter-btn" class="btn btn-success text-nowrap"><i class="fas fa-sliders-h scale2 mr-2" aria-hidden="true"></i>Filtros</button>
        </div>
    </div>

    <div class="row d-none">
        <div class="col-xl-12" style="padding-left: 5px;">
            <div class="tab-content">
                <div id="All" class="tab-pane active fade show">
                    <div class="table-responsive" style="overflow-x: auto;">
                        <table id="exp-orders-list" class="table card-table display dataTablesCard">
                            <thead>
                                <tr>
                                    <th>Pedido</th>
                                    <th>Cliente</th>
                                    <th>WhatsApp</th>
                                    <th>Produto</th>
                                    <th>Oferta</th>
                                    <th>Qnt.</th>
                                    <th>Entre.</th>
                                    <th>Faturam. (R$)</th>
                                    <th>Taxa (R$)</th>
                                    <th>Entrega (R$)</th>
                                    <th>Comissão (R$)</th>
                                    <th>Afiliado</th>
                                    <th>Comissão (R$)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php


                                while ($row = $stmt->fetch()) {

                                    if (isset($_GET['filtro']) && $_GET['filtro'] == 'ativo') {
                                        if (!(in_array($row['order_id'], $filter_result))) {
                                            continue;
                                        }
                                    }

                                    if (preg_match("/AFI/", $row['order_number']) && $_SESSION['UserPlan'] == 5) {
                                        continue;
                                    }

                                    if (preg_match("/AFI/", $row['order_number'])) {
                                        $ship_tax = 0;
                                    } else {
                                        $ship_tax = $row['product_shipping_tax'];
                                    }

                                    $client_name = $row['client_name'];

                                    if ($row['delivery_period'] == "manha") {
                                        $period = "Manhã";
                                    } else {
                                        $period = "Tarde";
                                    }

                                    if (isset($row['use_coupon']) && $row['use_coupon'] != 0) {
                                        $final_price = $row['order_final_price'];
                                    } else {
                                        $final_price = $row['sale_price'];
                                    }

                                    $LIQUIDO  = $row['order_liquid_value'];
                                ?>
                                    <tr>
                                        <td data-order="<?php echo date_format(date_create($row['order_date']), 'YmdHi'); ?>"><?php echo date_format(date_create($row['order_date']), 'd/m \<\b\r\> H:i'); ?></td>
                                        <td>
                                            <span class="text-nowrap"><?php echo $client_name; ?></span>
                                        </td>
                                        <td><i class="fab fa-whatsapp"></i>&nbsp;<?php echo $row['client_number']; ?></td>
                                        <td><?php echo $row['product_name']; ?></td>
                                        <td><?php echo $row['sale_name']; ?></td>
                                        <td class="text-center"><?php echo $row['sale_quantity']; ?></td>
                                        <td><?php echo date_format(date_create($row['order_deadline']), 'd/m'); ?><br><?php echo $period; ?></td>
                                        <td>R$ <?php echo number_format($final_price, 2, ',', '.'); ?></td>
                                        <td>R$ <?php echo number_format($row['sale_tax'], 2, ',', '.'); ?></td>
                                        <td>R$ <?php echo number_format($ship_tax, 2, ',', '.'); ?></td>
                                        <td>R$ <?php echo number_format($LIQUIDO, 2, ',', '.');  ?></td>
                                        <td>
                                            <?php
                                                $afi_order = "AFI" . $row['order_number'];

                                                $get_afi_id = $conn->prepare('SELECT user__id FROM orders WHERE order_number = :order_number');
                                                $get_afi_id->execute(array('order_number' => $afi_order));
                                                $afi_id = $get_afi_id->fetch();
                                                @$afi_id = $afi_id['user__id'];

                                                if ($afi_id != NULL && !(empty($afi_id))) {

                                                    $get_afi_name = $conn->prepare('SELECT full_name FROM users WHERE user__id = :user__id');
                                                    $get_afi_name->execute(array('user__id' => $afi_id));
                                                    $afi_name = $get_afi_name->fetch();
                                                    echo  @$afi_name = $afi_name['full_name'];
                                                } else {
                                                    echo "-";
                                                }
                                            ?>
                                        </td>
                                        <td>
                                            <?php

                                            $get_afi_commission = $conn->prepare('SELECT meta_value FROM orders_meta WHERE order_number = :order_number AND meta_key = "member_commission"');
                                            $get_afi_commission->execute(array('order_number' => $row['order_number']));

                                            if ($get_afi_commission->rowCount() != 0) {

                                                $afi_commission = $get_afi_commission->fetch();
                                                $afi_commission = $afi_commission['meta_value'];

                                                echo "R$ " . number_format($afi_commission, 2, ',', '.');
                                            } else {
                                                echo "-";
                                            }
                                            ?>
                                        </td>
                                        <td class="here-update-badge" style="padding-left: 5px;padding-right: 5px;">
                                            <?php
                                            switch ($row['order_status']) {
                                                  case 0:
                                                    $btn_classes = "light badge-success";
                                                    $status_string = "Á Enviar";
                                                    break;
                                                case 1:
                                                    $btn_classes = "light badge-dark";
                                                    $status_string = "Enviando";
                                                    break;
                                                case 2:
                                                    $btn_classes = "badge-success";
                                                    $status_string = "Enviado";
                                                    break;
                                                default:
                                                    $btn_classes = "light badge-success";
                                                    $status_string = "Á Enviar";
                                                    break;
                                            }
                                            ?>
                                            <span class="badge badge-xs <?php echo $btn_classes; ?> mb-1"><?php echo $status_string; ?></span>
                                        </td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row ">
        <div class="col-xl-12" style="padding-left: 5px;">
            <div class="tab-content">
                <div id="All" class="tab-pane active fade show">
                    <div class="table-responsive" style="overflow-x: visible;">
                        <table id="orders-list" class="table card-table display dataTablesCard" data-page-length='50' data-order='[[0, "desc"]]'>
                            <thead>
                                <tr>
                                    <th class="col-md-1">Pedido</th>
                                    <th class="col-md-3">Cliente</th>
                                    <th class="col-md-2">Produto</th>
                                    <th class="col-md-1">Qnt.</th>
                                    <th class="col-md-1">Entreg.</th>
                                    <th class="col-md-1">Fatur. (R$)</th>
                                    <th class="col-md-2">Taxa (R$)</th>
                                    <th class="col-md-2">Entreg. (R$)</th>
                                    <th class="col-md-2">Comis. (R$)</th>
                                    <th class="col-md-1">Stat.</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php


                                while ($row_two = $get_orders_list->fetch()) {

                                    if (isset($_GET['filtro']) && $_GET['filtro'] == 'ativo') {
                                        if (!(in_array($row_two['order_id'], $filter_result))) {
                                            continue;
                                        }
                                    }

                                    if (preg_match("/AFI/", $row_two['order_number']) && $_SESSION['UserPlan'] == 5) {
                                        continue;
                                    }

                                    if (preg_match("/AFI/", $row_two['order_number'])) {
                                        $ship_tax = 0;
                                    } else {
                                        $ship_tax = $row_two['product_shipping_tax'];
                                    }

                                    if (strlen($row_two['client_name']) > 10 && preg_match("/ /", $row_two['client_name'])) {
                                        $client_name = explode(" ", $row_two['client_name']);
                                        if (strlen($client_name[1]) > 4) {
                                            $client_name = $client_name[0] . " " . @$client_name[1];
                                        } else {
                                            $client_name = $client_name[0] . " " . @$client_name[1] . " " . @$client_name[2];
                                        }
                                    } else {
                                        $client_name = $row_two['client_name'];
                                    }

                                    if ($row_two['delivery_period'] == "manha") {
                                        $period = "Manhã";
                                    } else {
                                        $period = "Tarde";
                                    }

                                    if (isset($row_two['use_coupon']) && $row_two['use_coupon'] != 0) {
                                        $final_price = $row_two['order_final_price'];
                                    } else {
                                        $final_price = $row_two['sale_price'];
                                    }

                                    $LIQUIDO  = $row_two['order_liquid_value'];
                                ?>
                                    <tr>
                                        <td data-order="<?php echo date_format(date_create($row_two['order_date']), 'YmdHi'); ?>"><?php echo date_format(date_create($row_two['order_date']), 'd/m \<\b\r\> H:i'); ?></td>
                                        <td>
                                            <a href="<?php echo SERVER_URI . "/meu-pedido/" . $row_two['order_number']; ?>" target="_blank" title="Ver detalhes do Pedido"><i class="fa fa-eye"></i></a>
                                            <span class="text-nowrap"><?php echo $client_name; ?><br><small><i class="fab fa-whatsapp"></i>&nbsp;<?php echo $row_two['client_number']; ?></small></span>
                                        </td>
                                        <td><?php echo $row_two['product_name']; ?></td>
                                        <td class="text-center"><?php echo $row_two['sale_quantity']; ?></td>
                                        <td><?= $row_two['platform'] == 'braip' ? '<img src="/images/integrations/logos/braip.png" width="60px"/>' : date_format(date_create($row_two['order_deadline']), 'd/m') . "<br>" .  $period ?></td>
                                        <td>R$ <?php echo number_format($final_price, 2, ',', '.'); ?></td>
                                        <td>R$ <?php echo number_format($row_two['sale_tax'], 2, ',', '.'); ?></td>
                                        <td>R$ <?php echo number_format($ship_tax, 2, ',', '.'); ?></td>
                                        <td>R$ <?php echo number_format($LIQUIDO, 2, ',', '.');  ?></td>
                                        <td class="here-update-badge" style="padding-left: 5px;padding-right: 5px;">
                                            <?php
                                            switch ($row_two['order_status']) {
                                                case 0:
                                                    $btn_classes = "light badge-success";
                                                    $status_string = "Á Enviar";
                                                    break;
                                                case 1:
                                                    $btn_classes = "light badge-dark";
                                                    $status_string = "Enviando";
                                                    break;
                                                case 2:
                                                    $btn_classes = "badge-success";
                                                    $status_string = "Enviado";
                                                    break;
                                                default:
                                                    $btn_classes = "light badge-success";
                                                    $status_string = "Á Enviar";
                                                    break;
                                            }
                                            ?>
                                            <span class="badge badge-xs <?php echo $btn_classes; ?> mb-1"><?php echo $status_string; ?></span>

                                            <?php
                                            if ($_SESSION['UserPlan'] == 5) {
                                            ?>
                                                <div style="float: right;z-index: 999;margin-right: 20px;" class="dropdown text-sans-serif position-static"><button class="btn btn-success tp-btn-light sharp" type="button" id="order-dropdown-0" data-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="true"><span><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="18px" height="18px" viewBox="0 0 24 24" version="1.1">
                                                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                                    <rect x="0" y="0" width="24" height="24"></rect>
                                                                    <circle fill="#000000" cx="5" cy="12" r="2"></circle>
                                                                    <circle fill="#000000" cx="12" cy="12" r="2"></circle>
                                                                    <circle fill="#000000" cx="19" cy="12" r="2"></circle>
                                                                </g>
                                                            </svg></span></button>
                                                    <div class="dropdown-menu dropdown-menu-right border py-0" aria-labelledby="order-dropdown-0" x-placement="top-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(825px, 168px, 0px);">
                                                        <div class="py-2">
                                                            <a class="dropdown-item update-order-status" data-status="0" data-id="<?php echo $row_two['order_id']; ?>" href="#">Á Enviar</a></a>
                                                            <a class="dropdown-item update-order-status" data-status="1" data-id="<?php echo $row_two['order_id']; ?>" href="#">Enviando</a>
                                                            <a class="dropdown-item update-order-status" data-status="2" data-id="<?php echo $row_two['order_id']; ?>" href="#">Enviado</a>
                                                            <?php
                                                            if (@$_SESSION['UserPlan'] == 5) {
                                                            ?>
                                                                <div class="dropdown-divider"></div>
                                                                <a class="dropdown-item delete-order-link" data-id="<?php echo $row_two['order_number']; ?>" href="#">Deletar</a>
                                                            <?php
                                                            }
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php
                                            }
                                            ?>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="chatbox">
        <div class="chatbox-close"></div>
        <div class="col-xl-12">
            <div class="card">
                <div class="mt-4 center text-center ">
                    <h4 class="card-title">Filtros</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12 mb-2">
                            <form method="GET">
                                <div class="mb-3">
                                    <p class="mb-1">por Data</p>
                                    <input type="hidden" class="form-control mb-2" name="filtro" value="ativo">
                                    <input name="data-inicio" placeholder="Do dia ..." class="datepicker-default form-control picker__input mb-2" id="datepicker" readonly="" aria-haspopup="true" aria-expanded="false" aria-readonly="false" aria-owns="datepicker_root">
                                    <input name="data-final" placeholder=".. ao dia" class="datepicker-default form-control picker__input" id="datepicker" readonly="" aria-haspopup="true" aria-expanded="false" aria-readonly="false" aria-owns="datepicker_root">
                                    <div class="picker" id="datepicker_root" aria-hidden="true">
                                        <div class="picker__holder" tabindex="-1">
                                            <div class="picker__frame">
                                                <div class="picker__wrap">
                                                    <div class="picker__box">
                                                        <div class="picker__header">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="mb-1">por Cliente</p>
                                    <input type="text" class="form-control mb-2" name="nome-cliente" value="<?php echo @addslashes($_GET['nome-cliente']); ?>" placeholder="Nome do Cliente">
                                    <div class="form-group">
                                        <label class="text-label">por Produto</label>
                                        <select id="select-ship-product" class="d-block default-select">
                                            <option disabled selected>Nome do Produto</option>
                                            <?php
                                            while ($prodcut = $get_product_list->fetch()) {
                                            ?>
                                                <option value="<?php echo $prodcut['product_id']; ?>"><?php if (strlen($prodcut['product_name']) > 30) {
                                                                                                            echo substr($prodcut['product_name'], 0, 30) . "...";
                                                                                                        } else {
                                                                                                            echo $prodcut['product_name'];
                                                                                                        } ?></option>
                                            <?php
                                            }
                                            ?>
                                        </select>
                                        <input type="hidden" id="text-ship-product" name="produto" value="" required>
                                    </div>
                                    <p class="mb-1">por Status</p>
                                    <select class="form-control default-select" id="select-filter-status-id">
                                        <option selected disabled>Todos</option>
                                        <option value="0">Á Enviar</option>
                                        <option value="1">Enviando</option>
                                        <option value="2">Enviado</option>
                                    </select>
                                    <input type="hidden" id="text-filter-status-id" name="status" value="" required>
                                    <p class="mb-1 mt-2">por WhatsApp</p>
                                    <input type="text" class="form-control mb-2" name="numero-cliente-produto" value="<?php echo @addslashes($_GET['numero-cliente-produto']); ?>" placeholder="Número do Cliente">
                                    <p class="mb-1">por Afiliado</p>
                                    <input type="text" class="form-control mb-2" name="afiliado" value="<?php echo @addslashes($_GET['afiliado']); ?>" placeholder="Nome do Afiliado">
                                </div>
                                <button type="submit" class="btn btn-block btn-success text-nowrap mt-2"><i class="fas fa-sliders-h" aria-hidden="true"></i> Aplicar Filtros</button>
                                <a href="<?php echo SERVER_URI; ?>/pedidos/" class="btn btn-block mt-2">Limpar Filtros</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<?php
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>