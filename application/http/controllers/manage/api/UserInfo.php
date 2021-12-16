<?php

namespace app\http\controllers\manage\api;

use think\Request;
use app\http\controllers\ApiController;
use app\services\manage\UserInfoServices;

class UserInfo extends ApiController
{
    protected $userInfoServices;

    public function __construct(UserInfoServices $userInfoServices){
        $this->userInfoServices = $userInfoServices;
    }

    /**
     * 更新
     *
     * @param Request $request
     * 
     * @return json
     */
    public function update(Request $request) {
        $user = $request->user;

        if (empty($user)) {
            throw new \app\exceptions\ParameterException();
        }

        $id = $user->id;

        $data = $request->only(['nickname', 'avatar', 'sex', 'phone']);

        $this->userInfoServices->update($id, $data);

        return $this->message('操作成功');
    }

    /**
     * 用户基本信息
     *
     * @param Request $request
     * @return void
     */
    public function info(Request $request) {
        $user = $request->user;

        if (empty($user)) {
            throw new \app\exceptions\ParameterException();
        }

        $id = $user->id;

        $userinfo = $this->userInfoServices->find($id);

        if(!empty($userinfo)) {
            return $this->message($userinfo);
        }

        return $this->message('获取用户信息失败', 500);
    }

    /**
     * 更新密码
     *
     * @param Request $request
     *
     * @return json
     */
    public function updatePassword(Request $request) {
        $user = $request->user;

        if (empty($user)) {
            throw new \app\exceptions\ParameterException();
        }

        $id = $user->id;

        $data = $request->only(['oldPsw', 'newPsw', 'rePsw']);

        if(empty($data['oldPsw'])) {
            return $this->message('原始密码不正确', 500);
        }

        if(empty($data['newPsw']) || empty($data['rePsw']) || $data['newPsw'] != $data['rePsw']) {
            return $this->message('两次输入密码不一样', 500);
        }

        $userinfo = $this->userInfoServices->find($id);

        if($userinfo->password != md5($data['oldPsw'])) {
            return $this->message('原始密码不正确', 500);
        }

        $this->userInfoServices->update($id, ['password'=>md5($data['newPsw'])]);

        return $this->message('操作成功');
    }

}
