<?php
namespace app\http\controllers\manage\api;

use app\http\controllers\ApiController;
use app\repository\GoodsHouseRepository;
use app\services\manage\GoodsCategoryService;
use app\services\manage\GoodsService;
use app\services\manage\GoodsSpecItemsServices;
use app\services\manage\GoodsSpecOptionsServices;
use app\services\manage\GoodsSpecsServices;
use think\Request;

class Goods extends ApiController{
    protected $goods;
    protected $goodsSpecsServices;
    protected $goodsSpecOptionsServices;
    protected $goodsSpecItemsServices;
    public  function __construct(GoodsService $goods,GoodsCategoryService $goodsCategory,GoodsSpecsServices
        $goodsSpecsServices,GoodsSpecOptionsServices $goodsSpecOptionsServices,GoodsSpecItemsServices $goodsSpecItemsServices)
    {
        $this->goods = $goods;
        $this->goodsCategory = $goodsCategory;
        $this->goodsSpecsServices = $goodsSpecsServices;
        $this->goodsSpecOptionsServices = $goodsSpecOptionsServices;
        $this->goodsSpecItemsServices = $goodsSpecItemsServices;
    }

    public function getList(Request $request){
        $search = strval($request->param('title'));
        $category_id =intval($request->param('category_id'));
        $status =intval($request->param('status'));
        $start_at = $request->param('start_at');
        $end_at =$request->param('end_at');

        $page = intval($request->param('page'));
        $length = intval($request->param('limit'));
        $start = ($page * $length) - $length;

        $count = $this->goods->listForTotal($search, $category_id, $status,$start_at,$end_at);
        $data= $this->goods->listForPage($search, $category_id, $status,$start_at,$end_at,$start,$length);
        if($data){
            foreach ($data as $k=>$v){
                $v['stateon'] = date('Y-m-d H:i:s',$v['stateon_at']);
                $thumbs = explode(',',$v['thumbs']);
                foreach ($thumbs as $tv){
                    $v['thumbs_arr'][] = config('sitesystem.oss_domain').$tv;
                    $v['thumbs_arr_d'][] = $tv;
                }


                $spec = $this->goodsSpecsServices->selectWhere(['goods_id'=>$v['id']]);
                $v['spec'] = [];
                if($spec){
                    $spec = $spec->toArray();
                    foreach ($spec as $sk=>$sv){
                        $item =  $this->goodsSpecItemsServices->selectWhere(['spec_id'=>$sv['id']])->toArray();
                        foreach ($item as $ik => $iv){
                            $iv['path'] = $iv['thumb'];
                            $iv['thumb'] = config('sitesystem.oss_domain').$iv['thumb'];
                            $item[$ik] = $iv;
                        }
                        $spec[$sk]['item'] = $item;
                    }
                    $v['spec'] = $spec;
                }

                $specOption = $this->goodsSpecOptionsServices->selectWhere(['goods_id'=>$v['id']]);
                $v['options'] = $specOption;
                $v['description'] = htmlspecialchars_decode($v['description']);
                $v['thumbStr'] = config('sitesystem.oss_domain').$v['thumb'];
                $data[$k] = $v;
            }
        }

        $list = array();
        $list['code'] = 0;
        $list['msg'] = '';
        $list['count'] = $count;
        $list['data'] = $data;

        return json($list);
    }

    //添加商品
    public function save(Request $request){
        $data = $request->only(['title', 'subtitle', 'keyword','thumb', 'thumbs','category_id' ,'status', 'is_free_freight'
            , 'unit', 'marketprice', 'productprice', 'stock', 'ficti_num', 'expressprice', 'description', 'sort','coverImgUrl']);

        $validate = app('\app\http\validate\Goods');

        if (!$validate->check($data)) {
            return $this->message($validate->getError(), 500);
        }
        if($data['description']){
            $data['description'] = htmlspecialchars($data['description']);
        }
        $data['thumbs'] = rtrim($data['thumbs'],',');

        $result = $this->goods->save($data);
        return $this->message($result['msg'],$result['code']);
    }

    //更新商品状态
    public function upStatus(Request $request){
        $data = $request->only(['status']);
        $id = strval($request->post('id'));
        if (empty($id)) {
            return $this->message('参数错误', 500);
        }

        $result = $this->goods->update($id,$data);

        if(empty($result)) {
            return $this->message('操作失败', 500);
        }

        return $this->message('操作成功');
    }

    /**
     * 更新商品
     * @param Request $request
     * @return \think\response\Json
     */
    public function update(Request $request){
        $id = strval($request->post('id'));
        if (empty($id)) {
            return $this->message('参数错误', 500);
        }

        $data = $request->only(['title', 'subtitle', 'keyword','thumb', 'thumbs', 'category_id', 'status', 'is_free_freight'
            , 'unit', 'marketprice', 'productprice', 'stock', 'ficti_num', 'expressprice', 'description', 'sort']);

        $validate = app('\app\http\validate\Goods');
        if (!$validate->check($data)) {
            return $this->message($validate->getError(), 500);
        }
        $data['thumbs'] = rtrim($data['thumbs'],',');

        if($data['description']){
            $data['description'] = htmlspecialchars($data['description']);
        }
        $result = $this->goods->update($id,$data);

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
        $specWhere = ['goods_id'=>$id];
        $result = $this->goods->delete($id);
        $this->goodsSpecsServices->deleteWhere($specWhere);
        $this->goodsSpecItemsServices->deleteWhere($specWhere);
        $this->goodsSpecOptionsServices->deleteWhere($specWhere);
//        (new GoodsHouseRepository())->delete($specWhere);
        return $this->message($result['msg'],$result['code']);
    }

