<?php
namespace app\services\manage;

use app\repository\CartInfosRepository;

class CartInfosService{
    protected $info;
    public function __construct(CartInfosRepository $info)
    {
        $this->info = $info;
    }

    /**
     * 获取列表
     * @param array $columns
     * @return mixed
     */
    public function getList($where,$columns = ['*']){
        $data = $this->info->get($where,$columns);
        return $data;
    }

    /**
     * 创建
     * @param array $data
     * @return mixed
     */
    public function save(array $data){
        $result = $this->info->create($data);
        return $result;
    }

    /**
     * 更新
     * @param array $data
     * @return mixed
     */
    public function update(string $id,array $data){
        $result = $this->info->update($data,$id);
        return $result;
    }


    /**
     * 删除一条数据
     *
     * @param integer $id   编号
     *
     * @return bool
     */
    public function delete(int $id) {
        $article = $this->info->find($id);

        if(!empty($article)) {
            $result = $this->info->delete($id);
            if($result) {
                return $result;
            }
        }

        return false;
    }
}