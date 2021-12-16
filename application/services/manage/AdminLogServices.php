<?php
namespace app\services\manage;

use app\repository\AdminLogRepository;
use app\repository\AdminsRepository;

class AdminLogServices
{
	protected $adminLogRepository;
	protected $adminsRepository;

    public function __construct(AdminLogRepository $adminLogRepository, AdminsRepository $adminsRepository) {
        $this->adminLogRepository = $adminLogRepository;
        $this->adminsRepository = $adminsRepository;
    }

    /**
     * 检索所有数据
     *
     * @param array $columns
     *
     * @return array
     */
    public function all($columns = ['*']) {
        return $this->adminLogRepository->all($columns);
    }

    /**
     * 数据分页总数
     * 
     * @param  string $search    搜索关键词
     * @param  array  $class_ids 类别
     * 
     * @return int
     */
    public function listForTotal(string $search)
    {
        return $this->adminLogRepository->listForTotal($search);
    }

    /**
     * 数据分页
     * 
     * @param  string $search    搜索关键词
     * @param  array  $class_ids 类别
     * @param  int    $start     
     * @param  int    $length    
     * 
     * @return array
     */
    public function listForPage(string $search, int $start, int $length)
    {
        $logs = $this->adminLogRepository->listForPage($search, $start, $length)->toArray();

        $list = array();

        if(!empty($logs)) {
            $user_ids = array_column($logs, 'user_id');
            
            $users = $this->adminsRepository->selectWhereIn('id', $user_ids)->toArray();
            
            if(!empty($users)) {
                $users = array_pluck($users, 'id');
            }

            foreach($logs as $log) {
                $row = array();
                $row['id'] = $log['id'];
                $row['ip'] = $log['ip'];
                $row['user_name'] = '-';
                $row['browser'] = $log['browser'];
                $row['created_at'] = $log['created_at'];

                $user = array_get($users, $log['user_id']);
                    if(!empty($user)) {
                    $row['user_name'] = $user['name'];
                    if(!empty($user['nickname'])) {
                        $row['user_name'] = $row['user_name'] . '(' . $user['nickname'] . ')';
                    }
                }

                $list[] = $row;
            }
        }
        return $list;
    }
}