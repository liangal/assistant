<?php
namespace app\http\controllers\manage\api;

use app\http\controllers\ApiController;
use app\models;

class Type extends ApiController{

    /**
     * 商户列表
     */
    public function index(){

    }

    public function create(){
        $data = [
            'name'=>'fasasf',
            'img'=> 'vasasv'
        ];
        $type = \app\models\Type::create([$data]);
        var_dump($type->id);
    }

    public function update(){

    }

    public function del(){

    }
}