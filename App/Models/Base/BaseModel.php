<?php
namespace App\Models\Base;

use App\Commons\Library\Logger;
use App\Component\Enum\Services;

use Phalcon\Mvc\Model;

class BaseModel extends Model
{

    public function initialize()
    {

    }

    /**
     * @var integer
     */
    public $id;

    /**
     * @var  \Phalcon\Db\Adapter\Pdo\Mysql;
     */
    private $db;

    /**
     * @var  \Phalcon\Logger\AdapterInterface
     */
    private $logger;

    /**
     * 获取数据库对象
     *
     * @return mixed|\Phalcon\Db\Adapter\Pdo\Mysql
     */
    public function db()
    {
        if (!is_object($this->db)) {
            $this->db = $this->getDI()->getShared(Services::DB);
        }
        return $this->db;
    }

    /**
     * @return \Pdo
     */
    public function pdo()
    {
        return $this->db()->getInternalHandler();
    }

    /**
     *
     * @return  \Phalcon\Cache\Backend\Memcache
     */
    public function memCache()
    {
        return $this->getDI()->getShared(Services::MEMCACHE_CACHE);
    }

    /**
     * @return \Redis
     */
    public function redis()
    {
        return $this->getDI()->getShared(Services::REDIS_CACHE);
    }

    /**
     * @return Model\Query\BuilderInterface
     */
    public function queryBuilder()
    {
        return $this->getModelsManager()->createBuilder();
    }

    /**
     * 封装ORM查询语句
     *
     * @param string $columns
     *
     * @return Model\Query\BuilderInterface
     */
    public function select($columns = '*')
    {
        return $this->queryBuilder()->columns($columns);
    }

    /**
     * @return \Phalcon\Logger\Adapter\File
     * @throws \Exception
     */
    public function logger()
    {
        if (!is_object($this->logger)) {
            $this->logger = Logger::logger('db');
        }
        return $this->logger();
    }

    /**
     * 记录错误日志
     *
     * @param string   $type   数据库操作类型
     * @param string   $query  数据库查询语句
     * @param string   $reason 失败原因
     * @param string   $file   发生的文件
     * @param  integer $line   发生错误的行数
     *
     * @throws \Exception
     */
    public function errorLog($type, $query, $reason, $file, $line)
    {
        $this->logger()->error(printf('数据库操作失败：类型[%s]，Query[%s]原因[%s][%s:%s] 总运行时间: [%s]', $type, $query, $reason, $file, $line));
    }

}