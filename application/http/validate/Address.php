<?php

namespace app\http\validate;

use think\Validate;

class Address extends Validate
{
    protected $rule =   [
        'realname'  => 'require',
        'mobile'  => 'require',
        'province'  => 'require',
        'city'  => 'require',
        'district'  => 'require',
        'address_info'  => 'require',
        'postal_code'  => 'number',
        'is_defalut'  => 'number',
    ];

    protected $message  =   [
        'realname.require' => '别名不能为空',
        'mobile.require' => '手机号不能为空',
        'province.require' => '请选择省份',
        'city.require' => '请选择市',
        'district.require' => '请选择区',
        'address_info.require' => '请填写详细地址',
//        'postal_code.require' => '邮编',
    ];
}