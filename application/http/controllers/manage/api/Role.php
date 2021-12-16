<?php

namespace app\http\controllers\manage\api;

use think\Request;
use app\http\controllers\ApiController;

use app\services\manage\RoleServices;

class Role extends ApiController
{
    protected $roleService;

    public function __construct(RoleServices $roleService){
        $this->roleService = $roleService;
    }

    /**
     * 列表
     *
     * @param Request $request
     * 
     * @return json
     */
    public function list(Request $request)
    {
        $search = strval($request->param('search'));

        $start = intval($request->param('start'));
        $length = intval($request->param('length'));

        $list = array();
        $list['code'] = 0;
        $list['msg'] = '';
        $list['count'] = $this->roleService->listForTotal($search);
        $list['data'] = $this->roleService->listForPage($search, $start, $length);

        return json($list);
    }

    /**
     * 保存
     *
     * @param Request $request
     * 
     * @return json
     */
    public function store(Request $request)
    {
        $data = $request->only(['name', 'description']);
        
        $validate = app('\app\http\validate\Role');

        if (!$validate->check($data)) {
            throw new \app\exceptions\ParameterException($validate->getError());
        }
        
        $role = $this->roleService->store($data);

        if(empty($role)) {
            return $this->message('操作失败', 500);
        }

        return $this->message('操作成功');
    }

    /**
     * 更新
     *
     * @param Request $request
     * 
     * @return json
     */
    public function update(Request $request) {
        $id = strval($request->post('id'));
        
        if (empty($id)) {
            throw new \app\exceptions\ParameterException();
        }

        $data = $request->only(['name', 'description']);

        $validate = app('\app\http\validate\Role');

        if (!$validate->check($data)) {
            throw new \app\exceptions\ParameterException($validate->getError());
        }
        
        $result = $this->roleService->update($id, $data);

        if(empty($result)) {
            return $this->message('操作失败', 500);
        }

        return $this->message('操作成功');
    }

    /**
     * 删除一条记录
     *
     * @param Request $request
     * @return json
     */
    public function delete(Request $request)
    {
        $id = intval($request->post('id'));
        
        if (empty($id)) {
            throw new \app\exceptions\ParameterException();
        }
        
        $result = $this->roleService->delete($id);

        if(empty($result)) {
            return $this->message('操作失败', 500);
        }

        return $this->message('操作成功');
    }

    /**
     * 分配权限
     *
     * @param Request $request
     * @return json
     */
    public function assignPermissions(Request $request) {
        $id = intval($request->post('id'));
        
        if (empty($id)) {
            throw new \app\exceptions\ParameterException();
        }

        $list = $this->roleService->permissions($id);

        return json($list);
    }

    /**
     * 更新角色权限
     *
     * @param Request $request
     * @return void
     */
    public function updateRolePermissions(Request $request)
    {
        $id = intval($request->post('role_id'));
        $perm_ids = strval($request->post('permission_ids'));
        
        $perm_ids = str_replace('[', '', $perm_ids);
        $perm_ids = str_replace(']', '', $perm_ids);

        $perm_ids = explode(',', $perm_ids);

        if (empty($id)) {
            throw new \app\exceptions\ParameterException();
        }

        $result = $this->roleService->updatePerms($id, $perm_ids);

        if(empty($result)) {
            return $this->message('操作失败', 500);
        }

        return $this->message('操作成功');
    }
}