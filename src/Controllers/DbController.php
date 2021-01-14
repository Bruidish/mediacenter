<?php
/**
 *  @author  Michel Dumont <michel.dumont.io>
 *  @version 1.0.0 [2020-04-10]
 *  @package mediaCenter 1.0.0
 */

namespace Mdg\Controllers;

use Mdg\Models\ContextModel;
use \PDO;

class DbController
{
    /** @var object instance PDO */
    private $db;

    /** @var object instance de Mdg\Models\ContextModel */
    private $context;

    public function __construct()
    {
        $this->context = new ContextModel;

        $this->db = new PDO("mysql:host={$this->context->dbHost};dbname={$this->context->dbName}", $this->context->dbUser, $this->context->dbPassword, []);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $this;
    }

    /** éxécute une requête sql passée en argument
     *
     * @param string
     *
     * @return bool
     */
    public function execute($query)
    {
        return $this->db->query($query);
    }

    /** éxécute une requête sql passée en argument et retourne la première ligne
     *
     * @param string
     *
     * @return array
     */
    public function getRow($query)
    {
        return $this->db->query($query)->fetch(PDO::FETCH_ASSOC);
    }

    /** éxécute une requête sql passée en argument et retourne toutes les lignes
     *
     * @param string
     *
     * @return array
     */
    public function getRows($query)
    {
        return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    /** éxécute une requête sql passée en argument et retourne la valeur d'une colomne
     *
     * @param string
     *
     * @return array
     */
    public function getValue($query)
    {
        return $this->db->query($query)->fetchColumn();
    }

    /** Ajoute les datas en base
     *
     * @param string nom de la table
     * @param array [key => value, ...]
     *
     * @return bool
     */
    public function insert($table, $values)
    {
        if ($values) {
            $table = preg_match("/^{$this->context->dbPrefix}/", $table) ? $table : $this->context->dbPrefix . $table;
            $fieldsSql = '';
            $valuesSql = [];

            foreach ($values as $key => $value) {
                $fieldsSql .= ($fieldsSql != '' ? ',' : '') . "{$key} = :{$key}";
                $valuesSql[":{$key}"] = $value;
            }

            return (bool) ($this->db->prepare("INSERT IGNORE INTO `{$table}` SET {$fieldsSql}"))->execute($valuesSql);
        }

        return false;
    }

    /** Modifie les datas en base
     * @param string
     * @param array
     * @param string
     *
     * @return bool
     */
    public function update($table, $values, $where)
    {
        if ($values) {
            $table = preg_match("/^{$this->context->dbPrefix}/", $table) ? $table : $this->context->dbPrefix . $table;
            $fieldsSql = '';
            $valuesSql = [];

            foreach ($values as $key => $value) {
                $fieldsSql .= ($fieldsSql != '' ? ',' : '') . "{$key} = :{$key}";
                $valuesSql[":{$key}"] = $value;
            }

            return (bool) ($this->db->prepare("UPDATE `{$table}` SET {$fieldsSql} WHERE {$where}"))->execute($valuesSql);
        }

        return false;
    }

    /** Supprime les datas en base
     * @param string
     * @param string
     *
     * @return bool
     */
    public function delete($table, $where = null)
    {
        $table = preg_match("/^{$this->context->dbPrefix}/", $table) ? $table : $this->context->dbPrefix . $table;
        $where = $where ? " WHERE {$where}" : '';
        return (bool) ($this->db->prepare("DELETE FROM `{$table}`{$where}"))->execute();
    }

    /** Retourne l'id créer par la dernier inert commande
     *
     * @return int
     */
    public function lastInsertId()
    {
        return $this->db->lastInsertId();
    }
}
