<?php
namespace App\Component\Auth\Account;

interface AccountType
{
    /**
     * @param array $data Login data
     *
     * @return string Identity
     */
    public function login($data);

    /**
     * @param string $identity Identity
     *
     * @return bool Authentication successful
     */
    public function authenticate($identity);

    /**
     * @param array $data Login data
     *
     * @return string Identity
     */

    public function register($data);
}