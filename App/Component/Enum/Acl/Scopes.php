<?php
namespace App\Component\Enum\Acl;
/**
 * 资源式思维：一个resource（资源）定义一个常量，只有拥有该资源的人才能访问。设计一个scope表，存储这些资源。只读，不可增删改
 * 设计一个role表角色关联资源，一个角色可以拥有多个资源
 * role表和用户表关联
 * Class Scopes
 * @package App\constants\Acl
 */
class Scopes
{
    /**
     * 公用,无需登录验证
     */
    const SCOPES_UNAUTHORIZED = "unauthorized";
    /**
     * 普通用户
     */
    const SCOPES_COMMON_USERS = "common_user";
    /**
     * 平台用户
     */
    const SCOPES_MANAGER_USERS = "manager_user";
    /**
     * 系统管理员
     */
    const SCOPES_SUPER_USERS = "super_user";

    /**
     * 企业账户：拥有所有权限
     */
    const SCOPES_ENT_ADMIN = 'ent_admin';

    /**
     * 企业子用户
     */
    const SCOPES_ENT_USER = 'ent_user';

    /**
     * Dashboard资源
     */
    const DASHBOARD = 'dashboard';
}