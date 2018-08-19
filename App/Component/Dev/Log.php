<?php
namespace App\Component\Dev;

use App\Component\Enum\Services;
use Phalcon\Di;

class Log
{
    public static $logger = null;

    /**
     * @return \Phalcon\Logger\AdapterInterface
     */
    public static function logger()
    {
        if (self::$logger == null) {
            self::$logger = (new Di())->getDefault()->get(Services::LOG);
        }
        return self::$logger;
    }
}