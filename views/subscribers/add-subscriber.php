<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID'])) || $_SESSION['UserPlan'] != 5) {
    header('Location: ' . SERVER_URI . '/login');
}

$page_title = "Cadastrar Usuário | Logzz";
$subscriber_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

?>
<div class="container-fluid">
    <!-- row -->
    <div class="row">
        <div class="col-xl-6 col-xxl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Dados do Usuário</h4>
                    <a href="<?php echo SERVER_URI; ?>/usuarios/" class="btn btn-rounded btn-success">
                        <i class="fa fa-arrow-left color-success"></i>
                        Voltar
                    </a>
                </div>
                <div class="card-body">
                    <form id="AddSubscriberForm" action="novo-assinante" method="POST">
                        <div class="row">
                            <div class="col-lg-12 mb-2">
                                <input type="hidden" name="action" value="new-subscriber">
                                <div class="form-group">
                                    <label class="text-label">Nome<i class="req-mark">*</i></label>
                                    <input type="text" name="nome-assinante" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="text-label">Email<i class="req-mark">*</i></label>
                                    <input type="email" name="email-assinante" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="text-label">Telefone<i class="req-mark">*</i></label>
                                    <input type="text" name="phone-assinante" class="form-control phone" required>
                                </div>
                                <div class="form-group">
                                    <label class="text-label">Crie uma senha<i class="req-mark">*</i></label>
                                    <input type="text" id="input-nova-senha" name="senha-assinante" autocomplete="off" class="form-control" required>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <a id="generate-new-pass" class="btn btn-success">Gerar Senha</a>
                                    </div>
                                    <div class="custom-control custom-checkbox checkbox-success col-md-8" style="font-size: 1 em">
                                        <input type="checkbox" class="custom-control-input" name="send-new-user-email" id="send-new-user-email" checked>
                                        <label class="custom-control-label" for="send-new-user-email"> Enviar email com os dados de acessos do usuário.</label>
                                        <input type="hidden" id="send-new-user-email-text" name="enviar-email" class="form-control" value="1">
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
            <button type="submit" id="SubmitButton" class="btn btn-success">Cadastrar Usuário</button>
        </div>
        <div class="col-xl-6 col-xxl-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Informações do Plano</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12 mb-2">

                            <div class="form-group">
                                <label class="text-label">Plano<i class="req-mark">*</i></label>
                                <select id="select-plano-assinante" class="d-block default-select">
                                    <option selected disabled>Selecione o plano</option>
                                    <option value="1">Bronze</option>
                                    <option value="2">Silver</option>
                                    <option value="3">Gold</option>
                                    <option value="4">Personalizado</option>
                                </select>
                            </div>
                            <input type="hidden" id="text-plano-assinante" name="plano-assinante" required>

                            <div id="tax-disabled-field" class="form-group">
                                <label class="text-label">Taxa Pagamento Físico<i class="req-mark">*</i></label>
                                <input class="form-control" id="disabled-tax-input" type="text" disabled>
                            </div>

                            <div id="tax-field" class="form-group d-none">
                                <label class="text-label">Taxa Pagamento Físico<i class="req-mark">*</i></label>
                                <select id="select-taxa-assinante" class="d-block default-select">
                                    <option disabled selected>Selecione a taxa</option>
                                    <option value="0">0%</option>
                                    <option value="0.0297">2,97%</option>
                                    <option value="0.0397">3,97%</option>
                                    <option value="0.0497">4,97%</option>
                                    <option value="0.0597">5.97%</option>
                                    <option value="0.0697">6.97%</option>
                                    <option value="0.0797">7.97%</option>
                                </select>
                            </div>
                            <input type="hidden" id="text-taxa-assinante" name="taxa-assinante" required>
                            <div id="prazo-disabled-field" class="form-group">
                                <label class="text-label">Prazo de Liberação<i class="req-mark">*</i></label>
                                <input class="form-control" id="disabled-prazo-input" type="text" disabled>
                            </div>

                            <div id="prazo-field" class="form-group d-none">
                                <label class="text-label">Prazo de Liberação<i class="req-mark">*</i></label>
                                <select id="select-prazo-assinante" class="d-block default-select">
                                    <option disabled selected>Selecione o Prazo</option>
                                    <option value="7">7 dias </option>
                                    <option value="14">14 dias</option>
                                    <option value="30">30 dias</option>
                                </select>
                            </div>
                            <input type="hidden" id="text-prazo-assinante" name="prazo-assinante" required>

                            <div id="entrega-disabled-field" class="form-group">
                                <label class="text-label">Custo por Entrega<i class="req-mark">*</i></label>
                                <input class="form-control" id="disabled-entrega-input" type="text" disabled>
                            </div>

                            <div id="entrega-field" class="form-group d-none">
                                <label class="text-label">Custo por Entrega<i class="req-mark">*</i></label>
                                <input type="text" id="text-entrega-assinante" name="valor-entrega-assinante" placeholder="Digite o custo por entrega" class="form-control money" required>
                            </div>

                            <div class="form-group">
                                <label class="text-label">Comissão por recrutamento<i class="req-mark">*</i></label>
                                <input type="text" id="text-comissao-recrutamento" name="valor-comissao-recrutamento" placeholder="Digite o valor da comissão" class="form-control money" required>
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