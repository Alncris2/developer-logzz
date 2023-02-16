<?php

    require (dirname(__FILE__)) . "/../includes/config.php";
    session_name(SESSION_NAME);
    session_start();

    $query = $_GET['query'];
    $user__id = $_SESSION['UserID'];

    $get_affiliate_list = $conn->prepare('SELECT full_name, user__id, membership_affiliate_id FROM memberships INNER JOIN users ON membership_affiliate_id = user__id WHERE membership_producer_id = :user__id AND full_name LIKE :query GROUP BY membership_affiliate_id ORDER BY full_name ASC');
    $get_affiliate_list->bindParam(':user__id', $user__id, PDO::PARAM_INT);
    $get_affiliate_list->bindParam(':query', $query, PDO::PARAM_STR);
    $get_affiliate_list->execute();

    $options =  '<option id="all-affiliates-option" value="">Todos</option>';

    var_dump($affiliate_list);
    
    while ($affiliate_list = $get_affiliate_list->fetch()) {

        $affiliate_name = $affiliate_list['full_name'];
        $afilliate_id = $affiliate_list['user__id'];

        if (strlen($affiliate_name) > 10 && preg_match("/ /",  $affiliate_name)) {
            $affiliate_name = explode(" ",  $affiliate_name);
            if (strlen(@$affiliate_name[2]) > 4) {
                $affiliate_name = @$affiliate_name[0] . " " . @$affiliate_name[1] . " " . @$affiliate_name[2];
            } else {
                $affiliate_name = @$affiliate_name[0] . " " . @$affiliate_name[1] . " " . @$affiliate_name[2] . " " . @$affiliate_name[3];
            }
        } else {
            $affiliate_name =  $affiliate_name;
        }
        
        $options .= '<option value="' .  $afilliate_id . '">' . $affiliate_name . '</option>';
    }

    echo $options;

?>
