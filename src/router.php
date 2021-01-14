<?php
/**
 *  @author  Michel Dumont <michel.dumont.io>
 *  @version 1.0.0 [2020-04-10]
 *  @package mediaCenter 1.0.0
 */

/** Dependances */
require __DIR__ . '/../assets/php/Slim/Slim.php';
\Slim\Slim::registerAutoloader();

/** Initialise l'application */
$_app = new \Slim\Slim();

/** Affichage de la page Index */
$_app->get("/", function () {});

/** JSON : retourne les fichiers des différents stream
 *
 * @return array
 */
$_app->get("/files", function () {(new Mdg\Controllers\StreamController)->outputJson('getFiles');});

/** JSON : enregistre un fichier sur le serveur
 *
 * @var array $_FILES['file']
 *
 * @return bool
 */
$_app->post("/file/upload", function () {(new Mdg\Controllers\StreamController)->outputJson('postFiles');});

/** JSON : Renomme un fichier à partir de la nomenclature définie
 *
 * @var string $_POST['path']
 *
 * @return boolean
 */
$_app->post("/file/rename", function () {(new Mdg\Controllers\StreamController)->outputJson('renameFile');});

/** JSON : Supprime un fichier sur le serveur
 *
 * @var string $_POST['path']
 *
 * @return boolean
 */
$_app->post("/file/delete", function () {(new Mdg\Controllers\StreamController)->outputJson('deleteFile');});

/** JSON : Supprime un fichier sur le serveur
 *
 * @var string $_POST['path']
 *
 * @return boolean
 */
$_app->post("/file/encode", function () {(new Mdg\Controllers\StreamController)->outputJson('mpegEncode');});

/** JSON : Enregistre / met à jour un Mdg\Models\FileModel
 *
 * @param int id de l'objet
 * @var $_POST[arguments, ...]
 *
 * @return object|bool
 */
$_app->map("/file(/:id)", function ($id = null) {die(json_encode((new Mdg\Models\FileModel($id))->save()));})->via('POST', 'PUT');

/** JSON : Supprime un fichier sur le serveur
 *
 * @var string $_POST['path']
 *
 * @return boolean
 */
$_app->post("/archive/delete", function () {(new Mdg\Controllers\StreamController)->outputJson('deleteArchive');});

/** Exécute l'application */
$_app->run();
