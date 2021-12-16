<?php
namespace app\services\manage;

use app\repository\TeacherRepository;

class TeacherService
{
    protected $teacher;

    public function __construct(TeacherRepository $teacher){
        $this->teacher = $teacher;
    }

    /**
     * 保存数据
     * @param array $attributes
     */
    public function save(array $attributes){
        return $this->teacher->create($attributes);
    }

    /**
     * 更新数据
     * @param array $attributes
     */
    public function update(int $id,array $attributes){
        return $this->teacher->update($attributes,$id);
    }

    /**
     * 删除收货地址
     * @param int $id
     * @return int
     */
    public function delete(int $id){
        $address = $this->teacher->delete($id);
        return $address;
    }


    /**
     * 通过条件查询数据
     *
     * @param $field    字段名
     * @param $value    字段值
     * @return mixed
     */
    public function find($where,$c=['*']) {

        return $this->teacher->findWhere($where,$c);
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
    public function listForPage($start, $limit)
    {
        return $this->teacher->listForPage($start,$limit);
    }

    /**
     * 数据总数
     *
     * @param string $search    搜索关键词
     * @return number
     */
    public function listForTotal(){
        return $this->teacher->listForTotal();
    }
}