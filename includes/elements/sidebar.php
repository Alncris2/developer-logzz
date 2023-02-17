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
                    <?php if ($_SESSION['UserPlan'] == 5) { ?>
                        <li><a href="<?php echo SERVER_URI; ?>/usuarios/">Usuários</a></li>
                        <li><a href="<?php echo SERVER_URI; ?>/conquistas/">Conquistas</a></li>
                        <li><a href="<?php echo SERVER_URI; ?>/operadores/">Gerentes de Armazéns</a></li>
                        <li><a href="<?php echo SERVER_URI; ?>/usuarios/contas-bancarias/">Listar Contas Bancárias</a></li>                        
                    <?php   } else { ?>
                        <li><a href="<?php echo SERVER_URI; ?>/perfil/informacoes/">Minhas Informações</a></li>
                        <li><a href="<?php echo SERVER_URI; ?>/perfil/planos/">Planos</a></li>
                        <li><a href="<?php echo SERVER_URI; ?>/perfil/financeiro/">Financeiro</a></li>
                        <li><a href="<?php echo SERVER_URI; ?>/perfil/contas-bancarias/">Contas Bancárias</a></li>
                    <?php   } ?>
                    <?php if (isset($_SESSION['UserSuperAdmin']) && $_SESSION['UserSuperAdmin'] == TRUE) { ?>    
                        <li><a type="button" id="get_users">Trocar de Usuário</a></li>  
                    <?php } ?>   
                </ul>
            </li> 

            <?php if ($_SESSION['UserPlan'] == 5) : ?>
                <li>
                    <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                        <i class="flaticon-381-repeat-1"></i>
                        <span class="nav-text">Financeiro </span>
                    </a>
                    <ul aria-expanded="false">
                        <li><a href="<?php echo SERVER_URI; ?>/usuarios/saques/">Histórico de Movimentações</a></li>
                        <li><a href="<?php echo SERVER_URI; ?>/operadores/repasses/">Repasses de operadores</a></li>
                        <li><a href="<?php echo SERVER_URI; ?>/assinaturas/dashboard/">Dashboard de Assinaturas</a></li>
                        <li><a href="<?php echo SERVER_URI; ?>/assinaturas/lista/">Lista de Assinaturas</a></li>
                    </ul>
                </li>
            <?php endif; ?>


            <?php if ($_SESSION['UserPlan'] < 5) : ?>
                <li><a class="ai-icon" href="<?php echo SERVER_URI; ?>/indiqueganhe/" aria-expanded="false">
                        <i class="flaticon-381-gift"></i>
                        <span class="nav-text">Indique e Ganhe</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($_SESSION['UserPlan'] != 6) { ?>
                <li <?php if (@$hasnt_submenu_shop == 'active') {
                        echo 'class="mm-active"';
                    } ?>>

                    <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                        <i class="flaticon-381-pad"></i>
                        <span class="nav-text">Loja</span>
                    </a>
                    <ul aria-expanded="false">
                        <li><a target="_blank" href="<?php echo SERVER_URI; ?>/loja/">Vitrine de Afiliação </a></li>
                        <li><a target="_blank" href="https://atacado.logzz.com.br/">Produtos no Atacado</a></li>
                    </ul>
                </li>
            <?php } ?>

            <li><a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <i class="flaticon-381-networking"></i>
                    <span class="nav-text">Relatórios</span>
                </a>
                <ul aria-expanded="false">
                    <?php if ($_SESSION['UserPlan'] == 6) { ?>
                        <li><a href="<?php echo SERVER_URI; ?>/pedidos/dashboard-operador/">Dashboard</a></li>
                    <?php   } else { ?>
                        <li><a href="<?php echo SERVER_URI; ?>/pedidos/dashboard/">Dashboard Operações Locais</a></li>
                        <!-- <li><a href="<?php echo SERVER_URI; ?>/pedidos/dashboard/logistic">Dashboard Logística Tradicional</a></li> -->
                    <?php   } ?>
                    <?php if ($_SESSION['UserPlan'] == 6) { ?>
                        <li><a href="<?php echo SERVER_URI; ?>/pedidos/lista-operador">Lista</a></li>
                    <?php   } else { ?>
                        <li><a href="<?php echo SERVER_URI; ?>/pedidos/">Lista</a></li>
                    <?php   } ?>
                </ul>
            </li>

            <li <?php if (@$has_submenu_product == 'active') {
                    echo 'class="mm-active"';
                } ?>><a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <i class="flaticon-381-price-tag"></i>
                    <span class="nav-text">Produtos</span>
                </a>
                <ul aria-expanded="false">
                    <?php if ($_SESSION['UserPlan'] == 5) : ?>
                        <li><a href="<?php echo SERVER_URI; ?>/produtos/todos/">Todos os Produtos</a></li>
                    <?php elseif ($_SESSION['UserPlan'] == 6) : ?>
                        <li><a href="<?php echo SERVER_URI; ?>">Produtos em minha Operação Local</a></li>
                    <?php else : ?>
                        <li><a href="<?php echo SERVER_URI; ?>/meus-produtos">Meus Produtos</a></li>
                        <li><a href="<?php echo SERVER_URI; ?>/produto/novo/">Cadastrar</a></li>
                        <li><a href="<?php echo SERVER_URI; ?>/produtos/afiliacoes/">Minhas Afiliações</a></li>
                        <li><a href="<?php echo SERVER_URI; ?>/produtos/solicitacoes/">Solicitações de Afiliação</a></li>
                    <?php endif; ?>
                </ul>
            </li>

            <?php if ($_SESSION['UserPlan'] == 6) : ?>
                <li>
                    <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                        <i class="flaticon-381-map-2"></i>
                        <span class="nav-text">Operações Locais</span>
                    </a>
                    <ul aria-expanded="false">
                        <li><a href="<?php echo SERVER_URI; ?>/localidades/meus-estoques/">Meus Estoques</a></li>
                        <li><a href="<?php echo SERVER_URI; ?>">Lista Estoques</a></li>
                        <li><a href="<?php echo SERVER_URI; ?>">Minha Operação Local</a></li>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if ($_SESSION['UserPlan'] == 5) : ?>
                <li>
                    <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                        <i class="flaticon-381-map-1"></i>
                        <span class="nav-text text-center">Operações Locais</span>
                    </a> 
                    <ul aria-expanded="false">
                        <li><a href="<?php echo SERVER_URI; ?>/localidades/operacoes-locais/">Listagem</a></li>
                        <!-- <li><a href="<?php echo SERVER_URI; ?>/localidades/centros-de-distribuicao/">Listagem de Centros de Distribuição</a></li> -->
                        <li><a href="<?php echo SERVER_URI; ?>/usuarios/estoques/">Solicitações de Estoques</a></li>
                        <li><a href="<?php echo SERVER_URI; ?>/localidades/manusear-estoque/">Manusear Estoques</a></li>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if ($_SESSION['UserPlan'] != 5 && $_SESSION['UserPlan'] != 6) : ?>
                <li><a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                        <i class="flaticon-381-map-2"></i>
                        <span class="nav-text">Localidades</span>
                    </a>
                    <ul aria-expanded="false"> 
                        <?php if ($_SESSION['UserPlan'] <= 4) { ?>
                            <li><a href="<?php echo SERVER_URI; ?>/localidades/operacoes-locais/">Listagem de Operações Locais</a></li>
                            <!-- <li><a href="<?php echo SERVER_URI; ?>/localidades/centros-de-distribuicao/">Listagem de Centros de Distribuição</a></li> -->
                            <li><a href="<?php echo SERVER_URI; ?>/localidades/meus-estoques/">Meus Estoques</a></li>
                            <li><a href="<?php echo SERVER_URI; ?>/localidades/informar-envio/">Informar envio de estoque</a></li>
                            <li><a href="<?php echo SERVER_URI; ?>/localidades/envios-realizados/">Envios Realizados</a></li>
                        <?php   } else if ($_SESSION['UserPlan']  > 10) { ?>
                            <li><a href="<?php echo SERVER_URI; ?>/localidades/operacoes-locais/">Ver Disponibilidade</a></li>
                            <li><a href="<?php echo SERVER_URI; ?>/localidades/meus-estoques/">Estoques</a></li>
                        <?php   }  ?>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if ($_SESSION['UserPlan'] != 4 && $_SESSION['UserPlan'] != 6) { ?>
                <li><a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                        <i class="flaticon-381-share"></i>
                        <span class="nav-text">Integrações</span> 
                    </a>
                    <ul aria-expanded="false">
                        <?php if ($_SESSION['UserPlan'] == 5) { ?> 
                            <li><a href="<?php echo SERVER_URI; ?>/integracoes/nota-fiscal/">Nota Fiscal</a></li>
                            <li><a href="<?php echo SERVER_URI; ?>/integracoes/atendezap/">Atende Zap</a></li> 
                        <?php } else { ?>
                            <li><a href="<?php echo SERVER_URI; ?>/integracoes/postback/">Plataformas Externas</a></li>
                        <?php } ?>
                    </ul>
                </li>
            <?php   }  ?>

            <?php if ($_SESSION['UserPlan'] == 5) { ?>
                <li>
                    <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                        <i class="flaticon-381-send-2"></i>
                        <span class="nav-text">Expedição</span>
                    </a>
                    <ul aria-expanded="false">
                        <li><a href="<?php echo SERVER_URI; ?>/integracoes/expedicao/">Integrar Usuários</a></li>
                        <li><a href="<?php echo SERVER_URI; ?>/integracoes/usuarios-pendentes/">Usúarios Pendentes</a></li>
                    </ul>
                </li>
            <?php   }  ?>
            <li>
                <a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                    <i class="flaticon-381-play-button"></i>
                    <span class="nav-text">Plataforma Educacional</span>
                </a>
                <ul aria-expanded="false">
                    <li><a href="https://escola.logzz.com.br/" target="_blank">Área de Membros</a></li>
                </ul>
            </li>
        </ul>
    </div>
</div>
<!--**********************************
  Sidebar end
***********************************-->

