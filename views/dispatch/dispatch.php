<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID'])) || $_SESSION['UserPlan'] != 5) {
    header('Location: ' . SERVER_URI . '/login');
}

$page_title = "Expedição | Logzz";
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
                    <!--<a href="<?php echo SERVER_URI; ?>/expedicao/bling-sul/" class="intg-btn-5" id="intg-btn-bling-su" style="background: url('<?php echo SERVER_URI; ?>/images/integrations/bling-su.png');"></a>-->
                    <a href="<?php echo SERVER_URI; ?>/expedicao/tiny-sudeste/" class="intg-btn-5" id="intg-btn-bling-se" style="background: url('<?php echo SERVER_URI; ?>/images/integrations/tiny-sudeste.png');"></a>
                    <a href="<?php echo SERVER_URI; ?>/expedicao/tiny-centro-oeste/" class="intg-btn-5" id="intg-btn-bling-co" style="background: url('<?php echo SERVER_URI; ?>/images/integrations/tiny-centro-oeste.png');"></a>
                    <!--<a href="<?php echo SERVER_URI; ?>/expedicao/bling-nordeste/" class="intg-btn-5" id="intg-btn-bling-ne" style="background: url('<?php echo SERVER_URI; ?>/images/integrations/bling-ne.png');"></a>-->
                    <!--<a href="<?php echo SERVER_URI; ?>/expedicao/bling-norte/" class="intg-btn-5" id="intg-btn-bling-no" style="background: url('<?php echo SERVER_URI; ?>/images/integrations/bling-no.png');"></a>-->
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>