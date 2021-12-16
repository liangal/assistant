<?php
namespace app\services\home;

use app\repository\ClassificationRepository;

class ApplicationService
{
	protected $classificationRepository;

    public function __construct(ClassificationRepository $classificationRepository){
        $this->classificationRepository = $classificationRepository;
    }

    /**
     * 应用服务列表
     */
    public function list()
    {
        $service = array_values($this->applicationServerCategoryList(2));
        $welfares = array_values($this->applicationServerCategoryList(34));
        
        $data = array('service'=>$service, 'pwelfare'=>$welfares);

        return $data;
    }

    /**
     * 应用服务分类
     */
    protected function applicationServerCategoryList(int $id) {
        $list = array();

        $where = [['parent_id', '=', $id]];
        $sorts = array('sort'=>'asc','id'=>'desc');
        $columns = array('id', 'code', 'name');

        $list = $this->classificationRepository->selectWhereToSort($where, $sorts, $columns);
        
        $categorys = array();

        $image_domain = config('sitesystem.image_domain');

        foreach($list as $key=>$value) {
            $row = array();
            $row['id'] = $value['id'];
            $row['name'] = $value['name'];
            $row['image'] = $image_domain . '/static/ico/' . $value['code'] . '.png';

            $categorys[] = $row;
        }

        return $categorys;
    }
}