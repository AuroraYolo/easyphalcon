<?php
namespace EasyPhalcon;

use App\Bootstrap\BootStrap;
use App\Bootstrap\EndPointBootstrap;
use App\Bootstrap\MicroServiceBootstrap;
use App\Bootstrap\MiddleWareBootstrap;
use App\Component\Core\ApiFactory;
use App\Component\Core\App;
use App\Component\Enum\Services;
use App\Component\Http\Response;
use Phalcon\Config;
use Phalcon\Config\Adapter\Ini;
use Phalcon\Loader;

date_default_timezone_set('Etc/GMT-8');

//定义常量
define('DS', DIRECTORY_SEPARATOR);
define('BASE_PATH', realpath(dirname(__FILE__) . '/../'));
define('APP_PATH', BASE_PATH . '/APP');

//检查是否开启phalcon扩展
if (!extension_loaded('phalcon')) {
    exit("Please install phalcon extension. See https://phalconphp.com/zh/ \n");
}

//检查配置文件
if (!file_exists(BASE_PATH . '/.env')) {
    exit("Please check the configuration file \n");
}

try {
    $envPath = BASE_PATH . '/.env';
    if (!is_readable($envPath)) {
        throw new \Exception('Unable to read env from ' . $envPath);
    }
    $envConfig = new Ini($envPath);
    define('ENVIRONMENT', $envConfig['ENVIRONMENT']);

    $config     = null;
    $configPath = APP_PATH . '/Commons/Config/Base.php';
    if (!is_readable($configPath)) {
        throw new \Exception('Unable to read config from ' . $configPath);
    }
    $currentConfig = new Config(include_once $configPath);
    $config        = $currentConfig->merge($envConfig);
    if (defined('ENVIRONMENT')) {
        switch ((ENVIRONMENT)) {
            case 'development':
                error_reporting(E_ALL);
                ini_set('display_errors', 'On');
                break;
            case  'testing':
                error_reporting(E_ALL);
                ini_set('display_errors', 'On');
                break;
            case 'production':
            default:
                error_reporting(0);
                ini_set('display_errors', 'Off');
                break;
        }
    }
    //注册命名空间和目录

    $loader = new Loader();
    $loader->registerNamespaces([
        'App' => APP_PATH
    ]);
    $loader->registerDirs([
        'bootstrapDir'  => $config->application->bootstrapDir,
        'commonsDir'    => $config->application->commonsDir,
        'controllerDir' => $config->application->controllerDir,
        'componentDir'  => $config->application->componentDir,
        'configDir'     => $config->application->configDir,
        'fractalDir'    => $config->application->fractalDir,
        'helperDir'     => $config->application->helperDir,
        'modelsDir'     => $config->application->modelsDir,
        'viewsDir'      => $config->application->viewsDir,
        'logsDir'       => $config->application->logsDir,
    ]);
    $loader->registerFiles([$config->application->vendorAutoLoaderFile]);
    $loader->register();

    $di = new ApiFactory();

    $app = new App($di);

    $bootstrap = new BootStrap(
        new MicroServiceBootstrap(),
        new MiddleWareBootstrap(),
        new EndPointBootstrap()
    );
    /**
     *运行服务
     */
    $bootstrap->run($app, $di, $config);
    /* 捕获请求*/
    $app->handle();
    /**
     * @var Response $response
     */
    $response      = $app->di->getShared(Services::RESPONSE);
    $returnedValue = $app->getReturnedValue();
    if ($returnedValue !== null) {
        if (is_string($returnedValue)) {
            $response->setContent($returnedValue);
        } else {
            $response->setJsonContent($returnedValue);
        }
    }
} catch (\Throwable $throwable) {
    // Handle exceptions
    $di       = $app->di ?? new ApiFactory();
    $response = $di->getShared(Services::RESPONSE);
    if (!$response || !$response instanceof Response) {
        $response = new Response();
    }
    $debugMode = ENVIRONMENT == 'development' ? true : false;
    #$debugMode = false;
    $response->setErrorContent($throwable, $debugMode);
}
finally {
    /** @var $response Response */
    if (!$response->isSent()) {
        $response->send();
    }
}






