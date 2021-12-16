<?php
namespace app\models;

use think\Model;

class AdminLog extends Model
{

    protected $pk = 'id';

    protected $insert = ['user_id', 'ip', 'browser', 'created_at', 'updated_at'];

    protected $update = ['updated_at'];

    protected function setIdAttr()
    {
        return getGenerateID();
    }

    protected function setCreatedAtAttr()
    {
        return get_time();
    }

    protected function setUpdatedAtAttr()
    {
        return get_time();
    }
}