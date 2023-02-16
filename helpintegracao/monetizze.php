<?php require_once ('../includes/config.php'); ?>

<html lang="pt-br">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="icon" type="image/x-icon" href="<?= SERVER_URI . "/images/logo-compact.png"?>">
    <title>Ajuda - Integração Monetizze</title>
  </head>
  <body>

    <nav class="navbar navbar-light"  style="background-color:#fff;height: 70px;">
      <!-- LOGO DROP EXPRESS -->
      <img src="<?= SERVER_URI . "/images/logo2.png.webp"?>" alt="" width="200px">
    </nav>

    <main>
      <div class="container-fluid bg-light" style="height:300px; background: url(<?= SERVER_URI . "/images/background.png"?>) 50% 50% no-repeat;">
        <div class="container-sm d-flex justify-content-center align-items-center h-100">
          <h1 class="text-dark font-weight-bold text-center">Como Fazer a Integração com a Monetizze</h2>
        </div>
      </div>
      
      <div class="container mt-5">
        <p>Dentro da plataforma da Dropexpress informe o <strong>nome da integração a chave da sua conta monetizze e qual produto a integração pertence</strong> conforme anexo abaixo.</p>
        <div class="d-flex justify-content-center align-items-center mt-4">
          <img src="<?= SERVER_URI . "/images/drop2.png"?>" alt="" class="img-fluid img-responsive center-block">
        </div>
         <p class="mt-2">Obs: para conseguir a sua chave unica da conta da monetizze <a href="https://app.monetizze.com.br/login"> entre na sua conta monetizze </a> acesse o menu <strong>Ferramentas > Postback</strong> na tela que irá abrir sua chave única estará logo abaixo.</p>
        <p class="mt-1">Após a criação da url de Postback dentro da plataforma da drop express acesse a sua conta da monetizze. </p>
        <p>1 - Agora Dentro da sua conta Monetizze vá em “Ferramentas” e clique em “Postback”;</strong></p>
        <div class="d-flex justify-content-center align-items-center mt-5">
          <img src="<?= SERVER_URI . "/images/monetizze1.png"?>" alt="" class="img-fluid img-responsive center-block">
        </div>

        <p class="mt-3">2- Na tela que abrirá coloque a <strong>URL de Postback</strong> gerada na plataforma da DropExpress.</p>
        <p><strong>Exemplo de url : https://transporte.primeturoficial.com.br/postback/monetizze/wRuJKSea1sYoclpk.</strong></p>
      
        <p>Selecione <strong>o mesmo produto informado</strong> na criação da url de postback.</p>
        <p>Marque o evento "Finalizada / Aprovada".</p>

        <div class="d-flex justify-content-center align-items-center mt-5">
          <img src="<?= SERVER_URI . "/images/monetizze02.png"?>" alt="" class="img-fluid img-responsive center-block">
        </div>

        <p class="mt-3">3 - Clique no botão  <strong>Salvar</strong></p>

        <p class="mt-3">Pronto! agora após a aprovação da sua integração todos <strong>os produtos informados com status de pagamento completo</strong> estarão chegando na plataforma da DropExpress</p>
      </div>
    </main>
    <footer class="text-center text-lg-start mt-5">
      <!-- Copyright -->
      <div class="text-center p-3" style="background-color: #f7f7f7;">
        © <?= date('Y'); ?> Copyright:
        <a class="text-dark" href="">DropExpress</a>
      </div>
      <!-- Copyright -->
    </footer>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  </body>
</html>
