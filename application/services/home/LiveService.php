<?php
namespace app\services\home;

use app\repository\LiveNavRepository;
use app\repository\LiveRepository;
use app\services\WechatServices;
use think\Request;

class LiveService{
    protected $nav;
    protected $live;
    protected $wechat;
    public function __construct(WechatServices $wechat,LiveNavRepository $nav,LiveRepository $live)
    {
        $this->nav = $nav;
        $this->live = $live;
        $this->wechat = $wechat;
        $this->app = $wechat->miniProgram();
    }

    public function getNav(){
        $data = $this->nav->one(['endTime'=>['>',time()]]);
        if(!$data){
            $data = $this->nav->one([],'id','desc');
        }
        $res = [];

        if($data){
            $dataArr = $data->toArray();
            $live = $this->live->one(['live_id'=>$dataArr['live_id']]);
            $res['anchorName'] = $live['anchorName'];
            $res['name'] = $live['name'];
            $res['coverImgUrl'] = $live['shareImgUrl'];
            $res['shareImgUrl'] = $live['shareImgUrl'];
            $res['showStart'] = date('Y-m-d H:i:s',$dataArr['startTime']) ;
            $res['showEnd'] = date('Y-m-d H:i:s',$dataArr['endTime']) ;
            $res['liveStart'] = date('Y/m/d H:i',$live['startTime']) ;
            $res['liveEnd'] = date('Y/m/d H:i',$live['endTime']) ;
            $res['live_id'] = $dataArr['live_id'];

            $media_url = '';
            if($live['startTime'] >time()){
                $res['live_status'] = '未开始';
            }else if($live['endTime'] <time()){
                $res['live_status'] = '已结束';
            }else if($live['startTime']<time() && $live['endTime']>time()){
                $res['live_status'] = '直播中';
            }
            $res['black_url'] = $media_url;
        }
        return $res;
    }

    public function getNavList(){
        $liveData = $this->nav->all();
        $res = [];
        if($liveData){
            $liveData=$liveData->toArray();
            foreach ($liveData as $k=>$v){
                $resDetail = [];

                $live = $this->live->findWhere(['live_id'=>$v['live_id']]);
                $resDetail['anchorName'] = $live['anchorName'];
                $resDetail['name'] = $live['name'];
                $resDetail['start'] = date('Y-m-d H:i:s',$v['startTime']) ;
                $resDetail['end'] = date('Y-m-d H:i:s',$v['endTime']) ;
                $resDetail['live_id'] = strval($v['live_id']) ;

                $res[$k] = $resDetail;
            }
        }
        return $res;
    }

    public function oneWhere($where){
        return $this->live->one($where);
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
    public function listForPage( int $start, int $length)
    {

        $data= $this->live->listForPage($start,$length);
        $res = [];
        if($data){
            foreach ($data as $k=>$v){
                $res[$k]['id'] = strval($v['id']) ;
                $res[$k]['live_id'] = strval($v['live_id']) ;
                $res[$k]['goods_ids'] = $v['goods_ids'];
                $res[$k]['name'] = $v['name'];
                $res[$k]['coverImgUrl'] = $v['shareImgUrl'];
                $res[$k]['shareImgUrl'] = $v['shareImgUrl'];
                $res[$k]['liveStart'] = date('Y/m/d H:i',$v['startTime']) ;
                $res[$k]['liveEnd'] = date('Y/m/d H:i',$v['endTime']) ;
                $res[$k]['anchorName'] = $v['anchorName'];
                $res[$k]['anchorWechat'] = $v['anchorWechat'] ;
                $res[$k]['screenType'] = $v['screenType'] ;
                $res[$k]['closeGoods'] = $v['closeGoods'] ;
                $res[$k]['closeLike'] = $v['closeLike'] ;
                $res[$k]['closeComment'] = $v['closeComment'] ;
                $media_url = '';
                $res[$k]['live_status'] = $this->wechat->liveStatus[$v['status']];

                $res[$k]['black_url'] = $media_url;
            }
        }

        return $res;
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
    public function listForTotal(string $search)
    {
        return $this->live->listForTotal($search);
    }
}