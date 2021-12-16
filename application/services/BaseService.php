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
    public function paging($modelName, $params=[], $getField = '*',$orderBy = 'id', $sort = 'desc',$db = 'mysql') {

        //$data = Request()->query->get('page');
        $pageSize = Request()->param('pageSize');
        $pageSize = isset($pageSize) && $pageSize ? $pageSize : $this->pageSize;
        $page = Request()->param('page');
        $start = $page <= 1 ? 0 : $pageSize * ($page - 1);
        $modelObject = DB::name($modelName);
        if ($params) {
//            var_dump($params);
            foreach ($params as $key => $value) {
                $arr = explode(',', $value);
//                var_dump($value);
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


        $data= $modelObject->field($getField)->order("".$orderBy." ".$sort."")->paginate($this->pageSize);
        if($data){
            $data = $data->toArray();
        }

        return $data;
    }

    /**
     * Desc 搜索参数处理
     * @param adday $params 搜索的参数
     * @param adday $searchField 搜索字段 格式-['name'=>'like','mobile'=>'=']
     * @return $data
     */
    public function searchParams($searchField, $params) {
        if (empty($searchField) || empty($params)) {
            return [];
        }

        $data = [];
        foreach ($searchField as $key => $value) {
            if (isset($params[$key]) && $params[$key] !== false) {
                if ($value == 'like') {
                    $data[$key] = $value . ',' . '%' . $params[$key] . '%';
                    continue;
                }
                $data[$key] = $value . ',' . $params[$key];
            }
        }
        return $data;
    }

    /**
     * 操作
     */
    public static function message($code = 200,$data=[],$msg='成功',$count=0)
    {
        $data = array(
            'code' => $code,
            'msg' => $msg,
            'count' => $count,
            'data' => $data,
        );

        return json($data);
    }
}