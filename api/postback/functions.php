<?php 

    function formatPhone($number_phone){
        $format = substr($number_phone, 0, 2);
        $format_2 = substr($number_phone, 3, 5);
        $format_3 = substr($number_phone, 4, 4);
        return "(".$format.") " . $format_2 . "-". $format_3;
    }