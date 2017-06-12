<?php
namespace Hossein\Gateway;

class AllException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = strtoupper($message);
        $code = -100;

        parent::__construct($message, $code, $previous);
    }
}