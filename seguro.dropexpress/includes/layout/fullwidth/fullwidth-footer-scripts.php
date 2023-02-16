<?php
    require_once (dirname(__FILE__) . '/../../config.php');
?>		
	<script src="<?php echo CHECKOUT_URI; ?>/js/jquery-3.6.0.min.js" type="text/javascript"></script>
    <script src="<?php echo CHECKOUT_URI; ?>/js/global.min.js" type="text/javascript"></script>
	<script src="<?php echo CHECKOUT_URI; ?>/js/bootstrap-select.min.js" type="text/javascript"></script>
	<script src="<?php echo CHECKOUT_URI; ?>/js/daterangepicker.js" type="text/javascript"></script>
	<script src="<?php echo CHECKOUT_URI; ?>/js/bootstrap-clockpicker.min.js" type="text/javascript"></script>
	<script src="<?php echo CHECKOUT_URI; ?>/js/bootstrap-material-datetimepicker.js" type="text/javascript"></script>
	<script src="<?php echo CHECKOUT_URI; ?>/js/picker.js" type="text/javascript"></script>
	<script src="<?php echo CHECKOUT_URI; ?>/js/picker.time.js" type="text/javascript"></script>
	<script src="<?php echo CHECKOUT_URI; ?>/js/picker.date.js" type="text/javascript"></script>
	<script src="<?php echo CHECKOUT_URI; ?>/js/material-date-picker-init.js" type="text/javascript"></script>
	<script src="<?php echo CHECKOUT_URI; ?>/js/pickadate-init.js" type="text/javascript"></script>
	<script src="<?php echo CHECKOUT_URI; ?>/js/deznav-init.js" type="text/javascript"></script>
	<script src="<?php echo CHECKOUT_URI; ?>/js/sweetalert2.min.js" type="text/javascript"></script>
	<script src="<?php echo CHECKOUT_URI; ?>/js/jquery.mask.js" type="text/javascript"></script> 
	<script src="<?php echo CHECKOUT_URI; ?>/js/toastr.min.js" type="text/javascript"></script>
<?php  
	if (isset($simple_checkout)){     
		echo '	<script src="' . CHECKOUT_URI . '/js/simple-checkout.js?v=21" type="text/javascript"></script>
    <script src="' . CHECKOUT_URI . '/js/main.js?v=2" type="text/javascript"></script>
';
	} else {
		echo '	<script src="' . CHECKOUT_URI . '/js/main.js?v=1" type="text/javascript"></script>
';
	}
	
	if (isset($upsell_feedback)){
	    echo '	<script src="' . CHECKOUT_URI . '/js/add-product-alert.js" type="text/javascript"></script>';
	}
?>
