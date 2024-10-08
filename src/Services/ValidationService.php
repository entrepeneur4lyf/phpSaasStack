<?php

namespace Src\Services;

use Src\Utils\Validator;

class ValidationService
{
    private Validator $validator;

    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    public function validate(array $data, array $rules): array
    {
        if (!$this->validator->validate($data, $rules)) {
            return $this->validator->getErrors();
        }
        return [];
    }
}
