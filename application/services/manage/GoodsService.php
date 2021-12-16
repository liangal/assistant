<?php
namespace app\services\manage;

use app\repository\GoodsHouseAuditRepository;
use app\repository\GoodsHouseRepository;
use app\repository\GoodsRepository;
use app\services\WechatServices;
use think\Db;
use think\Exception;

class GoodsService{
    protected $goods;
    protected $wechat;
    public function __construct(WechatServices $wechat,GoodsRepository $goods)
    {
        $this->wechat = $wechat;
        $this->goods = $goods;
        $this->getApp = $wechat->miniProgram();
    }

    /**
     * 获取商品列表
     * @param array $columns
     * @return mixed
     */
    public function getList($columns = ['*']){
        $data = $this->goods->all($columns);
        return $data;
    }

    public function listForTotal(string $search, int $category_id,int $status, $start_at, $end_at)
    {
        return $this->goods->listForTotal($search, $category_id, $status,$start_at,$end_at);
    }

    public function listForPage(string $search,int $category_id,int $status,$start_at,$end_at, int $start, int $length)
    {
        return $this->goods->listForPage($search, $category_id, $status,$start_at,$end_at, $start, $length)->toArray();
    }

    /**查询单条
     * @param array $where 查询条件
     * @return mixed
     */
    public function find(array $where,$columns = ['*']){
        $result = $this->goods->one($where,$columns);
        return $result;
    }

    /**
     * 创建商品
     * @param array $data
     * @return mixed
     */
    public function save(array $data){
        $table = Db::table('pt_goods');
        Db::startTrans();
        try{
            $coverImgUrl = $data['coverImgUrl'];
            unset($data['coverImgUrl']);

            $houseData = [
                'name'=>$data['title'],
                'coverImg'=>$data['thumb'],
                'coverImgUrl'=>$coverImgUrl,
                'priceType'=>1,
                'price'=>$data['marketprice'],
                'price2'=>'',
            ];
            $data['created_at'] = time();
            $data['updated_at'] = time();
            $data['stateon_at'] = time();
            $result = $table->insert($data);
            if(!$result){
                Db::rollback();
                throw new Exception('添加商品失败');
            }
            $goods_id = $table->getLastInsID();

            $app_url = "pages/details/index?type=1&id=".$goods_id;
            $houseData['url']=$app_url;
            $result = $this->getApp->broadcast->create($houseData);
            if(isset($result['errcode']) && $result['errcode']>0) {
                $result['errmsg'] = $this->wechat->createLive[$result['errcode']];
                throw new Exception($result['errmsg']);
            }
            $houseData['goodsId']=$result['goodsId'];
            $houseData['auditId']=$result['auditId'];
            $houseData['detail_id']=$goods_id;
            (new GoodsHouseAuditRepository())->create($houseData);

            Db::commit();
            return ['msg'=>'操作成功','code'=>200];
        }catch (\Exception $e){
            return ['msg'=>$e->getMessage(),'code'=>201];
        }
    }

    /**
     * 更新商品
     * @param array $data
     * @return mixed
     */
    public function update(string $id,array $data){
        $result = $this->goods->update($data,$id);
        return $result;
    }


    /**
     * 删除一条数据
     *
     * @param integer $id   编号
     *
     * @return array
     */
    public function delete(int $id) {
        $table = Db::table('pt_goods');
        $auditTable = Db::table('pt_goods_house_audit');
        Db::startTrans();
        $article = $table->find(['id'=>$id]);
        if(!empty($article)) {
            $audit = $auditTable->get(['detail_id'=>$id]);
            if($audit){
                //获取商品库商品状态
                $goodsHouseStatus = $this->getApp->broadcast->getGoodsWarehouse([$audit['goodsId']]);
                if(isset($goodsHouseStatus['errcode']) && $goodsHouseStatus['errcode']>0) {
                    Db::rollback();
                    $goodsHouseStatus['errmsg'] = $this->wechat->createLive[$goodsHouseStatus['errcode']];
                    return ['msg'=> $goodsHouseStatus['errmsg'],'code'=>$goodsHouseStatus['errcode']];
                }

                foreach ($goodsHouseStatus['goods'] as $k=>$v){
                    if($v['audit_status']==1){//撤回商品库商品审核
                        $resetAudit = $this->getApp->broadcast->resetAudit($audit['auditId'],$audit['goodsId']);
                        if(isset($resetAudit['errcode']) && $resetAudit['errcode']>0) {
                            Db::rollback();
                            $resetAudit['errmsg'] = $this->wechat->createLive[$resetAudit['errcode']];
                            return ['msg'=> $resetAudit['errmsg'],'code'=>$resetAudit['errcode']];
                        }
                    }
                }

                //删除商品库商品
                $res = $this->getApp->broadcast->delete($audit['goodsId']);
                if(isset($res['errcode']) && $res['errcode']>0) {
                    Db::rollback();
                    $res['errmsg'] = $this->wechat->createLive[$res['errcode']];
                    return ['msg'=> $res['errmsg'],'code'=>$res['errcode']];
                }
                $auditTable->where(['detail_id'=>$id])->delete();
            }

            $result =  $table->where(['id'=>$id])->delete();
            if($result) {
                Db::commit();
                return ['msg'=>'操作成功','code'=>200];
            }
        }
        Db::rollback();
        return ['msg'=>'操作失败','code'=>201];
    }


}