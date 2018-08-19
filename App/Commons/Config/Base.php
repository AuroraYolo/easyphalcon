<?php
namespace App\Commons\Config;

return array_merge(
    [
        'application'    => [
            'commonsDir'           => dirname(__DIR__) . '/',
            'vendorAutoLoaderFile' => dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php',
            'bootstrapDir'         => dirname(dirname(__DIR__)) . '/Bootstrap/',
            'componentDir'         => dirname(dirname(__DIR__)) . '/Component/',
            'controllerDir'        => dirname(dirname(__DIR__)) . '/Controller/',
            'configDir'            => dirname(dirname(__DIR__)) . '/Config/',
            'fractalDir'           => dirname(dirname(__DIR__)) . '/Fractal/',
            'helperDir'            => dirname(dirname(__DIR__)) . '/Helper/',
            'modelsDir'            => dirname(dirname(__DIR__)) . '/Models/',
            'viewsDir'             => dirname(dirname(__DIR__)) . '/Views/',
            'logsDir'              => dirname(dirname(__DIR__)) . '/Logs/',
        ],
        'jwt'            => [
            'private_key' => dirname(dirname(__DIR__)) . '/Private/jwt/' . ENVIRONMENT . '/rsa_private_key.pem',
            'public_key'  => dirname(dirname(__DIR__)) . '/Private/jwt/' . ENVIRONMENT . '/rsa_public_key.pem'
        ],
        'authentication' => [
            'secret'         => 'L%JZZ#aJ%Ka#I3koe!jHxcXd@U',
            'expirationTime' => 86400 * 7, // One week till token expires
        ],
        "router"         => [
            'adapter'         => 'files', //{''：不使用缓存,files：文件缓存}
            "frontendOptions" => [
                "lifetime" => 172800,
            ],
            "backendOptions"  => [
                "cacheDir" => APP_PATH . "/Cache/Router/",
            ],
            'annotations'     => [
                'prefix'         => 'annotations',
                'lifetime'       => '3600',
                'adapter'        => 'memory',    //{memory 测试开发用,apc 正式环境 }
                "annotationsDir" => APP_PATH . "/Cache/Annotations/", // files 模式启用
            ]
        ],
        'logs'           => [
            'swoole' => dirname(dirname(__DIR__)) . '/Logs/Swoole/',
            'api'    => dirname(dirname(__DIR__)) . '/Logs/Api/',
            'db'     => dirname(dirname(__DIR__)) . '/Logs/Db/',
            'others' => dirname(dirname(__DIR__)) . '/Logs/Other/'
        ]
    ],
    include_once dirname(dirname(__DIR__)) . '/Config/Db.php');