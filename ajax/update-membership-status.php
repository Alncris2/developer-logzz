<?php

    require_once(dirname(__FILE__) . '/../includes/config.php');

    /**
     * 
     * Aprove or Delete UNIQUE item.
     * 
     * **/
    if (isset($_GET['id'])){

        $membership_status = addslashes($_GET['status']);
        $memberships_hotcode = addslashes($_GET['id']);

        if ($membership_status == 1){

            $membership_status = 'ATIVA';
            $title = "Solicitação Aprovada!";
            $msg = "O afiliado será notificado.";

        } else {

            $membership_status = 'REMOVIDA';
            $title = "Solicitação Removida!";
            $msg = "O afiliado não será notificado.";

        }

        try {

            $stmt = $conn->prepare('UPDATE memberships SET membership_status = :membership_status WHERE memberships_hotcode = :memberships_hotcode');
            $stmt->execute(array('membership_status' => $membership_status, 'memberships_hotcode' => $memberships_hotcode));

            $url = SERVER_URI . "/produtos/solicitacoes/";

            $feedback = array('msg' => $msg, 'title' => $title, 'type' => 'success', 'url' => $url);
            echo json_encode($feedback);
            exit;

        } catch(PDOException $e) {

            # Armazena o feeback negativo na variável.
            $error= 'ERROR: ' . $e->getMessage();
            $feedback = array('title' => 'Erro Interno!', 'type' => 'warning', 'msg' => $error);
            exit;
        }

    /**
     * 
     * Aprove or Delete MULTIPLE itens.
     * 
     * **/
    } else if (isset($_GET['array'])) {

        $membership_status = addslashes($_GET['status']);
        $memberships_hotcodes = explode(",", $_GET['array']);

        if ($membership_status == 1){

            $membership_status = 'ATIVA';
            $title = "As Solicitações Foram Aprovadas!";
            $msg = "Os afiliados serão notificados.";

        } else {

            $membership_status = 'REMOVIDA';
            $title = "Solicitações Removidas!";
            $msg = "Os afiliados não serão notificados.";

        }

        foreach ($memberships_hotcodes as $membership_hotcode) :

            // if ($membership_hotcode == "checkAll"){
            //     continue;
            // }

            $stmt = $conn->prepare('UPDATE memberships SET membership_status = :membership_status WHERE memberships_hotcode = :memberships_hotcode');
            $stmt->execute(array('membership_status' => $membership_status, 'memberships_hotcode' => $membership_hotcode));

            $url = SERVER_URI . "/produtos/solicitacoes/";

        endforeach;

        $feedback = array('msg' => $msg, 'title' => $title, 'type' => 'success', 'url' => $url);
        echo json_encode($feedback);
        exit;


    }


?>