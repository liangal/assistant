<?php
namespace app\models;

use think\Model;
class Goods extends Model{
    protected $pk = 'id';

    protected $insert = ['created_at','updated_at'];

    protected $update = ['updated_at'];
}