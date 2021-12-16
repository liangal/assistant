<?php
namespace app\services\home;

use app\models\OrderRefundReason;
use app\repository\CartsRepository;
use app\repository\CourseRepository;
use app\repository\ExpressesRepository;
use app\repository\GoodsRepository;
use app\repository\GoodsSpecOptionsRepository;
use app\repository\OrderChangeLogsRepository;
use app\repository\OrderInfoRepository;
use app\repository\OrderRefundReasonRepository;
use app\repository\OrderRepository;

use app\services\ShandePayService;
use app\services\WechatServices;
use think\Db;
use think\db\Connection;
use think\facade\Log;
use think\Request;
use think\db\Query;
class OrderService
{
    protected $order;
    protected $log;
    protected $info;
    protected $goods;
    protected $course;
    protected $option;
    protected $wechat;

    public function __construct(OrderRepository $order,OrderChangeLogsRepository $log,OrderInfoRepository $info,
                                GoodsRepository $goods,CourseRepository $course,GoodsSpecOptionsRepository $option,WechatServices $wechat){
        $this->order = $order;
        $this->log = $log;
        $this->info = $info;
        $this->goods = $goods;
        $this->course = $course;
        $this->option = $option;
        $this->wechat = $wechat;
    }

    protected function msgCode($code=200,$msg,$data=[]){
        return $res = [
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ];
    }

    /**
     * 按ID查找数据
     *
     * @param       $id
     * @param array $columns
     *
     * @return mixed
     */
    public function find($id, $columns = ['*']) {
        return $this->order->find($id, $columns);
    }

    /**
     * 通过字段查找数据
     *
     * @param $field
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findByField($field,$value,$columns = ['*']){
       return $this->order->findByField($field,$value,$columns);
    }

    /**
     * 数据分页
     *
     * @param  string $search    搜索关键词
     * @param  int    $status    支付类型
     * @param  int    $type      商品类型
     * @param  int    $start_at  开始时间
     * @param  int    $end_at    结束时间
     * @param  int    $start
     * @param  int    $length
     *
     * @return array
     */
    public function pageList(string $search,$status,$type,$start_at,$end_at,$start,$length)
    {
        $data = $this->listForPage($search,$status,$type,$start_at,$end_at,$start,$length);
        $res = [];
        if($data){

            foreach ($data as $k=>$v){
                $info_ids = explode(',',$v['info_id']);
                $goods = [];
                foreach ($info_ids as $infoKey => $infoValue){
                    $infoWhere = [
                        'oid'=>$v['id'],
                        'goods_type'=>$type,
                        'type_id'=>$infoValue,
                    ];
                    $info = $this->info->findWhere($infoWhere);
                    if($info){
                        $info= $info->toArray();
                        $info2 = json_decode($info['goods_info'],true);

                        if($type ==2){
                            $goods[]['goods'] = $info2;
                        }else{
                            $goods[] = $info2;
                        }
                    }
                }
                $res[$k]['pay_str'] = $v->getOrderStatusNameAttribute();
                $res[$k]['pay_status'] = strval($v->getOrderStatus());
                $res[$k]['order_id'] = $v['order_id'];
                $res[$k]['uid'] = strval($v['uid']);
                $res[$k]['goods_type'] = strval($v['goods_type']);
                $res[$k]['nickname'] = $v['nickname'];
                $res[$k]['mobile'] = strval($v['mobile']);
                $res[$k]['user_address'] = $v['user_address'];
                $res[$k]['total_num'] = $v['total_num'];
                $res[$k]['total_price'] = $v['total_price'];
                $res[$k]['total_postage'] = $v['total_postage'];
                $res[$k]['paid'] = strval($v['paid']);
                $res[$k]['remark'] = $v['remark'];
                $res[$k]['pay_time'] = $v['pay_time']!=0?date('Y-m-d',$v['pay_time']):'0';
                $res[$k]['created_at'] = date('Y-m-d',$v['created_at']);
                $res[$k]['updated_at'] = date('Y-m-d',$v['updated_at']);
                $res[$k]['detail'] = $goods;
            }
        }

        return $res;
    }

