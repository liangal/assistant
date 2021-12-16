<?php

return [
    // 系统名称
    'system_name'            => '画家管理系统',
    //签发令牌密钥
    'jwt_secret'             => 'sWWDHVKVhI2eXOAWp7K1OH3gqOD2y5FxBe7Aw1BFicY=',
    //签发令牌过期时间
//    'jwt_access_token_exp'   => 86400 * 30,
    'jwt_access_token_exp'   => 86400*7,
    //刷新令牌过期时间
    'jwt_refresh_token_exp'  => 2592000,//一个月
    // 站点域名
    'site_domain'            => 'http://emg.com/manage/api/uploade/image',
    //图片服务器
    'image_domain'           => 'http://emg.com/manage/api/uploade/image',
    //oss图片服务器
    'oss_domain'             => 'http://huajia-static.xsbaopay.com/',
//    'oss_domain'             => 'http://xs-huajia.oss-cn-shenzhen.aliyuncs.com/',
    //oss视频服务器
    'video_oss_domain'       => 'http://huajia-video.xsbaopay.com/',

    //短信配置
    'juhe'                   => array('key'=>'e37fa2c03ac121899c0638fbf3847fa1', 'url'=>'http://v.juhe.cn/sms/send'),
    //高德地图KEy
    'amap_key'               => 'bd28ea2711e4d8c9325435920335ce30',
    //极光推送配置
    'jpush'                  => array('key'=>'a0a9e26f7f326d3a779b4fbf', 'secret'=>'262e6826386353074f704f43'),
    //阿里OSS存储配置
    'alioss'                 => array('id'=>'LTAI4Fydvq6ULTcDhke73fD7', 'secret'=>'il8IKKAZTnTAKKyYspL48A2zVnIQyt',
                                'bucket'=>'xs-huajia',
                                'endpoint'=>'oss-cn-shenzhen.aliyuncs.com',
                                'host'=>'http://xs-huajia.oss-cn-shenzhen.aliyuncs.com',

                                'video_bucket'=>'xs-v-huajia',//视频空间
                                'video_host'=>'http://xs-v-huajia.oss-cn-shenzhen.aliyuncs.com'//视频访问地址
                                ),
    // 开启推送
    'open_push'              => true,
    // 自动阅览
    'autopreview'            => array('auto'=>true, 'preview' => 10000, 'support'=> 7000),
];