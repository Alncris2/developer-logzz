<?php

require_once (dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
}

$page_title = "Gateways de Pagamento | Logzz";
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

?>
<div class="container-fluid">
  <!-- row -->
  <div class="row">
    <div class="col-xl-12 col-xxl-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">Integrações Disponíveis</h4>
        </div>
        <div class="card-body">
          <a href="mercad-pago/" class="intg-btn" id="intg-btn-notazz" style="background: url('http://localhost/dashboard.dropexpress/images/integrations/logos/mercado-pago.png');"></a>
          <a href="#" class="intg-btn" id="intg-btn-notazz" style="background: url('http://localhost/dashboard.dropexpress/images/integrations/logos/pagseguro.png');"></a>
          <a href="#" class="intg-btn" id="intg-btn-notazz" style="background: url('http://localhost/dashboard.dropexpress/images/integrations/logos/pagar-me.png');"></a>
          <a href="#" class="intg-btn" id="intg-btn-notazz" style="background: url('http://localhost/dashboard.dropexpress/images/integrations/logos/moip.png');"></a>
        </div>
      </div>
    </div>
    <!-- <div class="col-xl-12 col-xxl-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">Integrações em Andamento</h4>
        </div>
        <div class="card-body">
          
        </div>
      </div>
    </div> -->
  </div>
</div>

<?php
    require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>