    /**
     * 数据分页总数
     *
     * @param  string $search    搜索关键词
     * @param  int    $status    状态
     * @param  int    $tag       标识
     *
     * @return number
     */
    public function listForTotal(string $search,$status,int $type, $start_at, $end_at)
    {
        $request = Request();
        $user =$request->user;
        $model = $this->order->modelRepostory();
        $count = $model->where(function ($query) use ($search,$start_at,$end_at) {
            $query->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('order_id', 'like', '%' . $search . '%');
                }
            })->where(function ($query) use ($start_at) {
                if (!empty($start_at)) {
                    $query->where('add_time','>', strtotime($start_at));
                }
            })->where(function ($query) use ($end_at) {
                if (!empty($end_at)) {
                    $query->where('add_time','<', strtotime($end_at));
                }
            });
        })->where('goods_type',$type)->where('uid',$user->id)->where('is_del',0);

        if($status!=''){
            $count = $this->order->statusByWhere($status,$count);
        }
        $counte= $count->count();
        return $counte;
    }

    public function mycount(){
        $request = Request();
        $model = $this->order->modelRepostory();

        $unPay = $this->order->statusByWhere(0,$model)->where('uid',$request->user->id)->count();
        $unDeliver = $this->order->statusByWhere(1,$model)->where('uid',$request->user->id)->count();
        $unReceived = $this->order->statusByWhere(2,$model)->where('uid',$request->user->id)->count();
        $refundCount = $this->order->statusByWhere(5,$model)->where('uid',$request->user->id)->count();

        $count = [
            'unPay' =>$unPay,
            'unDeliver' =>$unDeliver,
            'unReceived' =>$unReceived,
            'refundCount' =>$refundCount,
        ];

        return $count;
    }

    /**
     * 生成唯一订单号
     * @return string
     */
    public static function createOrderId()
    {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 10000);
        $orderId =  $msectime . mt_rand(10000000, 99999999);

        return $orderId;
    }

    /**
     * 创建订单
     *
     * @param array $data
     * @param $user
     * @param $address
     * @return array
     */
    public function createOrder(array $data,$user,$address){

        $orderTable = Db::table('pt_order');
        $infoTable = Db::table('pt_order_info');
        $goodsTable = Db::table('pt_goods');
        $optionTable = Db::table('pt_goods_spec_options');
        Db::startTrans();
        $detail = $data['detail'];
        $infoId = '';

        $s = strtotime(date('Y-m-d').'00:00:00');
        $l = strtotime(date('Y-m-d').'23:59:59');
        $logData = $this->log->getAll(['change_type'=>'cancel_order','created_at'=>['<'=>$l],'created_at'=>['>'=>$s],'uid'=>$user->id],['id']);

        if(count($logData)>=3){
            Db::rollback();
            return ['code'=>-1,'msg'=>'当日取消订单次数过多，请联系客服！'];
        }

        $orderRes = $orderTable->insert([
            'order_id' => $data['order_id'],
            'uid'=>$user->id,
            'goods_type'=>$data['goods_type'],
            'key'=>$data['key'],
            'nickname'=>$address['realname'],
            'mobile'=>$address['mobile'],
            'user_address'=>$address['province'].' '.$address['city'].' '.$address['district'].' '.$address['address_info'],
            'total_num'=>$data['goods_num'],
            'total_price'=>$data['price_total'],
            'total_postage'=>$data['price_express'],
            'order_expire'=>strtotime('+20 minute'),
            'pay_type'=>'weixin',
            'remark'=>$data['remark'],
            'created_at'=>time(),
            'updated_at'=>time(),
        ]);
        if(!$orderRes){
            Db::rollback();
            return ['code'=>-1,'msg'=>'添加订单失败'];
        }
        $oid = $orderTable->getLastInsID();

        foreach ($detail as $k=>$v){
            $good = $v['goods'];
            if($data['goods_type']==1){
//                if($num && $goods_id){
                    if($good['stock']<$good['num']){
                        return ['code'=>-1,'msg'=>'库存不足'];
                    }elseif($v['specOption'] && $v['specOption']['stock']<$good['num']){
                        return ['code'=>-1,'msg'=>'规格库存不足'];
                    }

                    $info = [
                        'oid' => $oid,
                        'goods_type' => 1,
                        'type_id' => $good['goods_id'],
                        'price' => $good['price'],
                        'cart_id' => $good['cart_id'],
                        'goods_info' => json_encode($v) ,
                        'num' => $good['num'] ,
                    ];
                    $goodsRes =$infoTable->insert($info);
                    if(!$goodsRes){
                        Db::rollback();
                        return ['code'=>-1,'msg'=>'保存商品信息失败'];
                    }

                    $infoId .= $good['goods_id'].',';

                    //减少库存
                   $goodsUp = $goodsTable->where(['id'=>$good['goods_id']])->update(['stock'=>($good['stock']-$good['num'])]);
                    if(!$goodsUp){
                        Db::rollback();
                        return ['code'=>-1,'msg'=>'库存不足'];
                    }

                    if(isset($v['specOption']) && $v['specOption']){
                        $optionField = ['stock'=>((int)$v['specOption']['stock']-(int)$good['num'])];
                        $optionUp = $optionTable->where(['id'=>$v['specOption']['id']])->update($optionField);
                        if(!$optionUp){
                            Db::rollback();
                            return ['code'=>-1,'msg'=>'规格库存不足'];
                        }
                    }

            }elseif ($data['goods_type']==2) {

                $info = [
                    'oid' => $oid,
                    'goods_type' => 2,
                    'type_id' => $good['course_id'],
                    'price' => $good['price'],
                    'goods_info' => json_encode($good) ,
                ];

               $infoRes = $this->info->create($info);
               if(!$infoRes){
                   Db::rollback();
                   return ['code'=>-1,'msg'=>'保存课程信息失败'];
               }
               $infoId .= $infoRes->type_id.',';
            }
        }
        $infoId = rtrim($infoId,',');
        $orderUp = $orderTable->where(['id'=>$oid])->update(['info_id'=>$infoId]);
        if(!$orderUp){
            Db::rollback();
            return ['code'=>-1,'msg'=>'更新商品信息失败'];
        }

        $this->log->create(['oid'=>$oid,'change_type'=>'create_order','change_message'=>'生成订单','uid'=>$user->id]);

        Db::commit();
    }

    //杉德支付统一下单
    public function unifiedShande(string $order_id,$uid,$openid){
        $cacheOrderId = cache('order_'.$order_id);
        if($cacheOrderId){
            return $this->msgCode(200,'');
        }else{
            cache('order_'.$order_id,$order_id,3);
        }

        $orderOne = $this->order->one(['order_id'=>$order_id,'uid'=>$uid,'paid'=>0,'is_del'=>0,'status'=>0,'refund_status'=>0,'order_expire'=>['>',time()]]);
        if(!$orderOne){
            return $this->msgCode(404,'订单异常');
        }
        $info = $this->info->one(['oid'=>$orderOne->id]);
        if(!$info){
            return $this->msgCode(404,'商品信息错误');
        }
        $price = $orderOne->total_price;
        $timeOut = date('YmdHis',$orderOne->order_expire);

        $order_code = self::createOrderId();
        $result = ShandePayService::unified($order_code,$price,$openid,$timeOut);
        if($result['code']!='200'){
           return $this->msgCode($result['code'],'下单失败，请稍后重试！');
        }

        $this->order->updateWhere(['sand_order_id'=>$order_code],['order_id'=>$order_id]);
        $res = json_decode($result['data']->params) ;

        $data = [
            'appId'=>$res->appId,
            'timeStamp'=>time(),
            'nonceStr' =>$res->nonceStr,
            'package' =>$res->package,
//            'signType' =>'MD5',
            'signType' =>$res->signType,
            'paySign' =>$res->paySign,
            'order_id' =>$order_id,
        ];

        return $this->msgCode(200,'',$data);
    }


    /**
     * 微信统一下单
     *
     * @param string $order_id
     * @param $uid
     * @param $openid
     * @return array
     */
    public function unifiedOrder(string $order_id,$uid,$openid){
        $orderOne = $this->order->one(['order_id'=>$order_id,'uid'=>$uid,'paid'=>0,'is_del'=>0,'status'=>0,'refund_status'=>0,'order_expire'=>['>',time()]]);
        if(!$orderOne){
            return $this->msgCode(404,'订单异常');
        }
        $info = $this->info->one(['oid'=>$orderOne->id]);
        if(!$info){
            return $this->msgCode(404,'商品信息错误');
        }

        $price = $orderOne->total_price;
        $app = $this->wechat->payMent();
        $data = [
            'body' => '喜刷艺术',
            'out_trade_no' => $order_id,
            'total_fee' => price_bcmul($orderOne->total_price),
            'notify_url' => config('app.notify_url'), // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            'trade_type' => 'JSAPI', // 请对应换成你的支付方式对应的值类型
            'openid' => $openid,
        ];
        $result = $app->order->unify($data);
       if($result['return_code']!='SUCCESS'){
           log::write(['下单失败'=>$result]);
           return $this->msgCode('201','下单失败');
       }

       $data = [
           'appId'=>$result['appid'],
           'timeStamp'=>time(),
           'nonceStr' =>$result['nonce_str'],
           'package' =>'prepay_id='.$result['prepay_id'],
           'signType' =>'MD5',
       ];

        $sign = $this->wechat->wechatSign($data);
        $data['paySign'] = $sign;

        return $this->msgCode(200,'',$data);
    }

    /**
     * 下单
     *
     * @param string $order_id 订单id
     * @param $uid 用户id
     */
    public function payOrder(string $order_id,$uid,$openid){
        $orderOne = $this->order->one(['order_id'=>$order_id,'uid'=>$uid,'paid'=>0,'is_del'=>0,'status'=>0,'refund_status'=>0,'order_expire'=>['>',time()]]);
        if(!$orderOne){
            return $this->msgCode(404,'订单异常');
        }
        $info = $this->info->one(['oid'=>$orderOne->id]);
        if(!$info){
            return $this->msgCode(404,'商品信息错误');
        }
        $info = $info->toArray();
        $goods = json_decode($info['goods_info'],true)['goods'];
        ThirdService::confirm($goods,$openid,$order_id,1000);

    }

    /**
     * 数据分页
     *
     * @param  string $search    搜索关键词
     * @param  int    $start
     * @param  int    $length
     *
     * @return array
     */
    public function listForPage(string $search,$status,int $type, $start_at, $end_at, int $start, int $length)
    {
        $request = Request();
        $user =$request->user;
        $model = $this->order->modelRepostory();;
        $list = $model->where(function ($query) use ($search,$start_at,$end_at) {
            $query->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('order_id', 'like', '%' . $search . '%');
                }
            })->where(function ($query) use ($start_at) {
                if (!empty($start_at)) {
                    $query->where('add_time','>', strtotime($start_at));
                }
            })->where(function ($query) use ($end_at) {
                if (!empty($end_at)) {
                    $query->where('add_time','<', strtotime($end_at));
                }
            });
        })->where('goods_type',$type)->where('uid',$user->id)->where('is_del',0);
        if($status!=''){
            $list = $this->order->statusByWhere($status,$list);
        }
        $liste = $list->order('updated_at', 'desc')->limit($start, $length)->select();
        return $liste;
    }

    /**
     * 订单详情
     * @param int $order_id 订单order_id
     * @return array
     */
    public function orderDetail($order_id,$type){
        $request = Request();
        $user =$request->user;

        $detailOrder=  $this->order->findWhere(['order_id'=>$order_id,'goods_type'=>$type,'uid'=>$user->id]);

        $res = [];
        if($detailOrder){
            $detailOrder2= $detailOrder->toArray();
            $info_ids = explode(',',$detailOrder['info_id']);
            $goods = [];
            foreach ($info_ids as $infoKey => $infoValue){
                $infoWhere = [
                    'oid'=>$detailOrder2['id'],
                    'goods_type'=>$type,
                    'type_id'=>$infoValue,
                ];
                $info = $this->info->findWhere($infoWhere);
                if($info){
                    $info= $info->toArray();
                    $info2 = json_decode($info['goods_info'],true);

                    if($type ==2){
                        $goods[]['goods'] = $info2;
                    }else{
                        $goods[] = $info2;
                    }
                }
            }
            $express = '';
            $delivery_type_str = '';
            if($detailOrder['delivery_type']=='express'){
               $expressObj = (new ExpressesRepository())->find($detailOrder['express_id']);
                $express = $expressObj->name;
                $delivery_type_str = '发货';
            }
            if($detailOrder['delivery_type']=='send'){
                $delivery_type_str = '送货';
            }

            if($detailOrder2['refund_reason_id']){
                $reason = (new OrderRefundReasonRepository())->find($detailOrder2['refund_reason_id']);
                $res['reason_string'] = $reason->name;
            }

            $refundCancel = 0;
            if($detailOrder2['refund_status']==3 || $detailOrder2['refund_status']==2){
                $refundCancel = 1;
            }
            $res['pay_str'] = $detailOrder->getOrderStatusNameAttribute();
            $res['pay_status'] = strval($detailOrder->getOrderStatus());
            $res['delivery_name'] = strval($detailOrder2['delivery_name']);
            $res['delivery_mobile'] = strval($detailOrder2['delivery_mobile']);
            $res['delivery_type'] = strval($detailOrder2['delivery_type']);
            $res['delivery_type_str'] = $delivery_type_str;
            $res['express_number'] = strval($detailOrder2['express_number']);
            $res['express'] = $express;

            $res['mark'] = strval($detailOrder2['mark']);
            $res['pay_type'] = strval($detailOrder2['pay_type']);
            $res['order_id'] = $detailOrder2['order_id'];
            $res['uid'] = strval($detailOrder2['uid']);
            $res['goods_type'] = strval($detailOrder2['goods_type']);
            $res['nickname'] = $detailOrder2['nickname'];
            $res['mobile'] = strval($detailOrder2['mobile']);
            $res['user_address'] = $detailOrder2['user_address'];
            $res['total_num'] = $detailOrder2['total_num'];
            $res['total_price'] = $detailOrder2['total_price'];
            $res['total_postage'] = $detailOrder2['total_postage'];
            $res['paid'] = strval($detailOrder2['paid']);
            $res['remark'] = $detailOrder2['remark'];
            $res['refund_type'] =strval($detailOrder2['refund_type']);
            $res['refund_price'] =$detailOrder2['refund_price'];
            $res['refund_reason_time'] =$detailOrder2['refund_reason_time']!=0?date('Y-m-d H:i',$detailOrder2['refund_reason_time']):'0';
            $res['refund_cancel'] =$refundCancel;
            $res['refund_refuse_text'] =$detailOrder2['refund_refuse_text'];

            $res['pay_time'] = $detailOrder2['pay_time']!=0?date('Y-m-d',$detailOrder2['pay_time']):'0';
            $res['created_at'] = date('Y-m-d',$detailOrder2['created_at']);
            $res['updated_at'] = date('Y-m-d',$detailOrder2['updated_at']);
            $res['order_expire'] = $detailOrder2['order_expire'];
            $res['detail'] = $goods;
        }
        return $res;
    }

    /**
     * 取消订单
     *
     * @param string $order_id
     * @return bool|mixed
     */
    public function cancelOrder(string $order_id,$type,$user){
        $model = $this->order->modelRepostory();
        if($type ==2){
            $model = $this->order->cancelByWhere(0,$model);
        }
        $detailOrder  = $model->where('uid',$user->id)->where('order_id',$order_id)->find();

        if($detailOrder){
            if($type==2 && $detailOrder->refund_status==1){
                $res = $this->order->update(['refund_status'=>0,'refund_type'=>0,'refund_reason_wap_img'=>'','refund_reason_wap_explain'=>''],$detailOrder->id);
                if($res){
                    $this->log->create(['oid'=>$detailOrder->id,'change_type'=>'cancel_refund','change_message'=>'撤销退款','uid'=>$user->id]);
                }
            }elseif ($detailOrder->refund_status==0){
                $res = $this->order->update(['is_del'=>1,'deleted_at'=>time()],$detailOrder->id);

                if($res){
                    $this->log->create(['oid'=>$detailOrder->id,'change_type'=>'cancel_order','change_message'=>'取消订单','uid'=>$user->id]);
                }
            }

            $goods =  $this->info->selectWhere(['oid'=>$detailOrder->id]);
            foreach ($goods as $k=>$v){
                $detail = $v->toArray();
                if($detail['goods_type'] ==1){
                    $goods_info = json_decode($detail['goods_info'],true);
                    $good = $goods_info['goods'];
                    $option = $goods_info['specOption'];
                    $goodsDetail = $this->goods->find($good['goods_id']);
                    $this->goods->update(['stock'=>(int)$goodsDetail->stock + (int)$detail['num']],$good['goods_id']);

                    if($option){
                        $optionDetail = $this->info->find($option['id']);
                        $this->info->update(['stock'=>(int)$optionDetail->stock + (int)$detail['num']],$option['id']);
                    }
                }
            }

            return $res;
        }else{
            return false;
        }
    }

    /**
     * 确认收货
     *
     * @param $order_id
     * @param $user
     * @return bool|mixed
     */
    public function deliverConfirm($order_id,$user){
        $model = $this->order->modelRepostory();
        $modeld = $this->order->statusByWhere(2,$model);
        $detailOrder  = $modeld->where('uid',$user->id)->where('order_id',$order_id)->find();
        if($detailOrder){
            $res = $this->order->update(['status'=>3],$detailOrder->id);
            if($res){
                $this->log->create(['oid'=>$detailOrder->id,'change_type'=>'order_end','change_message'=>'订单完成']);
            }
            return $res;
        }else{
            return false;
        }
    }

    public function refundReason(){
       $reason = (new OrderRefundReasonRepository())->list();
        return $this->msgCode(200,'',$reason);
    }

    /**
     * 申请退款
     *
     * @return array
     */
    public function refundAsd(){
        $request = Request();

        $order_id = strval($request->post('order_id'));
        $type = intval($request->post('type'));
        $refund_reason_id = strval($request->post('refund_reason_id'));
        $explain = strval($request->post('explain'));
        $images = strval($request->post('images'));
        if(!$order_id || !$type || !$refund_reason_id || empty($explain)){
            return $this->msgCode(401,'参数错误');
        }

        $user = $request->user;
        $model = $this->order->modelRepostory();
        $detailOrder  = $model->where('uid',$user->id)->where('order_id',$order_id)->find();

        if($detailOrder){
            if($detailOrder->paid ==1 && $detailOrder->status<=1 && $detailOrder->refund_status==0){
                $res = $this->order->update([
                    'refund_status'=>1,
                    'refund_type'=>$type,
                    'refund_reason_wap_img'=>$images,
                    'refund_reason_wap_explain'=>$explain,
                    'refund_reason_id'=>$refund_reason_id,
                ],$detailOrder->id);
                if($res){
                    $this->log->create(['oid'=>$detailOrder->id,'change_type'=>'refund_price','change_message'=>'申请退款']);
                    return $this->msgCode(200,'');
                }else{
                    return $this->msgCode(201,'申请退款失败');
                }
            }else{
                return $this->msgCode(403,'非法操作！');
            }

        }else{
            return $this->msgCode(404,'订单不存在');
        }
    }

    public function delete(){
        $request = Request();
        $order_id = strval($request->post('order_id'));

        if (empty($order_id)) {
            return $this->msgCode( 500,'参数错误');
        }

        $user = $request->user;
        $model = $this->order->modelRepostory();
        $modeld = $this->order->statusByWhere(4,$model);
        $detailOrder  = $modeld->where('uid',$user->id)->where('order_id',$order_id)->find();
        if (empty($detailOrder)) {
            return $this->msgCode( 403,'订单异常');
        }

        $result = $this->order->updateWhere(['is_del'=>1,'deleted_at'=>time()],['order_id'=>$order_id]);

        if(empty($result)) {
            return $this->msgCode( 201,'操作失败');
        }

        return $this->msgCode(200,'操作成功');
    }
}
