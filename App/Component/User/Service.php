<?php
namespace App\Component\User;

use App\Component\Core\ApiPlugin;
use App\Component\Enum\Acl\UserRoles;
use App\Component\Enum\ErrorCode;
use App\Component\Exception\ApiException;
use App\Models\User;

class Service extends ApiPlugin
{
    protected $_detailCache = [];

    public function getDetails()
    {
        $details = null;
        $session = $this->authManager->getSession();
        if ($session) {
            $identity = $session->getIdentity();
            $details  = $this->getDetailsForIdentity($identity);
        }
        return $details;
    }

    protected function getDetailsForIdentity($identity)
    {
        if (array_key_exists($identity, $this->_detailCache)) {
            return $this->_detailCache[$identity];
        }

        $details                       = User::findFirst((int)$identity);
        $this->_detailCache[$identity] = $details;
        return $details;
    }

    public function getIdentity()
    {
        $session = $this->authManager->getSession();
        if ($session) {
            return $session->getIdentity();
        }
        return null;
    }

    /**
     * @return array
     * @throws ApiException
     */
    public function getRole()
    {
        /** @var User $userModel */
        $userModel = $this->getDetails();
        $role      = [];
        if ($userModel && isset($userModel->userRole)) {
            $arr = [];
            foreach ($userModel->userRole as $item) {
                if ($item->role) {
                    $arr[] = $item->role->name;
                } else {
                    throw new ApiException(ErrorCode::POST_DATA_INVALID);
                }
            }
            $role = array_merge($role, $arr);
        }
        return $role;
    }

    /**
     * 读取t_scopes表，获取该用户所属的role所具备的scope权限域
     *
     * @return array
     * @throws ApiException
     */
    public function getScopes()
    {
        $userModel = $this->getDetails();
        $scopes    = [];
        if ($userModel && isset($userModel->userRole)) {
            $arr = [];
            foreach ($userModel->userRole as $item) {
                if ($item->role && $item->role->scope) {
                    $arr[] = $item->role->scope->name;
                } else {
                    throw new ApiException(ErrorCode::POST_DATA_INVALID);
                }
            }
            $scopes = array_merge($scopes, $arr);
        }
        return $scopes;
    }

    /**
     * @return bool
     * @throws ApiException
     */
    public function isVip()
    {
        return isset($this->getRole()[UserRoles::VIP]) ?? false;
    }

    /**
     * @return bool
     * @throws ApiException
     */
    public function isAdministrator()
    {
        return isset($this->getRole()[UserRoles::ADMINISTRATOR]) ?? false;
    }

    /**
     * @return bool
     * @throws ApiException
     */
    public function isManager()
    {
        return isset($this->getRole()[UserRoles::MANAGER]) ?? false;
    }

}