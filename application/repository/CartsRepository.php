<?php
namespace app\repository;

use app\repository\Repository;

class CartsRepository extends Repository{
    public function model() {
        return 'app\models\Carts';
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
    public function listForPage($search,$uid,$start,$length)
    {
        return $this->model->where(function ($query) use ($search) {
            $query->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('title', 'like', '%' . $search . '%');
                }
            });
        })->where('uid',$uid)->limit($start, $length)->select();
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
    public function listForTotal($search,$uid)
    {
        return $this->model->where(function ($query) use ($search) {
            $query->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('title', 'like', '%' . $search . '%');
                }
            });
        })->where('uid',$uid)->count();
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
        return $bul->find();
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