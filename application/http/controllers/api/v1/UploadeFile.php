<?php

namespace app\http\controllers\api\v1;

use think\Request;
use app\http\controllers\ApiController;
use think\facade\Env;

class UploadeFile extends ApiController
{
    public function __construct(){
        
    }

    /**
     * 上传图片(base64)
     */
    public function imageBase64(Request $request)
    {
        $base64_data = $request->post('data');
        
        if(!empty($base64_data)) {
            $root_path = Env::get('root_path') . '/public';
            $path = $root_path . '/uploads/' . date('Ymd') . '/';
            createDir($path);
            $base64_img = trim($base64_data);

            if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_img, $result)){
                $type = $result[2];

                if(in_array($type, array('pjpeg', 'jpeg', 'jpg', 'gif', 'bmp', 'png'))){
                    $fine_name = str_rand() . '.' . $type;
                    $new_file = $path . $fine_name;

                    if(file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_img)))){
                        $img_path = str_replace($root_path, "", $new_file);
                        $result = array('code'=>0, 'msg'=>'', 'data'=>array('src'=>$img_path));
                        return json($result);
                    }
                }
                else
                {
                    return $this->message('图片上传类型错误', 500);
                }
            }
        }

        return $this->message('图片上传失败', 500);
    }
}