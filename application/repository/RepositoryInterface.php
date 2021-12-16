<?php
namespace app\repository;

/**
 * Interface RepositoryInterface
 * 
 * @author lidahua <253102396@qq.com>
 */
interface RepositoryInterface
{
	/**
     * 检索所有数据
     *
     * @param array $columns
     *
     * @return mixed
     */
    public function all($columns = ['*']);

    /**
     * 检索数据分页
     *
     * @param null $limit
     * @param array $columns
     *
     * @return mixed
     */
    public function paginate($limit = null, $columns = ['*']);

    /**
     * 按ID查找数据
     *
     * @param       $id
     * @param array $columns
     *
     * @return mixed
     */
    public function find($id, $columns = ['*']);

    /**
     * 按字段和值查找数据
     *
     * @param       $field
     * @param       $value
     * @param array $columns
     *
     * @return mixed
     */
    public function findByField($field, $value, $columns = ['*']);

    /**
     * 按多个字段查找数据
     *
     * @param array $where
     * @param array $columns
     *
     * @return mixed
     */
    public function findWhere(array $where, $columns = ['*']);

    /**
     * 按多个字段查找数据
     *
     * @param array $where
     * @param array $columns
     *
     * @return mixed
     */
    public function selectWhere(array $where, $columns = ['*']);

    /**
     * 在一个字段中按多个值查找数据
     *
     * @param       $field
     * @param array $values
     * @param array $columns
     *
     * @return mixed
     */
    public function selectWhereIn($field, array $values, $columns = ['*']);

    /**
     * 通过在一个字段中排除多个值来查找数据
     *
     * @param       $field
     * @param array $values
     * @param array $columns
     *
     * @return mixed
     */
    public function selectWhereNotIn($field, array $values, $columns = ['*']);

    /**
     * 保存一条数据
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function create(array $attributes);

    /**
     * 按ID更新数据
     *
     * @param array $attributes
     * @param       $id
     * @param       $field
     *
     * @return mixed
     */
    public function update(array $attributes, $id, $field = 'id');

    /**
     * 按条件更新数据
     *
     * @param array $attributes
     * @param       $where
     *
     * @return mixed
     */
    public function updateWhere(array $attributes, array $where);

    /**
     * 按ID删除数据
     *
     * @param $id
     *
     * @return int
     */
    public function delete($id);

    /**
     * 按条件删除数据
     *
     * @param $where
     *
     * @return int
     */
    public function deleteWhere(array $where);

    /**
     * 执行SQL查询
     * @param  string $sql sql语句
     * @return mixed
     */
    public function query(string $sql);
}