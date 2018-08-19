<?php
namespace App\Component\Http;

use App\Component\Enum\PostedDataMethods;

class Request extends \Phalcon\Http\Request
{
    protected $_postedDataMethod = PostedDataMethods::AUTO;

    /**
     * @param string $method One of the method constants defined in PostedDataMethods
     *
     * @return $this
     */
    public function postedDataMethod($method)
    {
        $this->_postedDataMethod = $method;
        return $this;
    }

    /**
     * Sets the posted data method to POST
     *
     * @return $this
     */
    public function expectsPostData()
    {
        $this->postedDataMethod(PostedDataMethods::POST);
        return $this;
    }

    /**
     * Sets the posted data method to PUT
     *
     * @return static
     */
    public function expectsPutData()
    {
        $this->postedDataMethod(PostedDataMethods::PUT);
        return $this;
    }

    /**
     * Sets the posted data method to PUT
     *
     * @return static
     */
    public function expectsGetData()
    {
        $this->postedDataMethod(PostedDataMethods::GET);
        return $this;
    }

    /**
     * Sets the posted data method to JSON_BODY
     *
     * @return static
     */
    public function expectsJsonData()
    {
        $this->postedDataMethod(PostedDataMethods::JSON_BODY);
        return $this;
    }

    /**
     * @return string $method One of the method constants defined in PostedDataMethods
     */
    public function getPostedDataMethod()
    {
        return $this->_postedDataMethod;
    }

    public function getPostedData($httpMethod = null)
    {
        $method = $httpMethod !== null ? $httpMethod : $this->_postedDataMethod;
        if ($method == PostedDataMethods::AUTO) {
            if ($this->getContentType() === 'application/json') {
                $method = PostedDataMethods::JSON_BODY;
            } elseif ($this->isPost()) {
                $method = PostedDataMethods::POST;
            } elseif ($this->isGet()) {
                $method = PostedDataMethods::GET;
            } elseif ($this->isPut()) {
                $method = PostedDataMethods::PUT;
            }
        }
        if ($method == PostedDataMethods::JSON_BODY) {
            return $this->getJsonRawBody();
        } elseif ($method == PostedDataMethods::POST) {
            return $this->getPost();
        } elseif ($method == PostedDataMethods::PUT) {
            return $this->getPut();
        } elseif ($method == PostedDataMethods::GET) {
            return $this->getQuery();
        }
        return [];
    }

    /**
     * Returns auth username
     *
     * @return string|null
     */
    public function getUsername()
    {
        return $this->getServer('PHP_AUTH_USER');
    }

    /**
     * Returns auth password
     *
     * @return string|null
     */
    public function getPassword()
    {
        return $this->getServer('PHP_AUTH_PW');
    }

    /**
     * Returns token from the request.
     * Uses token URL query field, or Authorization header
     *
     * @return mixed|null|string|string[]
     */
    public function getToken()
    {
        $authHeader = $this->getHeader('AUTHORIZATION');
        $authQuery  = $this->getQuery('token');
        return $authQuery ? $authQuery : $this->parseBearerValue($authHeader);
    }

    protected function parseBearerValue($string)
    {
        if (strpos(trim($string), 'Bearer') !== 0) {
            return null;
        }

        return preg_replace('/.*\s/', '', $string);
    }

    public function getCallbackUrl()
    {

    }

    public function getClientID()
    {

    }

    public function getClientSecret()
    {

    }

}