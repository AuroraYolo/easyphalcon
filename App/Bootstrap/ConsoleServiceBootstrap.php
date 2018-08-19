<?php
namespace App\Bootstrap;

use Phalcon\Cli\Console;
use Phalcon\Config;
use Phalcon\Di\FactoryDefault\Cli;
use App\Commons\Library\Logger;
use App\Fractal\CustomSerializer;
use Phalcon\Cache\Backend\Libmemcached;
use Phalcon\Crypt;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Db\Profiler;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Loader;
use Phalcon\Mvc\Model\Manager as ModelManager;
use Redis;
use Phalcon\Cache\Backend\Memcache;
use Phalcon\Cache\Frontend\Data as FrontData;
use League\Fractal\Manager as FractalManager;
use App\Component\Enum\Services;

class ConsoleServiceBootstrap
{
    /**
     * @var Console
     */
    private $console;

    public function __construct(Config $envConfig)
    {
        $di        = new Cli();
        $commons   = dirname(APP_PATH) . '/Commons';
        $component = dirname(APP_PATH) . '/Component';
        $tasks     = APP_PATH . DIRECTORY_SEPARATOR . 'Tasks';
        $config    = (new Config(include $commons . '/Config/Base.php'))->merge($envConfig);
        $loader    = new Loader();
        $loader->registerNamespaces([
            'App' => dirname(APP_PATH)
        ]);
        $loader->registerDirs([
            $commons,
            $tasks,
            $component
        ]);
        $loader->registerFiles([$config->application->vendorAutoLoaderFile]);
        $loader->register();
        /**
         * @var $config \Phalcon\Config
         */
        $di->setShared(Services::CONFIG, function () use ($config)
        {
            return $config;
        });

        $di->setShared(Services::DB, function () use ($config)
        {
            $db = new DbAdapter([
                'host'     => $config->dbMaster->host,
                'username' => $config->dbMaster->username,
                'password' => $config->dbMaster->password,
                'dbname'   => $config->dbMaster->dbname,
                'charset'  => 'utf8mb4',
                'options'  => [
                    \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
                    \PDO::ATTR_EMULATE_PREPARES   => false,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
                ]
            ]);

            if (defined('ENVIRONMENT') && ENVIRONMENT == 'development') {
                $eventsManager = new EventsManager();
                $profiler      = new Profiler();
                $eventsManager->attach('db', function ($event, $connection) use ($profiler, $config)
                {
                    if ($event->getType() == 'beforeQuery') {
                        $profiler->startProfile($connection->getSQLStatement());
                    }
                    if ($event->getType() == 'afterQuery') {
                        $profiler->stopProfile();
                        $profile = $profiler->getLastProfile();
                        //获取sql对象
                        $sql = $profile->getSqlStatement();
                        //获取查询参数
                        $params = $profile->getSqlVariables();
                        $params = json_encode($params);
                        //获取执行时间
                        $executeTime = $profile->getTotalElapsedSeconds();
                        $profiler->reset();
                        $maxExecuteTime = isset($config->db->max_execute_time) ?? 0;
                        $scale          = intval($config->db->scale);
                        if (bccomp($executeTime, $maxExecuteTime, $scale) != -1) {
                            Logger::logger('db')->debug("$sql $params $executeTime");
                        }
                    }
                });
                $db->setEventsManager($eventsManager);
                return $db;
            }
        });
        $di->setShared('dbSlave', function () use ($config)
        {
            $db = new DbAdapter([
                'host'     => $config->dbSlave->host,
                'username' => $config->dbSlave->username,
                'password' => $config->dbSlave->password,
                'dbname'   => $config->dbSlave->dbname,
                'charset'  => 'utf8mb4',
                'options'  => [
                    \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
                    \PDO::ATTR_EMULATE_PREPARES   => false,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
                ]
            ]);

            if (defined('ENVIRONMENT') && ENVIRONMENT == 'development') {
                $eventsManager = new EventsManager();
                $profiler      = new Profiler();
                $eventsManager->attach('db', function ($event, $connection) use ($profiler, $config)
                {
                    if ($event->getType() == 'beforeQuery') {
                        $profiler->startProfile($connection->getSQLStatement());
                    }
                    if ($event->getType() == 'afterQuery') {
                        $profiler->stopProfile();
                        $profile = $profiler->getLastProfile();
                        //获取sql对象
                        $sql = $profile->getSqlStatement();
                        //获取查询参数
                        $params = $profile->getSqlVariables();
                        $params = json_encode($params);
                        //获取执行时间
                        $executeTime = $profile->getTotalElapsedSeconds();
                        $profiler->reset();
                        $maxExecuteTime = isset($config->db->max_execute_time) ?? 0;
                        $scale          = intval($config->db->scale);
                        if (bccomp($executeTime, $maxExecuteTime, $scale) != -1) {
                            Logger::logger('db')->debug("$sql $params $executeTime");
                        }
                    }
                });
                $db->setEventsManager($eventsManager);
                return $db;
            }
        });
        $di->setShared(Services::REDIS_CACHE, function () use ($config)
        {
            $redis = new Redis();
            $redis->connect($config->redis->host, $config->redis->port);
            $redis->setOption(Redis::OPT_PREFIX, $config->redis->prefix);
            return $redis;
        });
        $di->setShared(Services::MEMCACHE_CACHE, function () use ($config)
        {
            $frontCache = new FrontData([
                [
                    "lifetime" => 172800,
                ]
            ]);
            if (extension_loaded('memcached')) {
                $memcache = new Libmemcached($frontCache, [
                    "servers" => [
                        [
                            "host"   => $config->memcache->host,
                            "port"   => $config->memcache->port,
                            "weight" => 1,
                        ],
                    ],
                    "client"  => [
                        \Memcached::OPT_HASH       => \Memcached::HASH_MD5,
                        \Memcached::OPT_PREFIX_KEY => "prefix.",
                    ]
                ]);
            } else {
                $memcache = new Memcache($frontCache, [
                    "host"       => $config->memcache->host,
                    "port"       => $config->memcache->port,
                    "persistent" => false,
                ]);
            }
            return $memcache;
        });
        $di->set(Services::CRYPT, function () use ($config)
        {
            $crypt = new Crypt();
            $crypt->setCipher('AES-256-CBC');
            $crypt->setKey(md5(Services::CRYPT_KEY));
            return $crypt;
        });
        $di->setShared(Services::FRACTAL_MANAGER, function () use ($config)
        {
            $fractal = new FractalManager();
            $fractal->setSerializer(new CustomSerializer());
            return $fractal;
        });
        $di->set(Services::EVENTS_MANAGER, function ()
        {
            $eventsManager = new EventsManager();
            return $eventsManager;
        }, true);
        $di->set(Services::MODELS_MANAGER, function () use ($di)
        {
            $modelManager = new ModelManager();
            $modelManager->setEventsManager($di->get(Services::EVENTS_MANAGER));
            return $modelManager;
        }, true);
        $this->console = new Console();
        $this->console->setDI($di);
    }

    public function run($argv, $module = 'Tasks')
    {
        $arguments = [];
        foreach ($argv as $k => $arg) {
            if ($k == 1) {
                $arguments['task'] = 'App\\Console\\' . ucfirst($module) . '\\' . ucfirst(str_replace('/', '\\', $arg));
            } else {
                if ($k == 2) {
                    $arguments['action'] = $arg;
                } else {
                    if ($k >= 3) {
                        $arguments['params'][] = $arg;
                    }
                }
            }
        }

        define('CURRENT_TASK', (isset($argv[1]) ? $argv[1] : null));
        define('CURRENT_ACTION', (isset($argv[2]) ? $argv[2] : null));

        try {
            // 处理参数
            $this->console->handle($arguments);
        } catch (\Phalcon\Exception $e) {
            echo $e->getMessage();
            exit(255);
        } catch (\Throwable $throwable) {
            echo $throwable->getMessage();
        } catch (\Exception $ex) {
            echo $ex->getMessage();
        }
    }
}