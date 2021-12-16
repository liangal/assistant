<?php
namespace app\services\manage;

use app\repository\OrderChangeLogsRepository;

class OrderChangeLogsService{
    protected $logs;
    public function __construct(OrderChangeLogsRepository $logs)
    {
        $this->logs = $logs;
    }

    protected $types = [
        'pay_success' => '支付成功',
        'create_order' => '生成订单',
        'refund_price' => '退款',
        'delivery_goods' => '已发货',
    ];

    /**
     * 获取列表
     * @param array $columns
     * @return mixed
     */
    public function getList($where,$columns = ['*']){
        $data = $this->logs->get($where,$columns);
        return $data;
    }


    /**创建订单
     * @param array $data
     * @return mixed
     */
    public function save(array $data){
        $result = $this->logs->create($data);
        return $result;
    }

    /**
     * 更新订单
     * @param array $data
     * @return mixed
     */
    public function update(string $id,array $data){
        $result = $this->logs->update($data,$id);
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
        $article = $this->logs->find($id);

        if(!empty($article)) {
            $result = $this->logs->delete($id);
            if($result) {
                return $result;
            }
        }

        return false;
    }


}