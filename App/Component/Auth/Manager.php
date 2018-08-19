<?php
namespace App\Component\Auth;

use App\Component\Auth\Account\AccountType;
use App\Component\Core\ApiPlugin;
use App\Component\Enum\ErrorCode;
use App\Component\Exception\ApiException;

class Manager extends ApiPlugin
{
    const LOGIN_DATA_USERNAME = 'username';
    const LOGIN_DATA_PASSWORD = 'password';
    const REGISTER_DATA_USERNAME = 'username';
    const REGISTER_DATA_PASSWORD = 'password';
    /**
     * @var AccountType[] Account types
     */
    protected $_accountTypes;
    /**
     * @var Session  Current active session
     */
    protected $_session;

    /**
     * @var int Expiration time of created sessions
     */
    protected $_sessionDuration;

    public function __construct($sessionDuration = 86400)
    {
        $this->_sessionDuration = $sessionDuration;
        $this->_accountTypes    = [];
        $this->_session         = null;
    }

    public function registerAccountType($name, AccountType $accountType)
    {
        $this->_accountTypes[$name] = $accountType;
        return $this;
    }

    public function getAccountTypes()
    {
        return $this->_accountTypes;
    }

    public function getSessionDuration()
    {
        return $this->_sessionDuration;
    }

    public function setSessionDuration(int $time)
    {
        $this->_sessionDuration = $time;
    }

    public function getSession()
    {
        return $this->_session;
    }

    public function setSession(Session $session)
    {
        $this->_session = $session;
    }

    /**
     * Check if a user is currently logged in
     *
     * @return bool
     */
    public function loggedIn()
    {
        return !!$this->_session;
    }

    /**
     * @param $accountTypeName
     * @param $userName
     * @param $password
     *
     * @return Session|null
     * @throws ApiException
     */
    public function loginWithUsernamePassword($accountTypeName, $userName, $password)
    {
        return $this->login($accountTypeName, [
            self::LOGIN_DATA_USERNAME => $userName,
            self::LOGIN_DATA_PASSWORD => $password
        ]);
    }

    /**
     * @param       $accountTypeName
     * @param array $data
     *
     * @return Session|null
     * @throws ApiException
     */
    public function login($accountTypeName, array $data)
    {
        if (!$account = $this->getAccountType($accountTypeName)) {
            throw new ApiException(ErrorCode::AUTH_INVALID_ACCOUNT_TYPE);
        }
        if (!$data) {
            throw new ApiException(ErrorCode::DATA_NOT_FOUND);
        }
        $identity = $account->login($data);
        if (!$identity) {
            throw  new ApiException(ErrorCode::AUTH_LOGIN_FAILED);
        }
        $startTime = time();

        $session = new Session($accountTypeName, $identity, $startTime, $startTime + $this->_sessionDuration);
        $token   = $this->tokenParser->getToken($session);
        $session->setToken($token);
        $this->_session = $session;
        return $this->_session;
    }

    /**
     * 根据相应 的账户进行注册
     *
     * @param       $accountTypeName
     * @param array $data
     *
     * @return Session|null
     * @throws ApiException
     */
    public function registration($accountTypeName, array $data)
    {
        if (!$account = $this->getAccountType($accountTypeName)) {
            throw new ApiException(ErrorCode::AUTH_INVALID_ACCOUNT_TYPE);
        }
        $identity = $account->register($data);
        if (!$identity) {
            throw new ApiException(ErrorCode::AUTH_LOGIN_FAILED);
        }
        $startTime = time();
        $session   = new Session($accountTypeName, $identity, $startTime, $startTime + $this->_sessionDuration);
        $token     = $this->tokenParser->getToken($session);
        $session->setToken($token);
        $this->_session = $session;
        return $this->_session;
    }

    /**
     * @param $name
     *
     * @return AccountType|mixed|null
     */
    public function getAccountType($name)
    {
        if (array_key_exists($name, $this->_accountTypes)) {

            return $this->_accountTypes[$name];
        }

        return null;
    }

    /**
     * 验证token
     *
     * @param $token
     *
     * @return Session|bool
     * @throws ApiException
     */
    public function authenticateToken($token)
    {
        //Todo 从redis中查询是否存在,不存在return，从而来确保token的时效性
        try {
            $session = $this->tokenParser->getSession($token);
        } catch (\Exception $ex) {
            throw new ApiException(ErrorCode::AUTH_TOKEN_INVALID);
        }
        if (!$session) {
            return false;
        }
        if ($session->getExpirationTime() < time()) {
            throw new ApiException(ErrorCode::AUTH_SESSION_EXPIRED);
        }
        $session->setToken($token);
        // Authenticate identity
        if (!$account = $this->getAccountType($session->getAccountTypeName())) {
            throw new ApiException(ErrorCode::AUTH_TOKEN_INVALID);
        }

        if (!$account->authenticate($session->getIdentity())) {
            throw new ApiException(ErrorCode::AUTH_TOKEN_INVALID);
        }
        $this->_session = $session;
        return true;
    }
}