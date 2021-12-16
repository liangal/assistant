<?php
namespace app\services\manage;

use app\repository\GoodsCategoryRepository;
use app\repository\GoodsNavRepository;

class GoodsNavService{
    protected $goodsNav;
    public function __construct(GoodsNavRepository $goodsNav)
    {
        $this->goodsNav = $goodsNav;
    }

    /**
     * 获取商品列表
     * @param array $columns
     * @return mixed
     */
    public function getList($columns = ['*']){
        $data = $this->goodsNav->all($columns);
        return $data;
    }

    /**
     * 创建商品导航
     * @param array $data
     * @return mixed
     */
    public function save(array $data){
        $result = $this->goodsNav->create($data);
        return $result;
    }

    /**
     * 更新商品导航
     * @param array $data
     * @return mixed
     */
    public function update(string $id,array $data){
        $result = $this->goodsNav->update($data,$id);
        return $result;
    }

    public function count(){

        return  $this->goodsNav->listForTotal();
    }

    public function  getNavByGoodsid($goods_id){
        return  $this->goodsNav->getNavByGoodsid($goods_id);
    }

    public function  getNavByID($id){
        return  $this->goodsNav->getNavByID($id);
    }
    /**
     * 删除一条数据
     *
     * @param integer $id   编号
     *
     * @return bool
     */
    public function delete(int $id) {
        $article = $this->goodsNav->find($id);

        if(!empty($article)) {
            $result = $this->goodsNav->delete($id);
            if($result) {
                return $result;
            }
        }

        return false;
    }


}