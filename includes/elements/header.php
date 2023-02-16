<!--**********************************
  Header start
***********************************-->
<?php

    function abbreviateNumber($num) {
        if ($num >= 0 && $num < 1000) {
        $format = floor($num);
        $suffix = '';
        } 
        else if ($num >= 1000 && $num < 1000000) {
        $format = floor($num / 1000);
        $suffix = 'K';
        } 
        else if ($num >= 1000000 && $num < 1000000000) {
        $format = floor($num / 1000000);
        $suffix = 'M';
        } 
        else if ($num >= 1000000000 && $num < 1000000000000) {
        $format = floor($num / 1000000000);
        $suffix = 'B';
        } 
        else if ($num >= 1000000000000) {
        $format = floor($num / 1000000000000);
        $suffix = 'T';
        }
        
        return !empty($format . $suffix) ? $format . $suffix : 0;
    }

    $raised = raised($_SESSION['UserID']);     
    if ($raised >= 0 && $raised <= 10000) {
        // echo '10k';
        $nivel_atual = '';  
        $color_atual = 'display: none';      
        $nivel_value = 10000;
        $nivel_proxi = 'Bronze';
        $color_proxi = 'color: #cd7f32';
        $porcent = round(($raised /  10000) * 100, 0);
    }
    if ($raised > 10000 && $raised <= 50000) {
        // echo '50k';
        $nivel_atual = 'Bronze';
        $color_atual = 'color: #cd7f32';
        $nivel_value = 50000;
        $nivel_proxi = 'Silver';
        $color_proxi = 'color: #c0c0c0';
        $porcent = round(($raised /  50000) * 100, 0);
    }
    if ($raised > 50000 && $raised <= 100000) {
        // echo '100k';
        $nivel_atual = 'Silver';
        $color_atual = 'color: #c0c0c0';
        $nivel_value = 100000;
        $nivel_proxi = 'Gold';
        $color_proxi = 'color: #FFD700';
        $porcent = round(($raised /  100000) * 100, 0);
    }
    if ($raised > 100000 && $raised <= 500000) {
        // echo '500k';
        $nivel_atual = 'Gold';
        $color_atual = 'color: #FFD700';
        $nivel_value = 500000;
        $nivel_proxi = 'Diamond';
        $color_proxi = 'color: #96d5ff';
        $porcent = round(($raised /  500000) * 100, 0);
    }
    if ($raised > 500000 && $raised <= 1000000) {
        // echo '1m';
        $nivel_atual = 'Diamond';
        $color_atual = 'color: #96d5ff';
        $nivel_value = 1000000;
        $nivel_proxi = 'Black';
        $color_proxi = 'color: #3e3e3e';
        $porcent = round(($raised /  1000000) * 100, 0);
    }
    if ($raised > 1000000 && $raised <= 5000000) {
        // echo '5m';
        $nivel_atual = 'Black';
        $nivel_value = 5000000;
        $color_atual = 'color: #3e3e3e';
        $nivel_proxi = 'Hero';
        $color_proxi = 'color: #ff8300';
        $porcent = round(($raised /  5000000) * 100, 0);
    }
    if ($raised > 5000000) { 
        // echo '10m';  
        $nivel_atual = 'Hero';
        $color_atual = 'color: #ff8300';   
        $nivel_value = 10000000; 
        $nivel_proxi = 'Legend';
        $color_proxi = 'color: #2fde91';  
        $porcent = round(($raised /  10000000) * 100, 0);
    }  
