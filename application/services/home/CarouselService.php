<?php
namespace app\services\home;

use app\repository\CarouselRepository;
use think\Request;

class CarouselService
{
	protected $carouselRepository;

    public function __construct(CarouselRepository $carouselRepository){
        $this->carouselRepository = $carouselRepository;
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
        return $this->carouselRepository->find($id, $columns);
    }

    public function getList($columns = ['*']){
        $data = $this->carouselRepository->all($columns);
        $oss_domain = config('sitesystem.oss_domain');
        if($data){
            foreach ($data as $k=>$v){
                $data[$k]['thumb'] = $oss_domain.$v['thumb'];
            }
        }
        return $data;
    }

    public function getFindByDate(){
        return $this->carouselRepository->getFindByDate();
    }

    /**
     * 数据分页
     *
     * @param  string $search    搜索关键词
     * @param  int    $class_id  分类编号
     * @param  int    $status    状态
     * @param  array  $sorts     排序
     * @param  int    $start
     * @param  int    $length
     *
     * @return array
     */
    public function listForPage(string $search)
    {
        return $this->carouselRepository->listForPage($search);
    }
}