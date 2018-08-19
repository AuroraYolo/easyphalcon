<?php
namespace App\Bootstrap;

use App\Component\Core\App;
use App\Component\Enum\Services;
use App\MiddleWare\AclMiddleware;
use App\MiddleWare\AuthTokenMiddleware;
use App\MiddleWare\CORSMiddleware;
use App\MiddleWare\FirewallMiddleware;
use App\MiddleWare\FractalMiddleware;
use App\MiddleWare\NotFoundMiddleware;
use App\MiddleWare\OptionsResponseMiddleware;
use Phalcon\Config;
use Phalcon\Di\FactoryDefault as Di;
use Phalcon\Events\Manager;

class MiddleWareBootstrap implements BootstrapInterface
{

    /**
     * @param App    $app
     * @param Di     $di
     * @param Config $config
     */
    public function run(App $app, Di $di, Config $config)
    {
        /** @var $eventsManager Manager */
        $eventsManager = $app->getService(Services::EVENTS_MANAGER);
        $eventsManager->attach('micro', new AuthTokenMiddleware());
        $eventsManager->attach('micro', new AclMiddleware());
        $eventsManager->attach('micro', new NotFoundMiddleware());
        $eventsManager->attach('micro', new CORSMiddleware());
        $eventsManager->attach('micro', new FirewallMiddleware());
        $eventsManager->attach('micro', new FractalMiddleware());/**/
        $eventsManager->attach('micro', new OptionsResponseMiddleware());
        $app->setEventsManager($eventsManager);
    }
}