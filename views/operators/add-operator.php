<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID'])) || $_SESSION['UserPlan'] != 5) {
    header('Location: ' . SERVER_URI . '/login');
}

$page_title = "Cadastrar Gerente da Operação | Logzz";
$operator_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

?>
<div class="container-fluid">
    <!-- row -->
    <div class="row">
        <div class="col-xl-6 col-xxl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Dados do Gerente</h4>
                </div>
                <div class="card-body">
                    <form id="AddOperatorForm" method="POST">
                        <div class="row">
                            <div class="col-lg-12 mb-2">
                                <input type="hidden" name="action" value="new-operator">
                                <div class="form-group">
                                    <label class="text-label">Nome<i class="req-mark">*</i></label>
                                    <input type="text" id="nomeOperador" name="nome-operador" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="text-label">Email<i class="req-mark">*</i></label>
                                    <input type="email" id="emailOperador" name="email-operador" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="text-label">Telefone<i class="req-mark">*</i></label>
                                    <input type="text" id="telefoneOperador" name="telefone-operador" placeholder="(99) 9 9999-9999" class="form-control telefone-operation" required>
                                </div>
                                <div class="form-group">
                                    <label class="text-label">Documento<i class="req-mark">*</i></label>
                                    <input placeholder="CPF/CPNJ" type="text" id="docOperador" name="doc-operador" class="form-control cpf-cnpj" required>
                                </div>
                                <div class="form-group">
                                    <label class="text-label">Nova senha<i class="req-mark">*</i></label>
                                    <input type="password" id="input-nova-senha" name="senha-operador" autocomplete="off" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="text-label">Cofirme a nova senha<i class="req-mark">*</i></label>
                                    <input type="password" id="input-conf-senha" name="conf-senha-operador" autocomplete="off" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-success">Cadastrar Operador</button>
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
                                    $stmt = $conn->prepare("SELECT operation_id, operation_name FROM local_operations WHERE operation_active = 1");
                                    $stmt->execute();

                                    while($operation = $stmt->fetch()) {
                                      echo '<option value=' . $operation["operation_id"]  . '>' . $operation["operation_name"] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <input type="hidden" id="text-operacao-local" name="operacao-local" required>

                            <div class="form-group">
                                <label class="text-label">Taxa cobrada na opção de pagamento débito<i class="req-mark">*</i></label>
                                <input placeholder="Digite a taxa" type="text" class="form-control" id="text-taxa-debito" name="taxa-debito" required>
                            </div>

                            <div class="form-group">
                                <label class="text-label">Taxa cobrada na opção de pagamento crédito<i class="req-mark">*</i></label>
                                <select id="select-taxa-credito" class="d-block default-select">
                                    <option disabled selected>Selecione o número de vezes</option>
                                    <option id="credit-1" value="1">1x</option>
                                    <option id="credit-2" value="2">2x</option>
                                    <option id="credit-3" value="3">3x</option>
                                    <option id="credit-4" value="4">4x</option>
                                    <option id="credit-5" value="5">5x</option>
                                    <option id="credit-6" value="6">6x</option>
                                    <option id="credit-7" value="7">7x</option>
                                    <option id="credit-8" value="8">8x</option>
                                    <option id="credit-9" value="9">9x</option>
                                    <option id="credit-10" value="10">10x</option>
                                    <option id="credit-11" value="11">11x</option>
                                    <option id="credit-12" value="12">12x</option>
                                </select>
                            </div>
                            <input type="hidden" id="text-taxa-credito" name="taxa-credito" disabled>

                            <div id="taxa-prazo-credito" class="form-group d-none">
                                <label class="text-label">Taxa cobrada no prazo de <span id="text-credit-tax"></span><i class="req-mark">*</i></label>
                                <input placeholder="Digite a taxa" type="text" class="form-control" id="text-taxa-prazo" required>
                            </div>

                            <div class="form-group">
                                <label class="text-label">Taxa cobrada no opção de pagamento PIX e dinheiro<i class="req-mark">*</i></label>
                                <input placeholder="Digite a taxa" type="text" class="form-control" id="text-taxa-dinheiro" name="taxa-dinheiro" required>
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
                                <input placeholder="Inserir valor pago para cada cidade componente, de forma isolada" type="text" class="form-control" id="text-entrega-completa" name="taxa-entrega-completa" required>
                            </div>

                            <div class="form-group">
                                <label class="text-label">Custo por entrega com status "Frustrada"<i class="req-mark">*</i></label>
                                <input placeholder="Inserir valor pago para cada cidade componente, de forma isolada" type="text" class="form-control" id="text-entrega-frustrada" name="taxa-entrega-frustrada" required>
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>
