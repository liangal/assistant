<?php

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;

class PreviewHttp extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('previewhttp');
        // 设置参数
    }

    protected function execute(Input $input, Output $output)
    {
        $sitesystem = config('sitesystem.');

        $secret = $sitesystem['jwt_secret'];
        $url = $sitesystem['site_domain'] . '/manage/autopreview';

        $http = app('GuzzleHttp\Client');

        try {
            $http->post($url,[
                'form_params' => array('secret'=>$secret)
            ]);
        }
        catch (\GuzzleHttp\Exception\RequestException $e)
        {

        }
    }
}