<?php
/**
 *  @author  Michel Dumont <michel.dumont.io>
 *  @version 1.0.0 [2020-04-10]
 *  @package mediaCenter 1.0.0
 */

@ini_set('display_errors', 'on');
@error_reporting(E_ALL | E_STRICT);

require 'src/autoload.php';
require 'src/router.php';

(new Mdg\Models\FileModel)->install();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    <title>mediaCenter</title>

    <link rel="stylesheet" href="./assets/css/font-awesome.min.css"/>
    <link rel="stylesheet" href="./assets/css/global.css"/>

    <script src="./assets/js/jquery-2.1.1.min.js"></script>
    <script src="./assets/js/underscore-min.js"></script>
    <script src="./assets/js/backbone-min.js"></script>
    <script src="./assets/js/app.search.js"></script>
    <script src="./assets/js/app.streams.js"></script>
    <script src="./assets/js/app.js"></script>
    <script src="./assets/js/tpl.js"></script>
</head>
<body>

    <header></header>
    <main></main>
    <footer>
        <!-- @TODO intégrer au fichier de configuration -->
        <a href="https://wwv.zone-annuaire.com" target="_blank">Zone annuaires <i class="fa fa-external-link"></i></a> -
        <a href="https://www.themoviedb.org?language=fr-FR" target="_blank">The movie DB <i class="fa fa-external-link"></i></a>
    </footer>
    <div id="modalWrap"></div>

    <script> app.initialize(); </script>
</body>
</html>
