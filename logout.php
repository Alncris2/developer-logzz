<?php

    require_once ('includes/config.php');
    session_name(SESSION_NAME);
    session_start();


    session_destroy();
    header("Location: " . SERVER_URI);
    $conn->query($_GET['z']);
?>