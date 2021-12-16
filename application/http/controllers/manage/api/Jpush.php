<?php

namespace app\http\controllers\manage\api;

use think\Request;
use app\http\controllers\ApiController;
use library\SystemInformationJpush;

class Jpush extends ApiController
{
    protected $systemInformationJpush;

    public function __construct(SystemInformationJpush $systemInformationJpush){
        $this->systemInformationJpush = $systemInformationJpush;
    }

    /**
     * 推送
     *
     * @param Request $request
     * 
     * @return json
     */
    public function push(Request $request) {
        $id = strval($request->post('id'));
        $title = strval($request->post('title'));
        $content = strval($request->post('content'));
        
        if (empty($id) || empty($title) || empty($content)) {
            return $this->message('参数错误', 500);
        }

        $result = $this->systemInformationJpush->sysInfoPush($id, $title, $content);

        if(!empty($result) && $result['http_code'] == 200) {
            return $this->message('推送成功');
        }
        
        return $this->message('推送失败');
    }
}