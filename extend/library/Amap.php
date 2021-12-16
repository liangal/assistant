<?php

namespace library;

class Amap
{
    private $url = 'https://restapi.amap.com/v3';
    
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
    protected function getKey() {
        return config('sitesystem.amap_key');
    }

    /**
     * 逆地理编码
     * 
     * @return array
     */
    public function regeo($location) {
        $key = $this->getKey();
        $url = $this->url . '/geocode/regeo?key=' . $key . '&location=' . $location;
        $result = $this->get($url);

        return $result;
    }

    /**
     * 天气查询
     * 
     * @return array
     */
    public function weatherInfo($city) {
        $key = $this->getKey();
        $url = $this->url . '/weather/weatherInfo?key=' . $key . '&city=' . $city;
        $result = $this->get($url);

        return $result;
    }

    /**
     * get 请求
     *
     * @param string $url 请求网址
     * @param array $params 请求参数
     * 
     * @return array
     */
    protected function get(string $url)
    {
        $http = app('GuzzleHttp\Client');

        try {
            $response = $http->get($url);

            return json_decode((string) $response->getBody(), true);
        } 
        catch (\GuzzleHttp\Exception\RequestException $e)
        {
            return json_decode($e->getResponse()->getBody()->getContents(), true);
        }
    }
}