<?php
namespace app\models;

use think\Model;

class Users extends Model
{
    protected $table = 'pt_users';
	protected $pk = 'id';
//
//	protected $hidden=['password'];
//
//	protected $insert = ['id', 'created_at','updated_at'];
//
//    protected $update = ['updated_at'];
//
//    protected function setIdAttr()
//    {
//        return getGenerateID();
//    }
//
//    protected function setCreatedAtAttr()
//    {
//        return get_time();
//    }
//
//    protected function setUpdatedAtAttr()
//    {
//        return get_time();
//    }
//
//    /**
//     * 消息中心(已阅读)
//     */
//    public function systemInformations()
//    {
//        return $this->belongsToMany('SystemInformation', 'app\models\SystemInformationPreview', 'object_id', 'user_id');
//    }
}