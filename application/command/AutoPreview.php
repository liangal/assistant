<?php

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use Db;

class AutoPreview extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('autopreview');
        // 设置参数
    }

    protected function execute(Input $input, Output $output)
    {
        $preview = config('sitesystem.autopreview');

        if($preview['auto']) {

            $where = [
                ['preview_count', '<', $preview['preview']],
                ['support_count', '<', $preview['support']]
            ];

            $statistics = Db::name("statistics")->where($where)->select();

            foreach ($statistics as $row) {
                $data = array();

                $p = mt_rand(4, 8);
                $s = mt_rand(1, $p / 2);

                if (intval($row['preview_count']) < $preview['preview']) {
                    $data['preview_count'] = $row['preview_count'] + $p;
                }

                if (intval($row['support_count']) < $preview['support']) {
                    $data['support_count'] = $row['support_count'] + $s;
                }

                if(!empty($data)) {
                    $result = Db::name("statistics")->where('object_id', $row['object_id'])->update($data);

                    if($result) {
                        if (isset($data['preview_count'])) {
                            cache('statistics:preview:' . $row['object_id'], $row['preview_count']);
                        }

                        if (isset($data['support_count'])) {
                            cache('statistics:support:' . $row['object_id'], $row['support_count']);
                        }
                    }
                }
            }
        }
    }
}