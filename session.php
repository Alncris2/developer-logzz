<?php
    require_once (dirname(__FILE__) . '/includes/config.php');
    session_name(SESSION_NAME);
    session_start();

    print_r($_SESSION);
?>