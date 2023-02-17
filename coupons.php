<?php
require_once ('includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
}


//Verify GET values integrity
if (isset($_GET['p']) && isset($_GET['cupom'])){
    $coupon_product_id = intval(addslashes($_GET['p']));
    $coupon_string = addslashes($_GET['cupom']);

    $stmt = $conn->prepare('SELECT * FROM coupons WHERE coupon_product_id = :coupon_product_id AND coupon_string = :coupon_string');
    $stmt->execute(array('coupon_product_id' => $coupon_product_id, 'coupon_string' => $coupon_string));
    

    if ($stmt->rowCount() != 0){
        while($row = $stmt->fetch()) {
          $coupon_id             = $row['coupon_id'];
          $coupon_product_id     = $row['coupon_product_id'];
          $coupon_linked_sales   = $row['coupon_linked_sales'];
          $coupon_string         = $row['coupon_string'];
          $coupon_percent        = $row['coupon_percent'];
          $coupon_limit          = $row['coupon_limit'];
          $coupon_limit_date     = $row['coupon_limit_date'];
          $coupon_trashed        = $row['coupon_trashed'];
        } 
      } else {
        header('Location: ' . SERVER_URI . '/meus-produtos');
    }

$page_title = "Editar Cupom | Logzz";
$multselect_preload = $coupon_linked_sales;
require_once('includes/layout/default/default-header.php');

?>

<div class="container-fluid"><!-- row -->
    <div class="row">
      <div class="col-xl-6 col-xxl-6">
        <div class="card">
         
          <div class="card-header">
            <h4 class="card-title">Detalhes</h4>
          </div>

          <div class="card-body">
           
            <form id="UpdateCouponForm" action="editar-cupom" method="POST">

                      <input type="hidden" id="ActionInput" name="action" value="update-coupon">
                      <input type="hidden" name="cupom" value="<?php echo $coupon_id; ?>">

                        <div class="form-group">
                          <label class="text-label">Texto do Cupom<i class="req-mark">*</i></label>
                          <input type="text" name="texto-cupom" style="text-transform: uppercase;" class="form-control" value="<?php echo $coupon_string;  ?>" required>
                        </div>
                        
                        <div class="form-group">
                          <label class="text-label">Ofertas<i class="req-mark">*</i></label>
                              <select class="multselect_preload" name="ofertas-vinculadas[]" multiple="multiple">
                              <?php
                                  $stmt = $conn->prepare('SELECT * FROM sales WHERE product_id = :product_id AND sale_trashed = 0 ORDER BY sale_id DESC');
                                  $stmt->execute(array('product_id' => $coupon_product_id));
                                  
                                  if ($stmt->rowCount() != 0){
                                    $s = 1;
                                      while($row = $stmt->fetch()) {
                                          $sale_name = $row['sale_name'];
                                          $sale_id = $row['sale_id'];
                                  ?>
                                  <option value="<?php echo $sale_id;  ?>"><?php echo $sale_name;  ?></option>
                                  <?php
                                      }
                                    }
                                  ?>
                              </select>
                              <input type="hidden" id="ofertas-vinculadas-mult-select-text" name="ofertas-vinculadas-mult-select-text" value="">
                        </div>
                        <div class="form-group">
                          <label class="text-label">% de Desconto<i class="req-mark">*</i></label>
                          <input type="text" name="porcentagem-cupom" class="form-control" value="<?php echo $coupon_percent;  ?>" required>
                        </div>
                        <div class="form-group">
                          <label class="text-label">Limite de Usos<i class="req-mark">*</i></label>
                          <input  type="number" name="quantidade-cupom" class="form-control" value="<?php echo $coupon_limit;  ?>" required>
                     </div>
                     <button type="submit" class="btn btn-success">Salvar Alterações</button>
                      <a href="<?php echo SERVER_URI . "/meus-produtos?detalhe=" . $coupon_product_id; ?>" class="btn btn-warning light" data-dismiss="modal">Cancelar</a>
              </form>
          </div>

        </div>
      </div>
</div>
</div>

<?php
}


else {
  header('Location: ' . SERVER_URI . '/meus-produtos');
}

    require_once('includes/layout/default/default-footer.php');
?>