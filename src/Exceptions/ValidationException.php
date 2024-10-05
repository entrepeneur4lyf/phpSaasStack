<?php

namespace Src\Exceptions;

class ValidationException extends HttpException
{
    protected $errors;

    public function __construct($errors, $message = "Validation Failed", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, 422, $previous);
        $this->errors = $errors;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}