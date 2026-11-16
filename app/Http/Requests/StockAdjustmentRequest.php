<?php

namespace App\Http\Requests;

use App\Models\BarangJasa;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin_sales';
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'action' => ['required', 'in:masuk,koreksi'],
            'idbarangjasa' => ['required', 'string', 'max:20', 'exists:barang_jasa,idbarangjasa'],
            'qty' => ['nullable', 'integer', 'min:1'],
            'stok_baru' => ['nullable', 'integer', 'min:0'],
            'keterangan' => ['required', 'string', 'max:500'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $product = BarangJasa::query()->find($this->input('idbarangjasa'));

            if ($product !== null && $product->tipe !== 'fisik') {
                $validator->errors()->add('idbarangjasa', 'Produk jasa tidak memiliki stok.');
            }

            if ($this->input('action') === 'masuk' && ! $this->filled('qty')) {
                $validator->errors()->add('qty', 'Jumlah stok masuk wajib diisi.');
            }

            if ($this->input('action') === 'koreksi' && ! $this->filled('stok_baru')) {
                $validator->errors()->add('stok_baru', 'Stok baru wajib diisi.');
            }
        });
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'action.required' => 'Jenis penyesuaian wajib dipilih.',
            'action.in' => 'Jenis penyesuaian tidak valid.',
            'idbarangjasa.required' => 'Produk fisik wajib dipilih.',
            'idbarangjasa.exists' => 'Produk tidak ditemukan.',
            'keterangan.required' => 'Keterangan wajib diisi.',
        ];
    }
}
