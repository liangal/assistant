<?php
namespace app\http\controllers\api\v1;

use app\http\controllers\ApiController;
use app\services\home\LiveService;
use think\Request;

class Live extends ApiController{
    protected $live;
    public function __construct(Request $request,LiveService $live)
    {
        $this->live = $live;
        parent::__construct($request);
    }

    public function list(Request $request){

        $search = strval($request->param('search'));
        $page = $this->page;
        $length = $this->limit;
        $start = $this->start;

        $count = $this->live->listForTotal($search);
        $data = $this->live->listForPage($start,$length);
        return $this->listMessage($data,$count,$page,count($data));
    }

    public function getNavList(){
        $liveData = $this->live->getNavList();
        return $this->listMessage($liveData,count($liveData));
    }
}