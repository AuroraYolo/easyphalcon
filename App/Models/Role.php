<?php
namespace App\Models;

use App\Models\Base\BaseModel;

class Role extends BaseModel
{
    const ACTIVE = 1;

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
            'update_at'   => 'update_at'
        ];
    }

    public function initialize()
    {
        $this->setSchema('wewechat_dev');
        $this->setSource('role');
        $this->hasMany('id', 'App\Models\UserRole', 'roleId', [
            'alias' => 'userRole'
        ]);
        $this->belongsTo('scopeId', 'App\Models\Scope', 'id', [
            'alias' => 'scope'
        ]);
    }
}