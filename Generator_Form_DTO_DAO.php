<?php
/*
  Generator-form-HTML-DTO-DAO-via-DataBase-SQL.php
 */
session_start();

// Au préalable, déclaration des différentes variables utilisées
$listesBDs = "";
$listeTables = "";
$nomBD = "";
$nomTable = "";
$lsEntetes = "";
$lsContenu = "";
$lsContenuMethodes = "";
$lsContenu2 = "";
$lsContenu3 = "";
$lsContenu4 = "";
$lsContenu5 = "";
$lsContenu6 = "";
$lsAffiche = "";
$lsMessage = "";

// Fichiers nécessaires au fonctionnement du générateur de formulaire
require_once 'Connexion.php';
require_once 'Metabase.class.php';
require_once 'TravailDeChaineDeCaracteres.php';

// Méthode pour se connecter à la base de données
$lcnx = Connexion::seConnecter("connexion.ini");

// Début de la transaction --> beginTransaction()
// Car PDO::ATTR_AUTOCOMMIT dans le fichier de connexion
Connexion::initialiserTransaction($lcnx);

$tBDs = Metabase::getBDsFromServeur($lcnx);
foreach ($tBDs as $bd) {
    $listesBDs .= "<option>$bd</option>";
}

// Validation de la transaction --> commit() 
Connexion::validerTransaction($lcnx);


/*
 * AFFICHAGE LISTE DES TABLES D'UNE BD
 */
$btValiderBD = filter_input(INPUT_GET, "btValiderBD");
if ($btValiderBD != null) {
    $nomBD = filter_input(INPUT_GET, "listesBDs");
    $_SESSION["bd"] = $nomBD;
}

if (isSet($_SESSION["bd"])) {
    $nomBD = $_SESSION["bd"];
    $tTables = Metabase::getTablesFromBD($lcnx, $nomBD);
    foreach ($tTables as $table) {
        $listeTables .= "<option>$table</option>";
    }
}

/*
 * AFFICHAGE FINAL ...
 */
$btValiderTout = filter_input(INPUT_GET, "btValiderTout");

