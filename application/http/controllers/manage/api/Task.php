<?php
namespace app\http\controllers\manage\api;

use app\http\controllers\ApiController;
use app\models;
use app\services\manage\TaskService;
use think\Request;

class Task extends ApiController{

    /**
     * 商户列表
     */
    public function list(){
        return (new TaskService())->index();
    }

    public function create(){
        return TaskService::create();
    }

    public function update(){
        return (new TaskService())->update();
    }

    public function del(){
        return (new TaskService())->del();
    }
}