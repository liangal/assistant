<?php

namespace app\http\controllers\manage\api;

use think\Controller;
use think\Request;

use app\services\manage\AuthAuthenticateService;

class Oauth extends Controller
{
    protected $authAuthenticateService;

    public function __construct(AuthAuthenticateService $authAuthenticateService){

        $this->authAuthenticateService = $authAuthenticateService;
    }

    /**
     * 验证获取令牌
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function token(Request $request)
    {

        $name = $request->post('name');
        $password = $request->post('password');

        if(empty($name) || empty($password)) {
            throw new \app\exceptions\ParameterException();
        }

        $result = $this->authAuthenticateService->login($name, $password);

        return json($result);
    }
}
