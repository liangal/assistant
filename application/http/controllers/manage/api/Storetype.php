<?php
namespace app\http\controllers\manage\api;

use app\http\controllers\ApiController;
use app\models;
use app\services\manage\TypeService;
use think\Request;

class Storetype extends ApiController{

    /**
     * 商户列表
     */
    public function list(Request $request){
       return (new TypeService())->index();
    }

    public function save(){
       return TypeService::create();
    }

    public function update(){
        return (new TypeService())->update();
    }

    public function del(){
        return (new TypeService())->del();
    }
}