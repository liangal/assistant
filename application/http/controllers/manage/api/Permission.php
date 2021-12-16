<?php

namespace app\http\controllers\manage\api;

use think\Request;
use app\http\controllers\ApiController;

use app\services\manage\PermissionServices;

class Permission extends ApiController
{
    protected $permissionServices;

    public function __construct(PermissionServices $permissionServices){
        $this->permissionServices = $permissionServices;
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
        $permissions = $this->permissionServices->all()->toArray();

        $list = array();
        $list['code'] = 200;
        $list['msg'] = '';
        $list['count'] = count($permissions);

        $data = array('permissions'=>array(), 'menus'=>array());

        if (!empty($permissions)) {
            $data['permissions'] = $permissions;

            $menus = $this->permissionServices->getMenuOption($permissions);

            $data['menus'] = $menus;
        }

        $list['data'] = $data;

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
        $data = $request->only(['name', 'menuUrl', 'sort_order', 'display_menu', 'parent_id', 'icon', 'description']);
        
        if(empty($data['description'])) {
            return $this->message('权限名称不能为空?', 500);
        }

        if($data['display_menu'] == 1 && empty($data['menuUrl'])) {
            return $this->message('菜单URL不能为空?', 500);
        }
        
        $permission = $this->permissionServices->store($data);

        if(empty($permission)) {
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
            return $this->message('参数错误?', 500);
        }

        $data = $request->only(['name', 'menuUrl', 'sort_order', 'display_menu', 'parent_id', 'icon', 'description']);
        
        if(empty($data['description'])) {
            return $this->message('权限名称不能为空?', 500);
        }

        if($data['display_menu'] == 1 && empty($data['menuUrl'])) {
            return $this->message('菜单URL不能为空?', 500);
        }
        
        $result = $this->permissionServices->update($id, $data);

        if(empty($result)) {
            return $this->message('操作失败', 500);
        }

        return $this->message('操作成功');
    }

    /**
     * 删除权限
     *
     * @param Request $request
     * @return void
     */
    public function delete(Request $request) 
    {
        $id = intval($request->post('id'));
        
        if (empty($id)) {
            throw new \app\exceptions\ParameterException();
        }
        
        $result = $this->permissionServices->delete($id);

        if(empty($result)) {
            return $this->message('操作失败', 500);
        }

        return $this->message('操作成功');
    }
}
