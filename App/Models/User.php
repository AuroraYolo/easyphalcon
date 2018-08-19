<?php
namespace App\Models;

use App\Component\Enum\Services;
use App\Models\Base\BaseModel;
use Phalcon\Db\Exception;
use Phalcon\Mvc\Model\Behavior\SoftDelete;

class User extends BaseModel
{
    const ACTIVE = 1;
    const EMAIL_VERIFIED = 1;
    const MAN = 1;
    const WOMAN = 0;
    const DELETED = '1';
    const NOT_DELETED = '0';

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=11, nullable=false)
     */
    public $id;

    /**
     *
     * @var string
     * @Column(type="string", length=64, nullable=true)
     */
    public $openid;

    /**
     *
     * @var string
     * @Column(type="string", length=64, nullable=true)
     */
    public $nickName;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $avatar;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $create_at;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $update_at;

    public function initialize()
    {
        //如果模型映射到与默认模式/数据库不同的模式/数据库中的表
        //        $this->setSchema('wewechat_dev');
        //定义数据库连接类型
        //        $this->setConnectionService('db');
        //配置读库
        $this->setReadConnectionService('dbSlave');
        //配置写库
        $this->setWriteConnectionService(Services::DB);
        $this->setSource('user');
        $this->hasMany('id', 'App\Models\Score', 'user_id', [
            'alias' => 'Score'
        ]);
        $this->addBehavior(new SoftDelete([
            'field' => 'isDeleted',
            'value' => User::DELETED
        ]));
        $this->hasMany('id', 'App\Models\UserRole', 'userId', [
            'alias' => 'userRole'
        ]);
    }

    public function getSource()
    {
        return 'user';
    }

    public function verifyPassWord($password)
    {
        return true;
    }

    public function gets()
    {
        try {
            return $this->select('*')->from(self::class)->getQuery()->execute()->toArray();
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

}