<?php
namespace app\exceptions;

use \Throwable;

class BaseException extends \Exception
{
    protected $statusCode;

    public function __construct(string $message = "", int $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        
        $this->statusCode = $code;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }
}