<?php
namespace library;

use OSS\OssClient;
use OSS\Core\OssException;

class AliOSS
{
    public function __construct(){}

    /**
     * 配置信息
     *
     * @return array
     */
    protected function config() {
        return config('sitesystem.alioss');
    }

    /**
     * 直传前面
     */
    public function signature($type='image')
    {
        $config = $this->config();

        $id= $config['id'];
        $key= $config['secret'];

        if($type=='video'){
            $host = $config['video_host'];
            $bucket = $config['video_bucket'];
        }else{
            $host = $config['host'];
            $bucket = $config['bucket'];
        }

        $dir = 'uploads/' . date('Ymd') . '/';

        $now = time();
        $expire = 30;
        $end = $now + $expire;
        $expiration = gmt_iso8601($end);

        //最大文件大小.用户可以自己设置
        $condition = array(0=>'content-length-range', 1=>0, 2=>1048576000);
        $conditions[] = $condition; 

        // 表示用户上传的数据，必须是以$dir开始，不然上传会失败，这一步不是必须项，只是为了安全起见，防止用户通过policy上传到别人的目录。
        $start = array(0=>'starts-with', 1=>'$key', 2=>$dir);
        $conditions[] = $start; 

        $arr = array('expiration'=>$expiration,'conditions'=>$conditions);
        $policy = json_encode($arr);
        $base64_policy = base64_encode($policy);
        $string_to_sign = $base64_policy;
        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $key, true));

        $response = array();
        $response['accessid'] = $id;
        $response['host'] = $host;
        $response['policy'] = $base64_policy;
        $response['signature'] = $signature;
        $response['expire'] = $end;
        $response['bucket'] = $bucket;
        $response['dir'] = $dir;

        return $response;
    }

    public  function getVideoUrl(string $object){
        $config = $this->config();

        $accessKeyId = $config['id'];
        $accessKeySecret = $config['secret'];
        $endpoint = $config['endpoint'];
        $bucket = $config['video_bucket'];
        $videoOssDomain = config('sitesystem.video_oss_domain');

        $style = "video/snapshot,t_7000,f_jpg,w_800,h_600";
        $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
        $timeout = 10*60*60;

        try{
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $signUrl = $ossClient->signUrl($bucket,$object,$timeout);
//            $imageUrl = $ossClient->signUrl($bucket,$object,$timeout,'GET',['x-oss-process'=>'video/snapshot,t_3000,f_jpg,w_0,h_0,m_fast']);
        } catch(OssException $e) {
            return ['code'=>404,'data'=>[],'msg'=>$e->getMessage()] ;
        }
        $pre = '/(http):\/\/([^\/]+)\//i';
        $url = preg_replace($pre,$videoOssDomain,$signUrl);

        return ['code'=>0,'data'=>['url'=>$url],'msg'=>''];
    }

    public function putObject(string $object,string $filePath){
        $config = $this->config();
        $accessKeyId = $config['id'];
        $accessKeySecret = $config['secret'];
        $endpoint = $config['endpoint'];
        $bucket= $config['bucket'];
        try{
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);

            $res = $ossClient->uploadFile($bucket, $object, $filePath);
        } catch(OssException $e) {
            return $e->getMessage();
        }

        return $res;
    }

    /**
     * 删除OSS文件
     */
    public function deleteObject(string $object,$type = 'image')
    {
        $config = $this->config();

        $accessKeyId = $config['id'];
        $accessKeySecret = $config['secret'];
        $endpoint = $config['endpoint'];
        $bucket= $config['bucket'];

        if($type=='video'){
            $bucket = $config['video_bucket'];
        }
        try{
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
            $ossClient->deleteObject($bucket, $object);
        } catch(OssException $e) {
            return false;
        }

        return true;
    }
}