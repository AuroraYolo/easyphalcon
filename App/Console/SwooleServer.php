<?php
namespace App\Console;

use App\Bootstrap\ConsoleServiceBootstrap as Bootstrap;
use Phalcon\Config;

set_time_limit(0);

define('APP_PATH', __DIR__);
include_once dirname(__DIR__) . '/Bootstrap/ConsoleServiceBootstrap.php';
$envConfig = new Config(parse_ini_file(dirname(dirname(APP_PATH)) . '/.env', true));
define('ENVIRONMENT', $envConfig['ENVIRONMENT']);
$bootstrap = new Bootstrap($envConfig);
$bootstrap->run($argv, 'Server');