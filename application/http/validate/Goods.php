<?php

namespace app\http\validate;

use think\Validate;

class Goods extends Validate
{
    protected $rule =   [
        'title'  => 'require|max:200',
        'expressprice'  => 'require',
        'unit'  => 'require',
        'category_id'  => 'require',
        'thumb'  => 'require',
        'thumbs'  => 'require',
        'marketprice'  => 'require',
        'productprice'  => 'require',
        'stock'  => 'require|number',
    ];

    protected $message  =   [
        'title.require' => '标题不能为空',
        'expressprice.require' => '快递费不能为空',
        'unit.require' => '单位不能为空',
        'category_id.require' => '请选择分类',
        'thumb.require' => '主图不能为空',
        'thumbs.require' => '轮播图不能为空',
        'marketprice.require' => '商品价格不能为空',
        'productprice.require' => '市场价格不能为空',
        'stock.require' => '库存不能为空',
        'title.max'     => '标题最多不能超过200个字符',
    ];
}