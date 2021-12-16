<?php
namespace app\http\controllers\manage\api;

use app\http\controllers\ApiController;

use app\services\manage\CarouselService;
use app\services\manage\CourseService;
use think\Request;

class Carousel extends ApiController{
    protected $carousel;
    protected $course;
    public  function __construct(CarouselService $carousel,CourseService $course)
    {
        $this->carousel = $carousel;
        $this->course = $course;
    }

    public function getList(Request $request){
        $data = $this->carousel->getList();
        $oss_domain = config('sitesystem.oss_domain');
        if($data){
            foreach ($data as $k=>$v){
                $data[$k]['thumbImg'] = $oss_domain.$v['thumb'];
            }
        }

        $list = array();
        $list['code'] = 0;
        $list['msg'] = '';
        $list['count'] = count($data);
        $list['data'] = $data;

        return json($list);
    }

    public function save(Request $request){
        $data = $request->only(['title','thumb','sort']);
        $app_url = $request->post('app_url');
        if(!$data['title']){
            return $this->message('名称不能为空',500);
        }
        if(!$data['thumb']){
            return $this->message('请上传轮播图片',500);
        }
        if($app_url){
            $data['app_url'] = $app_url;
        }

        $result = $this->carousel->save($data);

        if(empty($result)) {
            return $this->message('操作失败', 500);
        }

        return $this->message('操作成功');
    }

    public function update(Request $request){
        $id = strval($request->post('id'));
        if (empty($id)) {
            return $this->message('参数错误', 500);
        }
        $data = $request->only(['title','thumb','sort','goods_type','goods_id','course_id']);
        $app_url = $request->post('app_url');
        if(!$data['title']){
            return $this->message('名称不能为空',500);
        }
        if(!$data['thumb']){
            return $this->message('请上传轮播图片',500);
        }
        if($app_url){
            $data['app_url'] = $app_url;
        }

        if($data['goods_type']==1){
            $data['type_id'] = $data['goods_id']??0;
        }
        if($data['goods_type']==2){

            $data['type_id'] = $data['course_id']??0;
        }

        $params = [
          'type_id' =>$data['type_id'],
          'goods_type' =>$data['goods_type'],
          'title' =>$data['title'],
          'thumb' =>$data['thumb'],
          'sort' =>$data['sort']?$data['sort']:0,
        ];
        $result = $this->carousel->update($id,$params);

        if(empty($result)) {
            return $this->message('操作失败', 500);
        }

        return $this->message('操作成功');
    }

    /**
     * 删除一条记录
     *
     * @param Request $request
     * @return json
     */
    public function delete(Request $request)
    {
        $id = intval($request->post('id'));

        if (empty($id)) {
            return $this->message('参数错误', 500);
        }

        $result = $this->carousel->delete($id);

        if(empty($result)) {
            return $this->message('操作失败', 500);
        }

        return $this->message('操作成功');
    }
}