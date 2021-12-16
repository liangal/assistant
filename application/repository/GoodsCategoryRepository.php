<?php
namespace app\repository;

use app\repository\Repository;

class GoodsCategoryRepository extends Repository{

    public function model() {
        return 'app\models\GoodsCategory';
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
    public function listForPage(string $search , int $start, int $length)
    {
        return $this->model->where(function ($query) use ($search) {
            $query->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('name', 'like', '%' . $search . '%');
                }
            });
        })->order('id', 'desc')->limit($start, $length)->select();
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
    public function listForTotal(string $search)
    {
        return $this->model->where(function ($query) use ($search) {
            $query->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('ip', 'like', '%' . $search . '%');
                }
            });
        })->count();
    }

    /**
     * 检索列表
     * @param array $columns 检索字段
     * @return mixed
     */
    public function all($type=0,$columns = ['*']) {
        return $this->model->where('goods_type',$type)->order('sort', 'Desc')->all();
    }

    /**
     * 保存一条数据
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function create(array $attributes) {
        $attributes['created_at'] = time();
        $attributes['updated_at'] = time();
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
        $attributes['updated_at'] = time();
        return $this->model->where($field, $id)->update($attributes);
    }
}