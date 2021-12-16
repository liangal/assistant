<?php
namespace app\http\controllers\api\v1;

use app\http\controllers\ApiController;
use app\http\controllers\manage\api\AliOSS;
use app\services\home\CourseService;
use app\services\manage\TeacherService;
use app\services\WechatServices;
use think\Request;

class Course extends ApiController{
    protected $course;
    protected $wechat;
    protected $teacher;
    public function __construct(Request $request,CourseService $course,WechatServices $wechat,TeacherService $teacher)
    {
        $this->course = $course;
        $this->wechat = $wechat;
        $this->teacher = $teacher;
        parent::__construct($request);
    }

    //课程列表
    public function list(Request $request){
        $search = strval($request->param('search'));
        $sale = (int)($request->param('sale'));
        $new= (int)($request->param('new'));
        $category_id = intval($request->param('category_id'));
        $start = $this->start ;

        $courseData = $this->course->listForPage($search,$sale,$new,$category_id,$start,$this->limit);
        $courseCount = $this->course->listForTotal($search,$category_id);

        $res = [];
        if($courseData){
            foreach($courseData as $k=>$v){
                $data = $this->course->courseArr($v);
                $res[] = $data;
            }
        }
        return $this->listMessage($res,$courseCount,$this->page,count($res));
    }

    //课程导航
    public function getNav(){
        $navData = $this->course->getNav();
        return $this->listMessage($navData,count($navData));
    }

    //课程详情
    public function detail(Request $request){
        $course_id = (int)($request->param('course_id'));
        if(empty($course_id)){
           return $this->message('参数错误',500);
        }
        $res = $this->course->find($course_id);
        $result =  $this->course->courseArr($res);
        return $this->message('',200,$result);
    }

    /**
     * 添加历史播放时间
     *
     * @param Request $request
     * @return \think\response\Json
     */
    public function addPlayTime(Request $request){
        $oid = (int)($request->param('oid'));
        $time = (int)($request->param('time'));
        if(empty($oid) || empty($time)){
            return $this->message('参数错误',500);
        }
        $user =$request->user;
        $res = $this->course->addPlayTime($oid,$time,$user);
        if(!$res){
            return $this->message('操作失败',201);
        }
        return $this->message('操作成功',200);
    }

    /**
     * 获取我的课程
     * @return \think\response\Json
     */
    public function mycourse(){
       $page = $this->page;
       $start = $this->start;
       $length = $this->limit;

       $res = $this->course->myc($start,$length);
       $count = $this->course->mycCount();

       return $this->listMessage($res,$count,$page,count($res));
    }

    public function teacherList(){
        $page = $this->page;
        $start = $this->start;
        $length = $this->limit;
        $data = $this->teacher->listForPage($start,$length);
        $res =[];
        foreach ($data as $k=>$v){
            $res[$k]['id'] = $v['id'];
            $res[$k]['content'] = htmlspecialchars_decode($v['description']);
            $res[$k]['create'] = $v['created_at']?date('Y-m-d H:i:s',$v['created_at']):0;
            $res[$k]['update'] = $v['updated_at']?date('Y-m-d H:i:s',$v['updated_at']):0;
        }
        $count = $this->teacher->listForTotal();

        return $this->listMessage($res,$count,$page,count($res));
    }

    public function videoTime(Request $request){
        $id = intval($request->post('course_id'));
        if (empty($id)) {
            return $this->message('参数错误', 500);
        }
    }
}