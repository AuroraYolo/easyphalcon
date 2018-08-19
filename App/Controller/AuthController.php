<?php

namespace App\Controller;

use App\Component\Auth\Account\EmailAccountType;
use App\Component\Auth\Manager;
use App\Component\Enum\ErrorCode;
use App\Component\Exception\ApiException;
use App\Helper\RegVerify;

/**
 * Class AuthController
 * @package App\controller
 * @group(path="/auth",name=auth)
 */
class AuthController extends BaseController
{
    use RegVerify;

    /**
     * @point(path="/authenticate",method="post")
     * @throws
     */
    public function authenticate()
    {
        // 获取请求参数
        $username = $this->request->getUsername();
        $password = $this->request->getPassword();
        if (!$username || !$password) {
            throw new ApiException(ErrorCode::POST_DATA_NOT_PROVIDED, "Basic Auth:{username,password}");
        }
        //校验认证类型
        $identityType = $this->identityTypeCheck($username);
        //调取登录服务
        $session = $this->authManager->login($identityType, [
            Manager::LOGIN_DATA_USERNAME    => $username,
            Manager::REGISTER_DATA_PASSWORD => $password
        ]);
        return $this->response->setJsonContent([
            'token'   => $session->getToken(),
            'expires' => $session->getExpirationTime(),
            'user'    => $session->getIdentity()
        ]);
    }

    /**
     * @point(path="/signup",method="post")
     * @throws ApiException
     */
    public function signUp()
    {
    }

    /**
     * @point(path="/me",name='me',scopes={super_user,manager_user})
     */
    public function me()
    {
        return $this->response->setJsonContent([
            $this->userService->getDetails()
        ]);
    }

    /**
     * @param $username
     *
     * @return string
     */
    protected function identityTypeCheck($username)
    {
        if ($this->isEmail($username)) {
            $identityType = EmailAccountType::NAME;
        } elseif ($this->isMobile($username)) {
            $identityType = 'phone';
        } else {
            $identityType = 'username';
        }
        return $identityType;
    }

    protected function accountTypeCheck($username)
    {

    }

}