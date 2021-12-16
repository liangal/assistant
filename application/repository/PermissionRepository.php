<?php
namespace app\repository;

use app\repository\Repository;

class PermissionRepository extends Repository
{
    public function model() {
    	return 'app\models\Permissions';
    }

    /**
     * 数据分页
     * @param  string $search        [搜索关键词]
     * @param  int    $start         [description]
     * @param  int    $length        [description]
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
        })->order('id','desc')->limit($start, $length)->column('*','id');
    }

    /**
     * 数据分页总数
     * @param  string $search        [搜索关键词]
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
    
    /**
     * 全部数据
     *
     * @return array
     */
    public function all($columns = ['*']) {
        return $this->model->order('sort_order', 'asc')->all();
    }

    /**
     * 菜单数据
     *
     * @return array
     */
    public function getMeus() {
        return $this->model->where('display_menu', 1)->order('sort_order', 'asc')->select();
    }
}