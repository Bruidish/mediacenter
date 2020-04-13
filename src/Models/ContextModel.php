<?php
/**
 *  @author  Michel Dumont <michel.dumont.io>
 *  @version 1.0.0 [2020-04-10]
 *  @package mediaCenter 1.0.0
 */

namespace Mdg\Models;

class ContextModel
{
    public function __construct()
    {
        /** stocke les paramètres dans le fichier de configuration */
        foreach (json_decode(file_get_contents(realpath(__DIR__ . '/../../config/config.json'))) as $var => $value) {
            $this->$var = $value;
        }
    }

}
