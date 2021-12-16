<?php
namespace app\http\controllers\api\v1;

use app\http\controllers\ApiController;
use app\services\home\CartsService;
use app\services\home\GoodsService;
use app\services\manage\GoodsSpecOptionsServices;
use think\Request;

class Carts extends ApiController{
    protected $carts;
    protected $goods;
    protected $option;
    public function __construct(Request $request,CartsService $carts,GoodsService $goods,GoodsSpecOptionsServices $option)
    {
        $this->carts = $carts;
        $this->goods = $goods;
        $this->option = $option;
        parent::__construct($request);
    }

    public function list(Request $request){
       $search = $request->post('search');
       $page = $this->page;
       $start = $this->start;
       $length = $this->limit;
       $user = $request->user;

       $list = $this->carts->listForPage($search,$user->id,$start,$length);
       $count =  $this->carts->listForTotal($search,$user->id);

       return $this->listMessage($list,$count,$page,count($list));
    }

    public function count(Request $request){
        $search = '';
        $user = $request->user;
        $count =  $this->carts->listForTotal($search,$user->id);
        return $this->message('成功',200,['count'=>$count]);
    }

    public function save(Request $request){
        $data = $request->only(['goods_id','unique_id','num']);
        $type = $request->post('type')?(int)$request->post('type'):1;
        if(!isset($data['goods_id']) || !isset($data['unique_id']) || !isset($data['num'])){
            return $this->message('参数错误',401);
        }
        if(!$data['goods_id'] || !$data['num']){
            return $this->message('参数错误',401);
        }

        $result = $this->carts->save($data,$request->user->id,$type);
        return $result;
    }

    public function delete(Request $request){
            $cart_id = $request->post('cart_id');
        if(!$cart_id){
            return $this->message('参数错误',401);
        }
        $res = $this->carts->delete($cart_id);

        if(!$res){
            return $this->message('操作失败',500);
        }else{
            return $this->message('操作成功',200);
        }
    }

    public function checkGoods(Request $request){
        $cart_id = $request->post('cart_id');
        $check= $request->post('check');
        $check= $check==1?$check:0;
        if(!$cart_id){
            return $this->message('参数错误',401);
        }
        $cart_id_arr = explode(',',$cart_id);
        foreach ($cart_id_arr as $item){
            $result = $this->carts->update(['check'=>$check],$item);
            if(!$result){
                return $this->message('系统出错，请稍等',500);
            }
        }
        return $this->message('操作成功',200);
    }
}