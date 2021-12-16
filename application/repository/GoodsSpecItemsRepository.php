<?php
namespace app\repository;

use app\repository\Repository;

class GoodsSpecItemsRepository extends Repository{
    public function model() {
        return 'app\models\GoodsSpecItems';
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
    public function listForPage(string $search,int $category_id,int $status, $start_at, $end_at, int $start, int $length, $field = 'id', $sort ='desc')
    {
        return $this->model->where(function ($query) use ($search,$category_id,$status,$start_at,$end_at) {
            $query->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('title', 'like', '%' . $search . '%');
                }
            })->where(function ($query) use ($status) {
                if (!empty($status)) {
                    $query->where('status', $status);
                }
            })->where(function ($query) use ($category_id) {
                if (!empty($category_id)) {
                    $query->where('category_id', $category_id);
                }
            })->where(function ($query) use ($start_at) {
                if (!empty($start_at)) {
                    $query->where('stateon_at','>', strtotime($start_at));
                }
            })->where(function ($query) use ($end_at) {
                if (!empty($end_at)) {
                    $query->where('stateon_at','<', strtotime($end_at));
                }
            });
        })->order($field, $sort)->limit($start, $length)->select();
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
    public function listForTotal(string $search,int $category_id,int $status, $start_at, $end_at)
    {
        return $this->model->where(function ($query) use ($search,$category_id,$status,$start_at,$end_at) {
            $query->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('title', 'like', '%' . $search . '%');
                }
            })->where(function ($query) use ($status) {
                if (!empty($status)) {
                    $query->where('status', $status);
                }
            })->where(function ($query) use ($category_id) {
                if (!empty($category_id)) {
                    $query->whereIn('category_id', $category_id);
                }
            })->where(function ($query) use ($start_at) {
                if (!empty($start_at)) {
                    $query->where('stateon_at','>', strtotime($start_at));
                }
            })->where(function ($query) use ($end_at) {
                if (!empty($end_at)) {
                    $query->where('stateon_at','<', strtotime($end_at));
                }
            });
        })->count();
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
     * 查询一条记录
     * @param array $where
     * @return mixed
     */
    public function one($where,$columns = ['*']){
        $bul = $this->model->field($columns);
        if($where){
            foreach ($where as $k=>$v){
                if(is_array($v)){
                    if($v[0] == 'like')$bul=$bul->where($k,'like','%' . $v[1] . '%');
                    if($v[0] == 'in')$bul=$bul->where($k,'in',$v[1]);

                }else{
                    $bul= $bul->where($k,$v);
                }
            }
        }
        return $bul->order('sort', 'asc')->find();
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