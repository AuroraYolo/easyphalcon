<?php
namespace App\Commons\Library;

use App\Component\Enum\Services;
use Phalcon\Config;
use Phalcon\Di;
use Phalcon\Logger\Adapter\File as LoggerAdapterFile;
use Phalcon\Logger\Formatter\Line as LineFormatter;

class Logger
{
    /**
     * @var $logger LoggerAdapterFile
     */
    private static $logger = null;
    /**
     * 存放日志的目录
     *
     * @var string $projectLogPath
     */
    private static $projectLogPath;

    /**
     * @param      $project
     * @param null $file
     *
     * @return null|LoggerAdapterFile
     * @throws \Exception
     */
    public static function logger($project, $file = null)
    {
        $config = (new Di())->getDefault()->getShared(Services::CONFIG);
        /**
         * @var Config $config
         */
        $dirs = $config->get('logs')->toArray();
        if (is_array($dirs) && isset($dirs[$project])) {
            self::$projectLogPath = $dirs[$project];
        } else {
            throw new \Exception("日志的配置文件不正确");
        }
        if (is_null($file)) {
            $file = date('Ymd') . ".log";
        }
        self::$logger = new LoggerAdapterFile(self::$projectLogPath . $file);
        self::$logger->setFormatter(new LineFormatter('[%date%][%type%] [%message%]', 'Y-m-d H:i:s'));
        return self::$logger;
    }

}