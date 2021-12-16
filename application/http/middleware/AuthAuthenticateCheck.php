<?php

namespace app\http\middleware;

class AuthAuthenticateCheck
{
    public function handle($request, \Closure $next)
    {       
        $routeInfo = $request->routeInfo();
        $rule = str_replace('manage/api/', '', $routeInfo['rule']);
        $rule = str_replace('/', ':', $rule);

        $user = $request->user;
        if(empty($user) || !$user->can($rule)) {
            throw new \app\exceptions\ApiException('未授权访问', 401);
        }

        return $next($request);
    }
}
