<?php
    require_once (dirname(__FILE__) . '/../../config.php'); 
?>	 	    
	<script src="<?php echo SERVER_URI; ?>/js/jquery-3.6.0.min.js" type="text/javascript"></script> 
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
    <script src="<?php echo SERVER_URI; ?>/js/global.min.js" type="text/javascript"></script>
	<script src="<?php echo SERVER_URI; ?>/js/bootstrap-select.min.js" type="text/javascript"></script>
	<script src="<?php echo SERVER_URI; ?>/js/daterangepicker.js" type="text/javascript"></script>
	<script src="<?php echo SERVER_URI; ?>/js/bootstrap-clockpicker.min.js" type="text/javascript"></script>
	<script src="<?php echo SERVER_URI; ?>/js/bootstrap-material-datetimepicker.js" type="text/javascript"></script>
	<script src="<?php echo SERVER_URI; ?>/js/picker.js" type="text/javascript"></script>
	<script src="<?php echo SERVER_URI; ?>/js/picker.time.js" type="text/javascript"></script>
	<script src="<?php echo SERVER_URI; ?>/js/picker.date.js" type="text/javascript"></script>
	<script src="<?php echo SERVER_URI; ?>/js/material-date-picker-init.js" type="text/javascript"></script>
	<script src="<?php echo SERVER_URI; ?>/js/pickadate-init.js" type="text/javascript"></script>
	<script src="<?php echo SERVER_URI; ?>/js/deznav-init.js" type="text/javascript"></script>
	<script src="<?php echo SERVER_URI; ?>/js/sweetalert2.min.js" type="text/javascript"></script>
	<script src="<?php echo SERVER_URI; ?>/js/jquery.mask.js" type="text/javascript"></script>
	<script src="<?php echo SERVER_URI; ?>/js/toastr.min.js" type="text/javascript"></script> 
<?php
	if (@$simple_checkout){
		echo '	<script src="' . SERVER_URI . '/js/simple-checkout.js" type="text/javascript"></script>
    <script src="' . SERVER_URI . '/js/main.js" type="text/javascript"></script>
';
	} else {
		echo '	<script src="' . SERVER_URI . '/js/main.js" type="text/javascript"></script>
';
	}

	if (@$signup_page){
		echo '	<script src="' . SERVER_URI . '/js/signup.js?v=10" type="text/javascript"></script>
';
	}
	
	if (isset($upsell_feedback)){
	    echo '	<script src="' . SERVER_URI . '/js/add-product-alert.js" type="text/javascript"></script>';
	}

    if (isset($password_page)) {
        echo '	<script src="' . SERVER_URI . '/js/password.js" type="text/javascript"></script>';
    }
?>