<?php
namespace app\http\controllers\manage;

use think\Controller;
use think\Request;
use app\services\manage\VideoServices;

class Player extends Controller
{
    protected $videoServices;

    public function __construct(VideoServices $videoServices){
        $this->videoServices = $videoServices;
    }

    public function show($id)
    {
        $id = intval($id);
        $video = $this->videoServices->find($id)->toArray();
        return view('/manage/player', ['video'=>$video]);
    }
}