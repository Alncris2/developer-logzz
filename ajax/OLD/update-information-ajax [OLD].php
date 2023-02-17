<?php

require dirname(__FILE__) . "/../includes/config.php";

session_name(SESSION_NAME);
session_start();

if (isset($_POST['action'])) {
  $user_id = $_SESSION['UserID'];

  //Realizar operação de mudança de celular
  if ($_POST['action'] == 'mudar-celular') {
    $whatsapp = addslashes($_POST['celular']);

    $stmt = $conn->prepare('UPDATE users SET user_phone = :user_phone WHERE user__id = :user__id');

    try {
      $stmt->execute(array('user_phone' => $whatsapp, 'user__id' => $user_id));

      $feedback = array('status' => 1, 'title' => "Sucesso", 'msg' => 'Número de telefone alterado.');
      echo json_encode($feedback);
      exit;
    } catch(PDOException $e) {
      $feedback = array('status' => 0, 'title' => "Erro", 'msg' => 'Algo de errado aconteceu, tente novamente.');
      echo json_encode($feedback);
      exit;
    }
  }

  //Realizar operação de mudança de senha
  if ($_POST['action'] == 'mudar-senha') {
    //Campos do formulário
    $senha_atual = sha1($_POST['senha-atual']);
    $nova_senha = sha1($_POST['nova-senha']);
    $nova_senha_conf = sha1($_POST['nova-senha-conf']);

    if($nova_senha != $nova_senha_conf) {
      $feedback = array('status' => 0, 'title' => "Erro", 'msg' => 'As senhas não coincidem.');
      echo json_encode($feedback);
      exit;
    }
    $stmt = $conn->prepare('SELECT users.user_password FROM users WHERE user__id = :user_id');
    $stmt->execute(array('user_id' => $user_id));
    
    if($stmt->rowCount() != 0) {
      while ($row = $stmt->fetch()) {
        $old_password = $row['user_password'];
      }
      if($old_password == $senha_atual) {

        if(!isset($_POST["verification-code"])) {

          $code = random_int(100000, 999999);
          
          $_SESSION["VerificationCode"] = $code;

          $feedback = array('status' => 2, 'title' => "Verifique seu Email", 'msg' => 'Enviamos um código de validação para seu e-mail, digite-o abaixo para alterar sua senha.', 'code' => $code, 'id' => $user_id);
          echo json_encode($feedback);
          exit;

        } else {

          if($_SESSION["VerificationCode"] != $_POST['verification-code']) {
            $feedback = array('status' => 0, 'title' => "Código Incorreto", 'msg' => 'O código inserido não é igual ao que enviamos.');
            echo json_encode($feedback);
            exit;
          }

        }
        $stmt = $conn->prepare('UPDATE users SET user_password = :user_password WHERE user__id = :user__id');
        $stmt->execute(array('user_password' => $nova_senha, 'user__id' => $user_id));

        $feedback = array('status' => 1, 'title' => "Feito!", 'msg' => 'Sua Senha Alterada.');
        echo json_encode($feedback);
        exit;
      }
      else {
        $feedback = array('status' => 0, 'title' => "Senha Incorreta", 'msg' => 'Confira se digitou corretamente a sua senha atual.');
        echo json_encode($feedback);
        exit;
      }
    }

  }  
}
?>