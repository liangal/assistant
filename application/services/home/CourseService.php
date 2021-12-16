<?php
namespace app\services\home;

use app\repository\CourseNavRepository;
use app\repository\CourseRepository;
use app\repository\OrderInfoRepository;
use app\repository\OrderRepository;
use library\AliOSS;
use think\Request;

class CourseService
{
    protected $course;
    protected $courseNav;
    protected $order;
    protected $info;

    public function __construct(CourseRepository $course,CourseNavRepository $courseNav,OrderRepository $order,OrderInfoRepository $info){
        $this->course = $course;
        $this->courseNav = $courseNav;
        $this->order = $order;
        $this->info = $info;
    }

    /**
     * 按ID查找数据
     *
     * @param       $id
     * @param array $columns
     *
     * @return mixed
     */
    public function find($id, $columns = ['*']) {
        return $this->course->findWhere(['id'=>$id,'status'=>1],$columns);
    }

    /**
     * 数据分页
     *
     * @param  string $search    搜索关键词
     * @param  string    $sale  按销量排序
     * @param  string    $new    按最新排序
     * @param  array  $sorts     排序
     * @param  int    $start
     * @param  int    $length
     *
     * @return array
     */
    public function listForPage(string $search,int $sale,int $new,int $category_id, int $start, int $length)
    {
        $model = $this->course->modelRepostory();
        $bul = $model->where(function ($query) use ($search,$category_id) {
            $query->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('title', 'like', '%' . $search . '%');
                }
            })->where(function ($query) use ($category_id) {
                if (!empty($category_id)) {
                    $query->where('category_id', $category_id);
                }
            })->where('status',1);
        });
        if($sale==1){
            $bul = $bul ->order('sale_num','DESC');
        }elseif($sale==2){
            $bul =  $bul ->order('sale_num','ASC');
        }elseif($new==1){
            $bul =  $bul ->order('craeted_at','DESC');
        }

        if(empty($sale) && empty($new)){
            $bul =  $bul ->order('id','DESC');
        }
        return $bul->limit($start, $length)->select();
    }

    /**
     * 数据总数
     *
     * @param  string $search    搜索关键词
     * @param  int    $start
     * @param  int    $length
     *
     * @return array
     */
    public function listForTotal(string $search,int $category_id)
    {
        $model = $this->course->modelRepostory();
        return $model->where(function ($query) use ($search,$category_id) {
            $query->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('title', 'like', '%' . $search . '%');
                }
            })->where(function ($query) use ($category_id) {
                if (!empty($category_id)) {
                    $query->where('category_id', $category_id);
                }
            })->where('status',1);;
        })->count();
    }

    /**
     * 我的课程列表
     *
     * @param $start  页码
     * @param $length 每页显示条数
     * @return array
     */
    public function myc($start,$length){
        $request = Request();
        $user =$request->user;
        $model = $this->order->modelRepostory();

        $courseModel = $this->order->statusByWhere(4,$model);
        $courseOrder = $courseModel->field(['id','order_id','uid','goods_type'])->where('goods_type',2)->where('uid',$user->id)->limit($start, $length)->select();
        $res = [];

        if($courseOrder){
            $courseOrder = $courseOrder->toArray();

            foreach ($courseOrder as $k=>$v){
               $info = $this->info->findWhere(['oid'=>$v['id']]);
               $course = $this->course->findWhere(['id'=>$info->type_id]);
               if($course){
                   $course = $course->toArray();
                   $arr = $this->courseArr($course);

                   $alioss = new AliOSS();
                   $videoUrl = $alioss->getVideoUrl($course['video_url']);
                   $arr['video_url'] = $videoUrl['data']['url'];

                   $arr['oid'] = $v['id'];
                   $arr['paly_time'] = $info->play_time;
                   $res[] = $arr;
               }
            }
        }
        return $res;
    }

    /**
     * 我的课程总数
     * @return mixed
     */
    public function mycCount(){
        $request = Request();
        $user =$request->user;
        $model = $this->order->modelRepostory();

        $courseModel = $this->order->statusByWhere(4,$model);
        $count = $courseModel->where('goods_type',2)->where('uid',$user->id)->count();

        return $count;
    }


    public function addPlayTime($oid,$time,$user){
        $model = $this->order->modelRepostory();

        $courseModel = $this->order->statusByWhere(4,$model);
        $courseOrder = $courseModel->field(['id','order_id','uid','goods_type'])->where('id',$oid)->where('goods_type',2)->where('uid',$user->id)->find();
        if(!$courseOrder){
            return false;
        }
        $info = $this->info->updateWhere(['play_time'=>$time],['oid'=>$courseOrder->id]);
        return $info;
    }

    /**
     * 获取课程导航
     *
     * @param string $search
     */
    public function getNav($search=''){
        $navData = $this->courseNav->list($search);
        $courseNavRes = [];
        if($navData){
            foreach ($navData as $k=>$v){
                $course = $this->find($v['course_id']);
                if($course){
                    $course = $course->toArray();
                    $resDetail = [
                        'course_id'=>strval($v['course_id']),
                        'name'=>$course['title'],
                        'thumb'=>config('sitesystem.oss_domain').$course['thumb'],
                        'price'=>$course['price'],
                        'sale_num' =>$course['sale_num'],
                        'expressprice'=>$course['expressprice'],
                        'stateon_at'=>date('Y-m-d H:i:s',$course['stateon_at']),
                    ];
                    $courseNavRes[$k] = $resDetail;
                }
            }
        }

        return $courseNavRes;
    }

    /**
     * 课程搜索
     *
     * @param string $search 检索字段
     * @return mixed
     */
    public function search(string $search){
        $model = $this->course->modelRepostory();
        $bul = $model->field(['id','title'])->where(function ($query) use ($search) {
            $query->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('title', 'like', '%' . $search . '%');
                }
            })->where('status',1);;
        });
        return $bul->select();
    }

    //返回数据整合
    public function courseArr($v){
        $data = [];
        if($v){

//            $alioss = new AliOSS();
//            $videoUrl = $alioss->getVideo($v['video_url']);

            $data['course_id'] = strval($v['id']) ;
            $data['category_id'] = strval($v['category_id']) ;
            $data['title'] = $v['title'];
            $data['thumb'] = config('sitesystem.oss_domain').$v['thumb'];
//            $data['video_url'] = $videoUrl['data']['url'];
            $data['price'] = strval($v['price']) ;
            $data['expressprice'] = strval($v['expressprice']);
            $data['ficti_num'] = strval($v['ficti_num']);
            $data['sale_num'] = strval($v['sale_num']);
            $data['description'] = htmlspecialchars_decode($v['description']);
            $data['status'] = strval($v['status']);
            $data['statusStr'] = $v['status']==1?'上架':'下架';
            $data['stateon_at'] = date('Y-m-d H:i:s',$v['stateon_at']);

            $thumbs =  explode(',',$v['thumbs']);
            foreach ($thumbs as $tk=>$tv){
                $tv = config('sitesystem.oss_domain').$tv;
                $thumbs[$tk] = $tv;
            }
            $data['thumbs'] = $thumbs;
        }

        return $data;
    }
}