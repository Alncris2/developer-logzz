<?php
// error_reporting(-1);
// ini_set('display_errors', 1);

require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
} else if ($_SESSION['UserPlan'] == 6) {
    header('Location: ' . SERVER_URI . '/perfil/financeiro-operador/');
}

$user__id = $_SESSION['UserID'];

$userPlan = $_SESSION['UserPlan'];

if(!isset($_GET['cod'])){
    header('Location: ' . SERVER_URI . '/perfil/financeiro/');
}

$transaction_id = $_GET['cod'];

# Valor Disponível p/ Saque
$get_info_transaction = $conn->prepare('SELECT t.*, transaction_type_name as type, transaction_status_name as status FROM transactions t INNER JOIN transactions_type ON transaction_type_id = type INNER JOIN transaction_status ON transaction_status_id = status WHERE transaction_id = :transaction_id');
$get_info_transaction->execute(array('transaction_id' => $transaction_id));
if(!$details = $get_info_transaction->fetch()){
    header('Location: ' . SERVER_URI . '/perfil/financeiro/');
}

$user_id = $details['user_id'];

if($details['bank_id']){
    $account_id = $details['bank_id'];
        
    $get_account = $conn->prepare('SELECT * FROM bank_account_list WHERE account_id = :account_id AND account_user_id = :account_user_id');
    $get_account->execute(array('account_id' => $account_id, 'account_user_id' => $user_id));
    $account = $get_account->fetch();
}

if($details['orders_antecipation']){
    $orders_antecipation = explode(',', $details['orders_antecipation']);
    
    foreach($orders_antecipation as $order_number){
        if($order_number == 0){
            continue;
        }
        
        $get_order_antecipation = $conn->prepare("SELECT * FROM orders o INNER JOIN sales s ON s.sale_id = o.sale_id WHERE order_number LIKE :order_number ");
        $get_order_antecipation->execute(array('order_number' => $order_number));
        if($order_antecipation = $get_order_antecipation->fetch()) {
            $orders_info[] = $order_antecipation;
        }
    }
}

$page_title = "Detalhamento Transação | Logzz";
$profile_page = true;
$select_datatable_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

?>

<input type="hidden" id="user-plan" value="<?= $userPlan ?>">


