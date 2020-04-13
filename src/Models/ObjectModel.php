<?php
/**
 *  @author  Michel Dumont <michel.dumont.io>
 *  @version 1.0.0 [2020-04-10]
 *  @package mediaCenter 1.0.0
 */

namespace Mdg\Models;

use Mdg\Controllers\DbController as DbController;
use Slim\Slim;

class ObjectModel
{
    const TYPE_INT = 1;

    const TYPE_BOOL = 2;

    const TYPE_STRING = 3;

    const TYPE_FLOAT = 4;

    const TYPE_DATE = 5;

    const TYPE_HTML = 6;

    const TYPE_NOTHING = 7;

    const TYPE_SQL = 8;

    /** @var string nom de la table dans la base de données */
    protected $table;

    /** @var string clé primaire */
    protected $primary;

    /** @var int */
    public $id;

    /** @var ContextModel */
    protected $context;

    /** @var array */
    public static $definition = [
        'table'   => '',
        'primary' => '',
        'fields'  => [],
    ];

    public function __construct($id = null)
    {
        $this->context = new ContextModel;
        $this->table   = static::$definition['table'];
        $this->primary = static::$definition['primary'];

        if ($id) {
            $thisData = (new DbController)->getRow("
                SELECT *
                FROM {$this->context->dbPrefix}{$this->table}
                WHERE {$this->primary}=\"{$id}\"
            ");

            if ($thisData) {
                foreach ($thisData as $key => $value) {
                    $this->$key = $value;
                }
            }
        }

        return $this;
    }

    /** enregistre les données transmises en rest
     *
     * @return object|bool
     */
    public function save()
    {
        $requestData = json_decode(Slim::getInstance()->request()->getBody());
        $dataToSave  = [];
        foreach (static::$definition['fields'] as $key => $params) {
            if (isset($requestData->$key)) {
                $dataToSave[$key] = (isset($requestData->$key) ? trim($requestData->$key) : $this->$key);

                if (isset($params['default']) && $params['default'] === 'NULL' && empty($dataToSave[$key])) {
                    $dataToSave[$key] = null;
                }
            }
        }

        if ($this->id) {
            $output = (new DbController)->update($this->table, $dataToSave, "{$this->primary}=\"{$this->id}\"");
        } else {
            $DbController = new DbController;
            $output       = $DbController->insert($this->table, $dataToSave);
            $this->id     = $DbController->lastInsertId();
        }

        return $output ? new static($this->id) : false;
    }

    /** Installe l'objet
     *
     * @return bool
     */
    public function install()
    {
        if ((new DbController)->getValue("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME=\"{$this->context->dbPrefix}{$this->table}\"")) {
            return true;
        }

        $sql = "CREATE TABLE IF NOT EXISTS `{$this->context->dbPrefix}{$this->table}` (";
        $sql .= "`{$this->primary}` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, ";

        foreach (static::$definition['fields'] as $column => $params) {
            if (!isset($params['default'])) {
                $params['default'] = 'NOT NULL';
            }

            switch ($params['type']) {
                case static::TYPE_INT:
                    $sql_column = "`{$column}` int(11) unsigned {$params['default']}";
                    break;
                case static::TYPE_BOOL:
                    $sql_column = "`{$column}` tinyint(1) unsigned {$params['default']}";
                    break;
                case static::TYPE_FLOAT:
                    $sql_column = "`{$column}` decimal(20,6) {$params['default']}";
                    break;
                case static::TYPE_SQL:
                case static::TYPE_HTML:
                    $sql_column = "`{$column}` text {$params['default']}";
                    break;
                case static::TYPE_STRING:
                    $sql_column = "`{$column}` varchar(255) {$params['default']}";
                    break;
                case static::TYPE_DATE:
                    $sql_column = "`{$column}` datetime {$params['default']}";
                    break;
            }

            $sql .= "{$sql_column},";
        }

        $sql .= " PRIMARY KEY (`{$this->primary}`) ) ENGINE={$this->context->dbEngine} DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;";

        return (new DbController)->execute($sql);
    }
}
