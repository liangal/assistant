<?php
namespace app\services\manage;

use app\repository\OrderChangeLogsRepository;
use app\repository\OrderInfoRepository;
use app\repository\OrderRefundReasonRepository;
use app\repository\OrderRepository;
use app\repository\UsersRepository;
use app\services\ShandePayService;
use app\services\WechatServices;
use EasyWeChat\Factory;
use think\facade\Env;
use think\facade\Log;
class OrderService{
    protected $order;
    public function __construct(OrderRepository $order)
    {
        $this->order = $order;
    }

    protected $payTypes = [
        'yue' => '余额支付',
        'weixin' => '微信支付',
    ];
    protected $refundTypes = [
        1 => '退款',
        2 => '退货',
        3 => '退货退款',
    ];

    /**
     * 获取列表
     * @param array $columns
     * @return mixed
     */
    public function getList($columns = ['*']){

        $data = $this->order->all($columns);
        return $data;
    }

    public function find($columns = ['*']){
        $data = $this->order->find($columns);
        return $data;
    }

    public function listForTotal(string $search,$status,int $type, $start_at, $end_at)
    {
        return $this->order->listForTotal($search, $status,$type,$start_at,$end_at);
    }

    public function listForPage(string $search,$status,int $type,$start_at,$end_at, int $start, int $length)
    {
        $list = $this->order->listForPage($search, $status,$type,$start_at,$end_at, $start, $length);
         foreach ($list as $k=>$v){
             $v->pay_status_tring =  $v->getOrderStatusNameAttribute();
             $v->pay_type_string = $this->payTypes[$v->pay_type];
             if($v->refund_type){
                 $v->refund_type_string = $this->refundTypes[$v->refund_type];
             }
             if($v->refund_reason_id){
                 $reason = (new OrderRefundReasonRepository())->find($v->refund_reason_id);
                 $v->reason_string = $reason->name;
             }

             $fh = 0;
             if($v->paid==1 && $v->status==0 && $v->is_del==0 && $v->refund_status==0 && $v->refund_type==0){
                 $fh =1;
             }
             $v->fh = $fh;
         }
        return $list->toArray();
    }

    /**创建订单
     * @param array $data
     * @return mixed
     */
    public function save(array $data){
        $result = $this->order->create($data);
        return $result;
    }

    /**
     * 更新订单
     * @param array $data
     * @return mixed
     */
    public function update(string $id,array $data){
        $result = $this->order->update($data,$id);
        return $result;
    }


    /**
     * 删除一条数据
     *
     * @param integer $id   编号
     *
     * @return bool
     */
    public function delete(int $id) {
        $article = $this->order->find($id);

        if(!empty($article)) {
            $result = $this->order->delete($id);
            if($result) {
                return $result;
            }
        }

        return false;
    }

    /**
     * 申请退款
     *
     * @param $params
     * @return array
     */
    public function refund($params){

        $model = $this->order->modelRepostory();
        $modeld = $this->order->statusByWhere(6,$model);
        $detailOrder  = $modeld->where('order_id',$params['order_id'])->find();
        if(!$detailOrder){
            return ['code' => 404, 'msg' => '订单不存在'];
        }

        $detailOrder = $detailOrder->toArray();
        if($params['status']==2){
            if(!isset($params['refuse_text']) || empty($params['refuse_text'])){
                return ['code' => 403, 'msg' => '请输入拒绝理由'];
            }
            $res = $this->order->update(['refund_refuse_text'=>$params['refuse_text'],'refund_status'=>3],$detailOrder['id']);
            (new OrderChangeLogsRepository())->create(['oid'=>$detailOrder['id'],'change_type'=>'not_refund','change_message'=>'拒绝退款']);
            if($res){
                $info = (new OrderInfoRepository())->findWhere(['oid'=>$detailOrder['id']])->toArray();
                if($info['goods_type']==2){
                    $course = json_decode($info['goods_info'],true) ;
                    $title = $course['title'];
                }else{
                    $goods = json_decode($info['goods_info'],true) ;
                    $title = $goods['goods']['name'];
                }

                $user = (new UsersRepository())->find($detailOrder['uid']);
                $getApp = (new WechatServices())->miniProgram();
                $data = [
                    'touser'=>$user->wxapp_openid,
                    'template_id'=>'8V_m-dyxkqzJS3krqKO_FC_Wn_PGsaJF0E7eQbh01HE',
                    'data'=>[
                        'amount1'=>"￥{$info['price']}元",
                        'thing2' =>$title,
                        'number3' =>$detailOrder['sand_order_id'],
                        'phrase4' =>$params['refuse_text'],
                    ],
                ];
                $getApp->subscribe_message->send($data);
                return ['code' => 200, 'msg' => '操作成功'];
            }else{
                return ['code' => 201, 'msg' => '操作失败'];
            }

        }elseif($params['status']==1){
            if(!isset($params['total_price']) || empty($params['total_price'])){
                return ['code' => 403, 'msg' => '请输入金额'];
            }
            if($params['total_price']>$detailOrder['total_price']){
                return ['code' => 403, 'msg' => '退款金额不能大于实际支付金额'];
            }

            $newOrderId = \app\services\home\OrderService::createOrderId();

            $res = ShandePayService::refund($newOrderId,$detailOrder['sand_order_id'],$params['total_price'],$detailOrder['refund_reason_wap_explain']);;
            if($res['code'] == 200){
                $this->order->update(['refund_reason_time'=>time()],$detailOrder['id']);
            }
            return ['code' => $res['code'], 'msg' => $res['msg']];
        }
    }

