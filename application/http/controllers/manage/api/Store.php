<?php
namespace app\http\controllers\manage\api;

use app\http\controllers\ApiController;
use app\services\manage\StoreService;

class Store extends ApiController{

    /**
     * 商户列表
     */
    public function index(){
        return (new StoreService())->index();
    }

    public function create(){
        return (new StoreService())->create();
    }

    public function update(){
        return (new StoreService())->update();
    }

    public function del(){
        return (new StoreService())->del();
    }
}