
<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID'])) || $_SESSION['UserPlan'] != 5) {
    header('Location: ' . SERVER_URI . '/login');
}

$page_title = "Cadastrar Operação Local | Logzz";
$operator_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

$ufs = file_get_contents('https://servicodados.ibge.gov.br/api/v1/localidades/estados?view=nivelado');
$ufs = json_decode($ufs);

?>
<div class="container-fluid">
    <!-- row -->
    <div class="row">
        <div class="col-xl-12 col-xxl-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Dados da operação</h4>
                </div>
                <div class="card-body">
                  <form id="AddOperationForm">
                    <div class="row">
                        <div class="col-lg-12 mb-2">
                            <input type="hidden" name="action" value="new-operation">
                            <div class="form-group">
                                <label class="text-label">Insira o nome da Operação Local<i class="req-mark">*</i></label>
                                <input placeholder="Digite o nome" type="text" class="form-control" id="text-nome-operacao" name="nome-operacao" required>
                            </div>

                            <div class="form-group">
                                <label for="cep-operacao">Informe o endereço para armazenamento de estoque (CEP)</label>
                                <input onblur="pesquisacep(this.value);" type="text" class="form-control cep" id="CEP" id="cep-operacao" name="cep-operacao" placeholder="Apenas Números" value="" required>
                            </div>
                            <hr class="my-4">
                            <div class="row">
                                <div class=" col-md-8 mb-3" id="div-rua">
                                    <label for="address">Endereço</label>
                                    <input type="text" class="form-control" id="rua" name="endereco-operacao" placeholder="Rua, Avenida..." required>
                                </div>
                                <div class=" col-md-4 mb-3" id="div-numero">
                                    <label for="numero">Número</label>
                                    <input type="text" class="form-control" id="numero" name="numero-operacao" placeholder="" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="  col-md-4 mb-2" id="div-bairro">
                                    <label for="bairro-operacao">Bairro</label>
                                    <input type="text" class="form-control" id="bairro" name="bairro-operacao" placeholder="" value="" required>
                                </div>
                                <div class="  col-md-4 mb-3" id="div-cidade">
                                    <label for="cidade-operacao">Cidade</label>
                                    <input type="text" class="form-control" id="cidade" name="cidade-operacao" placeholder="" value="" required>
                                </div>
                                <div class="col-md-4 mb-2 " id="div-uf">
                                    <label for="estado-operacao">Estado</label>
                                    <input type="text" name="estado-operacao" class="form-control" id="uf" placeholder="" value="" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3" id="div-referencia">
                                    <label for="referencia-operacao">Complemento</label>
                                    <input type="text" name="referencia-operacao" class="form-control" id="referencia-operacao" placeholder="Apartamento, Bloco, etc." value="">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="text-label">Documento do destinatário<i class="req-mark">*</i></label>
                                    <input placeholder="CPF/CPNJ" type="text" class="form-control cpf-cnpj" id="text-doc-destinatario" name="doc-destinatario" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="text-label">Telefone de contato<i class="req-mark">*</i></label>
                                    <input placeholder="Número de telefone" type="text" placeholder="(99) 9 9999-9999" class="form-control telefone-operation" id="text-telefone-destinatario" name="telefone-destinatario" required>
                                </div>
                            </div>
                            <hr class="mb-4">
                            <div class="form-group">
                                <label class="text-label">Essa Operação Local pertence a qual estado<i class="req-mark">*</i></label>
                                <select id="select-uf-operacao" class="d-block default-select" required data-live-search="true">
                                    <option disabled selected>Selecione a UF</option>
                                    <?php
                                        foreach ($ufs as $uf) {
                                          echo '<option value="' . $uf->{'UF-sigla'} . '">' . $uf->{'UF-nome'} . '</option>';
                                        }
                                    ?>
                                </select>
                            </div>
                            <input type="hidden" id="text-uf-operacao" name="uf-operacao" disabled>

                            <div class="form-group d-none cidades-container">
                                <label class="text-label">Quais cidades compõem essa Operação Local<i class="req-mark">*</i></label>
                                <select required data-uf="" id="select-cidade-operacao" class="d-block default-select select-cidade" multiple="multiple" data-live-search="true">
                                </select>
                            </div>
                            <input type="hidden" id="text-cidade-operacao" name="cidades-operacao" disabled>

                            <button type="submit" id="SubmitButton" class="btn btn-success">Cadastrar Operação</button>
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
