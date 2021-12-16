<?php

namespace app\http\controllers\manage\api;

use app\services\WechatServices;
use think\Request;
use app\http\controllers\ApiController;
use think\facade\Env;
use think\response\Json;

class UploadeFile extends ApiController
{
    protected $wechat;
    public function __construct(WechatServices $wechat){
        $this->wechat = $wechat;
    }

    /**
     * 上传图片
     *
     * @param Request $request
     * @return void
     */
    public function image(Request $request)
    {

        $file = $request->file('file');
        $info = $file->move('uploads');

        if($info)
        {
            $path = $info->getPathname();

            $oss = app('library\AliOSS');
            $aliFilename = $this->randomkeys(32).'.jpg';
            $object = 'uploads/'.date('Ymd').'/'.$aliFilename;
            $result = $oss->putObject($object,$path);
            $res['path'] =$object;
            $url = preg_replace("/^(http:\/\/)?([^\/]+)/i", 'http://huajia-static.xsbaopay.com', $result['info']['url']);
            $res['url'] =$url;

            unlink($path);
            $result = array('code'=>0, 'msg'=>'', 'data'=>['src'=>$res['url'],'title'=>$aliFilename]);

        }else{
            $result = array('code'=>-1, 'msg'=>'上传失败', 'data'=>['src'=>'','title'=>'']);
        }
        return json($result);
    }

    /**
     * 上传微信素材图片
     *
     * @param Request $request
     * @return void
     */
    public function wechatImage(Request $request)
    {
        $file = $request->file('file');
        $info = $file->validate(['size'=>2097152])->move('uploads');
        if($info){
            $path = $info->getPathname();
            $app = $this->wechat->officialAccount();
            $res = $app->media->uploadImage($path);
            if(isset($res['errcode']) ){
                return $this->message($res['errmsg'], 500);
            }
            $oss = app('library\AliOSS');
            $aliFilename = $this->randomkeys(32).'.jpg';
            $result = $oss->putObject($aliFilename,$path);
            $res['path'] =$result['info']['url'];
            unlink($path);
            $fileUrl = preg_replace('/(.*)\/{1}([^\/]*)/i', '$1', $path);

            return json($res);
        }
        return $this->message($file->getError(), 500);
    }

    function randomkeys($length)
    {
        $key = '';
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
        for($i=0;$i<$length;$i++)
        {
            $key .= $pattern[mt_rand(0,35)]; //生成php随机数
        }
        return $key;
    }

    /**
     * 上传微信商品素材图片
     *
     * @param Request $request
     * @return void
     */
    public function wechatGoodsImage(Request $request)
    {
        $file = $request->file('file');
        $info = $file->move('uploads');
        if($info){
            $aliFilename = $this->randomkeys(32).'.jpg';
            $path = $info->getPathname();
            $pathWechat = 'uploads/'.date('Ymd').'/'.$aliFilename;
            $this->compressedImage($path,$pathWechat);

            $app = $this->wechat->officialAccount();
            $res = $app->media->uploadImage($pathWechat);
            if(isset($res['errcode']) ){
                return $this->message($res['errmsg'], 500);
            }

            $oss = app('library\AliOSS');

            $object = 'uploads/'.date('Ymd').'/'.$aliFilename;
            $result = $oss->putObject($object,$path);
            $res['path'] =$object;
            $res['url'] =$result['info']['url'];

            unlink($path);
            unlink($pathWechat);
            return json($res);
        }
        return $this->message($file->getError(), 500);
    }

    /**
     * 上传CKEditor图片
     *
     * @param Request $request
     * @return Json
     */
    public function ckEditorImage(Request $request)
    {
        $file = $request->file('upload');
        $info = $file->move('uploads');

        if($info)
        {
            $path = $info->getPathname();

            $oss = app('library\AliOSS');
            $aliFilename = $this->randomkeys(32).'.jpg';
            $object = 'uploads/'.date('Ymd').'/'.$aliFilename;
            $result = $oss->putObject($object,$path);
            $res['path'] =$object;
            $res['url'] =$result['info']['url'];
            unlink($path);

            $result = array('uploaded'=>1, 'fileName'=>$aliFilename, 'url'=>$res['url']);
            return json($result);
        }

        $error_msg = array('uploaded'=>0, 'error'=>array('message'=>'参数错误'));

        return json($error_msg);
    }

