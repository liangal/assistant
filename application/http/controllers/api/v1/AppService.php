<?php

namespace app\http\controllers\api\v1;

use think\Request;
use app\http\controllers\ApiController;
use app\services\home\ApplicationService;

class AppService extends ApiController
{
    protected $applicationService;

    public function __construct(ApplicationService $applicationService){
        $this->applicationService = $applicationService;
    }

    /**
     * 列表
     * @return [type] [description]
     */
    public function list(Request $request)
    {
        $data = $this->applicationService->list();
        $count = count($data['service']) + count($data['pwelfare']);

        return $this->listMessage($count, $data);
    }
}
