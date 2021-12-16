<?php

namespace app\http\controllers\api\v1;

use app\services\home\AddressService;
use think\Request;
use app\http\controllers\ApiController;

class Address extends ApiController
{
    protected $addressService;

    public function __construct(Request $request,AddressService $addressService){
        $this->addressService = $addressService;
        parent::__construct($request);
    }
    public function list(Request $request){
        $user = $request->user;
        $start = $this->start;
        $limit = $this->limit;
        $list = $this->addressService->listForPage($user->id,$start,$limit);
        $count = $this->addressService->listForTotal($user->id);

       return $this->listMessage($list,$count,$this->page,count($list));
    }

    public function save(Request $request){
       $fieldData = $request->only($this->addressService->addData());

        $validate = app('\app\http\validate\Address');
        if (!$validate->check($fieldData))
        {
            return $this->message('参数错误', 500);
        }

       $res = $this->addressService->save($fieldData);
        if(!$res){
            return $this->message('操作失败',401);
        }
        return $this->message('操作成功',200);
     }

     public function update(Request $request){
         $fieldData = $request->only($this->addressService->addData());
         $addressId = (int)$request->post('address_id');
         $validate = app('\app\http\validate\Address');
         if (!$validate->check($fieldData))
         {
             return $this->message('参数错误', 500);
         }
         if(!$addressId){
             return $this->message('参数错误', 500);
         }
         $res = $this->addressService->update($addressId,$fieldData);
         if(!$res){
             return $this->message('操作失败',401);
         }
         return $this->message('操作成功',200);
     }

     public function delete(Request $request){
         $addressId = (int)$request->post('address_id');
         if(!$addressId){
             return $this->message('参数错误', 500);
         }
         $res = $this->addressService->delete($addressId);
         if(!$res){
             return $this->message('操作失败',401);
         }
         return $this->message('操作成功',200);
     }

    /**
     * 设置默认地址
     * @param Request $request
     * @return \think\response\Json
     */
     public function changeDefault(Request $request){
         $addressId = (int)$request->post('address_id');
         if(!$addressId){
             return $this->message('参数错误', 500);
         }
         $uid = $request->user->id;
         $res = $this->addressService->setDefalut($addressId,$uid);
         if(!$res){
             return $this->message('操作失败',401);
         }
         return $this->message('操作成功',200);
     }

    /**
     * 获取地址
     * @param Request $request
     */
     public function getAddress(Request $request){
         $addressId = (int)$request->post('address_id');
         if(!$addressId){
             return $this->message('参数错误', 500);
         }
         $address = $this->addressService->find(['id'=>$addressId,'uid'=>$request->user->id]);
         return $this->message('',200,$address);
     }

    /**
     * 获取默认地址
     * @return \think\response\Json
     */
     public function getDefault(Request $request){
         $user = $request->user;
         $res = $this->addressService->find(['is_default'=>1,'uid'=>$user->id]);
         if(!$res){
             return $this->message('',404,$res);
         }
         return $this->message('',200,$res);
     }
}