<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID'])) || $_SESSION['UserID'] > 4) {
    header('Location: ' . SERVER_URI . '/login');
}

$page_title = "Cadastrar Operador Logístico | Logzz";
$operator_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

?>
<div class="container-fluid">
    <!-- row -->
    <div class="row">
        <div class="col-xl-6 col-xxl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Dados do Operador</h4>
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

                            <div class="form-group">
                                <label class="text-label">Integrar a operação local<i class="req-mark">*</i></label>
                                <select id="select-operacao-local" class="d-block default-select">
                                    <option selected disabled>Selecione a operação</option>
                                    <?php
                                    $stmt = $conn->prepare("SELECT operation_id, operation_name FROM local_operations");
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
                                    <option disabled selected>Selecione a taxa</option>
                                    <option value="1">1x</option>
                                    <option value="2">2x</option>
                                    <option value="3">3x</option>
                                    <option value="4">4x</option>
                                    <option value="5">5x</option>
                                    <option value="6">6x</option>
                                    <option value="7">7x</option>
                                    <option value="8">8x</option>
                                    <option value="9">9x</option>
                                    <option value="10">10x</option>
                                    <option value="11">11x</option>
                                    <option value="12">12x</option>
                                </select>
                            </div>
                            <input type="hidden" id="text-taxa-credito" name="taxa-credito" disabled>

                            <div class="form-group">
                                <label class="text-label">Taxa cobrada no opção de pagamento PIX e dinheiro<i class="req-mark">*</i></label>
                                <input placeholder="Digite a taxa" type="text" class="form-control" id="text-taxa-dinheiro" name="taxa-dinheiro" required>
                            </div>

                            <div class="form-group">
                                <label class="text-label">Custo por entrega com status "Completa"<i class="req-mark">*</i></label>
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
