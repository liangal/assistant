<?php
namespace app\services\manage;

use app\repository\RoleRepository;
use think\facade\Cache;

class RoleServices
{
	protected $roleRepository;

    public function __construct(RoleRepository $roleRepository){
        $this->roleRepository = $roleRepository;
    }

    /**
     * 按ID查找数据
     *
     * @param       $id
     * @param array $columns
     *
     * @return mixed
     */
    public function find($id, $columns = ['*']) {
        return $this->roleRepository->find($id, $columns);
    }

    /**
     * 存储一条数据
     *
     * @param array $data
     * @return void
     */
    public function store(array $data) {
        $role = $this->roleRepository->create($data);
        return $role;
    }

    /**
     * 更新一条数据
     *
     * @param string $id   编号
     * @param array $data
     * 
     * @return bool
     */
    public function update(string $id, array $data) {
        return $this->roleRepository->update($data, $id);
    }

    /**
     * 删除一条数据
     *
     * @param integer $id   编号
     * 
     * @return bool
     */
    public function delete(int $id) {
        return $this->roleRepository->delete($id);
    }

    /**
     * 检索所有数据
     *
     * @param array $columns
     *
     * @return array
     */
    public function all($columns = ['*']) {
        return $this->roleRepository->all($columns);
    }

    /**
     * 数据分页总数
     * 
     * @param  string $search        [搜索关键词]
     * 
     * @return array
     */
    public function listForTotal(string $search)
    {
        return $this->roleRepository->listForTotal($search);
    }

    /**
     * 数据分页
     *
     * @param string $search 
     * @param integer $start
     * @param integer $length
     * 
     * @return void
     */
    public function listForPage(string $search, int $start, int $length)
    {
        return $this->roleRepository->listForPage($search, $start, $length);
    }

    /**
     * 角色权限
     *
     * @param integer $role_id
     * @return void
     */
    public function permissions(int $role_id)
    {
        $permissionRepository = app('app\repository\PermissionRepository');

        $permissions = $permissionRepository->all();
        $tree = array();
        $role_perms = array();

        $role = $this->roleRepository->find($role_id);
        if(!empty($role)) {
            $role_perms = array_pluck($role->perms->toArray(), 'id');
        }

        foreach($permissions as $key=>$per) {
            $row = array();
            $row['name'] = $per['description'];
            
            if(!empty($per->name) && $per->display_menu == 0) {
                $row['name'] = $row['name'] . ' ' . $per->name;
            }

            if(empty($role_perms)) {
                $row['checked'] = false;
            }
            else
            {
                if(array_has($role_perms, $per->id))
                    $row['checked'] = true;
                else
                    $row['checked'] = false;
            }           

            $parent_id = $per['parent_id'] == 0 ? -1 : $per['parent_id'];
            $row['pId'] = $parent_id;

            $row['id'] = $per['id'];
            $row['open'] = true;

            $tree[] = $row;
        }

        return $tree;
    }

    /**
     * 更新角色权限
     *
     * @param integer $role_id
     * @param array $perm_ids
     * 
     * @return bool
     */
    public function updatePerms(int $role_id, array $perm_ids)
    {
        $role = $this->roleRepository->find($role_id);
        
        if(empty($role)) {
            return false;
        }

        $role->perms()->sync($perm_ids);

        Cache::rm('rbac_permissions_for_role_'.$role_id);

        return true;
    }
}