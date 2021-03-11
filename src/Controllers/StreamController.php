<?php
/**
 *  @author  Michel Dumont <michel.dumont.io>
 *  @version 1.0.0 [2020-04-10]
 *  @package mediaCenter 1.0.0
 */

namespace Mdg\Controllers;

use Exception;
use Mdg\Controllers\DbController;
use Mdg\Models\ContextModel;
use Mdg\Models\FileModel;

class StreamController
{
    public $streams = [];

    public function __construct()
    {
        $this->context = new ContextModel;
        $this->streams = json_decode(file_get_contents(realpath(__DIR__ . '/../../config/streams.json')));
    }

    /** Exécute une fonction de l'objet et retourne son résultat en JSON
     *
     * @param string
     *
     * @return string
     */
    public function outputJson($callback)
    {
        header('Content-Type: application/json');
        die(json_encode($this->$callback()));
    }

    /** Charge une vidéo dans le dossier vidéos
     *
     * @todo traitement des erreurs et fichiers non valides
     *
     * @var string $_FILES["file"]
     *
     * @return bool
     */
    public function postFiles()
    {
        return move_uploaded_file($_FILES["file"]["tmp_name"], "videos/{$_FILES["file"]["name"]}");
    }

    /** Supprime un fichier vidéo dans le dossier vidéos
     *
     * @var string $_POST['path']
     *
     * @return bool
     */
    public function deleteFile()
    {
        return unlink($_POST['path']);
    }

    /** Supprime un FileModel
     *
     * @var int $_POST['id']
     *
     * @return bool
     */
    public function deleteArchive()
    {
        return (new FileModel((int) $_POST['id']))->delete();
    }

    /** Encode un fichier au format mp4
     * @see http://pierrehirel.info/blog/?p=26
     * @todo desfois les fichiers sont trop compressés et desfois àa échoue
     *
     * @var string $_POST['path']
     *
     * @return bool
     */
    public function mpegEncode()
    {
        $output = true;
        $FileModel = $this->renameFile($_POST['path']);
        $fileExtension = substr($_POST['path'], -3);
        if ($FileModel->id && $FileModel->path) {
            $fileSize = filesize($FileModel->path);
            $encodedFilePath = str_replace($fileExtension, 'mp4', $FileModel->path);
            $encodingCompression = "-q:a 0 -q:v 0";

            if ($fileSize >= 1000000000) {
                return false;
            }

            try {
                $output &= ('' == shell_exec("/usr/local/bin/ffmpeg -i \"{$FileModel->path}\" {$encodingCompression} \"{$encodedFilePath}\" 2>&1"));
            } catch (Exception $e) {}
            if ($output) {
                return unlink($FileModel->path);
            }
        }
        return false;
    }

    /** Renomme le fichier vidéo à partir des paramètres enregistrés en base
     *
     * @var string $_POST['path']
     *
     * @return object
     */
    public function renameFile()
    {
        $filePath = $_POST['path'];
        $fileName = substr(basename($filePath), 0, -4);
        $fileHash = FileModel::getHash($fileName);
        $fileId = FileModel::getIdByHash($fileHash, $this->context);
        $FileModel = new FileModel($fileId);

        if (!$FileModel->id) {
            die(false);
        }

        $newName = FileModel::getFileName($FileModel);
        $newHash = FileModel::getHash($newName);
        $newPath = str_replace($fileName, $newName, $filePath);

        /** Renomme le fichier */
        $output = rename("{$filePath}", "{$newPath}");

        /** Met à jour le hash en base */
        if ($output) {
            $output &= (new DbController)->update(FileModel::$definition['table'], ['hash' => $newHash], "id={$FileModel->id}");
            $FileModel->path = $newPath;
            $FileModel->filename = substr(basename($newPath), 0, -4);
        }

        /** Met à jour le hash de l'image de couverture */
        if ($output && is_dir("images/{$fileHash}")) {
            $output &= rename("images/{$fileHash}", "images/{$newHash}");
            $output &= (new DbController)->update(FileModel::$definition['table'], ['image' => "images/$newHash/image.jpg"], "id={$FileModel->id}");
        }

        return $FileModel;
    }

