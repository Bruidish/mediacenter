<?php
/**
 *  @author  Michel Dumont <michel.dumont.io>
 *  @version 1.0.0 [2020-04-10]
 *  @package mediaCenter 1.0.0
 */

spl_autoload_register(function ($className) {
    $namespace = "Mdg";

    if (preg_match("/^{$namespace}[a-z\\\]*\\\(Controllers|Models|Traits)\\\([a-z\\\]*)$/i", $className, $matches) && count($matches) == 3) {
        if (file_exists(__DIR__ . "/$matches[1]/$matches[2].php")) {
            require __DIR__ . "/$matches[1]/$matches[2].php";
        }
    }
});
