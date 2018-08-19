<?php
namespace App\Component\Core;

use App\Component\Auth\Manager;
use App\Component\Auth\TokenParsers\JwtTokenParser;
use App\Component\Http\Request;
use App\Component\User\Service;
use Phalcon\Config;
use Phalcon\Mvc\User\Plugin;

/**
 * Class ApiPlugin
 * @package App\Component\Core
 * @property Config         $config
 * @property Service        $userService
 * @property JwtTokenParser $tokenParser
 * @property Manager        $authManager
 * @property Request        $request
 *
 * DI注册提示用
 */
class ApiPlugin extends Plugin
{

}