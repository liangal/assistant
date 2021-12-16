<?php
namespace app\services;

use app\services\PayStrategy;

class UnionPayStrategy extends PayStrategy
{
	public function AlgorithmInterface(){
		echo "银联支付";
	}
}