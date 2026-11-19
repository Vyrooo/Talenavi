<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'q' => ['sometimes', 'string', 'max:100'],
            'category' => ['sometimes', 'string', 'max:100'],
            'sort' => ['sometimes', 'string', 'in:price_asc,price_desc,newest'],
        ];
    }

    public function messages(): array
    {
        return [
            'page.integer' => 'Parameter page harus berupa angka integer.',
            'page.min' => 'Parameter page minimal 1.',
            'limit.integer' => 'Parameter limit harus berupa angka integer.',
            'limit.min' => 'Parameter limit minimal 1.',
            'limit.max' => 'Parameter limit maksimal 50.',
            'sort.in' => 'Pilihan sort tidak valid. Gunakan: price_asc, price_desc, atau newest.',
        ];
    }
}
