<?php
namespace app\models;

use think\Model;
class CartInfos extends Model{
    protected $pk = 'id';

    protected $insert = ['create_at','update_at'];

    protected $update = ['update_at'];
}