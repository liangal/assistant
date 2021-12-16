<?php

namespace app\http\controllers\api\v1;

use think\Request;
use app\http\controllers\ApiController;
use app\services\home\CarouselServices;

class Carousel extends ApiController
{
    protected $carouselServices;

    public function __construct(CarouselServices $carouselServices){
        $this->carouselServices = $carouselServices;
    }

    /**
     * 列表
     * @return [type] [description]
     */
    public function list(Request $request)
    {
        $data = $this->carouselServices->getList();
        return $this->listMessage(count($data), $data);
    }
}
