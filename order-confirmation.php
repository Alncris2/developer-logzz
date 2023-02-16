<?php
// error_reporting(-1);              
// ini_set('display_errors', 1);      
require_once('includes/config.php');
session_name(SESSION_NAME);
session_start();
 
if (isset($_GET['order'])) {
    $order = addslashes($_GET['order']);

    $order_data = $conn->prepare('SELECT * FROM orders INNER JOIN sales ON orders.sale_id = sales.sale_id WHERE order_number = :order');
    $order_data->execute(array('order' => $order));

    # Manda pra página de detalhes do pedido com upsel, caso haja
    if ($order_data->rowCount() > 1) {
        header("Location: " . SERVER_URI . "/meus-pedidos/" . $order); 
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

        if ($order_status  > 1) {     
            header("Location: " . SERVER_URI . "/meu-pedido/" . $order);   
            exit;     
        }    

        # String de Forma de Pagamento
        switch ($row['order_payment_method']) {
            case 'money':
                $order_payment_method = 'Dinheiro';
                break;

            case 'credit':
                $order_payment_method = 'Cartão de Crédito';
                break;

            case 'debit':
                $order_payment_method = 'Cartão de Débito';
                break;

            case 'pix':
                $order_payment_method = 'PIX';
                break;

            default:
                $order_payment_method = "-";
                break;
        }

        if (preg_match("/AFI/", $order)) {
            $order_number = explode("AFI", $order);
            $order_number = $order_number[1];
        } else {
            $order_number = $order;
        }

        # Busca o nome do Produtor com base no HOTCODE da Afiliação
        # Se não houver uma meta_value com o HOTCODE da afiliação, signfica que não houve um afiliado vinculado ao pedido.
        $get_users_id = $conn->prepare('SELECT meta_value FROM orders_meta WHERE meta_key = "membership_hotcode" AND order_number = :order_number');
        $get_users_id->execute(array('order_number' => $order_number));
        if ($get_users_id->rowCount() == 1) {
            $has_member = true;
            $get_users_id = $get_users_id->fetch();
            $hotcode = $get_users_id['meta_value'];

            $get_member_id = $conn->prepare('SELECT membership_affiliate_id FROM memberships WHERE memberships_hotcode = :memberships_hotcode');
            $get_member_id->execute(array('memberships_hotcode' => $hotcode));
            $member_id = $get_member_id->fetch();
            $member_id = $member_id[0];

            $get_member_name = $conn->prepare('SELECT full_name, email, user_phone FROM users WHERE user__id = :user__id');
            $get_member_name->execute(array('user__id' => $member_id));
            $member_details = $get_member_name->fetch();
            $member_name = $member_details['full_name'];
            $member_email = $member_details['email'];
            $member_phone = $member_details['user_phone'];
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

        # Cálculo dos valores as comissões
        $get_ship_tax = $conn->prepare('SELECT meta_value FROM orders_meta WHERE meta_key = "ship_tax" AND order_number = :order_number');
        $get_ship_tax->execute(array('order_number' => $order_number));
        $get_ship_tax = $get_ship_tax->fetch();
        $main_ship_tax = $get_ship_tax['meta_value'];

        $get_member_commission = $conn->prepare('SELECT meta_value FROM orders_meta WHERE meta_key = "member_commission" AND order_number = :order_number');
        $get_member_commission->execute(array('order_number' => $order_number));
        $get_member_commission = $get_member_commission->fetch();
        @$member_commission = $get_member_commission['meta_value'];

        $get_producer_commission = $conn->prepare('SELECT meta_value FROM orders_meta WHERE meta_key = "producer_commission" AND order_number = :order_number');
        $get_producer_commission->execute(array('order_number' => $order_number));
        $get_producer_commission = $get_producer_commission->fetch();
        $producer_commission = $get_producer_commission['meta_value'];

        $get_member_tax = $conn->prepare('SELECT meta_value FROM orders_meta WHERE meta_key = "member_tax" AND order_number = :order_number');
        $get_member_tax->execute(array('order_number' => $order_number));
        $get_member_tax = $get_member_tax->fetch();
        @$member_tax = $get_member_tax[0];

        $get_producer_tax = $conn->prepare('SELECT meta_value FROM orders_meta WHERE meta_key = "producer_tax" AND order_number = :order_number');
        $get_producer_tax->execute(array('order_number' => $order_number));
        $get_producer_tax = $get_producer_tax->fetch();
        $producer_tax = $get_producer_tax['meta_value'];

        $get_member_commission_base = $conn->prepare('SELECT meta_value FROM orders_meta WHERE meta_key = "member_commission_base" AND order_number = :order_number');
        $get_member_commission_base->execute(array('order_number' => $order_number));
        $get_member_commission_base = $get_member_commission_base->fetch();
        @$member_commission_base = $get_member_commission_base['meta_value'];

        $get_member_tax_base = $conn->prepare('SELECT meta_value FROM orders_meta WHERE meta_key = "member_tax_base" AND order_number = :order_number');
        $get_member_tax_base->execute(array('order_number' => $order_number));
        $get_member_tax_base = $get_member_tax_base->fetch();
        @$member_tax_base = $get_member_tax_base['meta_value'];

        $get_producer_tax_base = $conn->prepare('SELECT meta_value FROM orders_meta WHERE meta_key = "producer_tax_base" AND order_number = :order_number');
        $get_producer_tax_base->execute(array('order_number' => $order_number));
        $get_producer_tax_base = $get_producer_tax_base->fetch();
        $producer_tax_base = $get_producer_tax_base['meta_value'];

        if (!(preg_match("/AFI/", $order))) {
            # Entrega
            $ship_tax = $main_ship_tax;

            # Taxa
            $sale_tax = $producer_tax;
        } else {
            # Entrega
            $ship_tax = 0;

            # Taxa
            $sale_tax = $member_tax;
        }

        $system_commission = $main_ship_tax + $producer_tax + $member_tax;


        # Textos Detalhes das Comissões
        $syst_comm_details = "R$ " . number_format($main_ship_tax, 2, ',', '.') . " da Entrega + " . "R$ " . number_format(($producer_tax + $member_tax), 2, ',', '.') . " das taxas";
        $memb_comm_details = "R$ " . number_format(($member_commission + $member_tax), 2, ',', '.') . " (" . $member_commission_base . "% de " . number_format($fb_purchase_value, 2, ',', '.') . ") - R$ " . number_format(($member_tax), 2, ',', '.') . " (" . ($member_tax_base * 100) . "% de taxa).";
        if (isset($has_member)) {
            $prod_comm_details = "R$ " . number_format(($fb_purchase_value - $sale_freight), 2, ',', '.') . " + R$ " . number_format(($sale_freight), 2, ',', '.') . " (do Frete)" .  " - R$ " . number_format(($member_commission + $member_tax), 2, ',', '.') . " (Comissão do Afiliado) - R$ " . number_format($main_ship_tax, 2, ',', '.')  . " (da Entrega) - R$ " . number_format(($producer_tax), 2, ',', '.') . " (" . ($producer_tax_base * 100) . "% de taxa).";
        } else {
            $prod_comm_details = "R$ " . number_format(($fb_purchase_value - $sale_freight), 2, ',', '.') . " + R$ " . number_format(($sale_freight), 2, ',', '.') . " (do Frete)" . " - R$ " . number_format($main_ship_tax, 2, ',', '.') . " (da Entrega) - R$ " . number_format(($producer_tax), 2, ',', '.') . " (" . ($producer_tax_base * 100) . "% de taxa)";
        }



        // Função Desabiliatada
        # String de Hoário de Entrega
        // if ($row['delivery_period'] == "manha") {
        //     $period = "Manhã";
        // } else {
        //     $period = "Tarde";
        // }

        $get_warranty_time = $conn->prepare('SELECT product_warranty_time FROM products WHERE product_id = :product_id');
        $get_warranty_time->execute(array('product_id' => $product_id));

        $product_warranty_time = $get_warranty_time->fetch();
        $product_warranty_time = $product_warranty_time[0];

        if ($product_warranty_time == null || empty($product_warranty_time) || !($product_warranty_time > 0)) {
            $product_warranty_time = "<small>Período de garantia não informado.</small>";
        } else {
            # Calcula a data em que vence a garantia
            $warranty_time_timestamp = "+" . $product_warranty_time . "days";
            $expire_date = date('Y-m-d', strtotime($order_date . $warranty_time_timestamp));

            if (strtotime($expire_date) > strtotime(date('Y-m-d'))) {

                $today = date_create_from_format('Y-m-d', date('Y-m-d'));
                $expire_date = date_create_from_format('Y-m-d', $expire_date);
                $diff = date_diff($today, $expire_date);

                $product_warranty_time = $diff->days;

                $product_warranty_time = $product_warranty_time . " dias restantes";
            } else {

                $product_warranty_time = "Expirou em " . date_format(date_create($expire_date), 'd/m/Y');
            }
        } 
    }   
} 

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

    # verifica se a imagem principal é um video ou imagem
    $image_filetype_array = explode('.', $product_image);
    $filetype = strtolower(end($image_filetype_array));

    $isVideo = in_array($filetype, ['mp4', 'mkv']);

    if($isVideo){ 
        # busca as imagens associadas ao produto
        $product_images_data = $conn->prepare('SELECT * FROM products_images WHERE product_id = :product_id LIMIT 1');
        $product_images_data->execute(array('product_id' =>  $product_id));

        $product_images = [];
        while ($row = $product_images_data->fetch()) {
            $product_image = $row['product_image'];
        }
    }
    
    $delivery_date = date_format(date_create($product_delivery), 'd/m/Y'); # função desabilitada [. " | " . $period;]

    $page_title =  "Pedido #" . $order;
    require_once('includes/layout/fullwidth/fullwidth-header-confirm.php');  

    $Week = ["Domingo", "Segunda-Feira", "Terça-Feira", "Quarta-Feira", "Quinta-Feira", "Sexta-Feira", "Sábado"];        
    $dayName = $Week[date('w', strtotime('2022-01-08'))]; 
?>  
<style>
    @media only screen and (max-width: 767px) {  
        .text-header h3 {
            font-size: 1.1rem;   
        }
    }
</style>
    <div class="container-fluid" style="margin-top: 30px;min-height: 680px;"> 
        <div class="row">
            <div class="col-lg-9 d-block m-auto">
                <div class="card"> 
                    <div class="card-body text-header">  
                        <div class="mb-5 text-center">      
                            <img src="<?= SERVER_URI . '/images/svg/logo-full-fundo.png' ?>" width="200" alt="logo logzz" class="img-fluid mb-4">
                            <h3><?= $client_name ?>, para confirmar seu agendamento e reservar sua entrega para <?= $dayName ?>, escolha uma opção dos botões abaixo.</h3>
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
                        <div class="row">      
                            <div class="col-md-12 d-block mt-3 text-center">                 
                                <button type="button" data-status="10" data-order="<?= $order ?>" class="attstatus btn btn-success btn-block"> Confirmar Pedido</button>    
                                <small>Atenção! <strong>Caso o entregador se desloque e a entrega seja frustada</strong>, será cobrada uma taxa de R$20,00.</small>   
                                <small>Ao clicar no botão de confirmar, você estará atestando que esta ciente e de acordo</small>
                                      
                                <a type="button" class="btn btn-block mx-auto w-75" href="<?= SERVER_URI . '/reagendar-pedido/'. $order ?>" style="background: #c6d248;border-color: #c6d248; color: #fff;">Reagendar Pedido para outra data</a> 
                                <button type="button" onclick="setIdAndStatus(this)" data-toggle="modal" data-target="#cancelOrderModal" data-status="5" data-checkout='1' data-id="<?= $order_id ?>" class="btn btn-block mx-auto w-auto" style="background: #d47676;border-color: #d47676; color: #fff;"> Cancelar Pedido</button>
                            </div>
                        </div> 
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
    // $order

    function setIdAndStatus(element) {
        event.preventDefault();
        var status = element.getAttribute('data-status');
        var id = element.getAttribute('data-id');

        $('#cancel_id').val(id);
        $('#cancel_status').val(status);
    }  

    $(document).ready(function() {
        $('.attstatus').click(function(e) {
            e.preventDefault();

            let order_number = $(this).data('order'); 
            let status = $(this).data('status');

            $.ajax({
                type: "GET",
                url: u + '/update-order-status.php',
                data: { order_number, status },
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
                    }).then(() => { 
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