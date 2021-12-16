<?php
namespace app\models;

use think\Model;

class MyChannel extends Model
{

	protected $pk = 'id';

	protected $insert = ['created_at','updated_at'];

    protected $update = ['updated_at'];

    protected function setCreatedAtAttr()
    {
        return get_time();
    }

    protected function setUpdatedAtAttr()
    {
        return get_time();
    }
}