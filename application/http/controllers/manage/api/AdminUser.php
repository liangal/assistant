<?php

namespace app\http\controllers\manage\api;

use think\Request;
use app\http\controllers\ApiController;
use app\services\manage\AdminServices;

class AdminUser extends ApiController
{
    protected $adminServices;

    public function __construct(AdminServices $adminServices){
        $this->adminServices = $adminServices;
    }

    /**
     * 列表
     * @return [type] [description]
     */
    public function list(Request $request)
    {
        $search = strval($request->param('search'));

        $page = intval($request->param('page'));
        $length = intval($request->param('limit'));
        $start = ($page * $length) - $length;

        $list = array();
        $list['code'] = 0;
        $list['msg'] = '';
        $list['count'] = $this->adminServices->listForTotal($search);
        $list['data'] = $this->adminServices->listForPage($search, $start, $length);

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
        $data = $request->only(['name', 'nickname', 'sex', 'phone', 'role_id']);
        
        $validate = app('\app\http\validate\CreateAdminUser');

        if (!$validate->check($data)) {
            return $this->message($validate->getError(), 500);
        }
        
        $role = $this->adminServices->store($data);

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

        $data = $request->only(['nickname', 'sex', 'phone', 'role_id']);

        $this->adminServices->update($id, $data);

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
        
        $result = $this->adminServices->delete($id);

        if(empty($result)) {
            return $this->message('操作失败', 500);
        }

        return $this->message('操作成功');
    }

    /**
     * 重置密码
     *
     * @param Request $request
     * @return void
     */
    public function reset(Request $request)
    {
        $id = strval($request->post('id'));
        
        if (empty($id)) {
            throw new \app\exceptions\ParameterException();
        }

        $this->adminServices->resetPassword($id);

        return $this->message('操作成功');
    }

    /**
     * 锁定
     *
     * @param Request $request
     * @return void
     */
    public function switch(Request $request)
    {
        $id = strval($request->post('id'));
        $status = intval($request->post('status'));

        if (empty($id)) {
            throw new \app\exceptions\ParameterException();
        }

        $this->adminServices->updateStatus($id, ['status'=>$status]);

        return $this->message('操作成功');
    }
    
    /**
     * 用户基本信息
     *
     * @param Request $request
     * @return void
     */
    public function info(Request $request) {
        $userinfo = $this->adminServices->userInfo();

        if(!empty($userinfo)) {
            return $this->message('',200,$userinfo);
        }

        return $this->message('获取用户信息失败', 500);
    }

    /**
     * 用户角色
     *
     * @param Request $request
     * @return void
     */
    public function role(Request $request) {
        $roles = $this->adminServices->userRole();
        return $this->message($roles);
    }
}