if ($btValiderTout != null) {
    $nomTable = filter_input(INPUT_GET, "listeTables");
    $lsSQL = "SELECT * FROM " . $nomBD . "." . $nomTable;

    $rbSortie = filter_input(INPUT_GET, "rbSortie");

    if ($rbSortie != null) {
        try {
            $lrs = $lcnx->query($lsSQL);
            $lrs->setFetchMode(PDO::FETCH_ASSOC);
            $tData = $lrs->fetchAll();

            /*
              MODE FETCHALL
             */
            /*
              La première ligne
              http://php.net/manual/fr/function.each.php
              each() retourne la paire clé/valeur courante du tableau array et avance le pointeur de tableau.
             */
            $t = each($tData);
            foreach ($t[1] as $key => $value) {
                $lsEntetes .= "$key;";
            }
            $lsEntetes = substr($lsEntetes, 0, -1);


            /*
              Fermeture du curseur
             */
            $lrs->closeCursor();

            // Transformation des entêtes (chaine de caractères) en un tableau
            $taEntetes = explode(";", $lsEntetes);


//--------------------------------------------------------------------------------------------------------------------

            /*
             * SI FORMULAIRE HTML Coché
             */
            if ($rbSortie == "form") {


                // On écrit dans $lsContenu le formulaire
                $lsContenu .= "<form action='' method='' >\n";
                $lsContenu .= "<fieldset>\n";

                // Appel de la méthode afin de mettre en majuscule le nom de la table 
                $lsMajPremLetMots = new TravailDeChaineDeCaracteres();
                $lsContenu .= "<legend>" . $lsMajPremLetMots->majPremiereLettreMots($nomTable) . "</legend>\n";

                // Boucle afin de créer les entêtes et les inputs dynamiquement en fonction de la table sélectionnée
                for ($i = 0; $i < count($taEntetes); $i++) {
                    // Appel de la méthode afin de mettre en majuscule la légende du formulaire
                    $lsSnake = new TravailDeChaineDeCaracteres();
                    $lsContenu .= "<label>" . $lsSnake->majPremiereLettreMots($taEntetes[$i]) . " :" . "</label>\n<br>";
                    // Appel de la méthode afin d'écrire de façon caméliser le nom de l'input
                    $lsContenu .= "<input type='text' name='" . $lsSnake->camelize($taEntetes[$i]) . " :" . "'>\n<br>";
                }// Fin de la boucle
                // Fin du formulaire avec le bouton valider et les derniers chevrons
                $lsContenu .= "<br><input type='submit' name='btValider' value='valider' >\n";
                $lsContenu .= "</fieldset>\n";
                $lsContenu .= "</form>";

                /**
                 * Ceci est totalement optionnel. 
                 * Permet d'afficher un aperçu du rendu du code HTML. Il ne restera plus qu'à mettre un peu de CSS pour
                 * créer le formulaire selon ses envies.
                 */
                $lsAffiche = $lsContenu;

                /*
                 *  Utilisation de (&lt;) et (&gt;) pour remplacer respectivement les chevrons "<" et ">"
                 *  afin d'obtenir le code lui même et non le résultat final
                 *  Donc utilisation de code ASCII pour ce faire
                 */
                $lsContenu = str_replace("<", "&lt;", $lsContenu);
                $lsContenu = str_replace(">", "&gt;", $lsContenu);

                // Pour finir, utilisation de NL2BR pour remplacer les \n par des <br>
                $lsContenu = nl2br($lsContenu);
            }


//--------------------------------------------------------------------------------------------------------------------               

            /*
             * SI DTO Coché
             */
            if ($rbSortie == "dto") {

                // On écrit dans $lsContenu la page php du DTO en mode texte
                $lsContenu .= "<?php \n\n";
                // Préparation de la méthode afin de pouvoir mettre en MAJ ce dont j'ai besoin
                $lsMajPremier = new TravailDeChaineDeCaracteres();
                $lsContenu .= "// ---" . $lsMajPremier->majPremiereLettreMots($nomTable) . ".php \n\n";
                $lsContenu .= "class " . $lsMajPremier->majPremiereLettreMots($nomTable) . " { \n\n";

                $lsContenu .= "// --- Propriétés \n\n";

                // Boucle sur le tableau d'entêtes
                for ($i = 0; $i < count($taEntetes); $i++) {
                    // Préparation de la méthode afin de pouvoir caméliser ou mettre en maj ce dont j'ai besoin
                    $lsSnake = new TravailDeChaineDeCaracteres();
                    $lsContenu .= "private &#36;" . $lsSnake->camelize($taEntetes[$i]) . ";\n";

                    /*
                     * $lsContenuMethodes nous permet de stocker les différentes méthodes dont ont a besoin.
                     * C'est à dire les "public function getters and setters".
                     */
                    $lsContenuMethodes .= "public function set" . $lsSnake->majPremiereLettreMots($taEntetes[$i]) . "(&#36;" . $lsSnake->camelize($taEntetes[$i]) . ") { \n";
                    $lsContenuMethodes .= "&#36;this->" . $lsSnake->camelize($taEntetes[$i]) . " = &#36;" . $lsSnake->camelize($taEntetes[$i]) . "; \n}\n\n";
                    $lsContenuMethodes .= "public function get" . $lsSnake->majPremiereLettreMots($taEntetes[$i]) . "(&#36;" . $lsSnake->camelize($taEntetes[$i]) . ") { \n";
                    $lsContenuMethodes .= "return &#36;this->" . $lsSnake->camelize($taEntetes[$i]) . " = &#36;" . $lsSnake->camelize($taEntetes[$i]) . "; \n}\n\n";
                }// Fin de la boucle

                $lsContenu .= "\n// --- Méthodes \n\n";

                $lsContenu .= $lsContenuMethodes;

                $lsContenu .= "\n}";
                $lsContenu .= "\n\n?>";


                /*
                 *  Utilisation de (&lt;) et (&gt;) pour remplacer respectivement les chevrons "<" et ">"
                 *  afin d'obtenir le code lui même et non le résultat final
                 *  Donc utilisation de code ASCII pour ce faire
                 */
                $lsContenu = str_replace("<", "&lt;", $lsContenu);
                $lsContenu = str_replace(">", "&gt;", $lsContenu);
                // Pour finir, utilisation de NL2BR pour remplacer les \n par des <br>
                $lsContenu = nl2br($lsContenu);
            }


//--------------------------------------------------------------------------------------------------------------------               

            /*
             *  SI DAO
             */
            if ($rbSortie == "dao") {

                // Préparation de la méthode afin de pouvoir caméliser ce dont on a besoin
                $lsCaractereCamelize = new TravailDeChaineDeCaracteres();

                /*
                 * Récupération sous forme de tableau la liste des colonnes formant la PK d'une table
                 * Récupération sous forme de tableau la liste des colonnes d'une table
                 */
                $primaryKey = Metabase::getColumnsNamesPKFromTable($lcnx, $nomBD, $nomTable);
                $nomColonnes = Metabase::getColumnsNamesFromTable($lcnx, $nomBD, $nomTable);
                for ($i = 0; $i < count($nomColonnes); $i++) {
                    // pour le tableauValeurs de INSERT
                    $lsContenu2 .= "&#36;" . $lsCaractereCamelize->camelize($nomTable) . "->get" . $lsCaractereCamelize->camelize($nomColonnes[$i]) . "(),";
                    // pour l'ordre sql de INSERT
                    $lsContenu3 .= $nomColonnes[$i] . ",";
                    // pour l'ordre sql de INSERT
                    $lsContenu4 .= "?,";

                    // pour le tableauValeurs de UPDATE et son ordre SQL sans la PrimaryKey qui elle, ira dans le WHERE
                    if ($nomColonnes !== $primaryKey[0]) {
                        $lsContenu5 .= "&#36;" . $lsCaractereCamelize->camelize($nomTable) . "->get" . $lsCaractereCamelize->camelize($nomColonnes[$i]) . "(),";
                        $lsContenu6 .= $nomColonnes[$i] . "= ?,";
                    }// fin if
                }// fin boucle

                /*
                 * On enlève les derniers caractères indésirables avec substr afin d'avoir 
                 * des concaténations propres
                 */
                $lsContenu2 = substr($lsContenu2, 0, -1);
                $lsContenu3 = substr($lsContenu3, 0, -1);
                $lsContenu4 = substr($lsContenu4, 0, -1);
                $lsContenu6 = substr($lsContenu6, 0, -1);

                /*
                 * On ajoute à tableauValeurs de UPTADE la PrimaryKey à la fin de l'ordre SQL
                 */
                $lsContenu5 .= "&#36;" . $lsCaractereCamelize->camelize($nomTable) . "->get" . $lsCaractereCamelize->camelize($primaryKey[0]) . "()";

                // On écrit dans $lsContenu la page php du DAO en mode texte
                $lsContenu .= "<?php \n\n";
                $lsContenu .= "require_once '" . $lsCaractereCamelize->camelize($nomTable) . ".php';\n\n";
                $lsContenu .= "Class " . $lsCaractereCamelize->camelize($nomTable) . "DAO {\n\n";

                /*
                 * PREMIERE PARTIE CONCERNANT L'ORDRE INSERT
                 */
                $lsContenu .= "//=================================================================================\n\n";
                $lsContenu .= "/**\n*INSERT\n* @param PDO &#36;pcnx\n* @param type $nomTable\n* @return string\n*/\n\n";
                $lsContenu .= "public static function insert(PDO &#36;pcnx, &#36;$nomTable) {\n\n";
                $lsContenu .= "&#36;tableauValeurs = array($lsContenu2);\n\n";
                $lsContenu .= "&#36;lsSQL = 'INSERT INTO $nomTable($lsContenu3) VALUES($lsContenu4)'\n\n";
                $lsContenu .= "try {\n\n";
                $lsContenu .= "&#36;lcmd = &#36;pcnx->prepare(&#36;lsSQL);\n";
                $lsContenu .= "&#36;lcmd->execute(&#36;tValeurs);\n";
                $lsContenu .= "&#36;lsMessage = &#36;lcmd->rowcount();\n";
                $lsContenu .= "} catch (PDOException &#36;e) {\n";
                $lsContenu .= "&#36;lsMessage = 'Echec de l'exécution : ' . htmlentities(&#36;e->getMessage());\n";
                $lsContenu .= "}\n";
                $lsContenu .= "return &#36;lsMessage;\n";
                $lsContenu .= "}\n";

                /*
                 * DEUXIEME PARTIE CONCERNANT L'ORDRE UPDATE
                 */
                $lsContenu .= "\n\n//=================================================================================\n\n";
                $lsContenu .= "/**\n*UPDATE\n* @param PDO &#36;pcnx\n* @param type $nomTable\n* @return string\n*/\n\n";
                $lsContenu .= "public static function insert(PDO &#36;pcnx, &#36;$nomTable) {\n\n";
                $lsContenu .= "&#36;tValeurs = array($lsContenu5);\n\n";
                $lsContenu .= "&#36;lsSQL = 'UPDATE $nomTable SET $lsContenu6 WHERE $primaryKey[0]= ?'\n\n";
                $lsContenu .= "try {\n\n";
                $lsContenu .= "&#36;lcmd = &#36;pcnx->prepare(&#36;lsSQL);\n";
                $lsContenu .= "&#36;lcmd->execute(&#36;tValeurs);\n";
                $lsContenu .= "&#36;lsMessage = &#36;lcmd->rowcount();\n";
                $lsContenu .= "} catch (PDOException &#36;e) {\n";
                $lsContenu .= "&#36;lsMessage = 'Echec de l'exécution : ' . htmlentities(&#36;e->getMessage());\n";
                $lsContenu .= "}\n";
                $lsContenu .= "return &#36;lsMessage;\n";
                $lsContenu .= "}\n";

                /*
                 * TROISIEME PARTIE CONCERNANT L'ORDRE DELETE
                 */
                $lsContenu .= "\n\n//=================================================================================\n\n";
                $lsContenu .= "/**\n*DELETE\n* @param PDO &#36;pcnx\n* @param type $nomTable\n* @return string\n*/\n\n";
                $lsContenu .= "public static function insert(PDO &#36;pcnx, &#36;$nomTable) {\n\n";
                $lsContenu .= "&#36;tValeurs = array(" . $lsCaractereCamelize->camelize($nomTable) . "->get" . $lsCaractereCamelize->camelize($primaryKey[0]) . "());\n\n";
                $lsContenu .= "&#36;lsSQL = 'DELETE FROM $nomTable WHERE $primaryKey[0]= ?'\n\n";
                $lsContenu .= "try {\n\n";
                $lsContenu .= "&#36;lcmd = &#36;pcnx->prepare(&#36;lsSQL);\n";
                $lsContenu .= "&#36;lcmd->execute(&#36;tValeurs);\n";
                $lsContenu .= "&#36;lsMessage = &#36;lcmd->rowcount();\n";
                $lsContenu .= "} catch (PDOException &#36;e) {\n";
                $lsContenu .= "&#36;lsMessage = 'Echec de l'exécution : ' . htmlentities(&#36;e->getMessage());\n";
                $lsContenu .= "}\n";
                $lsContenu .= "return &#36;lsMessage;\n";
                $lsContenu .= "}\n\n";
                // fin de la Class DAO sans les différents ordres SELECT existants pour le moment (à venir probablement plus tard)
                $lsContenu .= "}///Fin Class DAO\n"; 
                
                /*
                 *  Utilisation de (&lt;) et (&gt;) pour remplacer respectivement les chevrons "<" et ">"
                 *  afin d'obtenir le code lui même et non le résultat final
                 *  Donc utilisation de code ASCII pour ce faire
                 */
                $lsContenu = str_replace("<", "&lt;", $lsContenu);
                $lsContenu = str_replace(">", "&gt;", $lsContenu);

                // Pour finir, utilisation de NL2BR pour remplacer les \n par des <br>
                $lsContenu = nl2br($lsContenu);
            }


//--------------------------------------------------------------------------------------------------------------------               


            /*
              Fermeture du curseur
             */
            $lrs->closeCursor();
            
            $lsMessage = "Ok, c'est fini ! Tu peux aller faire la sieste !!!";

            /*
             * REINITIALISATION
             */
            unset($_SESSION["bd"]);
            $nomBD = "";
        } catch (PDOException $e) {
            $lsMessage = "Echec de l'exécution : " . $e->getMessage();
        }
    } else {
        $lsMessage = "Il faut sélectionner un type de sortie !!!";
    }
}

