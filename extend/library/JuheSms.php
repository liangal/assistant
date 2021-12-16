<?php

namespace library;

class JuheSms
{
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
        return config('sitesystem.juhe');
    }

    /**
     * 发送短信
     *
     * @param string $mobile 手机号
     * @param string $tpl_id 短信模板ID
     * @param array  $tpl_value 模板变量值
     * 
     * @return array
     */
    public function sendSms($mobile, $tpl_id, $tpl_value){
        $config = $this->config();

        if (empty($mobile) || empty($tpl_id)) {
            return false;
        }

        $smsConf = [
            'key'       => $config['key'],
            'mobile'    => $mobile, 
            'tpl_id'    => $tpl_id
        ];

        if(!empty($tpl_value)) {
            $smsConf['tpl_value'] = $tpl_value;
        }

        $result = $this->post($config['url'], $smsConf);

        if($result['error_code'] == 0) {               
            return true;
        }

        return false;
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