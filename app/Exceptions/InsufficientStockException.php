<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use App\Support\ApiResponse;

class InsufficientStockException extends Exception
{
    protected mixed $errors;

    public function __construct(string $message = 'Stok produk tidak mencukupi', mixed $errors = null, int $code = 409)
    {
        parent::__construct($message, $code);
        $this->errors = $errors;
    }

    public function getErrors(): mixed
    {
        return $this->errors;
    }

    public function render(): JsonResponse
    {
        return ApiResponse::conflict($this->getMessage(), $this->errors);
    }
}
