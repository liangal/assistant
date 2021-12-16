<?php
namespace app\models;

use think\Model;
class Store extends Model{
    protected $pk = 'id';

    protected $insert = ['created_at','updated_at'];

    protected $update = ['updated_at'];
    protected $autoWriteTimestamp = true;// true 开启；false 关闭

    protected $createTime = 'created_at';// 默认create_time

    protected $updateTime = 'updated_at';// 默认update_time

    public static function validate($data){
        $validate = new \think\Validate;
        $validate->rule([
            'title'  => 'require',
            'uid' => 'require',
            'mobile' => 'require',
            'logo' => 'require',
            'address' => 'require',
        ])->message([
            "title.require"=>"请填写店铺名称",
            "uid.require"=>"请选择商户",
            "mobile.require"=>"请填写电话号码",
            "logo.require"=>"logo不能为空",
            "address.require"=>"请填写地址",
        ]);

        if (!$validate->check($data)) {
            return $validate->getError();
        }
        return ;
    }
}