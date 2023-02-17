<?php
// error_reporting(-1);  
// ini_set('display_errors', 1);
require_once(dirname(__FILE__) . '/../../includes/config.php');

session_name(SESSION_NAME);
session_start(); 

function monte_string_array ($input) {
    if(strpos($input, ',')){ 
        $array1 = explode(',', $input);
        $array2 = array();
        for ($i = 0; $i < count($array1); $i++) { 
            array_push($array2, $array1[$i]);
        } 
        return "'" . implode("','", $array2) . "'";
    } else {
        return "'" . $input . "'"; 
    }
}

function monte_number_array ($input, $minus = false) {
    if(strpos($input, ',')){ 
        $array1 = explode(',', $input);
        $array2 = array();
        for ($i = 0; $i < count($array1); $i++) { 
            array_push($array2, ( $minus ? $array1[$i] - 1 : $array1[$i] ));
        } 
        return "'" . implode("','", $array2) . "'";
    } else {
        return $minus ? $input - 1 : "'" . $input . "'"; 
    }
}

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}

// if ($_SESSION['UserPlan'] == 6) {
//     header('Location: ' . SERVER_URI . '/pedidos/lista-operador');
//     exit;
// } 

$user__id = $_SESSION['UserID'];

$filter_data = $_POST['filter_data'];
parse_str($filter_data, $params);

$referenceData = ' WHERE orders.order_date BETWEEN :start_date AND :final_date ';

if($params['reference-data'] == 'entrega') {
    $referenceData = ' WHERE STR_TO_DATE((CASE WHEN orders.order_status = 3 THEN orders.order_delivery_date ELSE orders.order_deadline END),"%Y-%m-%d %T") between :start_date AND :final_date ';
}

# Filtro por DATA
$filter_data_result = array();

if (!(empty($params['data-inicio']))) {
    $start_date = pickerDateFormate($params['data-inicio']);
    $start_date = explode(" ", $start_date);
    $start_date = $start_date[0] ." ". $params['time-inicio'];
} else {
    $start_date = '2010-01-01';
}

if (!(empty($params['data-final']))) {
    $final_date = pickerDateFormate($params['data-final']);
    $final_date = explode(" ", $final_date);
    $final_date = $final_date[0] ." ". $params['time-final'];
} else {
    $final_date = date('Y-m-d') ." ". $params['time-final'];
}
    
$date_ids = $conn->prepare("SELECT order_id FROM orders $referenceData AND order_number NOT LIKE '%AFI%' ");
$date_ids->execute(array('start_date' => $start_date, 'final_date' => $final_date));

while ($date_id = $date_ids->fetch()) {
    array_push($filter_data_result, $date_id['order_id']); 
}   

$filter_result = $filter_data_result;

//Filtro por NOME DO CLIENTE
if (!(empty($params['nome-cliente']))) {
    $filter_name_result = array();

    $cliente_name = '%' . addslashes($params['nome-cliente']) . '%';

    $name_cliente_ids = $conn->prepare('SELECT order_id FROM orders WHERE client_name LIKE :cliente_name');
    $name_cliente_ids->execute(array('cliente_name' => $cliente_name));

    while ($name_cliente_id = $name_cliente_ids->fetch()) {
        array_push($filter_name_result, $name_cliente_id['order_id']);
    }       

    $filter_result = array_intersect($filter_result, $filter_name_result);
}

//Filtro por NOME DO PRODUTO
if (!(empty($params['produto']))) {
    $filter_sale_result = array();

    $product = trim($params['produto']);

    $product = monte_string_array($params['produto']);

    $product_ids = $conn->prepare("SELECT order_id FROM orders WHERE product_name IN ( $product ) ");
    $product_ids->execute();

    while ($product_id = $product_ids->fetch()) {
        array_push($filter_sale_result, $product_id['order_id']);
    }

    $filter_result = array_intersect($filter_result, $filter_sale_result);
}

