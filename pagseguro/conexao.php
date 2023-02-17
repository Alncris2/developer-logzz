<?php

define('HOST', 'localhost');
define('USER', 'hgin3317_dellivv');
define('PASS', 'dellivvery191187');
define('DBNAME', 'hgin3317_dellivvery');

$conn = new PDO('mysql:host=' . HOST . ';dbname=' . DBNAME . ';', USER, PASS);

