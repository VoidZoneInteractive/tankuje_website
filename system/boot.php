<?php

define('DS', DIRECTORY_SEPARATOR);

define('ROOT_PATH', realpath(dirname(__FILE__) . '/..'));
define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
define('TEMPLATES_PATH', realpath(dirname(__FILE__) . '/../application/templates'));
define('MODULES_PATH', realpath(dirname(__FILE__) . '/../modules'));
define('SYSTEM_PATH', realpath(dirname(__FILE__) . '/../system'));
define('VENDOR_PATH', realpath(dirname(__FILE__) . '/../vendor'));

# Config
require_once APPLICATION_PATH . '/config/config.php';

# Autoloader
require_once SYSTEM_PATH . DS . 'autoloader.php';

# PHPTAL
require_once VENDOR_PATH . DS . 'phptal/PHPTAL.php';