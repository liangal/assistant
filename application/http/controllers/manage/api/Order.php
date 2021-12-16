<?php
namespace app\http\controllers\manage\api;

use app\http\controllers\ApiController;
use app\services\home\OrderInfoService;
use app\services\manage\CartInfosService;
use app\services\manage\GoodsService;
use app\services\manage\OrderChangeLogsService;
use app\services\manage\OrderService;
use app\services\manage\UsersServices;
use think\Request;

class Order extends ApiController{
    protected $order;
    protected $users;
    protected $goods;
    protected $info;
    protected $logs;
    public  function __construct(OrderService $order,UsersServices $users,GoodsService $goods,OrderInfoService $info,OrderChangeLogsService $logs)
    {
        $this->order = $order;
        $this->users = $users;
        $this->goods = $goods;
        $this->info = $info;
        $this->logs = $logs;
    }

    /**
     * 商品订单列表
     *
     * @param Request $request
     * @param string $status2
     * @return \think\response\Json
     */
    public function getList(Request $request,$status2=''){
        $search = strval($request->param('order_id'));
        $status =strval($request->param('status'));
        $start_at = $request->param('start_at');
        $end_at =$request->param('end_at');
     
        $page = intval($request->param('page'));
        $length = intval($request->param('limit'));
        $start = ($page * $length) - $length;

        $count = $this->order->listForTotal($search, $status,1,$start_at,$end_at);
        $data= $this->order->listForPage($search, $status,1,$start_at,$end_at,$start,$length);
        if($data){

            foreach ($data as $k=>$v){
                $data[$k]['created_at'] = date('Y-m-d H:i:s',$v['created_at']);
                $infos = $this->info->getAll(['oid'=>$v['id']])->toArray();
                $logs = $this->logs->getList(['oid'=>$v['id']])->toArray();
                $user = $this->users->find(['id'=>$v['uid']],'id,nickname,mobile');

                $data[$k]['created_at'] =$v['created_at']?date('Y-m-d H:i:s',$v['created_at']):0;
                $data[$k]['pay_time'] = $v['pay_time']?date('Y-m-d H:i:s',$v['pay_time']):0;
                $data[$k]['deliver_time'] =$v['deliver_time']? date('Y-m-d H:i:s',$v['deliver_time']):0;
                $data[$k]['finish_time'] =$v['finish_time']? date('Y-m-d H:i:s',$v['finish_time']):0;

                $data[$k]['user'] = $user;
                $data[$k]['orderLogs'] = $logs;

                foreach ($infos as $ii => $iv){
                    $goods_info = json_decode($iv['goods_info']);
                    if($goods_info->specOption){
                        $infos[$ii]['price'] = $goods_info->specOption->price;
                    }
                    $infos[$ii]['goods_info'] = $goods_info;
                }
                $data[$k]['infos'] = $infos ;
            }
        }

        $list = array();
        $list['code'] = 0;
        $list['msg'] = '';
        $list['count'] = $count;
        $list['data'] = $data;

        return json($list);
    }

    /**
     * 课程订单列表
     *
     * @param Request $request
     * @return \think\response\Json
     */
    public function courseList(Request $request){
        $search = strval($request->param('order_id'));
        $status =$request->param('status');
        $start_at = $request->param('start_at');
        $end_at =$request->param('end_at');

        $page = intval($request->param('page'));
        $length = intval($request->param('limit'));
        $start = ($page * $length) - $length;

        $count = $this->order->listForTotal($search, $status,2,$start_at,$end_at);
        $data= $this->order->listForPage($search, $status,2,$start_at,$end_at,$start,$length);
        if($data){

            foreach ($data as $k=>$v){
                $data[$k]['created_at'] = date('Y-m-d H:i:s',$v['created_at']);
                $infos = $this->info->getfind(['oid'=>$v['id']])->toArray();
                $logs = $this->logs->getList(['oid'=>$v['id']])->toArray();

                $data[$k]['created_at'] =$v['created_at']?date('Y-m-d H:i:s',$v['created_at']):0;
                $data[$k]['pay_time'] = $v['pay_time']?date('Y-m-d H:i:s',$v['pay_time']):0;
                $data[$k]['orderLogs'] = $logs;
                $data[$k]['infos']= json_decode($infos['goods_info']) ;

            }
        }

        $list = array();
        $list['code'] = 0;
        $list['msg'] = '';
        $list['count'] = $count;
        $list['data'] = $data;

        return json($list);
    }

    /**
     * 退款
     *
     * @param Request $request
     */
    public function orderRefund(Request $request){
        $params = $request->only(['status','order_id','refuse_text','total_price']);

        if(!isset($params['order_id']) || empty($params['order_id'])){
            return $this->message('非法操作', 201);
        }
        if(!isset($params['status']) || empty($params['status'])){
            return $this->message('请选择操作类型', 500);
        }
        $res = $this->order->refund($params);
        return json($res);
    }

    /**
     * 发货
     *
     * @param Request $request
     */
    public function deliver(Request $request){
        $params = $request->only(['delivery_type','delivery_name','delivery_mobile','express_number','express_id','order_id']);
        $res = $this->order->deliver($params);
        return json($res);
    }


    /**
     * 更新订单状态
     *
     * @param Request $request
     * @return \think\response\Json
     */
    public function upStatus(Request $request){
        $data = $request->only(['status']);
        $id = strval($request->post('id'));
        if (empty($id)) {
            return $this->message('参数错误', 500);
        }

        $result = $this->order->update($id,$data);

        if(empty($result)) {
            return $this->message('操作失败', 500);
        }

        return $this->message('操作成功');
    }

    public function update(Request $request){
        $id = strval($request->post('id'));
        if (empty($id)) {
            return $this->message('参数错误', 500);
        }

        $data = $request->only(['total_price', 'total_postage', 'pay_price','pay_postage']);
        if(!$data['total_price'])  return $this->message('请输入商品总价', 500);
        if(!$data['total_postage'])  return $this->message('请输入原始邮费', 500);
        if(!$data['pay_price'])  return $this->message('请输入实际支付金额', 500);
        if(!$data['pay_postage'])  return $this->message('请输入实际支付邮费', 500);

        $result = $this->order->update($id,$data);

        if(empty($result)) {
            return $this->message('操作失败', 500);
        }

        return $this->message('操作成功');
    }

    /**
     * 更新备注
     * @param Request $request
     * @return \think\response\Json
     */
    public function updateMark(Request $request){
        $id = strval($request->post('id'));
        if (empty($id)) {
            return $this->message('参数错误', 500);
        }
        $data = $request->only(['mark']);
        $result = $this->order->update($id,$data);
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
        $id = intval($request->post('id'));

        if (empty($id)) {
            return $this->message('参数错误', 500);
        }

        $result = $this->order->update($id,['is_del'=>1,'deleted_at'=>time()]);

        if(empty($result)) {
            return $this->message('操作失败', 201);
        }

        return $this->message('操作成功');
    }
}