//Filtro por NOME DO AFILIADO
if (!(empty($params['afiliado']))) {
    $filter_affiliate_result = array();
    $afiliatte = $params['afiliado'];
    if ($UserPlan == 5) {

        $all_orders  = $conn->prepare("SELECT o.order_id , o.order_number FROM orders AS o"); 
        $all_orders->execute();

        foreach( $all_orders->fetchAll() as $row_all ){
            $affiliate_ids  = $conn->prepare("SELECT o.order_id , REPLACE (o.order_number, 'AFI', '') as find_order  FROM orders AS o  WHERE  o.order_number LIKE :order_number AND o.user__id = :aff_id");
            $affiliate_ids->execute([ 'order_number' => "%".$row_all["order_number"], 'aff_id' => $afiliatte]);
            
            
            while($row_aff = $affiliate_ids->fetch()){
                if($row_aff["find_order"] == $row_all["order_number"]){                        
                    array_push($filter_affiliate_result, $row_all['order_id']);
                }

            }
        }
        
    }else{

        $all_orders  = $conn->prepare("SELECT o.order_id , o.order_number FROM orders AS o  WHERE  o.user__id = :user__id"); 
        $all_orders->execute([ 'user__id' => $user__id]);

        foreach( $all_orders->fetchAll() as $row_all ){
            $affiliate_ids  = $conn->prepare("SELECT o.order_id , REPLACE (o.order_number, 'AFI', '') as find_order  FROM orders AS o  WHERE  o.order_number LIKE :order_number AND o.user__id = :aff_id");
            $affiliate_ids->execute([ 'order_number' => "%".$row_all["order_number"], 'aff_id' => $afiliatte]);
            
            
            while($row_aff = $affiliate_ids->fetch()){
                if($row_aff["find_order"] == $row_all["order_number"]){                        
                    array_push($filter_affiliate_result, $row_all['order_id']);
                }

            }
        }            
    }
}

//Filtro por STATUS
if (!(empty($params['status']))) {

    $filter_status_result = array();  
    
    $status = monte_number_array($params['status'], true);          
    $status_ids = $conn->prepare("SELECT order_id, order_status FROM orders WHERE order_status in ( $status )"); 
    $status_ids->execute();  

    while ($status_id = $status_ids->fetch()) {
        array_push($filter_status_result, $status_id['order_id']);
    }

    $filter_result = array_intersect($filter_result, $filter_status_result);
}

//Filtro por WHATHSAPP
if (!(empty($params['numero-cliente-produto']))) {
    $filter_number_result = array();

    $client_number = '%' . $params['numero-cliente-produto'] . '%';

    $number_ids = $conn->prepare('SELECT order_id FROM orders WHERE client_number LIKE :client_number');
    $number_ids->execute(array('client_number' => $client_number));

    while ($number_id = $number_ids->fetch()) {
        array_push($filter_number_result, $number_id['order_id']);
    }

    $filter_result = array_intersect($filter_result, $filter_number_result);

    $filteractive['numero-cliente'] =  [ $params['numero-cliente-produto'], count($filter_number_result)];
}

if (!(empty($params['operacao']))) {
    $filter_operation_result = array();

    $operation_id = $params['operacao'];

    $operation_ids = $conn->prepare('SELECT o.order_id FROM orders o INNER JOIN local_operations_orders loo ON loo.order_id = o.order_id WHERE loo.operation_id = :operation_id');
    $operation_ids->execute(array('operation_id' => $operation_id));

    while ($order_id = $operation_ids->fetch()) {
        array_push($filter_operation_result, $order_id['order_id']);
    }

    $filter_result = array_intersect($filter_result, $filter_operation_result);
}

