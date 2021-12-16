<?php
namespace app\http\controllers\api\v1;

use app\http\controllers\ApiController;
use app\repository\CartsRepository;
use app\repository\GoodsSpecsRepository;
use app\repository\OrderInfoRepository;
use app\repository\OrderRepository;
use app\services\home\AddressService;
use app\services\home\CartsService;
use app\services\home\CourseService;
use app\services\home\GoodsService;
use app\services\home\OrderService;
use app\services\manage\GoodsSpecOptionsServices;
use app\services\manage\GoodsSpecsServices;
use app\services\WechatServices;
use Firebase\JWT\JWT;
use think\Db;
use think\facade\Cache;
use think\Request;

class Order extends ApiController{
    protected $order;
    protected $cart;
    protected $course;
    protected $goods;
    protected $spec;
    protected $option;
    protected $address;

    public function __construct(Request $request,CartsService $cart,OrderService $order,CourseService $course,GoodsService $goods
        ,GoodsSpecsServices $spec,GoodsSpecOptionsServices $option,AddressService $address)
    {
        $this->order = $order;
        $this->cart = $cart;
        $this->course = $course;
        $this->goods = $goods;
        $this->spec = $spec;
        $this->option = $option;
        $this->address = $address;
        parent::__construct($request);
    }

    public function myCount(){
        $count = $this->order->mycount();
        return $this->message('成功',200,$count);
    }

    public function list(Request $request){
        $search = strval($request->post('search'));
        $status = $request->post('status')==''?'all':strval($request->post('status'));
        $type = strval($request->post('type'));

        if(!$type==1 || !$type==2){
            return $this->message('参数错误',401);
        }
        $page = $this->page;
        $start = $this->start;
        $limit = $this->limit;

        $list = $this->order->pageList($search,$status,$type,'','',$start,$limit);
        $count = $this->order->listForTotal($search,$status,$type,'','');

        return $this->listMessage($list,$count,$page,count($list));
    }

    /**\
     * 确认订单
     * @param Request $request
     */
    public function confirm(Request $request){
        $goods_type = $request->post('goods_type');
        $num = $request->post('num');
        $goods_id = $request->post('goods_id');
        $spec_item_ids = strval($request->post('spec_item_ids')) ;

        $user = $request->user;
        $res =[];
        $goods = [];
        $goodsArr = [];
        $price_total = 0;
        $price_express = 0;
        $goods_num = 0;
        $courseRes = [];
        if ($goods_type==1){//商品订单

            if($goods_id && $num){
                $goods= $this->goods->selectWhere(['id'=>$goods_id]);
                if(!$goods){
                    return $this->message('无相关商品3',404);
                }
                $goods = $goods->toArray();
            }else{
                $cart= $this->cart->selectWhere(['check'=>1,'uid'=>$user->id]);
                if(!$cart){
                    return $this->message('无相关商品',404);
                }

                foreach ($cart as $ck => $cv){
                   $good = $this->goods->find($cv['goods_id']);
                   if($good){
                       $goods[$ck] = $good->toArray();
                   }else{
                       return $this->message('无相关商品',404);
                   }
                }
            }

            $is_stock = 1;
            //处理商品数据
            foreach ($goods as $k => $v){
                if($num && $goods_id){
                    if($v['stock']<$num){
                        $is_stock=0;
                    }
                    $cartNum = $num;
                    $item_ids = $spec_item_ids;
                }else{
                    if($v['stock']<$cart[$k]['cart_num'])  $is_stock=0;
                    $cartNum = $cart[$k]['cart_num'];
                    $item_ids = $cart[$k]['spec_item_ids'];
                    $v['cart_id'] = $cart[$k]['id'];
                }
                $goodsArr[$k]['goods'] = $this->goods->goodsArr($v);//获取商品
                $goodsArr[$k]['goods']['num'] = $cartNum;

                $goodsArr[$k]['specOption'] = [];
                $option = $this->option->find(['goods_id'=>$v['id']]);;
                if($option){//获取规格项
                    if(!$item_ids){
                        return $this->message('无规格信息',401);
                    }

                    $specOption = $this->option->find(['spec_item_ids'=>$item_ids,'goods_id'=>$v['id']]);
                    if(!$specOption) return $this->message('无规格信息',401);
                    if($specOption->stock<$cartNum) $is_stock=2;

                    $optionArr = [
                        'id' =>strval($specOption['id']),
                        'goods_id' =>strval($specOption['goods_id']),
                        'title' =>strval($specOption['title']),
                        'stock' =>strval($specOption['stock']),
                        'price' =>sprintf("%.2f", $specOption['productprice']),
                        'costprice' =>strval($specOption['costprice']),
                        'sale_num' =>strval($specOption['sale_num']),
                        'unique' =>$specOption['spec_item_ids'],
                    ];
                    $goodsArr[$k]['specOption'] = $optionArr;//获取商品规格
                    $price = (sprintf("%.2f", $specOption['productprice']*$cartNum));
                    $goodsArr[$k]['goods']['price'] = $price;
                }else{
                    $price = (sprintf("%.2f", $v['productprice']*$cartNum));
                }
                $price_express = (float)$v['expressprice']+$price_express;
                $price_total = $price_total + $price;
                $goods_num = $goods_num + $cartNum;
            }
        }elseif($goods_type==2){//课程订单
            if(!$num || !$goods_id){
                return $this->message('参数错误',404);
            }
            $info = (new OrderInfoRepository())->findWhere(['goods_type'=>2,'type_id'=>$goods_id]);
            if($info){
                $model = (new OrderRepository())->modelRepostory();
                $courseModel = (new OrderRepository())->statusByWhere(4,$model);
                $courseOrder = $courseModel->field(['id','order_id','uid','goods_type'])->where('info_id',$info->type_id)->where('uid',$user->id)->find();
                if($courseOrder){
                    return $this->message('此课程已购买，请跳转到我的课程进行观看！',203);
                }
            }

            $course_id = strval($goods_id);
            $course = $this->course->find($course_id);
            if(!$course){
                return $this->message('无相关课程',404);
            }
            $courseRes =  $this->course->courseArr($course);
            $courseRes['num'] =  $num;

            $price_total = $price_total + ($courseRes['price']*$num);
            $price_express = $courseRes['expressprice'];
            $goods_num = $num;

            $goodsArr[]['goods'] = $courseRes;
        }else{
            return $this->message('参数错误',500);
        }
        $key = md5(md5(time()));
        $res['detail'] = $goodsArr;
        $res['goods_type'] = $goods_type;
        $res['key'] = $key;
        $res['price_express'] = $price_express;
        $res['price_goods_total'] = $price_total;
        $res['price_total'] = $price_total + $price_express;
        $res['goods_num'] = $goods_num;
        $res['is_stock'] = $is_stock;

        if($is_stock==0){
            return $this->message('库存不足',200,$res);
        }else if($is_stock==2){
            return $this->message('规格库存不足',200,$res);
        }else{
            Cache::set($key,$res,60*15);
        }

        return $this->message('',200,$res);
    }

