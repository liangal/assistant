<?php
namespace app\http\controllers\api\v1;

use app\http\controllers\ApiController;
use think\Request;
use app\services\home\CollectionServices;

class Collection extends ApiController
{
    protected $collectionServices;

    public function __construct(CollectionServices $collectionServices){
        $this->collectionServices = $collectionServices;
    }
    
    /**
     * 列表
     *
     * @param Request $request
     * @return void
     */
    public function list(Request $request)
    {
        $user = $request->user;

        $user_id = strval($user->id);
        $search = strval($request->param('search'));
        $channel_id = intval($request->param('channel_id'));

        $page = intval($request->param('page'));
        $length = intval($request->param('limit'));
        $start = ($page * $length) - $length;
        
        $data = $this->collectionServices->listForPage($search, $user_id, $channel_id, $start, $length);
        $count = 0;

        return $this->listMessage($count, $data);
    }

    /**
     * 显示一条记录
     *
     * @param Request $request
     * @return json
     */
    public function show(Request $request)
    {
        $user = $request->user;
        
        $user_id = strval($user->id);
        $object_id = strval($request->post('object_id'));
        
        if (empty($object_id)) {
            return $this->message('参数错误', 500);
        }
        
        $result = $this->collectionServices->show($user_id, $object_id);

        if(empty($result)) {
            return $this->message(0);
        }

        return $this->message(1);
    }
    
    /**
     * 收藏
     *
     * @param Request $request
     * @return void
     */
    public function store(Request $request) 
    {
        $user = $request->user;

        $object_id = strval($request->post('object_id'));
        $channel_id = intval($request->post('channel_id'));
        
        if (empty($object_id) || empty($channel_id)) {
            return $this->message('参数错误', 500);
        }

        $data = array('channel_id'=>$channel_id,'object_id'=>$object_id, 'user_id'=>$user->id);

        $result = $this->collectionServices->store($data);

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
        $user = $request->user;
        
        $user_id = strval($user->id);
        $object_id = strval($request->post('object_id'));
        
        if (empty($object_id)) {
            return $this->message('参数错误', 500);
        }
        
        $result = $this->collectionServices->delete($user_id, $object_id);

        if(empty($result)) {
            return $this->message('操作失败', 500);
        }

        return $this->message('操作成功');
    }
}