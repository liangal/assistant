<?php
namespace app\command;

use app\repository\LiveRepository;
use app\services\WechatServices;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Exception;

class LiveSystem extends Command{

    protected function configure()
    {
        // 指令配置
        $this->setName('LiveSystem')->setDescription('更新直播状态 LiveSystem');
        // 设置参数
    }


    protected function execute(Input $input, Output $output)
    {
        $this->updetaLive($output,1);
    }

    public function updetaLive(Output $output,$page,$limit=100){
        $output->writeln("LiveSystem Command:");

        $page =$page <= 1 ? 1 : $page;
        $start = ($page-1)*$limit;

        $app = (new WechatServices())->miniProgram();
        $cache_room_info = cache('room_info_'.$page);
        try{
            $wechatLive = $app->live->getRooms((int)$start,(int)$limit);
            $liveModel = new LiveRepository();
            if(isset($wechatLive['errcode']) && $wechatLive['errcode']==0) {

                if( $wechatLive['room_info']){
                    $room_info = $wechatLive['room_info'];
                    if(empty($cache_room_info) || $cache_room_info != md5(json_encode($room_info))){
                        $liveList = $liveModel->getAll(['status'=>['in'=>[101,102,106]]],'id',$start,$limit);
                        if($liveList){
                            foreach ($liveList as $item=>$value){
                                foreach ($room_info as $k=>$v){

                                    if($value['live_id']==$v['roomid']){
                                        $res = $liveModel->update(['status'=>$v['live_status']],$value['id']);
                                        if($res){
                                            $output->writeln("修改直播成功--id={$value['live_id']}");
                                        }else{
                                            $output->writeln("修改直播失败--id={$value['live_id']}");
                                        }
                                        break ;
                                    }
                                }
                            }
                        }

                        cache('room_info_'.$page,md5(json_encode($room_info)));
                    }
                    if(count($room_info) == $limit){
                        $page = $page + 1;
                        $this->updetaLive($output,$page);
                    }
                }
            }
        }catch (\Exception $e){
            $output->writeln("直播异常{$e->getMessage()}");
        }
    }
}