<?php

namespace app\http\controllers\manage\api;

use think\Request;
use app\http\controllers\ApiController;
use app\services\manage\UsersServices;

class Users extends ApiController
{

    protected $userServices;

    public function __construct(UsersServices $userServices){
        $this->userServices = $userServices;
    }

    /**
     * 列表
     * @return [type] [description]
     */
    public function list(Request $request)
    {
        $search = strval($request->param('search'));
        $type = intval($request->param('type'));
        $status = intval($request->param('status'));

        $page = intval($request->param('page'));
        $length = intval($request->param('limit'));
        $start = ($page * $length) - $length;

        $count = $this->userServices->listForTotal($search, $type, $status);
        $data = $this->userServices->listForPage($search, $type, $status, $start, $length);

        $list = array();
        $list['code'] = 0;
        $list['msg'] = '';
        $list['count'] = $count;
        $list['data'] = $data;

        return json($list);
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
 
        $result = $this->userServices->updateStatus($id, ['status'=>$status]);

        if($result)
            return $this->message('操作成功');
        else
            return $this->message('操作失败', 500);
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
            return $this->message('参数错误', 500);
        }

        $result = $this->userServices->update($id,['delete_at'=>time()]);

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
    public function cancelDel(Request $request)
    {
        $id = intval($request->post('id'));

        if (empty($id)) {
            return $this->message('参数错误', 500);
        }

        $result = $this->userServices->update($id,['delete_at'=>0]);

        if(empty($result)) {
            return $this->message('操作失败', 500);
        }

        return $this->message('操作成功');
    }
}
