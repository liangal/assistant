<?php
namespace app\exceptions;

use app\exceptions\BaseException;

class ParameterException extends BaseException
{
    public function __construct(string $message = "参数错误")
    {
        parent::__construct($message, 500);
    }
}