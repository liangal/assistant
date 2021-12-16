<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/**
 * 生成随机字符串
 * 
 * @param  int|integer $length [description]
 * @param  string      $char   [description]
 * 
 * @return string
 */
function str_rand(int $length = 32, string $char = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
    if(!is_int($length) || $length < 0) {
		return false;
    }
 
	$string = '';
	
	for($i = $length; $i > 0; $i--) {
		$string .= $char[mt_rand(0, strlen($char) - 1)];
	}

	return $string;
}


if (! function_exists('price_bcmul')) {
    /**
     *  货币单位转换，元转分
     *
     * @param  string|int|float  $left_operand      数值（单位元）：输入0.01元钱，输出1分钱
     * @param  string|int|float  $right_operand     右操作数
     * @param  int  $scale  此可选参数用于设置结果中小数点后的小数位数
     * @return string
     */
    function price_bcmul(float $left_operand, int $right_operand = 100, $scale = 0) : string
    {
        return bcmul((string)$left_operand, (string)$right_operand, $scale);
    }
}


if(!function_exists('priceFormat')) {
    /**
     * 分转元
     * @param $price
     * @return string
     */
    function priceFormat($price)
    {

        return number_format($price/100,2);
    }
}


if (!function_exists('auth')) {
    /**
     * 实例化auth'
     *
     * @param string  $name auth名称，如果为数组表示进行auth设置
     *
     * @return mixed
     */
    function auth($name = 'admin')
    {
        return \library\Auth::guard('admin');
    }
}

/**
 * 从集合中获取值
 * 
 * @param  $data 对象或者数组
 * @param  $key  键
 * 
 * @return array|object
 */
function array_get($data, $key) {
    if(isset($data[$key]))
        return $data[$key];
    else
        return null;
}

/**
 * 从集合中是否存在键
 * 
 * @param  $data 对象或者数组
 * @param  $key  键
 * 
 * @return array|object
 */
function array_has($data, $key) {
    if(isset($data[$key]))
        return true;
    else
        return false;
}

/**
 * 返回集合的第一个元素
 *
 * @param  $data 对象或者数组
 *
 * @return array|object
 */
function array_first($data)
{
    return array_get($data, 0);
}

/**
 * 获取时间格式YYYYmmdd
 *
 * @return string
 */
function get_time()
{
    return date('Y-m-d H:i:s');
}

/**
 * 构造分类数据
 *
 * @param $data
 * @return array
 */
function generateTree($data){
    $tree = array();
    if($data){
        $items = array();
        foreach($data as $value){
            $items[$value['id']] = $value;
            $items[$value['id']]['son'] = [];
        }

        foreach($items as $key => $value){
            if(isset($items[$value['parent_id']])){
                $items[$value['parent_id']]['son'][] = &$items[$key];
            }else{
                $tree[] = &$items[$key];
            }
        }
    }
    return $tree;
}

/**
 * 设定键获取所有集合值
 * 
 * @param  array  $a      [description]
 * @param  string $column [description]
 * 
 * @return array
 */
function array_pluck($a = array(), $column = 'id')
{

    $two_level = func_num_args() > 2 ? true : false;
    if ( $two_level ) $scolumn = func_get_arg(2);

    $ret = array(); settype($a, 'array');
    
    if ( false == $two_level )
    {   
        foreach( $a AS $one )
        {   
            if ( is_array($one) ) 
                $ret[ @$one[$column] ] = $one;
            else
                $ret[ @$one->$column ] = $one;
        }   
    }
    else
    {
        foreach( $a AS $one )
        {   
            if (is_array($one)) {
                if ( false==isset( $ret[ @$one[$column] ] ) ) {
                    $ret[ @$one[$column] ] = array();
                }

                $ret[ @$one[$column] ][ @$one[$scolumn] ] = $one;
            } else {
                if ( false==isset( $ret[ @$one->$column ] ) )
                    $ret[ @$one->$column ] = array();

                $ret[ @$one->$column ][ @$one->$scolumn ] = $one;
            }
        }
    }

    return $ret;
}

/**
 * 设置数据键值
 *
 * @param array $data
 * @param string $column_id
 * @param string $column_name
 * 
 * @return array
 */
function array_pluck_column($data, $column_id, $column_name) {
    $list = array();

    if(!empty($data)){
        foreach($data as $v) {
            if(!isset($v[$column_id]) || !isset($v[$column_name])){
                continue;
            }

            $list[$v[$column_id]] = $v[$column_name];
        }
    }

    return $list;
}

/**
 * 指定数组以$key键值排序
 * @param  [type] $array [description]
 * @param  [type] $key   [description]
 * @param  string $type  [description]
 * @return [type]        [description]
 */
