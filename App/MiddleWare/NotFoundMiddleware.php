<?php
namespace App\MiddleWare;

use App\Component\Core\ApiPlugin;
use App\Component\Enum\ErrorCode;
use App\Component\Exception\ApiException;
use Phalcon\Events\Event;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\MiddlewareInterface;

class NotFoundMiddleware extends ApiPlugin implements MiddlewareInterface
{
    /**
     * @param Micro $app
     *
     * @return bool
     */
    public function call(Micro $app)
    {
        // TODO: Implement call() method.
        return true;
    }

    /**
     * @param Event $event
     * @param Micro $app
     *
     * @throws ApiException
     */
    public function beforeNotFound(Event $event, Micro $app)
    {
        throw new ApiException(ErrorCode::GENERAL_NOT_FOUND);
        //        $this->response->redirect('/error404');
        //        $this->response->send();
        //        return false;
    }
}