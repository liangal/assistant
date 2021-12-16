<?php
namespace app\services\manage;

use app\repository\PermissionRepository;
use app\repository\RoleRepository;

class MenuServices
{
	protected $permissionRepository;
    protected $roleRepository;

    public function __construct(PermissionRepository $permissionRepository, RoleRepository $roleRepository){
        $this->permissionRepository = $permissionRepository;
        $this->roleRepository = $roleRepository;
    }

    /**
     * 菜单数据
     *
     * @param array $columns
     * @return void
     */
    public function list()
    {
        $user = request()->user;

        $list = array();
        $menus = array();

        // 超级管理员
        if($user->id == 1) {
            $menus = $this->permissionRepository->getMeus()->toArray();
        }
        else
        {
            $user_roles = $user->cachedRoles();
            if(!$user_roles->isEmpty()) {
                $user_role = array_first($user_roles);
                if(!$user_role->pivot->isEmpty()) {
                    $role_id = $user_role->pivot->role_id;
                    $menus = cache('rbac_permissions_for_role_' . $role_id);
                    
                    if(empty($menus)){
                        $role = $this->roleRepository->find($role_id);
                        $menus = $role->cachedPermissions()->toArray();
                    }
                    else
                    {
                        $menus = $menus->toArray();
                    }

                    if(!empty($menus)){
                        $sort = array_column($menus,'sort_order');
                        array_multisort($sort, SORT_ASC, $menus);
                    }
                }
            }
        }
        
        if (!empty($menus)) {
            $list = $this->treeview(array_pluck($menus, 'parent_id', 'id'), 0);
        }

        return $list;
    }

    /**
     * treeview数据节点
     * 
     * @param array $permissions  [权限数据]
     * @param int   $nodeid [节点ID]
     * 
     * @param int   $defalutMeus [默认选择状态]
     */
    protected function treeview(array $permissions, int $nodeid)
    {
        $json = array();

        foreach ($permissions[$nodeid] as $pkey => $node) {
            if($node['display_menu'] == 1) {
                $json_item = array('name'=>$node['description']);

                if($node['parent_id'] == 0) {
                    $json_item['url'] = 'javascript:;';
                    $json_item['icon'] = $node['icon'];
                }
                else
                {
                    $json_item['url'] = '#/' . $node['menuUrl'];
                }

                if (!empty($permissions[$node['id']])) {
                    $subMenus = $this->treeview($permissions, $node['id']);
                    if(!empty($subMenus)) {
                        $json_item['subMenus'] = $subMenus;
                    }
                }

                $json[] = $json_item;
            }
        }

        return $json;
    }
}