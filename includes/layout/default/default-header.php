<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php if(isset($page_title)) {echo $page_title;} else {echo 'Logzz | Dashboard';}?></title>
    <meta name="description" content="<?php if(isset($page_description)) {echo $page_description;} else {echo 'Logzz';}?>"/>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo SERVER_URI; ?>/images/favicon.png">
	
    <!-- Stylesheets -->
    <link href="<?php echo SERVER_URI; ?>/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo SERVER_URI; ?>/css/bootstrap-material-datetimepicker.css" rel="stylesheet" type="text/css"/>
	<link href="<?php echo SERVER_URI; ?>/css/default.css" rel="stylesheet" type="text/css"/>
	<link href="<?php echo SERVER_URI; ?>/css/default.date.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo SERVER_URI; ?>/css/all.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo SERVER_URI; ?>/css/jquery.dataTables.min.css" rel="stylesheet" type="text/css"/>
	<link href="<?php echo SERVER_URI; ?>/css/dispatche_style.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo SERVER_URI; ?>/css/toastr.min.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo SERVER_URI; ?>/css/sweetalert2.min.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo SERVER_URI; ?>/css/summernote.css" rel="stylesheet" type="text/css"/>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&amp;family=Roboto:wght@100;300;400;500;700;900&amp;display=swap" rel="stylesheet" type="text/css"/>
    
</head> 

<body>

    <!--*******************
        Preloader start
    ********************-->
    <div id="preloader">
        <div class="sk-three-bounce">
            <div class="sk-child sk-bounce1"></div>
            <div class="sk-child sk-bounce2"></div>
            <div class="sk-child sk-bounce3"></div>
        </div>
    </div>
    <!--*******************
        Preloader end
    ********************-->

    <!--**********************************
        Main wrapper start
    ***********************************-->
    <div id="main-wrapper" <?php if ($page_title == "Pedidos | DropExpress") { echo 'class="show menu-toggle"';} ?>>

        <!--**********************************
            Nav header start
        ***********************************-->
        <div class="nav-header">
            <a href="<?php echo SERVER_URI; ?>" class="brand-logo">
                <img class="logo-abbr" src="<?php echo SERVER_URI; ?>/images/logo-compact.png" alt="DropExpress">
                <img class="logo-compact" src="<?php echo SERVER_URI; ?>/images/logo-full.png" alt="DropExpress">
                <img class="brand-title" src="<?php echo SERVER_URI; ?>/images/logo-full.png" alt="DropExpress">	
            </a>
            <div class="nav-control">
                <div class="hamburger <?php if ($page_title == "Pedidos | DropExpress") { echo ' is-active';} ?>">
                    <span class="line"></span><span class="line"></span><span class="line"></span>
                </div>
            </div>
        </div>
        <!--**********************************
            Nav header end
        ***********************************-->

        <!--**********************************
            Header start
        ***********************************-->
        
		<?php require_once (dirname(__FILE__) . '/../../elements/header.php'); ?>
		
        <!--**********************************
            Header end ti-comment-alt
        ***********************************-->

        <!--**********************************
            Sidebar start
        ***********************************-->
        <?php 
        if($_SESSION['UserPlan'] == 6) {
            require_once (dirname(__FILE__) . '/../../elements/sidebar-operator.php'); 
        } else {
            require_once (dirname(__FILE__) . '/../../elements/sidebar.php'); 
        }
        ?>
        <!--**********************************
            Sidebar end
        ***********************************-->

        <!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body"> 
            <!-- row -->