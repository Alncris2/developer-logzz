<?php
require 'Compress.php';

$img = Compress::shrink($_FILES["file"]["tmp_name"], 'img/', 'webp');

if ($img->error) {
    echo "Ocorreu um erro: ";
    var_dump($img->error);
} else {
    echo "Nome do arquivo: " . $img->image_name . "<br>";
    echo "Tamanho original: " . $img->original_size . "<br>";
    echo "Novo tamanho: " . $img->new_size . "<br>";
    echo "Redução: " . $img->reduction_of . "<br>";
}
