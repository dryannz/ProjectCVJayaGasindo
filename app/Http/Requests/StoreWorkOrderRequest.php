<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreWorkOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin_sales';
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'items' => is_array($this->input('items')) ? array_values($this->input('items')) : [],
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tanggal_terbit' => ['required', 'date'],
            'lokasi_pekerjaan' => ['required', 'string', 'max:255'],
            'uraian_pekerjaan' => ['required', 'string', 'max:5000'],
            'keluhan_tangki' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.detail_po_id' => ['required', 'integer', 'distinct', 'exists:detail_po,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'tanggal_terbit.required' => 'Tanggal terbit wajib diisi.',
            'lokasi_pekerjaan.required' => 'Lokasi pekerjaan wajib diisi.',
            'lokasi_pekerjaan.max' => 'Lokasi pekerjaan maksimal 255 karakter.',
            'uraian_pekerjaan.required' => 'Uraian pekerjaan wajib diisi.',
            'uraian_pekerjaan.max' => 'Uraian pekerjaan maksimal 5.000 karakter.',
            'keluhan_tangki.max' => 'Keluhan tangki maksimal 5.000 karakter.',
            'items.required' => 'Pilih minimal satu item jasa.',
            'items.min' => 'Pilih minimal satu item jasa.',
            'items.*.detail_po_id.required' => 'Item jasa wajib dipilih.',
            'items.*.detail_po_id.exists' => 'Item PO tidak ditemukan.',
            'items.*.detail_po_id.distinct' => 'Item jasa tidak boleh dipilih dua kali.',
            'items.*.qty.required' => 'Qty Work Order wajib diisi.',
            'items.*.qty.min' => 'Qty Work Order minimal 1.',
        ];
    }
}
