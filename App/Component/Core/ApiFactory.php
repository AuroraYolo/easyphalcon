<?php
namespace App\Component\Core;

use App\Component\Enum\Services;
use App\Component\Http\ErrorHelper;
use App\Component\Http\FormatHelper;
use App\Component\Http\Request;
use Phalcon\Di\FactoryDefault;
use Phalcon\Http\Response;

class ApiFactory extends FactoryDefault
{
    public function __construct()
    {
        parent::__construct();
        $this->setShared(Services::REQUEST, new Request);
        $this->setShared(Services::RESPONSE, new Response);
        $this->setShared(Services::ERROR_HELPER, new ErrorHelper);
        $this->setShared(Services::FORMAT_HELPER, new FormatHelper);
    }

}