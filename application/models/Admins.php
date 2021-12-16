<?php
namespace app\models;

use Jackchow\Rbac\Traits\RbacUser;
use think\Model;

class Admins extends Model
{
    use RbacUser;
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