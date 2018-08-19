<?php
namespace App\MiddleWare;

use Phalcon\Events\Event;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\MiddlewareInterface;

class OptionsResponseMiddleware implements MiddlewareInterface
{
    public function call(Micro $app)
    {
        return true;
    }

    public function beforeHandleRoute(Event $event, Micro $app)
    {
        if ($app->request->isOptions()) {
            $app->response->setJsonContent([
                'result' => 'OK',
            ]);
            return false;
        }
    }
}