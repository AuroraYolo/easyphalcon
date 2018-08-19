<?php
/**
 * Created by PhpStorm.
 * User: maksim
 * Date: 2018/1/30
 * Time: 17:12
 */

namespace App\Middleware;

use App\Component\Core\ApiPlugin;
use App\Component\Enum\Services;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\MiddlewareInterface;

/**
 * 自动解析include，关联模型数据
 * Class FractalMiddleware
 * @package App\Middleware
 */
class FractalMiddleware extends ApiPlugin implements MiddlewareInterface
{
    public $parseIncludes;

    public function __construct($parseIncludes = true)
    {
        $this->parseIncludes = $parseIncludes;
    }

    public function beforeExecuteRoute()
    {
        /** @var \League\Fractal\Manager $fractal */
        $fractal = $this->di->get(Services::FRACTAL_MANAGER);

        if ($this->parseIncludes) {
            $include = $this->request->getQuery('include');
            if (!is_null($include)) {
                $fractal->parseIncludes($include);
            }
        }
    }

    /**
     * Calls the middleware
     *
     * @param Micro $application
     *
     * @return bool
     */
    public function call(Micro $application)
    {
        return true;
    }
}