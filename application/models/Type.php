<?php
namespace app\models;

use think\Model;
class Type extends Model{
    protected $pk = 'id';

    protected $insert = ['created_at','updated_at'];

    protected $update = ['updated_at'];

    protected $autoWriteTimestamp = true;// true 开启；false 关闭

    protected $createTime = 'created_at';// 默认create_time

    protected $updateTime = 'updated_at';// 默认update_time

    protected $validate=[
        "rule"=>[
            "name"=>"require",
            "img"=>"require",
        ],
        "msg"=>[
            "name.require"=>"请填写类型名称",
            "img.alpha"=>"请上传图片",
        ]
    ];

    public static function validate($data){
        $validate = new \think\Validate;
        $validate->rule([
                'name'  => 'require|max:25',
                'img' => 'require'
            ])->message([
            "name.require"=>"请填写类型名称",
            "img.require"=>"请上传图片",
        ]);



        if (!$validate->check($data)) {
            return $validate->getError();
        }
        return ;
    }

}