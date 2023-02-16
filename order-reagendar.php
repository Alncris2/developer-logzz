<?php
require_once('includes/config.php');
session_name(SESSION_NAME);
session_start();
 
if (!isset($_GET['order'])) {  
    header("Location: /pagina-nao-encontrada");
    exit;  
} 

    $order = addslashes($_GET['order']);

    $order_data = $conn->prepare('SELECT * FROM orders INNER JOIN sales ON orders.sale_id = sales.sale_id WHERE order_number = :order');
    $order_data->execute(array('order' => $order));
    

    # Manda pra página de detalhes do pedido com upsel, caso haja
    if ($order_data->rowCount() !== 1) { 
        header("Location: " . SERVER_URI . "/meu-pedido/" . $order); 
        exit; 
    }     
 
    while ($row = $order_data->fetch()) {
        $sale_freight = $row['sale_freight'];
        $sale_name = $row['sale_name'];
        $product_id = $row['product_id'];
        $sale_id = $row['sale_id'];
        $sale_quantity = $row['sale_quantity'];
        $sale_price = $row['sale_price'];
        $user__id = $row['user__id'];
        $document = $row['client_document'];
        $address = $row['client_address'];
        $number = $row['client_number'];
        $email = $row['client_email'];
        $product_delivery = $row['order_deadline'];
        $order_delivery_date = $row['order_delivery_date'];
        $fb_pixel = $row['sale_fb_pixel'];
        $fb_purchase_value = $row['order_final_price'];
        $initiate_checkout = $row['initiate_checkout'];
        $order_id = $row['order_id'];
        $order_status = $row['order_status'];
        $order_status_description = $row['order_status_description'];
        $fail_delivery_attemp = $row['fail_delivery_attemp'];
        $order_date = $row['order_date'];
        $order_liquid_value = $row['order_liquid_value'];
        $order_tracking = $row['order_tracking'];
        $order_shipping = $row['order_shipping'];
        $sale_name = $row['sale_name']; 

        $client_name = explode(" ", $row['client_name']);
        $client_name = $client_name[0];

        $address = $row["client_address"];
        $city_state = explode("<br>", $address)[3];
        $city = explode(", ", $city_state)[0];
        $state = explode(", ", $city_state)[1]; 
 
        $local_operations = $conn->prepare('SELECT lop.operation_delivery_days, lop.operation_id  FROM local_operations lop INNER JOIN operations_locales ol ON lop.operation_id = ol.operation_id WHERE lop.uf = :uf AND ol.city like :city');
        $local_operations->execute(array("uf" => $state, "city" => '%' . $city . '%')); 
        $data = null; 

        if ($delivery_days = $local_operations->fetch()) { 
            $data = $delivery_days['operation_delivery_days'];

            $invetoriesStmt = $conn->prepare('SELECT product_delivery_days FROM inventories WHERE inventory_locale_id = :operation_id AND inventory_product_id = :product_id AND inventory_quantity > 0 AND ship_locale = 0');
            $invetoriesStmt->execute(array( "operation_id" => $delivery_days['operation_id'], 'product_id' => $product_id ));
            $Inventory = $invetoriesStmt->fetch();    

            if ($dadosInventory = $Inventory[0]) {  

                $days_available = explode(',', str_replace(array('[',']'), '', $data));
                $days_products = explode(',', str_replace(array('[',']'), '', $dadosInventory));  
                
                $data_array = array_intersect($days_products, $days_available); 

                $data =  '['. implode(',', $data_array) . ']'; 
            }
        }

        #ID DO PRODUTOR 
        $get_id_productor = $conn->prepare("SELECT user__id FROM products WHERE product_id = :product_id");
        $get_id_productor->execute(['product_id' => $product_id]);
        $id_productor = $get_id_productor->fetch(\PDO::FETCH_ASSOC)['user__id'];

        # Busca o operador responsável pela entrega do pedido
        $get_operator = $conn->prepare('SELECT * FROM local_operations_orders loo INNER JOIN logistic_operator lop ON loo.responsible_id = lop.operator_id WHERE order_id = :order_id');
        $get_operator->execute(array('order_id' => $order_id));

        # Orodutor vai ser idenficado pelo ID da Oferta.
        $get_producer_name = $conn->prepare('SELECT full_name FROM users WHERE user__id = :user__id');
        $get_producer_name->execute(array('user__id' => $id_productor));
        $producer_name = $get_producer_name->fetch();
        $producer_name = $producer_name[0];
        $system_commission = $main_ship_tax + $producer_tax + $member_tax;

    } 

    // if ($order_status != 0) {
    //     header("Location: " . SERVER_URI ."/meu-pedido/". $order);   
    //     exit; 
    // }  
 
$order_product_data = $conn->prepare('SELECT * FROM products WHERE product_id = :product_id AND status = 1');
$order_product_data->execute(array('product_id' => $product_id)); 

