<?php

namespace App\Controller;

use App\Component\Exception\ApiException;

/**
 * Class IndexController
 * @package App\controller
 * @group(path="/",name='index')
 */
class IndexController extends BaseController
{

    /**
     * @point(path="error404",name=404)
     */
    public function error404()
    {
        $this->display('/404');
    }

    /**
     * @point(path="error500",name=500)
     */
    public function error500()
    {

    }

    /**
     * @point(path='/',name='index')
     */
    public function index()
    {
        throw new ApiException('Not Found');
    }
}