<?php
namespace app\http\controllers\api\v1;

use think\Request;
use app\http\controllers\ApiController;
use app\services\home\SearchService;

class Search extends ApiController{
    protected $search;
    
    public function __construct(SearchService $search)
    {
        $this->search = $search;
    }

    public function list(Request $request){
        $title  = strval($request->get('title'));
        $field  = strval($request->get('field', 'id'));
        $sort   = strval($request->get('sort', 'desc'));

        $result = $this->search->goodsListForPage($title, 0, $field, $sort, 1, $this->start, $this->limit);
        $result['pageInfo']['page']         = $this->page;
        $result['pageInfo']['pageCount']    = count($result['list']);
        return $this->listMessage($result['pageInfo']['pageCount'], $result);
    }
}