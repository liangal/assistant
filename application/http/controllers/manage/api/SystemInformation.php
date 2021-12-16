<?php

namespace app\http\controllers\manage\api;

use think\Request;
use app\http\controllers\ApiController;
use app\services\manage\SystemInformationServices;

class SystemInformation extends ApiController
{
    protected $systemInformationServices;
    
    public function __construct(SystemInformationServices $systemInformationRepository){
        $this->systemInformationRepository = $systemInformationRepository;
    }

    /**
     * 列表
     * @return [type] [description]
     */
    public function list(Request $request)
    {
        $search = strval($request->param('search'));
        $class_id = intval($request->param('class_id'));
        $status =intval($request->param('status'));

        $page = intval($request->param('page'));
        $length = intval($request->param('limit'));
        $start = ($page * $length) - $length;

        $count = $this->systemInformationRepository->listForTotal($search, $class_id, $status);
        $data = $this->systemInformationRepository->listForPage($search, $class_id, $status, $start, $length);

        return $this->listMessage($count, $data);
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
        $data = $request->only(['class_id', 'title', 'content', 'status']);
        $user_id = $request->user->id;
        $data['release_user_id'] = $user_id;
        $data['last_update_user_id'] = $user_id;

        $validate = app('\app\http\validate\CreateAriticle');

        if (!$validate->check($data)) {
            return $this->message($validate->getError(), 500);
        }
        
        $role = $this->systemInformationRepository->store($data);

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
            return $this->message('参数错误', 500);
        }

        $data = $request->only(['class_id', 'title', 'images', 'source', 'content', 'status', 'sort', 'tag']);
        $user_id = $request->user->id;
        $data['last_update_user_id'] = $user_id;

        $this->systemInformationRepository->update($id, $data);

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
            return $this->message('参数错误', 500);
        }
        
        $result = $this->systemInformationRepository->delete($id);

        if(empty($result)) {
            return $this->message('操作失败', 500);
        }

        return $this->message('操作成功');
    }

    /**
     * 下架上架
     *
     * @param Request $request
     * @return void
     */
    public function switch(Request $request)
    {
        $id = strval($request->post('id'));
        $status = intval($request->post('status'));

        if (empty($id) || empty($status)) {
            return $this->message('参数错误', 500);
        }

        $this->systemInformationRepository->updateStatus($id, ['status'=>$status]);

        return $this->message('操作成功');
    }

    /**
     * 新闻分类
     *
     * @param Request $request
     * @return void
     */
    public function articleClass(Request $request)
    {
        $list = $this->systemInformationRepository->articleClass();
        return $this->message($list);
    }
}
