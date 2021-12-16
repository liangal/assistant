<?php
namespace app\services\home;

use app\repository\CarouselRepository;
use app\repository\CartsRepository;
use app\repository\GoodsSpecOptionsRepository;
use app\services\manage\GoodsSpecOptionsServices;
use think\Request;

class CartsService
{
    protected $cart;
    protected $goods;
    protected $option;

    public function __construct(CartsRepository $cart,GoodsService $goods,GoodsSpecOptionsRepository $option){
        $this->cart = $cart;
        $this->goods = $goods;
        $this->option = $option;
    }
    public function message($msg, $code = 200, $data=[])
    {
        $data = array(
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        );

        return json($data);
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
        return $this->cart->find($id, $columns);
    }

    public function findByField($field,$value,$columns=['*']){
       return $this->cart->findByField($field,$value,$columns);
    }

    public function one($where){
        return $this->cart->one($where);
    }

    /**
     * 获取购物车列表
     * @param array $columns
     * @return mixed
     */
    public function getList($columns = ['*']){
        $data = $this->cart->all($columns);
        $oss_domain = config('sitesystem.oss_domain');
        if($data){
            foreach ($data as $k=>$v){
                $data[$k]['thumb'] = $oss_domain.$v['thumb'];
            }
        }
        return $data;
    }

    public function selectWhere($where,$columns=['*']){
       return $this->cart->selectWhere($where,$columns);
    }

    /**
     * 数据分页
     *
     * @param  string $search    搜索关键词
     * @param  int    $start
     * @param  int    $length
     *
     * @return array
     */
    public function listForPage($search,$uid,$start,$length)
    {
        $list = $this->cart->listForPage($search,$uid,$start,$length);
        $pageList = [];
        if($list){
            foreach ($list as $k=>$v){
                $pageList[$k]['cart_id']  = strval($v['id']);
                $pageList[$k]['uid']  = strval($v['uid']);
                $pageList[$k]['goods_id']  = strval($v['goods_id']);
                $pageList[$k]['spec_item_ids']  = strval($v['spec_item_ids']);
                $pageList[$k]['cart_num']  = ($v['cart_num']);
                $pageList[$k]['check']  = strval($v['check']);
                $pageList[$k]['update']  = date('Y-m-d H:i:s',($v['updated_at'])) ;
                $pageList[$k]['create']  = date('Y-m-d H:i:s',($v['created_at'])) ;
                $good = $this->goods->find($v['goods_id']);
                $pageList[$k]['goods'] = $this->goods->goodsArr($good);
                $option = $this->option->findWhere(['spec_item_ids'=>$v['spec_item_ids']]);
                $optionArr = [];

                if($option){
                    $optionArr = [
                        'goods_id' =>strval($option['goods_id'])?strval($option['goods_id']):'',
                        'title' =>strval($option['title']),
                        'stock' =>($option['stock']),
                        'price' =>sprintf("%.2f", $option['productprice']),
                        'costprice' =>strval($option['costprice']),
                        'sale_num' =>($option['sale_num']),
                        'unique' =>$option['spec_item_ids']?$option['spec_item_ids']:'',
                    ];
                }

                $pageList[$k]['specOption'] = $optionArr;
            }
        }
        return $pageList;
    }

    /**
     * 获取总数
     * @param string $search
     * @return number
     *
     */
    public function listForTotal($search,$uid){
        return $this->cart->listForTotal($search,$uid);
    }

    /**
     * 保存数据
     *
     * @param array $data
     * @return mixed
     */
    public function save(array $data,$uid,$type){

        $goods = $this->goods->find($data['goods_id']);
        if(!$goods){
            return $this->message('无相关商品信息',404);
        }

        $num = (int)($data['num']);
        $res = $this->goods->goodsArr($goods);
        $spec_item_ids = strval($data['unique_id']);
        $cart = $this->one(['goods_id'=>$goods['id'],'spec_item_ids'=>$spec_item_ids,'uid'=>$uid]);
        if($cart && $type==1){
            $num = $cart->cart_num+$num;
        }

        if($res['stock']<$num){
            return $this->message('商品库存不足',403);
        }

        if($data['unique_id']){
            $specOption = $this->option->find(['goods_id'=>$res['goods_id']]);
            if($specOption && !$data['unique_id']){
                return $this->message('该商品规格不存在或已删除',404);
            }
            if($specOption && $data['unique_id']){
                $option = $this->option->find(['goods_id'=>$res['goods_id'],'spec_item_ids'=>$spec_item_ids]);
                if(!$option){
                    return $this->message('该商品规格不存在或已删除',404);
                }

                if($option->stock<$num){
                    return $this->message('该规格库存不足',403);
                }
            }
        }

        if(!$uid){
            return $this->message('请登录',202);
        }

        if(!$cart){
            $addData = [
                'uid'=>$uid,
                'goods_id'=>$goods['id'],
                'spec_item_ids'=>$data['unique_id'],
                'cart_num'=>$num,
            ];
            $result = $this->cart->create($addData);
        }else{
            $result = $this->update(['cart_num'=>$num],$cart->id);
        }
        if(!$result){
            return $this->message('操作失败',401);
        }

       return $this->message('操作成功',200);
    }

    /**
     * 更新购物车
     *
     * @param array $data
     * @param $id
     * @return mixed
     */
    public function update(array $data,$id){
        return $this->cart->update($data,$id);
    }


    public function delete($id){
       return $this->cart->delete($id);
    }
}