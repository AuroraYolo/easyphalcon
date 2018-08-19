<?php
namespace App\Controller\Resource;

use App\Controller\BaseController;

/**
 * Class UserResource
 * @package App\Controller\Resource
 * @group(path='/resource/user',name='user')
 */
class UserResource extends BaseController
{
    /**
     * @point(path='/all',method='GET')
     */
    public function getUser()
    {

    }
}