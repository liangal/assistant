<?php
namespace app\repository;

use App\Models\ShimmerLiveshopStoreOrder;
use app\repository\Repository;

class OrderChangeLogsRepository extends Repository{
    public function model() {
        return 'app\models\OrderChangeLogs';
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
     * 查询多条信息
     * @param array $where
     * @return mixed
     */
    public function get($where,$columns = ['*']){
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
        return $bul->order('id', 'asc')->all();
    }


    /**
     * 查询一条记录
     * @param array $where
     * @return mixed
     */
    public function one($where){
        $bul = $this->model;
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