<?php
namespace app\models;

use think\Model;

class SiteVisiter extends Model
{
    protected $pk = 'id';

    protected $insert = ['created_at'];

    protected function setCreatedAtAttr()
    {
        return get_time();
    }
}