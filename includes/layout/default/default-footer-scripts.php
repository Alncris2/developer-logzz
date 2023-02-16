<?php
/*
	##########

	 Scripts comuns a todas as páginas
 
	##########
	*/
?>  
<div class="modal fade" id="ModifyUserModal" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="ModifyUserForm">   
                <input type="hidden" id="ZsBlp" value="<?= isset($_SESSION['SuperAdmin']) && $_SESSION['SuperAdmin'] ? '4eaDZ' : 'wuPGq' ?>">
                <div class="card-body">
                    <div class="text-center"> 
                        <h3 class="mb-5">Trocar Usuário <?= $_SESSION['SuperAdmin'] ?></h3>  
                        <select name="modify_user" id="modify_user" class="default-select d-block" placeholder="Selecione o Usuário" data-live-search="true">
                        </select>
                    </div>
                    <button class="btn btn-success btn-lg btn-block mt-4">
                        <i class="fas fa-sign-in"></i> Trocar
                    </button>
                </div> 
            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.0.1/css/buttons.dataTables.min.css" />
<script src="<?php echo SERVER_URI; ?>/js/jquery-3.6.0.min.js" type="text/javascript"></script>
<script src="<?php echo SERVER_URI; ?>/js/daterangepicker.js" type="text/javascript"></script>
<script src="<?php echo SERVER_URI; ?>/js/highlight.pack.min.js" type="text/javascript"></script>
<script src="<?php echo SERVER_URI; ?>/js/bootstrap-clockpicker.min.js" type="text/javascript"></script>
<script src="<?php echo SERVER_URI; ?>/js/bootstrap-material-datetimepicker.js?v=1" type="text/javascript"></script>
<script src="<?php echo SERVER_URI; ?>/js/picker.js" type="text/javascript"></script>
<script src="<?php echo SERVER_URI; ?>/js/picker.time.js" type="text/javascript"></script> 
<script src="<?php echo SERVER_URI; ?>/js/picker.date.min.js?v=1" type="text/javascript"></script>
<script src="<?php echo SERVER_URI; ?>/js/material-date-picker-init.js" type="text/javascript"></script>
<script src="<?php echo SERVER_URI; ?>/js/pickadate-init.js?v=13" type="text/javascript"></script> 
<script src="<?php echo SERVER_URI; ?>/js/global.min.js" type="text/javascript"></script>
<script src="<?php echo SERVER_URI; ?>/js/bootstrap-select.min.js" type="text/javascript"></script> 
<script src="<?php echo SERVER_URI; ?>/js/jquery.mask.js" type="text/javascript"></script>  
<script src="<?php echo SERVER_URI; ?>/js/main.js?v=91" type="text/javascript"></script>                        
<script src="<?php echo SERVER_URI; ?>/js/toastr.min.js" type="text/javascript"></script>   
<script src="<?php echo SERVER_URI; ?>/js/deznav-init.js" type="text/javascript"></script>
<script src="<?php echo SERVER_URI; ?>/js/jquery.smartWizard.js" type="text/javascript"></script>
<script src="<?php echo SERVER_URI; ?>/js/jquery.steps.min.js" type="text/javascript"></script>
<script src="<?php echo SERVER_URI; ?>/js/sweetalert2.min.js" type="text/javascript"></script> 
        
<?php      
/* 
	##########

	 Scripts de páginas específicas.

	##########
	*/
# Plugin DataTable
if (isset($select_datatable_page)) {
    echo '	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
			<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.5.1/chart.min.js" type="text/javascript"></script>
			<script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-datalabels/2.0.0/chartjs-plugin-datalabels.min.js" integrity="sha512-R/QOHLpV1Ggq22vfDAWYOaMd5RopHrJNMxi8/lJu8Oihwi4Ho4BRFeiMiCefn9rasajKjnx9/fTQ/xkWnkDACg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js" type="text/javascript"></script>
			<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js" type="text/javascript"></script>
			<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js" type="text/javascript"></script>
			<script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js" type="text/javascript"></script>
			<script src="https://cdn.datatables.net/buttons/2.0.1/js/dataTables.buttons.min.js" type="text/javascript"></script>
			<script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.html5.min.js" type="text/javascript"></script>
		';
}

