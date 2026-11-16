<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin_sales';
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'items' => is_array($this->input('items')) ? array_values($this->input('items')) : [],
            'diskon' => $this->input('diskon', 0),
            'ppn_persen' => $this->input('ppn_persen', 0),
        ]);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'idcust' => ['required', 'string', 'max:20', 'exists:customer,idcust'],
            'tgl_order' => ['required', 'date'],
            'schedule_delivery' => ['required', 'date', 'after_or_equal:tgl_order'],
            'term_of_payment' => ['required', 'string', 'max:20'],
            'project_name' => ['nullable', 'string', 'max:255'],
            'project_location' => ['nullable', 'string', 'max:255'],
            'no_po_customer' => ['nullable', 'string', 'max:50'],
            'diskon' => ['nullable', 'numeric', 'min:0'],
            'ppn_persen' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'catatan' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.idbarangjasa' => ['nullable', 'string', 'max:20', 'exists:barang_jasa,idbarangjasa'],
            'items.*.nama_barang' => ['required', 'string', 'max:255'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.harga_satuan' => ['required', 'numeric', 'min:0'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'idcust.required' => 'Customer wajib dipilih.',
            'idcust.exists' => 'Customer tidak ditemukan.',
            'tgl_order.required' => 'Tanggal order wajib diisi.',
            'schedule_delivery.required' => 'Jadwal pengiriman wajib diisi.',
            'schedule_delivery.after_or_equal' => 'Jadwal pengiriman tidak boleh sebelum tanggal order.',
            'term_of_payment.required' => 'Termin pembayaran wajib diisi.',
            'items.required' => 'Minimal satu item wajib ada.',
            'items.min' => 'Minimal satu item wajib ada.',
            'items.*.qty.min' => 'Qty item minimal 1.',
            'items.*.harga_satuan.min' => 'Harga satuan tidak boleh kurang dari 0.',
        ];
    }
}
