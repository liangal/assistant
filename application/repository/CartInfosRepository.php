<?php
namespace app\repository;

use app\repository\Repository;
class CartInfosRepository extends Repository{
    public function model() {
        return 'app\models\CartInfos';
    }


    /**
     * 查询一条记录
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
        return $bul->order('oid', 'asc')->all();
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