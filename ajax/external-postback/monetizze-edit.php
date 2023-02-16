<?php 

    require dirname(__FILE__) . "/../../includes/config.php";
    
    session_name(SESSION_NAME);
    session_start();
    
    if (!(isset($_POST))) exit;
    
    $new_name = filter_var($_POST['integration-name'], FILTER_SANITIZE_STRING);
    $integration_id = filter_var($_POST['integration_id'], FILTER_SANITIZE_NUMBER_INT);
    $integration_key = $_POST['integration-unique-key'];
    $sql = "UPDATE integrations SET integration_name = :integration_name, integration_keys = :integration_keys WHERE integration_id = :integration_id";
    $stmt = $conn->prepare($sql);
    $isSuccess = $stmt->execute([
        'integration_name' => $new_name,
        'integration_keys' => $integration_key,
        'integration_id' => $integration_id
    ]);
    
    if($isSuccess){
        $_SESSION['editSucess'] = true;
        header('Location: '. SERVER_URI . "/integracoes/postback/monetizze/?e=".$integration_id );
        echo json_encode(['status' => 'ok']);
        return;
    }
    
    header('Location: '. SERVER_URI . "/integracoes/postback/monetizze/?e=".$integration_id );
    $_SESSION['editSucess'] = false;
    echo json_encode(['status' => 'bad']);
    