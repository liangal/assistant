<?php
namespace app\models;

use think\Model;

class Address extends Model
{

    protected $pk = 'id';

    public $insert = [ 'realname', 'mobile', 'province', 'city','district','address_info','postal_code','is_default'];

    protected $update = ['updated_at'];

}