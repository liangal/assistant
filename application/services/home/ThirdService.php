<?php
namespace app\services\home;

use app\repository\GoodsRepository;
use app\repository\GoodsSpecOptionsRepository;
use think\Request;

class ThirdService{
    protected $goods;
    protected $option;

    protected $third;
    protected static $msgSrc = 'devs';
    protected $requestTimestamp;
    protected $SystemNumber;//系统编号
    protected static $msgType='wx.unifiedOrder';
    protected static $mid='123123';//商户号
    protected static $tid = '213';//终端号
    protected $subAppId;//bu
    protected $subOpenId;//bu
    protected static $tradeType = 'MINI';//bu
    protected $sign;//bu
    protected static $key = 'AAABBBCCCDDDEEEFFFGGG';
    public function __construct(GoodsRepository $goods,GoodsSpecOptionsRepository $option)
    {
        $this->goods = $goods;
        $this->option = $option;
    }

    public static function confirm($goodsArr,$openid,$merOrderId, $totalAmount){
        $url = 'https://qr-test2.chinaums.com/netpay-route-server/api/';
        $requestTimestamp  = date('Y-m-d H:i:s',time());
        $appid = config('wechat.wechatDetail.app_id');

        $data = [
//            'goods' =>json_encode($goodsArr) ,
            'merOrderId' =>$merOrderId,
            'mid' =>self::$mid,
            'msgSrc' =>self::$msgSrc,
            'msgType' =>self::$msgType,
            'requestTimestamp' =>$requestTimestamp,
            'subOpenId' => $openid,
            'tid' =>self::$tid,
            'totalAmount' =>$totalAmount,
            'tradeType' => self::$tradeType,
        ];

        $sign = self::getSign($data);
        $data['sign'] =hash("sha256",$sign);
//        exit;
        $res = curl_post($url,json_encode($data,true));var_dump($res);
    }

    protected static function getSign(array $data){

        return self::changeStr($data).self::$key;
    }

    protected static function changeStr($arr){
        $str = '';
        foreach ($arr as $k=>$v){

            $str .= $k.'='.$v.'&';
        }
        return rtrim($str,'&') ;
    }
}