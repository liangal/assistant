<?php
namespace app\http\controllers\api\v1;

use app\http\controllers\ApiController;
use app\services\home\GoodsService;
use app\services\manage\GoodsSpecItemsServices;
use app\services\manage\GoodsSpecOptionsServices;
use app\services\manage\GoodsSpecsServices;
use think\Request;

class Goods extends ApiController{
    protected $goods;

    public function __construct(Request $request,GoodsService $goods)
    {
        $this->goods = $goods;
        parent::__construct($request);
    }

    public function list(Request $request){
        $search = strval($request->param('search'));
        $category_id = intval($request->param('category_id'));
        $sale = (int)($request->param('sale'));
        $new= (int)($request->param('new'));
        $page = $this->page;
        $length = $this->limit;
        $start = $this->start;

        $goods = $this->goods->listForPage($search, $sale, $new,$category_id,$start, $length);
        $goodsCount = $this->goods->listForTotal($search,$category_id);
        $res = [];
        if($goods){
            foreach ($goods as $k=>$v){
                $res[$k] = $this->goods->goodsArr($v);
            }
        }

        return $this->listMessage($res,$goodsCount,$page,count($goods));
    }

    //获取分类
    public function getCategory(Request $request){
       $data = $this->goods->getCategory();
       return $this->message('',200,$data) ;
    }

    //获取导航
    public function getNav(){
        $goodsNav = $this->goods->getNav();
        return $this->listMessage($goodsNav,count($goodsNav));
    }

    //商品详情
    public function detail(Request $request){
        $goods_id = (int)($request->param('goods_id'));
        if(empty($goods_id)){
            return $this->message('参数错误',500);
        }
        $result = $this->goods->goodsDetail($goods_id);
//        $this->sp

        return $this->message('',200,$result);
    }
}
