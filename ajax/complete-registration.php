<?php


require_once dirname(__FILE__) . "/../includes/config.php";
session_name(SESSION_NAME);
session_start();

if (isset($_POST['action']) && $_POST['action'] == 'complete-registration'){

$user__id = $_SESSION['UserID'];
$full_name 					= addslashes($_POST['nome-assinante']);
$email 						= addslashes($_POST['email-assinante']);
$whatsapp 					= addslashes($_POST['whatsapp-assinante']);
$razao_social 			    = addslashes($_POST['razao-social']);
$company_type 			    = addslashes($_POST['pessoa']);
$documento 			        = addslashes($_POST['documento']);



if ($company_type == 'fisica') {
    $company_name = $full_name;
} else if ($company_type == 'juridica') {
    $company_name = $razao_social;
}


$url = SERVER_URI . "/usuario/contrato/";

$stmt = $conn->prepare('UPDATE users SET full_name = :full_name, user_phone = :user_phone, company_name = :company_name, company_doc = :company_doc, company_type = :company_type WHERE user__id = :user__id');


try {

    $stmt->execute(array('full_name' => $full_name, 'user_phone' => $whatsapp, 'company_name' => $company_name, 'company_doc' => $documento, 'company_type' => $company_type, 'user__id' => $user__id));

    $feedback = array('status' => 1, 'url' => $url);
    echo json_encode($feedback);
    exit;

} catch(PDOException $e) {
    $error = 'ERROR: ' . $e->getMessage();
    $feedback = array('status' => 0, 'msg' => $error);
    echo json_encode($feedback);
    exit;
}

} 

else if (isset($_GET['aceito']) && $_GET['aceito'] == "on"){
    
    $set_user_terms_status = $conn->prepare('UPDATE users SET user_terms_accepted = :user_terms_accepted WHERE user__id = :user__id');

    $set_user_terms_status->execute(array('user_terms_accepted' => 'true', 'user__id' => $_SESSION['UserID']));

    $url = SERVER_URI . '/pedidos/dashboard/';
    
    $feedback = array('status' => 1, 'url' => $url);
    echo json_encode($feedback);
    exit;

}

else {
$feedback = array('status' => 0, 'msg' => 'NO ACTION!');
echo json_encode($feedback);
exit;

}
?>