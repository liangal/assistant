<?php
namespace app\services\manage;

use app\repository\ExpressesRepository;

class ExpressesService{
    protected $expresses;
    public function __construct(ExpressesRepository $expresses)
    {
        $this->expresses = $expresses;
    }

    /**
     * 获取列表
     * @param array $columns
     * @return mixed
     */
    public function getList($where,$columns = ['*']){
        $data = $this->expresses->selectWhere($where,$columns);
        return $data;
    }
}