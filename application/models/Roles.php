<?php
namespace app\models;

use Jackchow\Rbac\RbacRole;

class Roles extends RbacRole
{
    protected $table = 'pt_roles';

	protected $pk = 'id';

	protected $insert = ['created_at','updated_at'];

    protected $update = ['updated_at'];

    protected $type = [
        'id'               =>  'integer',
        'name'             =>  'string',
        'description'      =>  'string',
        'created_at'       =>  'datetime',
        'updated_at'       =>  'datetime',
    ];

    protected function setCreatedAtAttr()
    {
        return get_time();
    }

    protected function setUpdatedAtAttr()
    {
        return get_time();
    }
}