<?php
namespace app\services\manage;

use app\repository\UsersRepository;
use think\Cache;
use app\services\WechatServices;
class UsersServices
{
	protected $userRepository;
	protected $wechat;

    public function __construct(UsersRepository $userRepository,WechatServices $wechat){
        $this->userRepository = $userRepository;
        $this->wechat = $wechat;
    }

    /**
     * @return mixed
     */
    public  function getAccessToken(){
        $app = $this->wechat->miniProgram();
        $token = $app->access_token->getToken()['access_token'];

        return $token;
    }

    /**
     * 按ID查找数据
     *
     * @param       $id
     * @param array $columns
     *
     * @return array
     */
    public function find($id, $columns = ['*']) {
        return $this->userRepository->find($id, $columns);
    }

    /**
     * 存储一条数据
     *
     * @param array $data
     * @return void
     */
    public function store(array $data) {
        $user = $this->userRepository->findByField('name', $data['openid'], ['wxapp_openid']);
        
        if(!empty($user)) {
            throw new \app\exceptions\ApiException('已存在的用户.');
        }

        $data['password'] = md5($data['password']);

        $result = $this->userRepository->create($data);

        return $result;
    }

    /**
     * 更新一条数据
     *
     * @param string $id   编号
     * @param array $data
     * 
     * @return bool
     */
    public function update(string $id, array $data) {
        $user = $this->find($id);

        if(empty($user)) {
            throw new \app\exceptions\ParameterException();
        }

       return $this->userRepository->update($data, $id);
    }

    /**
     * 删除一条数据
     *
     * @param integer $id   编号
     * 
     * @return bool
     */
    public function delete(int $id) {
        return $this->userRepository->delete($id);
    }

    /**
     * 重置密码
     *
     * @param string $id   编号
     * @param array $data
     * 
     * @return bool
     */
    public function resetPassword(string $id) {
        $fields = array('password'=>md5('888888'));
        return $this->userRepository->update($fields, $id);
    }
    
    /**
     * 更新一条数据状态
     *
     * @param string $id   编号
     * @param array $data
     * 
     * @return bool
     */
    public function updateStatus(string $id, $data) {
        return $this->userRepository->update($data, $id);
    }

    /**
     * 检索所有数据
     *
     * @param array $columns
     *
     * @return array
     */
    public function all($columns = ['*']) {
        return $this->userRepository->all($columns);
    }

    /**
     * 数据分页总数
     * 
     * @param  string $search        [搜索关键词]
     * 
     * @return int
     */
    public function listForTotal(string $search, int $type, int $status)
    {
        return $this->userRepository->listForTotal($search, $type, $status);
    }

    /**
     * 数据分页
     *
     * @param string $search 
     * @param integer $start
     * @param integer $length
     * 
     * @return array
     */
    public function listForPage(string $search, int $type, int $status, int $start, int $length)
    {
        $list = $this->userRepository->listForPage($search, $type, $status, $start, $length)->toArray();
        
        foreach($list as $key=>$user) {
            $list[$key]['id'] = strval($user['id']);
            $list[$key]['register_time'] = $user['register_time']?date('Y-m-d H:i:s',$user['register_time']):0;
            $list[$key]['is_del'] = $user['delete_at']?1:0;
        }

        return $list;
    }

    /**
     * 用户分类
     */
    public function getTypes() {
        return config('category.user_type');
    }
}