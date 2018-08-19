<?php
namespace App\Component\Core;

use App\Component\Enum\ErrorCode;
use App\Component\Exception\ApiException;
use Phalcon\Annotations\Annotation;
use Phalcon\Annotations\Factory;
use Phalcon\Annotations\Collection;

class Core extends ApiPlugin
{
    const TYPE_CLASS = "class";
    const TYPE_METHOD = "method";
    const TYPE_PROPERTY = "property";
    /**
     * @var $instance Core;
     */
    protected static $instance;
    protected $annotations;
    protected $handle;

    /**
     * @return Core|static
     * @throws ApiException
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     *
     * Core constructor.
     * @throws ApiException
     */
    public function __construct()
    {
        $config = $this->config->get('router')['annotations'];
        if (!isset($config)) {
            throw new ApiException(ErrorCode::POST_DATA_NOT_PROVIDED, '配置文件不存在');
        }
        $this->annotations = Factory::load($config);
    }

    /**
     * 获取类或者方法的注释值
     *
     * @param        $name
     * @param string $type
     *
     * @return array|bool|mixed|Annotation
     */
    public function getValue($name, $type = self::TYPE_CLASS)
    {
        $res = false;
        if ($type == self::TYPE_CLASS) {
            $res = $this->getClassValue($name);
        }
        if ($type == self::TYPE_METHOD) {
            $res = $this->getMethodValue($name);
        }
        return $res;
    }

    /**
     * 获取类的注释反射
     * Example: @group(path="/auth",name=auth)
     *
     * @param $name
     *
     * @return array|bool|mixed|Annotation
     */
    public function getClassValue($name)
    {
        //返回在类中找到的注释
        $collection = $this->annotations->get($this->handle)->getClassAnnotations();
        if (!$collection) {
            return false;
        }
        $result = $this->getAnnotations($collection, $name);
        if ($result instanceof Annotation) {
            return $result->getArguments();
        }
        return $result;
    }

    /**
     *获取方法的注释反射
     * Example: @point(path="/authenticate",method="post")
     *
     * @param $name
     *
     * @return array
     */
    public function getMethodValue($name)
    {
        $array  = $this->annotations->get($this->handle)->getMethodsAnnotations();
        $result = [];

        if ($array && count($array) > 0) {
            foreach ($array as $funName => $collection) {
                $res = $this->getAnnotations($collection, $name);
                if ($res && $res instanceof Annotation) {
                    $result[$funName] = $res->getArguments();
                }
            }
        }

        return $result;
    }

    /**
     * @param Collection $collection
     * @param            $name
     *
     * @return bool|mixed|Annotation
     */
    protected function getAnnotations(Collection $collection, $name)
    {
        $result = false;
        if ($collection->has($name)) {
            $result = $collection->get($name);
        }
        if (count($collection) > 0) {
            foreach ($collection as $item) {
                if ($item->hasArgument($name)) {
                    $result = $item->getArgument($name);
                    break;
                }
            }
        }
        return $result;
    }

    public function setHandle($handle)
    {
        $this->handle = $handle;
        return $this;
    }

    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * 类名
     *
     * @param string $className
     *
     * @return object
     * @throws \ReflectionException
     */
    public function getHandleInstance($className = '')
    {
        if ($className == '') {
            $className = $this->getHandle();
        }
        //利用ReflectionClass反射类  newInstance()创造新的实例
        $oReflectionClass = new \ReflectionClass($className);
        $instance         = $oReflectionClass->newInstance();
        return $instance;
    }

}