function array_sort($array, $key, $type='asc'){
    $keysvalue = $new_array = array();

    foreach ($array as $k=>$v){
        $keysvalue[$k] = $v[$key];
    }
    
    if($type == 'asc'){
        asort($keysvalue);
    } else {
        arsort($keysvalue);
    }

    reset($keysvalue);
    
    foreach ($keysvalue as $k=>$v){
        $new_array[$k] = $array[$k];
    }

    return $new_array;
}

/**
 * 返回当前的毫秒时间戳
 *
 * @return int
 */
function msectime() {
    list($msec, $sec) = explode(' ', microtime());
    $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    return $msectime;
}

/**
 * 创建目录
 */
function createDir($path)
{
    $result = true;

    if (!is_dir($path)){  
        $res = mkdir(iconv("UTF-8", "GBK", $path), 0777, true); 
        if (!$res){
            $result = false;
        }
    }

    return $result;
}

/**
 * 利用经纬度计算两点之间的距离(米)
 */
function getDistance($lat1, $lng1, $lat2, $lng2){ 
    $radLat1 = deg2rad($lat1);
    $radLat2 = deg2rad($lat2);
    $radLng1 = deg2rad($lng1);
    $radLng2 = deg2rad($lng2);

    $a = $radLat1 - $radLat2;
    $b = $radLng1 - $radLng2;

    $s = 2 * asin(sqrt(pow(sin($a / 2), 2)+cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137;
    
    return $s;
}

/**
 * 获取分类选项html
 */
function getChildCagegoryOptionHtml($parent_id = 0)
{
    $services = app('\app\services\manage\ClassificationServices');
    return $services->getChildCagegoryOption($parent_id);
}

/**
 * 获取唯一的ID
 */
function getGenerateID() {
    $data_center_id = config('app.data_center_id');
    $machine_id = config('app.machine_id');

    $snowFlake = new library\SnowFlake($data_center_id, $machine_id);
    return $snowFlake->generateID();
}

/**
 * 多久前的时间
 * @param  [type] $time [description]
 * @return [type]       [description]
 */
function timeText($time){
    $time = time() - $time;
    if(is_numeric($time)){  
        $value = array(  
              "years" => 0, "days" => 0, "hours" => 0,  
              "minutes" => 0, "seconds" => 0,  
        );
        if($time >= 31556926){  
              $value["years"] = floor($time/31556926);  
              $time = ($time%31556926);
              $t = $value["years"].'年前';  
        }  
        elseif(31556926 >$time && $time >= 86400){  
             $value["days"] = floor($time/86400);  
              $time = ($time%86400);
              $t = $value["days"].'天前';  
        }  
        elseif(86400 > $time && $time >= 3600){  
             $value["hours"] = floor($time/3600);  
              $time = ($time % 3600);
              $t = $value["hours"] . '小时前';  
        }  
        elseif(3600 > $time && $time >= 60){  
              $value["minutes"] = floor($time/60);  
              $time = ($time%60);
              $t = $value["minutes"].'分钟前';  
        }else{
            if ($time == 0) {
                $time = 1;
            }
            
            $t = $time . '秒前';
        }

        return $t;    
    }
    else
    {  
        return date('Y-m-d H:i:s', time());
    }  
}

/**
 * 数字转换(万)
 * @param  [type] $number [description]
 * @return [type]         [description]
 */
function digitalConversion($number)
{
    return $number < 10000 ? $number : round($number/10000,2) . '万';
}

/**
 * 获取客户端访问IP
 * @return [type]         [description]
 */
function get_ip()
{
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
        $cip = $_SERVER['HTTP_CLIENT_IP'];
    }
    else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    }
    else if(!empty($_SERVER["REMOTE_ADDR"])){
        $cip = $_SERVER["REMOTE_ADDR"];
    }
    else
    {
        $cip = '';
    }

    preg_match("/[\d\.]{7,15}/", $cip, $cips);
    $cip = isset($cips[0]) ? $cips[0] : 'unknown';
    unset($cips);
    return $cip;
}

/**
 * 截取字符串
 * @return [type]         [description]
 */
function subtext($text, $length){
    if(mb_strlen($text, 'utf8') > $length)
        return mb_substr($text, 0, $length, 'utf8') . '...';

    return $text;
}

/**
 * 获取全部分类列表
 */
function getAllCategorys() {
    $result = cache('categorys');

    if(!empty($result)) {
        $categorys = $result;
    }
    else 
    {
        $services = app('app\services\manage\ClassificationServices');

        $list = $services->all();

        $categorys = array();

        if(!$list->isEmpty()) {
            $list = $list->toArray();

            foreach($list as $key=>$value) {
                $categorys[$value['id']] = $value;
            }

            cache('categorys', $categorys);
        }
    }

    return $categorys;
}

/**
 * 获取token对应的用户信息
 */