// Méthode pour se déconnecter à la base de données
Connexion::seDeconnecter($lcnx);
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <style>
            *{margin: 0; padding: 0;}
            html{
                width: 100%;
            }
            article{
                margin: 0.5em; 
                border: 1px red solid; 
                float: left; 
                width: 30%; 
                height: 500px;
            }
            aside{
                margin: 0.5em; 
                border: 1px black solid; 
                float: left; 
                width: 60%; 
                height: 500px;
                overflow: auto;
            }
            input, select, fieldset{
                margin: 0.5em;
                padding: 0.5em;
            }
            footer{
                margin: 0.5em;
                padding: 0.5em;
                clear: both;
            }
        </style>
        <title>Generator HTML_DTO_DAO</title>
    </head>

    <body>
        <article>
            <form action="" method="GET">
                <select name="listesBDs" size="5">
                    <?php echo $listesBDs; ?>
                </select>
                <br>
                <input type="submit" value="Valider BD" name="btValiderBD" />
            </form>

            <form action="" method="GET">
                <select name="listeTables" size="5">
                    <?php echo $listeTables; ?>
                </select>

                <fieldset>
                    <legend>Sorties</legend>
                    <input type="radio" name="rbSortie" id="rbForm" value="form" />
                    <label for="rbForm">FORMULAIRE HTML</label><br>
                    <input type="radio" name="rbSortie" id="rbDTO" value="dto" />
                    <label for="rbDTO">DTO</label><br>
                    <input type="radio" name="rbSortie" id="rbDAO" value="dao" />
                    <label for="rbDAO">DAO</label>
                </fieldset>

                <input type="submit" value="Valider Tout" name="btValiderTout" />
            </form>
        </article>

        <aside>
            <code>
                <?php echo $lsContenu; ?>
            </code>

            <br>
            <br>
            <hr>
            <hr>

            <p>
                <?php echo $lsAffiche; ?>
            </p>
        </aside>

        <footer>
            <label>
                <?php echo $lsMessage; ?>               

            </label>
        </footer>

    </body>
</html>

