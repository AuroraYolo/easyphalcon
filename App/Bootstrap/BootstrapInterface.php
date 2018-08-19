<?php
namespace App\Bootstrap;

use App\Component\Core\App;
use Phalcon\Config;
use Phalcon\Di\FactoryDefault AS Di;

interface BootstrapInterface
{
    public function run(App $app, Di $di, Config $config);
}
