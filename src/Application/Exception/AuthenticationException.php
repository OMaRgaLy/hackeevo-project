<?php

namespace App\Application\Exception;

class AuthenticationException extends \Exception
{
    public function __construct(string $message = "", int $code = 401)
    {
        parent::__construct($message, $code);
    }
}