<?php

namespace app\services\manage;

use app\repository\CarouselRepository;

class CarouselService
{
    protected $carousel;

    public function __construct(CarouselRepository $carousel)
    {
        $this->carousel = $carousel;
    }

    /**
     * 获取轮播图列表
     * @param array $columns
     * @return mixed
     */
    public function getList($columns = ['*'])
    {
        $data = $this->carousel->all($columns);
        return $data;
    }

    /**
     * 创建轮播图
     * @param array $data
     * @return mixed
     */
    public function save(array $data)
    {
        $result = $this->carousel->create($data);
        return $result;
    }

    /**
     * 更新轮播图
     * @param array $data
     * @return mixed
     */
    public function update(string $id, array $data)
    {
        $result = $this->carousel->update($data, $id);
        return $result;
    }

    /**
     * 删除一条数据
     *
     * @param integer $id 编号
     *
     * @return bool
     */
    public function delete(int $id)
    {
        $article = $this->carousel->find($id);

        if (!empty($article)) {
            $result = $this->carousel->delete($id);
            if ($result) {
                return $result;
            }
        }

        return false;
    }


}