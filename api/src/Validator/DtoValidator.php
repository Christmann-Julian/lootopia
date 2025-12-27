<?php

namespace App\Validator;

use App\Exception\ApiException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DtoValidator
{
    public function __construct(private ValidatorInterface $validator)
    {
    }

    /**
     * Validate DTO and throw ApiException if validation fails.
     *
     * @throws ApiException
     */
    public function validate(object $dto): void
    {
        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            $details = [];
            foreach ($errors as $err) {
                $details[$err->getPropertyPath()][] = $err->getMessage();
            }

            throw new ApiException(Response::HTTP_BAD_REQUEST, 'Validation failed', $details);
        }
    }
}
