<?php
namespace app\http\controllers\api\v1;

use app\http\controllers\ApiController;
use app\http\controllers\manage\api\Users;
use app\repository\CartsRepository;
use app\repository\CourseRepository;
use app\repository\GoodsRepository;
use app\repository\GoodsSpecOptionsRepository;
use app\repository\OrderChangeLogsRepository;
use app\repository\OrderInfoRepository;
use app\repository\OrderRepository;
use app\repository\UsersRepository;
use app\services\home\AuthAuthenticateService;
use app\services\home\CarouselService;
use app\services\home\CourseService;
use app\services\home\GoodsService;
use app\services\manage\CourseNavService;
use app\services\home\LiveService;
use app\services\ShandePayService;
use app\services\WechatServices;
use think\Exception;
use think\facade\Log;
use think\Request;

class Main extends ApiController{
    protected $goods;
    protected $carouselServices;
    protected $liveNav;
    protected $live;
    protected $courseNav;
    protected $course;
    protected $authAuthenticateService;
    public function __construct(Request $request,CarouselService $carouselServices,GoodsService $goods,LiveService $live
        ,CourseNavService $courseNav,CourseService $course,AuthAuthenticateService $authAuthenticateService)
    {
        $this->carouselServices = $carouselServices;
        $this->goods = $goods;
        $this->live = $live;
        $this->courseNav = $courseNav;
        $this->course = $course;
        $this->authAuthenticateService = $authAuthenticateService;
        parent::__construct($request);
    }

    public function initData(Request $request){
        $search = strval($request->param('search'));

        //获取轮播图
        $oss_domain = config('sitesystem.oss_domain');
        $carouselData = $this->carouselServices->listForPage($search);
        if($carouselData){
            foreach ($carouselData as $k=>$v){
                $carouselData[$k]['thumb'] = $oss_domain.$v['thumb'];;
                $carouselData[$k]['sort'] = strval($v['sort']);
            }
        }

        //获取直播展示栏
        $liveData = $this->live->getNav();
        if($liveData){
            $liveData['live_id'] = strval($liveData['live_id']);
        }

        //获取课程导航
        $navData = $this->course->getNav();

       //获取商品导航
        $goodsNav = $this->goods->getNav();

        $data = [
            'banners'=>$carouselData,
            'courseNav' => $navData,
            'liveData' => $liveData,
            'goodsNav' => $goodsNav,
        ];

        return $this->listMessage($data);
    }

    public function verifyToken(Request $request){
        $token = strval($request->post('access_token'));
        if(empty($token)){
           return $this->message('参数错误',401);
        }

        $payload =$this->authAuthenticateService->verifyToken($token);

        if(empty($payload)) {
            throw new \app\exceptions\ApiException('错误token', 401);
        }
        return $this->message('ok',200,['access_token'=>$token,'expires_in'=>$payload['exp']]);
    }

    public function search(Request $request){
        $search = strval($request->post('search'));
//        $category_id = (int)($request->post('category_id'));
        $res = [];
        if($search){
            $goods = $this->goods->search($search);
            $courses = $this->course->search($search);
            if($goods){
                foreach ($goods as $good){
                    $g['id'] = $good['id'];
                    $g['type'] = 1;
                    $g['title'] = $good['title'];
                    $res[] = $g;
                }
            }
            if($courses){
                foreach ($courses as $course){
                    $c['id'] = $course['id'];
                    $c['type'] = 2;
                    $c['title'] = $course['title'];
                    $res[] = $c;
                }
            }
        }
        return $this->message('',200,$res);
    }

