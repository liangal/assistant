<?php

namespace app\http\controllers\manage\api;

use think\Request;
use app\http\controllers\ApiController;

use app\services\manage\MenuServices;

class Menu extends ApiController
{
    protected $menuServices;

    public function __construct(MenuServices $menuServices){
        $this->menuServices = $menuServices;
    }

    /**
     * 列表
     *
     * @param Request $request
     * 
     * @return json
     */
    public function list(Request $request)
    {
        $menus = $this->menuServices->list();
        return json($menus);
    }
}
