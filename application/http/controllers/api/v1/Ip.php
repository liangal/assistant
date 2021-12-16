<?php

namespace app\http\controllers\api\v1;

use think\Request;
use app\http\controllers\ApiController;
use app\services\home\IpService;

class Ip extends ApiController
{
    protected $ipService;

    public function __construct(IpService $ipService){
        $this->ipService = $ipService;
    }

    /**
     * 同步IP位置信息
     *
     * @param Request $request
     * @return \think\response\Json
     */
    public function location(Request $request) {
        $result = $this->ipService->sysIpLocation();

        if (!$result) {
            $this->message('同步失败');
        }

        return $this->message('同步成功');
    }
}