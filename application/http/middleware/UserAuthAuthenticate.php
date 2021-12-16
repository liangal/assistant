<?php

namespace app\http\middleware;

class UserAuthAuthenticate
{
    public function handle($request, \Closure $next)
    {
		if(empty($request->header('Authorization')) && empty($request->get('token'))) {
			throw new \app\exceptions\ApiException('请登陆', 202);
		}

		if(!empty($request->header('Authorization'))){
			$token = str_replace('Bearer ', '', $request->header('Authorization'));
		}
		elseif(!empty($request->get('token'))){
			$token = $request->get('token');
		}

		$authAuthenticateService = app('app\services\home\AuthAuthenticateService');

		$payload = $authAuthenticateService->verifyToken($token);

		if(empty($payload)) {
			throw new \app\exceptions\ApiException('请登陆', 202);
		}
		
		$id = array_get($payload, 'id');
		$user = $authAuthenticateService->getTokenUser($id);
		
		if(empty($user)){
			throw new \app\exceptions\ApiException('请登陆', 202);
		}

//		if($user->status != 1){
//			throw new \app\exceptions\ApiException('请登陆', 401);
//		}
//
		$request->user = $user;

        return $next($request);
    }
}
