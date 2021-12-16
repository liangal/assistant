<?php

namespace app\http\controllers\api\v1;

use app\services\home\ThirdService;
use think\Request;
use app\http\controllers\ApiController;

class Third extends ApiController
{
    protected $third;
    protected $msgSrc;
    protected $SystemNumber;//系统编号
    protected $msgType='wx.unifiedOrder';
    protected $mid;//商户号
    protected $tid;//终端号
    protected $totalAmount;//终端号
    protected $subAppId;//bu
    protected $subOpenId;//bu
    protected $tradeType = 'MINI';//bu
    protected $sign;//bu

    public function __construct(ThirdService $third){
        $this->third = $third;
    }

    public function confirm($order_id){
        $requestTimestamp = date('Y-m-d H:i:s',time());
        $this->third->confirm($order_id);
    }
}