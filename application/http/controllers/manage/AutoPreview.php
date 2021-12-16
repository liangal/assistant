<?php

namespace app\http\controllers\manage;

use think\Request;
use think\Console;
use think\Controller;

class AutoPreview extends Controller
{
    public function __construct()
    {

    }

    /**
     * 执行命令
     * @return [type] [description]
     */
    public function executecommand(Request $request)
    {
        $secret = $request->post('secret');
        if($secret == config('sitesystem.jwt_secret')) {
            $output = Console::call('autopreview');

            $output->fetch();

            echo "ok";
        }
    }
}