<?php
namespace app\http\controllers\manage\api;

use think\Request;
use app\http\controllers\ApiController;
use app\services\manage\SiteStatisticServices;

class EntranceStatistic extends ApiController
{
    protected $siteStatisticServices;

    public function __construct(SiteStatisticServices $siteStatisticServices){
        $this->siteStatisticServices = $siteStatisticServices;
    }

    /**
     * 首页
     *
     * @param Request $request
     *
     * @return json
     */
    public function index(Request $request)
    {
        $data = $this->siteStatisticServices->getData('main');

        return $this->message($data);
    }
}