<div class="container-fluid">
    <!-- row -->
    <div class="row">
        
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6>Detalhas da transação</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-2 text-center">
                            <p><strong>Data</strong></p>
                            <span><?= date_format(date_create($details['date_start']), 'd/m/y \<\b\r\> H:i'); ?></span>
                        </div>
                        <div class="col-2 text-center">
                            <p><strong>Codigo da transação</strong></p>
                            <span><?= '#'. $details['transaction_code'] ?></span>
                        </div>
                        <div class="col-2 text-center">
                            <p><strong>Tipo de Transação</strong></p>
                            <span><?= $details['type'] ?></span>
                        </div>
                        <div class="col-2 text-center">
                            <p><strong>Movimentação</strong></p>
                            <span>
                                <?php
                                    if ($details['type'] == 'Pedido') {
                                        echo "Entrada";
                                    } else if ($details['type'] == 'Antecipação') {
                                        echo "--";
                                    } else {
                                        echo "Saída";
                                    }
                                ?>
                            </span>
                        </div>
                        <div class="col-2 text-center">
                            <p><strong>Status</strong></p>
                            <?php if ($details['status'] == 'Cancelado') {
                                    echo '<span class="badge badge-sm d-block m-auto light badge-danger"><i class="far fa-times-circle"></i> Cancelado</span>';
                                } else if ($details['status'] == 'Concluído') {
                                    echo '<span class="badge badge-sm d-block m-auto light badge-success"><i class="far fa-check-circle"></i> Concluído</span>';
                                } else if ($details['status'] == 'Atrasado') {
                                    echo '<span class="badge badge-sm d-block m-auto light badge-info"><i class="far fa-check-circle"></i> Atrasado</span>';
                                }  else if (!$details['bank_proof'] && $details['type'] != 'Pedido') { 
                                    echo '<span class="badge badge-sm d-block m-auto light badge-warning"><i class="far fa-clock"></i> Pendente</span>';
                                } else {
                                    echo '<span class="badge badge-sm d-block m-auto light badge-primary"><i class="far fa-check-circle"></i> Liberado</span>';
                                }
                            ?>
                        </div>
                        
                        
                        <div class="col-2 text-center">
                            <p><strong>Concluido</strong></p>
                            <span><?= date_format(date_create($details['date_end']), 'd/m/y \<\b\r\> H:i'); ?></span>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6>Valores</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-2 text-center">
                            <p><strong>Valor Bruto</strong></p>
                            <span><?= 'R$ '. number_format($details['value_brute'], 2, ',', '.') ?></span>
                        </div>
                        <div class="col-2 text-center">
                            <p><strong>Valor de taxa</strong></p>
                            <span><?= 'R$ '. number_format($details['tax_value'], 2, ',', '.') ?></span>
                        </div>
                        <div class="col-2 text-center">
                            <p><strong>Valor Liquido</strong></p>
                            <span>
                                <?php
                                    if ($details['type'] == 'Antecipação') {
                                        echo "+ R$ " . number_format(($details['value_brute'] - $details['tax_value']), 2, ",", ".");
                                    }  else {
                                        echo " R$ " . number_format($details['value_liquid'], 2, ",", ".");
                                    }
                                ?>
                            </span>
                        </div>
                        <div class="col-2 text-center">
                            <p><strong>Comprovante</strong></p>
                            <a title="Ver comprovante de trasnferência" <?= $details['bank_proof'] == NULL ? "" :  "href='" . SERVER_URI . "/uploads/saques/comprovantes/" . $details['bank_proof'] . "' target='_blank'" ?>>
                                <i class="fa fa-eye<?= $details['bank_proof'] == NULL ? '-slash' : '' ?>"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (isset($account)) { ?>
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Dados Bancários do Assinante</h4>
                    </div>
                    <div class="card-body">
                        <?php
                        
                        if ($billing_detail['billing_bank_account'] > 0) {
    
                            
    
                            if ($account['account_type'] == 1) {
                                $type = "Corrente";
                            } else {
                                $type = "Poupança";
                            } ?>
                            
                        <div class="row mb-1">
                            <div class="col-sm-12">
                                <spam class="mt-2"><small>Banco</small></spam>
                                <h5 class="mb-0"><?php echo bankName($account['account_bank']); ?></h5>
                            </div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-sm-12">
                                <spam class="mt-2"><small>Agência</small></spam>
                                <h5 class="mb-0"><?php echo $account['account_agency']; ?></h5>
                            </div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-sm-12">
                                <spam class="mt-2"><small>Conta</small></spam>
                                <h5 class="mb-0"><?php echo $account['account_number']; ?></h5>
                            </div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-sm-12">
                                <spam class="mt-2"><small>Tipo de Conta</small></spam>
                                <h5 class="mb-0"><?php echo $type; ?></h5>
                            </div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-sm-12">
                                <spam class="mt-2"><small>Chave Pix</small></spam>
                                <h5 class="mb-0"><?php echo $account['account_pix_key']; ?></h5>
                            </div>
                        </div>
                    </div>
                    <?php } else { ?>
                    <div class="media bg-light p-3 rounded align-items-center mt-1" style="border-radius: 2em !important;padding: 9px 20px !important;">
                        <div class="media-body">
                            <span class="fs-16 text-black"><span>O assinante cadastrou nenhuma conta.</span></span>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
        
        <?php if (isset($orders_info)) { ?>
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h6>Pedidos Antecipados</h6>
                    </div>
                    <div class="card-body">
                        <table id="ordes_antecipation" class="table ">
                            <thead>
                                <th class="text-center">Data do pedido</th>
                                <th>Produto</th>
                                <th class="text-center">Valor Liquido</th>
                                <th class="text-center">#</th>
                            </thead>
                            <tbody>
                                <?php 
                                
                                $quant_orders = 0;
                                $values_orders = 0;
                                foreach($orders_info as $row){ 
                                    $quant_orders++;
                                    $values_orders += $row['order_liquid_value'];
                                    ?> 
                                    <tr>
                                        <td class="text-center"><?= date_format(date_create($row['order_date']), 'd/m/y H:i'); ?></td>
                                        <td><?= $row['product_name'] ?></td>
                                        <td class="text-center"><?= 'R$ '. number_format($row['order_liquid_value'], 2, ',', '.') ?></td>
                                        <td class="text-center">
                                            <a title="Ver detalhamento do pedido" href="<?= SERVER_URI . '/meu-pedido/' . $row['order_number'] ?>" target="_blank">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                            <!--<tfoot>-->
                            <!--    <tr>-->
                            <!--        <td rowspan="1" class="text-right">Qtd:</td>-->
                            <!--        <td class="text-center"><?= $quant_orders ?></td>-->
                                    
                            <!--        <td rowspan="2" class="text-right">Total: </td>-->
                            <!--        <td><?= 'R$ '. number_format($values_orders, 2, ',', '.') ?></td>-->
                            <!--    </tr>-->
                            <!--</tfoot>-->
                        </table>
                        
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>





<?php
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/mathjs/11.5.1/math.js" integrity="sha512-gIaHF8pKynuRYPvDDLkS6Gj6dS+tpE4khy3CwBIwyKxK3rOJ+LXFGM97BoZh5xtGnGSIky6TJqxfAZcGA8DN3Q==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    $(document).ready(function() {
        $('#ordes_antecipation').DataTable( {
            searching: false,
            paging: true,
            select: false,
            lengthChange: false,
            pageLength: 5,
            language: {
                "url": "//cdn.datatables.net/plug-ins/1.13.2/i18n/pt-BR.json"
            },
            fixedHeader: {
                header: true,
                footer: true
            }
        } );
    } );
</script>