<?php
namespace app\models;

use think\Model;

class Order extends Model
{

    protected $pk = 'id';

    /**
     * 获取订单状态文本
     */
    public function getOrderStatusNameAttribute()
    {
        $result = '';
        if ($this->is_del == 1) {
            return '已关闭';
        }
        if ($this->paid == 0 && $this->status == 0) {
            $result = '未支付';
        } else if ($this->paid == 1 && $this->status == 0 && ($this->refund_status == 0 || $this->refund_status == 3)) {
            $result = '未发货';
        } else if ($this->paid == 1 && $this->status == 1 && ($this->refund_status == 0 || $this->refund_status == 3)) {
            $result = '待收货';
        } else if ($this->paid == 1 && $this->status == 2 && ($this->refund_status == 0 || $this->refund_status == 3)) {
            $result = '待评价';
        } else if ($this->paid == 1 && $this->status == 3 && $this->refund_status == 0) {
            $result = '已完成';
        } else if ($this->paid == 1 && $this->refund_status == 1) {
            $result = '退款中';
        } else if ($this->paid == 1 && $this->refund_status == 2) {
            $result = '已退款';
        }

        return $result;
    }

    /**
     * 获取订单状态值
     */
    public function getOrderStatus()
    {
        $result = '';
        if ($this->is_del == 1) {
            return -1;
        }
        if ($this->paid == 0 && $this->status == 0) {
            $result = 0;
        } else if ($this->paid == 1 && $this->status == 0 && ($this->refund_status == 0 || $this->refund_status == 3)) {
            $result = 1;
        } else if ($this->paid == 1 && $this->status == 1 && ($this->refund_status == 0 || $this->refund_status == 3)) {
            $result = 2;
        } else if ($this->paid == 1 && $this->status == 2 && ($this->refund_status == 0 || $this->refund_status == 3)) {
            $result = 3;
        } else if ($this->paid == 1 && $this->status == 3 && $this->refund_status == 0) {
            $result = 4;
        } else if ($this->paid == 1 && $this->refund_status == 1) {
            $result = 6;
        } else if ($this->paid == 1 && $this->refund_status == 2) {
            $result = 7;
        }

        return $result;
    }
}