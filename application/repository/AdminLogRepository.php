<?php
namespace app\repository;

use app\repository\Repository;
use Cache;

class AdminLogRepository extends Repository
{
    public function model() {
    	return 'app\models\AdminLog';
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
    public function listForPage(string $search, int $start, int $length)
    {
        return $this->model->where(function ($query) use ($search) {            
            $query->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('ip', 'like', '%' . $search . '%');
                }
            });
        })->order('id', 'desc')->limit($start, $length)->select();
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
    public function listForTotal(string $search)
    {
        return $this->model->where(function ($query) use ($search) {            
            $query->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('ip', 'like', '%' . $search . '%');
                }
            });
        })->count();
    }
}