    /** Retourne les vidéos des tous les streams paramétrés
     *
     * @return string
     */
    public function getFiles()
    {
        $output = [];
        foreach ($this->streams as $stream) {
            $this->_getFiles($stream->path, $output);
        }

        $this->_getArchives($output);

        return array_values($output);
    }

    /** Retourne les données de fichiers qui n'existent plus mais qui sont enregistrés en base
     *
     * @param array collection
     *
     * @return array
     */
    private function _getArchives(&$output)
    {
        $existingCollection = array_keys($output);
        $existingCollectionString = count($existingCollection) ? '"' . implode('","', $existingCollection) . '"' : '';
        $table = $this->context->dbPrefix . FileModel::$definition['table'];
        $archives = (new DbController)->getRows("SELECT * FROM {$table} WHERE hash NOT IN ({$existingCollectionString})");

        $output = array_merge($output, $archives);

        return $output;
    }

    /** Retourne tous les fichiers du dossier et des sous dossiers
     *
     * @param string
     * @param array collection
     *
     * @return array
     */
    private function _getFiles($path, &$output)
    {
        foreach (glob("{$path}*") as $entry) {
            if (is_file($entry) && preg_match('/(\.avi|\.mkv|\.mp4|\.flv)$/i', $entry)) {
                $fileName = substr(basename($entry), 0, -4);
                $fileHash = FileModel::getHash($fileName);
                $fileDatas = FileModel::getByHash($fileHash, $this->context);
                $fileDatas['hash'] = $fileHash;
                $fileDatas['path'] = $entry;
                $fileDatas['filename'] = $fileName;
                $fileDatas['filenameFormatted'] = FileModel::getFileName($fileDatas);
                $fileDatas['filesize'] = self::fileSizeConvert(filesize($entry));
                $fileDatas['extension'] = substr($entry, -3);
                if (!isset($fileDatas['title']) || empty($fileDatas['title'])) {
                    $cleanTitle = static::getDataByFilename($fileDatas['filename']);
                    $fileDatas['title'] = $cleanTitle[1];
                    if (isset($cleanTitle[2]) && (!isset($fileDatas['release_year']) || empty($fileDatas['release_year']))) {
                        $fileDatas['release_year'] = $cleanTitle[2];
                    }

                }
                $output[$fileHash] = $fileDatas;
            } else if (is_dir($entry)) {
                $this->_getFiles("{$entry}/", $output);
            }
        }

        return $output;
    }

    /** Retourne tous les fichiers du dossier et des sous dossiers en conservant l'arborescence
     *
     * @param string
     *
     * @return array
     */
    private function _getRecursiveFiles($path)
    {
        $output = [];
        foreach (glob("{$path}*") as $entry) {
            if (is_file($entry)) {
                $output[] = [
                    'name' => basename($entry),
                ];
            } else {
                $output[] = [
                    'name' => basename($entry),
                    'children' => $this->_getRecursiveFiles("{$entry}/"),
                ];
            }
        }

        return $output;
    }

    /** Récupère les données à partir du nom de fichier pour en extraire les datas
     *
     * @param string
     *
     * @return array
     */
    public static function getDataByFilename($title)
    {
        $output = [];
        preg_match('/^([a-z \.-_&]*)[\.-][ ]?([0-9]{4})/', $title, $output);

        if (!$output) {
            $output = [
                0 => trim($title),
                1 => trim($title),
                2 => '',
            ];
        }

        $output[1] = trim(str_replace(['.', '_'], ' ', $output[1]));

        return $output;
    }

    /** retourne le poids d'un fichier
     *
     * @param int
     *
     * @return string
     */
    public static function fileSizeConvert($bytes)
    {
        $bytes = floatval($bytes);
        $arBytes = [
            0 => ["UNIT" => "TB", "VALUE" => pow(1024, 4)],
            1 => ["UNIT" => "GB", "VALUE" => pow(1024, 3)],
            2 => ["UNIT" => "MB", "VALUE" => pow(1024, 2)],
            3 => ["UNIT" => "KB", "VALUE" => 1024],
            4 => ["UNIT" => "B", "VALUE" => 1],
        ];

        foreach ($arBytes as $arItem) {
            if ($bytes >= $arItem["VALUE"]) {
                $result = $bytes / $arItem["VALUE"];
                $result = str_replace(".", ",", strval(round($result, 2))) . " " . $arItem["UNIT"];
                break;
            }
        }
        return $result;
    }
}
