<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKategoriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin_sales';
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'urutan' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama kategori wajib diisi.',
            'nama.string' => 'Nama kategori harus berupa teks.',
            'nama.max' => 'Nama kategori maksimal 255 karakter.',
            'urutan.integer' => 'Urutan harus berupa angka bulat.',
            'urutan.min' => 'Urutan tidak boleh kurang dari 0.',
        ];
    }
}
