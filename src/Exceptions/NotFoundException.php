<?php

namespace Src\Exceptions;

class NotFoundException extends HttpException
{
    public function __construct($message = "Not Found", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, 404, $previous);
    }
}