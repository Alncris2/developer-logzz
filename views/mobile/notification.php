<?php

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

$page_title = "Notificação | App Logzz";
$profile_page = true;
$select_datatable_page = true;

$iphone = strpos($_SERVER['HTTP_USER_AGENT'], "iPhone");
$ipad = strpos($_SERVER['HTTP_USER_AGENT'], "iPad");
$android = strpos($_SERVER['HTTP_USER_AGENT'], "Android");
$palmpre = strpos($_SERVER['HTTP_USER_AGENT'], "webOS");
$berry = strpos($_SERVER['HTTP_USER_AGENT'], "BlackBerry");
$ipod = strpos($_SERVER['HTTP_USER_AGENT'], "iPod");
$symbian =  strpos($_SERVER['HTTP_USER_AGENT'], "Symbian");
if ($iphone || $ipad || $android || $palmpre || $ipod || $berry || $symbian == true) {
    $mobile = 1;
    require_once(dirname(__FILE__) . '/../../includes/layout/fullwidth/fullwidth-header.php');
} else {
    $mobile = 0;
    require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');
}

?>

<style>
    .img-fluid {
        max-width: 40%;
        display: block;
        margin-left: auto;
        margin-right: auto;
        margin-bottom: 10px;
    }

    @media only screen and (max-width: 756px) {
        .widget-stat .media>span {
            height: 50px !important;
            width: 50px !important;
            min-width: 50px !important;
        }

        .font-size-value {
            font-size: 25px;
        }
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
        float: right;
    }

    /* Hide default HTML checkbox */
    .switch input {
        display: none;
    }

    /* The slider */
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        -webkit-transition: .4s;
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
    }

    input.primary:checked+.slider {
        background-color: #2BC155;
    }

    input:focus+.slider {
        box-shadow: 0 0 1px #2196F3;
    }

    input:checked+.slider:before {
        -webkit-transform: translateX(26px);
        -ms-transform: translateX(26px);
        transform: translateX(26px);
    }

    /* Rounded sliders */
    .slider.round {
        border-radius: 34px;
    }

    .slider.round:before {
        border-radius: 50%;
    }

    .custom-select {
        border-color: #2BC155;
        color: #2BC155;
    }

    .form-control:hover, .form-control:focus, .form-control.active {
        color: #2BC155; 
    }

</style>

<input type="hidden" id="user-plan" value="<?= $userPlan ?>">

<div class="container-fluid h-100 mt-5">


    <div class="col-12 d-md-none d-block">
        <img class="img-fluid" src="/images/logo-full.png">
    </div>

    <!-- row -->
    <div class="row my-5" style="justify-content: space-between;">

        <div class="col-12">

            <div class="card" style="margin:6px 0">
                <div class="card-header">
                    <h4>Configurações Notificação</h4>
                </div>
                <ul class="list-group list-group-flush">

                    <li class="list-group-item">
                        <div class="input-group"> 
                            <div class="row w-100 ml-0">  
                                <div class="w-50 d-flex align-items-center">  
                                    <label for="inputGroupSelect01">Pedido Agendado</label>
                                </div>
                                <div class="w-50">
                                    <label class="switch "><input type="checkbox" class="primary">
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                            </div>
                        </div>       
                    </li>

                    <li class="list-group-item">
                        <div class="input-group"> 
                            <div class="row w-100 ml-0">  
                                <div class="w-50 d-flex align-items-center">  
                                    <label for="inputGroupSelect01">Pedido Completo</label>
                                </div>
                                <div class="w-50">
                                    <label class="switch "><input type="checkbox" class="primary">
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                            </div>
                        </div>                        
                    </li>

                    <li class="list-group-item"> 
                        <div class="input-group"> 
                            <div class="row w-100 ml-0">  
                                <div class="w-50 d-flex align-items-center">  
                                    <label for="inputGroupSelect01">Som</label>
                                </div>
                                <div class="w-50">    
                                    <select class="form-control custom-select"> 
                                        <option value="1" selected>Ativado</option> 
                                        <option value="2">Desativado</option>
                                    </select>
                                </div> 
                            </div>
                        </div>
                    </li>

                </ul>
            </div>

        </div>

    </div>

    <div class="d-block d-md-none w-100 mb-3">
        <button class="btn btn-light bg-white btn-lg btn-block">
            <a href="../../views/mobile/dashboard.php" style="color:inherit">Voltar</a>
        </button>
    </div>

</div>
<!-- FOOTER -->

<?php
require_once(dirname(__FILE__) . '/footer.php');
?>