<?php
namespace App\Component\Core;

use App\Component\Enum\ErrorCode;
use App\Component\Enum\Http\Methods;
use App\Component\Enum\Map\Point;
use App\Component\Core\Point AS PointHandle;
use App\Component\Exception\ApiException;
use Phalcon\Mvc\Micro\Collection;
use Phalcon\Mvc\Micro\CollectionInterface;

class ApiCollection extends Collection implements CollectionInterface
{
    protected $points;
    protected $name;
    protected $controllerName;
    protected $metadata;
    protected $prefix;

    /**
     * ApiCollection constructor.
     *
     * @param string $className
     * @param bool   $lazy
     *
     * @throws ApiException
     * @throws \ReflectionException
     */
    public function __construct(string $className, $lazy = false)
    {
        $core  = Core::getInstance()->setHandle($className);
        $class = $className;
        if (!$lazy) {  //非懒加载，就实例handle
            $class = $core->getHandleInstance();
        }
        // 设置主处理器，这里是控制器的实例
        $this->setHandler($class);
        //获取controller group 注释的反射
        $this->metadata       = $core->getClassValue(Point::DEFAULT_GROUP_NAME);
        $this->controllerName = $className;
        $this->setName();
        $this->setGroupPrefix();
        $docMethod = $core->getMethodValue(Point::DEFAULT_POINT_NAME);
        //对路由设置前缀
        $this->setPrefix($this->prefix);
        $this->mountPoint($docMethod);
    }

    /**
     * 设置Point名称
     */
    protected function setName()
    {
        $this->name = $this->metadata[Point::NAME] ?? null;
    }

    /**
     * 设置Group的前缀
     */
    protected function setGroupPrefix()
    {
        $this->prefix = $this->metadata[Point::PATH] ?? Point::DEFAULT_PATH;
    }

    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @param array $points
     *
     * @throws ApiException
     */
    protected function mountPoint(array $points)
    {
        if ($points && count($points) > 0) {
            foreach ($points as $handle => $item) {
                $point  = new PointHandle($item);
                $path   = $point->getPath();
                $method = $point->getMethod();
                $handelName = serialize([
                    APP::GROUP => $this->getMetadata(),
                    APP::POINT => $item
                ]);
                if ($path && in_array(strtoupper($method), Methods::$ALL_METHODS)) {
                    if (is_array($path)) {
                        foreach ($path as $value) {
                            $this->$method($value, $handle, $handelName);
                        }
                    } else {
                        $this->$method($path, $handle, $handelName);
                    }
                } else {
                    throw new ApiException(ErrorCode::POST_DATA_INVALID, "path error or method invalid");
                }
            }
        }
    }
}