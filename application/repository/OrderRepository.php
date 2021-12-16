<?php
namespace app\repository;

use App\Models\ShimmerLiveshopStoreOrder;
use app\repository\Repository;

class OrderRepository extends Repository{
    public function model() {
        return 'app\models\Order';
    }

    public function modelRepostory(){
        return $this->model;
    }

    /**
     * 数据分页
     *
     * @param  string $search    搜索关键词
     * @param  int    $start
     * @param  int    $length
     *
     * @return array
     */
    public function listForPage(string $search,$status,int $type, $start_at, $end_at, int $start, int $length)
    {
      $list = $this->model->where(function ($query) use ($search,$start_at,$end_at) {
            $query->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('order_id', 'like', '%' . $search . '%');
                }
            })->where(function ($query) use ($start_at) {
                if (!empty($start_at)) {
                    $query->where('created_at','>', strtotime($start_at));
                }
            })->where(function ($query) use ($end_at) {
                if (!empty($end_at)) {
                    $query->where('created_at','<', strtotime($end_at));
                }
            });
        })->where('goods_type',$type);
        if($status!=''){
            $list = $this->statusByWhere($status,$list);
        }
        $liste = $list->order('id', 'desc')->limit($start, $length)->select();
      return $liste;
    }

    /**
     * 数据分页总数
     *
     * @param  string $search    搜索关键词
     * @param  int    $status    状态
     * @param  int    $tag       标识
     *
     * @return number
     */
    public function listForTotal(string $search,$status,int $type, $start_at, $end_at)
    {
       $count = $this->model->where(function ($query) use ($search,$type,$start_at,$end_at) {
            $query->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->where('order_id', 'like', '%' . $search . '%');
                }
            })->where(function ($query) use ($start_at) {
                if (!empty($start_at)) {
                    $query->where('created_at','>', strtotime($start_at));
                }
            })->where(function ($query) use ($end_at) {
                if (!empty($end_at)) {
                    $query->where('created_at','<', strtotime($end_at));
                }
            })->where(function ($query) use ($type) {
                    if (!empty($type)) {
                        $query->where('goods_type',$type);
                    }
            });
        });

          if($status!=''){
              $count = $this->statusByWhere($status,$count);
          }
          $counte= $count->count();
          return $counte;
    }

    /**
     * 检索列表
     * @param array $columns 检索字段
     * @return mixed
     */
    public function all($columns = ['*']) {
        return $this->model->order('sort', 'asc')->all();
    }

    /**
     * 查询一条记录
     * @param array $where
     * @return mixed
     */
    public function one($where){
        $bul = $this->model;
        if($where){
            foreach ($where as $k=>$v){
                if(is_array($v)){
                    if($v[0] == 'like')$bul=$bul->where($k,'like','%' . $v[1] . '%');
                    if($v[0] == 'in')$bul=$bul->where($k,'in',$v[1]);

                }else{
                    $bul= $bul->where($k,$v);
                }
            }
        }

        return $bul->find();
    }

    /**
     * 保存一条数据
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function create(array $attributes) {
        $attributes['created_at'] = time();
        $attributes['updated_at'] = time();
        return $this->model->create($attributes);
    }

    /**
     * 按ID更新数据
     *
     * @param array $attributes
     * @param       $id
     * @param       $field
     *
     * @return mixed
     */
    public function update(array $attributes, $id, $field = 'id') {
        $attributes['updated_at'] = time();
        return $this->model->where($field, $id)->update($attributes);
    }

    /**
     * 订单状态获取where条件
     * @param $status
     * @param null $model
     * @return $this|ShimmerLiveshopStoreOrder|null
     */
    public function statusByWhere($status, $model = null)
    {
        if ($model == null) $model = $this->model;
        if ('' === $status || 'all' === $status) {
            return $model;
        } else if ($status == 0) {
            //未支付
            return $model->where('paid', 0)->where('is_del', 0)->where('status', 0)->where('refund_status', 0);
        } else if ($status == 1) {
            //待发货
            return $model->where('paid', 1)->where('is_del', 0)->where('status', 0)->whereIn('refund_status', [0, 3]);
        } else if ($status == 2) {
            //待收货
            return $model->where('paid', 1)->where('is_del', 0)->where('status', 1)->whereIn('refund_status', [0, 3]);
        } else if ($status == 3) {
            //待评价
            return $model->where('paid', 1)->where('is_del', 0)->where('status', 2)->where('refund_status', 0);
        } else if ($status == 4) {
            //已完成
            return $model->where('paid', 1)->where('is_del', 0)->where('status', 3)->whereIn('refund_status', [0, 3]);
        }   else if ($status == 6) {
            //退款中
            return $model->where('paid', 1)->where('is_del', 0)->where('refund_status', 1);
        } else if ($status == 7) {
            //已退款
            return $model->where('paid', 1)->where('is_del', 0)->where('refund_status', 2);
        } else if ($status == 5) {
            //退款
            return $model->where('paid', 1)->where('is_del', 0)->whereIn('refund_status', [1, 2]);
        }elseif ($status == -1) {
            //已关闭
            return $model->where('is_del', 1);
        }elseif ($status ==8) {
            //已付款
            return $model->where('paid', 1)->where('is_del', 0)->whereIn('refund_status', [0, 3]);
        } else {
            return $model;
        }
    }

    /**
     * 取消订单状态
     *
     * @param $status
     * @param null $model
     * @return |null
     */
    public function cancelByWhere($status, $model = null){
        if ($model == null) $model = $this->model;
        if ('' === $status || 'all' === $status) {
            return $model;
        } else if ($status == 0) {
            //已支付或退款中
            return $model->where('paid', 1)->where('is_del', 0)->whereIn('status', [0,1])->whereIn('refund_status', [0,1]);
        }else {
            return $model;
        }
    }

    /**
     * 统计订单数
     *
     */
    public function orderTotal($status){
        return $this->statusByWhere($status)->count();
    }
    /**
     * 某日成交订单数（已付款）
     *
     */
    public function dealOrderTotal($status,$day){
        $Dar = $this->statusByWhere($status)->whereTime('created_at', $day)->count();
        return $Dar;
    }
    /**
     * 某日交易额(×)
     *
     */
    public function oderMoney($status,$day){
        $price = $this->statusByWhere($status)->whereTime('created_at', $day)->sum('total_price');
        return $price;

    }
}