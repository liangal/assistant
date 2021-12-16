<?php
namespace app\services;


use think\Db;

class BaseService{
    //默认分页总数
    protected $pageSize = 20;

    /**
     * Desc 单表分页公共方法
     * @param string $modelName 模型名称
     * @param array $params 条件参数 格式-['id'=>'=,1','id'=>'like,2',....]
     * @param string $orderBy 排序
     * @param string $sort  排序规则
     * @return array
     */
    public function paging($modelName, $params, $getField = '*',$orderBy = 'id', $sort = 'desc',$db = 'mysql') {

        //$data = Request()->query->get('page');
        $pageSize = Request()->input('pageSize');
        $pageSize = isset($pageSize) && $pageSize ? $pageSize : $this->pageSize;
        $page = Request()->input('page');
        $start = $page <= 1 ? 0 : $pageSize * ($page - 1);
        $modelObject = DB::table($modelName);
        if ($params) {
            foreach ($params as $key => $value) {
                $arr = explode(',', $value);

                if ($arr[0] == 'or') {

                    $modelObject->where(function ($query) use ($key,$arr) {
                        $query->where($key, '=', $arr[1])
                            ->orWhere($key, '=', $arr[2]);
                    });
                    continue;
                }

                if($arr[0] == 'in'){
                    unset($arr[0]);
                    $modelObject->wherein($key, $arr);
                    continue;
                }

                if(count($arr) == 3){
                    $modelObject->where($key, '>=', $arr[1]);
                    $modelObject->where($key, '<=', $arr[2]);
                }
                else{
                    $modelObject->where($key, $arr[0], $arr[1]);
                }
            }


        }
        $modelObject->where('deleted_at', '=', null);
        $data = [];
        $data['pageInfo']['total'] = $modelObject->count();
        $data['list'] = $modelObject->select($getField)->orderBy($orderBy, $sort)->paginate($this->pageSize);

        return $data;
    }
}