    /**
     * 发货
     *
     * @param $params
     * @return array
     */
    public function deliver($params){
        if(!isset($params['delivery_type']) || !$params['delivery_type']){
            return ['code' => 403, 'msg' => '发货类型不能为空'];
        }
        $model = $this->order->modelRepostory();
        $modeld = $this->order->statusByWhere(1,$model);
        $detailOrder  = $modeld->where('order_id',$params['order_id'])->find();
        if(!$detailOrder){
            return ['code' => 404, 'msg' => '订单不存在'];
        }

        if($params['delivery_type'] == 'express'){
            if(!isset($params['express_id']) || !$params['express_id']){
                return ['code' => 403, 'msg' => '请选择快递公司'];
            }
            if(!isset($params['express_number']) || !$params['express_number']){
                return ['code' => 403, 'msg' => '请输入快递单号'];
            }

        }elseif($params['delivery_type'] == 'send'){
            if(!isset($params['delivery_name']) || !$params['delivery_name']){
                return ['code' => 403, 'msg' => '请输入送货人姓名'];
            }
            if(!isset($params['delivery_mobile']) || !$params['delivery_mobile']){
                return ['code' => 403, 'msg' => '请输入送货人电话'];
            }
        }
        $params['status'] = 1;
        $params['deliver_time'] = time();
        $res = $this->order->updateWhere($params,['order_id'=>$params['order_id']]);
        return ['code' => 200, 'msg' => '操作成功'];

    }

    /**
     * Desc 微信退款
     * @param int $code 状态码-[200:成功,201:失败，...]
     * @return json
     */
    private function wechatRefund($order,$price){

        $wechat = config('wechat.wechatDetail');
        $root_path = Env::get('root_path') . '/public';

        $params = [
            'appid'          => $wechat['app_id'],
            'mch_id'         => $wechat['mch_id'],
            'nonce_str'      => $order['order_id'],
            'key'            => $wechat['key'],
            'out_refund_no'  => $order['order_id'],
            'total_fee'      => 1,
            'refund_fee'     => 1,
            'notify_url'     => config('app.api_url').'main/refund',
            'cert_path'      => $root_path.'/apiclient_cert.pem',
            'key_path'       => $root_path.'/apiclient_key.pem',
        ];

        $app = Factory::payment($params);

//        $app->setSubMerchant($wechat['merchant_id'][$order->merchant_id]);

        unset($params['cert_path']);
        unset($params['key_path']);
        unset($params['key']);

        $result = $app->refund->byOutTradeNumber( $order['order_id'],  $order['order_id'], $params['total_fee'],$params['refund_fee'],$params);
        if ($result['return_code'] != 'SUCCESS' || $result['result_code'] != 'SUCCESS') {
            return ['code' => 201, 'msg' => '支付失败_' . $result['err_code_des']];
        }
        return ['code' => 200, 'msg' => '退款申请成功，请关注订单状态'];
    }
}