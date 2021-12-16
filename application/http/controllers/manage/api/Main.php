<?php
namespace app\http\controllers\manage\api;

use think\Request;
use app\http\controllers\ApiController;
use app\services\manage\MainServices;

class Main extends ApiController
{
    protected $mainServices;

    public function __construct(MainServices $mainServices){

        $this->mainServices = $mainServices;
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
        $show = $this->mainServices->getData();

        return $this->message('',200,$show);
    }
}
