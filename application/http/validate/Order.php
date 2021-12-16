<?php

namespace app\http\validate;

use think\Validate;

class Order extends Validate
{
    protected $rule =   [
        'name'  => 'require|min:3|max:17',
        'coverImg'  => 'require',
        'shareImg'  => 'require',
        'date'  => 'require',
        'date2'  => 'require',
        'anchorName'  => 'require|min:2|max:15',
        'anchorWechat'  => 'require',

    ];

    protected $message  =   [
        'name.require' => '标题不能为空',
        'name.min' => '标题名称最少3个汉字',
        'name.max' => '标题名称最多17个汉字',
        'coverImg.require' => '主图不能为空',
        'shareImg.require' => '分享图不能为空',
        'date.require' => '请选择开始时间',
        'date2.require' => '请选择结束时间',
        'anchorName.require' => '主播名称不能为空',
        'anchorName.min' => '标题名称最少2个汉字',
        'anchorName.max' => '标题名称最多15个汉字',
        'anchorWechat.require' => '主播微信好不能为空',
    ];
}