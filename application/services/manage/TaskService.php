<?php
namespace app\services\manage;

use app\models\Store;
use app\services\BaseService;

class TaskService extends BaseService
{
    public $status = [0=>'未审核',1=>'已审核',2=>'拒绝'];
    public function index(){
        $page = $this->paging('Store');
        $oss_domain = config('sitesystem.oss_domain');
        if($page){
            foreach ($page['data'] as $k=>&$v){
                $v['oss_logo'] = $oss_domain.$v['logo'];
                $v['oss_address_img'] = $oss_domain.$v['address_img'];
                $v['oss_dishes_img'] = $oss_domain.$v['dishes_img'];
                if($v['type'] == 1){
                    $v['oss_code'] = $oss_domain.$v['mt_code'];
                    $v['code'] =$v['mt_code'];
                }
                if($v['type'] == 2){
                    $v['oss_code'] = $oss_domain.$v['elm_code'];
                    $v['code'] =$v['mt_code'];
                }
                $v['status_str'] = $this->status[$v['status']];
            }
        }
        return self::message(0,$page['data'],'成功',$page['total']);

    }

    public function create(){
        $request = Request();
        $data = $request->only(['store_id','type_id','title','service_price','comment_num','discount_reduce','number','task_time','review_time',
            'start_time','end_time', 'enroll_start_time','enroll_end_time','limit_time','limit_time']);

        $logo_arr = explode(',',$data['logo']);
        $address_img_arr = explode(',',$data['address_img']);
        $dishes_img_arr = explode(',',$data['dishes_img']);
        $code_arr = explode(',',$data['code']);
        $data['logo'] = $logo_arr[count($logo_arr)-2];
        $data['code'] = $code_arr[count($code_arr)-2];
        $data['address_img'] = $address_img_arr[count($address_img_arr)-2];
        $data['dishes_img'] = $dishes_img_arr[count($dishes_img_arr)-2];

        if($data['type'] == 1){
            $data['mt_code'] = $data['code'];
        }
        if($data['type'] == 2){
            $data['elm_code'] = $data['code'];
        }

        $validate =Store::validate($data);
        if($validate){
            return self::message(201,[],$validate);
        }

        $result = Store::create($data);
        if(!$result){
            return self::message(201,[],'添加店铺失败');
        }
        return self::message();
    }

    public function update(){
        $request = Request();
        $data = $request->only(['store_id','type_id','title','service_price','comment_num','discount_reduce','number','task_time','review_time',
            'start_time','end_time', 'enroll_start_time','enroll_end_time','limit_time','limit_time']);
        $logo_arr = explode(',',$data['logo']);
        $address_img_arr = explode(',',$data['address_img']);
        $dishes_img_arr = explode(',',$data['dishes_img']);
        $code_arr = explode(',',$data['code']);

        $data['logo'] = count($logo_arr)>1?$logo_arr[count($logo_arr)-2]:$data['logo'];
        $data['code'] = count($code_arr)>1?$code_arr[count($code_arr)-2]:$data['code'];
        $data['address_img'] = count($address_img_arr)>1?$address_img_arr[count($address_img_arr)-2]:$data['address_img'];;
        $data['dishes_img'] = count($dishes_img_arr)>1?$dishes_img_arr[count($dishes_img_arr)-2]:$data['dishes_img'];

        if($data['type'] == 1){
            $data['mt_code'] = $data['code'];
        }
        if($data['type'] == 2){
            $data['elm_code'] = $data['code'];
        }

        if(!$data['id']){
            return self::message(201,[],'非法操作！！');
        }
        $validate =Store::validate($data);
        if($validate){
            return self::message(201,[],$validate);
        }

        $result = Store::update($data,['id'=>$data['id']]);
        if(!$result){
            return self::message(201,[],'更新店铺失败');
        }
        return self::message();
    }

    public function del(){
        $request = Request();
        $id = $request->param('id');
        if(!$id){
            return self::message(201,[],'非法操作！！');
        }

        $result = Store::destroy(['id'=>$id]);
        if(!$result){
            return self::message(201,[],'删除店铺失败');
        }
        return self::message();
    }
}