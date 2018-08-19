<?php
namespace App\Bootstrap;

use App\Commons\Library\Logger;
use App\Component\Auth\Account\EmailAccountType;
use App\Component\Auth\Account\UsernameAccountType;
use App\Component\Auth\Manager as AuthManager;
use App\Component\Auth\TokenParsers\JwtTokenParser;
use App\Component\Core\App;
use App\Component\Enum\Services;
use App\Fractal\CustomSerializer;
use App\Component\User\Service as UserService;
use Phalcon\Cache\Backend\Libmemcached;
use Phalcon\Config;
use Phalcon\Crypt;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Db\Profiler;
use Phalcon\Di\FactoryDefault as Di;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Mvc\Model\Manager as ModelManager;
use Phalcon\Mvc\Model\MetaData\Files as MetaDataFile;
use Phalcon\Mvc\Url;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Simple as SimpleView;
use Redis;
use Phalcon\Cache\Backend\Memcache;
use Phalcon\Cache\Frontend\Data as FrontData;
use League\Fractal\Manager as FractalManager;

class MicroServiceBootstrap implements BootstrapInterface
{
    public function run(App $app, Di $di, Config $config)
    {
        /**
         * @var $config \Phalcon\Config
         */
        $di->setShared(Services::CONFIG, function () use ($config)
        {
            return $config;
        });
        $di->setShared(Services::SIMPLE_VIEW, function () use ($config)
        {
            $view = new SimpleView();
            $view->setViewsDir($config->application->viewsDir);
            $view->registerEngines(
                [
                    ".phtml" => "Phalcon\\Mvc\\View\\Engine\\Php",
                    '.html'  => 'Phalcon\Mvc\View\Engine\Php',
                    ".volt"  => "Phalcon\\Mvc\\View\\Engine\\Volt",
                ]
            );
            return $view;
        });
        $di->set(Services::VIEW, function () use ($config)
        {
            $view = new View();
            $view->setViewsDir($config->application->viewsDir);
            $view->registerEngines(
                [
                    ".phtml" => "Phalcon\\Mvc\\View\\Engine\\Php",
                    '.html'  => 'Phalcon\Mvc\View\Engine\Php',
                    ".volt"  => "Phalcon\\Mvc\\View\\Engine\\Volt",
                ]
            );
            $view->disableLevel([
                View::LEVEL_BEFORE_TEMPLATE => true,
                View::LEVEL_AFTER_TEMPLATE  => true,
                //View::LEVEL_LAYOUT => true,
                View::LEVEL_MAIN_LAYOUT     => true,
                View::LEVEL_ACTION_VIEW     => true,
            ]);
            return $view;
        }, true);
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
        $di->set(Services::URL, function () use ($config)
        {
            $url = new Url();
            $url->setBasePath('');
            $url->setBaseUri('');
            $url->setStaticBaseUri('');
            return $url;
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
        $di->setShared(Services::TOKEN_PARSER, function () use ($di, $config)
        {
            return new JwtTokenParser($config->get('authentication')->secret, JwtTokenParser::ALGORITHM_HS256);
        });
        $di->setShared(Services::AUTH_MANAGER, function () use ($di, $config)
        {
            $authManager = new AuthManager($config->get('authentication')->expirationTime);
            $authManager->registerAccountType(EmailAccountType::NAME, new EmailAccountType());
            $authManager->registerAccountType(UsernameAccountType::name, new UsernameAccountType());
            return $authManager;
        });
        $di->setShared(Services::USER_SERVICE, new UserService());
        $di->set(Services::MODELS_METADATA, function () use ($config)
        {
            return new MetaDataFile([
                'metaDataDir' => APP_PATH . '/Cache/Metadata/'
            ]);
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
    }
}