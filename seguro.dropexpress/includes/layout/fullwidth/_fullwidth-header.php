<?php
    require_once (dirname(__FILE__) . '/../../config.php');
    
    $page_description = "Ofereça seus produtos com entrega em 1 dia e pagamento no ato e veja suas vendas explodirem!";
?>
<!DOCTYPE html>
<html lang="pt-br" class="h-100">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php if(isset($page_title)) {echo $page_title;} else {echo 'DropExpress | Dashboard';}?></title>
    <meta name="description" content="<?php if(isset($page_description)) {echo $page_description;} else {echo 'DropExpress';}?>"/>
    <meta name="title" content="<?php if(isset($page_title)) {echo $page_title;} else {echo 'DropExpress | Dashboard';}?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://dashboard.dropexpress.com.br/">
    <meta property="og:title" content="<?php if(isset($page_title)) {echo $page_title;} else {echo 'DropExpress | Dashboard';}?>">
    <meta property="og:description" content="<?php if(isset($page_description)) {echo $page_description;} else {echo 'DropExpress';}?>">
    <!-- <meta property="og:image" content="https://dashboard.dropexpress.com.br/images/meta-og-image-dashboard-drop-express.jpg"> -->

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://dashboard.dropexpress.com.br/">
    <meta property="twitter:title" content="<?php if(isset($page_title)) {echo $page_title;} else {echo 'DropExpress | Dashboard';}?>">
    <meta property="twitter:description" content="<?php if(isset($page_description)) {echo $page_description;} else {echo 'DropExpress';}?>">
    <!-- <meta property="twitter:image" content="https://dashboard.dropexpress.com.br/images/meta-og-image-dashboard-drop-express.jpg"> -->

    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo SERVER_URI; ?>/images/favicon.png">
    <link href="<?php echo SERVER_URI; ?>/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
	<link href="<?php echo SERVER_URI; ?>/css/bootstrap-material-datetimepicker.css" rel="stylesheet" type="text/css"/>
	<link href="<?php echo SERVER_URI; ?>/css/default.css" rel="stylesheet" type="text/css"/>
	<link href="<?php echo SERVER_URI; ?>/css/default.date.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo SERVER_URI; ?>/css/all.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo SERVER_URI; ?>/css/style.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo SERVER_URI; ?>/css/sweetalert2.min.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo SERVER_URI; ?>/css/toastr.min.css" rel="stylesheet" type="text/css"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&family=Roboto:wght@100;300;400;500;700;900&display=swap" rel="stylesheet">
    
    <?php
    
    if(isset($fb_pixel_purchase)){
        echo $fb_pixel_purchase;
    }
    
    ?>

</head>

<body class="h-100">
    <div class="authincation h-100">
        <div class="container h-100">
            <div class="row justify-content-center h-100 align-items-center">