    /**
     * 添加更新商品规格
     * @param Request $request
     * @return \think\response\Json
     */
    public function  saveOption(Request $request){
        $data = $request->only(['goods_id', 'spec']);

        if(!$data['goods_id']){
            return $this->message('请选择商品', 500);
        }
        $specs = [];
        $specItems = [];
        $spec = $data['spec'];
        $specId = $request->post('spec_id');
        $goods_id = $data['goods_id'];
        foreach ($spec as $k=>$v){

            $specData = [
                'title' => $v,
                'goods_id' => $goods_id,
            ];
            $items = [];

            $itemId = $request->post('spec_item_id_'.($k+1));
            $itemImg = $request->post('spec_item_img_'.($k+1));
            $itemTitle = $request->post('spec_item_title_'.($k+1));

            if(!$itemTitle){
                return $this->message('请添加相应的规格项', 500);
            }

            if($specId[$k]){
                $result = $this->goodsSpecsServices->update($specId[$k],$specData);
                $spec_id = $specId[$k];
            }else{
                $result = $this->goodsSpecsServices->save($specData);
                $spec_id =$result->id;
            }

            foreach ($itemTitle as $key =>$value){
                $itemData = [
                    'title' => $value,
                    'goods_id' => $goods_id,
                    'spec_id' => $spec_id,
                ];
                if(isset($itemImg[$key])){
                    $itemData['thumb'] = $itemImg[$key];
                }

                if($itemId[$key]){
                    $itemRes = $this->goodsSpecItemsServices->update($itemId[$key],$itemData);
                    $item_id = $itemId[$key];
                }else{
                    $itemRes = $this->goodsSpecItemsServices->save($itemData);
                    $item_id = $itemRes->id;;
                }
                $itemData['id'] = $item_id;

                $items[] = $itemData;
            }
            $specItems[$k] = $items;
        }
        $options = [];
        if (count($specItems) > 1) {
            for ($i = 0; $i < count($specItems) - 1; $i++) {
                $tmp = [];
                if ($i == 0) {
                    $options = $specItems[$i];
                }

                foreach ($options as $option) {
                    foreach ($specItems[$i + 1] as $specItem) {
                        $arr=[];
                        array_push($arr, $option);
                        array_push($arr, $specItem);
                        $tmp[] = $arr;
                    }
                }
                $options = $tmp;
            }
        } else {
            for ($i = 0; $i < count($specItems[0]); $i++) {
                $options[$i][0] = $specItems[0][$i];
            }
        }

        //添加规格
        $option_ids = $request->post('option_id');
        $option_stocks = $request->post('option_stock_');
        $option_productprices = $request->post('option_productprice_');
        $option_marketprices = $request->post('option_marketprice_');
        $option_costprices = $request->post('option_costprice_');
        foreach ($options as $opKey => $optionDetail){
            $specItemIds = array_column($optionDetail, 'id');
            $specItemIds = implode(',', $specItemIds);

            $title = array_column($optionDetail, 'title');
            $title = implode(',', $title);

            $saveData = [
                'goods_id' => $goods_id,
                'title' => $title,
                'spec_item_ids' => (string)$specItemIds,
            ];
            if(isset($option_ids[$opKey]) && $option_ids[$opKey]){
                $saveData['stock'] = (int)$option_stocks[$opKey];
                $saveData['marketprice'] = (float)$option_marketprices[$opKey];
                $saveData['costprice'] = (float)$option_costprices[$opKey];
                $saveData['productprice'] = (float)$option_productprices[$opKey];
                $this->goodsSpecOptionsServices->update($option_ids[$opKey],$saveData);
            }else{
                $this->goodsSpecOptionsServices->save($saveData);
            }
        }

        if(empty($result)) {
            return $this->message('操作失败', 500);
        }

        return $this->message('操作成功');
    }

    public function delSpec(Request $request){
        $id = $request->param('id');

        if (!$id) {
            return $this->message( '参数错误',500);
        }
        $specWhere = ['spec_id'=>$id];
        $specData = $this->goodsSpecsServices->find(['id'=>$id]);

        $this->goodsSpecsServices->deleteWhere(['id'=>$id]);
        $this->goodsSpecItemsServices->deleteWhere($specWhere);
        $this->goodsSpecOptionsServices->deleteWhere(['goods_id'=>$specData->goods_id]);
        return $this->message( '操作成功',200);
    }

    public function delSpecItem(Request $request){
        $id = $request->param('id');

        if (!$id) {
            return $this->message( '参数错误',500);
        }
        $specWhere = ['id'=>$id];
        $specData = $this->goodsSpecItemsServices->find($specWhere);
        $this->goodsSpecItemsServices->deleteWhere($specWhere);
        $this->goodsSpecOptionsServices->deleteWhere(['goods_id'=>$specData->goods_id]);
        return $this->message( '操作成功',200);
    }
}