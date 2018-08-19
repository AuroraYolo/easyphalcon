<?php
namespace App\Console;

use Phalcon\Cli\Task;
use Phalcon\Config;
use Redis;

class Base extends Task
{

    /**
     * @var \Redis
     */
    protected static $redis = null;
    /**
     * @var Config
     */
    protected static $config = null;
    public function initialize()
    {

    }

    /**
     * 连接redis
     * @link http://redis.cn
     * @return Redis
     * @throws \Exception
     */
    public function redis()
    {
        if (is_null(self::$redis)) {
            self::$redis = new Redis();
            self::$redis->pconnect('127.0.0.1');
            return self::$redis;
        }
        try {
            if (self::$redis->ping() == '+PONG') {
                return self::$redis;
            } else {
                self::$redis = new Redis();
                self::$redis->pconnect('127.0.0.1');
                return self::$redis;
            }
        } catch (\Exception $ex) {
            throw new \Exception('链接缓存服务器失败！');
        }
    }

    /**
     *
     *
     * @return \Phalcon\Config
     */
    public function config(){
        if(empty(self::$config)){
            self::$config = $this->getDI()->getDefault()->get('config');
        }
        return self::$config;
    }

}