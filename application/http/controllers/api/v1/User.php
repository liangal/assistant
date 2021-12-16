<?php

namespace app\http\controllers\api\v1;

use app\services\WechatServices;
use think\Request;
use app\http\controllers\ApiController;
use app\services\home\AuthAuthenticateService;

class User extends ApiController
{
    protected $authAuthenticateService;
    protected $wechat;

    public function __construct(AuthAuthenticateService $authAuthenticateService, WechatServices $wechat){
        $this->authAuthenticateService = $authAuthenticateService;
        $this->wechat = $wechat;
    }

    /**
     * 登录验证获取令牌
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function login()
    {
        $result= $this->authAuthenticateService->mpAuta();

        return $result;
    }

    /**
     * 登录验证获取令牌
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function mobile()
    {
        $result= $this->authAuthenticateService->authMobile();

        return $result;
    }

    public function info(Request $request){
        $user = $request->user;

        if (empty($user)) {
            return $this->message('获取用户信息失败', 500);
        }
        $res['nickname'] = $user->nickname;
        $res['avatar'] = $user->avatar;
        $res['mobile'] = $user->mobile;
        return $this->message('', 200,$res);
    }
}