while ($sow = $order_product_data->fetch()) {
    $product_image = $sow['product_image'];
    $product_name = $sow['product_name'];
    $product_description = $sow['product_description'];
    $user__id = $sow['user__id'];

    if (strlen($product_description) > 100) {
        $product_description = substr($product_description, 0, 100) . "...";
    }

    $delivery_date = date_format(date_create($product_delivery), 'd/m/Y'); # função desabilitada [. " | " . $period;]


    $page_title =  "Pedido #" . $order;
    require_once('includes/layout/fullwidth/fullwidth-header-confirm.php'); 
?>
<style>
    @media only screen and (max-width: 767px) {  
        .text-header h3 { 
            font-size: 1.1rem;   
        } 
    } 
</style> 
    <div class="container-fluid" style="margin-top: 30px;">
        <div class="row">
            <div class="col-lg-9 d-block m-auto">
                <div class="card"> 
                    <div class="card-body text-header">   
                        <div class="mb-5 text-center">    
                            <img src="<?= SERVER_URI . '/images/svg/logo-full-fundo.png' ?>" width="200" alt="logo logzz" class="img-fluid mb-4">
                            <h3><?= $client_name ?>, para reagendar sua entrega, escolha uma data clicando no campo abaixo.</h3> 
                            <input type="hidden" id="order_number" name="order_number" value="<?= $order ?>">
                        </div> 
                        <div class="table-responsive"> 
                            <table class="table" style="font-size: 0.9em;">
                                <thead>
                                    <tr>
                                        <th class="col-md-3 center">Produto</th>
                                        <th class="col-md-2 center">Valor</th>
 
                                        <th class="col-md-5 text-center">Imagem</th>
                                        <th class="col-md-2 center">Qtd.</th> 
                                    </tr>   
                                </thead>
                                <tbody>
                                    <tr> 
                                        <td class="center"><?php echo $product_name; ?></td> 
                                        <td class="center"><?php echo number_format($fb_purchase_value, 2, ',', '.'); ?></td>
                                        <td class="center text-center"><img class="img-fluid" src="<?php echo SERVER_URI . "/uploads/imagens/produtos/" . $product_image; ?>" style="max-width: 150px"></td>
                                        <td class="center"><?php echo $sale_quantity; ?></td> 
                                    </tr>
                                </tbody>   
                            </table>
                        </div>  
                        <form>
                            <div class="row">
                                <div class="col-md-12 form-group">
                                    <label for="data_reagendada"></label>
                                    <input name="data_reagendada" value="Data" class="datepicker-reagendar form-control picker__input" id="data_reagendada" data-days="<?=  $data  ?>" readonly="" aria-haspopup="true" aria-expanded="false" aria-readonly="false" aria-owns="datepicker_root" placeholder="dia / mês / ano">

                                    <div class="picker" id="datepicker_root" aria-hidden="true">
                                        <div class="picker__holder" tabindex="-1">
                                            <div class="picker__frame">
                                                <div class="picker__wrap">
                                                    <div class="picker__box">
                                                        <div class="picker__header">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div> 
                            </div> 
                            <div class="row">   
                                <div class="col-md-12 mt-3">              
                                    <button data-status="1" data-order="<?= $order; ?>" class="attstatus btn btn-success btn-block mb-2 mb-md-0"> Solicitar Reagendamento</button>
                                <!--  onclick='setIdAndStatus(this)'  data-toggle='modal' data-target='#cancelOrderModal'  data-status='5' data-checkout='1' data-id='" . $row['order_id'] . "'  -->
                                    <a type="button" href="<?= SERVER_URI . '/confirma-pedido/'. $order ?>" class="btn btn-danger btn-block w-75 mx-auto"> Voltar </a>   
                                    <button type="button" onclick="setIdAndStatus(this)" data-toggle="modal" data-target="#cancelOrderModal" data-status="5" data-checkout='1' data-id="<?= $order_id ?>" class="btn btn-block mx-auto w-auto" style="background: #d47676;border-color: #d47676; color: #fff;"> Cancelar Pedido</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
 
    <!-- Modal -->
    <div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="calcelOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form id="cancelationForm" class="modal-content">
                <div class="modal-header text-center">
                    <h5 class="modal-title w-100 text-center" id="calcelOrderModalLabel">Justificativa de Cancelamento</h5>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="cancel_status" name="status">
                    <input type="hidden" id="cancel_id" name="id">

                    <div class="form-check mb-2">
                        <input class="form-check-input mt-2" type="radio" name="status_description" id="statusDescription1" value="Cliente disse que estava sem dinheiro" required>
                        <label class="form-check-label" for="statusDescription1">
                            Estou sem dinheiro
                        </label>
                    </div>

                    <div class="form-check mb-2">
                        <input class="form-check-input mt-2" type="radio" name="status_description" id="statusDescription2" value="Cliente disse que não poderia estar em casa no horário da rota">
                        <label class="form-check-label" for="statusDescription2">
                            Não poderei estar no endereço nessa data
                        </label>
                    </div>

                    <div class="form-check mb-2">
                        <input class="form-check-input mt-2" type="radio" name="status_description" id="statusDescription3" value="Cliente disse que vai remarcar com o vendedor">
                        <label class="form-check-label" for="statusDescription3">
                            Remarcarei diretamente com o vendedor
                        </label>
                    </div>

                    <div class="form-check mb-2">
                        <input class="form-check-input mt-2" type="radio" name="status_description" id="statusDescription4" value="Cliente disse que não queria mais o produto">
                        <label class="form-check-label" for="statusDescription4">
                            Não quero mais o produto
                        </label>
                    </div> 
 
                    <div class="form-check mb-2">
                        <input class="form-check-input mt-2" type="radio" name="status_description" id="statusDescription5" value="Cliente disse que não havia confirmado a entrega">
                        <label class="form-check-label" for="statusDescription5">
                            Não confirmei a entrega, logo, não venham
                        </label>
                    </div>

                    <div class="form-check mb-2">
                        <input class="form-check-input mt-2" type="radio" name="status_description" id="statusDescription6" value="0">
                        <label class="form-check-label" for="statusDescription6">
                            Outros
                        </label>
                    </div>

                    <div class="form-group">
                        <input type="text" id="otherDescription" class="form-control" name="other_description" maxlength="150" style="display: none;">
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-primary">Confirmar Cancelamento</button>
                </div>
            </form>
        </div>
    </div>

 
<?php
} $footer_text = true; 
require_once('includes/layout/fullwidth/fullwidth-footer.php');
?>
<script>

    function setIdAndStatus(element) {
        event.preventDefault();
        var status = element.getAttribute('data-status');
        var id = element.getAttribute('data-id');

        $('#cancel_id').val(id);
        $('#cancel_status').val(status);
    }  

    $(document).ready(function() { 

        let picker = $('.datepicker-reagendar').pickadate("picker");
        picker.set("disable", [1, 2, 3, 4, 5, 6, 7]); 
        picker.set("enable", $('.datepicker-reagendar').data('days'));
        $("#data_reagendada").val("");      
 
        $('.attstatus').click(function(e) {
            e.preventDefault()
            
            if (!$('#data_reagendada').val()) {
                $('#data_reagendada').css('border-color', 'red');
                throw "Deu errado!";
            }
            
            let order_number    = $(this).data('order'); 
            let status          = $(this).data('status');
            let data_pedido     = $('#data_reagendada').val();

            $.ajax({ 
                type: "GET",
                url: u + '/update-order-status.php',
                data: { order_number, status, data_pedido }, 
                dataType: 'json',
                processData: true,
                contentType: false,
                beforeSend: function(){
                    display_loader();
                } ,
                complete: function() {
                    display_loader(false);
                },
                success: function (feedback) {
                    Swal.fire({
                        title: feedback.title,
                        text: feedback.msg,
                        icon: feedback.type,
                    }).then((value) => {
                        if (typeof feedback.url !== 'undefined' ) {                             
                            window.location.replace(feedback.url);
                        } else if ( feedback.type == 'success' ) {                             
                            window.location.reload(); 
                        } 
                    });
                }
            }).fail(function (data) {
                Swal.fire({
                    title: "Erro de Conexão",
                    text: "Quando a página atulizar, tente mudar o status do pedido novamente.",
                    icon: 'warning' 
                });
            })
        }); 

        $('#cancelationForm').click(function() {
            if ($('#statusDescription6').prop('checked')) {
                $("#otherDescription").show();
                $("#otherDescription").prop("required", true);
                $("#otherDescription").focus();
            } else {
                $("#otherDescription").hide();
                $("#otherDescription").prop("required", false);
            }
        });

        $("#cancelationForm").submit(function() {
            event.preventDefault(); 

            var formdata = new FormData(this);

            var id = formdata.get('id');
            var status = formdata.get('status');
            var status_description = formdata.get('status_description') == '0' ?
                formdata.get('other_description') :
                formdata.get('status_description')

            // Envia os parâmetro para o PHP via AJAX
            $.ajax({
                url: u + "/update-order-status.php",
                type: "GET",
                data: {
                    status,
                    id,
                    status_description
                },
                dataType: 'json',
                processData: true,
                contentType: false,
                beforeSend: function(){
                    display_loader();
                    $('#cancelOrderModal').modal('hide');  
                    $('.modal-backdrop').remove();
                } ,
                complete: function() {
                    display_loader(false);
                },
                success: function(feedback) {
                    Swal.fire({
                        title: feedback.title,
                        text: feedback.msg,
                        icon: feedback.type,
                    }).then((value) => {
                        if (feedback.type == 'success') {
                            window.location.replace( u + '/meu-pedido/' + $('#order_number').val() ); 
                        } 
                    }); 
                    // $('#cancelOrderModal').modal('hide');
                    $("#otherDescription").val(''); 
                } 
            }).fail(function(data) {
                Swal.fire({
                    title: "Erro de Conexão",
                    text: "Quando a página atulizar, tente mudar o status do pedido novamente.",
                    icon: 'warning',
                }).then((value) => {
                    $('#cancelOrderModal').modal('hide'); 
                });
            });
            return false;
        });
    })
</script>