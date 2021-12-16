<?php
namespace app\http\controllers\manage\api;

use app\services\manage\TeacherService;
use think\Request;
use app\http\controllers\ApiController;

class Teacher extends ApiController
{
    protected $teacher;

    public function __construct(TeacherService $teacher){
        $this->teacher = $teacher;
    }

    /**
     * 列表
     */
    public function getList(Request $request)
    {
        $page = $this->page;
        $start = $this->start;
        $limit = $this->limit;

        $data = $this->teacher->listForPage($start,$limit);
        foreach ($data as $k=>$v){
            $data[$k]['description'] = htmlspecialchars_decode($v['description']) ;
        }
        $count = $this->teacher->listForTotal();

        $list = array();
        $list['code'] = 0;
        $list['msg'] = '';
        $list['count'] = $count;
        $list['data'] = $data;

        return json($list);
    }

    public function save(Request $request){
       $name =  $request->post('name');
       $content = $request->post('content');

       if(!$name){
           return $this->message('名称不能为空');
       }
       if(!$content){
           return $this->message('描述不能为空');
       }
       $data['name'] = $name;
       $data['description'] = htmlspecialchars($content);
       $res = $this->teacher->save($data);
        if(empty($res)) {
            return $this->message('操作失败', 500);
        }
        return $this->message('操作成功');
    }

    public function update(Request $request){
        $id = $request->post('id');
        if (empty($id)) {
            return $this->message('系统错误', 500);
        }

        $name =  $request->post('name');
        $content = $request->post('content');
        if(!$name){
            return $this->message('名称不能为空');
        }
        if(!$content){
            return $this->message('描述不能为空');
        }
        $data['name'] = $name;
        $data['description'] = htmlspecialchars($content);
        $res = $this->teacher->update($id,$data);
        if(empty($res)) {
            return $this->message('操作失败', 500);
        }
        return $this->message('操作成功');
    }

    public function delete(Request $request){
        $id = $request->post('id');
        if (empty($id)) {
            return $this->message('系统错误', 500);
        }
        $res = $this->teacher->delete($id);
        if(empty($res)) {
            return $this->message('操作失败', 500);
        }
        return $this->message('操作成功');
    }
}
