<?php
namespace app\services\home;

use app\repository\UsersRepository;
use app\repository\OauthAccessTokenRepository;
use app\repository\OauthRefreshTokenRepository;
use app\services\WechatServices;
use Firebase\JWT\JWT;
use think\facade\Log;
use think\Request;

class AuthAuthenticateService
{
	protected $user;
	protected $oauthAccessTokenRepository;
    protected $wechat;
    protected $time;

    public function __construct(UsersService $user, OauthAccessTokenRepository $oauthAccessTokenRepository,WechatServices $wechat){
        $this->user = $user;
        $this->oauthAccessTokenRepository = $oauthAccessTokenRepository;
        $this->wechat = $wechat;
        $this->time = time();
    }

    /**
     * 操作
     */
    public function message($msg, $code = 200, $data=[])
    {
        $data = array(
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        );

        return json($data);
    }

    public function  mpAuta(){
        $request = Request();
        $code =  $request->post('code');
        $iv = $request->post('iv');
        $encryptedData = $request->post('encryptedData');
        $app = $this->wechat->miniProgram();

        if (empty($code) || empty($iv) || empty($encryptedData)) {
           return $this->message('参数错误',401);
        }


        if(is_array($code) && $code['errMsg']=='login:ok'){
            $code = $code['code'];
        }
        if (!is_string($code)) {
            return $this->message('code无效',401);
        }

        $session = $app->auth->session($code);
        if (isset($session['errcode'])) {
            return $this->message($session['errmsg'],401);
        }

        try{
            $decryptedData = $app->encryptor->decryptData($session['session_key'], $iv, $encryptedData);
            $uid = $this->user->addUserInfo($decryptedData);
            if(empty($uid)){
                return $this->message('授权失败',401);
            }
        }catch (\Exception $e){
            log::write(['code'=>$code,'iv'=>$iv,'encryptedData'=>$encryptedData]);
        }


        $access_id = md5(uniqid('JWT').time());
        $access_token = $this->getToken($access_id);
        $expires_in = $this->time + config('sitesystem.jwt_access_token_exp');

        $access_expires_at = date("Y-m-d H:i:s", $expires_in);
        $token_fields = array('id'=>$access_id, 'user_id'=>$uid, 'expires_at'=>$access_expires_at);
        $this->oauthAccessTokenRepository->create($token_fields);

        $token = array();
        $token['access_token'] = $access_token;
        $token['expires_in'] = $expires_in;

        return $this->message('授权成功', 200,$token);
    }

    public function authMobile(){
        $request = Request();
        $code =  $request->post('code');
        $iv = $request->post('iv');
        $encryptedData = $request->post('encryptedData');

        $app = $this->wechat->miniProgram();

        if (empty($code) || empty($iv) || empty($encryptedData)) {
            return $this->message('参数错误',401);
        }

        if(is_array($code) && $code['errMsg']=='login:ok'){
            $code = $code['code'];
        }
        if (!is_string($code)) {
            return $this->message('code无效',401);
        }

        $session = $app->auth->session($code);
        if (isset($session['errcode'])) {
            return $this->message($session['errmsg'],401);
        }

        $decryptedData = $app->encryptor->decryptData($session['session_key'], $iv, $encryptedData);
        $mobile = $decryptedData['phoneNumber'];
        if (empty($mobile)) {
            return $this->message('手机号码无效',203, '');
        }

        //获取用户
        if(!empty($request->header('Authorization'))){
            $token = str_replace('Bearer ', '', $request->header('Authorization'));
        }else{
            return $this->message('无效用户',201);
        }

        $authAuthenticateService = app('app\services\home\AuthAuthenticateService');
        $payload = $authAuthenticateService->verifyToken($token);
        if(empty($payload)) {
            throw new \app\exceptions\ApiException('请登陆', 401);
        }
        $id = array_get($payload, 'id');
        $user = $authAuthenticateService->getTokenUser($id);
        if(empty($user)){
            return $this->message('无效用户',201);
        }

        $res = $this->user->update(['mobile'=>$mobile],['id'=>$user->id]);
        if(empty($res)){
            return $this->message('授权失败',401);
        }

        return $this->message('授权成功', 200,$token);
    }

    /**
     * 获取令牌
     *
     * @param string $id
     * @param string $scopes
     * 
     * @return string
     */
    protected function getToken(string $id, string $scopes = 'access')
    {
        $secret = config('sitesystem.jwt_secret');
        $payload = $this->getPayload($id);

        if($scopes == 'refresh') {
            $payload['exp'] = $this->time + config('sitesystem.jwt_refresh_token_exp');
        }

        return JWT::encode($payload, $secret);
    }

    /**
     * 验证token
     *
     * @param string $token
     * 
     * @return array
     */
    public function verifyToken(string $token) {
        $secret = config('sitesystem.jwt_secret');
        JWT::$leeway = 60;

        $payload = array();

        $tokens = explode('.', $token);
        if (count($tokens) != 3){
            return $payload;
        }

        try {
            $decoded = JWT::decode($token, $secret, ['HS256']);
            $payload = (array) $decoded;
        } catch (\Firebase\JWT\SignatureInvalidException $e) {

        } catch (\Firebase\JWT\BeforeValidException $e) {

        } catch (\Firebase\JWT\ExpiredException $e) {

        } catch (\Exception $e) {
            throw new \app\exceptions\ApiException($e->getMessage());
        }

        return $payload;
    }
    
    /**
     * 获取令牌用户
     *
     * @param string $id
     * 
     * @return Object|null
     */
    public function getTokenUser(string $id)
    {
        
        $at = date("Y-m-d H:i:s");
            
        $where = array(
            ['id', '=', $id],
            ['expires_at', '>', $at],
        );
        
        $token = $this->oauthAccessTokenRepository->findWhere($where);
       
        $user = null;

        if(!empty($token)) {
            $user = $this->user->findWhere(['id'=>$token->user_id,'delete_at'=>0]);
        }

        return $user;
    }

    /**
     * 载荷
     *
     * @param string $id
     * 
     * @return array
     */
    protected function getPayload(string $id) {
        $payload = array(
            'id' => $id,//唯一身份标识
            'iss' => 'http://emg.com',//签发者
           	'aud' => 'http://emg.com',//接收方
           	'iat' => $this->time,//签发时间
           	'nbf' => $this->time,//生效时间
            'exp' => $this->time + config('sitesystem.jwt_access_token_exp')//过期时间
        );

        return $payload;
    }
}