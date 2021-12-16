<?php

namespace library;

use JPush\Client as JPush;

class SystemInformationJpush
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
        return config('sitesystem.jpush');
    }

    /**
     * 系统消息推送
     *
     * @param string $id 内容编号
     * @param string $title 标题
     * @param string  $content 内容
     * 
     * @return array
     */
    public function sysInfoPush(string $id, string $title, string $content){
        $jpush_config = $this->config();

        if (!empty($content) && !empty($jpush_config)) {
            $app_key = $jpush_config['key'];
            $master_secret = $jpush_config['secret'];

            $client = new JPush($app_key, $master_secret);
            $pusher = $client->push();
            $pusher->addAllAudience();
            $pusher->setPlatform(['android', 'ios']);

            $pusher->androidNotification($content, ['title'=>$title, 'extras'=>array('id'=>$id)]);
            $pusher->iosNotification($content, ['extras'=>array('id'=>$id)]);

            try {
                return $pusher->send();
            } catch (\JPush\Exceptions\APIConnectionException $e) {
                throw new \app\exceptions\ParameterException($e);
            } catch (\JPush\Exceptions\APIRequestException $e) {
                throw new \app\exceptions\ParameterException($e);
            }
        }
    }
}