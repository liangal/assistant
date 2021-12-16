<?php
namespace app\repository;

use app\repository\Repository;

class UsersRepository extends Repository
{
    public function model() {
    	return 'app\models\Users';
    }

//    public function findByField($field, $value, $columns = ['*'])
//    {
//        return $this->model->findByField($field, $value, $columns); // TODO: Change the autogenerated stub
//    }

    public function create(array $attributes)
    {
        return $this->model->create($attributes); // TODO: Change the autogenerated stub
    }

    public function update(array $attributes, $id, $field = 'id')
    {
        return $this->model->where($field, $id)->update($attributes);
    }

    /**
     * 数据分页
     * 
     * @param  string $search     [搜索关键词]
     * @param  int $type          [用户类型]
     * @param  int $status        [账号状态]
     * @param  int    $start      [description]
     * @param  int    $length     [description]
     * 
     * @return array
     */
    public function listForPage(string $search, int $type, int $status, int $start, int $length)
    {
        return $this->model->where(function ($query) use ($search, $type, $status) {            
            $query->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('realname', 'like', '%' . $search . '%')->whereOr('nickname', 'like', '%' . $search . '%')->whereOr('mobile', 'like', '%' . $search . '%');
                }
            })
            ->where(function ($query) use ($type) {
                if (!empty($type)) {
                    $query->where('type', $type);
                }
            })
            ->where(function ($query) use ($status) {
                if (!empty($status)) {
                    $query->where('status', $status);
                }
            });
        })->order('id','desc')->limit($start, $length)->select();
    }

    /**
     * 数据分页总数
     * 
     * @param  string $search     [搜索关键词]
     * @param  int $type          [用户类型]
     * @param  int $status        [账号状态]
     *  
     * @return number
     */
    public function listForTotal(string $search, int $type, int $status)
    {
        return $this->model->where(function ($query) use ($search, $type, $status) {            
            $query->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('realname', 'like', '%' . $search . '%')->whereOr('nickname', 'like', '%' . $search . '%')->whereOr('mobile', 'like', '%' . $search . '%');
                }
            })
            ->where(function ($query) use ($type) {
                if (!empty($type)) {
                    $query->where('type', $type);
                }
            })
            ->where(function ($query) use ($status) {
                if (!empty($status)) {
                    $query->where('status', $status);
                }
            });
        })->count();
    }


    /**
     * 全部数据总数
     */
    public function allTotal()
    {
        return $this->model->where('delete_at','0')->count();
    }

    /**
     * 时间范围数据总数//////(string???  'created_at'=>'create_at')
     */
    public function dayForTotal($d)
    {
        return $this->model->where('delete_at','0')->whereTime('create_at', $d)->count();
    }
}