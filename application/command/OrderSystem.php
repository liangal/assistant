<?php

namespace app\command;

use app\models\Goods;
use app\repository\GoodsRepository;
use app\repository\OrderChangeLogsRepository;
use app\repository\OrderInfoRepository;
use app\repository\OrderRepository;
use app\services\home\OrderInfoService;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class OrderSystem extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('OrderSystem')->setDescription('订单任务 orderSystem');
        // 设置参数
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln("TestCommand:");
        $this->unpaid($output);
    }


    public function unpaid(Output $output){

        $order = new OrderRepository();
        $where = ['paid'=>0,'is_del'=>0,'order_expire'=>['<'=>time()]];
        $output->writeln("OrderSystem Command:");

        $orderList = $order->getAll($where);
        $info = new OrderInfoRepository();
        $goods = new GoodsRepository();
        $log = new OrderChangeLogsRepository();
        if($orderList){
            foreach ($orderList as $k=>$v){
                $infoDetail = $info->selectWhere(['oid'=>$v->id]);
                foreach ($infoDetail as $ik=>$iv){
                    $detail = $iv->toArray();
                    if($detail['goods_type'] ==1){
                        $goods_info = json_decode($detail['goods_info'],true);
                        $good = $goods_info['goods'];
                        $option = $goods_info['specOption'];
                        $goodsDetail = $goods->find($good['goods_id']);
                        $goods->update(['stock'=>(int)$goodsDetail->stock + (int)$detail['num']],$good['goods_id']);

                        if($option){
                            $optionDetail = $info->find($option['id']);
                            $info->update(['stock'=>(int)$optionDetail->stock + (int)$detail['num']],$option['id']);
                        }
                    }
                }

                $res = $order->update(['is_del'=>1,'deleted_at'=>time()],$v->id);
                $log->create(['oid'=>$v->id,'change_type'=>'cancel_order','change_message'=>'取消订单','uid'=>$v->uid]);
                if($res){
                    $output->writeln("关闭订单成功--order_id=".$v->id);
                }else{
                    $output->writeln("关闭订单失败--order_id=".$v->id);
                }
            }
        }

        $output->writeln("OrderSystem end....");
    }
}