    /**
     * 搜索列表
     * @param Request $request
     * @return \think\response\Json
     */
    public function searchList(Request $request){
        $search = strval($request->post('search')) ;
        $type = (int)($request->post('type')) ;
        $sale = (int)($request->post('sale')) ;
        $new = (int)($request->post('new')) ;
        $category_id = (int)($request->post('category_id')) ;

        $page = $this->page;
        $length = $this->limit;
        $start = $this->start;

        $res = [];
        $count = 0;
        if($type==1 ){
               $resData = $this->goods->listForPage($search,$sale,$new,$category_id,$start,$length);
               $count = $this->goods->listForTotal($search);
               if($resData){
                   foreach ($resData as $k =>$data){
                       $resDetail = [
                           'goods_id'=>strval($data['id']),
                           'name'=>$data['title'],
                           'thumb'=>config('sitesystem.oss_domain').$data['thumb'],
                           'price'=> $data['productprice'],
                           'expressprice'=>$data['expressprice'],
                           'marketprice'=>$data['marketprice'],
                           'costprice'=>$data['costprice'],
                           'stock'=>$data['stock'],
                           'sale_num'=>strval($data['sale_num']),
                           'ficti_num'=>strval($data['ficti_num']),
                           'stateon_at'=>date('Y-m-d H:i:s',$data['stateon_at']),

                           'description' => html_entity_decode($data['description']) ,
                       ];
                       $thumbs =  explode(',',$data['thumbs']);
                       foreach ($thumbs as $tk=>$tv){
                           $tv = config('sitesystem.oss_domain').$tv;
                           $thumbs[$tk] = $tv;
                       }
                       $resDetail['thumbs'] = $thumbs;
                       $res[$k] = $resDetail;
                    }
           }

        }else if($type==2 ){
                $resData = $this->course->listForPage($search,$sale,$new,$category_id,$start,$length);
                if($resData){
                    foreach ($resData as $k=>$v){
                        $data['course_id'] = strval($v['id']) ;
                        $data['title'] = $v['title'];
                        $data['thumb'] = config('sitesystem.oss_domain').$v['thumb'];
                        $data['price'] = strval($v['price']) ;
                        $data['expressprice'] = strval($v['expressprice']);
                        $data['ficti_num'] = strval($v['ficti_num']);
                        $data['sale_num'] = strval($v['sale_num']);
                        $data['description'] = $v['description'];
                        $data['status'] = strval($v['status']);
                        $data['statusStr'] = $v['status']==1?'上架':'下架';
                        $data['stateon_at'] = date('Y-m-d H:i:s',$v['stateon_at']);
                        $res[$k]=$data;
                    }
                }
                $count = $this->course->listForTotal($search,$category_id);
        }else{
            return $this->message('参数错误',401);
        }

        return $this->listMessage($res,$count,$page,count($res));
    }

    /**
     * 杉德回调
     * @throws \Exception
     */
    public function sandCallback(){
        log::write("sandCallback=====start=====");
        $pubkey = ShandePayService::loadCert();

        if ($_POST) {
            $sign = $_POST['sign']; //签名
            $signType = $_POST['signType']; //签名方式
            $data = stripslashes($_POST['data']); //支付数据
            $charset = $_POST['charset']; //支付编码
            $result = json_decode($data, true); //data数据
            if (ShandePayService::verify($data, $sign, $pubkey)) {
                //签名验证成功
                try{
                    $orderRepository = new OrderRepository();
                    $order_id = $result['body']['tradeNo'];
                    $orderFind = $orderRepository->findWhere(['sand_order_id'=>$order_id])->toArray();
                    if($orderFind['goods_type']==2){
                        $coursePay = [
                            'paid'=>1,
//                        'transaction_id'=>$params['transaction_id'],
                            'status'=>3,
                            'updated_at'=>time(),
                            'finish_time'=>time(),
                            'pay_time'=>time(),
                        ];
                        $orderRepository->updateWhere($coursePay,['sand_order_id'=>$order_id]);
                        (new OrderChangeLogsRepository())->create(['oid'=>$orderFind['id'],'change_type'=>'order_end','change_message'=>'订单完成']);

                        $info = (new OrderInfoRepository())->findWhere(['oid'=>$orderFind['id']])->toArray();
                        $course = json_decode($info['goods_info'],true) ;

                        $user = (new UsersRepository())->find($orderFind['uid']);
                        $getApp = (new WechatServices())->miniProgram();
                        $data = [
                            'touser'=>$user->wxapp_openid,
                            'template_id'=>'gU5E2XXSIwkmC0qYrqwWKYH-h2XSxDsIquRfIbG0aes',
                            'page'=> "pages/user/course/index",
                            'data'=>[
                                'character_string1'=>$order_id,
                                'thing2' =>$course['title'],
                                'date3' =>date('Y年m月d日 H:i',time()),
                                'amount4' =>"￥{$info['price']}元",
                                'thing5' =>'支付成功请到我的课程内进行观看',
                            ],
                        ];
                        $getApp->subscribe_message->send($data);

                    }else{
                        $orderRepository->updateWhere(['paid'=>1,'updated_at'=>time(),'pay_time'=>time()],['sand_order_id'=>$order_id]);
                        (new OrderChangeLogsRepository())->create(['oid'=>$orderFind['id'],'change_type'=>'pay_success','change_message'=>'支付成功']);
                    }

                    if($orderFind['paid']==0){
                        $info = (new OrderInfoRepository())->selectWhere(['oid'=>$orderFind['id']])->toArray();
                        $goodsRepository = new GoodsRepository();
                        $courseRepository = new CourseRepository();
                        foreach ($info as $k=>$v){
                            if($v['cart_id']){//删除购物车
                                (new CartsRepository())->delete($v['cart_id']);
                            }
                            //增加销量
                            if($orderFind['goods_type']==2){
                                $course = $courseRepository->find($v['type_id']);
                                $sale_num = ($course->sale_num)+1;
                                $courseRepository->update(['sale_num'=>$sale_num],$v['type_id']);
                            }else{
                                $goods =  $goodsRepository->find($v['type_id']);
                                $goodsRepository->update(['sale_num'=>$goods->sale_num+$v['num']],$v['type_id']);
                            }
                        }
                    }

                }catch (\Exception $e){
                    log::write(["sandCallback================msg=================="=>$e->getMessage()],'error');
                }

                log::write("sandCallback================end==================");
                echo "respCode=000000";
                exit;
            } else {
                //签名验证失败
                log::write("sandCallback================验签失败==================");
                exit;
            }
        }
    }

