<?php
namespace app\repository;

use app\repository\Repository;

class OauthAccessTokenRepository extends Repository
{
    public function model() {
    	return 'app\models\OauthAccessTokens';
    }
}