<?php
namespace app\http\controllers\manage\api;

use app\http\controllers\ApiController;
use app\services\manage\GoodsCategoryService;
use think\Request;

class GoodsCategories extends ApiController{
    protected $GoodsCategoryServices;

    public  function __construct(GoodsCategoryService $GoodsCategoryService)
    {
        $this->GoodsCategoryServices=$GoodsCategoryService;
    }

    public function getList(){
        $category = $this->GoodsCategoryServices->getList(1)->toArray();

        $list = array();
        $list['code'] = 200;
        $list['msg'] = '';
        $list['count'] = count($category);

        $data = array('category'=>array(), 'menus'=>array());

        if (!empty($category)) {
            foreach ($category as $k=>$v){
                $category[$k]['pic2'] = $v['pic']?config('sitesystem.oss_domain').$v['pic']:'';
            }

            $data['category'] = $category;

            $menus = $this->GoodsCategoryServices->getMenuOption($category);
            $data['menus'] = $menus;
        }

        $list['data'] = $data;

        return json($list);
    }

    public function save(Request $request){
        $data = $request->only(['parent_id', 'name','pic', 'sort', 'status']);

        if(isset($data['name']) && empty($data['name'])){
            return $this->message('请输入分类名称', 500);
        }
        $data['goods_type'] = 1;
        $result = $this->GoodsCategoryServices->save($data);

        if(empty($result)) {
            return $this->message('操作失败', 500);
        }

        return $this->message('操作成功');
    }

    public function update(Request $request){
        $id = strval($request->post('id'));
        if (empty($id)) {
            return $this->message('参数错误', 500);
        }

        $data = $request->only(['parent_id', 'name','pic', 'sort', 'status']);
        if(isset($data['name']) && empty($data['name'])){
            return $this->message('请输入分类名称', 500);
        }
        $result = $this->GoodsCategoryServices->update($id,$data);

        if(empty($result)) {
            return $this->message('操作失败', 500);
        }

        return $this->message('操作成功');
    }

    /**
     * 删除一条记录
     *
     * @param Request $request
     * @return json
     */
    public function delete(Request $request)
    {
        $id = intval($request->post('id'));

        if (empty($id)) {
            return $this->message('参数错误', 500);
        }

        $result = $this->GoodsCategoryServices->delete($id);

        if(empty($result)) {
            return $this->message('操作失败', 500);
        }

        return $this->message('操作成功');
    }
}