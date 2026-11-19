<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'ID produk wajib diisi.',
            'product_id.exists' => 'Produk tidak ditemukan dalam database.',
            'quantity.required' => 'Quantity wajib diisi.',
            'quantity.integer' => 'Quantity harus berupa bilangan bulat.',
            'quantity.min' => 'Quantity minimal 1.',
        ];
    }
}
