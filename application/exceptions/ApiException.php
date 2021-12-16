<?php
namespace app\exceptions;

use app\exceptions\BaseException;
use Throwable;

class ApiException extends BaseException
{
    public function __construct(string $message = "", int $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}