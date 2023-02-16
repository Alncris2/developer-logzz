<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}

$page_title = "Informar Envio | Logzz";
$locale_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');


$get_product_list = $conn->prepare('SELECT * FROM products WHERE user__id = :user__id AND product_trash = 0 AND status = 1');
$get_product_list->execute(array('user__id' => $_SESSION['UserID']));

// PEGAR OPERAÇÕES LOCAIS 
$get_locales = $conn->prepare('SELECT * FROM local_operations WHERE operation_active = 1');
$get_locales->execute();
$locales = $get_locales->fetchAll();


// PEGAR CENTROS DE DISTRIBUIÇÃO 
$get_locales_center = $conn->prepare('SELECT locale_name, locale_id FROM locales WHERE type_locale = 1');
$get_locales_center->execute();
$locales_center = $get_locales_center->fetchAll();

?>
<div class="container-fluid">
    <!-- row -->
    <div class="row">
        <div class="col-xl-10 col-xxl-10">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Detalhes do Envio</h4>
                    
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 order-md-2">
                            <form id="ReportShipForm" action="<?php echo SERVER_URI; ?>">
                                <!-- <h4 class="mb-3">Detalhes do Entrega</h4> -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="nome-pedido">Data do Envio</label>
                                        <div class="media bg-light p-3 rounded align-items-center">
                                            <div class="media-body">
                                                <span class="fs-16"><?php echo date("d/m/Y"); ?></span>
                                            </div>
                                        </div>
                                        <input type="hidden" value="report-ship" name="action">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="nome-pedido">Status do Envio</label>
                                        <div class="media bg-light p-3 rounded align-items-center">
                                            <div class="media-body">
                                                <span class="fs-16">A enviar</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label class="text-label">Produto<i class="req-mark">*</i></label>
                                            <select id="select-ship-product" class="d-block default-select">
                                                <option disabled selected>Selecione o Produto</option>
                                                <?php
                                                while ($prodcut = $get_product_list->fetch()) {
                                                ?>
                                                    <option value="<?php echo $prodcut['product_id']; ?>"><?php if (strlen($prodcut['product_name']) > 30) {
                                                                                                                echo substr($prodcut['product_name'], 0, 30) . "...";
                                                                                                            } else {
                                                                                                                echo $prodcut['product_name'];
                                                                                                            } ?></option>
                                                <?php
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <input type="hidden" id="text-ship-product" name="produto" value="" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label class="text-label">Tipo de localidade<i class="req-mark">*</i></label>
                                            <select id="select-locale-center" name="localidade" class="d-block default-select"required>
                                                <option disabled selected>Selecione o tipo de localidade</option>
                                                <!-- <optgroup label="Centro de Distribuição">
                                                    <?php foreach($locales_center as $locale_center): ?>
                                                        <option value="<?= $locale_center['locale_id']?>+CD"> <?= $locale_center['locale_name']; ?> </option>
                                                    <?php endforeach; ?>
                                                </optgroup> -->
                                                <optgroup label="Operação Local">
                                                    <?php foreach($locales as $locale): ?>
                                                        <option value="<?= $locale['operation_id']?>+OPL"> <?= $locale['operation_name']; ?> </option>
                                                    <?php endforeach; ?>
                                                </optgroup>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label class="text-label">Quantidade Enviada<i class="req-mark">*</i></label>
                                            <input type="number" class="form-control" name="quantidade-enviada" value="" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group">
                                            <label class="text-label">Código de Rastreio<i class="req-mark">*</i></label>
                                            <input type="text" class="form-control" name="codigo-rastreio" value="" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-12 mb-3">
                                        <label class="text-label">Anexar Nota Fiscal <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="top" title="Estoques enviados com nota fiscal terão 100% de garantia em caso de quaisquer eventuais problemas."></i></label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-file-invoice-dollar"></i></span>
                                            </div>
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" name="nota-fiscal" accept=".png, .jpg, .pdf">
                                                <label class="custom-file-label">Selecionar arquivo...</label>
                                            </div>
                                        </div>
                                    </div>
                                    <button class="btn btn-success btn-lg btn-block" type="submit" name="action">Confirmar Solicitação de Envio</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>