    /**
     * 上传CKEditor图片
     *
     * @param Request $request
     * @return Json
     */
    public function ckEditorFile(Request $request)
    {
        $file = $request->file('upload');

        $dir = 'uploads/video';

        $info = $file->validate(['ext' => 'mp4'])->move($dir);

        if($info)
        {
            $fileName = $info->getSaveName();
            $path = '/' . $dir . '/' . $fileName;
            $path = str_replace('\\', '/', $path);
            $result = array('uploaded'=>1, 'fileName'=>$fileName, 'url'=>$path);
            return json($result);
        }

        $error_msg = array('uploaded'=>0, 'error'=>array('message'=>$file->getError()));

        return json($error_msg);
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

    /**
     * 上传视频
     *
     * @param Request $request
     * @return void
     */
    public function video(Request $request)
    {
        $dir = 'uploads/video';

        $file = $request->file('file');
        $info = $file->move($dir);

        if($info)
        {
            $path = '/' . $dir . '/' . $info->getSaveName();
            $path = str_replace('\\', '/', $path);
            $result = array('code'=>0, 'msg'=>'', 'data'=>array('src'=>$path));
            return json($result);
        }

        return $this->message($file->getError(), 500);
    }

    /**
     * 上传Excel
     *
     * @param Request $request
     * @return void
     */
    public function excel(Request $request)
    {
        $dir = 'uploads/excel';

        $file = $request->file('file');
        $info = $file->validate(['ext' => 'xls'])->move($dir);

        if($info)
        {
            $path = '/' . $dir . '/' . $info->getSaveName();
            $path = str_replace('\\', '/', $path);
            $result = array('code'=>0, 'msg'=>$path);
            return json($result);
        }

        return $this->message($file->getError(), 500);
    }


    /**
     * desription 压缩图片
     * @param sting $imgsrc 图片路径
     * @param string $imgdst 压缩后保存路径
     */
    public function compressedImage($imgsrc, $imgdst) {
        list($width, $height, $type) = getimagesize($imgsrc);

        $new_width = $width;//压缩后的图片宽
        $new_height = $height;//压缩后的图片高

        if($width >= 300){
            $per = 300 / $width;//计算比例
            $new_width = $width * $per;
            $new_height = $height * $per;
        }

        switch ($type) {
            case 1:
                $giftype = check_gifcartoon($imgsrc);
                if ($giftype) {
                    header('Content-Type:image/gif');
                    $image_wp = imagecreatetruecolor($new_width, $new_height);
                    $image = imagecreatefromgif($imgsrc);
                    imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                    //90代表的是质量、压缩图片容量大小
                    imagejpeg($image_wp, $imgdst, 90);
                    imagedestroy($image_wp);
                    imagedestroy($image);
                }
                break;
            case 2:
                header('Content-Type:image/jpeg');
                $image_wp = imagecreatetruecolor($new_width, $new_height);
                $image = imagecreatefromjpeg($imgsrc);
                imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                //90代表的是质量、压缩图片容量大小
                imagejpeg($image_wp, $imgdst, 90);
                imagedestroy($image_wp);
                imagedestroy($image);
                break;
            case 3:
                header('Content-Type:image/png');
                $image_wp = imagecreatetruecolor($new_width, $new_height);
                $image = imagecreatefrompng($imgsrc);
                imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                //90代表的是质量、压缩图片容量大小
                imagejpeg($image_wp, $imgdst, 90);
                imagedestroy($image_wp);
                imagedestroy($image);
                break;
        }
    }
}
