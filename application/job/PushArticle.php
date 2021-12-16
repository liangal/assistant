<?php

namespace app\job;

use think\queue\Job;

/**
 * 推送新闻
 */
class PushArticle
{
    public function fire(Job $job, $data)
    {
        $pushArticle = app('app\services\manage\PushArticle');

        if ($job->attempts() > 3) {
            $job->delete();
        }
        else
        {
            $result = $pushArticle->pushPiece($data['id'], $data['action']);

            if(!$result) {
                $job->release(3);
            }else {
                $job->delete();
            }
        }
    }
}