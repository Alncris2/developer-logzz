<?php
  require dirname(__FILE__) . "/../includes/config.php";

  if(isset($_POST['idSend'])){
    $unique = filter_var($_POST['idSend'], FILTER_VALIDATE_INT);

    $delete = $conn->prepare("DELETE FROM integration_notazz WHERE integration_id = ?");
    try{
      $delete->execute(array($unique)); 
    }catch (Exception $e){
      echo json_encode(array("type"=>"error","title"=>"Erro ao deletar"));
    }
  }

 
?> 