<?php
namespace app\services\manage;

use app\repository\AdminsRepository;
use app\repository\OauthAccessTokenRepository;
use app\repository\OauthRefreshTokenRepository;
use app\repository\AdminLogRepository;
use Firebase\JWT\JWT;

class AuthAuthenticateService
{
	protected $adminsRepository;
	protected $oauthAccessTokenRepository;
	protected $oauthRefreshTokenRepository;
    protected $adminLogRepository;
    protected $time;

    public function __construct(AdminsRepository $adminsRepository, OauthAccessTokenRepository $oauthAccessTokenRepository, OauthRefreshTokenRepository $oauthRefreshTokenRepository, AdminLogRepository $adminLogRepository){
        $this->adminsRepository = $adminsRepository;
        $this->oauthAccessTokenRepository = $oauthAccessTokenRepository;
        $this->oauthRefreshTokenRepository = $oauthRefreshTokenRepository;
        $this->adminLogRepository = $adminLogRepository;
        $this->time = time();
    }

    /**
     * 登录获取令牌
     *
     * @param string $name
     * @param string $password
     * 
     * @return string
     */
    public function login(string $name, string $password)
    {
        $user = $this->adminsRepository->findByField('name', $name);
        
        if(empty($user)) {
            throw new \app\exceptions\ApiException('用户名或密码错误?');
        }

        if($user->password != md5($password)) {
            throw new \app\exceptions\ApiException('用户名或密码错误?');
        }

        if($user->status != 1){
			throw new \app\exceptions\ApiException('用户名或密码错误?', 401);
        }
        
        $access_id = md5(uniqid('JWT').time());
        $access_token = $this->getToken($access_id);
        
        $refresh_id = md5(uniqid('JWT').time());
        $refresh_token = $this->getToken($refresh_id, 'refresh');

        $expires_in = $this->time + config('sitesystem.jwt_access_token_exp');

        $token = array();
        $token['access_token'] = $access_token;
        $token['token_type'] = "bearer";
        $token['refresh_token'] = $refresh_token;
        $token['expires_in'] = $expires_in;
        $token['scope'] = 'select';
       
        $access_expires_at = date("Y-m-d H:i:s", $expires_in);
        $token_fields = array('id'=>$access_id, 'user_id'=>$user->id, 'expires_at'=>$access_expires_at);
        $this->oauthAccessTokenRepository->create($token_fields);
        
        $refresh_expires_at = date("Y-m-d H:i:s", $this->time + config('sitesystem.jwt_refresh_token_exp'));
        $refresh_fields = array('id'=>$refresh_id, 'access_token_id'=>$access_id, 'expires_at'=>$refresh_expires_at);
        $this->oauthRefreshTokenRepository->create($refresh_fields);

        $data = array();

        $data['user_id'] = $user->id;
        $data['ip'] = get_ip();
        $data['browser'] = getBrowser();

        $this->adminLogRepository->create($data);

        return $token;
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
            
        } catch (Exception $e) {
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
            ['expires_at', '>', $at]
        );
        
        $token = $this->oauthAccessTokenRepository->findWhere($where);
       
        $user = null;

        if(!empty($token)) {
            $user = $this->adminsRepository->find($token->user_id);
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
            'iss' => 'http://emg.1yop.com',//签发者
           	'aud' => 'http://emg.1yop.com',//接收方
           	'iat' => $this->time,//签发时间
           	'nbf' => $this->time,//生效时间
            'exp' => $this->time + config('sitesystem.jwt_access_token_exp')//过期时间
        );

        return $payload;
    }
}