    /**
     * 创建订单
     *
     * @param Request $request
     * @return \think\response\Json
     */
    public function save(Request $request){
//        return $this->message('暂无支付方式',201);
        $data = $request->only(['key','address_id','remark']);
        if(!isset($data['key']) || !isset($data['address_id'])){
            return $this->message('参数错误',500);
        }
        if(!$data['key'] || !$data['address_id']){
            return $this->message('参数错误',500);
        }

        $res = $this->order->findByField('key',$data['key']);
        if($res){
            return $this->message('订单已生成',200,['order_id'=>$res->order_id]);
        }
        $cacheRes = Cache::get($data['key']);
        if(!$cacheRes){
            return $this->message('订单异常',401);
        }

        if(!$cacheRes['detail'] && !$cacheRes['course']) {
            return $this->message('订单异常',401);
        }

        $address = $this->address->find(['id'=>$data['address_id']]);
        if($cacheRes['detail'] && !$address) {
            return $this->message('收货地址不存在',401);
        }

        $user = $request->user;
        $cacheRes['order_id'] = OrderService::createOrderId();
        $cacheRes['remark'] = '';
        if(isset($data['remark'])){
            $cacheRes['remark'] = $data['remark'];
        }
        try{
            $order = $this->order->createOrder($cacheRes,$user,$address);
            if (isset($order['code']) && $order['code'] = -1) {
                return $this->message( $order['msg'],203);
            }
        }catch(\Exception $e){
            return $this->message('下单失败，请稍后重试',403);
        }
        return $this->message('下单成功',200,['order_id'=>$cacheRes['order_id'],'key'=>$cacheRes['key']]);

    }

    /**
     * 订单支付
     *
     * @param Request $request
     */
    public function pay(Request $request){
        $order_id = strval($request->post('order_id'));
        if(!$order_id){
            return $this->message('参数错误',401);
        }
        $user = $request->user;
        $this->order->payOrder($order_id,$user->id,$user->wxapp_openid);
    }

    /**
     * 统一下单
     *
     * @param Request $request
     * @return \think\response\Json
     */
    public function unified(Request $request){
        $order_id = strval($request->post('order_id'));
        if(!$order_id){
            return $this->message('参数错误',401);
        }
        $user = $request->user;
        $msg= $this->order->unifiedOrder($order_id,$user->id,$user->wxapp_openid);
        return json($msg);
    }

    public function unifiedShande(){
        $request = Request();
        $order_id = strval($request->post('order_id'));
        $user = $request->user;
        $msg= $this->order->unifiedShande($order_id,$user->id,$user->wxapp_openid);
        return json($msg);
    }

    /**
     * 订单详情
     *
     * @param Request $request
     */
    public function detail(Request $request){
       $order_id = strval($request->post('order_id'));
       $type = intval($request->post('type'));
       if(!$order_id || !$type){
           return $this->message('参数错误',500);
       }
       $res = $this->order->orderDetail($order_id,$type);
       return $this->message('',200,$res);
    }

    /**
     * 取消订单
     *
     * @param Request $request
     * @return \think\response\Json
     */
    public function cancel(Request $request){
        $order_id = strval($request->post('order_id'));
        $type = $request->post('type')?$request->post('type'):1;
        if(!$order_id && !$type){
            return $this->message('参数错误',500);
        }

        $user =$request->user;
        $res = $this->order->cancelOrder($order_id,$type,$user);
        if(!$res){
            return $this->message('取消失败',401);
        }else{
            return $this->message('操作成功');
        }
    }

    /**
     * 确认收货
     *
     * @param Request $request
     * @return \think\response\Json
     */
    public function deliverConfirm(Request $request){
        $order_id = strval($request->post('order_id'));
        if(!$order_id ){
            return $this->message('参数错误',500);
        }

        $user =$request->user;
        $res = $this->order->deliverConfirm($order_id,$user);
        if(!$res){
            return $this->message('收货失败',401);
        }else{
            return $this->message('收货成功');
        }
    }

    public function refundReason(){
       $res = $this->order->refundReason();
       return json($res);
    }

    /**
     * 申请退款
     *
     * @param Request $request
     */
    public function refundAsd(){
      $res = $this->order->refundAsd();
      return json($res);
    }

    /**
     * 删除一条记录
     *
     * @param Request $request
     * @return json
     */
    public function del()
    {
        $res = $this->order->delete();
        return json($res);
    }
}
