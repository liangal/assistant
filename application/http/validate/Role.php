<?php

namespace app\http\validate;

use think\Validate;

class Role extends Validate
{
    protected $rule =   [
        'name'  => 'require|max:20',
        'description'   => 'require|max:250' 
    ];
    
    protected $message  =   [
        'name.require' => '名称不能为空',
        'name.max'     => '名称最多不能超过20个字符',
        'description.require' => '描述不能为空',
        'description.max'     => '描述最多不能超过250个字符',    
    ];
    
}