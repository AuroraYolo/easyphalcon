<?php
namespace App\Component\Enum;

class Services
{
    const CRYPT_KEY = 'easyphalcon';

    /**
     * Phalcon 服务
     */
    const CONFIG = 'config';
    const REDIS_CACHE = 'redis';
    const MEMCACHE_CACHE = 'memcache';
    const URL = 'url';
    const DISPATCHER = 'dispatcher';
    const MODELS_MANAGER = 'modelsManager';
    const MODELS_METADATA = "modelsMetadata";
    const ROUTER = 'router';
    const RESPONSE = 'response';
    const COOKIES = 'cookies';
    const REQUEST = 'request';
    const SESSION = 'session';
    const FILTER = 'filter';
    const CRYPT = 'crypt';
    const TAG = 'tag';
    const FLASH = 'flash';
    const ANNOTATIONS = "annotations";
    const LOG = 'log';
    const DB = 'db';
    const VIEW = 'view';
    const EVENTS_MANAGER = 'eventsManager';
    const SIMPLE_VIEW = 'simple_view';

    /**
     * PhalaconApi
     */
    const AUTH_MANAGER = "authManager";
    const ACL = "acl";
    const TOKEN_PARSER = "tokenParser";
    const QUERY = "query";
    const USER_SERVICE = "userService";
    const URL_QUERY_PARSER = "urlQueryParser";
    const ERROR_HELPER = "errorHelper";
    const FORMAT_HELPER = "formatHelper";

    const FRACTAL_MANAGER = 'fractalManager';
}