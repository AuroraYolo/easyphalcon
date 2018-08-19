<?php
namespace App\Controller;

use App\Commons\Library\Logger;
use App\Component\Auth\Manager;
use App\Component\Auth\TokenParsers\JwtTokenParser;
use App\Component\Http\Request;
use App\Component\Http\Response;
use App\Component\User\Service;
use Phalcon\Config;
use Phalcon\Mvc\Controller;

/**
 * Class BaseController
 * @package App\Controller
 * @property Request        $request
 * @property   Response     $response
 * @property JwtTokenParser $jwt
 * @property Config         $config
 * @property Service        $userService
 * @property Manager        $authManager
 */
class BaseController extends Controller
{
    private $simpleView;

    /**
     * 返回Phalcon\Mvc\View\Simple对象
     *
     * @return \Phalcon\Mvc\View\Simple
     */
    public function simpleView()
    {
        if (!is_object($this->simpleView)) {
            $this->simpleView = $this->getDI()->getShared('simple_view');
        }
        return $this->simpleView;
    }

    /**
     * 显示模板
     *
     * @param string $path
     * @param array  $params
     *
     * @return void
     */
    public function display($path = null, $params = null)
    {
        //Phalcon\Mvc\View
        $params = array_merge([], (array)$this->view->getParamsToView(), (array)$params);
        $this->view->setVars($params);
        if ($path != null && $path != '') {
            $content = $this->simpleView()->render(trim($path, '/'), $params);
            $layout  = $this->view->getLayout();
            if ($layout == '') {
                echo $content;
                exit();
            } else {
                $this->view->setContent($content);
            }
        }
        $this->view->render($this->dispatcher->getControllerName(), $this->dispatcher->getActionName(), $this->view->getParams());
    }

    /**
     * @return null|\Phalcon\Logger\Adapter\File
     * @throws \Exception
     */
    protected function logger()
    {
        return Logger::logger('api');
    }
}