    /**
     * 微信支付回调
     * @return string
     */
    public function payBack(){
        $xml = file_get_contents("php://input");
        $jsonxml = json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA));

        $params = json_decode($jsonxml,true);

        if($jsonxml ){
            if($params['return_code']=='SUCCESS' && $params['result_code']=='SUCCESS'){
                $orderRepository = new OrderRepository();
                $order_id = $params['out_trade_no'];
                $orderFind = $orderRepository->findWhere(['order_id'=>$params['out_trade_no']])->toArray();
                if($orderFind['goods_type']==2){
                    $coursePay = [
                        'paid'=>1,
                        'transaction_id'=>$params['transaction_id'],
                        'status'=>3,
                        'updated_at'=>time(),
                        'finish_time'=>time(),
                        'pay_time'=>time(),
                    ];
                    $orderRepository->updateWhere($coursePay,['order_id'=>$order_id]);
                    (new OrderChangeLogsRepository())->create(['oid'=>$orderFind['id'],'change_type'=>'order_end','change_message'=>'订单完成']);

                    $info = (new OrderInfoRepository())->findWhere(['oid'=>$orderFind['id']])->toArray();
                    $course = json_decode($info['goods_info'],true) ; log::write(['infofofofo'=>$info['goods_info']]);

                    $getApp = (new WechatServices())->miniProgram();
                    $data = [
                        'touser'=>'oEUUS5bJqfqDSTjXIQtwB8Y9FffI',
                        'template_id'=>'gU5E2XXSIwkmC0qYrqwWKYH-h2XSxDsIquRfIbG0aes',
                        'page'=> "pages/user/course/index",
                        'data'=>[
                            'character_string1'=>$order_id,
                            'thing2' =>$course['title'],
                            'date3' =>date('Y年m月d日 H:i',time()),
                            'amount4' =>"￥{$info['price']}元",
                            'thing5' =>'支付成功请到我的课程内进行观看',
                        ],
                    ];
                    $getApp->subscribe_message->send($data);

                }else{
                    $orderRepository->updateWhere(['paid'=>1,'transaction_id'=>$params['transaction_id'],'updated_at'=>time(),'pay_time'=>time()],['order_id'=>$order_id]);
                    (new OrderChangeLogsRepository())->create(['oid'=>$orderFind['id'],'change_type'=>'pay_success','change_message'=>'支付成功']);
                }

                if($orderFind['paid']==0){
                    $info = (new OrderInfoRepository())->selectWhere(['oid'=>$orderFind['id']])->toArray();
                    $goodsRepository = new GoodsRepository();
                    $courseRepository = new CourseRepository();
                    foreach ($info as $k=>$v){
                        if($v['cart_id']){//删除购物车
                            (new CartsRepository())->delete($v['cart_id']);
                        }
                        //增加销量
                        if($orderFind['goods_type']==2){
                            $course = $courseRepository->find($v['type_id']);
                            $courseRepository->update(['sale_num'=>$course->sale_num+1],$v['type_id']);
                        }else{
                            $goods =  $goodsRepository->find($v['type_id']);
                            $goodsRepository->update(['sale_num'=>$goods->sale_num+$v['num']],$v['type_id']);
                        }
                    }
                }

                //返回状态给微信服务器
                return 'SUCCESS';
            }
        }else{
            log::write(['支付回调失败====='=>$params]);
        }
    }

    /**
     * 退款回调
     */
    public function refund(){

        $xml = file_get_contents("php://input");
        $jsonxml = json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA));
        $params = json_decode($jsonxml,true);

        $key = config('wechat.wechatDetail.key');
        $decrypt = base64_decode($params['req_info'], true);
        $req_info = openssl_decrypt($decrypt , 'aes-256-ecb', md5($key), OPENSSL_RAW_DATA);
        $infoxml = json_encode(simplexml_load_string($req_info, 'SimpleXMLElement', LIBXML_NOCDATA));
        $infoParams = json_decode($infoxml,true);

        if($infoParams){
            $orderRepository = new OrderRepository();
            $order_id = $infoParams['out_trade_no'];
            $price = priceFormat($infoParams['refund_fee']);

            $orderFind = $orderRepository->findWhere(['order_id'=>$order_id]);
            $orderRepository->updateWhere(['refund_status'=>2,'refund_price'=>$price,'updated_at'=>time()],['order_id'=>$order_id]);
            (new OrderChangeLogsRepository())->create(['oid'=>$orderFind->id,'change_type'=>'refund_price','change_message'=>'退款成功']);

            $goods = (new OrderInfoRepository())->selectWhere(['oid'=>$orderFind->id]);
            $goodsRepository = new GoodsRepository();
            $optionsRepository = new GoodsSpecOptionsRepository();
            foreach ($goods as $k=>$v){
                $detail = $v->toArray();
                if($detail['goods_type'] ==1){
                     $goods_info = json_decode($detail['goods_info'],true);
                     $good = $goods_info['goods'];
                     $option = $goods_info['specOption'];
                     $goodsDetail = $goodsRepository->find($good['goods_id']);
                     $goodsRepository->update(['stock'=>(int)$goodsDetail->stock + (int)$detail['num']],$good['goods_id']);

                     if($option){
                        $optionDetail = $optionsRepository->find($option['id']);
                        $optionsRepository->update(['stock'=>(int)$optionDetail->stock + (int)$detail['num']],$option['id']);
                    }
                }
            }
        }
    }

    /**
     * 杉德退款
     * @throws \Exception
     */
    public function sandRefund(){
        log::write("sandRefund=====start=====");
        $pubkey = ShandePayService::loadCert();

        if ($_POST) {
            $sign = $_POST['sign']; //签名
            $signType = $_POST['signType']; //签名方式
            $data = stripslashes($_POST['data']); //支付数据
            $charset = $_POST['charset']; //支付编码
            $result = json_decode($data, true); //data数据
            if (ShandePayService::verify($data, $sign, $pubkey)) {
                //签名验证成功
                try{
                    $orderRepository = new OrderRepository();
                    $order_id = $result['body']['oriOrderCode'];
                    $price = priceFormat((int)$result['body']['refundAmount']);

                    $orderFind = $orderRepository->findWhere(['sand_order_id'=>$order_id]);

                    $orderRepository->updateWhere(['refund_status'=>2,'refund_price'=>$price,'updated_at'=>time()],['sand_order_id'=>$order_id]);
                    (new OrderChangeLogsRepository())->create(['oid'=>$orderFind->id,'change_type'=>'refund_price','change_message'=>'退款成功']);

                    $goods = (new OrderInfoRepository())->selectWhere(['oid'=>$orderFind->id]);
                    $goodsRepository = new GoodsRepository();
                    $optionsRepository = new GoodsSpecOptionsRepository();
                    foreach ($goods as $k=>$v){
                        $detail = $v->toArray();
                        if($detail['goods_type'] ==1){
                            $goods_info = json_decode($detail['goods_info'],true);
                            $good = $goods_info['goods'];
                            $option = $goods_info['specOption'];
                            $goodsDetail = $goodsRepository->find($good['goods_id']);
                            $goodsRepository->update(['stock'=>(int)$goodsDetail->stock + (int)$detail['num']],$good['goods_id']);
                            if($option){
                                $optionDetail = $optionsRepository->find($option['id']);
                                $optionsRepository->update(['stock'=>(int)$optionDetail->stock + (int)$detail['num']],$option['id']);
                            }
                        }
                    }

                }catch (\Exception $e){
                   log::write(["sandRefund================msg=================="=>$e->getMessage()]);
                }

                log::write("sandRefund================end==================");
                echo "respCode=000000";
                exit;
            } else {
                //签名验证失败
                log::write("sandRefund================验签失败==================");
                exit;
            }
        }
    }

    /**
     * 签名
     * @return [type] [description]
     */
    public function signature()
    {
        $oss = app('library\AliOSS');
        $data = $oss->signature();

        return $this->message('',200,$data);
    }

}