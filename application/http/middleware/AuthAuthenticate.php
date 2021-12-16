<?php

namespace app\http\middleware;

use Auth;

class AuthAuthenticate
{
    public function handle($request, \Closure $next, $name)
    {

        $permission_name = trim(substr($request->path(),strpos($request->path(),'/')), '/'); 
        echo $permission_name;
        die();
    	if(!Auth::guard($name)->guest()){
    		//http://tp5.test/manage/product/edit
			if($request->isAjax() || !$request->controller()){
				//product/edit 
				$permission_name = trim(substr($request->path(),strpos($request->path(),'/')), '/'); 
			}else{ 
				$permission_name = strtolower($request->controller()) . '/' . $request->action(true); 
			}

			echo $permission_name;
    		die();
            return $this->redirect('/manage/login');
        }

        return $next($request);
    }
}
