<?php
namespace app\services\home;

use App\Models\ShimmerLiveshopUserAddress;
use app\models\Users;
use app\repository\AddressRepository;
use think\Request;

class AddressService
{
    protected $address;

    public function __construct(AddressRepository $address){
        $this->address = $address;
    }


    public function addData(){
        return $this->address->inserField();
    }

    /**
     * 保存数据
     * @param array $attributes
     */
    public function save(array $attributes){
        $request = Request();
        $uid = $request->user->id;
        $attributes['uid'] = $uid;
        $attributes['is_default'] = isset($attributes['is_default'])?$attributes['is_default']:0;
        $model = $this->address->create($attributes);
        if(!$model){
            return $model;
        }
        if ($attributes['is_default'] == 1) {
            $this->setDefalut($model->id,$uid);
        }

        return $model;
    }

    /**
     * 更新数据
     * @param array $attributes
     */
    public function update(int $id,array $attributes){
        $request = Request();
        $uid = $request->user->id;
        $attributes['uid'] = $uid;
        $attributes['is_default'] = isset($attributes['is_default'])?$attributes['is_default']:0;

        $address = $this->address->find($id);
        if(!$address || $address->uid != $uid){
            return false;
        }

        $model = $this->address->update($attributes,$id);
        if ($attributes['is_default'] == 1) {
            $this->setDefalut($id,$uid);
        }

        return $model;
    }

    /**
     * 删除收货地址
     * @param int $id
     * @return int
     */
    public function delete(int $id){
        $address = $this->address->delete($id);
        return $address;
    }

    /**
     * 设置默认地址
     * @param int $id 收货地址id
     * @param int $uid 用户id
     * @return mixed
     */
    public function setDefalut(int $id,int $uid){
        $userAddress = $this->address->getFind('id',$id);
        if (!$userAddress) {
            $userAddress = $this->address->getOne();
        }

        if ($userAddress) {
            $this->address->update(['is_default' => 0],$uid,'uid');
            $res = $this->address->update(['is_default' => 1],$userAddress->id,'id');
        }
        return  $res;
    }

    /**
     * 通过条件查询数据
     *
     * @param $field    字段名
     * @param $value    字段值
     * @return mixed
     */
    public function find($where,$c=['*']) {
        $address = $this->address->findWhere($where,$c);
        $res = [];
        if($address){
            $res['address_id'] = strval($address['id']);
            $res['uid'] = strval($address['uid']);
            $res['realname'] = strval($address['realname']);
            $res['mobile'] = strval($address['mobile']);
            $res['province'] = strval($address['province']);
            $res['city'] = strval($address['city']);
            $res['district'] = strval($address['district']);
            $res['address_info'] = strval($address['address_info']);
            $res['postal_code'] = strval($address['postal_code']);
            $res['is_default'] = strval($address['is_default']);
            $res['create'] = date('Y-m-d H:i:s',$address['created_at']);
            $res['update'] = date('Y-m-d H:i:s',$address['updated_at']);
        }

        return $res ;
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
    public function listForPage(int $uid,int $start,int $limit)
    {
        $list = $this->address->listForPage($uid,$start,$limit);
        $pageList = [];
        if($list){
            foreach ($list as $k=>$v){
                $pageList[$k]['address_id'] = strval($v['id']);
                $pageList[$k]['uid'] = strval($v['uid']);
                $pageList[$k]['realname'] = strval($v['realname']);
                $pageList[$k]['mobile'] = strval($v['mobile']);
                $pageList[$k]['province'] = strval($v['province']);
                $pageList[$k]['city'] = strval($v['city']);
                $pageList[$k]['district'] = strval($v['district']);
                $pageList[$k]['address_info'] = strval($v['address_info']);
                $pageList[$k]['postal_code'] = strval($v['postal_code']);
                $pageList[$k]['is_default'] = strval($v['is_default']);
                $pageList[$k]['create'] = date('Y-m-d H:i:s',$v['created_at']);
                $pageList[$k]['update'] = date('Y-m-d H:i:s',$v['updated_at']);
            }
        }

        return $pageList;
    }

    /**
     * 数据总数
     *
     * @param string $search    搜索关键词
     * @return number
     */
    public function listForTotal($uid){
        return $this->address->listForTotal($uid);
    }
}