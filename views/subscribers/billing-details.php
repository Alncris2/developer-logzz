<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

//Verifica privilégio de administrador
if (!(isset($_SESSION['UserID'])) || $_SESSION['UserPlan'] != 5) {
    header('Location: ' . SERVER_URI . '/login');
}


$billing_id = addslashes($_GET['billing']);

$page_title = "Solicitação de Saque | Logzz";
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');


// Localiza a solicitação pelo ID
$billing_available = $conn->prepare('SELECT * FROM billings WHERE billing_id = :billing_id LIMIT 1');
$billing_available->execute(array('billing_id' => $billing_id));


$billing_detail = $billing_available->fetch();

$billing_authorizer = $billing_detail['billing_authorizer'];
$billing_receiver = $billing_detail['user__id'];

$account_id = $billing_detail['billing_bank_account'];

$user_authorizer = $conn->prepare('SELECT * FROM users WHERE user__id = :user__id LIMIT 1');
$user_authorizer->execute(array('user__id' => $billing_authorizer));

$user_receiver = $conn->prepare('SELECT * FROM users WHERE user__id = :user__id LIMIT 1');
$user_receiver->execute(array('user__id' => $billing_receiver));

$authorizer = $user_authorizer->fetch();
$receiver = $user_receiver->fetch();

if ($billing_available->rowCount() != 0) {

    //Ver
    if ($billing_detail['billing_released'] == null) {
?>
        <div class="container-fluid">
            <!-- row -->
            <div class="row">
                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Processar Solicitação</h4>
                        </div>
                        <div class="card-body">
                            <form id="ReleaseBillingForm" action="liberar-saque" method="POST">
                                <div class="row">
                                    <div class="col-lg-12 mb-2">
                                        <input type="hidden" name="action" value="liberar-solicitacao">
                                        <input type="hidden" name="billing" value="<?php echo $billing_id; ?>">
                                        <label class="text-label">Anexar Comprovante<i class="req-mark">*</i></label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-file-invoice-dollar"></i></span>
                                            </div>
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" name="comprovante" accept=".png, .jpg, .pdf">
                                                <label class="custom-file-label">Selecionar arquivo...</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" id="processBillingBtn" class="btn btn-success mt-3 mb-3">Processar Solicitação</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Descrição da Movimentação</h4>
                        </div>
                        <div class="card-body">

                            <div class="row mb-1">
                                <div class="col-sm-12">
                                    <spam class="mt-2"><small>Valor Solicitado</small></spam>
                                    <h5 class="mb-0"><?= "R$ " . number_format($billing_detail['billing_value_full'], 2, ",", "."); ?></h5>
                                </div>
                            </div>
                            <div class="row mb-1">
                                <div class="col-sm-12">
                                    <spam class="mt-2"><small>Taxa de Saque</small></spam>
                                    <h5 class="mb-0"><?= "R$ " . number_format(2.99, 2, ",", "."); ?></h5>
                                </div>
                            </div>

                            <?php if ($billing_detail['billing_type'] == 'ANTECIPACAO') : ?>
                                <div class="row mb-1">
                                    <div class="col-sm-12">
                                        <spam class="mt-2"><small>Taxa de Antecipação</small></spam>
                                        <h5 class="mb-0"><?= "R$ " . number_format($billing_detail['billing_tax'] - 2.99, 2, ",", "."); ?></h5>
                                    </div>
                                </div>
                            <?php endif ?>

                            <div class="row mb-1">
                                <div class="col-sm-12">
                                    <spam class="mt-2"><small>Valor a Liberar</small></spam>
                                    <h5 class="mb-0"><strong><?= "R$ " . number_format($billing_detail['billing_value'], 2, ",", "."); ?></strong></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Dados Bancários do Assinante</h4>
                        </div>
                        <div class="card-body">
                            <?php
                            if ($billing_detail['billing_bank_account'] > 0) {

                                $get_account = $conn->prepare('SELECT * FROM bank_account_list WHERE account_id = :account_id AND account_user_id = :account_user_id');
                                $get_account->execute(array('account_id' => $account_id, 'account_user_id' => $billing_receiver));

                                $account = $get_account->fetch();

                                if ($account['account_type'] == 1) {
                                    $type = "Corrente";
                                } else {
                                    $type = "Poupança";
                                }

                            ?>
                                <div class="row mb-1">
                                    <div class="col-sm-12">
                                        <spam class="mt-2"><small>Banco</small></spam>
                                        <h5 class="mb-0"><?php echo bankName($account['account_bank']); ?></h5>
                                    </div>
                                </div>
                                <div class="row mb-1">
                                    <div class="col-sm-12">
                                        <spam class="mt-2"><small>Agência</small></spam>
                                        <h5 class="mb-0"><?php echo $account['account_agency']; ?></h5>
                                    </div>
                                </div>
                                <div class="row mb-1">
                                    <div class="col-sm-12">
                                        <spam class="mt-2"><small>Conta</small></spam>
                                        <h5 class="mb-0"><?php echo $account['account_number']; ?></h5>
                                    </div>
                                </div>
                                <div class="row mb-1">
                                    <div class="col-sm-12">
                                        <spam class="mt-2"><small>Tipo de Conta</small></spam>
                                        <h5 class="mb-0"><?php echo $type; ?></h5>
                                    </div>
                                </div>
                                <div class="row mb-1">
                                    <div class="col-sm-12">
                                        <spam class="mt-2"><small>Chave Pix</small></spam>
                                        <h5 class="mb-0"><?php echo $account['account_pix_key']; ?></h5>
                                    </div>
                                </div>
                        </div>
                    <?php
                            } else {
                    ?>
                        <div class="media bg-light p-3 rounded align-items-center mt-1" style="border-radius: 2em !important;padding: 9px 20px !important;">
                            <div class="media-body">
                                <span class="fs-16 text-black"><span>O assinante cadastrou nenhuma conta.</span></span>
                            </div>
                        </div>

                    <?php
                            }
                    ?>

                    </div>
                </div>
            </div>
        </div>
        </div>

    <?php
    } else {

    ?>
        <div class="container-fluid">
            <!-- row -->
            <div class="row">
                <div class="col-xl-12 col-xxl-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Solicitação Processada</h4>
                        </div>
                        <div class="card-body">
                            <span class="text-black"><small>O Pagamento solicitado por <b><?php echo $receiver['full_name']; ?></b> foi liberado por <b><?php echo $authorizer['full_name']; ?></b> em <b><?php echo $billing_detail['billing_released']; ?></b>.</small></span><br>
                            <a href="<?php echo SERVER_URI . "/uploads/saques/comprovantes/" . $billing_detail['billing_proof'] ?>" target="_blank" class="btn btn-success mt-3 mb-3"><i class="fas fa-file-invoice-dollar"></i> Ver comprovante</a>
                            <a onClick="window.history.back();" class="btn btn-light mt-3 mb-3"><i class="fas fa-sign-out-alt"></i> Voltar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
<?php
    }
} else {
    echo '<script>window.location.assign("' . SERVER_URI . '/assinantes/saque/conteudo-nao-encontrado");</script>';
}

require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>