<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
}

$page_title = "Detalhes do Afiliado | Logzz";
$sale_page = true;
$select_datatable_page = true;
$sidebar_expanded = false;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');


//INICIO PRIMEIRA CONDICIONAL - Exibe página de detalhes.
if (isset($_GET['membership'])) {

    $memberships_hotcode = addslashes($_GET['membership']);

    $stmt = $conn->prepare('SELECT * FROM users INNER JOIN memberships ON users.user__id = memberships.membership_affiliate_id WHERE memberships_hotcode = :memberships_hotcode');
    $stmt->execute(array('memberships_hotcode' => $memberships_hotcode));

    if ($stmt->rowCount() != 0) {
        while ($row = $stmt->fetch()) {
            $full_name = $row['full_name'];
            $email = $row['email'];
            $user_phone = $row['user_phone'];
            $product_id = $row['membership_product_id'];
        }
    } else {
        exit;
    }

?>

    <div class="container-fluid">
        <!-- row -->
        <form id="UpdateSaleForm" action="update-sale" method="POST">
            <div class="row">

                <div class="col-md-4">
                    <div class="card">

                        <div class="card-header">
                            <h4 class="card-title">Detalhes do Afiliado</h4>
                        </div>

                        <div class="card-body">

                            <input type="hidden" name="memberships_hotcode" value="<?php echo $memberships_hotcode; ?>">

                            <div class="form-group">
                                <label class="text-label">Nome</label>
                                <input value="<?php echo $full_name;  ?>" type="text" class="form-control" disabled="disabled">
                            </div>

                            <div class="form-group">
                                <label class="text-label">Email</label>
                                <input value="<?php echo $email; ?>" type="text" class="form-control" disabled="disabled">
                            </div>


                            <div class="form-group">
                                <label class="text-label">Telefone</label>
                                <input type="text" class="form-control" value="<?php echo $user_phone;  ?>" disabled="disabled">
                            </div>

                        </div>

                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card">
                        <?php
                            $get_product_commission = $conn->prepare('SELECT product_commission FROM products WHERE product_id = :product_id');
                            $get_product_commission->execute(array('product_id' => $product_id));
                            $get_product_commission = $get_product_commission->fetch();
                            $product_commission = $get_product_commission['product_commission'];
                        ?>
                        <div class="card-header">
                            <h4 class="card-title">Comissões Personalizadas <small class="text-muted">(Comissão do produto: <b><?php echo $product_commission; ?></b>%)</small></h4>
                        </div>
                        <div class="table-responsive accordion__body--text">
                            <table class="table table-responsive-md" id="sales-datatable">
                                <thead>
                                    <tr>
                                        <th class="text-center col-md-4">Oferta</th>
                                        <th class="text-center col-md-2">Preço (R$)</th>
                                        <th class="text-center col-md-2" data-toggle="tooltip" data-placement="top" title="Comissão Personalizada da Oferta">% Oferta</th>
                                        <th class="text-center col-md-3" data-toggle="tooltip" data-placement="top" title="Comissão Personalizada da Oferta para este Afiliado">% Afiliado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $get_sales_list = $conn->prepare('SELECT * FROM sales WHERE product_id = :product_id AND sale_trashed = 0 ORDER BY sale_id DESC');
                                    $get_sales_list->execute(array('product_id' => $product_id));
                                    if ($get_sales_list->rowCount() != 0) {
                                        while ($row = $get_sales_list->fetch()) {
                                            $sale_name = $row['sale_name'];
                                            $sale_quantity = $row['sale_quantity'];
                                            $sale_price = $row['sale_price'];
                                            $sale_status = $row['sale_status'];
                                            $sale_id = $row['sale_id'];

                                            $get_sale_commission = $conn->prepare('SELECT meta_value FROM sales_meta WHERE sale_id = :sale_id AND meta_key = "custom_commission"');
                                            $get_sale_commission->execute(array('sale_id' => $sale_id));

                                            if ($get_sale_commission->rowCount()){
                                                $get_sale_commission = $get_sale_commission->fetch();
                                                $sale_commission = $get_sale_commission['meta_value'] . "%";
                                            } else {
                                                $sale_commission = "-";
                                            }
                                            
                                            $meta_key = "custom_commission_" . $memberships_hotcode;

                                            $get_custom_commission = $conn->prepare('SELECT meta_value FROM sales_meta WHERE sale_id = :sale_id AND meta_key = :meta_key');
                                            $get_custom_commission->execute(array('sale_id' => $sale_id, 'meta_key' => $meta_key));
                                            
                                        
                                            if ($get_custom_commission->rowCount() != 0) {
                                        
                                                $custom_commission = $get_custom_commission->fetch();
                                                $custom_commission = $custom_commission['meta_value'];
                                        
                                            } else {
                                        
                                                $custom_commission = "";
                                        
                                            }
                                    ?>
                                            <tr>
                                                <td class="text-center">
                                                    <?php echo utf8_encode($sale_name); ?>
                                                    <br><small class="text-muted">(<?php echo $sale_quantity; ?> unidades)</small>
                                                </td>
                                                <td class="text-center">R$ <?php echo number_format($sale_price, 2, ',', ''); ?></td>
                                                <td class="text-center"><?php echo $sale_commission; ?></td>
                                                <td class="text-center">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control maskPercent" id="<?php echo $sale_id; ?>" style="border-top-left-radius: 2em; border-bottom-left-radius: 2em;" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" value="<?php echo $custom_commission; ?>">
                                                        <div class="input-group-append">
                                                            <button class="btn btn-primary btn-xs salvar-oferta-personalizada" type="button" title="Salvar Alteração" data-hotcode="<?php echo $memberships_hotcode; ?>" data-id="<?php echo $sale_id; ?>"><i class="fas fa-save"></i></button>
                                                            <button class="btn btn-warning btn-xs excluir-oferta-personalizada" type="button" title="Excluir Comissão Personalizada" data-hotcode="<?php echo $memberships_hotcode; ?>" data-id="<?php echo $sale_id; ?>"><i class="fas fa-trash"></i></button>
                                                        </div>
                                                    </div>
                                                </td>

                                            </tr>
                                        <?php
                                        }
                                    } else {
                                        ?>
                                        <tr>
                                            <td class="text-center" colspan="5">Este produto ainda não possui Ofertas.</td>
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
        </form>
    </div>

<?php
}
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>