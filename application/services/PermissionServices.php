<?php
namespace app\services;

use app\repository\PermissionRepository;

class PermissionServices
{
	protected $permissionRepository;

    public function __construct(PermissionRepository $permissionRepository){
        $this->permissionRepository = $permissionRepository;
    }

    /**
     * 检索所有数据
     *
     * @param array $columns
     *
     * @return mixed
     */
    public function all($columns = ['*']) {
        return $this->permissionRepository->all($columns);
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
        return $this->permissionRepository->paginate($limit, $columns);
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
        return $this->permissionRepository->find($id, $columns);
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
        return $this->permissionRepository->listForPage($search, $start, $length);
    }
}