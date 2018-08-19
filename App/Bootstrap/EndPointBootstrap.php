<?php
namespace App\Bootstrap;

use App\Component\Core\ApiCollection;
use App\Component\Core\App;
use App\Controller\AuthController;
use App\Controller\IndexController;
use App\Controller\OAuthController;
use App\Controller\Resource\UserResource;
use Phalcon\Config;
use Phalcon\Di\FactoryDefault as Di;

class EndPointBootstrap implements BootstrapInterface
{
    /**
     * @param App    $app
     * @param Di     $di
     * @param Config $config
     *
     * @throws \App\Component\Exception\ApiException
     * @throws \ReflectionException
     */
    public function run(App $app, Di $di, Config $config)
    {
        $app->mount(new ApiCollection(IndexController::class))
            ->mount(new ApiCollection(AuthController::class))
            ->mount(new ApiCollection(OAuthController::class))
            ->mount(new ApiCollection(UserResource::class));
    }
}