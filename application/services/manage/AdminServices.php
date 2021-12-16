<?php
namespace app\services\manage;

use app\repository\AdminsRepository;
use think\facade\Cache;

class AdminServices
{
	protected $adminsRepository;

    public function __construct(AdminsRepository $adminsRepository){
        $this->adminsRepository = $adminsRepository;
    }

    /**
     * 按ID查找数据
     *
     * @param       $id
     * @param array $columns
     *
     * @return array
     */
    public function find($id, $columns = ['*']) {
        return $this->adminsRepository->find($id, $columns);
    }

    /**
     * 存储一条数据
     *
     * @param array $data
     * @return void
     */
    public function store(array $data) {
        $user = $this->adminsRepository->findByField('name', $data['name'], ['id']);
        if(!empty($user)) {
            throw new \app\exceptions\ApiException('已存在的用户.');
        }

        $fields = array('name'=>$data['name'], 'nickname'=>$data['nickname'], 'sex'=>$data['sex'], 'phone'=>$data['phone']);
        $fields['password'] = md5('888888');
        $user = $this->adminsRepository->create($fields);

        if(!empty($user)) {
            $user->attachRole($data['role_id']);
        }

        return $user;
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
        $fields = array('nickname'=>$data['nickname'], 'sex'=>$data['sex'], 'phone'=>$data['phone']);
        
        $user = $this->find($id);

        if(empty($user)) {
            throw new \app\exceptions\ParameterException();
        }

        $result = $this->adminsRepository->update($fields, $id);

        $user->detachRoles(false);
        
        $user->attachRole($data['role_id']);

        Cache::rm('rbac_roles_for_user_'.$id);
    }

    /**
     * 删除一条数据
     *
     * @param integer $id   编号
     * 
     * @return bool
     */
    public function delete(int $id) {
        return $this->adminsRepository->delete($id);
    }

    /**
     * 更新一条数据
     *
     * @param string $id   编号
     * @param array $data
     * 
     * @return bool
     */
    public function resetPassword(string $id) {
        $fields = array('password'=>md5('888888'));
        return $this->adminsRepository->update($fields, $id);
    }
    
    /**
     * 更新一条数据状态
     *
     * @param string $id   编号
     * @param array $data
     * 
     * @return bool
     */
    public function updateStatus(string $id, $data) {
        return $this->adminsRepository->update($data, $id);
    }

    /**
     * 检索所有数据
     *
     * @param array $columns
     *
     * @return array
     */
    public function all($columns = ['*']) {
        return $this->adminsRepository->all($columns);
    }

    /**
     * 数据分页总数
     * 
     * @param  string $search        [搜索关键词]
     * 
     * @return int
     */
    public function listForTotal(string $search)
    {
        return $this->adminsRepository->listForTotal($search);
    }

    /**
     * 数据分页
     *
     * @param string $search 
     * @param integer $start
     * @param integer $length
     * 
     * @return array
     */
    public function listForPage(string $search, int $start, int $length)
    {
        $list = $this->adminsRepository->listForPage($search, $start, $length)->load('roles');

        $users = array();

        foreach($list as $user) {
            $row = array();

            $row['id'] = $user->id;
            $row['name'] = $user->name;
            $row['nickname'] = $user->nickname;
            $row['sex'] = $user->sex;
            $row['phone'] = $user->phone;
            $row['status'] = $user->status;
            $row['created_at'] = $user->created_at;
            $row['updated_at'] = $user->updated_at;
            $row['role_id'] = "";

            if(!$user->roles->isEmpty())
            {
                $role = array_first($user->roles);
                $row['role_id'] = $role->id;
            }

            $users[] = $row;
        }

        return $users;
    }

    /**
     * 用户信息
     *
     * @return array
     */
    public function userInfo() {
        $user = request()->user;

        $info = array();

        if(!empty($user)) {
            $info = array(
                'userId'=>$user->id, 
                'username'=>$user->name, 
                'nickName'=>$user->nickname, 
                'avatar'=>$user->avatar, 
                'sex'=>$user->sex, 
                'phone'=>$user->phone, 
                'email'=>$user->email, 
                'emailVerified'=>$user->emailVerified,
                'createTime'=>$user->created_at, 
                'updated_at'=>$user->updated_at,
                'authorities' => array() 
            );

            $permissions = array();
            $user_roles = $user->cachedRoles();

            if(!$user_roles->isEmpty()) {
                $user_role = array_first($user_roles);
                if(!$user_role->pivot->isEmpty()) {
                    $role_id = $user_role->pivot->role_id;
                    $permissions = cache('rbac_permissions_for_role_' . $role_id);
                    
                    if(empty($permissions)){
                        $roleRepository = app('app\repository\RoleRepository');
                        $role = $roleRepository->find($role_id);
                        $permissions = $role->cachedPermissions()->toArray();
                    }
                    else
                    {
                        $permissions = $permissions->toArray();
                    }
                }
            }

            if(!empty($permissions)) {
                foreach($permissions as $perm) {
                    //if($perm['display_menu'] == 0) {
                    if(!empty($perm['name'])){
                        $info['authorities'][] = array('authority'=>$perm['name']);
                    }
                }               
            }
        }

        return $info;
    }

    /**
     * 用户角色
     *
     * @return void
     */
    public function userRole()
    {
        $roleRepository = app('app\repository\RoleRepository');

        $list = $roleRepository->all(['id', 'name']);

        return $list;
    }
}