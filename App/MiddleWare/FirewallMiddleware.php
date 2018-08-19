<?php
namespace App\MiddleWare;

use App\Component\Core\ApiPlugin;
use App\Component\Core\App;
use Phalcon\Events\Event;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\MiddlewareInterface;

class FirewallMiddleware extends ApiPlugin implements MiddlewareInterface
{
    /**
     * @param Micro $application
     *
     * @return bool
     */
    public function call(Micro $application)
    {
        return true;
    }

    public function beforeExecuteRoute(Event $event, App $app)
    {
//        $whitelist = [
//            '10.4.6.1',
//            '10.4.6.2',
//            '10.4.6.3',
//            '10.4.6.4',
//        ];
//        $ipAddress = $app->request->getClientAddress();
//
//        if (true !== array_key_exists($ipAddress, $whitelist)) {
//            $app->response->setStatusCode(401, 'Not Allowed');
//            $app->response->sendHeaders();
//            $message = "当前ip无法访问";
//            $app->response->setContent($message);
//            $app->response->send();
//            return false;
//        }
        return true;
    }
}