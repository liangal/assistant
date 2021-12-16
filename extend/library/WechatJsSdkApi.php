<?php

namespace library;

use Cache;

class WechatJsSdkApi {

    public function __construct(){

    }

    /**
    *获取ACCESS_TOKEN
    */
    protected function getAccessToken()
    {
        $appid = config('sitesystem.wechat_key_id');
        $secret = config('sitesystem.wechat_secret');

        $token_key = 'jsapi_access_token';

        $accessToken = Cache::get($token_key);

        if(empty($accessToken)) {
            $accessTokenUrl = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$secret";

            $result = $this->httpGet($accessTokenUrl);

            if (!empty($result['access_token'])) {
                $accessToken = $result['access_token'];
                Cache::set($token_key, $accessToken, 120);
            }
        }

        return $accessToken;
    }

    /**
     * 获取 jsapi_ticket
     * @param  string $normal_access_token [普通access_token]
     * @return [type]               [description]
     */
    protected function getJsApiTicket(string $accessToken)
    {
        $ticket_key = 'jsapiticket';

        $jsapiTicket = Cache::get($ticket_key);

        if(empty($jsapiTicket)) {
            $ticketUrl = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$accessToken&type=jsapi";

            $result = $this->httpGet($ticketUrl);

            if (!empty($result['ticket'])) {
                $jsapiTicket = $result['ticket'];
                Cache::set($ticket_key, $jsapiTicket, 120);               
            }
        }

        return $jsapiTicket;
    }

    /**
     * 获取签名
     * @param  string $url [该url为调用jssdk接口的url]
     * @return [type]      [description]
     */
    public function getSignPackage(string $url) {
        // 获取token
        $access_token = $this->getAccessToken();
        
        if (empty($access_token)) {
            return false;
        }

        // 获取ticket
        $ticket = $this->getJsApiTicket($access_token);

        if (empty($ticket)) {
            return false;
        }

        // 生成时间戳
        $time_stamp = time();
        
        // 生成随机字符串
        $noncestr = str_rand(16);

        $par_string = "jsapi_ticket=$ticket&noncestr=$noncestr&timestamp=$time_stamp&url=$url";

        $signature = sha1($par_string);
        
        $signPackage = array (
            "appid" => config('sitesystem.wechat_key_id'),
            "timestamp" => $time_stamp,
            "noncestr" => $noncestr,
            "signature" => $signature
        );
        
        return $signPackage;
    }

    /**
     * Http Get请求
     * @param  string $url [description]
     * @return [type]      [description]
     */
    protected function httpGet(string $url)
    {
        $http = new \GuzzleHttp\Client;

        $response = $http->get($url);

        return json_decode((string) $response->getBody(), true);
    }
}