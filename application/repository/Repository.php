<?php
namespace app\repository;

use app\repository\RepositoryInterface;

abstract class Repository implements RepositoryInterface {

    protected $model;

    public function __construct() {
        $this->makeModel();
    }

    /**
     * 指定模型类名称
     *
     * @return mixed
     */
    abstract function model();

    /**
     * 检索所有数据
     *
     * @param array $columns
     *
     * @return mixed
     */
    public function all($columns = ['*']) {
        return $this->model->field($columns)->all();
    }

    /**
     * 查询分页数据
     * @param array $where
     * @return mixed
     */
    public function getAll($where,$orderBy='id',$start=0, $length=10000){

        $bul = $this->model;
        if($where){
            foreach ($where as $k=>$v){
                if(is_array($v)){
                    $key = array_key_first($v);
                    if(isset($v['like']))$bul=$bul->where($k,'like','%' . $v['like'] . '%');
                    elseif(isset($v['in']))$bul=$bul->whereIn($k,$v['in']);
                    else $bul = $bul->where($k,$key,$v[$key]);
                }else{
                    $bul= $bul->where($k,$v);
                }
            }
        }

        return $bul->order($orderBy, 'desc')->limit($start, $length)->select();
    }

    /**
     * 查询分页总数
     * @param array $where
     * @return mixed
     */
    public function pageCount($where,$start, $length){
        $bul = $this->model;
        if($where){
            foreach ($where as $k=>$v){
                if(is_array($v)){
                    $key = array_key_first($v);
                    if(isset($v['like']))$bul=$bul->where($k,'like','%' . $v['like'] . '%');
                    elseif(isset($v['in']))$bul=$bul->whereIn($k,$v['in']);
                    else $bul = $bul->where($k,$key,$v[$key]);
                }else{
                    $bul= $bul->where($k,$v);
                }
            }
        }

        return $bul->count();
    }

    /**
     * 检索数据分页
     *
     * @param null $limit
     * @param array $columns
     *
     * @return mixed
     */
    public function paginate($limit = null, $columns = ['*']) {
        return $this->model->field($columns)->limit($limit)->select();
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
        return $this->model->field($columns)->find($id);
    }

    /**
     * 按字段和值查找一条数据
     *
     * @param       $field
     * @param       $value
     * @param array $columns
     *
     * @return mixed
     */
    public function findByField($field, $value, $columns = ['*']) {
        return $this->model->field($columns)->where($field, $value)->find();
    }

    /**
     * 按多个字段查找一条数据
     *
     * @param array $where
     * @param array $columns
     *
     * @return mixed
     */
    public function findWhere(array $where, $columns = ['*']) {
        return $this->model->field($columns)->where($where)->find();
    }

    /**
     * 按多个字段查找数据
     *
     * @param array $where
     * @param array $columns
     *
     * @return mixed
     */
    public function selectWhere(array $where, $columns = ['*']) {
        return $this->model->where($where)->field($columns)->select();
    }

    /**
     * 在一个字段中按多个值查找数据
     *
     * @param       $field
     * @param array $values
     * @param array $columns
     *
     * @return mixed
     */
    public function selectWhereIn($field, array $values, $columns = ['*']) {
        return $this->model->field($columns)->whereIn($field, $values)->select();
    }

    /**
     * 通过在一个字段中排除多个值来查找数据
     *
     * @param       $field
     * @param array $values
     * @param array $columns
     *
     * @return mixed
     */
    public function selectWhereNotIn($field, array $values, $columns = ['*']) {
        return $this->model->field($columns)->whereNotIn($field, $values)->select();
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

        $das= $this->model->where($field, $id)->update($attributes);
        return $das;
    }

    /**
     * 按条件更新数据
     *
     * @param array $attributes
     * @param       $where
     *
     * @return mixed
     */
    public function updateWhere(array $attributes, array $where) {
        return $this->model->where($where)->update($attributes);
    }

    /**
     * 按ID删除数据
     *
     * @param $id
     *
     * @return int
     */
    public function delete($id) {
        return $this->model->destroy($id);
    }

    /**
     * 按条件删除数据
     *
     * @param $where
     *
     * @return int
     */
    public function deleteWhere(array $where) {
        return $this->model->where($where)->delete();
    }

    /**
     * 执行SQL查询
     * @param  string $sql [description]
     * @return [type]      [description]
     */
    public function query(string $sql) {
        return Db::query($sql);
    }
    
    /**
     * 初始化模型
     * @return Model
     * @throws RepositoryException
     */
    public function makeModel() {
        $model = app($this->model());
        return $this->model = $model;
    }
}