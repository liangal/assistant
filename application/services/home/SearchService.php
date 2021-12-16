<?php
namespace app\services\home;

use app\repository\GoodsRepository;

class SearchService
{
    protected $goods;

    public function __construct(GoodsRepository $goods){
        $this->goods = $goods;
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
        return $this->goods->find($id, $columns);
    }

    public function getList($columns = ['*']){
        $data = $this->goods->all($columns);
        return $data;
    }

    public function findByField($field,$value,$columns = ['*']){
        $this->goods->findByField($field,$value,$columns);
    }

    /**
     * 艺术画数据分页
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
    public function goodsListForPage(string $search, int $class_id, string $field, string $sort, int $status, int $start, int $length)
    {
        $goodsInfos = $this->goods->listForPage($search, $class_id, $status, null, null, $start, $length, $field, $sort);
        $total = $this->goods->listForTotal($search, $class_id, $status, null, null);
        $list = array();

        $oss_domain = config('sitesystem.oss_domain');

        foreach($goodsInfos as $goodsInfo) {
            $row = array();

            $row['id']              = strval($goodsInfo->id);                     // 商品标识
            $row['title']           = $goodsInfo->title;                  // 商品名称

            // 商品图片
            $row['local_thumb']     = $goodsInfo->local_thumb ? $oss_domain . $goodsInfo->local_thumb : '';            
            $row['productprice']    = $goodsInfo->productprice;           // 商品价格
            $row['type']            = '1';                                 // 商品类型：1艺术画、2课程视频
            $row['size']            = '600cm * 300cm'; // $goodsInfo->size;                   // 商品尺寸，类型为1时存在

            $list[] = $row;
        }

        $result = [
            'list' => $list,
            'pageInfo' => [
                'total'     => $total, 
                'page'      => 0, 
                'pageCount' => 0,
            ],
        ];

        return $result;
    }


}