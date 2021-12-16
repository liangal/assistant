<?php
namespace app\services;

use app\repository\CartsRepository;
use app\repository\OrderRepository;
use EasyWeChat\Factory;
use think\Config;
use think\facade\Log;

class WechatServices
{

    private static $instances = null;
    public $liveStatus = [101=>'直播中',102=>'未开始',103=>'已结束',104=>'禁播',105=>'暂停',106=>'异常',107=>'已过期'];
    public $createLive = [
        1003=>'商品id不存在',

        47001=>'入参格式不符合规范',

        200002=>'入参错误',

        300001=>'禁止创建/更新商品 或 禁止编辑&更新房间',

        300002=>'名称长度不符合规则',

        300003=>'价格输入不合规（如：现价比原价大、传入价格非数字等）',

        300004=>'商品名称存在违规违法内容',

        300005=>'商品图片存在违规违法内容',

        300006=>'图片上传失败（如：mediaID过期）',

        300007=>'线上小程序版本不存在该链接',

        300008=>'添加商品失败',

        300009=>'商品审核撤回失败',

        300010=>'商品审核状态不对（如：商品审核中）',

        300011=>'操作非法（API不允许操作非API创建的商品）',

        300012=>'没有提审额度（每天500次提审额度）',

        300013=>'提审失败',

        300014=>'审核中，无法删除（非零代表失败）',
        300015=>'goodsId不存在',

        300017=>'商品未提审',
        300018=>'上传图片不符',

        300021=>'商品添加成功，审核失败',

        300022=>'此房间号不存在',

        300023=>'房间状态 拦截（当前房间状态不允许此操作）',

        300024=>'商品不存在',

        300025=>'商品审核未通过',

        300026=>'房间商品数量已经满额',

        300027=>'导入商品失败',

        300028=>'房间名称违规',

        300029=>'主播昵称违规',

        300030=>'主播微信号不合法',

        300031=>'直播间封面图不合规',

        300032=>'直播间分享图违规',

        300033=>'添加商品超过直播间上限',

        300034=>'主播微信昵称长度不符合要求',

        300035=>'主播微信号不存在',

        300036=> '主播微信号未实名认证',

        9410000=> '直播间列表为空',

        9410001=>'获取房间失败',

        9410002=> '获取商品失败',

        9410003=>'获取回放失败',
    ];
    /**
     * 获取实例
     * @param $appType
     */
    public function getApp($appType,$configValue)
    {
        if (isset(self::$instances[$appType])) {
            return self::$instances[$appType];
        }
        $config = config($configValue);
        $app = Factory::$appType($config);
        self::$instances[$appType] = $app;

        return $app;
    }
    public function miniProgram(){
        $app = $this->getApp('miniProgram','wechat.wechatDetail');
        return $app;
    }

    public function officialAccount(){
        $app = $this->getApp('officialAccount','wechat.wechatDetail');
        return $app;
    }
    public function payMent(){
        $app = $this->getApp('Payment','wechat.wechatDetail');
        return $app;
    }

    public function addNewsMedia($path)
    {
        $getApp = $this->miniProgram();
        $getApp->media->uploadImage($path);
    }

    public function wechatSign($params) {

        $key = config('wechat.wechatDetail.key');
        if(isset($params['sign']) && $params['sign']){
            $sign = $params['sign'];
            unset($params['sign']);
        }

        ksort($params);

        $buff = "";
        foreach ($params as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");

        $string = md5($buff . "&key=".$key);
        $my_sign = strtoupper($string);

        if(isset($sign) && $sign){
            return $sign == $my_sign;
        }
        return $my_sign;
    }

    public function orderRefund(){

    }
}