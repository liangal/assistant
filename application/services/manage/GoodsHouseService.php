<?php
namespace app\services\manage;

use app\repository\GoodsAuditRepository;
use app\repository\GoodsHouseRepository;
use app\services\WechatServices;

class GoodsHouseService{
    protected $wechat;
    protected $getApp;
    protected $goodsAudit;
    protected $key = 'goods_list_key';
    protected $status = [0=>'未审核',1=>'审核中',2=>'已入库',3=>'审核未通过'];
    public function __construct(WechatServices $wechat,GoodsAuditRepository $goodsAudit)
    {
        $this->wechat = $wechat;
        $this->goodsAudit = $goodsAudit;
        $this->getApp = $wechat->miniProgram();
    }

    /**
     * 获取列表
     * @param array $columns
     * @return mixed
     */
    public function getList($start=0,$length=30 ,$status = 0){
        $res = cache($this->key.$status)?cache($this->key.$status):[];

        $goods = isset($res['goods'])?$res['goods']:[];

        $result = [];
        $list = array_slice($goods,$start,$length);
        if(!$res || !$list){

            $ids = array();
            $res = array();
            $data = $this->getApp->broadcast->getapproved(['offset'=>$start,'limit'=>$length,'status'=>$status]);
            if((isset($data['errcode']) && $data['errcode']>0) || empty($data['goods']) ) return $data;

            $count = $data['total'];
            foreach ($data['goods'] as $k=>$v){
                $ids[] = $v['goodsId'];
            }

            $data = $this->getApp->broadcast->getGoodsWarehouse($ids);//获取审核状态
            if(isset($data['errcode']) && $data['errcode']>0) return $data;
            foreach ($data['goods'] as $rk => $rv){
                $data['goods'][$rk]['status_str'] = $this->status[$rv['audit_status']];
                array_push($res,$data['goods'][$rk]);
            }

            $res['goods'] = $data['goods'];
            $res['total']= $count;
            $list = $data['goods'];
            cache($this->key.$status,$res,10*60);
        }

        $result['total'] = $res['total'];
        $result['goods'] = $list;
        return $result;
    }

    /**
     * 创建商品库接口
     * @param array $data
     * @return mixed
     */
    public function save(array $data){

        $result = $this->getApp->broadcast->create($data);
        if(isset($result['errcode']) && $result['errcode']>0) {
            $result['errmsg'] = $this->wechat->createLive[$result['errcode']];
            return $result;
        }
        $data2['wechatGoodsId']=$result['goodsId'];
        $data2['auditId']=$result['auditId'];
        $this->goodsAudit->create($data2);
    }
    /**
     * 撤销审核接口
     * @param int $id
     * @param int $status
     * @return mixed
     */
    public function restaudit(int $id,int $status){
        $goods = $this->goodsAudit->one(['goodsId'=>$id]);
        if(!$goods){
            return ['errcode'=>300009,'errmsg'=>'审核id不存在'];
        }

        $result = $this->getApp->broadcast->resetAudit($goods['auditId'],$goods['goodsId']);
        if(isset($result['errcode']) && $result['errcode']>0) {
            $result['errmsg'] = $this->wechat->createLive[$result['errcode']];
            return $result;
        }
        cache($this->key.$status,[]);
        return $result;
    }

    /**
     * 提交审核接口
     * @param int $id
     * @param int $status
     * @return mixed
     */
    public function audit(int $id){
        $result = $this->getApp->broadcast->resubmitAudit($id);
        if(isset($result['errcode']) && $result['errcode']>0) {
            $result['errmsg'] = $this->wechat->createLive[$result['errcode']];
            return $result;
        }
        cache($this->key.'1',[]);
        cache($this->key.'0',[]);
        return $result;
    }

    /**
     * 更新商品接口
     * @param array $data
     * @return mixed
     */
    public function update(string $id,array $data){

        $result = $this->getApp->broadcast->update($data);
        if(isset($result['errcode']) && $result['errcode']>0) {
            $result['errmsg'] = $this->wechat->createLive[$result['errcode']];
            return $result;
        }
        $this->goodsAudit->update($data,$id,'goodsId');
        cache($this->key.$data['status'],[]);
        return $result;
    }


    /**
     * 删除一条数据
     *
     * @param integer $id   编号
     *
     * @return bool
     */
    public function delete(int $id,int $status) {
        $result = $this->getApp->broadcast->delete($id);
        if(isset($result['errcode']) && $result['errcode']>0) {
            $result['errmsg'] = $this->wechat->createLive[$result['errcode']];
            return $result;
        }
        cache($this->key.$status,[]);
        $this->goodsAudit->deleteWhere(['goodsId'=>$id]);

        return $result;
    }
}