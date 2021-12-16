<?php

namespace library;

class Fluorite
{
    private $url = 'https://open.ys7.com/api/';

    /**
     * Create a new class instance.
     *
     * @param $guard
     *
     */
    public function __construct()
    {
        
    }

    /**
     * 配置信息
     *
     * @return array
     */
    protected function config() {
        return config('sitesystem.monitor.fluorite');
    }

    /**
     * 获取accessToken
     *
     * @return void
     */
    protected function getToken()
    {
        $access_token = cache('fluorite_open_access_token');

        if(empty($access_token)) {
            $url = $this->url . 'lapp/token/get';

            $config = $this->config();

            if(!empty($config)) {
                $params = array('appKey'=>$config['app_key'], 'appSecret'=>$config['secret']);
                $result = $this->post($url, $params);
                
                if($result['code'] == 200 && !empty($result['data'])) {               
                    // $expire = $result['data']['expireTime'] - msectime();
                    // $expire = intval($expire / 1000);
                    $expire = 518400;
                    $access_token = $result['data']['accessToken'];
                    cache('fluorite_open_access_token', $access_token, $expire);
                }
            }
        }

        return $access_token;
    }

    /**
     * 获取用户下直播视频列表
     *
     * @return void
     */
    public function videoList()
    {
        $accessToken = $this->getToken();
        $url = $this->url . 'lapp/live/video/list';
        $params = array('accessToken'=>$accessToken, 'pageStart'=>0, 'pageSize'=>50);
        $result = $this->post($url, $params);
        return $result;
    }

    /**
     * 获取单个设备信息
     *
     * @param [type] $device_serial 设备序列号
     * @return array
     */
    public function deviceInfo($device_serial)
    {
        $accessToken = $this->getToken();
        $url = $this->url . 'lapp/device/info';
        $params = array('accessToken'=>$accessToken, 'deviceSerial'=>$device_serial);
        $result = $this->post($url, $params);
        return $result;
    }

    /**
     * 设备抓拍图片
     *
     * @param [type] $device_serial
     * @param [type] $channel_no
     * @return void
     */
    public function deviceCapture($device_serial, $channel_no)
    {
        $accessToken = $this->getToken();
        $url = $this->url . 'lapp/device/capture';
        $params = array('accessToken'=>$accessToken, 'deviceSerial'=>$device_serial, 'channelNo'=>$channel_no);
        $result = $this->post($url, $params);
        return $result;
    }

    /**
     * post 请求
     *
     * @param string $url 请求网址
     * @param array $params 请求参数
     * 
     * @return array
     */
    protected function post(string $url, array $params = [])
    {
        $http = app('GuzzleHttp\Client');

        try {
            $response = $http->post($url, [
                'form_params' => $params,
            ]);

            return json_decode((string) $response->getBody(), true);
        } 
        catch (\GuzzleHttp\Exception\RequestException $e)
        {
            return json_decode($e->getResponse()->getBody()->getContents(), true);
        }
    }
}