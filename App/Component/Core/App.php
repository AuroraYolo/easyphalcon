<?php
namespace App\Component\Core;

use Phalcon\Mvc\Micro;

class App extends Micro
{
    const GROUP = 'g';
    const POINT = 'p';
    /**
     * @var null 匹配的路线名称
     */
    protected $_matchedRouteNameParts = null;

    /**
     * 返回当前路由的 point 参数
     *
     * @return mixed|null
     */
    public function getMatchedEndpoint()
    {
        $endPoint = $this->getMatchedRouteNamePart(self::POINT);
        return $endPoint;
    }

    /**
     * 获取路由名称的匹配部分
     *
     * @param $key
     *
     * @return mixed|null
     */
    protected function getMatchedRouteNamePart($key)
    {
        if (is_null($this->_matchedRouteNameParts)) {
            $routeName = $this->getRouter()->getMatchedRoute()->getName();
            if (!$routeName) {
                return null;
            }
            $this->_matchedRouteNameParts = @unserialize($routeName);
        }
        if (is_array($this->_matchedRouteNameParts) && array_key_exists($key, $this->_matchedRouteNameParts)) {
            return $this->_matchedRouteNameParts[$key];
        }
        return null;
    }
}