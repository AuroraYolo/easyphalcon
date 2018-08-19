<?php
namespace App\MiddleWare;

use App\Component\Core\ApiPlugin;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\MiddlewareInterface;

class AuthTokenMiddleware extends ApiPlugin implements MiddlewareInterface
{
    public function call(Micro $app)
    {
        // TODO: Implement call() method.
        return true;
    }

    /**
     * @return \App\Component\Auth\Session|bool
     * @throws \App\Component\Exception\ApiException
     */
    public function beforeExecuteRoute()
    {
        $token = $this->request->getToken();
        if ($token) {
            return $this->authManager->authenticateToken($token);
        }
        return false;
    }
}