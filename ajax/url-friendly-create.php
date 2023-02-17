<?php

    require (dirname(__FILE__) . '/../includes/classes/StrToURL.php');

    $str = addslashes($_GET['string']);
    $url_create = new StrToURL();
    
    $url_array = $url_create->urlCreate($str);

    $url = $url_array['url'];
    $unified = $url_array['unified'];

    $feedback = array('url' => $url, 'unified' => $unified);
    echo json_encode($feedback);
    exit;

?>