<?php
namespace library;

class Hikvision
{   
    protected $host = '';
    protected $app_key = '';
    protected $app_secret = '';
    
    protected $charset = 'utf-8';
    protected $time;
    protected $accept = '*/*';
    protected $content_type = 'application/json';
    
    protected $http;

    public function __construct(string $app_key = '', string $app_secret = '')
    {
        if($app_key!='') $this->app_key = $app_key;
        if($app_secret!='') $this->app_secret = $app_secret;
        list($msec, $sec) = explode(' ', microtime());
		$this->time = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    }

    /**
     * 监控点数据列表
     *
     * @param integer $pageNo
     * @param integer $pageSize
     * @param integer $treeCode
     * @return void
     */
    public function regions(int $pageNo = 0, int $pageSize=100, int $treeCode = 0)
    {
        $url = '/api/resource/v1/cameras';
        $params = array('pageNo'=>$pageNo, 'pageSize'=>$pageSize, 'treeCode'=>$treeCode);
        $headers = $this->get_headers($url);

        $result = $this->post($this->host . $url, $params, $headers);

        return $result;
    }

    /**
     * 获取监控点预览取流URL
     *
     * @param string $camera_index_code 监控点唯一标识
     * @param integer $stream_type 码流类型
     * @param string $protocol 取流协议
     * @param integer $transmode 传输协议
     * @return void
     */
    public function previewURLs(string $camera_index_code, int $stream_type = 0, string $protocol = 'rtsp', int $transmode = 1)
    {
        $url = '/api/video/v1/cameras/previewURLs';
        $params = array('cameraIndexCode'=>$camera_index_code, 'streamType'=>$stream_type, 'protocol'=>$protocol, 'transmode'=>$transmode, 'expand'=>'');
        $headers = $this->get_headers($url);

        $result = $this->post($this->host . $url, $params, $headers);

        return $result;
    }

    /**
     * 手动抓图
     *
     * @param string $camera_index_code
     * @return void
     */
    public function manualCapture(string $camera_index_code)
    {
        $url = '/api/video/v1/manualCapture';
        $params = array('cameraIndexCode'=>$camera_index_code);
        $headers = $this->get_headers($url);

        $result = $this->post($this->host . $url, $params, $headers);

        return $result;
    }

    /**
     * 按事件类型订阅事件
     *
     * @param array $event_types 事件类型
     * @param string $event_dest 指定事件接收的地址
     * @return void
     */
    public function eventSubscriptionByEventTypes(array $event_types, string $event_dest)
    {
        $url = '/api/eventService/v1/eventSubscriptionByEventTypes';
        $params = array('eventTypes'=>$event_types, 'eventDest'=>$event_dest);
        $headers = $this->get_headers($url);

        $result = $this->post($this->host . $url, $params, $headers);

        return $result;
    }

    /**
     * 按事件类型订阅事件
     *
     * @param array $event_types 事件类型
     * @return void
     */
    public function eventUnSubscriptionByEventTypes(array $event_types)
    {
        $url = '/api/eventService/v1/eventUnSubscriptionByEventTypes';
        $params = array('eventTypes'=>$event_types);
        $headers = $this->get_headers($url);

        $result = $this->post($this->host . $url, $params, $headers);

        return $result;
    }

    /**
     * HTTP请求头
     *
     * @param string $url 请求接口
     * @return void
     */
    protected function get_headers(string $url) {
        $sign = $this->get_sign($url);
        
        $headers = array(
            "Accept" => $this->accept,
            "Content-Type" => $this->content_type,
            "x-Ca-Key" => $this->app_key,
            "X-Ca-Signature" => $sign,
            "X-Ca-Timestamp" => $this->time,
            "X-Ca-Signature-Headers" => "x-ca-key,x-ca-timestamp"
        );

        return $headers;
    }

    /**
     * 签名
     *
     * @param string $url 请求接口
     * @return void
     */
    protected function get_sign(string $url){
    	$sign_str = $this->get_sign_str($url); //签名字符串
        
        $priKey=$this->app_secret;

        $sign = hash_hmac('sha256', $sign_str, $priKey, true); //生成消息摘要

        $result = base64_encode($sign);

        return $result;
    }

    /**
     * 签名字符串
     *
     * @param string $url 请求接口
     * @return void
     */
    protected function get_sign_str(string $url) {
        $next = '\n';

        $http_headers = 'POST' . $next . $this->accept . $next . $this->content_type . $next;

        $custom_headers = "x-ca-key:" . $this->app_key . $next . "x-ca-timestamp:".$this->time . $next;

        $str = $http_headers . $custom_headers . $url;
        
        return $str;
    }

    /**
     * post 请求
     *
     * @param string $url 请求网址
     * @param array $params 请求参数
     * 
     * @return array
     */
    protected function post(string $url, array $params = [], $headers = [])
    {
        $http = $this->getHttpClient();

        $options = array(
            'headers' => $headers,
            'body' => json_encode($params, JSON_UNESCAPED_UNICODE)
        );

        try {
            $response = $http->post($url, $options);

            return json_decode((string) $response->getBody(), true);
        }
        catch (\GuzzleHttp\Exception\RequestException $e)
        {
            return json_decode($e->getResponse()->getBody()->getContents(), true);
        }
    }

    /**
     * 获取请求HTTP
     *
     * @return void
     */
    protected function getHttpClient() {
        if(empty($this->http)) {
            $this->http = app('GuzzleHttp\Client');
        }

        return $this->http;
    }
}