<?php
namespace app\http\controllers\manage\api;

use app\services\manage\ExpressesService;
use think\Request;
use app\http\controllers\ApiController;
use app\services\manage\MainServices;

class Expresses extends ApiController
{
    protected $expresses;

    public function __construct(ExpressesService $expresses){
        $this->expresses = $expresses;
    }

    /**
     * 列表
     *
     * @return json
     */
    public function list()
    {
        $data = $this->expresses->getList(['is_show'=>1]);
        $res = [];
        if($data){
            $res = $data->toArray();
        }
        return $this->message('',200,$res);
    }
}
