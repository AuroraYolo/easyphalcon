<?php
namespace App\Component\Auth\Account;

use App\Component\Auth\Manager;
use App\Models\User;

class UsernameAccountType extends BaseAccountType
{
    const name = 'username';

    public function login($data)
    {
        $username = $data[Manager::LOGIN_DATA_USERNAME];
        $password = $data[Manager::LOGIN_DATA_PASSWORD];
        /**@var User $user */
        $user = User::findFirst([
            'conditions' => 'username = :username:',
            'bind'       => ['username' => $username]
        ]);
        if (!$user) {
            return null;
        }
        if (!$user->verifyPassWord($password)) {
            return null;
        }
        return (string)$user->id;
    }

    public function register($data)
    {
        $username = $data[Manager::REGISTER_DATA_USERNAME];
        $password = $data[Manager::REGISTER_DATA_PASSWORD];
    }

    public function authenticate($identity)
    {
        return User::count([
                'conditions' => 'id = :id:',
                'bind' => ['id' => (int)$identity]
            ]) > 0;
    }
}