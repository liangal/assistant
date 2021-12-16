<?php
namespace app\services\manage;

use app\repository\GoodsCategoryRepository;

class GoodsCategoryService{

    protected $GoodsCategoryRepository;

    public function __construct(GoodsCategoryRepository $GoodsCategoryRepository){
        $this->GoodsCategoryRepository = $GoodsCategoryRepository;
    }

    /**
     * 获取分类列表
     * @param array $columns
     * @return mixed
     */
    public function getList($type=0,$columns = ['*']){
        $data = $this->GoodsCategoryRepository->all($type,$columns);
        return $data;
    }

    /**
     * 获取全部菜单
     * @return [type] [description]
     */
    public function getMenuOption(array $list)
    {
        $html = '';
        if (!empty($list)) {
            $cagegorys = array_pluck($list, 'parent_id', 'id');
            foreach(array_first($cagegorys) as $row) {
                $html .= $this->createOptionHtml($cagegorys, $row,0);
            }
        }
        return $html;
    }

    /**
     * 创建分类
     * @param array $data
     * @return mixed
     */
    public function save(array $data){
       $result = $this->GoodsCategoryRepository->create($data);
       return $result;
    }

    /**
     * 更新分类
     * @param array $data
     * @return mixed
     */
    public function update(string $id,array $data){
       $result = $this->GoodsCategoryRepository->update($data,$id);
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
        $article = $this->GoodsCategoryRepository->find($id);

        if(!empty($article)) {
            $result = $this->GoodsCategoryRepository->delete($id);
            if($result) {
                return $result;
            }
        }

        return false;
    }

    /**
     * 创建菜单选项
     * @param  array  $list     [分类]
     * @param  array  $row     [description]
     * @return [type]            [description]
     */
    protected function createOptionHtml(array $list, array $row,$level)
    {
        $option_html = "";
        $parent_id = $row['id'];
        if($row['parent_id'] == 0){
            $level = 0;
        }else{
            $level = $level+1;
        }
        if(isset($list[$parent_id]))
        {
            foreach ($list[$parent_id] as $k => $v) {
                $option_html .= $this->createOptionHtml($list, $v,$level);
            }
        }

        $option_html = '<option value="' . $row['id'] . '">' . str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $level) . $row['name'] . '</option>' . $option_html;
        return $option_html;
    }
}