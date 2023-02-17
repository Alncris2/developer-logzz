<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID'])) || $_SESSION['UserPlan'] < 5) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}

$user__id = $_SESSION['UserID'];

$page_title = "Editar Usuário | Logzz";
$subscriber_page = true;
$profile_page = true;
$select_datatable_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

$user_code = addslashes($_GET['user']);

$get_user_details = $conn->prepare('SELECT users.user__id, full_name, company_doc, email, user_phone, user_code, user_plan, subscriptions.plan_price,subscriptions.user_external_gateway_tax, subscriptions.user_plan_shipping_tax, subscriptions.user_plan_tax, subscriptions.user_payment_term, subscriptions.user_plan_commission_recruitment FROM users INNER JOIN subscriptions ON users.user__id = subscriptions.user__id WHERE user_code = :user_code AND active = 1');
$get_user_details->execute(array('user_code' => $user_code));

if ($get_user_details->rowCount() != 1) {
    echo '<script>window.location.assign("' . SERVER_URI  . '/usuarios/")</script>';
    exit;
}

while ($row = $get_user_details->fetch()) {
    $this_user__id = $row['user__id'];

    # Insere os dados do plano do usuário em um script no footer
    # (arquivo includes/elements/default-footer-script.php)
    # para que sejam exibidos pelo plugin Select2.js   
    $subscriber_plan_details = array(
        'plano' => $row['user_plan'],
        'taxa' => $row['user_plan_tax'],
        'prazo' => $row['user_payment_term'],
        'gateway' => $row['user_external_gateway_tax']
    );

?>

    <div class="container-fluid">
        <!-- row -->
        <div class="row">
            <div class="col-xl-6 col-xxl-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Dados do Usuário</h4>
                        <a href="<?= SERVER_URI . '/usuarios/' ?>" class="btn btn-rounded btn-success">
                            <i class="fa fa-arrow-left color-success"></i>
                            Voltar
                        </a>
                    </div>
                    <div class="card-body">
                        <form id="UpdateSubscriberForm" action="atualizar-assinante" method="POST">
                            <div class="row">
                                <div class="col-lg-12 mb-2">
                                    <input type="hidden" name="action" value="update-subscriber">
                                    <input type="hidden" name="user" value="<?php echo addslashes($_GET['user']); ?>">
                                    <div class="form-group">
                                        <label class="text-label">Nome<i class="req-mark">*</i></label>
                                        <input type="text" name="nome-assinante" class="form-control" required value="<?php echo $row['full_name']; ?>">
                                    </div>
                                    <div class="form-group"> 
                                        <label class="text-label">Email<i class="req-mark">*</i></label>
                                        <input type="email" readonly style="cursor: not-allowed;" name="email-assinante" class="form-control" required value="<?php echo $row['email']; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="text-label">Telefone<i class="req-mark">*</i></label>
                                        <input type="text" readonly style="cursor: not-allowed;" name="telefone-assinante" class="form-control" required value="<?php echo $row['user_phone']; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="text-label">Documento<i class="req-mark">*</i></label>
                                        <input type="text" readonly style="cursor: not-allowed;" name="documento-assinante" class="form-control" required value="<?php echo $row['company_doc']; ?>">
                                    </div>
                                    <a href="#" class="btn btn-success btn-xs light" data-toggle="modal" data-target="#ModalAlterarSenha">Alterar Senha do Usuário</a>
                                </div>
                            </div> 
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-xxl-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Informações do Plano</h4>
                        <button type="button" data-toggle="modal" data-target="#ModalUserPayInfos" class="btn btn-dark light">Ver Dados Bancários</a></button>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-12 mb-2">
                                <div class="form-group">
                                    <label class="text-label">Plano<i class="req-mark">*</i></label>
                                    <select id="select-plano-assinante" class="d-block default-select">
                                        <option value="0" disabled selected>Nenhum Plano Selecionado</option>
                                        <option <?= isset($row['user_plan']) && $row['user_plan'] == 1 ? 'selected' : '' ?> value="1">Bronze</option>
                                        <option <?= isset($row['user_plan']) && $row['user_plan'] == 2 ? 'selected' : '' ?> value="2">Silver</option>
                                        <option <?= isset($row['user_plan']) && $row['user_plan'] == 3 ? 'selected' : '' ?> value="3">Gold</option>
                                        <option <?= isset($row['user_plan']) && $row['user_plan'] == 4 ? 'selected' : '' ?> value="4">Personalizado</option>
                                        <option <?= isset($row['user_plan']) && $row['user_plan'] == 5 ? 'selected' : '' ?> value="5">Administrador</option>
                                    </select>
                                </div>
                                <input type="hidden" id="text-plano-assinante" name="plano-assinante" value="" required>

                                <div class="form-group">
                                    <label class="text-label">Taxa pagamento físico<i class="req-mark">*</i></label>
                                    <select id="select-taxa-assinante" class="d-block default-select">
                                    <option disabled selected>Nenhuma Taxa Selecionada</option>
                                        <option <?= isset($row['user_plan_shipping_tax']) && $row['user_plan_shipping_tax'] == 0 ? 'selected' : '' ?> value="0">0%</option>
                                        
                                        <!-- Opções aparenmente antigas mas que ainda existe ocorrencias -->
                                        <option <?= isset($row['user_plan_shipping_tax']) && $row['user_plan_shipping_tax'] == 0.0297 ? 'selected' : '' ?> value="0.0297">2,97%</option>
                                        <option <?= isset($row['user_plan_shipping_tax']) && $row['user_plan_shipping_tax'] == 0.0397 ? 'selected' : '' ?> value="0.0397">3,97%</option>
                                        <option <?= isset($row['user_plan_shipping_tax']) && $row['user_plan_shipping_tax'] == 0.0497 ? 'selected' : '' ?> value="0.0497">4,97%</option>
                                        <!-- fim das opções aparenmente antigas mas que ainda existe ocorrencias -->

                                        <option <?= isset($row['user_plan_shipping_tax']) && $row['user_plan_shipping_tax'] == 0.0597 ? 'selected' : '' ?> value="0.0597">5,97%</option>
                                        <option <?= isset($row['user_plan_shipping_tax']) && $row['user_plan_shipping_tax'] == 0.0697 ? 'selected' : '' ?> value="0.0697">6,97%</option>
                                        <option <?= isset($row['user_plan_shipping_tax']) && $row['user_plan_shipping_tax'] == 0.0797 ? 'selected' : '' ?> value="0.0797">7,97%</option>
                                    </select>
                                </div>
                                <input type="hidden" id="text-taxa-assinante" name="taxa-assinante" value="" required>

                                <div class="form-group">
                                    <label class="text-label">Prazo de Liberação<i class="req-mark">*</i></label>
                                    <select id="select-prazo-assinante" class="d-block default-select">
                                        <option value="0" disabled selected>Nenhum Prazo Selecionado</option>
                                        <option <?= isset($row['user_payment_term']) && $row['user_payment_term'] == 0 ? 'selected' : '' ?> value="0">0 dias </option>
                                        <option <?= isset($row['user_payment_term']) && $row['user_payment_term'] == 1 ? 'selected' : '' ?> value="1">1 dias </option>
                                        <option <?= isset($row['user_payment_term']) && $row['user_payment_term'] == 7 ? 'selected' : '' ?> value="7">7 dias </option>
                                        <option <?= isset($row['user_payment_term']) && $row['user_payment_term'] == 14 ? 'selected' : '' ?> value="14">14 dias</option>
                                        <option <?= isset($row['user_payment_term']) && $row['user_payment_term'] == 30 ? 'selected' : '' ?> value="30">30 dias</option>
                                    </select>
                                </div>

                                <input type="hidden" id="text-prazo-assinante" name="prazo-assinante" value="" required>
                                
                                <div class="form-group">
                                    <label class="text-label">Recorrência mensal<i class="req-mark">*</i></label>
                                    <input type="text" id="text-recorrencia-mensal" name="valor-recorrencia-mensal" value="<?php echo number_format($row['plan_price'], 2, ',', ''); ?>" class="form-control money" required>
                                </div>
                                <div class="form-group">
                                    <label class="text-label">Custo por Entrega<i class="req-mark">*</i></label>
                                    <input type="text" id="text-entrega-assinante" name="valor-entrega-assinante" value="<?php echo number_format($row['user_plan_shipping_tax'], 2, ',', ''); ?>" class="form-control money" required>
                                </div>

                                <div class="form-group">
                                    <label class="text-label">Comissão por recrutamento<i class="req-mark">*</i></label>
                                    <input type="text" id="text-comissao-recrutamento" name="valor-comissao-recrutamento" value="<?php echo number_format($row['user_plan_commission_recruitment'], 2, ',', ''); ?>" class="form-control money" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" id="SubmitButton" class="btn btn-success mr-3">Salvar as Alterações</button>
            <a id="update-account-status" data-user-code="<?php echo $row['user_code']; ?>" href="?excluir-usuário" class="btn btn-danger light">Suspender Acesso</a>
            </form>
        </div>
    </div>



    <?php // Modal que exibe os dados bancários do usuário. 
    ?>
    <div class="modal fade" id="ModalUserPayInfos" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Dados Bancários do Usuário</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>×</span>
                    </button>
                </div>
                <div class="card-body">
                    <?php
                    $verify_added_accs = $conn->prepare('SELECT meta_value FROM users_meta WHERE user__id = :user__id AND meta_key = "added_accounts"');
                    $verify_added_accs->execute(array('user__id' => $this_user__id));

                    if ($verify_added_accs->rowCount() == 1) {

                        $added_accs = $verify_added_accs->fetch();
                        $added_accs = $added_accs['meta_value'];

                        $this_acc = 1;
                    ?>
                        <table class="table" id="bank-accounts">
                            <thead>
                                <tr>
                                    <th class="col-md-3" style="text-align: center;">Banco</th>
                                    <th class="col-md-3" style="text-align: center;">Agência</th>
                                    <th class="col-md-3" style="text-align: center;">Conta</th>
                                    <th class="col-md-3" style="text-align: center;">Tipo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                while ($this_acc <= $added_accs) {

                                    $meta_key_bank = "ACC_U" . $this_user__id . "-A" . $this_acc . "_BANK";
                                    $meta_key_agency = "ACC_U" . $this_user__id . "-A" . $this_acc . "_AGENCY";
                                    $meta_key_account = "ACC_U" . $this_user__id . "-A" . $this_acc . "_ACCOUNT";
                                    $meta_key_type = "ACC_U" . $this_user__id . "-A" . $this_acc . "_TYPE";

                                    $get_bank = $conn->prepare('SELECT meta_value FROM users_meta WHERE user__id = :user__id AND meta_key = :meta_key');
                                    $get_bank->execute(array('meta_key' => $meta_key_bank, 'user__id' => $this_user__id));
                                    $get_bank = $get_bank->fetch();

                                    $bank_name = bankName($get_bank['meta_value']);

                                    $get_agency = $conn->prepare('SELECT meta_value FROM users_meta WHERE user__id = :user__id AND meta_key = :meta_key');
                                    $get_agency->execute(array('meta_key' => $meta_key_agency, 'user__id' => $this_user__id));
                                    $get_agency = $get_agency->fetch();

                                    $get_account = $conn->prepare('SELECT meta_value FROM users_meta WHERE user__id = :user__id AND meta_key = :meta_key');
                                    $get_account->execute(array('meta_key' => $meta_key_account, 'user__id' => $this_user__id));
                                    $get_account = $get_account->fetch();

                                    $get_type = $conn->prepare('SELECT meta_value FROM users_meta WHERE user__id = :user__id AND meta_key = :meta_key');
                                    $get_type->execute(array('meta_key' => $meta_key_type, 'user__id' => $this_user__id));
                                    $get_type = $get_type->fetch();

                                    if ($get_type['meta_value'] == 1) {
                                        $get_type = "Conta Corrente";
                                    } else {
                                        $get_type = "Conta Poupança";
                                    }

                                ?>
                                    <tr>
                                        <td style="text-align: center;"><?php echo $bank_name; ?></td>
                                        <td style="text-align: center;"><?php echo $get_agency['meta_value']; ?></td>
                                        <td style="text-align: center;"><?php echo $get_account['meta_value']; ?></td>
                                        <td style="text-align: center;"><?php echo $get_type; ?></td>
                                    </tr>
                                <?php

                                    $this_acc = $this_acc + 1;
                                }
                                ?>
                            </tbody>
                        </table>
                    <?php
                    } else {
                    ?>

                        <div class="alert alert-secondary alert-light alert-dismissible fade show">
                            <small>Nenhuma conta ainda.</small>
                        </div>

                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ModalAlterarSenha" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header center text-center d-block">
                    <h5 class="modal-title">Alterar Senha do Usuário</h5>
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