# Scripts específicos das páginas de produtos.
if (isset($product_page)) {
    echo '	
	<script src="' . SERVER_URI . '/js/product.js" type="text/javascript"></script>';
}

# Scripts específicos das páginas de operações locais.
if (isset($operator_page)) {
    echo '	
	<script src="' . SERVER_URI . '/js/operation.js" type="text/javascript"></script>';
}

# Scripts específicos das páginas de expedição.
if (isset($dispatch_page)) {
    echo '	
	<script src="' . SERVER_URI . '/js/dispatch.js" type="text/javascript"></script>';
}

# Scripts específicos das páginas de postback.
if (isset($postback_page)) {
    echo '	
	<script src="' . SERVER_URI . '/js/postback2.js" type="text/javascript"></script>';
}

# Scripts específicos das páginas de pedidos.
if (isset($orders_page)) {  
    echo '	   
	<script src="' . SERVER_URI . '/js/orders.js?v=2" type="text/javascript"></script>';  
}
   
# Scripts específicos das páginas de pedidos.  
if (isset($orders_operator_page)) { 
    echo '<script src="' . SERVER_URI . '/js/orders.js?v=2" type="text/javascript"></script>';
    echo '<script src="' . SERVER_URI . '/js/orders_operator.js" type="text/javascript"></script>';
}

# Scripts específicos das páginas de loja. 
if (isset($shop_page)) { 
    echo '	
	<script src="' . SERVER_URI . '/js/shop.js" type="text/javascript"></script>';
    //'<script src="' . SERVER_URI . '/js/summernote-pt-BR.min.js" type="text/javascript"></script>';
}

# Scripts específicos das páginas de perfil.
if (isset($profile_page)) {
    echo '	
		<script src="' . SERVER_URI . '/js/profile.js?v=1" type="text/javascript"></script>';  
}

# Scripts específicos das páginas de histórico de movimentações.
if (isset($billing_history)) {
    echo '	
		<script src="' . SERVER_URI . '/js/billing-history.js" type="text/javascript"></script>';
}

# Scripts específicos das páginas de ofertas.
if (isset($sale_page)) {
    echo '	
        <script src="' . SERVER_URI . '/js/sale.js" type="text/javascript"></script>';
}

# Scripts específicos das páginas de Localidade.
if (isset($locale_page)) {
    // echo '<script src="' . SERVER_URI . '/js/locale.js" type="text/javascript"></script>';
    echo '<script src="' . SERVER_URI . '/js/_locale.js" type="text/javascript"></script>';
}

# Script necessário para que o plugin select2.js possa carregar as ofertas do
# banco de dados para um select múltiplos. Arquivos views/products/products.php e sales.php
if (isset($multselect_preload)) {
    echo '
    <script type="text/javascript">$(document).ready(function () {var preload_sales = $(".multselect_preload").select2();preload_sales.val([' . $multselect_preload . ']).trigger("change"); var multipleValues = $(".multselect_preload").val();$("#ofertas-vinculadas-mult-select-text").val(multipleValues);});</script>';
}

# Script genérico para selects Select2.
if (isset($generic_multselect_preload)) {
    echo '
    <script type="text/javascript">$(document).ready(function () {var preload_select = $(".multselect_preload").select2();preload_select.val([' . $generic_multselect_preload . ']).trigger("change"); var multipleValues = $(".multselect_preload").val();$(".multselect_preload_text").val(multipleValues);});</script>';
}

# Scripts específicos das páginas de loja.
if (isset($subscriber_page)) {
    echo '	
	<script src="' . SERVER_URI . '/js/subscriber.js" type="text/javascript"></script>';
}

