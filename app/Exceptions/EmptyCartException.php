<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use App\Support\ApiResponse;

class EmptyCartException extends Exception
{
    public function __construct(string $message = 'Keranjang belanja kosong. Tidak dapat melakukan checkout.', int $code = 400)
    {
        parent::__construct($message, $code);
    }

    public function render(): JsonResponse
    {
        return ApiResponse::error($this->getMessage(), null, 400);
    }
}
