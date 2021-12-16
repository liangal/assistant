<?php

namespace app\http\controllers\manage\api;

use think\Request;
use app\http\controllers\ApiController;

class AliOSS extends ApiController
{
    public function __construct(){}

    /**
     * 签名
     * @return [type] [description]
     */
    public function signature(Request $request)
    {
        $type = strval($request->param('type'));
        $oss = app('library\AliOSS');
        $data = $oss->signature($type);

        return $this->message($data);
    }

    public function getVideo($param){
        $path = $param;
        $oss = app('library\AliOSS');
        $result = $oss->getVideoUrl($path);
        if($result['code']!=0) {
            return $this->message($result['msg'],500);
        }

        return $result;
    }

    public function put(Request $request){
        $path = strval($request->param('object'));
        $oss = app('library\AliOSS');
        $result = $oss->putObject('xis',$path);
        if(!$result) {
            return $this->message("操作失败");
        }

        return $this->message("操作成功");
    }

    /**
     * 删除文件
     * @return [type] [description]
     */
    public function delete(Request $request)
    {
        $object = strval($request->param('object'));
        $type = strval($request->param('type'))?strval($request->param('type')):'image';

        if(empty($object))
        {
            return $this->message("选择要删除的文件", 500);
        }

        $oss = app('library\AliOSS');
        $result = $oss->deleteObject($object,$type);
        if(!$result) {
            return $this->message("操作失败");
        }

        return $this->message("操作成功");
    }
}
