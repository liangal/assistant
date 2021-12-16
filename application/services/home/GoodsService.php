<?php
namespace app\services\home;

use app\repository\GoodsCategoryRepository;
use app\repository\GoodsNavRepository;
use app\repository\GoodsRepository;
use app\repository\GoodsSpecItemsRepository;
use app\repository\GoodsSpecOptionsRepository;
use app\repository\GoodsSpecsRepository;

class GoodsService
{
    protected $goods;
    protected $category;
    protected $goodsNav;
    protected $specItem;
    protected $spec;
    protected $specOption;

    public function __construct(GoodsRepository $goods,GoodsCategoryRepository $category,GoodsNavRepository $goodsNav,
                                GoodsSpecItemsRepository $specItem,GoodsSpecsRepository $spec,GoodsSpecOptionsRepository $specOption){
        $this->goods = $goods;
        $this->category = $category;
        $this->goodsNav = $goodsNav;
        $this->specItem = $specItem;
        $this->spec = $spec;
        $this->specOption = $specOption;
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
        $res = $this->goods->find($id, $columns);
        if($res){
            if($res['status']==0){
                return [];
            }
        }
        return $res;
    }

    public function findByField($field,$value,$columns = ['*']){
        $this->goods->findByField($field,$value,$columns);
    }

    //获取商品导航
    public function getNav($search=''){
        $goodsNavData = $this->goodsNav->all();
        $res = [];

        if($goodsNavData){
            $goodsNavDataArr = $goodsNavData->toArray();

            foreach ($goodsNavDataArr as $k=>$v){
                $result = $this->goods->find($v['goods_id']);
                if($result){
                    if($result->status > 0){
                        $result = $result->toArray();
                        $resDetail = [
                            'goods_id'=>$v['goods_id'],
                            'name'=>$result['title'],
                            'sale_num'=>$result['sale_num'],
                            'thumb'=>config('sitesystem.oss_domain').$result['thumb'],
                            'price'=>$result['productprice'],
                            'expressprice'=>$result['expressprice'],
                            'stateon_at'=>date('Y-m-d H:i:s',$result['stateon_at']),
//                  'name'=>$course['title'],
                        ];
                        $res[] = $resDetail;
                    }
                }
            }
        }
        return $res;
    }


