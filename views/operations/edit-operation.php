<?php


require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID'])) || $_SESSION['UserPlan'] != 5) {
    header('Location: ' . SERVER_URI . '/login');
}

$page_title = "Editar Operação Local | Logzz";
$operator_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

$ufs = file_get_contents('https://servicodados.ibge.gov.br/api/v1/localidades/estados?view=nivelado');
$ufs = json_decode($ufs);

$operation_id = addslashes($_GET['operation']);

$conn->query("UPDATE operations_locales SET city = TRIM(city)");

$get_operation = $conn->prepare("SELECT * FROM local_operations l WHERE operation_id = :operation_id");
$get_operation->execute(array("operation_id" => $operation_id));

$get_operation_locales = $conn->prepare("SELECT city FROM operations_locales WHERE operation_id = :operation_id");
$get_operation_locales->execute(array("operation_id" => $operation_id));

$locales = array();
while ($row2 = $get_operation_locales->fetch()) {
    array_push($locales, $row2["city"]);
}

while ($row = $get_operation->fetch()) {
    $address = $row["storage_address"];
    $address = explode("<br>", $address);

    $address2 = $row["storage_address_2"];
    $address2 = explode("<br>", $address2);

    $street_number = explode(", ", $address[0]);
    $street = $street_number[0];
    $number = explode("nº ", $street_number[1])[1];

    $bairro = $address[1];
    $complement = $address[2];

    $city_state = explode(", ", $address[3]);
    $city = $city_state[0];
    $state = $city_state[1];

    $cep = explode("CEP: ", $address[4])[1];

    // --

    $street_number2 = explode(", ", $address2[0]);
    $street2 = $street_number2[0];
    $number2 = explode("nº ", $street_number2[1])[1];

    $bairro2 = $address2[1];
    $complement2 = $address2[2];

    $city_state2 = explode(", ", $address2[3]);
    $city2 = $city_state2[0];
    $state2 = $city_state2[1];

    $cep2 = explode("CEP: ", $address2[4])[1];

    $delivery_days = json_decode($row['operation_delivery_days']);
?>
    <script>
        const current_locales = <?php echo json_encode($locales); ?>;
        const current_state = <?php echo json_encode($row["uf"]); ?>;
    </script>

    <style>
        .select2-selection {
            height: auto !important;
        }
    </style>
    <div class="container-fluid">
        <!-- row -->
        <div class="row">
            <div class="col-xl-12 col-xxl-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Dados da operação</h4>
                    </div>
                    <div class="card-body">
                        <form id="UpdateOperationForm">
                            <input type="hidden" name="operation" value="<?php echo addslashes($_GET['operation']); ?>">
                            <div class="row">
                                <div class="col-lg-12 mb-2">
                                    <input type="hidden" name="action" value="update-operation">
                                    <div class="form-group">
                                        <label class="text-label">Insira o nome da Operação Local<i class="req-mark">*</i></label>
                                        <input placeholder="Digite o nome" type="text" class="form-control" id="text-nome-operacao" name="nome-operacao" required value="<?php echo $row['operation_name']; ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="cep-operacao">Informe o endereço para armazenamento de estoque (CEP)</label>
                                        <input onblur="pesquisacep(this.value);" type="text" class="form-control cep" id="CEP" id="cep-operacao" name="cep-operacao" placeholder="Apenas Números" value="<?php echo $cep; ?>" required>
                                    </div>
                                    <hr class="my-4">
                                    <div class="row">
                                        <div class=" col-md-8 mb-3" id="div-rua">
                                            <label for="address">Endereço</label>
                                            <input type="text" class="form-control" id="rua" name="endereco-operacao" value="<?php echo $street; ?>" placeholder="Rua, Avenida..." required>
                                        </div>
                                        <div class=" col-md-4 mb-3" id="div-numero">
                                            <label for="numero">Número</label>
                                            <input type="text" class="form-control" id="numero" name="numero-operacao" value="<?php echo $number; ?>" placeholder="" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="  col-md-4 mb-2" id="div-bairro">
                                            <label for="bairro-operacao">Bairro</label>
                                            <input type="text" class="form-control" id="bairro" name="bairro-operacao" value="<?php echo $bairro; ?>" placeholder="" value="" required>
                                        </div>
                                        <div class="  col-md-4 mb-3" id="div-cidade">
                                            <label for="cidade-operacao">Cidade</label>
                                            <input type="text" class="form-control" id="cidade" name="cidade-operacao" value="<?php echo $city; ?>" placeholder="" value="" required>
                                        </div>
                                        <div class="col-md-4 mb-2 " id="div-uf">
                                            <label for="estado-operacao">Estado</label>
                                            <input type="text" name="estado-operacao" class="form-control" id="uf" value="<?php echo $state; ?>" placeholder="" value="" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3" id="div-referencia">
                                            <label for="referencia-operacao">Complemento</label>
                                            <input type="text" name="referencia-operacao" class="form-control" id="referencia-operacao" value="<?php echo $complement; ?>" placeholder="Apartamento, Bloco, etc." value="">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="text-label">Documento do destinatário<i class="req-mark">*</i></label>
                                            <input placeholder="CPF/CPNJ" type="text" class="form-control cpf-cnpj" id="text-doc-destinatario" value="<?php echo $row['destinatary_doc']; ?>" name="doc-destinatario" required>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="text-label">Telefone de contato<i class="req-mark">*</i></label>
                                            <input placeholder="Número de telefone" type="text" placeholder="(99) 9 9999-9999" class="form-control telefone-operation" value="<?php echo $row['telefone']; ?>" id="text-telefone-destinatario" name="telefone-destinatario" required>
                                        </div>
                                    </div>

                                    <hr class="my-4">
                                    <div class="form-group">
                                        <label for="cep-operacao">Informe o endereço para armazenamento de estoque (CEP)</label>
                                        <input onblur="pesquisacep(this.value);" type="text" class="form-control cep" id="cep-operacao-2" name="cep-operacao-2" placeholder="Apenas Números" value="<?php echo $cep; ?>" required>
                                    </div>
                                    <hr class="my-4">
                                    <div class="row">
                                        <div class=" col-md-8 mb-3" id="div-rua">
                                            <label for="rua2">Endereço 2</label>
                                            <input type="text" class="form-control" id="rua2" name="endereco-operacao-2" value="<?php echo $street2; ?>" placeholder="Rua, Avenida..." required>
                                        </div>
                                        <div class=" col-md-4 mb-3" id="div-numero">
                                            <label for="numero2">Número 2</label>
                                            <input type="text" class="form-control" id="numero2" name="numero-operacao-2" value="<?php echo $number2; ?>" placeholder="" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="  col-md-4 mb-2" id="div-bairro">
                                            <label for="bairro-operacao-2">Bairro 2</label>
                                            <input type="text" class="form-control" id="bairro2" name="bairro-operacao-2" value="<?php echo $bairro2; ?>" placeholder="" value="" required>
                                        </div>
                                        <div class="  col-md-4 mb-3" id="div-cidade">
                                            <label for="cidade-operacao">Cidade 2</label>
                                            <input type="text" class="form-control" id="cidade2" name="cidade-operacao-2" value="<?php echo $city2; ?>" placeholder="" value="" required>
                                        </div>
                                        <div class="col-md-4 mb-2 " id="div-uf">
                                            <label for="eestado-operacao-2">Estado 2</label>
                                            <input type="text" name="estado-operacao-2" class="form-control" id="uf2" value="<?php echo $state2; ?>" placeholder="" value="" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3" id="div-referencia">
                                            <label for="referencia-operacao-2">Complemento 2</label>
                                            <input type="text" name="referencia-operacao-2" class="form-control" id="referencia-operacao-2" value="<?php echo $complement2; ?>" placeholder="Apartamento, Bloco, etc." value="">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="text-label">Documento do destinatário 2</label>
                                            <input placeholder="CPF/CPNJ" type="text" class="form-control cpf-cnpj" id="text-doc-destinatario-2" value="<?php echo $row['destinatary_doc']; ?>" name="doc-destinatario-2" required>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="text-label">Telefone de contato 2</label>
                                            <input placeholder="Número de telefone" type="text" placeholder="(99) 9 9999-9999" class="form-control telefone-operation" value="<?php echo $row['telefone_2']; ?>" id="text-telefone-destinatario-2" name="telefone-destinatario-2" required>
                                        </div>
                                    </div>                                    

                                    <div class="col-md-12 text-right">
                                        <button type="submit" id="SubmitButton" class="btn btn-success">Editar Operação</button>
                                        <a class="btn btn-danger" id="deleteOperation" data-id="<?= $operation_id; ?>" title="Excluir Operacão"><i class="fas fa-trash-alt mr-2"></i>Excluir</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div id="product-accordion" class="accordion accordion-with-icon">
            <div class="card accordion__item">
                <div class="card-header collapsed" data-toggle="collapse" data-target="#cities-collapse" aria-expanded="false">
                    <h4 class="card-title"><i style="color: #777777" class="fas fa-angle-down"></i>&nbsp; Localidades</h4>
                </div>
                <div id="cities-collapse" class="card-bodyaccordion__body collapse" data-parent="#product-accordion">
                    <div class="accordion__body--text">
                        <form id="UpdateCitiesOperation">
                            <input type="hidden" name="operation" value="<?php echo $operation_id; ?>">
                            <input type="hidden" name="action" value="update-cities-operation">
                            <div class="form-group">
                                <label class="text-label">Essa Operação Local pertence a qual estado<i class="req-mark">*</i></label>
                                <select id="select-uf-operacao" class="d-block default-select" required data-live-search="true">
                                    <option disabled selected>Selecione a UF</option>
                                    <?php
                                    foreach ($ufs as $uf) {
                                        if ($uf->{'UF-sigla'} == $row["uf"]) {
                                            echo '<option selected value="' . $uf->{'UF-sigla'} . '">' . $uf->{'UF-nome'} . '</option>';
                                        } else {
                                            echo '<option value="' . $uf->{'UF-sigla'} . '">' . $uf->{'UF-nome'} . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <input type="hidden" id="text-uf-operacao" value="<?php echo $row['uf']; ?>" name="uf-operacao" disabled>

                            <div class="form-group cidades-container">
                                <label class="text-label">Quais cidades compõem essa Operação Local<i class="req-mark">*</i></label>
                                <select required data-uf="" id="select-cidade-operacao" class="d-block select-cidade"  multiple="multiple">
                                </select>
                            </div>  
                            <input type="hidden" id="text-cidade-operacao" name="cidades-operacao" value="<?php echo implode(",", $locales) ?>">

                            <div class="col-md-12 text-right">
                                <button type="submit" id="SubmitButton" class="btn btn-success">Salvar localidades</button>
                            </div>                                          
                        </form>    
                    </div>                    
                </div>
            </div>

            <div class="card accordion__item">
                <div class="card-header collapsed" data-toggle="collapse" data-target="#week-collapse" aria-expanded="false">
                    <h4 class="card-title"><i style="color: #777777" class="fas fa-angle-down"></i>&nbsp; Disponibilidade</h4>
                </div>
                <div id="week-collapse" class="card-bodyaccordion__body collapse" data-parent="#product-accordion">
                    <div class="accordion__body--text">
                        <form id="UpdateDeliveryDays">
                            <input type="hidden" name="operation" value="<?php echo addslashes($_GET['operation']); ?>">
                            <input type="hidden" name="action" value="update-availability-operation">
                            <div class="form-group">
                                <label class="text-label">
                                    Disponibilizar entregas em quais dias da semana:<i class="req-mark">*</i>
                                </label>
                                <select class="delivery-days-select" name="delivery-days[]" multiple="multiple" required>
                                    <option value="1" <?= in_array(1, $delivery_days) ? "selected" : null ?>>Domingo</option>
                                    <option value="2" <?= in_array(2, $delivery_days) ? "selected" : null ?>>Segunda-Feira</option>
                                    <option value="3" <?= in_array(3, $delivery_days) ? "selected" : null ?>>Terça-Feira</option>
                                    <option value="4" <?= in_array(4, $delivery_days) ? "selected" : null ?>>Quarta-Feira</option>
                                    <option value="5" <?= in_array(5, $delivery_days) ? "selected" : null ?>>Quinta-Feira</option>
                                    <option value="6" <?= in_array(6, $delivery_days) ? "selected" : null ?>>Sexta-Feira</option>
                                    <option value="7" <?= in_array(7, $delivery_days) ? "selected" : null ?>>Sábado</option>
                                </select>
                                <input type="hidden" id="delivery-days-select-text" name="delivery-days-select-text" value="">
                            </div>                            
                            
                            <div class="col-md-12 text-right">
                                <button type="submit" id="SubmitButton" class="btn btn-success">Salvar disponibilidades</button>                                         
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
}
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>

<script>
    $("#deleteOperation").on('click', function(element) {
        const idClicked = element.target.dataset.id;

        console.log(element.target)
        Swal.fire({
            title: "Deseja deletar essa operação?",
            text: "Não será mais possivel enviar estoques para essa localidade!",
            icon: 'warning',
            showCancelButton: true
        }).then(({
            isConfirmed
        }) => {
            if (isConfirmed) {
                const URL = `/ajax/delete-local-operation.php?idClicked=${idClicked}`;
                $.ajax({
                    url: URL,
                    type: "GET",
                    dataType: 'json',
                    processData: true,
                    contentType: false,
                    success: function({
                        type,
                        msg,
                        title
                    }) {
                        if (type == 'success') {
                            Swal.fire({
                                title: title,
                                text: msg,
                                icon: type
                            }).then(() => {
                                window.location.href = "/localidades/operacoes-locais/";
                            });
                        }
                    }
                });
            }
        });
    });
</script>


<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    // select dos dias da semana disponiveis para entrega
    $(document).ready(function($) {
        $('.delivery-days-select').select2();

        $(".delivery-days-select").change(function() {
            var multipleValues = $(".delivery-days-select").val();
            $("#delivery-days-select-text").val(multipleValues);
        });

        $('.select-cidade').select2();
    });


    $("#UpdateCitiesOperation").submit(function () {
        var estado_operacao = $("#text-uf-operacao").val();
        var cidade_operacao = $("#text-cidade-operacao").val();

        if ( estado_operacao == null || cidade_operacao == null ) {
            Swal.fire({
                title: "Erro!",
                text: "Todos os detalhes da localidade precisam ser informados.",
                icon: "warning",
            });
            return false;
        }

        // Captura os dados do formulário
        var UpdateCitiesOperation = document.getElementById("UpdateCitiesOperation");

        // Instância o FormData passando como parâmetro o formulário
        var formData = new FormData(UpdateCitiesOperation);

        formData.append("cidades-operacao", cidade_operacao);
        formData.append("uf-operacao", estado_operacao);

        var url = u + "/ajax/add-operation-ajax.php";
        // Envia O FormData através da requisição AJAX
        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            dataType: "json",
            processData: false,
            contentType: false,
            success: function (feedback) {
                if (feedback.status > 0) {
                    Swal.fire({
                        title: "Sucesso!",
                        html: "A operação foi atualizada com sucesso. <br> As taxas de entrega por cidade foram reiniciadas, configure-as novamente na página de edição de operadores.",
                        icon: "success",
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Erro!",
                        text: feedback.msg,
                        icon: "warning",
                    });
                }
            },
        });

        return false;
    });

    $("#UpdateDeliveryDays").submit(function () {

        // Captura os dados do formulário
        var UpdateDeliveryDays = document.getElementById("UpdateDeliveryDays");

        // Instância o FormData passando como parâmetro o formulário
        var formData = new FormData(UpdateDeliveryDays);

        var url = u + "/ajax/add-operation-ajax.php";
        // Envia O FormData através da requisição AJAX
        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            dataType: "json",
            processData: false,
            contentType: false,
            success: function (feedback) {
                if (feedback.status > 0) {
                    Swal.fire({
                        title: "Sucesso!",
                        html: "A operação foi atualizada com sucesso.",
                        icon: "success",
                    }).then(() => {
                        window.location.reload(); 
                    });
                } else {
                    Swal.fire({
                        title: "Erro!",
                        text: feedback.msg,
                        icon: "warning",
                    });
                }
            },
        });

        return false;
    });
</script>