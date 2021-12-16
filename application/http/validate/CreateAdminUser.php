<?php

namespace app\http\validate;

use think\Validate;

class CreateAdminUser extends Validate
{
    protected $rule =   [
        'name'  => 'require|max:20',
        'role_id' => 'require'
    ];
    
    protected $message  =   [
        'name.require' => '用户名不能为空',
        'name.max'     => '用户名最多不能超过20个字符',
        'role_id.require' => '角色不能为空',    
    ];   
}