    /**
     * 数据总数
     *
     * @param  string $search    搜索关键词
     * @param  int    $start
     * @param  int    $length
     *
     * @return array
     */
    public function listForTotal(string $search)
    {
        $model = $this->goods->modelRepostory();
        return $model->where(function ($query) use ($search) {
            $query->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('title', 'like', '%' . $search . '%');
                }
            })->where('status',1);;
        })->count();
    }


    /**
     * 数据分页
     *
     * @param  string $search    搜索关键词
     * @param  string    $sale  按销量排序
     * @param  string    $new    按最新排序
     * @param  array  $sorts     排序
     * @param  int    $start
     * @param  int    $length
     *
     * @return array
     */
    public function listForPage(string $search,int $sale,int $new,$category_id, int $start, int $length)
    {
        $model = $this->goods->modelRepostory();
        $bul = $model->where(function ($query) use ($search,$category_id) {
            $query->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('title', 'like', '%' . $search . '%');
                }
            })->where(function ($query) use ($category_id) {
                if (!empty($category_id)) {
                    $query->where('category_id', $category_id);
                }
            })->where('status',1);
        });
        if($sale==1){
            $bul = $bul ->order('sale_num','DESC');
        }elseif($sale==2){
            $bul =  $bul ->order('sale_num','ASC');
        }elseif($new==1){
            $bul =  $bul ->order('craeted_at','DESC');
        }

        if(empty($sale) && empty($new)){
            $bul =  $bul ->order('id','DESC');
        }

        return $bul->limit($start, $length)->select();
    }

    public function search(string $search){
        $model = $this->goods->modelRepostory();
        $bul = $model->field(['id','title'])->where(function ($query) use ($search) {
            $query->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('title', 'like', '%' . $search . '%');
                }
            })->where('status',1);;
        });
        return $bul->select();
    }

    public function selectWhere($where,$columns=['*']){
       return $this->goods->selectWhere($where,$columns);
    }

    /**
     * 获取商品详情
     * @param $goods_id 商品id
     * @return array
     */
    public function goodsDetail($goods_id){
        $res = $this->find($goods_id);
        if(!$res){
            return $res;
        }

        $result = $this->goodsArr($res);
        $specs = $this->spec->selectWhere(['goods_id'=>$goods_id]);
        $result['spec'] = [];
        if($specs){
            $specs = $specs->toArray();
            //获取规格项
            foreach ($specs as $k=>$spec){
                $title = [];
                $value = [];
                $specItem = $this->specItem->selectWhere(['spec_id'=>$spec['id']])->toArray();
                foreach ($specItem as $ik=>$item){
                    $title[$ik] = $item['title'];
                    $value[$ik]['id'] = $item['id'];
                    $value[$ik]['title'] = $item['title'];
                }
                $specs[$k]['itemsTitles'] = $title;
                $specs[$k]['itemsValue'] = $value;
            }
            $specOptions = $this->specOption->selectWhere(['goods_id'=>$goods_id])->toArray();

            $option = [];
            foreach($specOptions as $pk=>$specOption){
                $option[$specOption['title']] = [
                    'goods_id' =>strval($specOption['goods_id']),
                    'title' =>strval($specOption['title']),
                    'stock' =>strval($specOption['stock']),
                    'price' =>strval($specOption['productprice']),
                    'costprice' =>strval($specOption['costprice']),
                    'sale_num' =>strval($specOption['sale_num']),
                    'unique' =>$specOption['spec_item_ids'],
                ];
                if($pk==0){
                    $result['price'] = strval($specOption['productprice']);
                    $result['marketprice'] = strval($specOption['marketprice']);
                    $result['costprice'] = strval($specOption['costprice']);
                }
            }

            $result['spec'] = $specs;
            $result['specOption'] = $option;
        }
        return $result;
    }


    /*
     * 整合商品数据
     *
     * @param $data 商品数据
     * @return array
     */
    public function goodsArr($data){
        $resDetail = [];
        if($data){
            $resDetail = [
                'goods_id'=>strval($data['id']),
                'cart_id'=>isset($data['cart_id'])?$data['cart_id']:0,
                'category_id' => strval($data['category_id']) ,
                'name'=>$data['title'],
                'thumb'=>config('sitesystem.oss_domain').$data['thumb'],
                'price'=> sprintf("%.2f", $data['productprice']),
                'expressprice'=>$data['expressprice'],
                'marketprice'=>$data['marketprice'],
                'costprice'=>$data['costprice'],
                'sale_num'=>strval($data['sale_num']),
                'stock'=>strval($data['stock']),
                'ficti_num'=>strval($data['ficti_num']),
                'stateon_at'=>date('Y-m-d H:i:s',$data['stateon_at']),

                'description' => htmlspecialchars_decode($data['description']) ,
            ];

            $thumbs =  explode(',',$data['thumbs']);
            foreach ($thumbs as $tk=>$tv){
                $tv = config('sitesystem.oss_domain').$tv;
                $thumbs[$tk] = $tv;
            }
            $resDetail['thumbs'] = $thumbs;
        }
        return $resDetail;
    }

    /**
     * 获取分类
     * @return array
     */
    public function getCategory(){
       $category = $this->category->selectWhere(['status'=>1]);

       $res = [];
       if($category){
           $category = $category->toArray();
           foreach ($category as $k=>$v){
               $res[$k]['id'] = $v['id'];
               $res[$k]['parent_id'] = $v['parent_id'];
               $res[$k]['goods_type'] = $v['goods_type'];
               $res[$k]['name'] = $v['name'];
               $res[$k]['pic'] =$v['pic']?config('sitesystem.oss_domain').$v['pic']:'';
//               $res[$k]['home_pic'] = $v['home_pic'];
               $res[$k]['sort'] = $v['sort'];
//               $res[$k]['status'] = $v['status'];
           }
       };
        return generateTree($res);
    }

    public function update(array $data,$id){
       return $this->goods->update($data,$id);
    }
}