if (!(empty($params['operador']))) {
    $filter_operator_result = array();

    $operator_id = $params['operador'];

    if($operator_id != "indefinido") {
        $operator_ids = $conn->prepare('SELECT o.order_id FROM orders o INNER JOIN local_operations_orders loo ON loo.order_id = o.order_id WHERE loo.responsible_id = :operator_id');
        $operator_ids->execute(array('operator_id' => $operator_id));
    } else {
        $operator_ids = $conn->prepare('SELECT o.order_id FROM orders o INNER JOIN local_operations_orders loo ON loo.order_id = o.order_id WHERE loo.responsible_id IS NULL');
        $operator_ids->execute();
    }

    while ($order_id = $operator_ids->fetch()) {
        array_push($filter_operator_result, $order_id['order_id']);
    }

    $filter_result = array_intersect($filter_result, $filter_operator_result);
}

//Filtro por RESPONSAVEL
if (!(empty($params['responsavel']))) {
    $filter_responsible_result = array();

    $responsavel = $params['responsavel'];

    if ($responsavel == "indef") {
        $responsible_ids = $conn->prepare('SELECT DISTINCT o.order_id FROM orders o INNER JOIN local_operations_orders lo ON lo.order_id=o.order_id WHERE lo.operation_id = :operation_id AND lo.responsible_id IS NULL');
        $responsible_ids->execute(array('operation_id' => $operation_id));
    } else {
        $responsible_ids = $conn->prepare('SELECT o.order_id FROM orders o INNER JOIN local_operations_orders lo ON lo.order_id=o.order_id WHERE lo.operation_id = :operation_id AND lo.responsible_id = :responsible');
        $responsible_ids->execute(array('operation_id' => $operation_id, 'responsible' => $operator_id));
    }

    while ($responsible_id = $responsible_ids->fetch()) {
        array_push($filter_responsible_result, $responsible_id['order_id']);
    }

    $filter_result = array_intersect($filter_result, $filter_responsible_result);
}


$query_operator = false;
//Filtro por RESPONSAVEL
if (!(empty($params['filtro'] && $params['filtro'] == 'operador'))) {

    $get_operation_id = $conn->prepare("SELECT lo.*, u.created_at FROM logistic_operator lo INNER JOIN users u ON lo.user_id=u.user__id WHERE lo.user_id = :user__id");
    $get_operation_id->execute(array("user__id" => $user__id));

    $data = $get_operation_id->fetch();
    $operation_id = $data["local_operation"];
    $operator_id = $data["operator_id"];
    $created_at = $data["created_at"];

    # Busca os produtos dos pedidos
    $get_product_list = $conn->prepare('SELECT DISTINCT products.* FROM products INNER JOIN orders ON products.product_id = orders.product_id INNER JOIN local_operations_orders ON local_operations_orders.order_id = orders.order_id WHERE product_trash = 0 AND local_operations_orders.operation_id = :operation_id');
    $get_product_list->execute(array('operation_id' => $operation_id));

    # Busca os locais da operação do usuário
    $get_order_locale = $conn->prepare("SELECT id, city FROM operations_locales WHERE operation_id = :operation_id");
    $get_order_locale->execute(array("operation_id" => $operation_id));

    # Busca as taxas de entrega da operação do usuário
    $get_delivery_taxes = $conn->prepare("SELECT * FROM operations_delivery_taxes WHERE operation_id = :operation_id AND operator_id = :operator_id");
    $get_delivery_taxes->execute(array("operation_id" => $operation_id, "operator_id" => $operator_id));

    $locales = array();
    $cities = $get_order_locale->fetchAll();
    $delivery_taxes = $get_delivery_taxes->fetchAll();

    # Relaciona as taxas de entrega aos locais em um array de chave-valor
    foreach ($cities as $city) {
        for ($i = 0; $i < sizeof($delivery_taxes); $i++) {
            $tax = $delivery_taxes[$i];
            if ($city["id"] == $tax["operation_locale"]) {
                if($city["city"][0] == ' ')
                    $city["city"] = substr($city["city"], 1);

                

                $locales[$city["city"]] = $tax["complete_delivery_tax"] . "--" . $tax["frustrated_delivery_tax"];
            }
        }
    }


    $query_operator = true;
    
}

