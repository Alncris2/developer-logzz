<?php

require_once(dirname(__FILE__) . '/../config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_GET['context'])) || !(isset($_GET['userid']))) {
    exit;
}

$user__id = addslashes($_GET['userid']);

$set_timeout = $conn->prepare('SET SESSION interactive_timeout = 28800');
$set_timeout->execute();



// ! PORQUE subscription_current_plan tem um prefixo DELETED ???
// Porque nem deveria existir, e ninguém sabe quem criou (já perguntei no grupo). A coluna correta é "user_plan"
$get_user_details = $conn->prepare('SELECT full_name, email, user_plan FROM subscriptions INNER JOIN users ON users.user__id = subscriptions.user__id WHERE users.user__id = :user__id');
$get_user_details->execute(array('user__id' => $user__id));

if ($get_user_details->rowCount() != 0) {
    while ($row = $get_user_details->fetch()) {
        $user_name              = $row['full_name'];
        $user_email             = $row['email'];
        $user_plan              = userPlanString($row['user_plan']);
        @$user_pass              = $_SESSION['LastSubscriberPassword'];
    }
} else {
    echo "Erro!";
    exit;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require(dirname(__FILE__) . "/../../vendor/autoload.php");

$mail = new PHPMailer();
$Username = "contato@logzz.com.br";
$titulo = "Logzz";
try {
    $mail->SMTPDebug = false;
    $mail->IsHTML(true);
    $mail->isSMTP();
    $mail->Host = "smtp.gmail.com"; // Servidor SMTP
    $mail->SMTPSecure = "tls"; // conexão segura com TLS
    $mail->Port = 587;
    $mail->SMTPAuth = true; // Caso o servidor SMTP precise de autenticação
    $mail->Username = $Username; // SMTP username
    $mail->Password = "jzrvwmattkbufczq"; // SMTP password
    $mail->CharSet = 'UTF-8';

    #Define qual email será enviado.
    switch ($_GET['context']) {
        case 'first_access':
            $mail->From = $Username;
            $mail->FromName = $titulo;
            $mail->addAddress($user_email);
            $mail->Sender = $Username;
            $mail->Subject = 'Parabéns! Você agora é Assinante ' . $user_plan . ' Logzz';

            require('PHPMailer/mails/first-access-mail.php');

            $mail->Body = $first_access_mail_body;
            $url = SERVER_URI . "/";
            break;

        case 'reset_password':
            $mail->From =$Username;
            $mail->FromName = $titulo;
            $mail->addAddress($user_email);
            $mail->Subject = 'Redefinição de Senha';

            require('PHPMailer/mails/reset-password-mail.php');

            $mail->Body = $reset_password_mail_body;
            $url = SERVER_URI . '/esqueci-minha-senha?sucesso';
            break;

        case 'daily_resume':
            $mail->From = $Username;
            $mail->FromName = $titulo;
            $mail->addAddress($user_email);
            $mail->Subject = 'Resumo diário da sua conta';
            $mail->Body = '<h1>Mensagem enviada via PHPMailer</h1>';
            break;

        case 'change_password':
            $mail->From = $Username;
            $mail->FromName = $titulo;
            $mail->addAddress($user_email);
            $mail->Subject = 'Alteração de Senha';
            $validation_code = $_SESSION['VerificationCode'];

            require('PHPMailer/mails/password-verification-mail.php');

            $mail->Body = $password_verification_mail_body;
            $url = SERVER_URI . "/perfil/informacoes/";
            break;

        case 'change_phone':
            $mail->From = $Username;
            $mail->FromName = $titulo;
            $mail->addAddress($user_email);
            $mail->Subject = 'Alteração de Contato';
            $validation_code = $_SESSION['PhoneVerificationCode'];

            require('PHPMailer/mails/phone-verification-mail.php');

            $mail->Body = $phone_verification_mail_body;
            $url = SERVER_URI . "/perfil/informacoes/";
            break;
    }





    if ($mail->Send()) {
        #Configurar LOG de emails disparados.
        #echo 'Enviado com sucesso !';
        header('Location: ' . $url);
    } else {
        #Configurar LOG de emails com erros.
         echo 'Erro ao enviar Email:' . $mail->ErrorInfo;
    }
} catch (Exception $e) {
    echo "MException: {$mail->ErrorInfo}";
}
