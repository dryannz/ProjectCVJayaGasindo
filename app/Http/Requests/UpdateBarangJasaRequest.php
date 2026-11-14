<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateBarangJasaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin_sales';
    }

    public function rules(): array
    {
        return [
            'kategori_id' => ['nullable', 'integer', 'exists:kategori,id'],
            'tipe' => ['required', 'in:fisik,jasa'],
            'nama_barang' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'ukuran' => ['required', 'string', 'max:50'],
            'unit' => ['required', 'string', 'max:30'],
            'mode_harga' => ['required', 'in:fix,rfq,khusus'],
            'price' => ['nullable', 'numeric', 'min:0.01'],
            'stok' => ['nullable', 'integer', 'min:0'],
            'gambar' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],
            'hapus_gambar' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $values = [];

        if ($this->input('tipe') === 'jasa') {
            $values['stok'] = null;
        }

        if ($this->input('mode_harga') === 'rfq') {
            $values['price'] = null;
        }

        if ($values !== []) {
            $this->merge($values);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->input('tipe') === 'fisik' && ($this->input('stok') === null || $this->input('stok') === '')) {
                $validator->errors()->add('stok', 'Stok wajib diisi untuk barang fisik.');
            }

            if ($this->input('mode_harga') === 'fix' && ($this->input('price') === null || $this->input('price') === '')) {
                $validator->errors()->add('price', 'Harga wajib diisi untuk mode harga tetap.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'kategori_id.integer' => 'Kategori tidak valid.',
            'kategori_id.exists' => 'Kategori yang dipilih tidak ditemukan.',
            'tipe.required' => 'Tipe produk wajib dipilih.',
            'tipe.in' => 'Tipe produk harus berupa fisik atau jasa.',
            'nama_barang.required' => 'Nama produk wajib diisi.',
            'nama_barang.string' => 'Nama produk harus berupa teks.',
            'nama_barang.max' => 'Nama produk maksimal 255 karakter.',
            'deskripsi.string' => 'Deskripsi harus berupa teks.',
            'deskripsi.max' => 'Deskripsi maksimal 5.000 karakter.',
            'ukuran.required' => 'Ukuran wajib diisi.',
            'ukuran.max' => 'Ukuran maksimal 50 karakter.',
            'unit.required' => 'Unit wajib diisi.',
            'unit.max' => 'Unit maksimal 30 karakter.',
            'mode_harga.required' => 'Mode harga wajib dipilih.',
            'mode_harga.in' => 'Mode harga tidak valid.',
            'price.numeric' => 'Harga harus berupa angka.',
            'price.min' => 'Harga harus lebih besar dari 0.',
            'stok.integer' => 'Stok harus berupa angka bulat.',
            'stok.min' => 'Stok tidak boleh kurang dari 0.',
            'gambar.file' => 'Gambar produk tidak valid.',
            'gambar.mimes' => 'Gambar harus berformat JPG, JPEG, PNG, atau WEBP.',
            'gambar.max' => 'Ukuran gambar maksimal 2 MB.',
            'is_active.boolean' => 'Status aktif tidak valid.',
            'hapus_gambar.boolean' => 'Pilihan hapus gambar tidak valid.',
        ];
    }
}
