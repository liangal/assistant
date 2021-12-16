<?php
namespace app\services\manage;

use app\repository\GoodsSpecItemsRepository;

class GoodsSpecItemsServices{
    protected $goods;
    public function __construct(GoodsSpecItemsRepository $goods)
    {
        $this->goods = $goods;
    }

    /**
     * 获取商品列表
     * @param array $columns
     * @return mixed
     */
    public function getList($columns = ['*']){
        $data = $this->goods->all($columns);
        return $data;
    }

    /**查询单条
     * @param array $where 查询条件
     * @return mixed
     */
    public function find(array $where,$columns = ['*']){
        $result = $this->goods->one($where,$columns);
        return $result;
    }

    /**
     * 创建商品
     * @param array $data
     * @return mixed
     */
    public function save(array $data){
        $result = $this->goods->create($data);
        return $result;
    }

    /**
     * 更新商品
     * @param array $data
     * @return mixed
     */
    public function update(string $id,array $data){
        $result = $this->goods->update($data,$id);
        return $result;
    }

    public function selectWhere($where, $columns = ['*']){
        return $this->goods->selectWhere($where, $columns);
    }

    /**
     * 删除一条数据
     *
     * @param integer $id   编号
     *
     * @return bool
     */
    public function delete(int $id) {
        $article = $this->goods->find($id);

        if(!empty($article)) {
            $result = $this->goods->delete($id);
            if($result) {
                return $result;
            }
        }

        return false;
    }

    public function deleteWhere(array $where){
        $this->goods->deleteWhere($where);
    }
}