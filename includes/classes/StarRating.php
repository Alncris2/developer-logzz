<?php

require_once (dirname(__FILE__) . '/../../includes/config.php');

class StarRating {


    /** 
     * 
     * Função que cria a lista de classes 
     * que serão colocadas no HTML (markup)
     * das estrelinhas.
     * 
     * A sequência de IFs cria o array $stars com
     * base no valor da variável $rate, recebida
     * como parâmetro pela função.
     * 
     * */

    public function markupFromRate($rate)
    {
        if ($rate < 1){
            $stars = array('fas fa-star-half-alt', 'far fa-star', 'far fa-star', 'far fa-star', 'far fa-star');
        } else if ($rate < 1.5){
            $stars = array('fas fa-star', 'far fa-star', 'far fa-star', 'far fa-star', 'far fa-star');
        } else if ($rate < 2){
            $stars = array('fas fa-star', 'fas fa-star-half-alt', 'far fa-star', 'far fa-star', 'far fa-star');
        } else if ($rate < 2.5){
            $stars = array('fas fa-star', 'fas fa-star', 'far fa-star', 'far fa-star', 'far fa-star');
        } else if ($rate < 3){
            $stars = array('fas fa-star', 'fas fa-star', 'fas fa-star-half-alt', 'far fa-star', 'far fa-star');
        } else if ($rate < 3.5){
            $stars = array('fas fa-star', 'fas fa-star', 'fas fa-star', 'far fa-star', 'far fa-star');
        } else if ($rate < 4){
            $stars = array('fas fa-star', 'fas fa-star', 'fas fa-star', 'fas fa-star-half-alt', 'far fa-star');
        } else if ($rate < 4.5){
            $stars = array('fas fa-star', 'fas fa-star', 'fas fa-star', 'fas fa-star', 'far fa-star');
        } else if ($rate < 5){
            $stars = array('fas fa-star', 'fas fa-star', 'fas fa-star', 'fas fa-star', 'fas fa-star-half-alt');
        } else if ($rate < 6){
            $stars = array('fas fa-star', 'fas fa-star', 'fas fa-star', 'fas fa-star', 'fas fa-star');
        } else {
            $stars = array('far fa-star', 'far fa-star', 'far fa-star', 'far fa-star', 'far fa-star');
        }

        return $stars;
    }
    
    public function rateValueGenerate($rate)
    {
    }

}
?>