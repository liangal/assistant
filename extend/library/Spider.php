<?php

namespace library;

use GuzzleHttp\Client;

class Spider
{
    /**
     * 下载图片
     *
     * @param [type] $url
     * @param string $path
     * @return bool
     */
    public function downloadImage($url, $path)
    {
        if (!empty($path) && (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0)) {
            $client = new Client(['verify' => false]);
            try {
                $response = $client->get($url, [
                    'save_to' => $path,
                ]);
                
                if ($response->getStatusCode() == 200) {
                    return true;
                }
            } 
            catch (\GuzzleHttp\Exception\RequestException $e)
            {               
            }
        }

        return false;
    }
}