<?php
namespace App\MiddleWare;

use App\Component\Enum\Core\PointMap;
use App\Component\Core\ApiPlugin;
use App\Component\Core\App;
use App\Component\Enum\ErrorCode;
use App\Component\Exception\ApiException;
use Phalcon\Events\Event;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\MiddlewareInterface;

class AclMiddleware extends ApiPlugin implements MiddlewareInterface
{
    public function call(Micro $app)
    {
        // TODO: Implement call() method.
        return true;
    }

    /**
     * @param Event $event
     * @param App   $app
     *
     * @return bool
     * @throws ApiException
     */
    public function beforeExecuteRoute(Event $event, App $app)
    {
        $allowed     = false;
        $data        = $app->getMatchedEndpoint();
        $pointScopes = $data[PointMap::SCOPES] ?? [];
        if (empty($pointScopes)) { //如果point 没有配置scopes,则公开访问
            return true;
        }
        $scopes = $this->userService->getScopes();//获取用户角色的scopes,看是否和point的scopes匹配
        if (!empty($pointScopes) && isset($scopes)) {
            foreach ($scopes as $scope) {
                $allowed = in_array($scope, $pointScopes);
                if ($allowed) {
                    break;
                }
            }
        }

        if (!$allowed) {
            throw  new ApiException(ErrorCode::ACCESS_DENIED);
        }
        return true;
    }
}