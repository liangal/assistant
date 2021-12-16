<?php

namespace app\http\controllers;

use think\Request;
use think\Controller;

class ApiController extends Controller
{
    protected $page     = 0;
    protected $start    = 0;
    protected $limit    = 15;   // 默认一页15条数据

	public function __construct(Request $request)
    {
        if ($request->param('limit') != null) {
            $this->limit = intval($request->param('limit'));
            $this->limit = $this->limit <= 0 ? 15 : $this->limit;
        }

        if ($request->param('page') != null) {
            $this->page = (intval($request->param('page')));
            $this->page = $this->page <= 1 ? 1 : $this->page;
            $this->start = ($this->page-1)*$this->limit;
        }

        parent::__construct();
    }

    /**
     * 操作
     */
    public function message($msg, $code = 200, $data=[])
    {
        $data = array(
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        );

        return json($data);
    }

    /**
     * 列表
     */
    public function listMessage($data,$count=0, $page=0,$limit=0)
    {
        $data = array(
            'code' => 200,
            'msg' => '',
            'data' => array(
                'list'=>$data,
                'pageInfo'=>$this->pageDate($count,$page,$limit)
            )
        );

        return json($data);
    }

    /**
     * 分页信息
     * @param $count
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function pageDate($count,$page=0,$limit=10){
        $info = [
          'total'=>$count,
          'page'=>$page,
          'pageCount'=>$limit
        ];

        return $info;
    }
}