$page_title = "Pedidos | Logzz";
$sidebar_expanded = false;
$orders_page = $select_datatable_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/fullwidth/fullwidth-header.php');


$result = "'" . implode("','", $filter_result) . "'";
$stmt = $conn->prepare("SELECT * FROM orders INNER JOIN sales ON orders.sale_id = sales.sale_id WHERE orders.order_id IN ($result) ");  // NOT LIKE AFI
$stmt->execute();
$quantify_orders = $stmt->rowCount();

if($query_operator){ 
    $stmt = $conn->prepare(
        "SELECT *
        FROM orders o 
        INNER JOIN local_operations_orders lo ON lo.order_id = o.order_id 
        INNER JOIN sales s ON o.sale_id = s.sale_id 
        WHERE lo.operation_id = :operation_id 
            AND (lo.responsible_id IS NULL OR lo.responsible_id = :operator_id) 
            AND o.order_id IN ($result) 
            AND o.order_number NOT LIKE 'AFI%'
    ");  // NOT LIKE AFI
    $stmt->execute(array('operation_id' => $operation_id, 'operator_id' => $operator_id));

    $stmt_produto = $conn->prepare(
        "SELECT o.product_name, SUM(CASE WHEN o.platform <> null OR o.platform != '' THEN o.order_quantity ELSE s.sale_quantity END) AS qtd_product_sold , p.product_image, p.product_number
        FROM orders o 
        INNER JOIN local_operations_orders lo ON lo.order_id = o.order_id 
        INNER JOIN sales s ON o.sale_id = s.sale_id 
        INNER JOIN products p ON p.product_id = s.product_id
        WHERE lo.operation_id = :operation_id  
            AND (lo.responsible_id IS NULL OR lo.responsible_id = :operator_id) 
            AND o.order_id IN ( $result ) 
            AND o.order_number NOT LIKE 'AFI%'
            GROUP BY o.product_name;
        ");  // NOT LIKE AFI
        
        $quantify_orders = $stmt->rowCount();
        $stmt_produto->execute(array('operation_id' => $operation_id, 'operator_id' => $operator_id)); 


} else {
    $stmt = $conn->prepare("SELECT * FROM orders INNER JOIN sales ON orders.sale_id = sales.sale_id WHERE orders.order_id IN ($result) ");  // NOT LIKE AFI    
    $stmt->execute();

    $stmt_produto = $conn->prepare(
        "SELECT o.product_name, SUM(CASE WHEN o.platform <> null OR o.platform != '' THEN o.order_quantity ELSE s.sale_quantity END) AS qtd_product_sold , p.product_image, p.product_number
        FROM orders o
        INNER JOIN sales s ON o.sale_id = s.sale_id 
        INNER JOIN products p ON p.product_id = s.product_id
    WHERE o.order_id IN ($result) 
        AND o.order_number NOT LIKE 'AFI%' 
    GROUP BY o.product_name");  // NOT LIKE AFI    
    $stmt_produto->execute();  
} 

?>
<style>
    .info{
        padding-top: 2rem;
        padding-bottom: 1rem;
        border-bottom: 0.15rem solid hsl(0deg 0% 32%);
    }
    .info:last-child{
        border-bottom: none;
        padding-bottom: 0;
    }
    /* 
    @media print {
        div.card-header {
            position: fixed;
            top: 0;
        }
    } */

    body {
        color: black;
        font-size: 18px;
    }
</style>

<div class="container-fluid">    
    <div class="row ">
        <div class="col-xl-12" style="padding-left: 5px;">
            <div class="card">
                <div class="card-header">  
                    <div class="col-md-8 form-group"> 
                        <h3 class="card-title">Expedição Local: <span><?php echo $quantify_orders ?> <?= $quantify_orders == 1 ? 'Pedido' : 'Pedidos' ?></span></h3>
                    </div>
                    <div class="col-md-4 form-group d-flex">
                        Período:
                        <spam><?= date_format (date_create($start_date), 'd/m/y')  .' à '. date_format (date_create($final_date), 'd/m/y') ?></spam>
                    </div>
                </div>                
                <div class="card-body">  
                <?php 
                    if ($stmt_produto->rowCount() != 0) { ?>
                        <div class="col-md-12">  
                            <table class="table table-bordered"> 
                                <thead>
                                    <tr>
                                        <th class="text-center">Cód</th>
                                        <th>Produtos</th>
                                        <th>Unidades</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($product_qtd = $stmt_produto->fetch()) { 
                                        
                                        # verifica se a imagem principal é um video ou imagem
                                        $product_qtd_img = $product_qtd['product_image'];
                                        $image_filetype_array = explode('.', $product_qtd_img);
                                        $filetype = strtolower(end($image_filetype_array));                                    
                                        if(in_array($filetype, ['mp4', 'mkv'])){
                                            # busca as imagens associadas ao produto
                                            $product_images_data = $conn->prepare('SELECT product_image FROM products_images WHERE product_id = :product_id LIMIT 1');
                                            $product_images_data->execute(array('product_id' => $product_qtd['product_id']));
                                            while ($image_sec = $product_images_data->fetch()) {
                                                $product_qtd_img = $image_sec['product_image'];
                                            }
                                        }   
                                    ?>
                                        <tr>
                                            <td class="text-center py-2"><?= $product_qtd['product_number'] ?></td>
                                            <td class="py-2"><?= $product_qtd['product_name'] ?></td>
                                            <td class="py-2"><?= $product_qtd['qtd_product_sold'] ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <div style="page-break-before: always;">
                        </div> 
                    <?php }

                    if ($quantify_orders != 0){ $cont = 0;
                        foreach($stmt->fetchAll() as $row) {

                        // echo '<pre>';
                        // var_dump($row); 
                        // echo '</pre>';        

                        if ($row['platform'] == null) {
                            switch ($row['order_status']) {
                                case 1:
                                    $btn_classes = "light badge-success";
                                    $status_string = "Reag.";
                                    break;
                                case 2:
                                    $btn_classes = "light badge-warning";
                                    $status_string = "Atra.";
                                    break;
                                case 3:
                                    $btn_classes = "badge-success";
                                    $status_string = "Comp.";
                                    $comissinOp  = (int) explode("--", @$locales[$cityname])[0]; 
                                    break;
                                case 4:
                                    $btn_classes = "light badge-dark";
                                    $status_string = "Frust.";
                                    $comissinOp  = (int) explode("--", @$locales[$cityname])[1]; 
                                    break;
                                case 5:
                                    $btn_classes = "light badge-danger";
                                    $status_string = "Canc.";
                                    break;
                                case 9:
                                    $btn_classes = "light badge-info";
                                    $status_string = "Reembo."; 
                                    $comissinOp  = (int) explode("--", @$locales[$cityname])[0]; 
                                    break;
                                case 10:
                                    $btn_classes = "light badge-secondary";
                                    $status_string = "Confirm."; 
                                    break;
                                case 11:
                                    $btn_classes = "light badge-warning"; 
                                    $status_string = "Expirado"; 
                                    break;
                                default:
                                    $btn_classes = "light badge-success";
                                    $status_string = "Agen.";
                                    break;
                            }
                        } else {
                            switch ($row['order_status']) {
                                case 6:
                                    $btn_classes = "light badge-success";
                                    $status_string = "À Enviar.";
                                    break;
                                case 7:
                                    $btn_classes = "light badge-dark";
                                    $status_string = "Enviando.";
                                    break;
                                case 8:
                                    $btn_classes = "badge-success";
                                    $status_string = "Enviado.";
                                    break;
                            }
                        }  
                ?>              

                        <div class="info row" 
                            <?php 
                            echo $cont == 2 ? 'style="border-bottom: none;"' : '';
                            echo $cont == 3 ? 'style="page-break-before: always;"' : ''; 
                            $cont == 3 ? $cont = 1 : $cont++ ?>>
                            <div class="col-md-4 form-group">
                                <b>Pedido:</b>
                                <spam><?php echo '#'. $row['order_number']; ?></spam>
                            </div>
                            <div class="col-md-4 form-group">
                                <b>Data e hora do pedido:</b>
                                <spam><?=  date_format (date_create($row['order_date']), 'd/m/y H:i'); ?></spam>
                            </div>
                            <div class="col-md-4 form-group">
                                <b>Data de Entrega:</b>
                                <spam><?=  date_format (date_create($row['order_deadline']), 'd/m/y'); ?></spam>
                            </div>
                            <div class="col-md-6 form-group">
                                <b>Cliente:</b>
                                <spam><?php echo ucwords(strtolower($row['client_name'])) ?></spam>
                            </div>
                            <div class="col-md-6 form-group">
                                <b>Documento:</b>
                                <spam><?php echo $row['client_document']; ?></spam>
                            </div>
                            <div class="col-md-6 form-group">
                                <b>Telefone:</b>
                                <spam><?php echo $row['client_number']; ?></spam>
                            </div>
                            <!-- <div class="col-md-6 form-group">
                                <b>Período:</b>
                                <spam><?php echo $row['delivery_period']; ?></spam>
                            </div> -->
                            <div class="col-md-12 form-group">
                                <b>Endereço:</b>
                                <spam><?php echo str_replace('<br>',', ', $row['client_address']); ?></spam>
                            </div>
                            <div class="col-md-1 form-group">
                                <?php             
                                    $stmt_product = $conn->prepare('SELECT product_image FROM products WHERE product_id = :product_id AND (user__id = :user__id AND product_trash = 0)');
                                    $stmt_product->execute(array('product_id' => $row['product_id'], 'user__id' => $row['user__id']));

                                    if($image = $stmt_product->fetch()) {
                                        # verifica se a imagem principal é um video ou imagem
                                        $product_image = $image['product_image'];
                                        $image_filetype_array = explode('.', $product_image);
                                        $filetype = strtolower(end($image_filetype_array));                                    
                                        if(in_array($filetype, ['mp4', 'mkv'])){
                                            # busca as imagens associadas ao produto
                                            $product_images_data = $conn->prepare('SELECT product_image FROM products_images WHERE product_id = :product_id LIMIT 1');
                                            $product_images_data->execute(array('product_id' => $row['product_id']));
                                            while ($image = $product_images_data->fetch()) {
                                                $product_image = $image['product_image'];
                                            }
                                        }   
                                    }                                                                 
                                ?> 
                                <img class="img-fluid rounded-circle" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;" src="<?php echo SERVER_URI . '/uploads/imagens/produtos/' . $product_image ?>" />
                            </div>
                            <div class="col-md-11 row form-group">
                                <div class="col-md-12 form-group">
                                    <b>Produto:</b>
                                    <spam><?php echo $row['product_name']; ?></spam>
                                </div>                            
                                <div class="col-md-4 form-group">
                                    <b>Quantidade:</b>
                                    <spam><?php echo $row['sale_quantity']; ?></spam>
                                </div>
                                <div class="col-md-4 form-group">
                                    <b>Valor:</b>
                                    <spam><?php echo "R$ ". number_format($row['order_final_price'], 2, ',', '.') ?></spam>
                                </div>
                                <div class="col-md-4 form-group">
                                    <b>Status:</b>
                                    <spam><?php echo $status_string ?></spam>
                                </div>
                            </div>
                        </div>
                    <?php             
                        } 
                    } ?>
                    
                </div>
            </div>
        </div>
    </div>

</div>
<?php require_once(dirname(__FILE__) . '/../../includes/layout/fullwidth/fullwidth-footer.php'); ?>
