<?php

class RandomStrGenerator {

    public function onlyNumbers($lenght)
    {
        if ($lenght > 59){
            $lenght = 59;
        }

        $chars = '012345678901234567890123456789012345678901234567890123456789';
        $only_nums_random_str = str_shuffle($chars);
        $only_nums_random_str = substr($only_nums_random_str, 4, $lenght);     

        return $only_nums_random_str;

    }

    public function onlyLetters($lenght)
    {
        if ($lenght > 55) {
            $lenght = 55;
        }

        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $only_lets_random_str = str_shuffle($chars);
        $only_lets_random_str = substr($only_lets_random_str, 4, $lenght);

        return $only_lets_random_str;
    }

    public function lettersAndNumbers($lenght)
    {
        if ($lenght > 59) {
            $lenght = 59;
        }

        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $lets_and_nums_random_str = str_shuffle($chars);
        $lets_and_nums_random_str = substr($lets_and_nums_random_str, 4, $lenght);

        return $lets_and_nums_random_str;
    }
}

?>