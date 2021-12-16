<?php

namespace app\http\validate;

use think\Validate;

class Company extends Validate
{
    protected $rule =   [
        'class_id' => 'require',
        'name'  => 'require|max:200',
        'phone'  => 'require|max:40',
        'address'  => 'require|max:200'
    ];
    
    protected $message  =   [
        'class_id.require' => '请选择分类',
        'name.require' => '标题不能为空',
        'name.max'     => '标题最多不能超过200个字符',
        'phone.require' => '电话不能为空',
        'phone.max'     => '电话最多不能超过40个字符',
        'address.require' => '地址不能为空',
        'address.max'     => '地址最多不能超过200个字符'
    ];   
}