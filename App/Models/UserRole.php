<?php
namespace App\Models;

use App\Models\Base\BaseModel;

class UserRole extends BaseModel
{
    /**
     *
     * @var integer
     */
    public $userId;

    public $roleId;

    /**
     * Independent Column Mapping.
     * Keys are the real names in the table and the values their names in the application
     *
     * @return array
     */
    public function columnMap()
    {
        return [
            'id'          => 'id',
            'name'        => 'name',
            'scope_id'    => 'scopeId',
            'description' => 'description',
            'is_active'   => 'isActive',
            'create_at'   => 'create_at',
            'role_id'     => 'roleId',
            'user_id'     => 'userId',
            'update_at'   => 'update_at'
        ];
    }

    public function initialize()
    {
        $this->setSchema('wewechat_dev');
        $this->setSource('user_role');
        $this->belongsTo('roleId', 'App\Models\Role', 'id', [
            'alias' => 'role'
        ]);
        $this->belongsTo('userId', 'App\Models\User', 'id', [
            'alias' => 'user'
        ]);
    }

}