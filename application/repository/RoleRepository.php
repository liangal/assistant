<?php
namespace app\repository;

use app\repository\Repository;

class RoleRepository extends Repository
{
    public function model() {
    	return 'app\models\Roles';
    }

    /**
     * 数据分页
     * 
     * @param  string $search        [搜索关键词]
     * @param  int    $start         [description]
     * @param  int    $length        [description]
     * 
     * @return array
     */
    public function listForPage(string $search, int $start, int $length)
    {
        return $this->model->where(function ($query) use ($search) {            
            $query->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('name', 'like', '%' . $search . '%');
                }
            });
        })->order('id','desc')->limit($start, $length)->select();
    }

    /**
     * 数据分页总数
     * 
     * @param  string $search        [搜索关键词]
     * 
     * @return number
     */
    public function listForTotal(string $search)
    {
        return $this->model->where(function ($query) use ($search) {            
            $query->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('name', 'like', '%' . $search . '%');
                }
            });
        })->count();
    }
}