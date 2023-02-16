<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID'])) || $_SESSION['UserPlan'] != 5) {
    header('Location: ' . SERVER_URI . '/login');
}

$page_title = "Editar Operador Logístico | Logzz";
$operator_page = true; 
$profile_page = true;
$operation_id;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

$user_code = addslashes($_GET['user']);

$get_operator = $conn->prepare("SELECT * FROM users u INNER JOIN logistic_operator lo ON u.user__id=lo.user_id WHERE u.user_code = :user_code");
$get_operator->execute(array("user_code" => $user_code));


while($row = $get_operator->fetch()) {
  $get_delivery_taxes = $conn->prepare("SELECT * FROM operations_delivery_taxes od INNER JOIN operations_locales ol ON ol.id=od.operation_locale WHERE od.operator_id = :operator_id");
  $get_delivery_taxes->execute(array("operator_id" => $row["operator_id"]));

  $delivery_taxes = array();
  while($row2 = $get_delivery_taxes->fetch()) {
    $delivery_tax = array();
    $delivery_tax["city"] = $row2["city"];
    $delivery_tax["complete_tax"] = $row2["complete_delivery_tax"];
    $delivery_tax["frustrated_tax"] = $row2["frustrated_delivery_tax"];

    array_push($delivery_taxes, $delivery_tax);
  }

  $credit_taxes = array();
  for($i = 1; $i < 13; $i++) {
    $credit_taxes[$i] = $row["credito_tax_" . $i . "x"];
  }
?>
  <script>


    const current_credit_taxes = <?php echo json_encode($credit_taxes); ?>;

    const current_delivery_taxes = <?php echo json_encode($delivery_taxes); ?>;

  </script>
    <div class="container-fluid">
        <!-- row -->
        <div class="row">
            <div class="col-xl-6 col-xxl-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Dados do Operador</h4>
                    </div>
                    <div class="card-body">
                        <form id="UpdateOperatorForm" method="POST">
                            <div class="row">
                                <div class="col-lg-12 mb-2">
                                    <input type="hidden" name="action" value="update-operator">
                                    <input type="hidden" name="cod-operador" value="<?php echo $row["user_code"] ?>">
                                    <input type="hidden" name="operator-id" value="<?php echo $row["operator_id"] ?>">
                                    <div class="form-group">
                                        <label class="text-label">Nome<i class="req-mark">*</i></label>
                                        <input type="text" id="nomeOperador" name="nome-operador" class="form-control" value="<?php echo $row["full_name"] ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="text-label">Email<i class="req-mark">*</i></label>
                                        <input type="email" id="emailOperador" name="email-operador" class="form-control" value="<?php echo $row["email"] ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="text-label">Telefone<i class="req-mark">*</i></label>
                                        <input type="text" id="telefoneOperador" name="telefone-operador" placeholder="(99) 9 9999-9999" value="<?php echo $row["user_phone"] ?>" class="form-control telefone-operation" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="text-label">Documento<i class="req-mark">*</i></label>
                                        <input placeholder="CPF/CPNJ" type="text" id="docOperador" name="doc-operador" value="<?php echo $row["company_doc"] ?>" class="form-control cpf-cnpj" required>
                                    </div>
                                    <a href="#" class="btn btn-success btn-xs light" data-toggle="modal" data-target="#ModalAlterarSenha">Alterar Senha do Operador Logístico</a>
                                    
                                </div> 
                            </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-xxl-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Informações</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-12 mb-2"> 
                                <div class="form-group d-none">
                                    <label class="text-label">Integrar a operação local<i class="req-mark">*</i></label>
                                    <select id="select-operacao-local" class="d-block default-select">
                                        <option selected disabled>Selecione a operação</option>
                                        <?php
                                        $stmt = $conn->prepare("SELECT operation_id, operation_name FROM local_operations");
                                        $stmt->execute();

                                        while($operation = $stmt->fetch()) {
                                            $operation_id = $operation["operation_id"];
                                            if($operation_id == $row["local_operation"]) {
                                                echo '<option selected value=' . $operation_id  . '>' . $operation["operation_name"] . '</option>';
                                            } else {
                                                echo '<option value=' . $operation_id  . '>' . $operation["operation_name"] . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <input type="hidden" id="text-operacao-local" name="operacao-local" value="<?php echo $row["local_operation"] ?>" required>

                                <div class="form-group">
                                    <label class="text-label">Taxa cobrada na opção de pagamento débito<i class="req-mark">*</i></label>
                                    <input placeholder="Digite a taxa" type="text" class="form-control" value="<?php echo number_format($row['debito_tax'], 2) ?>" id="text-taxa-debito" name="taxa-debito" required>
                                </div>

                                <div class="form-group credit-tax-container">
                                    <label class="text-label">Selecione uma opção de pagamento crédito e digite sua taxa abaixo<i class="req-mark">*</i></label>
                                    <select id="select-taxa-credito" class="d-block default-select">
                                        <option disabled selected>Selecione o número de vezes</option>
                                        <option id="credit-1" value="1">1x - <?php echo $row["credito_tax_1x"] ?>%</option>
                                        <option id="credit-2" value="2">2x - <?php echo $row["credito_tax_2x"] ?>%</option>
                                        <option id="credit-3" value="3">3x - <?php echo $row["credito_tax_3x"] ?>%</option>
                                        <option id="credit-4" value="4">4x - <?php echo $row["credito_tax_4x"] ?>%</option>
                                        <option id="credit-5" value="5">5x - <?php echo $row["credito_tax_5x"] ?>%</option>
                                        <option id="credit-6" value="6">6x - <?php echo $row["credito_tax_6x"] ?>%</option>
                                        <option id="credit-7" value="7">7x - <?php echo $row["credito_tax_7x"] ?>%</option>
                                        <option id="credit-8" value="8">8x - <?php echo $row["credito_tax_8x"] ?>%</option>
                                        <option id="credit-9" value="9">9x - <?php echo $row["credito_tax_9x"] ?>%</option>
                                        <option id="credit-10" value="10">10x - <?php echo $row["credito_tax_10x"] ?>%</option>
                                        <option id="credit-11" value="11">11x - <?php echo $row["credito_tax_11x"] ?>%</option>
                                        <option id="credit-12" value="12">12x - <?php echo $row["credito_tax_12x"] ?>%</option>
                                    </select>
                                </div>
                                <input type="hidden" id="text-taxa-credito" name="taxa-credito" disabled>

                                <div id="taxa-prazo-credito" class="form-group d-none">
                                    <label class="text-label">Taxa cobrada no prazo de <span id="text-credit-tax"></span><i class="req-mark">*</i></label>
                                    <input placeholder="Digite a taxa" type="text" class="form-control" id="text-taxa-prazo">
                                </div>

                                <div class="form-group">
                                    <label class="text-label">Taxa cobrada no opção de pagamento PIX e dinheiro<i class="req-mark">*</i></label>
                                    <input placeholder="Digite a taxa" type="text" class="form-control" value="<?php echo number_format($row['dinheiro_tax'], 2) ?>" id="text-taxa-dinheiro" name="taxa-dinheiro" required>
                                </div>

                                <div class="form-group cities-select-container">
                                    <label class="text-label">Selecione uma cidade dentre as opções e digite sua taxa abaixo<i class="req-mark">*</i></label>
                                    <select id="select-taxa-cidades" class="d-block default-select">
                                        <option id="disabled-city-option" disabled selected>Selecione uma operação</option>
                                    </select>
                                </div>
                                <input type="hidden" id="text-cidade-taxa" disabled>

                                <div class="form-group d-none" id="taxa-completa-input">
                                    <label class="text-label">Taxa por entrega com status "Completa": <span id="complete_city_name"></span><i class="req-mark">*</i></label>
                                    <input placeholder="Inserir valor pago para cada cidade componente, de forma isolada" type="text" class="form-control" id="text-entrega-completa" name="taxa-entrega-completa">
                                </div>

                                <div class="form-group d-none" id="taxa-frustrada-input">
                                    <label class="text-label">Taxa por entrega com status "Frustrada": <span id="frustrated_city_name"></span><i class="req-mark">*</i></label>
                                    <input placeholder="Inserir valor pago para cada cidade componente, de forma isolada" type="text" class="form-control" id="text-entrega-frustrada" name="taxa-entrega-frustrada">
                                </div>
                            </div>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-success">Editar Operador</button>
        </div>
    </div> 
    <div class="modal fade" id="ModalAlterarSenha" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header center text-center d-block">
                    <h5 class="modal-title">Alterar Senha do Operador Logístico</h5>
                    </button>
                </div>
                <div class="card-body">
                    <form id="AdmChangeUserPassForm" method="POST">
                        <div class="row">
                            <div class="col-lg-12 mb-2">
                                <input type="hidden" name="action" value="adm-change-user-password">
                                <input type="hidden" name="user" value="<?php echo addslashes($_GET['user']); ?>">
                                <div class="form-group">
                                    <label class="text-label">Nova Senha<i class="req-mark">*</i></label>
                                    <input type="text" name="senha" id="senha" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="text-label">Confirmar Nova Senha<i class="req-mark">*</i></label>
                                    <input type="text" name="confirma-senha" id="confirma-senha" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-success btn-block mb-4">Confirmar Alteração</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php
}
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>
