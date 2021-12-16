<?php

use think\facade\Env;

return [ 'wechatDetail' => [
    /**
     * 账号基本信息，请从微信公众平台/开放平台获取
     */
    'app_id'  => 'wxc4649e92e1650f59',         // AppID
    'secret'  => '25c6560d09839754cb46f644a939394c',     // AppSecret
    'token'   => 'xishuayishu',          // Token

    'mch_id' =>'1600288859',
    'key' => '7a8eac2fc988a4bc261edefd1f9043c5',
    'log' => [
            'level'      => 'debug',
            'permission' =>  0777,
            'file'       =>  Env::get('root_path').'runtime/logs/wechat.log',
        ],
    ]
];
