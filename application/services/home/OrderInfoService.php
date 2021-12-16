<?php
namespace app\services\home;

use app\repository\OrderInfoRepository;
use think\Request;

class OrderInfoService
{
    protected $order;

    public function __construct(OrderInfoRepository $order){
        $this->order = $order;
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

    public function findByField($field,$value,$columns = ['*']){
        return $this->order->findByField($field,$value,$columns);
    }

    public function getAll($where){
        return $this->order->selectWhere($where);
    }
    public function getfind($where){
        return $this->order->one($where);
    }
//    public function
}