<?php

namespace App\Http\Requests;

use App\Models\Quotation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin_sales';
    }

    protected function prepareForValidation(): void
    {
        // Items dikirim sebagai array of row dari form dinamis.
        $items = $this->input('items', []);
        $this->merge(['items' => is_array($items) ? array_values($items) : []]);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $pakaiCustomerBaru = ($this->input('customer_mode') ?? '') === 'baru';

        $aturanCustomer = $pakaiCustomerBaru
            ? [
                'customer_mode' => ['required', 'in:existing,baru'],
                'customer_baru.nama_pt' => ['required', 'string', 'max:255'],
                'customer_baru.notelp_pt' => ['required', 'string', 'max:20', 'regex:/^[0-9+\\-\\s()]{8,20}$/'],
                'customer_baru.alamat_pt' => ['required', 'string', 'max:255'],
                'customer_baru.pic' => ['nullable', 'string', 'max:50'],
            ]
            : [
                'customer_mode' => ['nullable', 'in:existing,baru'],
                'idcust' => ['required', 'string', 'max:20', 'exists:customer,idcust'],
            ];

        return [
            ...$aturanCustomer,
            'lead_id' => ['nullable', 'integer', 'exists:leads,id'],
            'tanggal' => ['required', 'date'],
            'berlaku_sampai' => ['nullable', 'date', 'after_or_equal:tanggal'],
            'diskon' => ['nullable', 'numeric', 'min:0'],
            'ppn_persen' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'catatan' => ['nullable', 'string', 'max:5000'],
            'syarat' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.idbarangjasa' => ['nullable', 'string', 'max:20'],
            'items.*.nama_barang' => ['required', 'string', 'max:255'],
            'items.*.tipe' => ['nullable', 'in:fisik,jasa'],
            'items.*.ukuran' => ['nullable', 'string', 'max:50'],
            'items.*.unit' => ['nullable', 'string', 'max:30'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.harga_satuan' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach ($this->input('items', []) as $index => $item) {
                if (! is_array($item)) {
                    continue;
                }

                $harga = $item['harga_satuan'] ?? null;

                if ($harga === null || $harga === '') {
                    $validator->errors()->add(
                        "items.{$index}.harga_satuan",
                        "Harga satuan untuk item \"{$item['nama_barang']}\" wajib diisi.",
                    );
                }
            }
        });
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'idcust.required' => 'Customer wajib dipilih.',
            'idcust.exists' => 'Customer yang dipilih tidak ditemukan.',
            'customer_baru.nama_pt.required' => 'Nama perusahaan customer baru wajib diisi.',
            'customer_baru.notelp_pt.required' => 'Nomor telepon customer baru wajib diisi.',
            'customer_baru.notelp_pt.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, tanda +, -, dan kurung.',
            'customer_baru.alamat_pt.required' => 'Alamat customer baru wajib diisi.',
            'lead_id.exists' => 'Lead yang dirujuk tidak ditemukan.',
            'tanggal.required' => 'Tanggal quotation wajib diisi.',
            'berlaku_sampai.after_or_equal' => 'Masa berlaku tidak boleh sebelum tanggal quotation.',
            'diskon.numeric' => 'Diskon harus berupa angka.',
            'diskon.min' => 'Diskon tidak boleh kurang dari 0.',
            'ppn_persen.numeric' => 'PPN harus berupa angka persen.',
            'ppn_persen.min' => 'PPN tidak boleh kurang dari 0.',
            'ppn_persen.max' => 'PPN maksimal 100 persen.',
            'items.required' => 'Minimal satu item wajib ditambahkan.',
            'items.min' => 'Minimal satu item wajib ditambahkan.',
            'items.*.nama_barang.required' => 'Nama barang pada setiap item wajib diisi.',
            'items.*.qty.required' => 'Jumlah (qty) pada setiap item wajib diisi.',
            'items.*.qty.min' => 'Jumlah (qty) minimal 1.',
            'items.*.harga_satuan.numeric' => 'Harga satuan harus berupa angka.',
            'items.*.harga_satuan.min' => 'Harga satuan tidak boleh kurang dari 0.',
        ];
    }

    public function statusAwal(): string
    {
        return Quotation::STATUS_DRAFT;
    }
}
