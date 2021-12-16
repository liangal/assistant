<?php
namespace app\models;

use think\Model;

class OauthAccessTokens extends Model
{
    protected $table = 'pt_oauth_access_tokens';
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