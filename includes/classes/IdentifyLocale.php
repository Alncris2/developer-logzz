<?php

require dirname(__FILE__) . "/../config.php";

// if (!(isset($_GET['locale']))){
//     exit;
// }


$locale = $_GET['locale'];

function tirarAcentos($string){
    return preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/"),explode(" ","a A e E i I o O u U n N"),$string);
}

$stmt = $conn->prepare('SELECT COUNT(*) FROM locales WHERE locale_name = :locale');
$stmt->execute(array('locale' => $locale));

$row = $stmt->fetch();
$in_stock = $row['COUNT(*)'];

$locale = strtoupper(tirarAcentos($locale));

$feedback = array('in_stock' => $in_stock, 'locale_id' => $locale);
echo json_encode($feedback);

?>