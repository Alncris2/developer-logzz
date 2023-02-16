<?php

require 'vendor/autoload.php';

use Intervention\Image\ImageManagerStatic as Image;

class Compress
{


    static function shrink($file, $path, $format = 'jpg')
    {
        $image_name = Compress::makeImageName() . '.' . $format;

        $full_path = $path . $image_name;
        $img = Image::make($file);
        $original_size = $img->filesize();
        if ($img->width() > 1920) {
            $img->resize(1920, null, function ($constraint) {
                $constraint->aspectRatio();
            });
        }
        $saved = $img->save($full_path, 90, $format);
        $new_size = ($saved->filesize());

        $output = (object) [
            'error' => $saved ? false : $saved,
            'image_name' => $image_name,
            'new_size' => number_format(($new_size / 1024), 2) . "kb",
            'original_size' => number_format(($original_size / 1024), 2, ',', '.') . "kb",
            'reduction_of' => number_format(((($original_size - $new_size) / $original_size) * 100), 2, ',', '.') . "%"  
        ];
        return $output;
    }

    static function makeImageName()
    {
        //gera uma string unica para ser usada como nome de imagem e evitar problemas de carregamento de cache
        $letters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        return str_replace(".", "", uniqid(rand(), true) . substr(str_shuffle($letters), 0, 4)) . date('YmdHis');
    }
}
