<?php
namespace app\repository;

use app\repository\Repository;

class OauthRefreshTokenRepository extends Repository
{
    public function model() {
    	return 'app\models\OauthRefreshTokens';
    }
}