# Scripts específicos da página de integrar usuario com a tiny.
if (isset($users_integrations)) {
    echo '
		<script src="' . SERVER_URI . '/js/integration_users.js" type="text/javascript"></script>';
}

# Scripts específicos das páginas de Indique e ganhe.
if (isset($indicate_page)) {
    echo '
		<script src="' . SERVER_URI . '/js/profile.js" type="text/javascript"></script>;
		<script src="' . SERVER_URI . '/js/indicate.js" type="text/javascript"></script>';
}

# Scripts específicos das páginas de Manusear estoques.
if (isset($inventories_page)) {  
    echo '	
        <script src="' . SERVER_URI . '/js/inventorie.js" type="text/javascript"></script>';
}

# Script necessário para que os dados do usuários sejam selecionados corretamente dentro
# do Select2.js na página Editar Assinante. O array $subscriber_plan_details é criado
# no arquivo views/subscribers/subscriber.php
if (isset($subscriber_plan_details)) {
    if (!($subscriber_plan_details['gateway'] > 0)) {
        $subscriber_plan_details['gateway'] = 0;
    }
    if (!($subscriber_plan_details['plano'] > 0)) {
        $subscriber_plan_details['plano'] = 0;
    }
    if (!($subscriber_plan_details['taxa'] > 0)) {
        $subscriber_plan_details['taxa'] = 0;
    }
    if (!($subscriber_plan_details['prazo'] > 0)) {
        $subscriber_plan_details['prazo'] = 0;
    }
    echo '  
	<script type="text/javascript">

		$(document).ready(function () {

            var select_taxa_gateway_assinante = $("#select-taxa-gateway-assinante").select();
            select_taxa_gateway_assinante.val(' . $subscriber_plan_details['gateway'] . ').trigger("change"); 
            var select_plano_assinante = $("#select-plano-assinante").select();
            select_plano_assinante.val(' . $subscriber_plan_details['plano'] . ').trigger("change");
            var value = $("#select-plano-assinante").val();$("#text-plano-assinante").val(value);
            var select_taxa_assinante = $("#select-taxa-assinante").select();
            select_taxa_assinante.val(' . $subscriber_plan_details['taxa'] . ').trigger("change");
            var value = $("#select-taxa-assinante").val();
            $("#text-taxa-assinante").val(value);
            var select_prazo_assinante = $("#select-prazo-assinante").select();
            select_prazo_assinante.val(' . $subscriber_plan_details['prazo'] . ').trigger("change");
            var value = $("#select-prazo-assinante").val();$("#text-prazo-assinante").val(value);
        });
            
            </script>';
}

# Script do gráfico principal na página Dashboard
if (isset($dashboard_charts) && !isset($op_charts)) {
    require(dirname(__FILE__) . '/../../elements/graphs.php');
}

if (isset($dashboard_charts) && isset($op_charts)) {
    require(dirname(__FILE__) . '/../../elements/graphs-op.php');
}

if (isset($subscriptions_dashboard_charts)) {
    require(dirname(__FILE__) . '/../../elements/subscriptions-graph.php');
}

