<?php

class Router
{
    private static $default_controller = 'homepage';
    private static $default_action = 'default';

    public function __construct($path)
    {
        $path = trim($path, DS);

        switch (true)
        {
            # FIXME
            case array_key_exists('test', array()):
                break;

            default:
                # Use defaults if no path is provided
                if (empty($path))
                {
                    $path = self::$default_controller . DS . self::$default_action;
                }

                $parts = explode(DS, $path);
                if (count($parts) > 1)
                {
                    $action = array_pop($parts);
                }
                elseif (count($parts) == 1)
                {
                    $action = 'default';
                }
                array_unshift($parts, 'controller');

                array_walk($parts, function (&$v)
                {
                    $v = ucfirst($v);
                    return true;
                });

                $controller = implode('_', $parts);

                # If there is no class, we add action as class name and try default action
                if (!class_exists($controller))
                {
                    $controller .= '_' . ucfirst($action);
                    $action = self::$default_action;
                    exit($controller . ' ' . $action);

                }


                $controller = new $controller($action);

                break;
        }
    }
}