<?php
namespace app\http\controllers\manage\api;

use think\Request;
use app\http\controllers\ApiController;
use app\services\manage\AdminLogServices;

class AdminLog extends ApiController
{
    protected $adminLogServices;

    public function __construct(AdminLogServices $adminLogServices){
        $this->adminLogServices = $adminLogServices;
    }

    /**
     * 列表
     *
     * @param Request $request
     * @return void
     */
    public function list(Request $request)
    {
        $search = strval($request->param('search'));

        $page = intval($request->param('page'));
        $length = intval($request->param('limit'));
        $start = ($page * $length) - $length;
        
        $count = $this->adminLogServices->listForTotal($search);
        $data = $this->adminLogServices->listForPage($search, $start, $length);

        $list['code'] = 0;
        $list['msg'] = '';
        $list['count'] = $count;
        $list['data'] = $data;
        return json($list);
    }
}