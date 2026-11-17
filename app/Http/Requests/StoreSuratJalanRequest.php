<?php

namespace App\Http\Requests;

use App\Models\SuratJalan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSuratJalanRequest extends FormRequest
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
        $items = collect((array) $this->input('items', []))
            ->filter(fn (mixed $item): bool => is_array($item) && (
                array_key_exists('selected', $item)
                    ? filter_var($item['selected'], FILTER_VALIDATE_BOOLEAN)
                    : isset($item['detail_po_id'])
            ))
            ->map(fn (array $item): array => [
                'detail_po_id' => $item['detail_po_id'] ?? null,
                'qty' => $item['qty'] ?? null,
            ])
            ->values()
            ->all();

        $this->merge([
            'items' => $items,
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
            'tgl' => ['required', 'date'],
            'attn' => ['nullable', 'string', 'max:255'],
            'rev' => ['required', 'string', 'max:20'],
            'mobil' => ['nullable', 'string', 'max:100'],
            'jenis' => ['required', 'string', 'in:'.implode(',', array_keys(SuratJalan::JENIS))],
            'catatan' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.detail_po_id' => ['required', 'integer', 'distinct', 'exists:detail_po,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'tgl.required' => 'Tanggal Surat Jalan wajib diisi.',
            'tgl.date' => 'Tanggal Surat Jalan tidak valid.',
            'rev.required' => 'Revisi dokumen wajib diisi.',
            'rev.max' => 'Revisi dokumen maksimal 20 karakter.',
            'mobil.max' => 'Nomor kendaraan maksimal 100 karakter.',
            'jenis.required' => 'Jenis Surat Jalan wajib dipilih.',
            'jenis.in' => 'Jenis Surat Jalan tidak valid.',
            'catatan.max' => 'Catatan maksimal 5.000 karakter.',
            'items.required' => 'Pilih minimal satu item PO.',
            'items.min' => 'Pilih minimal satu item PO.',
            'items.*.detail_po_id.required' => 'Item PO wajib dipilih.',
            'items.*.detail_po_id.exists' => 'Item PO tidak ditemukan.',
            'items.*.detail_po_id.distinct' => 'Item PO tidak boleh dipilih dua kali.',
            'items.*.qty.required' => 'Qty item wajib diisi.',
            'items.*.qty.min' => 'Qty item minimal 1.',
        ];
    }
}
