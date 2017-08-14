<?php

class TravailDeChaineDeCaracteres {

    // Méthode afin de transformer l'écriture de style SNAKE en écriture CAMELCASE
    public function camelize($str) {
        return lcfirst(strtr(ucwords(strtr($str, ['_' => ' '])), [' ' => '']));
    }
    
    // Méthode afin de mettre en majuscule la 1ère lettre d'un mot
    public function majPremiereLettreMots($str) {
        return strtr(ucwords(strtr($str, ['_' => ' '])), [' ' => '']);
    }
    
    // Méthode afin de mettre en majuscule un mot complet
    public function upper($str) {
        return strtoupper(str_replace("_", " ", $str));
    }

}

?>