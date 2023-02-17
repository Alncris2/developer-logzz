<?php

include_once('braip.php');

$braip = new Braip();

echo '<pre>';
print_r($braip);
// print_r(json_decode($braip->getVendas()));