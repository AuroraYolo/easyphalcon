<?php
namespace App\Controller;

use App\Component\Enum\ErrorCode;
use App\Component\Exception\ApiException;
use App\Models\User;

/**
 * Class OAuthController
 * @package App\Controller
 * @group(path='/oauth',name='user')
 */
class OAuthController extends BaseController
{
    /**
     * @point(path='/token',method='post')
     * @throws ApiException
     */
    public function postToken()
    {
        $username = $this->request->getUsername();
        $password = $this->request->getPassword();
        if (!$username || !$password) {
            throw new ApiException(ErrorCode::POST_DATA_NOT_PROVIDED, "Basic Auth:{username,password}");
        }
        $user = new User();
        var_dump($user->gets());
    }
}