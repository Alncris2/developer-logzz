<!--**********************************
  Sidebar start
***********************************-->
<div class="deznav">
    <div class="deznav-scroll">
        <ul class="metismenu" id="menu">

            <?php if ($_SESSION['UserPlan'] == 5) { ?>
                <li>
                    <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                        <i class="flaticon-381-user-9"></i>
                        <span class="nav-text">Assinantes</span>
                    </a>
                    <ul aria-expanded="false">
                        <li><a href="<?php echo SERVER_URI; ?>/assinantes/">Listar Assinantes</a></li>
                        <li><a href="<?php echo SERVER_URI; ?>/assinantes/novo/">Cadastrar Novo</a></li>
                        <li><a href="<?php echo SERVER_URI; ?>/assinantes/estoques/">Listar Estoques</a></li>
                        <li><a href="<?php echo SERVER_URI; ?>/assinantes/saques/">Solicitações de Saque</a></li>
                    </ul>
                </li>
            <?php   }  ?>

            <li <?php if (@$hasnt_submenu_shop == 'active') {
                    echo 'class="mm-active"';
                } ?>><a class="ai-icon" href="<?php echo SERVER_URI; ?>/loja/" aria-expanded="false">
                    <i class="flaticon-381-pad"></i>
                    <span class="nav-text">Loja</span>
                </a>
            </li>

            <li><a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <i class="flaticon-381-networking"></i>
                    <span class="nav-text">Pedidos</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="<?php echo SERVER_URI; ?>/pedidos/dashboard/">Dashboard</a></li>
                    <li><a href="<?php echo SERVER_URI; ?>/pedidos/">Lista</a></li>
                </ul>
            </li>

            <li <?php if (@$has_submenu_product == 'active') {
                    echo 'class="mm-active"';
                } ?>><a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <i class="flaticon-381-price-tag"></i>
                    <span class="nav-text">Produtos</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="<?php echo SERVER_URI; ?>/produto/novo/">Cadastrar</a></li>
                    <li><a href="<?php echo SERVER_URI; ?>/meus-produtos">Meus Produtos</a></li>
                    <?php if ($_SESSION['UserPlan'] == 5) { ?>
                        <li><a href="<?php echo SERVER_URI; ?>/produtos/todos/">Todos os Produtos</a></li>
                    <?php } ?>
                    <li><a href="<?php echo SERVER_URI; ?>/produtos/afiliacoes/">Minhas Afiliações</a></li>
                    <li><a href="<?php echo SERVER_URI; ?>/produtos/solicitacoes/">Solicitações de Afiliação</a></li>
                </ul>
            </li>

            <li><a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <i class="flaticon-381-map-2"></i>
                    <span class="nav-text">Localidades</span>
                </a>
                <ul aria-expanded="false">
                    <?php if ($_SESSION['UserPlan'] != 4) { ?>
                        <li><a href="<?php echo SERVER_URI; ?>/localidades/ver-disponibilidade/">Ver Disponibilidade</a></li>
                        <li><a href="<?php echo SERVER_URI; ?>/localidades/meus-estoques/">Meus Estoques</a></li>
                        <li><a href="<?php echo SERVER_URI; ?>/localidades/informar-envio/">Informar Envio</a></li>
                        <li><a href="<?php echo SERVER_URI; ?>/localidades/envios-realizados/">Envios Realizados</a></li>
                    <?php   } else { ?>
                        <li><a href="<?php echo SERVER_URI; ?>/localidades/ver-disponibilidade/">Ver Disponibilidade</a></li>
                        <li><a href="<?php echo SERVER_URI; ?>/localidades/meus-estoques/">Estoques</a></li>
                    <?php   }  ?>
                </ul>
            </li>

            <?php if ($_SESSION['UserPlan'] != 4) { ?>
                <li><a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                        <i class="flaticon-381-share"></i>
                        <span class="nav-text">Integrações</span>
                    </a>
                    <ul aria-expanded="false">
                        <li><a href="<?php echo SERVER_URI; ?>/integracoes/nota-fiscal/">Nota Fiscal</a></li>
                        <li><a href="<?php echo SERVER_URI; ?>/integracoes/gateway-de-pagamento/">Gateway de Pagamento</a></li>
                        <li><a href="<?php echo SERVER_URI; ?>/integracoes/postback/">Integrações Postback</a></li>
                        <li><a href="<?php echo SERVER_URI; ?>/integracoes/expedicao/">Expedição</a></li>
                    </ul>
                </li>

            <?php   }  ?>

        </ul>
    </div>
</div>
<!--**********************************
  Sidebar end
***********************************-->