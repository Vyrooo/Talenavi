<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isPatch = $this->isMethod('patch');

        return [
            'category_id' => [$isPatch ? 'sometimes' : 'required', 'integer', 'exists:categories,id'],
            'title'       => [$isPatch ? 'sometimes' : 'required', 'string', 'max:255'],
            'description' => [$isPatch ? 'sometimes' : 'required', 'string'],
            'price'       => [$isPatch ? 'sometimes' : 'required', 'numeric', 'min:0'],
            'stock'       => [$isPatch ? 'sometimes' : 'required', 'integer', 'min:0'],
            'image_url'   => ['nullable', 'url', 'max:500'],
            'is_active'   => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategori produk wajib dipilih.',
            'category_id.exists'   => 'Kategori yang dipilih tidak valid.',
            'title.required'       => 'Judul produk wajib diisi.',
            'description.required' => 'Deskripsi produk wajib diisi.',
            'price.required'       => 'Harga produk wajib diisi.',
            'price.numeric'        => 'Harga produk harus berupa angka.',
            'price.min'            => 'Harga produk tidak boleh negatif.',
            'stock.required'       => 'Stok produk wajib diisi.',
            'stock.integer'        => 'Stok produk harus berupa bilangan bulat.',
            'stock.min'            => 'Stok produk tidak boleh negatif.',
            'image_url.url'        => 'URL gambar produk tidak valid.',
        ];
    }
}
