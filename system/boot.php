<?php

define('DS', DIRECTORY_SEPARATOR);

define('ROOT_PATH', realpath(dirname(__FILE__) . '/..'));
define('APPLICATION_PATH', ROOT_PATH . '/application');
define('TEMPLATES_PATH', ROOT_PATH . '/application/templates');
define('MODULES_PATH', ROOT_PATH . '/modules');
define('SYSTEM_PATH', ROOT_PATH . '/system');
define('VENDOR_PATH', ROOT_PATH . '/vendor');
define('MEDIA_PATH', ROOT_PATH . '/media');

# Config
require_once APPLICATION_PATH . DS . 'config/config.php';

# Autoloader
require_once SYSTEM_PATH . DS . 'autoloader.php';

# PHPTAL
require_once VENDOR_PATH . DS . 'phptal/PHPTAL.php';