?> 
<div class="header">
    <div class="header-content">
        <nav class="navbar navbar-expand">
            <div class="collapse navbar-collapse justify-content-between">
                <div class="header-left">
                    <div class="dashboard_bar">
                        <?php echo $page_title; ?>
                    </div> 
                </div>
                <ul class="navbar-nav header-right">
                    <li class="nav-item dropdown notification_dropdown">
                        <a class="nav-link  ai-icon" href="javascript:void(0)" role="button" data-toggle="dropdown" 
                        style="background: linear-gradient(90deg, rgba(235,255,246,1) 0%, rgba(235,255,246,1) <?= $porcent ?>%, rgba(241,241,241,1) <?= $porcent ?>%, rgba(241,241,241,1) 100%);">
                            <i class="fa fa-medal fa-2x"></i>   
                        </a>
                        <div class="dropdown-menu rounded dropdown-menu-right">
                            <div class="goals-user p-4">                                    
                               <div class="row align-items-center">  
                                   <div class="col-12 text-right">  
                                       <h6 class="d-flex justify-content-between"> 
                                           <span>
                                                <i class="fa fa-medal" style="<?= $color_atual ?>"></i> 
                                                <?= 'R$ '. abbreviateNumber($raised) ?>
                                            </span>  
                                           <span>  
                                                <?= 'R$ '. abbreviateNumber($nivel_value) ?>  
                                                <i class="fa fa-medal" style="<?= $color_proxi ?>"></i>
                                            </span>
                                       </h6>     
                                   </div>  
                                   <!-- <div class="col-1">
                                       <i class="fa fa-gem" style="color: <?= $color ?>"></i> 
                                   </div>      -->
                                   <div class="col">
                                       <div class="progress progress-bg">   
                                           <div class="progress-bar progress-bar-striped bg-primary progress-animated" role="progressbar" style="width: <?= $porcent ?>%" aria-valuenow="<?= $porcent ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                       </div>
                                   </div> 
                                    <div class="col-md-12 d-flex justify-content-between">
                                        <small><?= $nivel_atual ?></small>
                                        <small><?= $nivel_proxi ?></small>
                                    </div>
                               </div>
                           </div> 
                        </div>
                    </li>
                    <li class="nav-item d-none" style="width: 300px;">   
                        
                    </li>
                    <li class="nav-item notification_dropdown">    
                        <a class="nav-link ai-icon" href="https://t.me/+RHe2juwuosE2M2Ix" alt="Logzz - Avisos" target="_blank"> 
                            <i class="fab fa-telegram-plane text-primary" style="font-size: 28px"></i>   
                        </a>
                    </li>   
                    <li class="nav-item dropdown notification_dropdown">
                        <a class="nav-link  ai-icon" href="javascript:void(0)" role="button" data-toggle="dropdown">
                            <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12.8333 5.91732V3.49998C12.8333 2.85598 13.356 2.33331 14 2.33331C14.6428 2.33331 15.1667 2.85598 15.1667 3.49998V5.91732C16.9003 6.16698 18.5208 6.97198 19.7738 8.22498C21.3057 9.75681 22.1667 11.8346 22.1667 14V18.3913L23.1105 20.279C23.562 21.1831 23.5142 22.2565 22.9822 23.1163C22.4513 23.9761 21.5122 24.5 20.5018 24.5H15.1667C15.1667 25.144 14.6428 25.6666 14 25.6666C13.356 25.6666 12.8333 25.144 12.8333 24.5H7.49817C6.48667 24.5 5.54752 23.9761 5.01669 23.1163C4.48469 22.2565 4.43684 21.1831 4.88951 20.279L5.83333 18.3913V14C5.83333 11.8346 6.69319 9.75681 8.22502 8.22498C9.47919 6.97198 11.0985 6.16698 12.8333 5.91732ZM14 8.16664C12.4518 8.16664 10.969 8.78148 9.87469 9.87581C8.78035 10.969 8.16666 12.453 8.16666 14V18.6666C8.16666 18.8475 8.12351 19.026 8.04301 19.1881C8.04301 19.1881 7.52384 20.2265 6.9755 21.322C6.88567 21.5028 6.89501 21.7186 7.00117 21.8901C7.10734 22.0616 7.29517 22.1666 7.49817 22.1666H20.5018C20.7037 22.1666 20.8915 22.0616 20.9977 21.8901C21.1038 21.7186 21.1132 21.5028 21.0234 21.322C20.475 20.2265 19.9558 19.1881 19.9558 19.1881C19.8753 19.026 19.8333 18.8475 19.8333 18.6666V14C19.8333 12.453 19.2185 10.969 18.1242 9.87581C17.0298 8.78148 15.547 8.16664 14 8.16664Z" fill="#2fde91" />
                            </svg>
                        </a>
                        <div class="dropdown-menu rounded dropdown-menu-right">
                            <a class="all-notification">Nenhuma notificação por aqui.</i></a>
                        </div>
                    </li>
                    <li class="nav-item dropdown header-profile">
                        <a class="nav-link" href="javascript:void(0)" role="button" data-toggle="dropdown">
                            <img src="<?php echo SERVER_URI; ?>/uploads/imagens/usuarios/profile.jpg" width="20" alt="" />
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item ai-icon" href="<?php echo SERVER_URI; ?>/perfil/informacoes/"><i class="far fa-user-circle"></i>&nbsp;&nbsp; Minhas Informações</a>
                            <a class="dropdown-item ai-icon" href="<?php echo SERVER_URI; ?>/perfil/planos/"><i class="fas fa-align-justify"></i>&nbsp;&nbsp; Planos</a>
                            <?php if ($_SESSION['UserPlan'] == 6) : ?>
                                <a class="dropdown-item ai-icon" href="<?php echo SERVER_URI; ?>/perfil/financeiro-operador/"><i class="fas fa-wallet"></i>&nbsp;&nbsp; Financeiro</a>
                            <?php else : ?>
                                <a class="dropdown-item ai-icon" href="<?php echo SERVER_URI; ?>/perfil/financeiro/"><i class="fas fa-wallet"></i>&nbsp;&nbsp; Financeiro</a>
                            <?php endif; ?>
                            <a class="dropdown-item ai-icon" href="<?php echo SERVER_URI; ?>/perfil/contas-bancarias/"><i class="fas fa-university"></i>&nbsp;&nbsp; Contas Bancárias</a>
                            <hr class="dropdown-divider">
                            <a class="dropdown-item ai-icon" href="<?php echo SERVER_URI; ?>/sair" class="dropdown-item ai-icon">
                                <svg id="icon-logout" xmlns="http://www.w3.org/2000/svg" class="text-danger" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                    <polyline points="16 17 21 12 16 7"></polyline>
                                    <line x1="21" y1="12" x2="9" y2="12"></line>
                                </svg>
                                <span class="ml-2">Sair </span>
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</div>
<!--**********************************
  Header end ti-comment-alt
***********************************-->