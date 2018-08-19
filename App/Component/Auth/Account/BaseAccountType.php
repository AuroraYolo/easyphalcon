<?php
namespace App\Component\Auth\Account;

use App\Component\Enum\ErrorCode;
use App\Component\Exception\ApiException;
use App\Models\User;

class BaseAccountType implements AccountType
{
    public function numOfLogin(User $user)
    {

    }

    /**
     * @param $user User
     *
     * @throws ApiException
     */
    protected function statusOfUser($user)
    {
        if (!$user) {
            throw new ApiException(ErrorCode::AUTH_LOGIN_FAILED, 'Not_found');
        }
        if ($user->isActive !== User::ACTIVE) { //禁用
            throw new ApiException(ErrorCode::AUTH_LOGIN_FAILED, 'Account_disabled');
        }
    }

    /**
     * @param array $data
     *
     * @return string|void
     * @throws ApiException
     */
    public function login($data)
    {
        throw new ApiException(ErrorCode::POST_DATA_INVALID);
    }

    /**
     * @param string $identity
     *
     * @return bool|void
     * @throws ApiException
     */
    public function authenticate($identity)
    {
        throw new ApiException(ErrorCode::POST_DATA_INVALID);
    }

    /**
     * @param array $data
     *
     * @return string|void
     * @throws ApiException
     */
    public function register($data)
    {
        throw new ApiException(ErrorCode::POST_DATA_INVALID);
    }
}