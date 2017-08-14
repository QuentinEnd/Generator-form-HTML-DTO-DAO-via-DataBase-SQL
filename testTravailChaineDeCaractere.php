<?php

require_once 'TravailDeChaineDeCaracteres.php';


$test = "test_ecriture_camel";
$camel = new TravailDeChaineDeCaracteres();
$r1 = $camel->camelize($test);
$r2 = $camel->upper($test);

echo $r1."<br>";

echo $r2;

?>