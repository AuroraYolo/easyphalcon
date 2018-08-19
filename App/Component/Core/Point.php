<?php
namespace App\Component\Core;
use App\Component\Enum\Map\Point AS PointMap;
class Point{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $path;

    protected $method;
    /**
     * @var array 原信息
     */
    protected $metadata;
    /**
     * @var string 方法名
     */
    protected $methodName;

    /**
     * @return mixed
     */
    public function getMethodName()
    {
        return $this->methodName;
    }

    /**
     * @param mixed $methodName
     */
    public function setMethodName($methodName)
    {
        $this->methodName = $methodName;
    }

    function __construct($array)
    {
        $this->metadata = $array;
        $this->setMethod();
        $this->setPath();
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }


    protected function setMethod()
    {
        $this->method = $this->metadata[PointMap::METHOD] ?? PointMap::DEFAULT_METHOD;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }


    public function setName()
    {

        $this->name = $this->metadata[PointMap::NAME];
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    public function setPath()
    {
        $this->path = $this->metadata[PointMap::PATH] ?? PointMap::DEFAULT_PATH;
    }

    /**
     * @return mixed
     */
    public function getMetadata()
    {
        return $this->metadata;
    }
}