<?php
namespace app\models;

use Jackchow\Rbac\RbacPermission;

class Permissions extends RbacPermission
{
    protected $table = 'pt_admins';
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