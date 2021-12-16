<?php
namespace app\exceptions;

use Exception;
use think\exception\Handle;
use think\exception\HttpException;
use app\exceptions\BaseException;

class ExceptionHandler extends Handle
{
    public function render(Exception $e)
    {
        // if(config('app.app_debug')) {
        //     return parent::render($e);
        // }

        // Api请求异常
        if ($e instanceof BaseException) {
            $result = array('code'=>$e->getStatusCode(), 'msg'=>$e->getMessage());
            return json($result);
        }

        // 请求异常
        if ($e instanceof HttpException) {
            return response($e->getMessage(), $e->getStatusCode());
        }
        
        return parent::render($e);
    }
}