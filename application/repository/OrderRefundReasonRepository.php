<?php
namespace app\repository;

use app\repository\Repository;

class OrderRefundReasonRepository extends Repository{
    public function model() {
        return 'app\models\OrderRefundReason';
    }

    /**
     * 列表
     * @param array $columns 检索字段
     * @return mixed
     */
    public function list() {
        return $this->model->where('status',1)->select();
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
     * 按ID更新数据
     *
     * @param array $attributes
     * @param       $id
     * @param       $field
     *
     * @return mixed
     */
    public function update(array $attributes, $id, $field = 'id') {
        return $this->model->where($field, $id)->update($attributes);
    }

}