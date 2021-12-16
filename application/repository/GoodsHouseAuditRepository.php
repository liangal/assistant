<?php
namespace app\repository;

use app\repository\Repository;

class GoodsHouseAuditRepository extends Repository{
    public function model() {
        return 'app\models\GoodsHouseAudit';
    }

    /**
     * 检索列表
     * @param array $columns 检索字段
     * @return mixed
     */
    public function all($columns = ['*']) {
        return $this->model->order('sort', 'asc')->all();
    }

    /**
     * 保存一条数据
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function create(array $attributes) {
        return $this->model->create($attributes);
    }

    /**
     * 数据分页总数
     * @return number
     */
    public function listForTotal()
    {
        return $this->model->count();
    }

    public function getNavByGoodsid($id){
        return $this->model->where('goods_id',$id)->find();
    }

    public function getNavByID($id){
        return $this->model->where('id',$id)->find();
    }
    /**
     * 按ID更新数据
     *
     * @param array $attributes
     * @param       $id
     * @param       $field
     *
     * @return mixed
     */
    public function update(array $attributes, $id, $field = 'id') {
        $attributes['updated_at'] = time();
        return $this->model->where($field, $id)->update($attributes);
    }
}