# Script do gráfico de vendas por Status
if (isset($dashboard_charts)) {
?>
    <script>
        const salesStatusChart = document.getElementById("salesStatusChart").getContext('2d');
        new Chart(salesStatusChart, {
            type: 'pie',
            data: {
                datasets: [{
                    data: [
                        <?php echo $dashboard_charts['agendada'];  ?>, 
                        <?php echo $dashboard_charts['reagendada'];  ?>, 
                        <?php echo $dashboard_charts['completa'];  ?>, 
                        <?php echo $dashboard_charts['atrasada'];  ?>, 
                        <?php echo $dashboard_charts['frustrada'];  ?>, 
                        <?php echo $dashboard_charts['cancelada'];  ?>, 
                        <?php echo $dashboard_charts['reembolsada'];  ?>, 
                        <?php echo $dashboard_charts['confirmada'];  ?>, 
                        <?php echo $dashboard_charts['emaberto'];  ?>,
                        <?php echo $dashboard_charts['indisponivel'];  ?>  
                    ],
                    borderWidth: 0, 
                    backgroundColor: ["#2fde91", "#4fdf1f", "#0b352b", "#f7e39c", "#fc8567", "#db323d", "#2421DA", "#ffd200", "#aaaba4", "#ff237b"], 
                    hoverBackgroundColor: ["#2fde91", "#4fdf1f", "#0b352b", "#f7e39c", "#fc8567", "#db323d", "#2421DA", "#ffd200", "#aaaba4", "#ff237b"]   

                }],
                labels: ["Agendada", "Reagendada", "Completa", "Atrasada", "Frustrada", "Cancelada", "Reembolsada", "Confirmado", "Em aberto", 'Indisponível']
            },
            options: {
                responsive: true,
                legend: false,
            }
        });
    </script>
<?php
}
# Script do gráfico de vendas por Forma de Pagamento
if (isset($dashboard_charts)) {
?>
    <script>
        const salesPayMethodChart = document.getElementById("salesPayMethodChart").getContext('2d');
        new Chart(salesPayMethodChart, {
            type: 'pie',
            data: {
                datasets: [{
                    data: [<?php echo $dashboard_charts['money-payments']; ?>, <?php echo $dashboard_charts['credit-payments']; ?>, <?php echo $dashboard_charts['debit-payments']; ?>, <?php echo $dashboard_charts['pix-payments']; ?>],
                    borderWidth: 0,
                    backgroundColor: ["#2fde91", "#4fdf1f", "#0b352b", "#f7e39c"],
                    hoverBackgroundColor: ["#2fde91", "#4fdf1f", "#0b352b", "#f7e39c"]

                }],
                labels: [
                    "Dinheiro", "Crédito", "Débito", "PIX",
                ]
            },
            options: {
                responsive: true,
                legend: false,
            }
        });
    </script>
<?php
}
?>

<script>
    $(document).ready(function() {

        $('#get_users').click(function() {
            $.ajax({
                url: u + '/ajax/get-all-users.php',
                dataType: 'json',
                processData: false,
                contentType: false,
                beforeSend: function(){
                    display_loader();
                } ,
                complete: function() {
                    display_loader(false);
                },
                success: async function(feedback) {
                    if(feedback.status == 1){
                        let options = feedback.data;
                        $("#modify_user").selectpicker('destroy');
                        $("#modify_user option").remove();
                        if (options.length != 0) {
                            $.each(options, function(index, value) {
                                var newOption = new Option(value.name, value.id, false, false);
                                $('#modify_user').append(newOption).trigger('change');
                            });
                        }
                        $("#modify_user").selectpicker(); 
                        $('#ModifyUserModal').modal('toggle');
                    } else {
                        Swal.fire({
                            title: "Erro #PAS001",
                            text: feedback.msg,
                            icon: 'warning',
                        });
                    }
                }
            }).fail(function(data) {
                Swal.fire({
                    title: "Erro #PAS001",
                    text: "Não foi possível carregar os usuários..",
                    icon: 'warning',
                });
            });
        });
        
        $('#ModifyUserForm').submit(function(e) { 
            e.preventDefault(); 
            
            let ZsBlp = $('#ZsBlp').val(); 
            let user = $('#modify_user').val();

            console.log('ZsBlp::: ', ZsBlp);
            console.log('user::: ', user);

            $('#ModifyUserModal').modal('toggle');

            if (ZsBlp == '4eaDZ') { 
                Swal.fire({
                    title: "Sem Permissão",
                    text: "Você não tem permissão para trocar de usuário!",
                    icon: 'danger',
                });
                return;
            }

            if (!user) {
                Swal.fire({
                    title: "Erro #PAS001",
                    text: "Selecione um usuário para realizar a troca!",
                    icon: 'warning',
                });
                return;
            }

            window.location.href = `${u}/modify_user.php?id=${user}`;
        });
    });

</script>