function getTokenUser() {
    $request = request();

    if(!empty($request->header('Authorization')) || !empty($request->get('token'))) {
        if(!empty($request->header('Authorization'))){
            $token = str_replace('Bearer ', '', $request->header('Authorization'));
        }
        else
        {
            $token = $request->get('token');
        }

        $authAuthenticateService = app('app\services\home\AuthAuthenticateService');
        $payload = $authAuthenticateService->verifyToken($token);
        
        if(!empty($payload)) {
            $id = array_get($payload, 'id');
            $user = $authAuthenticateService->getTokenUser($id);
            
            if(!empty($user)){
                return $user;
            }
        }
    }

    return false;
}

/**
 * 消息中心分类
 */
function systemInformationCategorys() {
    $categorys = array();
    $image_domain = config('sitesystem.image_domain');
    $categorys[] = array('id'=>1, 'name'=>'系统消息', 'image'=>$image_domain . '/static/ico/system_info_ico.png');
    return $categorys;
}

/**
 * 获取Redis对象 进行额外方法调用
 */
function getRedisObject() {
    return \think\facade\Cache::store('redis')->handler();
}

/**
 * 是否微信打开
 * @param  [type]  $request [description]
 * @return boolean          [description]
 */
function isWeChatBrowser()
{
    return strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false;
}

/**
 * 默认头像
 */
function defaultAvatar() {
    $image_domain = config('sitesystem.image_domain');
    return $image_domain . '/home/image/avatar.png';
}

/**
 * 过滤敏感词
 */
function filterContent($content)
{
    $wordFilter = app('library\SensitiveWordFilter');
    $content = $wordFilter->filter($content, '', 2);
    return $content;
}

/**
 * 获取频道列表
 */
function channels()
{
    return array('1'=>'新闻', '2'=>'服务', '3'=>'知识', '6'=>'视频', '8'=>'预案', '9'=>'通讯', '10'=>'城市', '34'=>'公益', '60'=>'条文条例');
}

/**
 * 隐藏部分手机号码
 */
function hiddenPhone($str){
    $str = $str;
    $resstr = substr_replace($str, '****', 3, 4);
    return $resstr;
}

function gmt_iso8601($time) {
    $dtStr = date("c", $time);
    $mydatetime = new DateTime($dtStr);
    $expiration = $mydatetime->format(DateTime::ISO8601);
    $pos = strpos($expiration, '+');
    $expiration = substr($expiration, 0, $pos);
    return $expiration."Z";
}

/**
 * 获取当前完整的URL
 * @return [type] [description]
 */
function getUri()
{
    $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://": "http://";
    $url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    return $url;
}

/**
 * 系统版本
 */
function getOSType()
{
    $ua = $_SERVER['HTTP_USER_AGENT'];
    if (strpos($ua, 'Android') !== false) {
        preg_match("/(?<=Android )[\d\.]{1,}/", $ua, $version);
        return 2;
    } elseif (strpos($ua, 'iPhone') !== false) {
        preg_match("/(?<=CPU iPhone OS )[\d\_]{1,}/", $ua, $version);
        return 1;
    } elseif (strpos($ua, 'iPad') !== false) {
        preg_match("/(?<=CPU OS )[\d\_]{1,}/", $ua, $version);
        return 3;
    }
    
    return 0;
}

/**
 * 创建多级目录
 *
 * @param $dir
 * @param int $mode
 * @return bool
 */
function mkdirs($dir, $mode = 0777)
{
    if (is_dir($dir) || @mkdir($dir, $mode)) return TRUE;

    if (!mkdirs(dirname($dir), $mode)) return FALSE;

    return @mkdir($dir, $mode);
}

/**
 * 过滤城市名称(简写)
 * @param $str
 */
function filterCityName($str) {
    $str = str_replace('省', '', $str);
    $str = str_replace('市', '', $str);
    $str = str_replace('黎族自治', '', $str);
    $str = str_replace('县', '', $str);

    return $str;
}

/**
 * 获取浏览器
 */
function getBrowser() {
    if (!empty($_SERVER['HTTP_USER_AGENT'])) {
        $br = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match('/MSIE/i', $br)) {
            $br = 'MSIE';
        } elseif (preg_match('/Firefox/i', $br)) {
            $br = 'Firefox';
        } elseif (preg_match('/Chrome/i', $br)) {
            $br = 'Chrome';
        } elseif (preg_match('/Safari/i', $br)) {
            $br = 'Safari';
        } elseif (preg_match('/Opera/i', $br)) {
            $br = 'Opera';
        } else {
            $br = 'Other';
        }
        return $br;
    } else {
        return "-";
    }
}

function curl_get($url){

    $header = array(
        'Accept: application/json',
    );
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_TIMEOUT, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

    $data = curl_exec($curl);
    return $data;
}

function curl_post($url,$postdata) {

    if (empty($url) || empty($postdata)) {
        return false;
    }

    $param = http_build_query($postdata);
    $ch = curl_init();//初始化curl
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//    正式环境时解开注释
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $data = curl_exec($ch);//运行curl
    curl_close($ch);
    return $data;
}