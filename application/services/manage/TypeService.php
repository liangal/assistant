<?php
namespace app\services\manage;

use app\models\Type;
use app\repository\TeacherRepository;
use app\services\BaseService;
use think\Loader;
use think\Request;

class TypeService extends BaseService
{
    public function index(){
        $page = $this->paging('Type');
        $oss_domain = config('sitesystem.oss_domain');
        if($page){
            foreach ($page['data'] as $k=>&$v){
                $v['thumb'] = $v['img'];
                $v['img'] = $oss_domain.$v['img'];
            }
        }
        return self::message(0,$page['data'],'成功',$page['total']);

    }

    public static function create(){
        $request = Request();
        $data = $request->only(['name','thumb','status']);
        $data['img'] = $data['thumb'];
        $validate =Type::validate($data);
        if($validate){
          return self::message(201,[],$validate);
        }

        $result = Type::create($data);
        if(!$result){
            return self::message(201,[],'添加类型失败');
        }
        return self::message();
    }

    public static function update(){
        $request = Request();
        $data = $request->only(['id','name','thumb','status']);
        if(!$data['id']){
            return self::message(201,[],'非法操作！！');
        }

        $data['img'] = $data['thumb'];
        $validate =Type::validate($data);
        if($validate){
            return self::message(201,[],$validate);
        }

        $result = Type::update($data,['id'=>$data['id']]);
        if(!$result){
            return self::message(201,[],'添加类型失败');
        }
        return self::message();
    }

    public static function del(){
        $request = Request();
        $id = $request->param('id');
        if(!$id){
            return self::message(201,[],'非法操作！！');
        }

        $result = Type::destroy(['id'=>$id]);
        if(!$result){
            return self::message(201,[],'添加类型失败');
        }
        return self::message();
    }
}