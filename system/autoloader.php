<?php
/*
 * name: Autoloader
 */

class Autoloader
{
    public static function initialize($class)
    {
        $path = strtolower(str_replace('_', DS, $class)) . '.php';

        switch (true)
        {
            case file_exists(APPLICATION_PATH . DS . $path):
                require_once APPLICATION_PATH . DS . $path;
                return true;

            case file_exists(MODULES_PATH . DS . $path):
                require_once MODULES_PATH . DS . $path;
                return true;
                break;

            case file_exists(SYSTEM_PATH . DS . $path):
                require_once SYSTEM_PATH . DS . $path;
                return true;
        }

        exit (sprintf('%s not found.', $path));
    }
}

spl_autoload_register(array('Autoloader', 'initialize'));