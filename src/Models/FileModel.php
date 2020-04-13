<?php
/**
 *  @author  Michel Dumont <michel.dumont.io>
 *  @version 1.0.0 [2020-04-10]
 *  @package mediaCenter 1.0.0
 */

namespace Mdg\Models;

use Mdg\Controllers\DbController;

class FileModel extends ObjectModel
{
    /** @var string nom du fichier encodé en md5 */
    public $hash;

    /** @var string */
    public $title;

    /** @var string */
    public $subtitle;

    /** @var string */
    public $description;

    /** @var string image encodée en base64 */
    public $image;

    /** @var string année de sortie */
    public $release_year;

    /** @var int note perso sur 6 */
    public $rate;

    /** @var array */
    public static $definition = [
        'table'   => 'file',
        'primary' => 'id',
        'fields'  => [
            'hash'         => ['type' => self::TYPE_STRING],
            'title'        => ['type' => self::TYPE_STRING],
            'subtitle'     => ['type' => self::TYPE_STRING],
            'description'  => ['type' => self::TYPE_HTML],
            'image'        => ['type' => self::TYPE_SQL],
            'release_year' => ['type' => self::TYPE_STRING],
            'rate'         => ['type' => self::TYPE_FLOAT, 'default' => 'NULL'],
        ],
    ];

    public function save()
    {
        $output = parent::save();

        /** Enregistrement de l'image */
        if ($output !== false && preg_match('/^http/', $output->image)) {
            try {
                $data = file_get_contents($output->image);
                if (!is_dir(__DIR__ . "/../../images/{$output->hash}")) {
                    mkdir(__DIR__ . "/../../images/{$output->hash}");
                }
                if (file_put_contents(__DIR__ . "/../../images/{$output->hash}/image.jpg", $data)) {
                    $output->image = "images/{$output->hash}/image.jpg";
                    (new DbController)->update($this->table, ['image' => $output->image], "{$this->primary}=\"{$this->id}\"");
                }
            } catch (Exception $e) {}
        }

        return $output;
    }

    /** Retourne les données sur un fichier à partir d'un hash du nom du fichier
     *
     * @param string
     * @param object
     *
     * @return array
     */
    public static function getByHash($fileHash, $context)
    {
        $table = $context->dbPrefix . self::$definition['table'];
        return (new DbController)->getRow("SELECT * FROM {$table} WHERE hash=\"{$fileHash}\"");
    }

    /** Retourne le nom structuré pour les fichiers
     *
     * @param object FileModel
     *
     * @return string
     */
    public static function getFileName(FileModel $FileModel)
    {
        return $FileModel->title . ($FileModel->release_year ? " - {$FileModel->release_year}" : '');
    }

    /** retourne un hash à partir du nom du fichier
     *
     * @param string filename sans extenstion
     *
     * @return string hash
     */
    public static function getHash($fileName)
    {
        return md5(strtolower($fileName));
    }
}
