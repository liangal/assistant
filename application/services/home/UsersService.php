<?php
namespace app\services\home;

use app\repository\UsersRepository;
use app\services\manage\GoodsSpecOptionsServices;

class UsersService{
    protected $userRepository;
    public function __construct(UsersRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function find($id){
       return $this->userRepository->find($id);
    }

    public function findWhere($where){
        return $this->userRepository->findWhere($where);
    }

    /**
     * @param $userinfo
     * @return array
     * 添加用户
     */
    public function addUserInfo($userinfo)
    {
        $userModel = $this->userRepository->findByField('wxapp_openid', $userinfo['openId']);
        $data = [];
        $uid = '';
        if (!$userModel) {
            $data['weuid'] = 0;
            $data['openid'] = '';
            $data['realname'] = '';
            $data['mobile'] = '';
            $data['state'] = 0;
            $data['usertype'] = 0;
            $data['pay_count'] = 0;
            $data['pay_money'] = 0;
            $data['balance'] = 0;
            $data['parent_uid'] = 0;
            $data['child_nums'] = 0;
            $data['lat'] = 0;
            $data['lng'] = 0;
            $data['wxapp_openid'] = $userinfo['openId'];
            $data['nickname'] = $userinfo['nickName'];
            $data['avatar'] = $userinfo['avatarUrl'];
            $data['gender'] = $userinfo['gender'];
            $data['province'] = $userinfo['province'];
            $data['country'] = $userinfo['country'];
            $data['city'] = $userinfo['city'];
            $data['register_time'] = time();
//            $userModel->ip = CLIENT_IP;
            $user =  $this->userRepository->create($data);
            if($user){
                $uid = $user->id;
            }
        }else{

            if($userModel->delete_at){
                return false;
            }
            $data['nickname'] = $userinfo['nickName'];
            $data['avatar'] = $userinfo['avatarUrl'];
            $data['gender'] = $userinfo['gender'];
            $data['province'] = $userinfo['province'];
            $data['country'] = $userinfo['country'];
            $data['city'] = $userinfo['city'];
            $data['update_at'] = time();
//            $data['last_login_time'] = get_time();
            $user = $this->update($data,['wxapp_openid'=>$userinfo['openId']]);
            if($user){
                $uid = $userModel->id;
            }
        }
        return $uid;
     }

     public function update(array $data,$where){
       return $this->userRepository->updateWhere($data,$where);
     }
}