<?php

namespace App\Component\Auth\Account;

use App\Component\Auth\Manager;
use App\Models\User;

class EmailAccountType extends BaseAccountType
{
    const NAME = "email";

    public function login($data)
    {

        $email    = $data[Manager::LOGIN_DATA_USERNAME];
        $password = $data[Manager::LOGIN_DATA_PASSWORD];
        /** @var User $user */
        $user = User::findFirst([
            'conditions' => 'email = :email:',
            'bind'       => ['email' => $email]
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
        // TODO: Implement register() method.
    }

    public function authenticate($identity)
    {
        return User::count([
                'conditions' => 'id = :id:',
                'bind'       => ['id' => (int)$identity]
            ]) > 0;
    }
}