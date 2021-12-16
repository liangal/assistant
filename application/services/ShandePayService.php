<?php

namespace app\services;

use Exception;

/**
 * 杉德支付管理类
 *
 * Class ShandePayService
 * @package app\services
 */
class ShandePayService
{
    protected static $api_host = 'https://cashier.sandpay.com.cn/gateway/api/';//生产环境

    protected static $pub_key_path = 'cert/sand.cer';
    protected static $pri_key_path = 'cert/6888805013923.pfx';
    protected static $cert_pwd = 'xishua123';
    protected static $mid = '6888805013923';

    /**
     * 获取公钥
     * @param $path
     * @return mixed
     * @throws Exception
     */
    public static function loadCert()
    {
        try {
            $file = file_get_contents(self::$pub_key_path);
            if (!$file) {
                throw new \Exception('loadx509Cert::file_get_contents ERROR');
            }

            $cert = chunk_split(base64_encode($file), 64, "\n");
            $cert = "-----BEGIN CERTIFICATE-----\n" . $cert . "-----END CERTIFICATE-----\n";

            $res = openssl_pkey_get_public($cert);
            $detail = openssl_pkey_get_details($res);
            openssl_free_key($res);

            if (!$detail) {
                throw new \Exception('loadX509Cert::openssl_pkey_get_details ERROR');
            }

            return $detail['key'];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 获取私钥
     * @param $path
     * @param $pwd
     * @return mixed
     * @throws Exception
     */
    protected static function loadPkCert()
    {
        try {
            $file = file_get_contents(self::$pri_key_path);
            if (!$file) {
                throw new \Exception('loadPk12Cert::file
					_get_contents');
            }

            if (!openssl_pkcs12_read($file, $cert, self::$cert_pwd)) {
                throw new \Exception('loadPk12Cert::openssl_pkcs12_read ERROR');
            }
            return $cert['pkey'];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 公钥验签
     * @param $plainText
     * @param $sign
     * @param $path
     * @return int
     * @throws Exception
     */
    public static function verify($plainText, $sign, $path)
    {
        $resource = openssl_pkey_get_public($path);
        $result = openssl_verify($plainText, base64_decode($sign), $resource);
        openssl_free_key($resource);

        if (!$result) {
            throw new \Exception('签名验证未通过,plainText:' . $plainText . '。sign:' . $sign, '02002');
        }

        return $result;
    }


    /**
     * 私钥签名
     * @param $plainText
     * @param $path
     * @return string
     * @throws Exception
     */
    protected static function sign($plainText)
    {
        $path = self::loadPkCert();
//        $plainText = json_encode($plainText);
        try {
            $resource = openssl_pkey_get_private($path);
            $result = openssl_sign($plainText, $sign, $resource);
            openssl_free_key($resource);

            if (!$result) {
                throw new \Exception('签名出错' . $plainText);
            }

            return base64_encode($sign);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 获取
     * @return array
     */
    protected static function getHead($method){
        return [ 'head' => array(
            'version' => '1.0',
            'method' => $method,
            'productId' => '00002021',//产品编码
            'accessType' => '1',
            'mid' => self::$mid,
            'channelType' => '08',
            'reqTime' => date('YmdHis', time())
        )];
    }

    /**
     * 统一下单
     * @param $order_id 订单id
     * @param $price    价格
     * @param $openid   用户openid
     * @param $subject  标题
     * @param $timeOut  过期时间
     * @throws Exception
     */
    public static function unified($order_id,$price,$openid,$timeOut,$subject='xishua'){
        $price = str_pad(price_bcmul($price),12,"0",STR_PAD_LEFT);
        $data = self::getHead('sandpay.trade.pay');
        $data['body'] = [
                'orderCode' => $order_id,
                'totalAmount' => $price,
                'subject' => '喜刷艺术商品支付',
                'body' => '喜刷艺术商品',
                'txnTimeOut' => $timeOut,
                'payMode' => 'sand_wx',
                'payExtra' => json_encode(array('subAppid' => config('wechat.wechatDetail.app_id'), 'userId' => $openid)),
                'clientIp' => '61.129.71.103',
                'notifyUrl' => config('app.sand_notify_url'),
                'frontUrl' => config('app.sand_notify_url'),
                'extend' => ''
        ];

        $data = json_encode($data);
        // 私钥签名
        $sign = self::sign($data);

        $post = array(
            'charset' => 'utf-8',
            'signType' => '01',
            'data' =>$data,
            'sign' => $sign
        );
        $result = curl_post(self::$api_host.'order/pay', $post);
        $arr =self::parse_result($result);

        //公钥验签
        $pubkey =self::loadCert();
        try {
           self::verify($arr['data'], $arr['sign'], $pubkey);
        } catch (\Exception $e) {
           return ['code'=>405,'msg'=>'订单异常','data'=>[]];
        }

        $data = json_decode($arr['data'], true);
        if ($data['head']['respCode'] != "000000") {
            return ['code'=>403,'msg'=>'下单失败，请重试','data'=>[]];
        }

        $credential = json_decode($data['body']['credential']) ;
        return ['code'=>200,'msg'=>'','data'=>$credential];
    }

    /**
     * 退款
     *
     * @param $order_id
     * @param $onOrder_id
     * @param $price
     * @param $reason
     * @return array
     * @throws Exception
     */
    public static function refund($order_id,$onOrder_id,$price,$reason){
        $price = str_pad(price_bcmul($price),12,"0",STR_PAD_LEFT);
        $data = self::getHead('sandpay.trade.refund');
        $data['body'] = [
            'orderCode' => $order_id, //新的订单号
            'oriOrderCode' => $onOrder_id, //原订单号
            'refundAmount' => $price,
            'notifyUrl' =>config('app.sand_refund_url'),
            'refundReason' => $reason,
            'extend' => ''
        ];

        $data = json_encode($data);
        // 私钥签名
        $sign = self::sign($data);

        $post = array(
            'charset' => 'utf-8',
            'signType' => '01',
            'data' =>$data,
            'sign' => $sign
        );
        $result = curl_post(self::$api_host.'order/refund', $post);
        $arr =self::parse_result($result);

        //公钥验签
        $pubkey =self::loadCert();
        try {
            self::verify($arr['data'], $arr['sign'], $pubkey);
        } catch (\Exception $e) {
            return ['code'=>405,'msg'=>'订单异常','data'=>[]];
        }

        $data = json_decode($arr['data'], true);
        if ($data['head']['respCode'] != "000000") {
            return ['code'=>403,'msg'=>'退款失败，请重试','data'=>[]];
        }

        return ['code'=>200,'msg'=>'退款成功'];
    }

    /**
     * 处理返回值
     * @param $result
     * @return array
     */
    protected static function parse_result($result)
    {
        $arr = array();
        $response = urldecode($result);
        $arrStr = explode('&', $response);
        foreach ($arrStr as $str) {
            $p = strpos($str, "=");
            $key = substr($str, 0, $p);
            $value = substr($str, $p + 1);
            $arr[$key] = $value;
        }

        return $arr;
    }
}