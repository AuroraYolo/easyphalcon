<?php
namespace App\Component\Enum\Core;

class PointMap
{
    const DEFAULT_GROUP_NAME = 'group';
    const DEFAULT_POINT_NAME = 'point';
    const DEFAULT_METHOD = 'get';
    const DEFAULT_PATH = '/';
    const DEFAULT_NAME = null;
    const DEFAULT_ALLOW = [];
    /**
     * @group
     * @point
     *url路径
     */
    const PATH = 'path';
    /**
     * @point
     * 请求方式
     */
    const METHOD = 'method';
    /**
     * @group 必填
     * @point 必填
     */
    const NAME = 'name';

    /**
     * @point
     * 权限 作用域
     */
    const SCOPES = 'scopes';

}