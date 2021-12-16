<?php
namespace app\services\manage;

use app\repository\AdminsRepository;

class UserInfoServices
{
	protected $adminsRepository;

    public function __construct(AdminsRepository $adminsRepository){
        $this->adminsRepository = $adminsRepository;
    }

    /**
     * 按ID查找数据
     *
     * @param       $id
     * @param array $columns
     *
     * @return array
     */
    public function find($id, $columns = ['*']) {
        return $this->adminsRepository->find($id, $columns);
    }

    /**
     * 更新一条数据
     *
     * @param string $id   编号
     * @param array $data
     * 
     * @return bool
     */
    public function update(string $id, array $data) {
        $this->adminsRepository->update($data, $id);
    }
}