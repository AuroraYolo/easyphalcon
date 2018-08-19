<?php
namespace App\Component\Http;

use App\Component\Dev\Log;
use App\Component\Enum\Services;
use App\Component\Exception\ApiException;

class Response extends \Phalcon\Http\Response
{
    public function setErrorContent(\Throwable $throwable, $developerInfo = false)
    {
        /**
         * @var Request $request
         */
        $request = $this->getDI()->get(Services::REQUEST);
        /**
         * @var ErrorHelper $errorHelper
         */
        $errorHelper = $this->getDI()->has(Services::ERROR_HELPER) ? $this->getDI()->get(Services::ERROR_HELPER) : null;
        $errorCode   = $throwable->getCode();
        $statusCode  = 500;
        $message     = $throwable->getMessage();
        if ($errorHelper && $errorHelper->has($errorCode)) {
            $defaultMessage = $errorHelper->get($errorCode);
            $statusCode     = $defaultMessage['statusCode'];
            if (!$message) {
                $message = $defaultMessage['message'];
            }
        }
        $error = [
            'code'    => $errorCode,
            'message' => $message ? : 'Unspecified error'
        ];
        if ($throwable instanceof ApiException && $throwable->getUserInfo() != null) {
            $error['info'] = $throwable->getUserInfo();
        }
        if ($developerInfo === true) {
            $developerResponse = [
                'file'    => $throwable->getFile(),
                'line'    => $throwable->getLine(),
                'request' => $request->getMethod() . ' ' . $request->getURI()
            ];
            if ($throwable instanceof ApiException && $throwable->getDeveloperInfo() != null) {
                $developerResponse['info'] = $throwable->getDeveloperInfo();
            }
            $error['developer'] = $developerResponse;
        }
        if (!$developerInfo) {
            $log = Log::logger();
            if ($throwable->getCode() > 5000) {
                $log->error('msg' . $message);
            }
        }
        $this->setJsonContent(['error' => $error]);
        $this->setStatusCode($statusCode);
    }

    public function setJsonContent($content, $jsonOptions = 0, $depth = 512)
    {
        parent::setJsonContent($content, $jsonOptions, $depth);
        $this->setContentType('application/json', 'UTF_8');
        $this->setEtag(md5($this->getContent()));
    }
}