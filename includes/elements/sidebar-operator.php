<!--**********************************
  Sidebar start
***********************************-->
<div class="deznav">
    <div class="deznav-scroll">
        <ul class="metismenu" id="menu">
            <li>
                <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <i class="flaticon-381-user-9"></i>
                    <span class="nav-text">Perfil</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="<?php echo SERVER_URI; ?>/perfil/informacoes/">Minhas Informações</a></li>
                    <li><a href="<?php echo SERVER_URI; ?>/perfil/financeiro-operador/">Financeiro</a></li>
                    <li><a href="<?php echo SERVER_URI; ?>/perfil/contas-bancarias/">Contas Bancárias</a></li>                     
                    <?php if (isset($_SESSION['UserSuperAdmin']) && $_SESSION['UserSuperAdmin'] == TRUE) { ?>    
                        <li><a id="modify_user">Trocar de Usuário</a></li>  
                    <?php } ?> 
                </ul>
            </li>

            <li><a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <i class="flaticon-381-networking"></i>
                    <span class="nav-text">Relatórios</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="<?php echo SERVER_URI; ?>/pedidos/dashboard-operador/">Dashboard</a></li>
                    <li><a href="<?php echo SERVER_URI; ?>/pedidos/lista-operador">Lista</a></li>
                </ul>
            </li>

            <li>
                <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <i class="flaticon-381-price-tag"></i>
                    <span class="nav-text">Produtos</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="<?php echo SERVER_URI; ?>/localidades/estoques-operacao/">Meus Estoques</a></li>
                    <li><a href="<?php echo SERVER_URI; ?>/localidades/minha-operacao/">Minha Operação local</a></li>
                </ul>
            </li>
            <li>
                    <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                        <i class="flaticon-381-map-1"></i>
                        <span class="nav-text text-center">Estoques e Localidades</span>
                    </a> 
                    <ul aria-expanded="false">
                        <li><a href="<?php echo SERVER_URI; ?>/usuarios/estoques/">Solicitações de Estoques</a></li>
                    </ul>
                </li>
        </ul>
    </div>
</div>
<!--**********************************
  Sidebar end
***********************************-->
