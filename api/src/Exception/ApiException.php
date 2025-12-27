<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiException extends HttpException
{
    public ?array $details;

    public function __construct(
        int $statusCode,
        string $message,
        ?array $details = null,
    ) {
        parent::__construct($statusCode, $message);
        $this->details = $details;
    }
}
